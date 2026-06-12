<?php

declare(strict_types=1);

namespace App\Livewire\DiagnosticReport\Forms;

use App\Rules\InDictionary;
use App\Core\BaseForm;
use Illuminate\Validation\Rule;

class DiagnosticReportForm extends BaseForm
{
    public array $diagnosticReport = [];

    public array $observations = [];

    protected function rules(): array
    {
        return [
            'diagnosticReport.referralType' => ['nullable', 'string'],
            'diagnosticReport.primarySource' => ['required', 'boolean:strict'],
            'diagnosticReport.categoryCode' => [
                'required',
                'string',
                new InDictionary('eHealth/diagnostic_report_categories')
            ],
            'diagnosticReport.codeValue' => [
                'required',
                'uuid',
            ],
            'diagnosticReport.paperReferralRequisition' => ['nullable', 'string', 'max:255'],
            'diagnosticReport.paperReferralRequesterEmployeeName' => ['nullable', 'string', 'max:255'],
            'diagnosticReport.paperReferralRequesterLegalEntityEdrpou' => [
                Rule::requiredIf(
                    data_get($this->diagnosticReport, 'isReferralAvailable') === true
                    && data_get($this->diagnosticReport, 'referralType') === 'paper'
                ),
                'nullable',
                'digits_between:8,10',
                'string',
                'max:255',
            ],
            'diagnosticReport.paperReferralRequesterLegalEntityName' => [
                Rule::requiredIf(
                    data_get($this->diagnosticReport, 'isReferralAvailable') === true
                    && data_get($this->diagnosticReport, 'referralType') === 'paper'
                ),
                'nullable',
                'string',
                'max:255',
            ],
            'diagnosticReport.paperReferralServiceRequestDate' => [
                Rule::requiredIf(
                    data_get($this->diagnosticReport, 'isReferralAvailable') === true
                    && data_get($this->diagnosticReport, 'referralType') === 'paper'
                ),
                'nullable',
                'date',
            ],
            'diagnosticReport.paperReferralNote' => ['nullable', 'string', 'max:255'],
            'diagnosticReport.effectivePeriodStartDate' => ['nullable', 'date', 'before_or_equal:now',],
            'diagnosticReport.effectivePeriodStartTime' => ['nullable', 'date_format:H:i', 'before_or_equal:now'],
            'diagnosticReport.effectivePeriodEndDate' => [
                'nullable',
                'date',
                'before_or_equal:today',
                'after_or_equal:diagnosticReport.effectivePeriodStartDate'
            ],
            'diagnosticReport.effectivePeriodEndTime' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $now = now()->format('H:i');
                    if ($value > $now) {
                        $fail('Час завершення прийому не може бути в майбутньому.');
                    }
                },
                'after:diagnosticReport.effectivePeriodStartTime'
            ],
            'diagnosticReport.issuedDate' => ['required', 'date', 'before_or_equal:now'],
            'diagnosticReport.issuedTime' => ['required', 'date_format:H:i', 'before_or_equal:now'],
            'diagnosticReport.conclusionCode' => [
                'nullable',
                'string',
                new InDictionary('eHealth/ICD10_AM/condition_codes')
            ],
            'diagnosticReport.conclusion' => [
                Rule::requiredIf(function () {
                    return in_array(
                        data_get($this->diagnosticReport, 'categoryCode'),
                        ['diagnostic_procedure', 'imaging'],
                        true
                    );
                }),
                'nullable',
                'string',
                'max:1000',
            ],
            'diagnosticReport.divisionId' => ['nullable', 'uuid'],
            'diagnosticReport.resultsInterpreterEmployeeId' => ['nullable', 'uuid'],

            'observations' => ['nullable', 'array'],
            'observations.*.uuid' => ['nullable', 'uuid'],
            'observations.*.categorySystem' => ['required_with:observations', 'string'],
            'observations.*.categoryCode' => [
                'required_with:observations',
                'string',
                new InDictionary(['eHealth/observation_categories', 'eHealth/ICF/observation_categories']),
            ],
            'observations.*.codeSystem' => ['required_with:observations', 'string'],
            'observations.*.codeCode' => [
                'required_with:observations',
                'string',
                new InDictionary([
                    'eHealth/LOINC/observation_codes',
                    'eHealth/custom/observation_codes',
                    'eHealth/ICF/classifiers',
                ]),
            ],

            'observations.*.issuedDate' => ['required_with:observations', 'date', 'before_or_equal:today'],
            'observations.*.issuedTime' => ['required_with:observations', 'date_format:H:i'],
            'observations.*.effectiveDate' => ['nullable', 'date', 'before_or_equal:today'],
            'observations.*.effectiveTime' => ['nullable', 'date_format:H:i'],

            'observations.*.primarySource' => ['required_with:observations', 'boolean'],
            'observations.*.reportOriginCode' => Rule::forEach(function (mixed $value, string $attribute) {
                $index = (int) explode('.', $attribute)[1];
                $primarySource = $this->observations[$index]['primarySource'] ?? true;

                return [
                    Rule::requiredIf($primarySource === false),
                    $primarySource === true ? 'prohibited' : 'nullable',
                    'string',
                    new InDictionary('eHealth/report_origins'),
                ];
            }),
            'observations.*.reportOriginText' => ['nullable', 'string', 'max:255'],
            'observations.*.interpretationCode' => [
                'nullable',
                'string',
                new InDictionary('eHealth/observation_interpretations'),
            ],
            'observations.*.bodySiteCode' => [
                'nullable',
                'string',
                new InDictionary('eHealth/body_sites'),
            ],
            'observations.*.methodCode' => [
                'nullable',
                'string',
                new InDictionary('eHealth/observation_methods'),
            ],
            'observations.*.dictionaryName' => ['nullable', 'string'],
            'observations.*.comment' => ['nullable', 'string', 'max:1000'],

            'observations.*.valueQuantityValue' => ['nullable', 'numeric'],
            'observations.*.valueQuantityComparator' => ['nullable', 'string', Rule::in(['>', '>=', '=', '<=', '<'])],
            'observations.*.valueQuantityUnit' => ['nullable', 'string', new InDictionary('eHealth/ucum/units')],
            'observations.*.valueQuantitySystem' => ['nullable', 'string'],
            'observations.*.valueQuantityCode' => ['nullable', 'string'],

            'observations.*.valueCodeableConcept' => ['nullable', 'string'],
            'observations.*.valueString' => ['nullable', 'string'],
            'observations.*.valueBoolean' => ['nullable', 'boolean'],
            'observations.*.valueDate' => ['nullable', 'date', 'before_or_equal:today'],
            'observations.*.valueTime' => ['nullable', 'date_format:H:i'],

            'observations.*.components' => ['nullable', 'array'],
            'observations.*.components.*.codeCode' => ['nullable', 'string'],
            'observations.*.components.*.codeSystem' => ['nullable', 'string'],
            'observations.*.components.*.valueCode' => ['nullable', 'string'],
            'observations.*.components.*.valueSystem' => ['nullable', 'string'],
            'observations.*.components.*.interpretationCode' => [
                'nullable',
                'string',
                new InDictionary('eHealth/observation_interpretations'),
            ],
        ];
    }
}
