<?php

namespace App\Livewire\ActivityRequests;

use App\Mail\ActivityCommentCreated;
use App\Models\ActivityComment;
use App\Services\ActivityRequestDocumentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ViewActivityRequest extends Component
{
    public $activityRequest;

    public $comments;

    public $comment = '';

    public function mount()
    {
        $this->loadComments();
    }

    public function loadComments()
    {
        $this->comments = ActivityComment::where('activity_request_id', $this->activityRequest->id)
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

        $comment = ActivityComment::create([
            'content' => $this->comment,
            'user_id' => auth()->id(),
            'activity_request_id' => $this->activityRequest->id,
        ]);

        // Recharger la demande pour avoir les relations à jour
        $this->activityRequest->refresh();
        $this->activityRequest->load('creator');

        // Envoyer un email au créateur de la demande si ce n'est pas lui qui a commenté
        $this->sendCommentNotification($comment);

        $this->comment = '';
        $this->loadComments();

        session()->flash('message', 'Commentaire ajouté avec succès.');
    }

    /**
     * Télécharger un document spécifique
     * Pour les principals, on peut télécharger un fichier spécifique par son ID
     */
    public function downloadDocument(string $documentType, ?int $attachmentId = null)
    {
        // Charger les attachments si pas déjà chargés
        if (! $this->activityRequest->relationLoaded('attachments')) {
            $this->activityRequest->load('attachments');
        }

        $attachment = null;

        // Si un ID est fourni (pour les principals multiples), utiliser cet ID
        if ($attachmentId) {
            $attachment = $this->activityRequest->attachments()->find($attachmentId);
        } else {
            // Sinon, utiliser le service pour récupérer le premier document du type
            $documentService = new ActivityRequestDocumentService;
            $attachment = $documentService->getAttachmentByType($this->activityRequest, $documentType);
        }

        if (! $attachment) {
            session()->flash('error', 'Document non disponible.');

            return;
        }

        $disk = Storage::disk('public');

        // Vérifier si le fichier existe
        if (! $disk->exists($attachment->path)) {
            session()->flash('error', 'Le fichier n\'existe pas.');

            return;
        }

        // Obtenir le chemin absolu du fichier
        $absolutePath = $disk->path($attachment->path);

        // Récupérer le nom du fichier
        $filename = $attachment->name.'.'.pathinfo($attachment->path, PATHINFO_EXTENSION);

        return response()->download($absolutePath, $filename);
    }

    /**
     * Ouvrir un document dans le navigateur
     * Pour les principals, on peut ouvrir un fichier spécifique par son ID
     */
    public function viewDocument(string $documentType, ?int $attachmentId = null)
    {
        // Charger les attachments si pas déjà chargés
        if (! $this->activityRequest->relationLoaded('attachments')) {
            $this->activityRequest->load('attachments');
        }

        $attachment = null;

        // Si un ID est fourni (pour les principals multiples), utiliser cet ID
        if ($attachmentId) {
            $attachment = $this->activityRequest->attachments()->find($attachmentId);
        } else {
            // Sinon, utiliser le service pour récupérer le premier document du type
            $documentService = new ActivityRequestDocumentService;
            $attachment = $documentService->getAttachmentByType($this->activityRequest, $documentType);
        }

        if (! $attachment) {
            session()->flash('error', 'Document non disponible.');

            return;
        }

        $disk = Storage::disk('public');

        // Vérifier si le fichier existe
        if (! $disk->exists($attachment->path)) {
            session()->flash('error', 'Le fichier n\'existe pas.');

            return;
        }

        // Vérifier si le fichier peut être visualisé (PDF, PNG, JPEG)
        $extension = strtolower(pathinfo($attachment->path, PATHINFO_EXTENSION));
        if (! in_array($extension, ['pdf', 'png', 'jpg', 'jpeg'])) {
            session()->flash('error', 'Ce type de fichier ne peut pas être visualisé dans le navigateur.');

            return;
        }

        // Obtenir l'URL publique du fichier
        $url = $disk->url($attachment->path);

        // Retourner l'URL pour l'ouvrir dans un nouvel onglet via JavaScript
        $this->dispatch('open-document', url: $url);
    }

    /**
     * Vérifier si un document peut être visualisé dans le navigateur
     */
    public function canViewDocument(string $documentType, ?int $attachmentId = null): bool
    {
        // Charger les attachments si pas déjà chargés
        if (! $this->activityRequest->relationLoaded('attachments')) {
            $this->activityRequest->load('attachments');
        }

        $attachment = null;

        // Si un ID est fourni (pour les principals multiples), utiliser cet ID
        if ($attachmentId) {
            $attachment = $this->activityRequest->attachments()->find($attachmentId);
        } else {
            // Sinon, utiliser le service pour récupérer le premier document du type
            $documentService = new ActivityRequestDocumentService;
            $attachment = $documentService->getAttachmentByType($this->activityRequest, $documentType);
        }

        if (! $attachment) {
            return false;
        }

        $extension = strtolower(pathinfo($attachment->path, PATHINFO_EXTENSION));

        return in_array($extension, ['pdf', 'png', 'jpg', 'jpeg']);
    }

    /**
     * Télécharger tous les documents dans un ZIP
     */
    public function downloadAllDocuments()
    {
        $documentService = new ActivityRequestDocumentService;
        $zipPath = $documentService->createDocumentsZip($this->activityRequest);

        if (! $zipPath) {
            session()->flash('error', 'Aucun document disponible pour cette demande.');

            return;
        }

        $zipFileName = 'demande-activite-'.$this->activityRequest->id.'-'.now()->timestamp.'.zip';

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Envoie une notification par email au créateur de la demande d'activité
     * si ce n'est pas lui qui a ajouté le commentaire
     */
    private function sendCommentNotification(ActivityComment $comment): void
    {
        try {
            $currentUserId = auth()->id();
            $creator = $this->activityRequest->creator;

            // Ne pas envoyer d'email si :
            // - Le créateur n'existe pas
            // - Le créateur est la même personne que l'auteur du commentaire
            // - Le créateur n'a pas d'email
            if (! $creator || $creator->id === $currentUserId || ! $creator->email) {
                return;
            }

            Mail::to($creator->email)->send(new ActivityCommentCreated($this->activityRequest, $comment));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email de notification de commentaire:', [
                'activity_request_id' => $this->activityRequest->id,
                'comment_id' => $comment->id,
                'error' => $e->getMessage(),
            ]);
            // Ne pas faire échouer la création du commentaire si l'email ne fonctionne pas
        }
    }

    public function render()
    {
        // Charger les attachments avec eager loading pour éviter les N+1
        if (! $this->activityRequest->relationLoaded('attachments')) {
            $this->activityRequest->load('attachments');
        }

        return view('livewire.activity-requests.view-activity-request');
    }
}
