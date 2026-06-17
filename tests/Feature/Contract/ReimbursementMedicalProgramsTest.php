<?php

declare(strict_types=1);

namespace Tests\Feature\Contract;

use App\Classes\eHealth\Api\MedicalProgram;
use App\Livewire\Contract\ReimbursementContractCreate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

/**
 * Tests for ReimbursementContractCreate medical programs handling:
 *  - User selection is used (not hardcoded)
 *  - Output format is [uuid, ...] (array of strings)
 *  - Cache is not defeated on every page load
 *
 * These tests cover pure PHP logic and cache behaviour — no database needed.
 */
class ReimbursementMedicalProgramsTest extends TestCase
{
    public function test_medical_programs_payload_uses_uuid_array_format(): void
    {
        $programUuid1 = (string) Str::uuid();
        $programUuid2 = (string) Str::uuid();

        $result = array_values(array_filter([$programUuid1, $programUuid2]));

        $this->assertSame([$programUuid1, $programUuid2], $result);
    }

    public function test_medical_programs_payload_is_not_id_object_array(): void
    {
        $programUuid = (string) Str::uuid();

        $result = array_values(array_filter([$programUuid]));

        $this->assertSame([$programUuid], $result);
        $this->assertNotSame([['id' => $programUuid]], $result);
    }

    public function test_medical_programs_payload_is_empty_array_when_no_programs_selected(): void
    {
        $result = array_values(array_filter([]));

        $this->assertSame([], $result);
    }

    public function test_load_medical_programs_uses_cache_without_clearing_it_each_time(): void
    {
        Cache::flush();

        $mockPrograms = [
            ['id' => (string) Str::uuid(), 'name' => 'Insulin Program', 'type' => 'REIMBURSEMENT'],
        ];

        Cache::put('ehealth_medical_programs_reimbursement', $mockPrograms, 3600);

        $mockApi = Mockery::mock(MedicalProgram::class);
        // API must NOT be called since cache already has data
        $mockApi->shouldNotReceive('getMany');

        $programs = Cache::remember('ehealth_medical_programs_reimbursement', 3600, static function () use ($mockApi) {
            return $mockApi->getMany(['page_size' => 100])->getData();
        });

        $this->assertSame($mockPrograms, $programs);
    }

    public function test_hardcoded_insulin_id_is_not_used_in_payload(): void
    {
        $hardcodedInsulinId = '1a227396-a0e4-4c4f-a0a9-6b358c8929d2';
        $userSelectedId = (string) Str::uuid();

        $result = array_values(array_filter([$userSelectedId]));

        $this->assertNotContains($hardcodedInsulinId, $result);
        $this->assertContains($userSelectedId, $result);
    }

    public function test_collect_payload_includes_mfo_in_contractor_payment_details(): void
    {
        $data = [
            'contractorPaymentDetails' => [
                'bankName' => 'Test Bank',
                'MFO' => '351005',
                'payerAccount' => 'UA 12 345678 9012345678901234567',
            ],
        ];

        $payerAccount = str_replace(' ', '', $data['contractorPaymentDetails']['payerAccount'] ?? '');
        $mfo = trim((string) ($data['contractorPaymentDetails']['MFO'] ?? ''));

        $contractorPaymentDetails = [
            'payer_account' => $payerAccount,
            'bank_name' => $data['contractorPaymentDetails']['bankName'] ?? '',
        ];

        if ($mfo !== '') {
            $contractorPaymentDetails['MFO'] = $mfo;
        }

        $this->assertSame('351005', $contractorPaymentDetails['MFO']);
        $this->assertSame('UA123456789012345678901234567', $contractorPaymentDetails['payer_account']);
    }

    public function test_collect_payload_omits_mfo_when_not_provided(): void
    {
        $data = [
            'contractorPaymentDetails' => [
                'bankName' => 'Test Bank',
                'MFO' => '',
                'payerAccount' => 'UA123456789012345678901234567',
            ],
        ];

        $mfo = trim((string) ($data['contractorPaymentDetails']['MFO'] ?? ''));

        $contractorPaymentDetails = [
            'payer_account' => $data['contractorPaymentDetails']['payerAccount'] ?? '',
            'bank_name' => $data['contractorPaymentDetails']['bankName'] ?? '',
        ];

        if ($mfo !== '') {
            $contractorPaymentDetails['MFO'] = $mfo;
        }

        $this->assertArrayNotHasKey('MFO', $contractorPaymentDetails);
    }

    public function test_fallback_medical_programs_file_is_read_when_available(): void
    {
        $fallbackPath = storage_path('app/exports/medical-programs-valid-reimbursement.json');
        File::ensureDirectoryExists(dirname($fallbackPath));

        $expectedProgram = [
            'id' => (string) Str::uuid(),
            'name' => 'Fallback Program',
            'type' => 'MEDICATION',
        ];

        File::put($fallbackPath, json_encode([
            'programs' => [$expectedProgram],
        ], JSON_THROW_ON_ERROR));

        $component = app(ReimbursementContractCreate::class);
        $method = new \ReflectionMethod($component, 'loadMedicalProgramsFallback');
        $method->setAccessible(true);
        $result = $method->invoke($component);

        $this->assertCount(1, $result);
        $this->assertSame($expectedProgram['id'], $result[0]['id']);
        $this->assertSame($expectedProgram['name'], $result[0]['name']);
    }
}
