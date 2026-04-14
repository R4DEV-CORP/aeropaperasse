<?php

namespace App\Livewire\BadgeManagement;

use Flux\Flux;
use Livewire\Component;

class EditBadgeNumberForm extends Component
{
    public $badge;

    public ?string $badgeNumber = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    protected array $rules = [
        'badgeNumber' => 'nullable|string|max:255',
    ];

    protected array $messages = [
        'badgeNumber.max' => 'Le numéro de badge ne peut pas dépasser 255 caractères.',
    ];

    public function mount($badge): void
    {
        $this->badge = $badge;
        $this->badgeNumber = $badge->badge_number;
    }

    public function editBadgeNumber(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $this->validate();

        try {
            $this->badge->badge_number = $this->badgeNumber ?: null;
            $this->badge->save();

            $this->successMessage = 'Le numéro de badge a été modifié avec succès.';
            $this->dispatch('badge-number-updated', $this->badge->id);
            $this->dispatch('close-modal', name: 'edit-badge-number-'.$this->badge->id);

        } catch (\Exception $e) {
            \Log::error("Erreur lors de la modification du numéro du badge {$this->badge->id}: ".$e->getMessage());
            $this->errorMessage = 'Une erreur est survenue lors de la modification du numéro de badge.';
        }
    }

    public function closeModal(): void
    {
        $this->resetForm();
        Flux::modal('edit-badge-number-'.$this->badge->id)->close();
    }

    public function resetForm(): void
    {
        $this->reset(['errorMessage', 'successMessage', 'badgeNumber']);
    }

    public function render()
    {
        return view('livewire.badge-management.edit-badge-number-form');
    }
}
