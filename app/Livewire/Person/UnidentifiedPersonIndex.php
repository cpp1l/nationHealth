<?php

declare(strict_types=1);

namespace App\Livewire\Person;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class UnidentifiedPersonIndex extends Component
{
    public function render(): View
    {
        return view('livewire.person.unidentified-person-index');
    }
}
