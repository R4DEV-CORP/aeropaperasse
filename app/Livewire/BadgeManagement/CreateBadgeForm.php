<?php

namespace App\Livewire\BadgeManagement;

use Livewire\Component;
use App\Models\BadgeRequest;
use App\Models\Badge;
use App\Models\Client;

class CreateBadgeForm extends Component
{
    public $badgeRequests;

    public $clients;

    public $selected_client_id;

    public $selected_badge_request_id;

    public $expiry_date;

    public $errorMessage;

    public $successMessage;

    public function mount()
    {
        $this->badgeRequests = collect(); // Initialiser avec une collection vide
        $this->loadClients();
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
                ->whereHas('activityRequest', function($query) {
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
        $this->validate();
        
        try {
            $badge = Badge::create([
                'badge_request_id' => $this->selected_badge_request_id,
                'status' => 'active',
                'expiry_date' => $this->expiry_date,
            ]);

            $this->successMessage = 'Badge créé avec succès !';
            $this->reset(['selected_client_id', 'selected_badge_request_id', 'expiry_date', 'errorMessage']);
            $this->badgeRequests = collect();
            
            $this->dispatch('badge-created');
        } catch (\Exception $e) {
            $this->errorMessage = 'Erreur lors de la création du badge : ' . $e->getMessage();
        }
    }
    public function render()
    {
        return view('livewire.badge-management.create-badge-form');
    }
}
