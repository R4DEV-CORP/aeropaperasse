<?php

namespace App\Livewire\ActivityRequests;

use Livewire\Component;
use Livewire\Attributes\On; 
use App\Models\ActivityRequest;

class Index extends Component
{
    public string $search = '';

    private function loadActivityRequests()
    {
        return ActivityRequest::with('client')
            ->where('status', '!=', 'draft')
            ->get();
    }

    private function loadDraftActivityRequests()
    {
        return ActivityRequest::with('client')
            ->where('status', 'draft')
            ->get();
    }

    private function buildScoutQuery()
    {
        return ActivityRequest::search($this->search)
            ->query(function ($query) {
                $query->join('clients', 'activity_requests.client_id', 'clients.id')
                    ->select('activity_requests.*', 'clients.company_name as company_name', 'clients.trade_name as trade_name')
                    ->where('activity_requests.status', '!=', 'draft');
            });
    }

    /**
     * Écoute l'événement 'activity-request-created' et recharge la liste des clients
     */
    #[On('activity-request-created')]
    public function refreshActivityRequests()
    {
        // Force le re-rendu du composant pour mettre à jour la liste
        $this->search = '';
        $this->render();
    }

    /**
     * Ouvrir la modale en mode édition pour un brouillon
     */
    public function editDraft(int $activityRequestId)
    {
        // Dispatcher un événement pour informer le formulaire de charger ce brouillon
        $this->dispatch('edit-draft', activityRequestId: $activityRequestId);
    }

    public function render()
    {
        if(! empty($this->search)) {
            $activityRequests = $this->buildScoutQuery()->get();
        } else {
                $activityRequests = $this->loadActivityRequests();
        }

        $draftActivityRequests = $this->loadDraftActivityRequests();

        return view('livewire.activity-requests.index', [
            'activityRequests' => $activityRequests,
            'draftActivityRequests' => $draftActivityRequests,
        ]);
    }
}
