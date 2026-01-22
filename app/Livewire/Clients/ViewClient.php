<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Services\ClientOverviewPdfService;
use Livewire\Component;

class ViewClient extends Component
{
    public string $slug;

    public $client;

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        $this->client = Client::where('slug', $slug)
            ->with(['coworkers.user', 'users'])
            ->first();
    }

    public function downloadOverview()
    {
        $client = Client::where('slug', $this->slug)->first();
        $pdfService = new ClientOverviewPdfService;
        $pdf = $pdfService->generateOverview($client->id);
        $this->client = $client;

        // Générer le nom du fichier
        $filename = "Bilan_{$client->company_name}_".date('Y-m-d').'.pdf';

        // Créer le dossier temp s'il n'existe pas
        $tempDir = storage_path('app/temp');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Sauvegarder temporairement le PDF
        $tempPath = $tempDir.'/'.uniqid().'_'.$filename;
        $pdf->save($tempPath);

        // Télécharger le fichier et le supprimer après
        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.clients.view-client', ['client' => $this->client]);
    }
}
