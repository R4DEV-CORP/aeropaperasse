<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class BadgeRequestValidator
{
    /**
     * Règles de validation pour la création complète d'une demande de badge
     */
    public static function getCompleteRules(): array
    {
        return [
            // Relations (obligatoires)
            'activity_request_id' => 'required|integer|exists:activity_requests,id',
            'coworker_id' => 'required|integer|exists:coworkers,id',

            // Flags (obligatoires)
            'application_authorization' => 'required|boolean',
            'validate_training' => 'required|boolean',

            // Documents (obligatoires sauf formation_certificate si validate_training = true)
            'selfie_photo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'identification_card' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'activity_authorization' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'for_document' => 'required|file|mimes:xlsx,xls|max:10240',
            'invoice_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            // Le certificat de formation est géré conditionnellement dans validateComplete()
        ];
    }

    /**
     * Règles de validation pour l'enregistrement en brouillon
     * Tous les champs sont optionnels sauf les relations
     */
    public static function getDraftRules(): array
    {
        return [
            // Relations (optionnelles pour le brouillon mais validées si présentes)
            'activity_request_id' => 'nullable|integer|exists:activity_requests,id',
            'coworker_id' => 'nullable|integer|exists:coworkers,id',

            // Flags (optionnels)
            'application_authorization' => 'nullable|boolean',
            'validate_training' => 'nullable|boolean',

            // Documents (optionnels)
            'selfie_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'identification_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'activity_authorization' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'for_document' => 'nullable|file|mimes:xlsx,xls|max:10240',
            'invoice_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'formation_certificate_document' => 'nullable|file|mimes:pdf|max:8192',
        ];
    }

    /**
     * Messages d'erreur pour les règles de validation
     */
    public static function getMessages(): array
    {
        return [
            // Relations
            'activity_request_id.required' => 'Vous devez sélectionner une demande d\'activité',
            'activity_request_id.integer' => 'L\'identifiant de la demande d\'activité doit être un nombre',
            'activity_request_id.exists' => 'La demande d\'activité sélectionnée n\'existe pas',
            'coworker_id.required' => 'Vous devez sélectionner un collaborateur',
            'coworker_id.integer' => 'L\'identifiant du collaborateur doit être un nombre',
            'coworker_id.exists' => 'Le collaborateur sélectionné n\'existe pas',

            // Flags
            'application_authorization.required' => 'Vous devez indiquer si une demande d\'habilitation est nécessaire',
            'application_authorization.boolean' => 'La demande d\'habilitation doit être vraie ou fausse',
            'validate_training.required' => 'Vous devez indiquer si vous validez la formation',
            'validate_training.boolean' => 'La validation de la formation doit être vraie ou fausse',

            // Documents
            'selfie_photo.required' => 'La photo d\'identité est obligatoire',
            'selfie_photo.file' => 'La photo d\'identité doit être un fichier',
            'selfie_photo.mimes' => 'La photo d\'identité doit être au format JPG, JPEG, PNG ou PDF',
            'selfie_photo.max' => 'La photo d\'identité ne doit pas dépasser 8MB',

            'identification_card.required' => 'La carte d\'identité est obligatoire',
            'identification_card.file' => 'La carte d\'identité doit être un fichier',
            'identification_card.mimes' => 'La carte d\'identité doit être un fichier PDF, JPG ou PNG',
            'identification_card.max' => 'La carte d\'identité ne doit pas dépasser 8MB',

            'activity_authorization.required' => 'L\'autorisation d\'activité est obligatoire',
            'activity_authorization.file' => 'L\'autorisation d\'activité doit être un fichier',
            'activity_authorization.mimes' => 'L\'autorisation d\'activité doit être un fichier PDF, JPG ou PNG',
            'activity_authorization.max' => 'L\'autorisation d\'activité ne doit pas dépasser 8MB',

            'for_document.required' => 'Le document FOR est obligatoire',
            'for_document.file' => 'Le document FOR doit être un fichier',
            'for_document.mimes' => 'Le document FOR doit être un fichier XLSX ou XLS',
            'for_document.max' => 'Le document FOR ne doit pas dépasser 8MB',

            'formation_certificate_document.required' => 'Le certificat de formation est obligatoire si vous ne validez pas la formation',
            'formation_certificate_document.file' => 'Le certificat de formation doit être un fichier',
            'formation_certificate_document.mimes' => 'Le certificat de formation doit être un fichier PDF, JPG ou PNG',
            'formation_certificate_document.max' => 'Le certificat de formation ne doit pas dépasser 8MB',

            'invoice_document.file' => 'La facture doit être un fichier',
            'invoice_document.mimes' => 'La facture doit être un fichier PDF, JPG ou PNG',
            'invoice_document.max' => 'La facture ne doit pas dépasser 8MB',
        ];
    }

    /**
     * Fonction principale de validation pour la création complète
     */
    public static function validateComplete(array $data): \Illuminate\Validation\Validator
    {
        $rules = self::getCompleteRules();

        // Gestion conditionnelle du certificat de formation
        // Obligatoire uniquement si validate_training = false
        $validateTraining = $data['validate_training'] ?? false;
        if ($validateTraining) {
            $rules['formation_certificate_document'] = 'nullable|file|mimes:pdf|max:8192';
        } else {
            $rules['formation_certificate_document'] = 'required|file|mimes:pdf|max:8192';
        }

        return Validator::make($data, $rules, self::getMessages());
    }

    /**
     * Fonction principale de validation pour l'enregistrement en brouillon
     */
    public static function validateDraft(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, self::getDraftRules(), self::getMessages());
    }

    /**
     * Règles de validation pour la mise à jour d'un brouillon existant avec des documents
     * Les documents sont optionnels s'ils existent déjà
     */
    public static function getUpdateRules(array $existingDocuments = []): array
    {
        // Règles de base (identiques à getCompleteRules)
        $rules = [
            // Relations (obligatoires)
            'activity_request_id' => 'required|integer|exists:activity_requests,id',
            'coworker_id' => 'required|integer|exists:coworkers,id',

            // Flags (obligatoires)
            'application_authorization' => 'required|boolean',
            'validate_training' => 'required|boolean',
        ];

        // Gestion des documents : obligatoires seulement s'ils n'existent pas déjà
        $documentFields = [
            'selfie_photo' => 'jpg,jpeg,png,pdf',
            'identification_card' => 'pdf,jpg,jpeg,png',
            'activity_authorization' => 'pdf,jpg,jpeg,png',
            'for_document' => 'xlsx,xls',
            'formation_certificate_document' => 'pdf,jpg,jpeg,png',
        ];

        foreach ($documentFields as $field => $mimes) {
            if (isset($existingDocuments[$field]) && $existingDocuments[$field]) {
                // Document existe déjà, il est optionnel
                $rules[$field] = "nullable|file|mimes:{$mimes}|max:10240";
            } else {
                // Document n'existe pas, il est obligatoire
                // Note: formation_certificate_document sera géré conditionnellement dans validateUpdate()
                if ($field === 'formation_certificate_document') {
                    continue; // Géré plus tard
                }
                $rules[$field] = "required|file|mimes:{$mimes}|max:10240";
            }
        }

        // invoice_document est toujours optionnel
        $rules['invoice_document'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';

        return $rules;
    }

    /**
     * Validation pour la mise à jour complète d'un brouillon
     */
    public static function validateUpdate(array $data, array $existingDocuments = []): \Illuminate\Validation\Validator
    {
        $rules = self::getUpdateRules($existingDocuments);

        // Gestion conditionnelle du certificat de formation pour la mise à jour
        $validateTraining = $data['validate_training'] ?? false;
        $hasExistingCertificate = $existingDocuments['formation_certificate_document'] ?? false;

        if ($validateTraining) {
            // Si validate_training = true, le certificat n'est pas obligatoire
            $rules['formation_certificate_document'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
        } else {
            // Si validate_training = false
            if ($hasExistingCertificate) {
                // Document existe déjà, il est optionnel (on peut garder l'existant)
                $rules['formation_certificate_document'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
            } else {
                // Document n'existe pas, il est obligatoire
                $rules['formation_certificate_document'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            }
        }

        return Validator::make($data, $rules, self::getMessages());
    }
}
