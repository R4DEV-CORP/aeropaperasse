<?php

namespace App\Livewire\BadgeRequests;

use App\Mail\BadgeCommentCreated;
use App\Models\BadgeComment;
use App\Models\BadgeRequest;
use App\Services\BadgeRequestDocumentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ViewBadgeRequest extends Component
{
    public $badgeRequest;

    public $comments;

    public $comment = '';

    public function mount()
    {
        $this->loadComments();
    }

    public function loadComments()
    {
        $this->comments = BadgeComment::where('badge_request_id', $this->badgeRequest->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function sendComment()
    {
        $this->validate([
            'comment' => 'required|string|min:1|max:1000',
        ], [
            'comment.required' => 'Le commentaire ne peut pas être vide.',
            'comment.min' => 'Le commentaire doit contenir au moins 1 caractère.',
            'comment.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
        ]);

        $comment = BadgeComment::create([
            'content' => $this->comment,
            'user_id' => auth()->id(),
            'badge_request_id' => $this->badgeRequest->id,
        ]);

        // Recharger la demande pour avoir les relations à jour
        $this->badgeRequest->refresh();
        $this->badgeRequest->load('creator');

        // Envoyer un email au créateur de la demande si ce n'est pas lui qui a commenté
        $this->sendCommentNotification($comment);

        $this->comment = '';
        $this->loadComments();

        session()->flash('message', 'Commentaire ajouté avec succès.');
    }

    /**
     * Télécharger un document spécifique
     */
    public function downloadDocument(string $documentType)
    {
        $documentService = new BadgeRequestDocumentService;
        $relativePath = $documentService->getDocumentPath($this->badgeRequest, $documentType);

        if (! $relativePath) {
            session()->flash('error', 'Document non disponible.');

            return;
        }

        $disk = Storage::disk('public');

        // Vérifier si le fichier existe
        if (! $disk->exists($relativePath)) {
            session()->flash('error', 'Le fichier n\'existe pas.');

            return;
        }

        // Obtenir le chemin absolu du fichier
        $absolutePath = $disk->path($relativePath);

        // Récupérer le nom original du fichier
        $filename = basename($relativePath);

        return response()->download($absolutePath, $filename);
    }

    /**
     * Ouvrir un document dans le navigateur
     */
    public function viewDocument(string $documentType)
    {
        $documentService = new BadgeRequestDocumentService;
        $relativePath = $documentService->getDocumentPath($this->badgeRequest, $documentType);

        if (! $relativePath) {
            session()->flash('error', 'Document non disponible.');

            return;
        }

        $disk = Storage::disk('public');

        // Vérifier si le fichier existe
        if (! $disk->exists($relativePath)) {
            session()->flash('error', 'Le fichier n\'existe pas.');

            return;
        }

        // Vérifier si le fichier peut être visualisé (PDF, PNG, JPEG)
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        $viewableExtensions = ['pdf', 'png', 'jpg', 'jpeg'];

        $canView = in_array($extension, $viewableExtensions);

        // Vérifier aussi le type MIME si l'extension ne suffit pas
        if (! $canView) {
            try {
                $mimeType = $disk->mimeType($relativePath);
                $viewableMimeTypes = [
                    'application/pdf',
                    'image/png',
                    'image/jpeg',
                    'image/jpg',
                ];
                $canView = in_array($mimeType, $viewableMimeTypes);
            } catch (\Exception $e) {
                // Si on ne peut pas déterminer le type MIME, on se base uniquement sur l'extension
            }
        }

        if (! $canView) {
            session()->flash('error', 'Ce type de fichier ne peut pas être visualisé dans le navigateur.');

            return;
        }

        // Obtenir l'URL publique du fichier
        $url = $disk->url($relativePath);

        // Retourner l'URL pour l'ouvrir dans un nouvel onglet via JavaScript
        $this->dispatch('open-document', url: $url);
    }

    /**
     * Vérifier si un document peut être visualisé dans le navigateur
     */
    public function canViewDocument(string $documentType): bool
    {
        $documentService = new BadgeRequestDocumentService;
        $relativePath = $documentService->getDocumentPath($this->badgeRequest, $documentType);

        if (! $relativePath) {
            return false;
        }

        $disk = Storage::disk('public');

        // Vérifier si le fichier existe
        if (! $disk->exists($relativePath)) {
            return false;
        }

        // Vérifier l'extension
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        // Types de fichiers visualisables dans le navigateur
        $viewableExtensions = ['pdf', 'png', 'jpg', 'jpeg'];

        if (in_array($extension, $viewableExtensions)) {
            return true;
        }

        // Vérifier aussi le type MIME si possible
        try {
            $mimeType = $disk->mimeType($relativePath);
            $viewableMimeTypes = [
                'application/pdf',
                'image/png',
                'image/jpeg',
                'image/jpg',
            ];

            return in_array($mimeType, $viewableMimeTypes);
        } catch (\Exception $e) {
            // Si on ne peut pas déterminer le type MIME, on se base uniquement sur l'extension
            return false;
        }
    }

    /**
     * Télécharger tous les documents dans un ZIP
     */
    public function downloadAllDocuments()
    {
        $documentService = new BadgeRequestDocumentService;
        $zipPath = $documentService->createDocumentsZip($this->badgeRequest);

        if (! $zipPath) {
            session()->flash('error', 'Aucun document disponible pour cette demande.');

            return;
        }

        $zipFileName = 'demande-badge-'.$this->badgeRequest->id.'-'.now()->timestamp.'.zip';

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Rouvrir une demande de badge refusée (super admin seulement)
     */
    public function reopenRequest()
    {
        // Vérifier que l'utilisateur est un super administrateur
        if (! auth()->user()->isSAdmin()) {
            session()->flash('error', 'Vous n\'êtes pas autorisé à effectuer cette action.');

            return;
        }

        try {
            // Recharger la demande pour avoir les dernières données
            $badgeRequest = BadgeRequest::findOrFail($this->badgeRequest->id);

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

            // Recharger la demande mise à jour
            $this->badgeRequest = $badgeRequest->fresh();

            // Dispatcher un événement pour rafraîchir la liste dans Index
            $this->dispatch('badge-request-reopened', badgeRequestId: $badgeRequest->id);

            // Afficher un message de succès
            session()->flash('message', 'Demande rouverte avec succès. Elle est maintenant en statut brouillon et peut être modifiée.');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la réouverture de la demande : '.$e->getMessage());
        }
    }

    /**
     * Envoie une notification par email au créateur de la demande de badge
     * si ce n'est pas lui qui a ajouté le commentaire
     */
    private function sendCommentNotification(BadgeComment $comment): void
    {
        try {
            $currentUserId = auth()->id();
            $creator = $this->badgeRequest->creator;

            // Ne pas envoyer d'email si :
            // - Le créateur n'existe pas
            // - Le créateur est la même personne que l'auteur du commentaire
            // - Le créateur n'a pas d'email
            if (! $creator || $creator->id === $currentUserId || ! $creator->email) {
                return;
            }

            Mail::to($creator->email)->send(new BadgeCommentCreated($this->badgeRequest, $comment));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email de notification de commentaire:', [
                'badge_request_id' => $this->badgeRequest->id,
                'comment_id' => $comment->id,
                'error' => $e->getMessage(),
            ]);
            // Ne pas faire échouer la création du commentaire si l'email ne fonctionne pas
        }
    }

    public function render()
    {
        return view('livewire.badge-requests.view-badge-request');
    }
}
