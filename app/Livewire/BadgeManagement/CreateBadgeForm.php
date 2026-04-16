<?php

namespace App\Livewire\BadgeManagement;

use App\Mail\BadgeCreated;
use App\Models\Badge;
use App\Models\BadgeRequest;
use App\Models\Client;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class CreateBadgeForm extends Component
{
    public $badgeRequests;

    public $clients;

    public $selected_client_id;

    public $selected_badge_request_id;

    public $expiry_date;

    public $badge_number;

    public $errorMessage;

    public $successMessage;

    public function mount()
    {
        $this->badgeRequests = collect(); // Initialiser avec une collection vide
        if (auth()->user()->isAdmin()) {
            $this->loadClients();
        } else {
            $this->selected_client_id = auth()->user()->client->id;
            $this->badgeRequests = BadgeRequest::with(['activityRequest', 'coworker'])
                ->whereHas('activityRequest', function ($query) {
                    $query->where('client_id', $this->selected_client_id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }
    }

    public function loadClients()
    {
        $this->clients = Client::orderBy('company_name')->get();
        $this->selected_client_id = null;
        $this->selected_badge_request_id = null; // Réinitialiser aussi la sélection de badge request
        $this->badgeRequests = collect(); // Réinitialiser la liste des badge requests
    }

    public function updatedSelectedClientId()
    {
        $this->selected_badge_request_id = null; // Réinitialiser la sélection de badge request

        if ($this->selected_client_id) {
            // Charger les badge requests pour ce client via activity request
            $this->badgeRequests = BadgeRequest::with(['activityRequest', 'coworker'])
                ->whereHas('activityRequest', function ($query) {
                    $query->where('client_id', $this->selected_client_id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $this->badgeRequests = collect();
        }
    }

    protected $rules = [
        'selected_client_id' => 'required|exists:clients,id',
        'selected_badge_request_id' => 'required|exists:badge_requests,id',
        'expiry_date' => 'required|date|after:today',
        'badge_number' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'selected_client_id.required' => 'Veuillez sélectionner un client.',
        'selected_client_id.exists' => 'Le client sélectionné n\'existe pas.',
        'selected_badge_request_id.required' => 'Veuillez sélectionner une demande de badge.',
        'selected_badge_request_id.exists' => 'La demande de badge sélectionnée n\'existe pas.',
        'expiry_date.required' => 'La date d\'expiration est requise.',
        'expiry_date.date' => 'La date d\'expiration doit être une date valide.',
        'expiry_date.after' => 'La date d\'expiration doit être postérieure à aujourd\'hui.',
    ];

    public function createBadge()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->validate();

        $client = Client::find($this->selected_client_id);

        if (! $client) {
            $this->errorMessage = 'Le client sélectionné n\'existe pas. Veuillez sélectionner un autre client.';

            return;
        }

        $badge = Badge::where('badge_request_id', $this->selected_badge_request_id)->first();

        if ($badge) {
            $this->errorMessage = 'Un badge existe déjà pour cette demande. Veuillez sélectionner une autre demande.';

            return;
        }

        try {
            $badge = Badge::create([
                'badge_request_id' => $this->selected_badge_request_id,
                'status' => 'active',
                'expiry_date' => $this->expiry_date,
                'badge_number' => $this->badge_number ?: null,
            ]);

            if (! $badge) {
                $this->errorMessage = 'Une erreur est survenue lors de la création du badge. Veuillez réessayer.';

                return;
            }

            $this->successMessage = 'Badge créé avec succès !';
            $this->reset(['selected_client_id', 'selected_badge_request_id', 'expiry_date', 'badge_number', 'errorMessage']);
            $this->badgeRequests = collect();

            $this->dispatch('badge-created');

            // Envoyer une notification par email
            $email = $badge->badgeRequest->creator->email;
            if ($badge->badgeRequest->activityRequest->client->notification_email) {
                $email = $badge->badgeRequest->activityRequest->client->notification_email;
            }
            Mail::to($email)->send(new BadgeCreated($badge));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du badge', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->user()->id,
                'badge_request_id' => $this->selected_badge_request_id,
                'client_id' => $this->selected_client_id,
            ]);
        }
        $this->closeModal();
    }

    /**
     * Réinitialiser le formulaire
     */
    public function resetForm(): void
    {
        $this->reset([
            'errorMessage',
            'successMessage',
            'selected_client_id',
            'selected_badge_request_id',
            'expiry_date',
            'badge_number',
        ]);

        // Réinitialiser la sélection de client pour les admins
        if (auth()->user()->isAdmin()) {
            $this->selected_client_id = null;
            $this->clients = collect();
        }
    }

    /**
     * Fermer la modal
     */
    public function closeModal(): void
    {
        $this->resetForm();
        Flux::modal('add-badge')->close();
    }

    public function render()
    {
        return view('livewire.badge-management.create-badge-form');
    }
}
