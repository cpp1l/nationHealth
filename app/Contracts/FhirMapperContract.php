<?php

declare(strict_types=1);

namespace App\Contracts;

interface FhirMapperContract
{
    /**
     * Convert a flat form array to a FHIR structure for persistence/API.
     *
     * @param  array  $data  Flat form data
     * @param  mixed  ...$context  Additional context (e.g. UUIDs, detail maps)
     * @return array
     */
    public function toFhir(array $data, mixed ...$context): array;

    /**
     * Convert a FHIR structure (from DB/API) to a flat form array.
     *
     * @param  array  $data  FHIR resource data
     * @param  mixed  ...$context  Additional context (e.g. detail maps)
     * @return array
     */
    public function fromFhir(array $data, mixed ...$context): array;
}
