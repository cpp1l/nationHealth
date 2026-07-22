<?php

declare(strict_types=1);

namespace App\Livewire\Person\Records;

use App\Models\LegalEntity;
use App\Models\MedicalEvents\Sql\Episode;
use App\Models\Person\Person;
use App\Models\Preperson;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;

class PatientEpisodeEdit extends BasePatientComponent
{
    /**
     * Local episode ID; `null` when creating a new episode.
     *
     * @var int|null
     */
    #[Locked]
    public ?int $episodeId = null;

    public string $name = '';

    public string $careManagerUuid = '';

    public string $typeCode = 'treatment';

    public string $statusCode = 'active';

    public string $startDate = '';

    public string $startTime = '';

    public array $employees = [];

    public array $episodeTypes = [];

    public array $episodeStatuses = [];

    /**
     * Bind the route models; the episode is absent on the create route.
     *
     * @param  LegalEntity  $legalEntity
     * @param  Person|null  $person
     * @param  Preperson|null  $preperson
     * @param  Episode|null  $episode
     * @return void
     */
    public function mount(
        LegalEntity $legalEntity,
        ?Person $person = null,
        ?Preperson $preperson = null,
        ?Episode $episode = null
    ): void {
        parent::mount($legalEntity, $person, $preperson);

        $this->episodeId = $episode?->id;
    }

    public function save(): void
    {
    }

    public function cancel(): void
    {
    }

    public function render(): View
    {
        return view('livewire.person.records.episode-edit');
    }
}
