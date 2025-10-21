<?php

namespace App\Livewire\Training;

use Livewire\Component;
use App\Models\Client;

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
