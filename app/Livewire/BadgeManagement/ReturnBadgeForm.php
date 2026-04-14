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

    public function mount($badge): void
    {
        $this->badge = $badge;
    }

    public function returnBadge(): void
    {
        if (! $this->return_badge_document) {
            $this->addError('return_badge_document', 'Le document de restitution est requis.');

            return;
        }

        $client = $this->badge->getEffectiveClient();
        $service = new BadgeRequestDocumentService;

        if ($this->badge->badge_request_id) {
            $uploadedDocuments = $service->storeDocuments(
                ['return_badge_document' => $this->return_badge_document],
                $client,
                $this->badge->badge_request_id
            );
            $returnDocumentPath = $uploadedDocuments['return_badge_document'];
        } else {
            $returnDocumentPath = $service->storeBadgeReturnDocument(
                $this->return_badge_document,
                $client,
                $this->badge->id
            );
        }

        $this->badge->update([
            'status' => 'returned',
            'returned_at' => now(),
            'return_document' => $returnDocumentPath,
        ]);

        $this->closeModal();

        $email = $this->badge->getRecipientEmail();
        if ($email) {
            Mail::to($email)->send(new BadgeReturned($this->badge, $returnDocumentPath));
        }
    }

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
