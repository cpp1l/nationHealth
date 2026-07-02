<?php

declare(strict_types=1);

namespace App\Livewire\Preperson\Forms;

use App\Core\BaseForm;
use App\Enums\Preperson\UnidentifiedReason;
use App\Rules\InDictionary;
use App\Rules\NameFields;
use App\Rules\PhoneNumber;
use Illuminate\Validation\Rule;

class PrepersonForm extends BaseForm
{
    public array $person = [
        'emergencyContact' => [
            'phones' => [['type' => null, 'number' => null]]
        ]
    ];

    public array $reasonContext = [
        'unidentifiedReason' => '',
        'ambulanceCardNumber' => '',
        'policeReportId' => '',
        'policeReportDate' => '',
        'childBirthTime' => '',
        'unidentifiedOtherReason' => ''
    ];

    /**
     * Validation rules for creating an unidentified patient (preperson).
     *
     * @return array
     */
    public function rulesForCreate(): array
    {
        $emergencyContactRequired = $this->hasEmergencyContactData()
            || $this->reasonContext['unidentifiedReason'] === UnidentifiedReason::NEWBORN_WITHOUT_CERTIFICATE->value;

        return [
            'person.firstName' => ['nullable', 'min:3', new NameFields()],
            'person.lastName' => ['nullable', 'min:3', new NameFields()],
            'person.secondName' => ['nullable', 'min:3', new NameFields()],
            'person.birthDate' => ['nullable', 'date_format:' . config('app.date_format')],
            'person.gender' => ['required', 'string', new InDictionary('GENDER')],
            'person.emergencyContact.firstName' => [Rule::requiredIf($emergencyContactRequired), 'min:3', new NameFields()],
            'person.emergencyContact.lastName' => [Rule::requiredIf($emergencyContactRequired), 'min:3', new NameFields()],
            'person.emergencyContact.secondName' => ['nullable', 'min:3', new NameFields()],
            'person.emergencyContact.phones.*.type' => [
                'nullable',
                'string',
                'distinct',
                'required_with:person.emergencyContact.phones.*.number'
            ],
            'person.emergencyContact.phones.*.number' => [
                'nullable',
                'string',
                new PhoneNumber(),
                'distinct',
                'required_with:person.emergencyContact.phones.*.type'
            ],

            'reasonContext.unidentifiedReason' => [
                'nullable',
                Rule::when(
                    filled($this->reasonContext['unidentifiedReason']),
                    [Rule::enum(UnidentifiedReason::class)]
                )
            ],
            'reasonContext.ambulanceCardNumber' => [
                'nullable',
                'required_if:reasonContext.unidentifiedReason,' . UnidentifiedReason::EMERGENCY_HOSPITALIZATION->value,
                'string',
                'max:255'
            ],
            'reasonContext.policeReportId' => [
                'nullable',
                'required_if:reasonContext.unidentifiedReason,' . UnidentifiedReason::POLICE_HOSPITALIZATION->value,
                'string',
                'max:255'
            ],
            'reasonContext.policeReportDate' => [
                'nullable',
                'required_if:reasonContext.unidentifiedReason,' . UnidentifiedReason::POLICE_HOSPITALIZATION->value,
                'date_format:' . config('app.date_format')
            ],
            'reasonContext.childBirthTime' => [
                'nullable',
                'required_if:reasonContext.unidentifiedReason,' . UnidentifiedReason::NEWBORN_WITHOUT_CERTIFICATE->value,
                'date_format:H:i'
            ],
            'reasonContext.unidentifiedOtherReason' => [
                'nullable',
                'required_if:reasonContext.unidentifiedReason,' . UnidentifiedReason::OTHER_HOSPITALIZATION->value,
                'string',
                'max:255'
            ]
        ];
    }

    /**
     * Determine whether any emergency contact field was filled in.
     *
     * @return bool
     */
    private function hasEmergencyContactData(): bool
    {
        $contact = $this->person['emergencyContact'] ?? [];

        return filled($contact['firstName'] ?? null)
            || filled($contact['lastName'] ?? null)
            || filled($contact['secondName'] ?? null)
            || filled(array_filter($contact['phones'][0] ?? []));
    }

    /**
     * Assemble the eHealth "notes" text from the selected reason and its context fields.
     *
     * @return string
     */
    public function buildNote(): string
    {
        $reason = UnidentifiedReason::tryFrom($this->reasonContext['unidentifiedReason']);

        if ($reason === null) {
            return '';
        }

        return match ($reason) {
            UnidentifiedReason::EMERGENCY_HOSPITALIZATION => __('patients.unidentified_notes.ambulance', [
                'number' => $this->reasonContext['ambulanceCardNumber']
            ]),
            UnidentifiedReason::POLICE_HOSPITALIZATION => __('patients.unidentified_notes.police', [
                'id' => $this->reasonContext['policeReportId'],
                'date' => $this->reasonContext['policeReportDate']
            ]),
            UnidentifiedReason::NEWBORN_WITHOUT_CERTIFICATE => __('patients.unidentified_notes.newborn', [
                'time' => $this->reasonContext['childBirthTime']
            ]),
            UnidentifiedReason::OTHER_HOSPITALIZATION => $this->reasonContext['unidentifiedOtherReason']
        };
    }
}
