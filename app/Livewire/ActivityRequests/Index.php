<?php

namespace App\Livewire\ActivityRequests;

use App\Models\ActivityRequest;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';

    private function loadActivityRequests()
    {
        return ActivityRequest::with('client')
            ->where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function loadDraftActivityRequests()
    {
        return ActivityRequest::with('client')
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
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
        if (! empty($this->search)) {
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
