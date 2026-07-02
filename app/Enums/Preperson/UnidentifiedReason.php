<?php

declare(strict_types=1);

namespace App\Enums\Preperson;

use App\Traits\EnumUtils;

enum UnidentifiedReason: string
{
    use EnumUtils;

    case EMERGENCY_HOSPITALIZATION = 'EMERGENCY_HOSPITALIZATION';
    case POLICE_HOSPITALIZATION = 'POLICE_HOSPITALIZATION';
    case NEWBORN_WITHOUT_CERTIFICATE = 'NEWBORN_WITHOUT_CERTIFICATE';
    case OTHER_HOSPITALIZATION = 'OTHER_HOSPITALIZATION';

    /**
     * Human-readable label for the unidentified patient registration reason.
     *
     * @return string
     */
    public function label(): string
    {
        return __('patients.unidentified_reasons.' . $this->value);
    }
}
