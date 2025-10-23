<?php

namespace App\Livewire\VehiclePass;

use App\Models\VehiclePass;
use Livewire\Component;

class Index extends Component
{
    public $vehiclePasses;

    public function mount()
    {
        if (auth()->user()->isAdmin()) {
            $this->vehiclePasses = VehiclePass::with('client')->get();
        } else {
            $this->vehiclePasses = VehiclePass::with('client')->where('client_id', auth()->user()->client_id)->get();
        }
    }

    public function render()
    {
        return view('livewire.vehicle-pass.index');
    }
}
