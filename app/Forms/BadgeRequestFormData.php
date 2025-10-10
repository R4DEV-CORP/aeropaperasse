<?php

namespace App\Forms;

use Illuminate\Http\UploadedFile;
use App\Models\BadgeRequest;

/**
 * Encapsule les données du formulaire de demande de badge
 * Sépare la logique de gestion du formulaire du composant Livewire
 */
class BadgeRequestFormData
{
    public function __construct(
        // Relations
        public ?int $activity_request_id = null,
        public ?int $coworker_id = null,

        // Documents
        public ?UploadedFile $selfie_photo = null,
        public ?UploadedFile $identification_card = null,
        public ?UploadedFile $activity_authorization = null,
        public ?UploadedFile $for_document = null,
        public ?UploadedFile $formation_certificate_document = null,
        public ?UploadedFile $invoice_document = null,

        // Flags
        public bool $application_authorization = false,
        public bool $validate_training = false,
    ) {}

    /**
     * Crée une instance à partir d'un array de données brutes
     */
    public static function fromArray(array $data): self
    {
        // Normalisation des IDs
        $activityRequestId = $data['activity_request_id'] ?? null;
        if ($activityRequestId === '' || $activityRequestId === null) {
            $activityRequestId = null;
        } else {
            $activityRequestId = (int) $activityRequestId;
        }

        $coworkerId = $data['coworker_id'] ?? null;
        if ($coworkerId === '' || $coworkerId === null) {
            $coworkerId = null;
        } else {
            $coworkerId = (int) $coworkerId;
        }

        return new self(
            activity_request_id: $activityRequestId,
            coworker_id: $coworkerId,
            selfie_photo: $data['selfie_photo'] ?? null,
            identification_card: $data['identification_card'] ?? null,
            activity_authorization: $data['activity_authorization'] ?? null,
            for_document: $data['for_document'] ?? null,
            formation_certificate_document: $data['formation_certificate_document'] ?? null,
            invoice_document: $data['invoice_document'] ?? null,
            application_authorization: (bool) ($data['application_authorization'] ?? false),
            validate_training: (bool) ($data['validate_training'] ?? false),
        );
    }

    /**
     * Convertit les données du formulaire en array
     */
    public function toArray(): array
    {
        return [
            'activity_request_id' => $this->activity_request_id,
            'coworker_id' => $this->coworker_id,
            'selfie_photo' => $this->selfie_photo,
            'identification_card' => $this->identification_card,
            'activity_authorization' => $this->activity_authorization,
            'for_document' => $this->for_document,
            'formation_certificate_document' => $this->formation_certificate_document,
            'invoice_document' => $this->invoice_document,
            'application_authorization' => $this->application_authorization,
            'validate_training' => $this->validate_training,
        ];
    }

    /**
     * Vérifie si des documents sont présents
     */
    public function hasDocuments(): bool
    {
        return ! is_null($this->selfie_photo)
            || ! is_null($this->identification_card)
            || ! is_null($this->activity_authorization)
            || ! is_null($this->for_document)
            || ! is_null($this->formation_certificate_document)
            || ! is_null($this->invoice_document);
    }

    /**
     * Retourne uniquement les documents présents
     */
    public function getDocuments(): array
    {
        $documents = [];

        if ($this->selfie_photo) {
            $documents['selfie_photo'] = $this->selfie_photo;
        }
        if ($this->identification_card) {
            $documents['identification_card'] = $this->identification_card;
        }
        if ($this->activity_authorization) {
            $documents['activity_authorization'] = $this->activity_authorization;
        }
        if ($this->for_document) {
            $documents['for_document'] = $this->for_document;
        }
        if ($this->formation_certificate_document) {
            $documents['formation_certificate_document'] = $this->formation_certificate_document;
        }
        if ($this->invoice_document) {
            $documents['invoice_document'] = $this->invoice_document;
        }

        return $documents;
    }

    /**
     * Remplit les données depuis un modèle BadgeRequest existant
     */
    public function fillFromBadgeRequest(BadgeRequest $badgeRequest): void
    {
        $this->activity_request_id = $badgeRequest->activity_request_id;
        $this->coworker_id = $badgeRequest->coworker_id;
        $this->application_authorization = $badgeRequest->application_authorization;
        $this->validate_training = $badgeRequest->validate_training;
    }

    /**
     * Retourne les indicateurs de présence de documents existants
     */
    public function getExistingDocumentsFlags(BadgeRequest $badgeRequest): array
    {
        return [
            'hasExistingSelfiePhoto' => ! empty($badgeRequest->selfie_photo),
            'hasExistingIdentificationCard' => ! empty($badgeRequest->identification_card),
            'hasExistingActivityAuthorization' => ! empty($badgeRequest->activity_authorization),
            'hasExistingForDocument' => ! empty($badgeRequest->for_document),
            'hasExistingFormationCertificate' => ! empty($badgeRequest->formation_certificate_document),
            'hasExistingInvoice' => ! empty($badgeRequest->invoice_document),
        ];
    }
}

