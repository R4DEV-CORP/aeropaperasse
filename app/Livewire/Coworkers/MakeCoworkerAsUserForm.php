<?php

namespace App\Livewire\Coworkers;

use App\Models\Coworker;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreated;

class MakeCoworkerAsUserForm extends Component
{
    public $coworkerId;

    public $coworker;

    public $user;

    // Messages
    public $errorMessage;

    public $successMessage;

    // Données du formulaire
    public $password;

    public $password_confirmation;

    public $can_access_formation = false;

    public $role = 'client';

    // État du formulaire
    public $isSubmitting = false;

    public function mount($coworkerId)
    {
        $this->coworkerId = $coworkerId;
        $this->user = auth()->user();

        // Charger le collaborateur avec ses relations
        $this->coworker = Coworker::with(['client'])->findOrFail($coworkerId);

        // Vérifier que le collaborateur n'a pas déjà un compte utilisateur
        if ($this->coworker->user_id) {
            $this->errorMessage = 'Ce collaborateur a déjà un compte utilisateur.';

            return;
        }

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
                'can_access_formation' => 'boolean',
                'role' => 'required|in:admin,sadmin,sclient,client',
            ], [
                'password.required' => 'Le mot de passe est requis.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
                'password_confirmation.required' => 'La confirmation du mot de passe est requise.',
                'role.required' => 'Le rôle est requis.',
                'role.in' => 'Le rôle sélectionné n\'est pas valide.',
            ]);

            // Créer l'utilisateur
            $newUser = User::create([
                'name' => $this->coworker->firstname.' '.$this->coworker->lastname,
                'email' => $this->coworker->email,
                'password' => Hash::make($this->password),
                'role' => $this->role,
                'client_id' => $this->coworker->client_id,
                'coworker_id' => $this->coworker->id,
                'can_access_formation' => $this->can_access_formation,
                'is_new' => true,
            ]);

            // Lier l'utilisateur au collaborateur
            $this->coworker->update([
                'user_id' => $newUser->id,
            ]);

            Mail::to($this->coworker->email)->send(new UserCreated($newUser, $this->password));

            $this->successMessage = 'Le compte utilisateur a été créé avec succès pour '.$this->coworker->firstname.' '.$this->coworker->lastname.'.';

            // Fermer la modal
            $this->dispatch('close-modal', name: 'make-user-'.$this->coworkerId);

            // Émettre un événement pour rafraîchir la liste
            $this->dispatch('coworker-updated');

        } catch (\Exception $e) {
            Log::error('Erreur dans MakeCoworkerAsUserForm::submit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->user->id,
                'coworker_id' => $this->coworkerId,
            ]);

            $this->errorMessage = 'Une erreur est survenue lors de la création du compte utilisateur.';
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
        return view('livewire.coworkers.make-coworker-as-user-form');
    }
}
