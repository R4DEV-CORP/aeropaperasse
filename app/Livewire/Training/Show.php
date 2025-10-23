<?php

namespace App\Livewire\Training;

use App\Models\Client;
use App\Services\CertificateTrainingDocumentService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public $coworkers;

    public $slug;

    public $client;

    public $activeTrainings;

    public $soonExpiringTrainings;

    public $expiredTrainings;

    public $certificate;

    public $successMessage = '';

    public $errorMessage = '';

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->client = Client::where('slug', $slug)->first();
        $this->coworkers = $this->client->coworkers;

        $this->loadTrainings();
    }

    public function uploadCertificate($trainingId)
    {
        try {
            // Valider le fichier
            $this->validate([
                'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            ]);

            // Utiliser le service pour uploader le certificat
            $certificateService = new CertificateTrainingDocumentService;
            $storedPath = $certificateService->uploadCertificate($trainingId, $this->certificate);

            // Réinitialiser le fichier et les messages
            $this->certificate = null;
            $this->successMessage = 'Certificat uploadé avec succès !';
            $this->errorMessage = '';

            // Rafraîchir les données
            $this->loadTrainings();

            // Fermer le modal
            $this->dispatch('close-modal', 'upload-certificate-modal');

        } catch (\Exception $e) {
            $this->errorMessage = 'Erreur lors de l\'upload du certificat : '.$e->getMessage();
            $this->successMessage = '';
        }
    }

    /**
     * Télécharger un certificat de formation
     */
    public function downloadCertificate($trainingId)
    {
        try {
            $certificateService = new CertificateTrainingDocumentService;
            $certificatePath = $certificateService->getCertificatePath($trainingId);

            if (! $certificatePath) {
                $this->errorMessage = 'Aucun certificat trouvé pour cette formation.';

                return;
            }

            // Vérifier que le fichier existe
            if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($certificatePath)) {
                $this->errorMessage = 'Le fichier certificat n\'existe plus sur le serveur.';

                return;
            }

            // Retourner le fichier pour téléchargement
            return \Illuminate\Support\Facades\Storage::disk('public')->download($certificatePath);

        } catch (\Exception $e) {
            $this->errorMessage = 'Erreur lors du téléchargement du certificat : '.$e->getMessage();
        }
    }

    /**
     * Rafraîchir les données des formations
     */
    public function loadTrainings()
    {
        $this->activeTrainings = DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->join('trainings', 'coworker_trainings.training_id', '=', 'trainings.id')
            ->select(
                'coworker_trainings.*',
                'coworkers.firstname as coworker_firstname',
                'coworkers.lastname as coworker_lastname',
                'trainings.title as training_title'
            )
            ->where('coworker_trainings.expires_at', '>=', now())
            ->whereIn('coworker_trainings.coworker_id', $this->coworkers->pluck('id'))
            ->get();

        $this->soonExpiringTrainings = DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->join('trainings', 'coworker_trainings.training_id', '=', 'trainings.id')
            ->select(
                'coworker_trainings.*',
                'coworkers.firstname as coworker_firstname',
                'coworkers.lastname as coworker_lastname',
                'trainings.title as training_title'
            )
            ->where('coworker_trainings.expires_at', '<=', now()->addMonth(6))
            ->where('coworker_trainings.expires_at', '>=', now())
            ->whereIn('coworker_trainings.coworker_id', $this->coworkers->pluck('id'))
            ->get();

        $this->expiredTrainings = DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->join('trainings', 'coworker_trainings.training_id', '=', 'trainings.id')
            ->select(
                'coworker_trainings.*',
                'coworkers.firstname as coworker_firstname',
                'coworkers.lastname as coworker_lastname',
                'trainings.title as training_title'
            )
            ->where('coworker_trainings.expires_at', '<', now())
            ->whereIn('coworker_trainings.coworker_id', $this->coworkers->pluck('id'))
            ->get();
    }

    public function render()
    {
        return view('livewire.training.show');
    }
}
