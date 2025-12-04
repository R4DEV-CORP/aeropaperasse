<?php

namespace App\Livewire\BadgeRequests;

use App\Models\Badge;
use App\Models\BadgeRequest;
use App\Services\BadgeRequestDocumentService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Mail;
use App\Mail\Badge\ApprovedByRem;
use App\Mail\Badge\RejectedByRem;
use App\Mail\Badge\ApprovedByAdp;
use App\Mail\Badge\RejectedByAdp;
use App\Mail\Badge\InProduction;
use App\Mail\Badge\ReadyForPickup;

class Index extends Component
{
    use WithoutUrlPagination, WithPagination;

    public string $search = '';

    public $badgeCount = 0;

    public $client;

    public string $rejectReason = '';

    public function mount()
    {
        // Compter les badges via les relations : Badge -> BadgeRequest -> ActivityRequest -> client_id
        $this->badgeCount = Badge::where('status', '!=', 'returned')
            ->whereHas('badgeRequest.activityRequest', function ($query) {
                $query->where('client_id', auth()->user()->client_id);
            })
            ->count();

        $this->client = auth()->user()->client;
    }

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

        // Si l'utilisateur à le role client, il ne peut voir que les badges request associés à une activityRequest qu'il a créé
        if (auth()->user()->isClient()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('created_by', auth()->user()->id);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page');
    }

