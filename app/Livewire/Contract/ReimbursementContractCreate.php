<?php

declare(strict_types=1);

namespace App\Livewire\Contract;

use App\Classes\eHealth\Api\MedicalProgram;
use App\Livewire\Contract\Forms\ReimbursementContractRequestForm as Form;
use App\Models\LegalEntity;
use App\Repositories\Repository;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Log;

class ReimbursementContractCreate extends ContractComponent
{
    public Form $form;
    public array $allMedicalPrograms = [];
    public array $medicalProgramsList = [];

    /**
     * Stores the UUID of the current draft to prevent duplicates.
     */
    public ?string $savedUuid = null;

    protected array $dictionaryNames = [
        'REIMBURSEMENT_CONTRACT_TYPE',
        'REIMBURSEMENT_CONTRACT_CONSENT_TEXT'
    ];

    public function mount(LegalEntity $legalEntity): void
    {
        $this->baseMount($legalEntity);
        $this->loadMedicalPrograms();
    }

    protected function loadMedicalPrograms(): void
    {
        $programs = Cache::remember('ehealth_medical_programs_reimbursement', 3600, function () {
            try {
                $response = (new MedicalProgram())->asMis()->getMany([
                    'page_size' => 100,
                ]);

                $apiPrograms = $response->getData();

                if (!empty($apiPrograms)) {
                    return $apiPrograms;
                }

                Log::warning('Medical Programs API returned empty list. Using fallback JSON.');

                return $this->loadMedicalProgramsFallback();
            } catch (\Exception $e) {
                Log::error('Medical Programs Fetch Error: ' . $e->getMessage());

                return $this->loadMedicalProgramsFallback();
            }
        });

        $this->allMedicalPrograms = array_values(array_filter(
            $programs,
            static function (array $item): bool {
                $name = mb_strtolower((string) ($item['name'] ?? ''));
                $settings = $item['medical_program_settings'] ?? [];

                // Hide inactive, non-reimbursement-like, and obvious test programs.
                return (bool) ($item['is_active'] ?? false)
                    && ($item['funding_source'] ?? null) === 'NHS'
                    && ($item['type'] ?? null) === 'MEDICATION'
                    && (bool) ($settings['request_allowed'] ?? false)
                    && !str_contains($name, 'тест')
                    && !str_contains($name, 'test');
            }
        ));

        $this->applyMedicalProgramsFilter();
    }

    public function updatedFormIdForm(): void
    {
        $this->applyMedicalProgramsFilter();
    }

    private function applyMedicalProgramsFilter(): void
    {
        $idForm = $this->form->idForm ?? null;

        $filteredPrograms = array_values(array_filter(
            $this->allMedicalPrograms,
            static function (array $item) use ($idForm): bool {
                $mrBlankType = $item['mr_blank_type'] ?? null;

                // For psychiatry contracts, allow F-3 programs.
                if ($idForm === 'PSYCHIATRY') {
                    return in_array($mrBlankType, ['F-1', 'F-3'], true);
                }

                // For GENERAL/PMD_1/ND_1/INSULIN_1 keep regular reimbursement forms.
                return $mrBlankType === 'F-1';
            }
        ));

        $this->medicalProgramsList = array_map(static fn (array $item) => [
            'id' => $item['id'],
            'name' => $item['name'] . ' (' . ($item['type'] ?? 'N/A') . ')',
        ], $filteredPrograms);
    }

    /**
     * Load locally saved fallback list of valid reimbursement programs.
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadMedicalProgramsFallback(): array
    {
        $path = storage_path('app/exports/medical-programs-valid-reimbursement.json');

        if (!File::exists($path)) {
            Log::warning('Medical Programs fallback file is missing.', ['path' => $path]);

            return [];
        }

        try {
            $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            Log::error('Medical Programs fallback JSON decode failed.', [
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        $programs = $decoded['programs'] ?? $decoded;

        if (!is_array($programs)) {
            return [];
        }

        return array_values(array_filter(
            $programs,
            static fn (mixed $item): bool => is_array($item) && !empty($item['id']) && !empty($item['name'])
        ));
    }

    protected function getContractType(): string
    {
        return 'reimbursement';
    }

    protected function collectPayload(array $data): array
    {
        $consentTextString = $this->dictionaries['REIMBURSEMENT_CONTRACT_CONSENT_TEXT']['APPROVED']
            ?? 'Я підтверджую достовірність наданих даних...';

        $payerAccount = str_replace(' ', '', $data['contractorPaymentDetails']['payerAccount'] ?? '');
        $mfo = trim((string) ($data['contractorPaymentDetails']['MFO'] ?? ''));

        $selectedProgramIds = array_filter($data['medicalPrograms'] ?? []);

        $contractorPaymentDetails = [
            'payer_account' => $payerAccount,
            'bank_name' => $data['contractorPaymentDetails']['bankName'] ?? '',
        ];

        if ($mfo !== '') {
            $contractorPaymentDetails['MFO'] = $mfo;
        }

        $payload = [
            'contractor_owner_id' => $this->form->contractorOwnerId,
            'contractor_base' => $data['contractorBase'],
            'contractor_payment_details' => $contractorPaymentDetails,
            'start_date' => Carbon::now()->addDay()->format('Y-m-d'),
            'end_date' => Carbon::parse($data['endDate'])->format('Y-m-d'),

            'id_form' => $data['idForm'] ?? 'GENERAL',

            'statute_md5' => $data['statuteMd5'] ?? null,
            'additional_document_md5' => $data['additionalDocumentMd5'] ?? null,

            'consent_text' => $consentTextString,

            // eHealth create contract request schema expects array of UUID strings.
            'medical_programs' => array_values($selectedProgramIds),
        ];

        if (!empty($data['previousRequestId'])) {
            $payload['previous_request_id'] = $data['previousRequestId'];
        }

        return $payload;
    }

    /**
     * Saves or updates the draft using the repository.
     */
    public function save(): void
    {
        $validatedData = $this->form->validate();
        $payload = $this->collectPayload($validatedData);

        if ($this->savedUuid) {
            $payload['id'] = $this->savedUuid;
            $payload['status'] = 'NEW';
        } else {
            $newUuid = Str::uuid()->toString();
            $payload['id'] = $newUuid;
            $payload['contract_number'] = $this->generateContractNumber();
            $payload['status'] = 'NEW';
        }

        try {
            // Here it is saved/updated in the database
            $contractRequest = Repository::contractRequest()->saveFromEHealth($payload, 'REIMBURSEMENT');

            $this->savedUuid = $contractRequest->uuid;

            // Add "?? ''" to convert NULL to an empty string
            // This is critical for editing drafts where the number is not yet available
            $this->form->contractNumber = $contractRequest->contract_number ?? '';

            $this->dispatch('flashMessage', [
                'message' => 'Чернетку успішно збережено.',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving contract request: ' . $e->getMessage());
            $this->dispatch('flashMessage', [
                'message' => 'Помилка збереження: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    /**
     * Helper to generate a dummy number if NULL is rejected.
     */
    private function generateContractNumber(): string
    {
        return sprintf(
            '%04d-%s-%04d',
            rand(1000, 9999),
            strtoupper(Str::random(4)),
            rand(1000, 9999)
        );
    }

    public function render(): View
    {
        return view('livewire.contract.reimbursement-contract-create');
    }
}
