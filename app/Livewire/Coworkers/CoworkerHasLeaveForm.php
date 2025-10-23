<?php

namespace App\Livewire\Coworkers;

use App\Models\Coworker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CoworkerHasLeaveForm extends Component
{
    public $coworkerId;

    public $coworker;

    public $user;

    // Messages
    public $errorMessage;

    public $successMessage;

    // Données du formulaire
    public $departure_date;

    // État du formulaire
    public $isSubmitting = false;

    public function mount($coworkerId)
    {
        $this->coworkerId = $coworkerId;
        $this->user = auth()->user();

        // Charger le collaborateur
        $this->coworker = Coworker::findOrFail($coworkerId);

        // Vérifier que le collaborateur n'a pas déjà quitté l'entreprise
        if ($this->coworker->has_leave) {
            $this->errorMessage = 'Ce collaborateur a déjà quitté l\'entreprise.';

            return;
        }

        // Initialiser la date de départ à aujourd'hui par défaut
        $this->departure_date = Carbon::today()->format('Y-m-d');

        // Réinitialiser les messages
        $this->clearMessages();
    }

    public function submit()
    {
        $this->isSubmitting = true;
        $this->clearMessages();

        try {
            // Validation
            $this->validate([
                'departure_date' => 'required|date|before_or_equal:today',
            ], [
                'departure_date.required' => 'La date de départ est requise.',
                'departure_date.date' => 'La date de départ doit être une date valide.',
                'departure_date.before_or_equal' => 'La date de départ ne peut pas être dans le futur.',
            ]);

            // Mettre à jour le collaborateur
            $this->coworker->update([
                'has_leave' => true,
                'departure_date' => $this->departure_date,
            ]);

            $this->successMessage = 'Le collaborateur '.$this->coworker->firstname.' '.$this->coworker->lastname.' a été marqué comme ayant quitté l\'entreprise le '.Carbon::parse($this->departure_date)->format('d/m/Y').'.';

            // Fermer la modal
            $this->dispatch('close-modal', name: 'has-leave-'.$this->coworkerId);

            // Émettre un événement pour rafraîchir la liste
            $this->dispatch('coworker-updated');

        } catch (\Exception $e) {
            Log::error('Erreur dans CoworkerHasLeaveForm::submit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->user->id,
                'coworker_id' => $this->coworkerId,
            ]);

            $this->errorMessage = 'Une erreur est survenue lors de la mise à jour du statut du collaborateur.';
        } finally {
            $this->isSubmitting = false;
        }
    }

    /**
     * Efface les messages d'erreur et de succès
     */
    public function clearMessages()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function render()
    {
        return view('livewire.coworkers.coworker-has-leave-form');
    }
}