    private function loadDraftBadgeRequests()
    {
        $query = BadgeRequest::with('activityRequest')
            ->where('status', 'draft');

        // / Si l'utilisateur n'est pas admin, filtrer par client_id de l'ActivityRequest liée
        if (! auth()->user()->isAdmin()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('client_id', auth()->user()->client_id);
            });
        }

        // Si l'utilisateur à le role client, il ne peut voir que les badges request associés à une activityRequest qu'il a créé
        if (auth()->user()->isClient()) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('created_by', auth()->user()->id);
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

                // / Si l'utilisateur n'est pas admin, filtrer par client_id de l'ActivityRequest liée
                if (! auth()->user()->isAdmin()) {
                    $query->whereHas('activityRequest', function ($q) {
                        $q->where('client_id', auth()->user()->client_id);
                    });
                }

                // Si l'utilisateur à le role client, il ne peut voir que les badges request associés à une activityRequest qu'il a créé
                if (auth()->user()->isClient()) {
                    $query->whereHas('activityRequest', function ($q) {
                        $q->where('created_by', auth()->user()->id);
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

    /**
     * Approuver une demande de badge par REM (admin seulement)
     */
    public function approveRem(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);
        $badgeRequest->update([
            'status' => 'pending_adp',
            'pending_adp_at' => now(),
        ]);

        // Envoyer une notification par email
        $email = $badgeRequest->creator->email;
        if ($badgeRequest->activityRequest->client->notification_email) {
            $email = $badgeRequest->activityRequest->client->notification_email;
        }

        Mail::to($email)->send(new ApprovedByRem($badgeRequest));

        // Afficher un message de succès
        session()->flash('message', 'Demande approuvée par REM et transmise à ADP.');
    }

    /**
     * Rejeter une demande de badge par REM (admin seulement)
     */
    public function rejectRem(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);
        $badgeRequest->update([
            'status' => 'rejected_rem',
            'rejected_rem_at' => now(),
            'reject_reason' => $this->rejectReason,
        ]);

        // Envoyer une notification par email
        $email = $badgeRequest->creator->email;
        if ($badgeRequest->activityRequest->client->notification_email) {
            $email = $badgeRequest->activityRequest->client->notification_email;
        }

        Mail::to($email)->send(new RejectedByRem($badgeRequest, $this->rejectReason));

        $this->reset('rejectReason');

        // Afficher un message de succès
        session()->flash('message', 'Demande rejetée par REM.');
    }

    /**
     * Retourner une demande en attente REM depuis pending_adp (admin seulement)
     */
    public function backToPendingRem(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);
        $badgeRequest->update([
            'status' => 'pending_rem',
            'pending_rem_at' => now(),
        ]);

        // Afficher un message de succès
        session()->flash('message', 'Demande retournée en attente REM.');
    }

    /**
     * Approuver une demande de badge par ADP (admin seulement)
     */
    public function approveAdp(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);
        $badgeRequest->update([
            'status' => 'approved_adp',
            'approved_adp_at' => now(),
        ]);

        // Envoyer une notification par email
        $email = $badgeRequest->creator->email;
        if ($badgeRequest->activityRequest->client->notification_email) {
            $email = $badgeRequest->activityRequest->client->notification_email;
        }

        Mail::to($email)->send(new ApprovedByAdp($badgeRequest));

        // Afficher un message de succès
        session()->flash('message', 'Demande approuvée par ADP.');
    }

    /**
     * Rejeter une demande de badge par ADP (admin seulement)
     */
    public function rejectAdp(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);
        $badgeRequest->update([
            'status' => 'rejected_adp',
            'rejected_adp_at' => now(),
            'reject_reason' => $this->rejectReason,
        ]);

        // Envoyer une notification par email
        $email = $badgeRequest->creator->email;
        if ($badgeRequest->activityRequest->client->notification_email) {
            $email = $badgeRequest->activityRequest->client->notification_email;
        }

        Mail::to($email)->send(new RejectedByAdp($badgeRequest, $this->rejectReason));

        $this->reset('rejectReason');

        // Afficher un message de succès
        session()->flash('message', 'Demande rejetée par ADP.');
    }

    /**
     * Retourner une demande en attente ADP depuis approved_adp (admin seulement)
     */
    public function backToPendingAdp(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);
        $badgeRequest->update([
            'status' => 'pending_adp',
            'pending_adp_at' => now(),
        ]);

        // Afficher un message de succès
        session()->flash('message', 'Demande retournée en attente ADP.');
    }

    /**
     * Passer une demande de badge en fabrication (admin seulement)
     */
    public function fabrication(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);
        $badgeRequest->update([
            'status' => 'pending_fabrication',
            'pending_fabrication_at' => now(),
        ]);

        // Envoyer une notification par email
        $email = $badgeRequest->creator->email;
        if ($badgeRequest->activityRequest->client->notification_email) {
            $email = $badgeRequest->activityRequest->client->notification_email;
        }

        Mail::to($email)->send(new InProduction($badgeRequest));

        // Afficher un message de succès
        session()->flash('message', 'Demande passée en fabrication.');
    }

    /**
     * Retourner une demande en approuvé ADP depuis pending_fabrication (admin seulement)
     */
    public function backToApprovedAdp(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);
        $badgeRequest->update([
            'status' => 'approved_adp',
            'approved_adp_at' => now(),
        ]);

        // Afficher un message de succès
        session()->flash('message', 'Demande retournée en approuvé ADP.');
    }

    /**
     * Passer une demande de badge à prêt pour remise (admin seulement)
     */
    public function toDelivery(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);
        $badgeRequest->update([
            'status' => 'ready_for_delivery',
            'ready_for_delivery_at' => now(),
        ]);

        // Envoyer une notification par email
        $email = $badgeRequest->creator->email;
        if ($badgeRequest->activityRequest->client->notification_email) {
            $email = $badgeRequest->activityRequest->client->notification_email;
        }

        Mail::to($email)->send(new ReadyForPickup($badgeRequest));

        // Afficher un message de succès
        session()->flash('message', 'Badge prêt à être remis.');
    }

    /**
     * Télécharger tous les documents d'une demande de badge dans un ZIP
     */
    public function downloadDocuments(int $badgeRequestId)
    {
        $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);

        $documentService = new BadgeRequestDocumentService;
        $zipPath = $documentService->createDocumentsZip($badgeRequest);

        if (! $zipPath) {
            session()->flash('error', 'Aucun document disponible pour cette demande.');

            return;
        }

        $zipFileName = 'demande-badge-'.$badgeRequest->id.'-'.now()->timestamp.'.zip';

        // Retourner le téléchargement du fichier ZIP
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
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
