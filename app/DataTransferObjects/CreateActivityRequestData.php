<?php

namespace App\DataTransferObjects;

use App\Forms\ActivityRequestFormData;
use Illuminate\Http\UploadedFile;

class CreateActivityRequestData
{
    public function __construct(
        // Informations du responsable
        public ?string $manager_firstname,
        public ?string $manager_lastname,
        public ?string $manager_email,
        public ?string $manager_phone,
        public ?string $manager_role,

        // Informations sur l'activité
        public ?string $airport,
        public ?string $description,
        public ?string $customer_names,
        public ?int $person_count,
        public ?int $vehicule_count,

        // Documents (optionnels pour le brouillon)
        public ?UploadedFile $aao_request_document,
        public ?UploadedFile $kbis_document,
        public array $principals, // Renommé de term_document, peut être multiple
        public ?UploadedFile $safety_referent_document,
        public ?UploadedFile $cta_document,
        public ?UploadedFile $security_referent_document,

        // Metadata
        public int $client_id,
        public int $created_by,
        public bool $is_draft = false,
        public bool $renewal = false,
        public ?int $last_activity_request_id = null,
    ) {}

    /**
     * Créer le DTO à partir d'un array
     * Note : La logique de renouvellement a été déplacée vers ActivityRequestRenewalService
     * Cette méthode se contente de mapper les données
     */
    public static function fromArray(array $data, int $clientId, int $userId, bool $isDraft = false): self
    {
        $isRenewal = $data['renewal'] ?? false;
        $lastActivityRequestId = ! empty($data['last_activity_request_id']) ? (int) $data['last_activity_request_id'] : null;

        return new self(
            manager_firstname: $data['manager_firstname'] ?? null,
            manager_lastname: $data['manager_lastname'] ?? null,
            manager_email: $data['manager_email'] ?? null,
            manager_phone: $data['manager_phone'] ?? null,
            manager_role: $data['manager_role'] ?? null,
            airport: $data['airport'] ?? null,
            description: $data['description'] ?? null,
            customer_names: $data['customer_names'] ?? null,
            person_count: isset($data['person_count']) ? (int) $data['person_count'] : null,
            vehicule_count: isset($data['vehicule_count']) ? (int) $data['vehicule_count'] : null,
            aao_request_document: $data['aao_request_document'] ?? null,
            kbis_document: $data['kbis_document'] ?? null,
            principals: $data['principals'] ?? $data['term_document'] ?? [],
            safety_referent_document: $data['safety_referent_document'] ?? null,
            cta_document: $data['cta_document'] ?? null,
            security_referent_document: $data['security_referent_document'] ?? null,
            client_id: $clientId,
            created_by: $userId,
            is_draft: $isDraft,
            renewal: $isRenewal,
            last_activity_request_id: $lastActivityRequestId,
        );
    }

    /**
     * Créer le DTO à partir du ActivityRequestFormData
     */
    public static function fromFormData(
        ActivityRequestFormData $formData,
        int $clientId,
        int $userId,
        bool $isDraft = false
    ): self {
        return new self(
            manager_firstname: $formData->manager_firstname,
            manager_lastname: $formData->manager_lastname,
            manager_email: $formData->manager_email,
            manager_phone: $formData->manager_phone,
            manager_role: $formData->manager_role,
            airport: $formData->airport,
            description: $formData->description,
            customer_names: $formData->customer_names,
            person_count: $formData->person_count,
            vehicule_count: $formData->vehicule_count,
            aao_request_document: $formData->aao_request_document,
            kbis_document: $formData->kbis_document,
            principals: $formData->principals,
            safety_referent_document: $formData->safety_referent_document,
            cta_document: $formData->cta_document,
            security_referent_document: $formData->security_referent_document,
            client_id: $clientId,
            created_by: $userId,
            is_draft: $isDraft,
            renewal: $formData->renewal,
            last_activity_request_id: $formData->last_activity_request_id,
        );
    }

    /**
     * Retourne les données de la demande d'activité pour la base de données
     */
    public function getActivityRequestData(): array
    {
        $data = [
            'client_id' => $this->client_id,
            'created_by' => $this->created_by,
            'airport' => $this->airport,
            'manager_firstname' => $this->manager_firstname,
            'manager_lastname' => $this->manager_lastname,
            'manager_email' => $this->manager_email,
            'manager_phone' => $this->manager_phone,
            'manager_role' => $this->manager_role,
            'description' => $this->description,
            'customer_names' => $this->customer_names,
            'person_count' => $this->person_count,
            'vehicule_count' => $this->vehicule_count,
            'renewal' => $this->renewal,
            'last_activity_request_id' => $this->last_activity_request_id,
        ];

        // Ajouter les champs spécifiques au brouillon
        if ($this->is_draft) {
            $data['status'] = 'draft';
            $data['draft_at'] = now();
        } else {
            $data['status'] = 'pending';
            $data['pending_at'] = now();
        }

        // Filtrer uniquement les valeurs null (garder false et 0)
        return array_filter($data, function ($value) {
            return ! is_null($value);
        });
    }

    /**
     * Retourne les documents à enregistrer
     */
    public function getDocuments(): array
    {
        $documents = [];

        if ($this->aao_request_document) {
            $documents['aao_request_document'] = $this->aao_request_document;
        }
        if ($this->kbis_document) {
            $documents['kbis_document'] = $this->kbis_document;
        }
        if (! empty($this->principals)) {
            $documents['principals'] = $this->principals;
        }
        if ($this->safety_referent_document) {
            $documents['safety_referent_document'] = $this->safety_referent_document;
        }
        if ($this->security_referent_document) {
            $documents['security_referent_document'] = $this->security_referent_document;
        }
        if ($this->cta_document) {
            $documents['cta_document'] = $this->cta_document;
        }

        return $documents;
    }

    /**
     * Vérifie si des documents sont présents
     */
    public function hasDocuments(): bool
    {
        return ! empty($this->getDocuments());
    }
}
