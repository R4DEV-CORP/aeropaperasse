<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use App\Models\Client;

class ViewClient extends Component
{

    public string $slug;

    public function mount(string $slug) : void
    {
        $this->slug = $slug;

        $this->client = Client::where('slug', $slug)->first();
    }

    public function render()
    {
        return view('livewire.clients.view-client', ['client' => $this->client]);
    }
}
