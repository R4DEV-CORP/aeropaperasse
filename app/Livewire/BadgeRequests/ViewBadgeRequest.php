<?php

namespace App\Livewire\BadgeRequests;

use App\Models\BadgeComment;
use App\Services\BadgeRequestDocumentService;
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

        BadgeComment::create([
            'content' => $this->comment,
            'user_id' => auth()->id(),
            'badge_request_id' => $this->badgeRequest->id,
        ]);

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

    public function render()
    {
        return view('livewire.badge-requests.view-badge-request');
    }
}
