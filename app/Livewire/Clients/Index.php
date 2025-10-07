<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use Livewire\Attributes\On; 
use App\Models\Client;

class Index extends Component
{
    public string $search = '';

    private function loadClients()
    {
        return Client::all();
    }

    private function buildScoutQuery()
    {
        return Client::search($this->search);
    }

    /**
     * Écoute l'événement 'client-created' et recharge la liste des clients
     */
    #[On('client-created')]
    public function refreshClients()
    {
        // Force le re-rendu du composant pour mettre à jour la liste
        $this->search = '';
        $this->render();
    }

    public function render()
    {
        if(! empty($this->search)) {
            $clients = $this->buildScoutQuery()->get();
        } else {
                $clients = $this->loadClients();
        }
        return view('livewire.clients.index', [
            'clients' => $clients,
        ]);
    }
}
