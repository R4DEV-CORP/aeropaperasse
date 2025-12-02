<?php

namespace App\Livewire\BadgeManagement;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\BadgeRequestDocumentService;
use Flux\Flux;

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
        if(!$this->return_badge_document) {
            $this->addError('return_badge_document', 'Le document de restitution est requis.');
            return;
        }

        $badgeRequestDocumentService = new BadgeRequestDocumentService();
        $badgeRequestDocumentService->storeDocuments([
            'return_badge_document' => $this->return_badge_document,
        ], $this->badge->badgeRequest->activityRequest->client, $this->badge->badgeRequest->id);

        $this->badge->update([
            'status' => 'returned',
            'returned_at' => now(),
            'return_document' => $this->return_badge_document,
        ]);
        $this->closeModal();
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
