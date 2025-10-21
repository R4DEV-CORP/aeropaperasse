<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';

    public $openClientId = null;

    public function developpeClient(int $clientId)
    {
        if($this->openClientId == $clientId) {
            $this->openClientId = null;
        } else {
            $this->openClientId = $clientId;
        }
    }

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

    /**
     * Écoute l'événement 'client-updated' et recharge la liste des clients
     */
    #[On('client-updated')]
    public function refreshClientsAfterUpdate()
    {
        // Force le re-rendu du composant pour mettre à jour la liste
        $this->search = '';
        $this->render();
    }

    public function render()
    {
        if (! empty($this->search)) {
            $clients = $this->buildScoutQuery()->get();
        } else {
            $clients = $this->loadClients();
        }

        return view('livewire.clients.index', [
            'clients' => $clients,
        ]);
    }
}
