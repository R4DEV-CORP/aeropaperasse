<?php

namespace App\Livewire\Training;

use App\Models\Client;
use Livewire\Component;

class Index extends Component
{
    public $clients;

    public function mount()
    {
        $this->clients = Client::all();
    }

    public function render()
    {
        return view('livewire.training.index');
    }
}
