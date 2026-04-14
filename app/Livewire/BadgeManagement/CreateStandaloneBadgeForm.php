<?php

namespace App\Livewire\BadgeManagement;

use App\Models\Badge;
use App\Models\Client;
use App\Models\Coworker;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Component;

class CreateStandaloneBadgeForm extends Component
{
    public ?int $selected_client_id = null;

    public ?int $selected_coworker_id = null;

    public ?string $expiry_date = null;

    public Collection $clients;

    public Collection $coworkers;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    protected array $rules = [
        'selected_client_id' => 'required|exists:clients,id',
        'selected_coworker_id' => 'required|exists:coworkers,id',
        'expiry_date' => 'required|date|after:today',
    ];

    protected array $messages = [
        'selected_client_id.required' => 'Veuillez sélectionner un client.',
        'selected_client_id.exists' => 'Le client sélectionné n\'existe pas.',
        'selected_coworker_id.required' => 'Veuillez sélectionner un collaborateur.',
        'selected_coworker_id.exists' => 'Le collaborateur sélectionné n\'existe pas.',
        'expiry_date.required' => 'La date d\'expiration est requise.',
        'expiry_date.date' => 'La date d\'expiration doit être une date valide.',
        'expiry_date.after' => 'La date d\'expiration doit être postérieure à aujourd\'hui.',
    ];

    public function mount(): void
    {
        $this->clients = Client::orderBy('company_name')->get();
        $this->coworkers = collect();
    }

    public function updatedSelectedClientId(): void
    {
        $this->selected_coworker_id = null;
        $this->coworkers = $this->selected_client_id
            ? Coworker::where('client_id', $this->selected_client_id)
                ->orderBy('lastname')
                ->get()
            : collect();
    }

    public function createBadge(): void
    {
        $this->validate();

        $badge = Badge::create([
            'client_id' => $this->selected_client_id,
            'coworker_id' => $this->selected_coworker_id,
            'badge_request_id' => null,
            'status' => 'active',
            'expiry_date' => $this->expiry_date,
        ]);

        $this->successMessage = 'Badge créé avec succès !';
        $this->reset(['selected_client_id', 'selected_coworker_id', 'expiry_date', 'errorMessage']);
        $this->coworkers = collect();

        $this->dispatch('badge-created');
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->reset(['selected_client_id', 'selected_coworker_id', 'expiry_date', 'errorMessage', 'successMessage']);
        $this->coworkers = collect();
        Flux::modal('add-standalone-badge')->close();
    }

    public function render()
    {
        return view('livewire.badge-management.create-standalone-badge-form');
    }
}
