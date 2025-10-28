<?php

namespace App\Livewire\VehiclePass;

use App\Models\VehiclePass;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';

    public $vehiclePasses;

    public $client;

    public $vehiclePassCount = 0;

    public $statistics;

    public function mount()
    {
        $this->loadVehiclePasses();
        $this->statistics = $this->getStatistics();

    }

    public function updatedSearch()
    {
        $this->loadVehiclePasses();
    }

    private function loadVehiclePasses(): void
    {
        $query = VehiclePass::with('client');

        if (! auth()->user()->isAdmin()) {
            $query->where('client_id', auth()->user()->client_id);
            $this->client = auth()->user()->client;
        }

        if (trim($this->search) !== '') {
            $query->where('plate_number', 'like', '%'.$this->search.'%');
        }

        $this->vehiclePasses = $query->get();
    }

    private function getStatistics()
    {
        return [
            'total' => $this->vehiclePasses->count(),
            'pending' => $this->vehiclePasses->where('status', 'pending')->count(),
            'approved' => $this->vehiclePasses->where('status', 'approved')->count(),
            'rejected' => $this->vehiclePasses->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Approuver un laissez-passer véhicule (admin seulement)
     */
    public function approve(int $vehiclePassId): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $vehiclePass = VehiclePass::findOrFail($vehiclePassId);

        $vehiclePass->update([
            'previous_status' => $vehiclePass->status,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        session()->flash('message', 'Demande approuvée avec succès.');

        $this->loadVehiclePasses();
        $this->statistics = $this->getStatistics();
    }

    /**
     * Rejeter un laissez-passer véhicule (admin seulement)
     */
    public function reject(int $vehiclePassId): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $vehiclePass = VehiclePass::findOrFail($vehiclePassId);

        $vehiclePass->update([
            'previous_status' => $vehiclePass->status,
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        session()->flash('message', 'Demande rejetée avec succès.');

        $this->loadVehiclePasses();
        $this->statistics = $this->getStatistics();
    }

    public function render()
    {
        return view('livewire.vehicle-pass.index');
    }
}
