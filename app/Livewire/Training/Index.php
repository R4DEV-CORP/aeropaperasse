<?php

namespace App\Livewire\Training;

use App\Models\Client;
use Livewire\Component;

class Index extends Component
{
    public $clients;

    public function mount()
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            $this->clients = Client::all();
        } else {
            $client = Client::where('id', $user->client_id)->first();

            return redirect()->route('training.client', ['slug' => $client->slug]);
        }
    }

    public function render()
    {
        return view('livewire.training.index');
    }
}
