<?php

namespace App\Livewire\Training;

use App\Models\Client;
use App\Models\Coworker;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Flux\Flux;

class AddCoworkerToTrainingForm extends Component
{
    public $errorMessage;

    public $successMessage;

    public $allClients;

    public $selected_client_id;

    public $coworkers = [];

    public $selected_coworker_id;

    public $user;

    public $trainings;

    public $selected_training_id = null;

    public $start_date;

    public $validity_years;

    public function mount()
    {
        $this->user = auth()->user();
        if ($this->user->isAdmin()) {
            $this->allClients = Client::all();
        } else {
            $this->selected_client_id = $this->user->client->id;
            $this->loadCoworkers();
        }
        $this->trainings = Training::all();
    }

    public function updatedSelectedClientId()
    {
        $this->selected_coworker_id = null;
        $this->loadCoworkers();
    }

    public function loadCoworkers()
    {
        if ($this->selected_client_id) {
            $this->coworkers = Coworker::where('client_id', $this->selected_client_id)
                ->where('has_leave', false)
                ->orderBy('firstname')
                ->orderBy('lastname')
                ->get();
        } else {
            $this->coworkers = [];
        }
    }

    public function submit()
    {
        // Validation des champs requis
        $this->validate([
            'selected_coworker_id' => 'required|exists:coworkers,id',
            'selected_training_id' => 'required|exists:trainings,id',
            'start_date' => 'required|date',
            'validity_years' => 'required|in:3,5',
        ], [
            'selected_coworker_id.required' => 'Veuillez sélectionner un collaborateur.',
            'selected_coworker_id.exists' => 'Le collaborateur sélectionné n\'existe pas.',
            'selected_training_id.required' => 'Veuillez sélectionner une formation.',
            'selected_training_id.exists' => 'La formation sélectionnée n\'existe pas.',
            'start_date.required' => 'Veuillez saisir une date de début.',
            'start_date.date' => 'La date de début doit être une date valide.',
            'validity_years.required' => 'Veuillez sélectionner une durée de validité.',
            'validity_years.in' => 'La durée de validité doit être de 3 ou 5 ans.',
        ]);

        try {
            DB::beginTransaction();

            // Vérifier si l'association existe déjà
            $existingAssociation = DB::table('coworker_trainings')
                ->where('coworker_id', $this->selected_coworker_id)
                ->where('training_id', $this->selected_training_id)
                ->first();

            if ($existingAssociation) {
                $this->errorMessage = 'Ce collaborateur a déjà cette formation attribuée.';

                return;
            }

            // Calculer la date d'expiration
            $startDate = Carbon::parse($this->start_date);
            $expiresAt = $startDate->copy()->addYears($this->validity_years);

            // Créer l'enregistrement dans coworker_trainings
            DB::table('coworker_trainings')->insert([
                'coworker_id' => $this->selected_coworker_id,
                'training_id' => $this->selected_training_id,
                'started_at' => $startDate,
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->successMessage = 'Formation attribuée avec succès au collaborateur.';

            // Réinitialiser les champs
            $this->selected_training_id = null;
            $this->start_date = null;
            $this->validity_years = null;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Une erreur est survenue lors de l\'attribution de la formation.';
        }
    }

    public function resetForm()
    {
        $this->selected_coworker_id = null;
        $this->selected_training_id = null;
        $this->start_date = null;
        $this->validity_years = null;
    }

    public function closeModal()
    {
        $this->resetForm();
        Flux::modal('add-coworker-to-formation-modal')->close();
    }

    public function render()
    {
        return view('livewire.training.add-coworker-to-training-form');
    }
}
