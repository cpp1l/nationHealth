<?php

declare(strict_types=1);

namespace App\Livewire\DiagnosticReport;

use App\Classes\eHealth\EHealth;
use App\Exceptions\EHealth\EHealthConnectionException;
use App\Exceptions\EHealth\EHealthException;
use App\Enums\Person\DiagnosticReportStatus;
use App\Models\MedicalEvents\Sql\DiagnosticReport;
use App\Services\MedicalEvents\Fhir;
use App\Core\Arr;
use App\Repositories\MedicalEvents\Repository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Throwable;

class DiagnosticReportCreate extends DiagnosticReportComponent
{
    /**
     * Validate and save data.
     *
     * @param  array  $diagnosticReportData
     * @return void
     */
    public function save(array $diagnosticReportData): void
    {
        if (Auth::user()->cannot('create', DiagnosticReport::class)) {
            Session::flash('error', __('patients.policy.create_diagnostic_report'));

            return;
        }

        $employee = Auth::user()->getDiagnosticReportWriterEmployee();
        if (!$employee) {
            Session::flash('error', __('patients.messages.diagnostic_report_writer_employee_not_found'));
            return;
        }

        $this->form->diagnosticReport = $diagnosticReportData;

        try {
            $validated = $this->form->validate();
        } catch (ValidationException $exception) {
            Session::flash('error', $exception->validator->errors()->first());
            $this->setErrorBag($exception->validator->getMessageBag());

            return;
        }

        $formattedData = $this->prepareFormattedData($validated, DiagnosticReportStatus::DRAFT);

        try {
            $this->storeValidatedData($formattedData);
        } catch (Exception|Throwable $exception) {
            $this->handleDatabaseErrors($exception, 'Error while saving diagnostic report');

            return;
        }

        Session::flash('success', __('patients.messages.diagnostic_report_draft_saved'));
        $this->redirectRoute('persons.index', [legalEntity()], navigate: true);
    }

    /**
     * Submit encrypted data.
     *
     * @param  array  $diagnosticReportData
     * @return void
     */
    public function sign(array $diagnosticReportData): void
    {
        if (Auth::user()->cannot('create', DiagnosticReport::class)) {
            Session::flash('error', __('patient.policy.create_diagnostic_report'));

            return;
        }

        $this->form->diagnosticReport = $diagnosticReportData;

        try {
            $validated = $this->form->validate();
            $validatedCipher = $this->form->validate($this->form->signingRules());
        } catch (ValidationException $exception) {
            Session::flash('error', $exception->validator->errors()->first());
            $this->setErrorBag($exception->validator->getMessageBag());

            return;
        }

        $formattedData = $this->prepareFormattedData($validated, DiagnosticReportStatus::FINAL);

        try {
            $signedContent = signatureService()->signData(
                Arr::toSnakeCase($formattedData),
                $validatedCipher['password'],
                $validatedCipher['knedp'],
                $validatedCipher['keyContainerUpload'],
                Auth::user()->party->taxId
            );
        } catch (Throwable $exception) {
            Session::flash('error', $exception->getMessage());

            return;
        }

        try {
            EHealth::diagnosticReport()->create($this->patientUuid, ['signed_data' => $signedContent]);

            $this->storeValidatedData($formattedData);

            Session::flash('success', __('patients.messages.diagnostic_report_create_request_sent'));
            $this->redirectRoute('persons.index', [legalEntity()], navigate: true);
        } catch (EHealthException|EHealthConnectionException $exception) {
            $exception->handle('Error when creating a diagnostic report');

            return;
        } catch (Throwable $exception) {
            $this->handleDatabaseErrors($exception, 'Error while saving diagnostic report');

            return;
        }
    }

    /**
     * Prepare formatted data.
     *
     * @param  array  $validatedData
     * @param  DiagnosticReportStatus $status
     * @return array
     */
    protected function prepareFormattedData(array $validatedData, DiagnosticReportStatus $status): array
    {
        $uuids = [
            'employee' => Auth::user()->getDiagnosticReportWriterEmployee()->uuid,
            'diagnosticReport' => Str::uuid()->toString(),
        ];

        $diagnosticReport = Fhir::diagnosticReport()->toFhir(
            $validatedData['diagnosticReport'],
            $uuids,
            $status
        );

        $observations = collect($validatedData['observations'] ?? [])
            ->map(fn (array $observation) => Fhir::observation()->toFhir($observation, $uuids))
            ->values()
            ->toArray();

        return [
            'diagnosticReport' => $diagnosticReport,
            'observations' => $observations,
        ];
    }

    /**
     * Store validated formatted data into DB.
     *
     * @param  array  $formattedData
     * @return void
     * @throws Throwable
     */
    protected function storeValidatedData(array $formattedData): void
    {
        DB::transaction(function () use ($formattedData) {
            $diagnosticReportId = Repository::diagnosticReport()->store([$formattedData['diagnosticReport']], $this->personId);

            if (isset($formattedData['observations'])) {
                Repository::observation()->store($formattedData['observations'], $this->personId, diagnosticReportId: $diagnosticReportId);
            }
        });
    }
}
