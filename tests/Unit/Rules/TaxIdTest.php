<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\TaxId;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TaxIdTest extends TestCase
{
    public function test_set_data_accepts_eloquent_collection_of_documents_without_type_error(): void
    {
        $documents = new Collection([
            (object) ['type' => 'PASSPORT', 'number' => 'АА123456'],
        ]);

        $rule = new TaxId();

        $rule->setData([
            'party' => [
                'noTaxId' => true,
                'email' => 'unknown-user@example.test',
            ],
            'documents' => $documents,
        ]);

        $validator = Validator::make(
            [
                'party' => ['taxId' => 'АА123456', 'noTaxId' => true, 'email' => 'unknown-user@example.test'],
                'documents' => $documents,
            ],
            ['party.taxId' => ['required', 'string', $rule]]
        );

        $validator->validate();

        $this->assertTrue(true);
    }

    public function test_validates_national_id_from_form_documents_when_no_tax_id(): void
    {
        $rule = new TaxId();

        $validator = Validator::make(
            [
                'party' => ['taxId' => '123456789', 'noTaxId' => true, 'email' => 'unknown-user@example.test'],
                'documents' => [
                    ['type' => 'NATIONAL_ID', 'number' => '123456789'],
                ],
            ],
            ['party.taxId' => ['required', 'string', $rule]]
        );

        $this->assertFalse($validator->fails());
    }
}
