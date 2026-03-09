<?php

namespace App\Livewire\Workshop;

use Livewire\Component;
use App\Models\WorkshopJobcard;

class WorkshopJobcardIndex extends Component
{
    public $workshopJobcards;

    public function mount()
    {
        $this->workshopJobcards = WorkshopJobcard::with('jobcard')->get();
    }

    public function render()
    {
        return view('livewire.workshop.workshop-jobcard-index', [
            'workshopJobcards' => $this->workshopJobcards,
        ]);
    }
}
