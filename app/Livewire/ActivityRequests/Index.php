<?php

namespace App\Livewire\ActivityRequests;

use App\Mail\ActivityRequestStatusUpdated;
use App\Models\ActivityRequest;
use App\Services\ActivityRequestDocumentService;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

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
        $query = ActivityRequest::query();

        // Si l'utilisateur n'est pas admin, filtrer par client_id
        if (! auth()->user()->isAdmin()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        return [
            'total' => $query->where('status', '!=', 'draft')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];
    }

    private function loadActivityRequests()
    {
        $query = ActivityRequest::with('client')
            ->where('status', '!=', 'draft');

        // Si l'utilisateur n'est pas admin, filtrer par client_id
        if (! auth()->user()->isAdmin()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page');
    }

    private function loadDraftActivityRequests()
    {
        $query = ActivityRequest::with('client')
            ->where('status', 'draft');

        // Si l'utilisateur n'est pas admin, filtrer par client_id
        if (! auth()->user()->isAdmin()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10, ['*'], 'draftPage');
    }

    private function buildScoutQuery()
    {
        return ActivityRequest::search($this->search)
            ->query(function ($query) {
                $query->join('clients', 'activity_requests.client_id', 'clients.id')
                    ->select('activity_requests.*', 'clients.company_name as company_name', 'clients.trade_name as trade_name')
                    ->where('activity_requests.status', '!=', 'draft');

                // Si l'utilisateur n'est pas admin, filtrer par client_id
                if (! auth()->user()->isAdmin()) {
                    $query->where('activity_requests.client_id', auth()->user()->client_id);
                }
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

    /**
     * Approuver une demande d'activité (admin seulement)
     */
    public function approve(int $activityRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $activityRequest = ActivityRequest::findOrFail($activityRequestId);
        $activityRequest->update([
            'previous_status' => $activityRequest->status,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Envoyer une notification par email
        $email = $activityRequest->creator->email;
        if ($activityRequest->client->notification_email) {
            $email = $activityRequest->client->notification_email;
        }

        Mail::to($email)
            ->send(new ActivityRequestStatusUpdated($activityRequest, $activityRequest->client));

        // Afficher un message de succès
        session()->flash('message', 'Demande approuvée avec succès.');
    }

    /**
     * Rejeter une demande d'activité (admin seulement)
     */
    public function reject(int $activityRequestId)
    {
        // Vérifier que l'utilisateur est admin
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $activityRequest = ActivityRequest::findOrFail($activityRequestId);
        $activityRequest->update([
            'previous_status' => $activityRequest->status,
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        // Envoyer une notification par email
        $email = $activityRequest->creator->email;
        if ($activityRequest->client->notification_email) {
            $email = $activityRequest->client->notification_email;
        }

        Mail::to($email)
            ->send(new ActivityRequestStatusUpdated($activityRequest, $activityRequest->client));

        // Afficher un message de succès
        session()->flash('message', 'Demande rejetée avec succès.');
    }

    /**
     * Télécharger tous les documents d'une demande d'activité dans un ZIP
     */
    public function downloadDocuments(int $activityRequestId)
    {
        $activityRequest = ActivityRequest::findOrFail($activityRequestId);

        $documentService = new ActivityRequestDocumentService;
        $zipPath = $documentService->createDocumentsZip($activityRequest);

        if (! $zipPath) {
            session()->flash('error', 'Aucun document disponible pour cette demande.');

            return;
        }

        $zipFileName = 'demande-activite-'.$activityRequest->id.'-'.now()->timestamp.'.zip';

        // Retourner le téléchargement du fichier ZIP
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function render()
    {
        if (! empty($this->search)) {
            $activityRequests = $this->buildScoutQuery()->paginate(10, 'page');
        } else {
            $activityRequests = $this->loadActivityRequests();
        }

        $draftActivityRequests = $this->loadDraftActivityRequests();
        $statistics = $this->getStatistics();

        return view('livewire.activity-requests.index', [
            'activityRequests' => $activityRequests,
            'draftActivityRequests' => $draftActivityRequests,
            'statistics' => $statistics,
        ]);
    }
}
