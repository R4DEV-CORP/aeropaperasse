<?php

namespace App\Livewire\Coworkers;

use App\Actions\Coworker\CreateCoworkerAction;
use App\DataTransferObjects\CreateCoworkerData;
use App\Mail\UserCreated;
use App\Models\Client;
use App\Models\Training;
use App\Validators\CoworkerValidator;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class CreateCoworkerForm extends Component
{
    public $user;

    public $client;

    public $allClients;

    public $selected_client_id;

    // Messages
    public $errorMessage;

    public $successMessage;

    // Données du formulaire
    public $create_user = false;

    public $firstname;

    public $lastname;

    public $email;

    public $phone;

    public $can_access_formation = false;

    public $has_leave = false;

    public $departure_date;

    public $password;

    public $password_confirmation;

    public $role = 'client';

    // Formations
    public $trainings = [];

    public $selected_trainings = []; // Format: ['training_id' => ['start_date' => '', 'validity_years' => '']]

    // État du formulaire
    public $isSubmitting = false;

    public function mount()
    {
        $this->user = auth()->user();

        if ($this->user->isAdmin()) {
            $this->loadClients();
        } else {
            $this->client = $this->user->client;
            $this->selected_client_id = $this->client->id;
        }

        // Charger les formations disponibles
        $this->trainings = Training::all();

        // Réinitialiser les messages
        $this->clearMessages();
    }

    public function loadClients()
    {
        $this->allClients = Client::orderBy('company_name')->get();
        $this->selected_client_id = null;
    }

    public function updatedCreateUser()
    {
        // Réinitialiser les champs de mot de passe quand on change l'option
        if (! $this->create_user) {
            $this->password = '';
            $this->password_confirmation = '';
        }
    }

    public function updatedHasLeave()
    {
        // Réinitialiser la date de départ si on désactive le départ
        if (! $this->has_leave) {
            $this->departure_date = null;
        }
    }

    public function updatedSelectedTrainings($value, $key)
    {
        // Si on désélectionne une formation, nettoyer ses données
        // La clé sera au format "1.selected" ou "1.start_date" etc.
        if (str_contains($key, '.selected') && ! $value) {
            $parts = explode('.', $key);
            $trainingId = $parts[0];
            if (isset($this->selected_trainings[$trainingId])) {
                unset($this->selected_trainings[$trainingId]);
            }
        }
    }

    public function submit()
    {
        $this->isSubmitting = true;
        $this->clearMessages();

        try {
            // Validation avec CoworkerValidator uniquement
            $validationData = $this->getValidationData();
            $validator = CoworkerValidator::validateComplete($validationData);

            if ($validator->fails()) {
                // Afficher la première erreur
                $this->errorMessage = $validator->errors()->first();

                return;
            }

            // Créer le DTO
            $data = CreateCoworkerData::fromArray($validationData, $this->user->id);

            // Exécuter l'action
            $action = new CreateCoworkerAction;
            $result = $action->execute($data);

            if ($result->isSuccessful()) {
                // Attacher les formations si sélectionnées
                if (! empty($this->selected_trainings) && $result->coworker) {
                    $this->attachTrainings($result->coworker->id);
                }

                if ($this->create_user) {
                    Mail::to($this->email)->send(new UserCreated($result->user, $this->password));
                }
                $this->successMessage = $result->getMessage();
                $this->resetForm();

                // Fermer la modal
                $this->dispatch('close-modal', name: 'new-coworker');

                // Émettre un événement pour rafraîchir la liste
                $this->dispatch('coworker-created', $result->getData());
            } else {
                $this->errorMessage = $result->getMessage();
            }

        } catch (\Exception $e) {
            Log::error('Erreur dans CreateCoworkerForm::submit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->user->id,
            ]);

            $this->errorMessage = 'Une erreur est survenue lors de la création du collaborateur.';
        } finally {
            $this->isSubmitting = false;
        }
    }

    /**
     * Prépare les données pour la validation
     */
    private function getValidationData(): array
    {
        $data = [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'client_id' => $this->selected_client_id,
            'can_access_formation' => $this->can_access_formation,
            'has_leave' => $this->has_leave,
            'departure_date' => $this->departure_date,
            'create_user' => $this->create_user,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'role' => $this->role,
        ];

        // Si create_user est false, nettoyer les champs password
        if (! $this->create_user) {
            $data['password'] = null;
            $data['password_confirmation'] = null;
        }

        return $data;
    }

    /**
     * Attache les formations au collaborateur créé
     */
    private function attachTrainings(int $coworkerId): void
    {
        try {
            DB::beginTransaction();

            foreach ($this->selected_trainings as $trainingId => $trainingData) {
                // Vérifier que la formation est sélectionnée et a les données requises
                if (empty($trainingData['selected']) ||
                    empty($trainingData['start_date']) ||
                    empty($trainingData['validity_years'])) {
                    continue;
                }

                // Vérifier si l'association existe déjà
                $existingAssociation = DB::table('coworker_trainings')
                    ->where('coworker_id', $coworkerId)
                    ->where('training_id', $trainingId)
                    ->first();

                if ($existingAssociation) {
                    continue;
                }

                // Calculer la date d'expiration
                $startDate = Carbon::parse($trainingData['start_date']);
                $expiresAt = $startDate->copy()->addYears($trainingData['validity_years']);

                // Créer l'enregistrement dans coworker_trainings
                DB::table('coworker_trainings')->insert([
                    'coworker_id' => $coworkerId,
                    'training_id' => $trainingId,
                    'started_at' => $startDate,
                    'expires_at' => $expiresAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'attribution des formations', [
                'error' => $e->getMessage(),
                'coworker_id' => $coworkerId,
                'trainings' => $this->selected_trainings,
            ]);
        }
    }

    /**
     * Réinitialise le formulaire
     */
    public function resetForm()
    {
        $this->firstname = '';
        $this->lastname = '';
        $this->email = '';
        $this->phone = '';
        $this->can_access_formation = false;
        $this->has_leave = false;
        $this->departure_date = '';
        $this->create_user = false;
        $this->password = '';
        $this->password_confirmation = '';
        $this->role = 'client';
        $this->selected_trainings = [];

        // Réinitialiser les erreurs de validation
        $this->resetErrorBag();
    }

    /**
     * Efface les messages d'erreur et de succès
     */
    public function clearMessages()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function closeModal()
    {
        $this->resetForm();
        Flux::modal('new-coworker')->close();
    }

    public function render()
    {
        return view('livewire.coworkers.create-coworker-form');
    }
}
