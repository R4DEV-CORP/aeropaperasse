<?php

namespace App\Livewire\Coworkers;

use App\Models\Coworker;
use Livewire\Component;

class ViewCoworker extends Component
{
    public $coworker;

    public function mount($coworkerId)
    {
        $this->coworker = Coworker::with(['client', 'user', 'badgeRequests', 'trainings'])->find($coworkerId);
    }

    public function render()
    {
        return view('livewire.coworkers.view-coworker');
    }
}
