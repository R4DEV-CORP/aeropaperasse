<?php

namespace App\Actions\VehiclePass;

use App\DataTransferObjects\CreateVehiclePassData;
use App\Models\Client;
use App\Models\VehiclePass;
use App\Services\VehiclePassDocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action unifiée pour créer ou mettre à jour un laissez-passer véhicule
 */
class SaveVehiclePassAction
{
    public function __construct(
        private VehiclePassDocumentService $documentService
    ) {}

    /**
     * Exécute la création d'un laissez-passer véhicule
     */
    public function execute(
        CreateVehiclePassData $data,
        Client $client
    ): VehiclePassResult {
        try {
            return DB::transaction(function () use ($data, $client) {
                // Créer d'abord le laissez-passer véhicule avec des valeurs par défaut pour les documents
                $vehiclePass = $this->createNewVehiclePass($data);

                // Ensuite, stocker les documents et mettre à jour le laissez-passer
                if ($data->hasDocuments()) {
                    $storedDocuments = $this->documentService->storeDocuments(
                        $data->getDocuments(),
                        $client,
                        $vehiclePass->id
                    );
                    $vehiclePass->update($storedDocuments);
                }

                // Log de succès
                $this->logOperationSuccess($vehiclePass, $client, $data);

                $message = 'Laissez-passer véhicule créé avec succès';

                return VehiclePassResult::success($vehiclePass, $message, 'create');
            });
        } catch (\Exception $e) {
            return $this->handleException($e, $client, $data);
        }
    }

    /**
     * Crée un nouveau laissez-passer véhicule
     */
    private function createNewVehiclePass(CreateVehiclePassData $data): VehiclePass
    {
        $vehiclePassData = $data->getVehiclePassData();

        // Ajouter des valeurs par défaut pour les champs de documents
        $vehiclePassData['certificate_of_registration'] = '';
        $vehiclePassData['company_stamp'] = '';

        $vehiclePass = VehiclePass::create($vehiclePassData);

        return $vehiclePass;
    }

    /**
     * Retourne le message de succès approprié
     */
    private function getSuccessMessage(): string
    {
        return 'Laissez-passer véhicule créé avec succès';
    }

    /**
     * Log le succès de l'opération
     */
    private function logOperationSuccess(
        VehiclePass $vehiclePass,
        Client $client,
        CreateVehiclePassData $data
    ): void {
        Log::info('Laissez-passer véhicule créé avec succès', [
            'vehicle_pass_id' => $vehiclePass->id,
            'client_id' => $client->id,
            'company_name' => $client->company_name,
            'status' => $vehiclePass->status,
            'plate_number' => $data->plate_number,
            'airport' => $data->airport,
        ]);
    }

    /**
     * Gère les exceptions et retourne un résultat d'échec
     */
    private function handleException(
        \Exception $e,
        Client $client,
        CreateVehiclePassData $data
    ): VehiclePassResult {
        Log::error('Erreur lors de la création du laissez-passer véhicule', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'client_id' => $client->id,
        ]);

        $message = 'Erreur lors de la création du laissez-passer véhicule : '.$e->getMessage();

        return VehiclePassResult::failure($message, 'create');
    }
}
