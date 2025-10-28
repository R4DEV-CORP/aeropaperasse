<?php

namespace App\Actions\Client;

use App\DataTransferObjects\CreateClientData;
use App\Models\Client;
use App\Services\ClientDocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateClientAction
{
    /*
    * ⚠️ IMPORTANT ⚠️
    * Il y a une deuxième classe à la fin du fichier qui sert à retourner les résultats de l'action
    */

    public function __construct(
        private ClientDocumentService $documentService
    ) {}

    /**
     * Execute la création d'un client
     */
    public function execute(CreateClientData $data): CreateClientResult
    {
        try {
            return DB::transaction(function () use ($data) {
                // 1. Stocker les documents avec le nom de l'entreprise
                $storedDocuments = $this->documentService->storeDocuments($data->getDocuments(), $data->company_name);

                // 2. Préparer les données du client avec les chemins des documents
                $clientData = array_merge($data->getClientData(), $storedDocuments);

                // 3. Créer le client
                $client = Client::create($clientData);

                // 4. Créer les contacts
                $this->createContacts($client, $data->getContacts());

                // 5. Log de la création
                Log::info('Client créé avec succès', [
                    'client_id' => $client->id,
                    'company_name' => $client->company_name,
                    'documents_count' => count($storedDocuments),
                    'contacts_count' => count($data->getContacts()),
                ]);

                return new CreateClientResult(
                    success: true,
                    client: $client,
                    message: 'Client créé avec succès'
                );

            });
        } catch (\Exception $e) {
            // Log de l'erreur
            Log::error('Erreur lors de la création du client', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->user()->id,
            ]);

            return new CreateClientResult(
                success: false,
                client: null,
                message: 'Erreur lors de la création du client : '.$e->getMessage()
            );
        }
    }

    /**
     * Création des contacts pour le client
     */
    protected function createContacts(Client $client, array $contactsData): void
    {
        foreach ($contactsData as $contactData) {
            // Filtrer les valeurs nulles pour les contacts optionnels
            $filteredData = array_filter($contactData, function ($value) {
                return $value !== null && $value !== '';
            });

            // Ne créer le contact que s'il y a des données valides
            if (! empty($filteredData) && count($filteredData) >= 4) { // Au moins prénom, nom, email, téléphone
                $client->contacts()->create($contactData);
            }
        }
    }
}

/**
 * Classe de résultat pour la création d'un client
 */
class CreateClientResult
{
    public function __construct(
        public bool $success,
        public ?Client $client,
        public string $message
    ) {}

    /**
     * Check si l'action a été réussie
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Retourne le message de l'action
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Retourne des informations supplémentaires pour la réponse
     */
    public function getData(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'client_id' => $this->client?->id,
            'company_name' => $this->client?->company_name,
        ];
    }
}
