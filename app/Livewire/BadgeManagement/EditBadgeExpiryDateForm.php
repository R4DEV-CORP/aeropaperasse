<?php

namespace App\Livewire\BadgeManagement;

use Flux\Flux;
use Livewire\Component;

class EditBadgeExpiryDateForm extends Component
{
    public $badge;

    public $errorMessage;

    public $successMessage;

    public $expiryDate;

    protected $rules = [
        'expiryDate' => 'required|date|after:today',
    ];

    protected $messages = [
        'expiryDate.required' => 'La date d\'expiration est obligatoire.',
        'expiryDate.date' => 'La date d\'expiration doit être une date valide.',
        'expiryDate.after' => 'La date d\'expiration doit être postérieure à aujourd\'hui.',
    ];

    public function mount($badge)
    {
        $this->badge = $badge;
        $this->expiryDate = $badge->expiry_date ? $badge->expiry_date->format('Y-m-d') : '';
    }

    public function editExpiryDate()
    {
        // Réinitialiser les messages
        $this->errorMessage = null;
        $this->successMessage = null;

        // Valider les données
        $this->validate();

        try {
            // Mettre à jour la date d'expiration
            $this->badge->expiry_date = $this->expiryDate;
            $this->badge->save();

            // Message de succès
            $this->successMessage = 'La date d\'expiration a été modifiée avec succès.';

            // Émettre un événement pour actualiser la liste des badges
            $this->dispatch('badge-expiry-date-updated', $this->badge->id);

            // Fermer la modal après un délai
            $this->dispatch('close-modal', name: 'edit-badge-expiry-date-'.$this->badge->id);

        } catch (\Exception $e) {
            \Log::error("Erreur lors de la modification de la date d'expiration du badge {$this->badge->id}: ".$e->getMessage());
            $this->errorMessage = 'Une erreur est survenue lors de la modification de la date d\'expiration.';
        }
    }

    public function closeModal(): void
    {
        $this->resetForm();
        Flux::modal('edit-badge-expiry-date-'.$this->badge->id)->close();
    }

    public function resetForm(): void
    {
        $this->reset([
            'errorMessage',
            'successMessage',
            'expiryDate',
        ]);
    }

    public function render()
    {
        return view('livewire.badge-management.edit-badge-expiry-date-form');
    }
}
