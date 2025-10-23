<?php

namespace App\Livewire\Coworkers;

use Livewire\Component;
use App\Models\Coworker;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class NewPasswordForm extends Component
{
    public $coworkerId;
    public $coworker;
    public $user;
    public $userAccount;

    // Messages
    public $errorMessage;
    public $successMessage;

    // Données du formulaire
    public $password;
    public $password_confirmation;

    // État du formulaire
    public $isSubmitting = false;

    public function mount($coworkerId)
    {
        $this->coworkerId = $coworkerId;
        $this->user = auth()->user();
        
        // Charger le collaborateur avec ses relations
        $this->coworker = Coworker::with(['user'])->findOrFail($coworkerId);
        
        // Vérifier que le collaborateur a un compte utilisateur
        if (!$this->coworker->user_id) {
            $this->errorMessage = 'Ce collaborateur n\'a pas de compte utilisateur.';
            return;
        }
        
        $this->userAccount = $this->coworker->user;
        
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
                'password' => ['required', 'confirmed', Password::defaults()],
                'password_confirmation' => 'required',
            ], [
                'password.required' => 'Le nouveau mot de passe est requis.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
                'password_confirmation.required' => 'La confirmation du mot de passe est requise.',
            ]);

            // Mettre à jour le mot de passe et définir is_new à true
            $this->userAccount->update([
                'password' => Hash::make($this->password),
                'is_new' => true,
            ]);

            $this->successMessage = 'Le mot de passe a été réinitialisé avec succès pour ' . $this->coworker->firstname . ' ' . $this->coworker->lastname . '.';
            
            // Fermer la modal
            $this->dispatch('close-modal', name: 'reset-password-'.$this->coworkerId);
            
            // Émettre un événement pour rafraîchir la liste
            $this->dispatch('coworker-updated');

        } catch (\Exception $e) {
            Log::error('Erreur dans NewPasswordForm::submit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->user->id,
                'coworker_id' => $this->coworkerId,
                'target_user_id' => $this->userAccount->id,
            ]);
            
            $this->errorMessage = 'Une erreur est survenue lors de la réinitialisation du mot de passe.';
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
        return view('livewire.coworkers.new-password-form');
    }
}