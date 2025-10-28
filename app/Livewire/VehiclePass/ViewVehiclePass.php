<?php

namespace App\Livewire\VehiclePass;

use App\Models\VehiclePass;
use App\Services\VehiclePassDocumentService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ViewVehiclePass extends Component
{
    public $vehiclePass;

    public function mount($vehiclePassId)
    {
        $this->vehiclePass = VehiclePass::find($vehiclePassId);
    }

    /**
     * Télécharger un document spécifique
     */
    public function downloadDocument(string $documentType)
    {
        $documentService = new VehiclePassDocumentService;
        $relativePath = $documentService->getDocumentPath($this->vehiclePass, $documentType);

        if (! $relativePath) {
            session()->flash('error', 'Document non disponible.');

            return;
        }

        $disk = Storage::disk('public');

        // Vérifier si le fichier existe
        if (! $disk->exists($relativePath)) {
            session()->flash('error', 'Le fichier n\'existe pas.');

            return;
        }

        // Obtenir le chemin absolu du fichier
        $absolutePath = $disk->path($relativePath);

        // Récupérer le nom original du fichier
        $filename = basename($relativePath);

        return response()->download($absolutePath, $filename);
    }

    public function render()
    {
        return view('livewire.vehicle-pass.view-vehicle-pass');
    }
}
