<?php

namespace App\Livewire\ActivityRequests;

use Livewire\Component;

class CreateActivityRequestForm extends Component
{

    public $user;
    public $client;

    // Propriétés pour la gestion des messages
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->user = auth()->user();
        $this->client = $this->user->client;
    }

    public function render()
    {
        return view('livewire.activity-requests.create-activity-request-form');
    }
}
