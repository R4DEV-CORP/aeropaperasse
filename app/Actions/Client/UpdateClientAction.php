<?php

namespace App\Actions\Client;

use App\Models\Client;
use App\Services\ClientDocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateClientAction
{
    public function __construct(
        private ClientDocumentService $documentService
    ) {}

    /**
     * Execute la mise à jour d'un client
     */
    public function execute(Client $client, array $formData): UpdateClientResult
    {
        try {
            return DB::transaction(function () use ($client, $formData) {
                // 1. Préparer les données de base du client
                $clientData = [
                    'company_name' => $formData['company_name'],
                    'trade_name' => $formData['trade_name'],
                    'siret_number' => $formData['siret_number'],
                    'address' => $formData['address'],
                    'zip_code' => $formData['zip_code'],
                    'city' => $formData['city'],
                    'subcontractor_of' => $formData['subcontractor_of'],
                    'badge_limit' => $formData['badge_limit'],
                    'vehicle_pass_limit' => $formData['vehicle_pass_limit'],
                    'notification_email' => $formData['notification_email'],
                ];

                // 2. Gérer les documents s'ils sont fournis
                $documents = $this->getDocumentsFromFormData($formData);
                if (! empty($documents)) {
                    $storedDocuments = $this->documentService->storeDocuments($documents, $formData['company_name']);
                    $clientData = array_merge($clientData, $storedDocuments);
                }

                // 3. Mettre à jour le client
                $client->update($clientData);

                // 4. Mettre à jour les contacts
                $this->updateContacts($client, $formData);

                // 5. Log de la mise à jour
                Log::info('Client mis à jour avec succès', [
                    'client_id' => $client->id,
                    'company_name' => $client->company_name,
                    'documents_updated' => ! empty($documents),
                    'contacts_updated' => true,
                ]);

                return new UpdateClientResult(
                    success: true,
                    client: $client,
                    message: 'Client mis à jour avec succès'
                );

            });
        } catch (\Exception $e) {
            // Log de l'erreur
            Log::error('Erreur lors de la mise à jour du client', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return new UpdateClientResult(
                success: false,
                client: null,
                message: 'Erreur lors de la mise à jour du client : '.$e->getMessage()
            );
        }
    }

    /**
     * Extraire les documents du formulaire
     */
    protected function getDocumentsFromFormData(array $formData): array
    {
        $documents = [];

        if (! empty($formData['kbis_document'])) {
            $documents['kbis_document'] = $formData['kbis_document'];
        }
        if (! empty($formData['safety_document'])) {
            $documents['safety_document'] = $formData['safety_document'];
        }
        if (! empty($formData['security_document'])) {
            $documents['security_document'] = $formData['security_document'];
        }

        return $documents;
    }

    /**
     * Mise à jour des contacts pour le client
     */
    protected function updateContacts(Client $client, array $formData): void
    {
        // Supprimer tous les contacts existants
        $client->contacts()->delete();

        // Créer les nouveaux contacts
        $contacts = [];

        // Référent sûreté 1 (obligatoire)
        if (! empty($formData['safety_referent_1_prenom']) && ! empty($formData['safety_referent_1_nom'])) {
            $contacts[] = [
                'firstname' => $formData['safety_referent_1_prenom'],
                'lastname' => $formData['safety_referent_1_nom'],
                'email' => $formData['safety_referent_1_email'],
                'phone' => $formData['safety_referent_1_phone'],
                'role' => 'safety',
            ];
        }

        // Référent sûreté 2 (optionnel)
        if (! empty($formData['safety_referent_2_prenom']) && ! empty($formData['safety_referent_2_nom'])) {
            $contacts[] = [
                'firstname' => $formData['safety_referent_2_prenom'],
                'lastname' => $formData['safety_referent_2_nom'],
                'email' => $formData['safety_referent_2_email'],
                'phone' => $formData['safety_referent_2_phone'],
                'role' => 'safety',
            ];
        }

        // Référent sûreté 3 (optionnel)
        if (! empty($formData['safety_referent_3_prenom']) && ! empty($formData['safety_referent_3_nom'])) {
            $contacts[] = [
                'firstname' => $formData['safety_referent_3_prenom'],
                'lastname' => $formData['safety_referent_3_nom'],
                'email' => $formData['safety_referent_3_email'],
                'phone' => $formData['safety_referent_3_phone'],
                'role' => 'safety',
            ];
        }

        // Correspondant sécurité (obligatoire)
        if (! empty($formData['security_correspondent_prenom']) && ! empty($formData['security_correspondent_nom'])) {
            $contacts[] = [
                'firstname' => $formData['security_correspondent_prenom'],
                'lastname' => $formData['security_correspondent_nom'],
                'email' => $formData['security_correspondent_email'],
                'phone' => $formData['security_correspondent_phone'],
                'role' => 'security',
            ];
        }

        // Contact RH (obligatoire)
        if (! empty($formData['hr_contact_prenom']) && ! empty($formData['hr_contact_nom'])) {
            $contacts[] = [
                'firstname' => $formData['hr_contact_prenom'],
                'lastname' => $formData['hr_contact_nom'],
                'email' => $formData['hr_contact_email'],
                'phone' => $formData['hr_contact_phone'],
                'role' => 'hr',
            ];
        }

        // Créer tous les contacts
        foreach ($contacts as $contact) {
            $client->contacts()->create($contact);
        }
    }
}

/**
 * Classe de résultat pour la mise à jour d'un client
 */
class UpdateClientResult
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
