<?php

namespace App\Livewire\BadgeManagement;

use App\Models\Badge;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    protected $listeners = [
        'badge-created' => 'refreshBadges',
        'badge-expiry-date-updated' => 'refreshBadges',
    ];

    private function loadBadges()
    {
        $query = Badge::with(['badgeRequest.coworker', 'badgeRequest.activityRequest.client']);

        // Si l'utilisateur n'est pas admin, filtrer par client_id de l'ActivityRequest liée
        if (! auth()->user()->isAdmin()) {
            $query->whereHas('badgeRequest.activityRequest', function ($q) {
                $q->where('client_id', auth()->user()->client_id);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function refreshBadges()
    {
        // Force le re-rendu du composant pour mettre à jour la liste
        $this->search = '';
        $this->render();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function buildScoutQuery()
    {
        $query = Badge::with(['badgeRequest.coworker', 'badgeRequest.activityRequest.client']);

        // Si l'utilisateur n'est pas admin, filtrer par client_id de l'ActivityRequest liée
        if (! auth()->user()->isAdmin()) {
            $query->whereHas('badgeRequest.activityRequest', function ($q) {
                $q->where('client_id', auth()->user()->client_id);
            });
        }

        // Recherche manuelle sur les champs souhaités
        if (! empty($this->search)) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('badgeRequest.coworker', function ($subQuery) use ($searchTerm) {
                    $subQuery->where('firstname', 'like', "%{$searchTerm}%")
                        ->orWhere('lastname', 'like', "%{$searchTerm}%");
                })
                    ->orWhereHas('badgeRequest.activityRequest.client', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('company_name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Vérifie et met à jour le statut des badges expirés
     */
    private function checkAndUpdateExpiredBadges()
    {
        $today = now()->toDateString();

        // Trouver tous les badges actifs dont la date d'expiration est dépassée
        $expiredBadges = Badge::where('status', 'active')
            ->where('expiry_date', '<', $today)
            ->get();

        foreach ($expiredBadges as $badge) {
            // Sauvegarder l'ancien statut
            $badge->previous_status = $badge->status;
            // Changer le statut à expiré
            $badge->status = 'expired';
            $badge->save();

            \Log::info("Badge {$badge->id} automatiquement expiré - date d'expiration: {$badge->expiry_date}");
        }

        if ($expiredBadges->count() > 0) {
            \Log::info("{$expiredBadges->count()} badge(s) automatiquement expiré(s)");
        }
    }

    /**
     * Marque un badge comme retourné
     */
    public function returnBadge($badgeId)
    {
        try {
            $badge = Badge::findOrFail($badgeId);

            // Sauvegarder l'ancien statut
            $badge->previous_status = $badge->status;
            $badge->status = 'returned';
            $badge->returned_at = now();
            $badge->save();

            \Log::info("Badge {$badgeId} marqué comme retourné par l'utilisateur ".auth()->id());
            session()->flash('success', 'Le badge a été marqué comme retourné avec succès.');

        } catch (\Exception $e) {
            \Log::error("Erreur lors du retour du badge {$badgeId}: ".$e->getMessage());
            session()->flash('error', 'Une erreur est survenue lors de la mise à jour du badge.');
        }
    }

    /**
     * Marque un badge comme non retourné
     */
    public function notReturnedBadge($badgeId)
    {
        try {
            $badge = Badge::findOrFail($badgeId);

            // Sauvegarder l'ancien statut
            $badge->previous_status = $badge->status;
            $badge->status = 'not_returned';
            $badge->returned_at = null; // Réinitialiser la date de retour
            $badge->save();

            \Log::info("Badge {$badgeId} marqué comme non retourné par l'utilisateur ".auth()->id());
            session()->flash('success', 'Le badge a été marqué comme non retourné avec succès.');

        } catch (\Exception $e) {
            \Log::error("Erreur lors de la mise à jour du badge {$badgeId}: ".$e->getMessage());
            session()->flash('error', 'Une erreur est survenue lors de la mise à jour du badge.');
        }
    }

    public function render()
    {
        // Vérifier et mettre à jour les badges expirés avant d'afficher la liste
        $this->checkAndUpdateExpiredBadges();

        if (! empty($this->search)) {
            $badges = $this->buildScoutQuery()->paginate(10);
        } else {
            $badges = $this->loadBadges();
        }

        return view('livewire.badge-management.index', [
            'badges' => $badges,
        ]);
    }
}
