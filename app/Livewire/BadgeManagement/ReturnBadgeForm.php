<?php

namespace App\Livewire\BadgeManagement;

use App\Mail\BadgeReturned;
use App\Services\BadgeRequestDocumentService;
use Flux\Flux;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReturnBadgeForm extends Component
{
    use WithFileUploads;

    public $badge;

    public $return_badge_document;

    public function mount($badge)
    {
        $this->badge = $badge;
    }

    public function returnBadge()
    {
        if (! $this->return_badge_document) {
            $this->addError('return_badge_document', 'Le document de restitution est requis.');

            return;
        }

        $badgeRequestDocumentService = new BadgeRequestDocumentService;
        $uploadedDocument = $badgeRequestDocumentService->storeDocuments([
            'return_badge_document' => $this->return_badge_document,
        ], $this->badge->badgeRequest->activityRequest->client, $this->badge->badgeRequest->id);

        $this->badge->update([
            'status' => 'returned',
            'returned_at' => now(),
            'return_document' => $this->return_badge_document,
        ]);
        $this->closeModal();

        // Envoyer une notification par email
        $email = $this->badge->badgeRequest->creator->email;
        if ($this->badge->badgeRequest->activityRequest->client->notification_email) {
            $email = $this->badge->badgeRequest->activityRequest->client->notification_email;
        }

        Mail::to($email)->send(new BadgeReturned($this->badge, $uploadedDocument['return_badge_document']));
    }

    /**
     * Fermer la modal
     */
    public function closeModal(): void
    {
        $this->reset(['return_badge_document']);
        Flux::modal('return-badge-'.$this->badge->id)->close();
    }

    public function render()
    {
        return view('livewire.badge-management.return-badge-form');
    }
}
