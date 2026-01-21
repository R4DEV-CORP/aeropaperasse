<?php

namespace App\Livewire\BadgeRequests;

use App\Actions\BadgeRequest\DeliverBadgeRequestAction;
use App\Mail\Badge\ApprovedByAdp;
use App\Mail\Badge\ApprovedByRem;
use App\Mail\Badge\InProduction;
use App\Mail\Badge\ReadyForPickup;
use App\Mail\Badge\RejectedByAdp;
use App\Mail\Badge\RejectedByRem;
use App\Models\Badge;
use App\Models\BadgeRequest;
use App\Services\BadgeMailService;
use App\Services\BadgeRequestDocumentService;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithoutUrlPagination, WithPagination;

    public string $search = '';

    public ?string $selectedAirport = null;

    public ?string $selectedStatus = null;

    public $badgeCount = 0;

    public $client;

    public string $rejectReason = '';

    public $deliveryPhoto;

    public ?int $badgeRequestIdForDelivery = null;

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

    /**
     * Réinitialiser la pagination lors d'un changement de filtre
     */
    public function updatedSelectedAirport()
    {
        $this->resetPage('page');
    }

    /**
     * Réinitialiser la pagination lors d'un changement de filtre
     */
    public function updatedSelectedStatus()
    {
        $this->resetPage('page');
    }

    /**
     * Filtrer par statut (appelé depuis les cartes)
     */
    public function filterByStatus(?string $status): void
    {
        $this->selectedStatus = $status === $this->selectedStatus ? null : $status;
        $this->resetPage('page');
    }

    /**
     * Réinitialiser tous les filtres
     */
    public function resetFilters(): void
    {
        $this->selectedAirport = null;
        $this->selectedStatus = null;
        $this->search = '';
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
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
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

        // Filtrer par aéroport
        if ($this->selectedAirport) {
            $query->whereHas('activityRequest', function ($q) {
                $q->where('airport', $this->selectedAirport);
            });
        }

        // Filtrer par statut
        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
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

                // Filtrer par aéroport
                if ($this->selectedAirport) {
                    $query->whereHas('activityRequest', function ($q) {
                        $q->where('airport', $this->selectedAirport);
                    });
                }

                // Filtrer par statut
                if ($this->selectedStatus) {
                    $query->where('badge_requests.status', $this->selectedStatus);
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
     * Écoute l'événement 'badge-request-reopened' et recharge la liste des demandes de badge
     */
    #[On('badge-request-reopened')]
    public function refreshBadgeRequestsAfterReopen()
    {
        // Force le re-rendu du composant pour mettre à jour la liste
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
     * Marquer un badge comme remis (admin seulement)
     */
    public function deliver(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            session()->flash('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');

            return;
        }

        // Valider la photo
        $this->validate([
            'deliveryPhoto' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'deliveryPhoto.required' => 'La photo du badge remis est obligatoire',
            'deliveryPhoto.image' => 'Le fichier doit être une image',
            'deliveryPhoto.mimes' => 'La photo doit être au format JPEG, PNG ou JPG',
            'deliveryPhoto.max' => 'La photo ne doit pas dépasser 5MB',
        ]);

        try {
            $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);

            // Vérifier les permissions avec la policy
            if (! auth()->user()->can('deliver', $badgeRequest)) {
                session()->flash('error', 'Vous n\'êtes pas autorisé à marquer ce badge comme remis.');

                return;
            }

            // Récupérer le client
            $client = $badgeRequest->activityRequest->client;

            // Exécuter l'action
            $action = app(DeliverBadgeRequestAction::class);
            $result = $action->execute($badgeRequest, $this->deliveryPhoto, $client);

            if ($result->isSuccessful()) {
                // Envoyer la notification email
                $badgeMailService = app(BadgeMailService::class);
                $badgeMailService->sendBadgeRequestStatusMail($result->getBadgeRequest(), 'ready_for_delivery');

                session()->flash('message', 'Badge marqué comme remis avec succès.');
                $this->reset('deliveryPhoto', 'badgeRequestIdForDelivery');
            } else {
                session()->flash('error', $result->getMessage());
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la remise du badge : '.$e->getMessage());
        }
    }

    /**
     * Ouvrir la modale de remise
     */
    public function openDeliverModal(int $badgeRequestId)
    {
        $this->badgeRequestIdForDelivery = $badgeRequestId;
        $this->deliveryPhoto = null;
    }

    /**
     * Fermer la modale de remise
     */
    public function closeDeliverModal()
    {
        $this->reset('deliveryPhoto', 'badgeRequestIdForDelivery');
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

    /**
     * Rouvrir une demande de badge refusée (super admin seulement)
     */
    public function reopenRequest(int $badgeRequestId)
    {
        // Vérifier que l'utilisateur est un super administrateur
        if (! auth()->user()->isSAdmin()) {
            session()->flash('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');

            return;
        }

        try {
            $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);

            // Vérifier que la demande est dans un statut refusé
            if (! in_array($badgeRequest->status, ['rejected_rem', 'rejected_adp'])) {
                session()->flash('error', 'Cette demande ne peut pas être rouverte. Seules les demandes refusées peuvent être rouvertes.');

                return;
            }

            // Sauvegarder le statut précédent si nécessaire (pour l'historique)
            $badgeRequest->previous_status = $badgeRequest->status;

            // Changer le statut vers draft
            $badgeRequest->update([
                'status' => 'draft',
                'draft_at' => now(),
            ]);

            // Afficher un message de succès
            session()->flash('message', 'Demande rouverte avec succès. Elle est maintenant en statut brouillon et peut être modifiée.');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la réouverture de la demande : '.$e->getMessage());
        }
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
