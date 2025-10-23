<?php

namespace App\Livewire\Coworkers;

use App\Models\Coworker;
use App\Validators\CoworkerValidator;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EditCoworkerForm extends Component
{
    public $coworkerId;

    public $coworker;

    public $user;

    public $client;

    // Messages
    public $errorMessage;

    public $successMessage;

    // Données du formulaire
    public $firstname;

    public $lastname;

    public $email;

    public $phone;

    // Données utilisateur (si existe)
    public $has_user_account = false;

    public $can_access_formation = false;

    public $role = 'client';

    // État du formulaire
    public $isSubmitting = false;

    public function mount($coworkerId)
    {
        $this->coworkerId = $coworkerId;
        $this->user = auth()->user();

        // Charger le collaborateur avec ses relations
        $this->coworker = Coworker::with(['user', 'client'])->findOrFail($coworkerId);
        $this->client = $this->coworker->client;

        // Pré-remplir le formulaire
        $this->loadCoworkerData();

        // Réinitialiser les messages
        $this->clearMessages();
    }

    public function loadCoworkerData()
    {
        $this->firstname = $this->coworker->firstname;
        $this->lastname = $this->coworker->lastname;
        $this->email = $this->coworker->email;
        $this->phone = $this->coworker->phone;

        // Si le collaborateur a un compte utilisateur
        if ($this->coworker->user_id) {
            $this->has_user_account = true;
            $this->can_access_formation = $this->coworker->user->can_access_formation;
            $this->role = $this->coworker->user->role;
        }
    }

    public function submit()
    {
        $this->isSubmitting = true;
        $this->clearMessages();

        try {
            // Validation avec CoworkerValidator
            $validationData = $this->getValidationData();
            $validator = CoworkerValidator::validateUpdate($validationData, $this->coworkerId);

            if ($validator->fails()) {
                // Afficher la première erreur
                $this->errorMessage = $validator->errors()->first();

                return;
            }

            // Mettre à jour le collaborateur
            $this->coworker->update([
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'email' => $this->email,
                'phone' => $this->phone,
            ]);

            // Mettre à jour l'utilisateur si il existe
            if ($this->has_user_account && $this->coworker->user) {
                $this->coworker->user->update([
                    'can_access_formation' => $this->can_access_formation,
                    'role' => $this->role,
                    'name' => $this->firstname.' '.$this->lastname,
                ]);
            }

            $this->successMessage = 'Le collaborateur a été modifié avec succès.';

            // Fermer la modal
            $this->dispatch('close-modal', name: 'edit-coworker-'.$this->coworkerId);

            // Émettre un événement pour rafraîchir la liste
            $this->dispatch('coworker-updated');

        } catch (\Exception $e) {
            Log::error('Erreur dans EditCoworkerForm::submit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->user->id,
                'coworker_id' => $this->coworkerId,
            ]);

            $this->errorMessage = 'Une erreur est survenue lors de la modification du collaborateur.';
        } finally {
            $this->isSubmitting = false;
        }
    }

    /**
     * Prépare les données pour la validation
     */
    private function getValidationData(): array
    {
        return [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'can_access_formation' => $this->can_access_formation,
            'role' => $this->role,
        ];
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
        return view('livewire.coworkers.edit-coworker-form');
    }
}
