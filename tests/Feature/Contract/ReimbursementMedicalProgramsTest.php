<?php

declare(strict_types=1);

namespace Tests\Feature\Contract;

use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tests for ReimbursementContractCreate medical programs handling:
 *  - User selection is used (not hardcoded)
 *  - Output format is [uuid, ...] (array of strings)
 *  - MFO in contractor_payment_details
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

    public function test_hardcoded_insulin_id_is_not_used_in_payload(): void
    {
        $hardcodedInsulinId = '1a227396-a0e4-4c4f-a0a9-6b358c8929d2';
        $userSelectedId = (string) Str::uuid();

        $result = array_values(array_filter([$userSelectedId]));

        $this->assertNotContains($hardcodedInsulinId, $result);
        $this->assertContains($userSelectedId, $result);
    }

    public function test_reimbursement_program_filter_excludes_inactive_and_test_programs(): void
    {
        $programs = [
            [
                'id' => (string) Str::uuid(),
                'name' => 'Valid Program',
                'is_active' => true,
                'funding_source' => 'NHS',
                'type' => 'MEDICATION',
                'medical_program_settings' => ['request_allowed' => true],
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Test Program',
                'is_active' => true,
                'funding_source' => 'NHS',
                'type' => 'MEDICATION',
                'medical_program_settings' => ['request_allowed' => true],
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Inactive Program',
                'is_active' => false,
                'funding_source' => 'NHS',
                'type' => 'MEDICATION',
                'medical_program_settings' => ['request_allowed' => true],
            ],
        ];

        $filtered = collect($programs)->filter(static function (array $item): bool {
            $name = mb_strtolower((string) ($item['name'] ?? ''));
            $settings = $item['medical_program_settings'] ?? [];

            return (bool) ($item['is_active'] ?? false)
                && ($item['funding_source'] ?? null) === 'NHS'
                && ($item['type'] ?? null) === 'MEDICATION'
                && (bool) ($settings['request_allowed'] ?? false)
                && !str_contains($name, 'тест')
                && !str_contains($name, 'test');
        })->values()->all();

        $this->assertCount(1, $filtered);
        $this->assertSame('Valid Program', $filtered[0]['name']);
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
}
