<?php

namespace App\Livewire\BadgeRequests;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use App\Models\BadgeRequest;

class Index extends Component
{
    use WithPagination, WithoutUrlPagination;
    public string $search = '';

    /**
     * Réinitialiser la pagination lors d'une recherche
     */
    public function updatedSearch()
    {
        $this->resetPage('page');
    }

    private function getStatistics()
    {
        $query = BadgeRequest::query();

        // Si l'utilisateur n'est pas admin, filtrer par client_id de l'ActivityRequest liée
        if (! auth()->user()->isAdmin()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('client_id', auth()->user()->client_id);
            });
        }

        return [
            'total' => $query->where('status', '!=', 'draft')->count(),
            'pending_rem' => (clone $query)->where('status', 'pending_rem')->count(),
            'rejected_rem' => (clone $query)->where('status', 'rejected_rem')->count(),
            'pending_adp' => (clone $query)->where('status', 'pending_adp')->count(),
            'approved_adp' => (clone $query)->where('status', 'approved_adp')->count(),
            'rejected_adp' => (clone $query)->where('status', 'rejected_adp')->count(),
            'pending_fabrication' => (clone $query)->where('status', 'pending_fabrication')->count(),
            'ready_for_delivery' => (clone $query)->where('status', 'ready_for_delivery')->count(),
        ];
    }

    private function loadBadgeRequests()
    {
        $query = BadgeRequest::with('activityRequest')
            ->where('status', '!=', 'draft');

        // Si l'utilisateur n'est pas admin, filtrer par client_id de l'ActivityRequest liée
        if (! auth()->user()->isAdmin()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('client_id', auth()->user()->client_id);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page');
    }

    private function loadDraftBadgeRequests()
    {
        $query = BadgeRequest::with('activityRequest')
            ->where('status', 'draft');

        /// Si l'utilisateur n'est pas admin, filtrer par client_id de l'ActivityRequest liée
        if (! auth()->user()->isAdmin()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('client_id', auth()->user()->client_id);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10, ['*'], 'draftPage');
    }

    private function buildScoutQuery()
    {
        return BadgeRequest::search($this->search)
            ->query(function ($query) {
                $query->join('coworkers', 'badge_requests.coworker_id', 'coworkers.id')
                    ->select('badge_requests.*', 'coworkers.firstname as firstname', 'coworkers.lastname as lastname', 'coworkers.email as email')
                    ->where('badge_requests.status', '!=', 'draft');

                /// Si l'utilisateur n'est pas admin, filtrer par client_id de l'ActivityRequest liée
                if (! auth()->user()->isAdmin()) {
                    $query->whereHas('activityRequest', function ($q) {
                        $q->where('client_id', auth()->user()->client_id);
                    });
                }
            });
    }

    /**
     * Écoute l'événement 'badge-request-created' et recharge la liste des demandes de badge
     */
    #[On('badge-request-created')]
    public function refreshBadgeRequests()
    {
        // Force le re-rendu du composant pour mettre à jour la liste
        $this->search = '';
        $this->render();
    }

    /**
     * Ouvrir la modale en mode édition pour un brouillon
     */
    public function editDraft(int $badgeRequestId)
    {
        // Dispatcher un événement pour informer le formulaire de charger ce brouillon
        $this->dispatch('edit-draft', badgeRequestId: $badgeRequestId);
    }

    public function render()
    {
        if (! empty($this->search)) {
            $badgeRequests = $this->buildScoutQuery()->paginate(10, 'page');
        } else {
            $badgeRequests = $this->loadBadgeRequests();
        }

        $draftBadgeRequests = $this->loadDraftBadgeRequests();
        $statistics = $this->getStatistics();
        return view('livewire.badge-requests.index', [
            'badgeRequests' => $badgeRequests,
            'draftBadgeRequests' => $draftBadgeRequests,
            'statistics' => $statistics,
        ]);
    }
}
