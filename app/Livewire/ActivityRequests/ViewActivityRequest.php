<?php

namespace App\Livewire\ActivityRequests;

use App\Models\ActivityComment;
use App\Services\ActivityRequestDocumentService;
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

        ActivityComment::create([
            'content' => $this->comment,
            'user_id' => auth()->id(),
            'activity_request_id' => $this->activityRequest->id,
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
        $documentService = new ActivityRequestDocumentService;
        $filePath = $documentService->getDocumentPath($this->activityRequest, $documentType);

        if (! $filePath) {
            session()->flash('error', 'Document non disponible.');

            return;
        }

        // Récupérer le nom original du fichier
        $filename = basename($filePath);

        return response()->download($filePath, $filename);
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

    public function render()
    {
        return view('livewire.activity-requests.view-activity-request');
    }
}
