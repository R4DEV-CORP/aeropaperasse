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

    /**
     * Ouvrir un document dans le navigateur
     */
    public function viewDocument(string $documentType)
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

        // Vérifier si le fichier peut être visualisé (PDF, PNG, JPEG)
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if (! in_array($extension, ['pdf', 'png', 'jpg', 'jpeg'])) {
            session()->flash('error', 'Ce type de fichier ne peut pas être visualisé dans le navigateur.');

            return;
        }

        // Obtenir l'URL publique du fichier
        $url = $disk->url($relativePath);

        // Retourner l'URL pour l'ouvrir dans un nouvel onglet via JavaScript
        $this->dispatch('open-document', url: $url);
    }

    /**
     * Vérifier si un document peut être visualisé dans le navigateur
     */
    public function canViewDocument(string $documentType): bool
    {
        $documentService = new VehiclePassDocumentService;
        $relativePath = $documentService->getDocumentPath($this->vehiclePass, $documentType);

        if (! $relativePath) {
            return false;
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        return in_array($extension, ['pdf', 'png', 'jpg', 'jpeg']);
    }

    public function render()
    {
        return view('livewire.vehicle-pass.view-vehicle-pass');
    }
}
