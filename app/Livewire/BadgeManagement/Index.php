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
        'badge-number-updated' => 'refreshBadges',
    ];

    private function baseQuery()
    {
        $query = Badge::with([
            'client',
            'coworker',
            'badgeRequest.coworker',
            'badgeRequest.activityRequest.client',
        ]);

        // Si l'utilisateur n'est pas admin, filtrer par son client (badges liés ou standalone)
        if (! auth()->user()->isAdmin()) {
            $query->where(function ($q) {
                $q->whereHas('badgeRequest.activityRequest', function ($s) {
                    $s->where('client_id', auth()->user()->client_id);
                })->orWhere('client_id', auth()->user()->client_id);
            });
        }

        // Si l'utilisateur a le rôle client, il ne voit que les badges associés à son compte coworker
        if (auth()->user()->isClient()) {
            $query->where(function ($q) {
                $q->whereHas('badgeRequest.coworker', function ($s) {
                    $s->where('user_id', auth()->user()->id);
                })->orWhereHas('coworker', function ($s) {
                    $s->where('user_id', auth()->user()->id);
                });
            });
        }

        return $query;
    }

    private function loadBadges()
    {
        return $this->baseQuery()->orderBy('created_at', 'desc')->paginate(10);
    }

    public function refreshBadges(): void
    {
        $this->search = '';
        $this->render();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function buildScoutQuery()
    {
        $query = $this->baseQuery();

        if (! empty($this->search)) {
            $searchTerm = $this->search;
            $query->where(function ($q) use ($searchTerm) {
                // Recherche via la relation directe coworker (badges standalone)
                $q->whereHas('coworker', function ($s) use ($searchTerm) {
                    $s->where('firstname', 'like', "%{$searchTerm}%")
                        ->orWhere('lastname', 'like', "%{$searchTerm}%");
                })
                // Recherche via badgeRequest.coworker (badges liés)
                    ->orWhereHas('badgeRequest.coworker', function ($s) use ($searchTerm) {
                        $s->where('firstname', 'like', "%{$searchTerm}%")
                            ->orWhere('lastname', 'like', "%{$searchTerm}%");
                    })
                // Recherche via la relation directe client (badges standalone)
                    ->orWhereHas('client', function ($s) use ($searchTerm) {
                        $s->where('company_name', 'like', "%{$searchTerm}%");
                    })
                // Recherche via badgeRequest.activityRequest.client (badges liés)
                    ->orWhereHas('badgeRequest.activityRequest.client', function ($s) use ($searchTerm) {
                        $s->where('company_name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Vérifie et met à jour le statut des badges expirés
     */
    private function checkAndUpdateExpiredBadges(): void
    {
        $today = now()->toDateString();

        $expiredBadges = Badge::where('status', 'active')
            ->where('expiry_date', '<', $today)
            ->get();

        foreach ($expiredBadges as $badge) {
            $badge->previous_status = $badge->status;
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
    public function returnBadge($badgeId): void
    {
        try {
            $badge = Badge::findOrFail($badgeId);

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
    public function notReturnedBadge($badgeId): void
    {
        try {
            $badge = Badge::findOrFail($badgeId);

            $badge->previous_status = $badge->status;
            $badge->status = 'not_returned';
            $badge->returned_at = null;
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
