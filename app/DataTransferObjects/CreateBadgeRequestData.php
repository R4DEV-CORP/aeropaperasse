<?php

namespace App\DataTransferObjects;

use App\Forms\BadgeRequestFormData;
use Illuminate\Http\UploadedFile;

class CreateBadgeRequestData
{
    public function __construct(
        // Relations
        public int $activity_request_id,
        public int $coworker_id,

        // Documents (optionnels pour le brouillon)
        public ?UploadedFile $selfie_photo,
        public ?UploadedFile $identification_card,
        public ?UploadedFile $activity_authorization,
        public ?UploadedFile $for_document,
        public ?UploadedFile $formation_certificate_document,
        public ?UploadedFile $invoice_document,

        // Flags
        public bool $application_authorization,
        public bool $validate_training,

        // Metadata
        public int $client_id,
        public int $created_by,
        public bool $is_draft = false,
        public bool $is_resubmission = false,
    ) {}

    /**
     * Créer le DTO à partir d'un array
     */
    public static function fromArray(array $data, int $clientId, int $userId, bool $isDraft = false, bool $isResubmission = false): self
    {
        return new self(
            activity_request_id: (int) $data['activity_request_id'],
            coworker_id: (int) $data['coworker_id'],
            selfie_photo: $data['selfie_photo'] ?? null,
            identification_card: $data['identification_card'] ?? null,
            activity_authorization: $data['activity_authorization'] ?? null,
            for_document: $data['for_document'] ?? null,
            formation_certificate_document: $data['formation_certificate_document'] ?? null,
            invoice_document: $data['invoice_document'] ?? null,
            application_authorization: (bool) ($data['application_authorization'] ?? false),
            validate_training: (bool) ($data['validate_training'] ?? false),
            client_id: $clientId,
            created_by: $userId,
            is_draft: $isDraft,
            is_resubmission: $isResubmission,
        );
    }

    /**
     * Créer le DTO à partir du BadgeRequestFormData
     */
    public static function fromFormData(
        BadgeRequestFormData $formData,
        int $clientId,
        int $userId,
        bool $isDraft = false,
        bool $isResubmission = false
    ): self {
        return new self(
            activity_request_id: $formData->activity_request_id,
            coworker_id: $formData->coworker_id,
            selfie_photo: $formData->selfie_photo,
            identification_card: $formData->identification_card,
            activity_authorization: $formData->activity_authorization,
            for_document: $formData->for_document,
            formation_certificate_document: $formData->formation_certificate_document,
            invoice_document: $formData->invoice_document,
            application_authorization: $formData->application_authorization,
            validate_training: $formData->validate_training,
            client_id: $clientId,
            created_by: $userId,
            is_draft: $isDraft,
            is_resubmission: $isResubmission,
        );
    }

    /**
     * Retourne les données de la demande de badge pour la base de données
     */
    public function getBadgeRequestData(): array
    {
        $data = [
            'activity_request_id' => $this->activity_request_id,
            'coworker_id' => $this->coworker_id,
            'created_by' => $this->created_by,
            'application_authorization' => $this->application_authorization,
            'validate_training' => $this->validate_training,
        ];

        // Ajouter les champs spécifiques au brouillon
        if ($this->is_draft) {
            $data['status'] = 'draft';
            $data['draft_at'] = now();

            return $data;
        }

        $data['status'] = 'pending_rem';
        $data['pending_rem_at'] = now();

        if ($this->is_resubmission) {
            $data['reject_reason'] = null;
        }

        return $data;
    }

    /**
     * Retourne les documents à enregistrer
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
     * Vérifie si des documents sont présents
     */
    public function hasDocuments(): bool
    {
        return ! empty($this->getDocuments());
    }
}
