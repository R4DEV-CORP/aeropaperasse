<?php

namespace App\Forms;

use Illuminate\Http\UploadedFile;

/**
 * Encapsule les données du formulaire de demande d'activité
 * Sépare la logique de gestion du formulaire du composant Livewire
 */
class ActivityRequestFormData
{
    public function __construct(
        // Informations du responsable
        public ?string $manager_firstname = null,
        public ?string $manager_lastname = null,
        public ?string $manager_email = null,
        public ?string $manager_phone = null,
        public ?string $manager_role = null,

        // Informations sur l'activité
        public ?string $airport = null,
        public ?string $description = null,
        public ?string $customer_names = null,
        public ?int $person_count = null,
        public ?int $vehicule_count = null,

        // Documents
        public ?UploadedFile $customer_certificate_document = null,
        public ?UploadedFile $prefectural_agreement_document = null,
        public ?UploadedFile $iata_contract_document = null,
        public ?UploadedFile $cta_document = null,

        // Flags et métadonnées
        public bool $renewal = false,
        public ?int $last_activity_request_id = null,
    ) {}

    /**
     * Crée une instance à partir d'un array de données brutes
     */
    public static function fromArray(array $data): self
    {
        // Normalisation de last_activity_request_id
        $lastActivityRequestId = $data['last_activity_request_id'] ?? null;
        if ($lastActivityRequestId === '' || $lastActivityRequestId === null) {
            $lastActivityRequestId = null;
        } else {
            $lastActivityRequestId = (int) $lastActivityRequestId;
        }

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
            customer_certificate_document: $data['customer_certificate_document'] ?? null,
            prefectural_agreement_document: $data['prefectural_agreement_document'] ?? null,
            iata_contract_document: $data['iata_contract_document'] ?? null,
            cta_document: $data['cta_document'] ?? null,
            renewal: $data['renewal'] ?? false,
            last_activity_request_id: $lastActivityRequestId,
        );
    }

    /**
     * Convertit les données du formulaire en array
     */
    public function toArray(): array
    {
        return [
            'manager_firstname' => $this->manager_firstname,
            'manager_lastname' => $this->manager_lastname,
            'manager_email' => $this->manager_email,
            'manager_phone' => $this->manager_phone,
            'manager_role' => $this->manager_role,
            'airport' => $this->airport,
            'description' => $this->description,
            'customer_names' => $this->customer_names,
            'person_count' => $this->person_count,
            'vehicule_count' => $this->vehicule_count,
            'customer_certificate_document' => $this->customer_certificate_document,
            'prefectural_agreement_document' => $this->prefectural_agreement_document,
            'iata_contract_document' => $this->iata_contract_document,
            'cta_document' => $this->cta_document,
            'renewal' => $this->renewal,
            'last_activity_request_id' => $this->last_activity_request_id,
        ];
    }

    /**
     * Vérifie si le formulaire est pour un renouvellement valide
     */
    public function isRenewal(): bool
    {
        return $this->renewal && ! is_null($this->last_activity_request_id);
    }

    /**
     * Vérifie si des documents sont présents
     */
    public function hasDocuments(): bool
    {
        return ! is_null($this->customer_certificate_document)
            || ! is_null($this->prefectural_agreement_document)
            || ! is_null($this->iata_contract_document)
            || ! is_null($this->cta_document);
    }

    /**
     * Retourne uniquement les documents présents
     */
    public function getDocuments(): array
    {
        $documents = [];

        if ($this->customer_certificate_document) {
            $documents['customer_certificate_document'] = $this->customer_certificate_document;
        }
        if ($this->prefectural_agreement_document) {
            $documents['prefectural_agreement_document'] = $this->prefectural_agreement_document;
        }
        if ($this->iata_contract_document) {
            $documents['iata_contract_document'] = $this->iata_contract_document;
        }
        if ($this->cta_document) {
            $documents['cta_document'] = $this->cta_document;
        }

        return $documents;
    }

    /**
     * Remplit les données depuis un modèle ActivityRequest existant
     */
    public function fillFromActivityRequest(\App\Models\ActivityRequest $activityRequest): void
    {
        $this->manager_firstname = $activityRequest->manager_firstname;
        $this->manager_lastname = $activityRequest->manager_lastname;
        $this->manager_email = $activityRequest->manager_email;
        $this->manager_phone = $activityRequest->manager_phone;
        $this->manager_role = $activityRequest->manager_role;
        $this->airport = $activityRequest->airport;
        $this->description = $activityRequest->description;
        $this->customer_names = $activityRequest->customer_names;
        $this->person_count = $activityRequest->person_count;
        $this->vehicule_count = $activityRequest->vehicule_count;

        // Pour un brouillon existant, on désactive le mode renouvellement
        // car les données sont déjà présentes
        $this->renewal = false;
        $this->last_activity_request_id = null;
    }

    /**
     * Retourne les indicateurs de présence de documents existants
     */
    public function getExistingDocumentsFlags(\App\Models\ActivityRequest $activityRequest): array
    {
        return [
            'hasExistingCustomerCertificate' => ! empty($activityRequest->customer_certificate_document),
            'hasExistingPrefecturalAgreement' => ! empty($activityRequest->prefectural_agreement_document),
            'hasExistingIataContract' => ! empty($activityRequest->iata_contract_document),
            'hasExistingCta' => ! empty($activityRequest->cta_document),
        ];
    }
}
