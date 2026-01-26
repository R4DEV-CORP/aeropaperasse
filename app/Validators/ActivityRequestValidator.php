<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class ActivityRequestValidator
{
    /**
     * Règles de validation pour la création complète d'une demande d'activité
     */
    public static function getCompleteRules(bool $isRenewal = false, ?\App\Models\Client $client = null): array
    {
        // Si c'est un renouvellement, on valide SEULEMENT l'ID de la demande précédente
        if ($isRenewal) {
            return [
                'last_activity_request_id' => 'required|integer|exists:activity_requests,id',
            ];
        }

        // Sinon, validation complète classique
        $rules = [
            // Informations du responsable (obligatoires)
            'manager_firstname' => 'required|string|max:255',
            'manager_lastname' => 'required|string|max:255',
            'manager_email' => 'required|email|max:255',
            'manager_phone' => 'required|string|max:255',
            'manager_role' => 'required|string|max:255',

            // Informations sur l'activité (obligatoires)
            'airport' => 'required|in:ORY,CDG,LBG',
            'description' => 'required|string|max:2000',
            'customer_names' => 'required|string|max:2000',
            'person_count' => 'required|integer|min:1|max:1000',
            'vehicule_count' => 'required|integer|min:0|max:1000',

            // Documents (obligatoires)
            'aao_request_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'kbis_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'principals' => 'required|array|min:1',
            'principals.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'safety_referent_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'security_referent_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];

        // CTA conditionnel : requis uniquement si le client est une compagnie aérienne
        if ($client && $client->is_airline_company) {
            $rules['cta_document'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        return $rules;
    }

    /**
     * Règles de validation pour l'enregistrement en brouillon
     * Tous les champs sont optionnels
     */
    public static function getDraftRules(bool $isRenewal = false, ?\App\Models\Client $client = null): array
    {
        // Si c'est un renouvellement en brouillon, on valide SEULEMENT l'ID
        if ($isRenewal) {
            return [
                'last_activity_request_id' => 'required|integer|exists:activity_requests,id',
            ];
        }

        // Sinon, tous les champs sont optionnels
        $rules = [
            // Informations du responsable (optionnelles)
            'manager_firstname' => 'nullable|string|max:255',
            'manager_lastname' => 'nullable|string|max:255',
            'manager_email' => 'nullable|email|max:255',
            'manager_phone' => 'nullable|string|max:255',
            'manager_role' => 'nullable|string|max:255',

            // Informations sur l'activité (optionnelles)
            'airport' => 'nullable|in:ORY,CDG,LBG',
            'description' => 'nullable|string|max:2000',
            'customer_names' => 'nullable|string|max:2000',
            'person_count' => 'nullable|integer|min:1|max:1000',
            'vehicule_count' => 'nullable|integer|min:0|max:1000',

            // Documents (optionnels)
            'aao_request_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'kbis_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'principals' => 'nullable|array',
            'principals.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'safety_referent_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'security_referent_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'cta_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];

        return $rules;
    }

    /**
     * Messages d'erreur pour les règles de validation
     */
    public static function getMessages(): array
    {
        return [
            // Renouvellement
            'last_activity_request_id.required' => 'Vous devez sélectionner une demande précédente pour le renouvellement',
            'last_activity_request_id.integer' => 'L\'identifiant de la demande précédente doit être un nombre',
            'last_activity_request_id.exists' => 'La demande précédente sélectionnée n\'existe pas',

            // Informations du responsable
            'manager_firstname.required' => 'Le prénom du responsable est obligatoire et ne doit pas dépasser 255 caractères',
            'manager_lastname.required' => 'Le nom du responsable est obligatoire et ne doit pas dépasser 255 caractères',
            'manager_email.required' => 'L\'email du responsable est obligatoire et doit être une adresse email valide et ne pas dépasser 255 caractères',
            'manager_email.email' => 'L\'email du responsable doit être une adresse email valide',
            'manager_phone.required' => 'Le téléphone du responsable est obligatoire et ne doit pas dépasser 255 caractères',
            'manager_role.required' => 'La fonction du responsable est obligatoire et ne doit pas dépasser 255 caractères',

            // Informations sur l'activité
            'airport.required' => 'L\'aéroport est obligatoire',
            'airport.in' => 'L\'aéroport sélectionné n\'est pas valide',
            'description.required' => 'La description de l\'activité est obligatoire et ne doit pas dépasser 2000 caractères',
            'customer_names.required' => 'La dénomination des clients est obligatoire et ne doit pas dépasser 2000 caractères',
            'person_count.required' => 'Le nombre de personnes est obligatoire et doit être un nombre entre 1 et 1000',
            'person_count.integer' => 'Le nombre de personnes doit être un nombre entier',
            'person_count.min' => 'Le nombre de personnes doit être au minimum 1',
            'person_count.max' => 'Le nombre de personnes ne peut pas dépasser 1000',
            'vehicule_count.required' => 'Le nombre de véhicules est obligatoire et doit être un nombre entre 0 et 1000',
            'vehicule_count.integer' => 'Le nombre de véhicules doit être un nombre entier',
            'vehicule_count.min' => 'Le nombre de véhicules doit être au minimum 0',
            'vehicule_count.max' => 'Le nombre de véhicules ne peut pas dépasser 1000',

            // Documents
            'aao_request_document.required' => 'L\'attestation client est obligatoire, doit être un fichier PDF et ne pas dépasser 8MB',
            'aao_request_document.file' => 'L\'attestation client doit être un fichier',
            'aao_request_document.mimes' => 'L\'attestation client doit être un fichier PDF',
            'aao_request_document.max' => 'L\'attestation client ne doit pas dépasser 8MB',
            'kbis_document.required' => 'L\'agrément préfectoral est obligatoire, doit être un fichier PDF et ne pas dépasser 8MB',
            'kbis_document.file' => 'L\'agrément préfectoral doit être un fichier',
            'kbis_document.mimes' => 'L\'agrément préfectoral doit être un fichier PDF',
            'kbis_document.max' => 'L\'agrément préfectoral ne doit pas dépasser 8MB',
            'principals.required' => 'Au moins un document donneurs d\'ordre est obligatoire',
            'principals.array' => 'Les donneurs d\'ordre doivent être un tableau de fichiers',
            'principals.min' => 'Au moins un document donneurs d\'ordre est obligatoire',
            'principals.*.required' => 'Chaque fichier donneurs d\'ordre est obligatoire',
            'principals.*.file' => 'Chaque donneurs d\'ordre doit être un fichier',
            'principals.*.mimes' => 'Chaque donneurs d\'ordre doit être un fichier PDF, JPG, JPEG ou PNG',
            'principals.*.max' => 'Chaque donneurs d\'ordre ne doit pas dépasser 10MB',
            'safety_referent_document.required' => 'Le responsable de la sureté est obligatoire, doit être un fichier PDF et ne pas dépasser 8MB',
            'safety_referent_document.file' => 'Le responsable de la sureté doit être un fichier',
            'safety_referent_document.mimes' => 'Le responsable de la sureté doit être un fichier PDF',
            'safety_referent_document.max' => 'Le responsable de la sureté ne doit pas dépasser 8MB',
            'cta_document.required' => 'Le CTA est obligatoire pour les compagnies aériennes, doit être un fichier PDF, JPG, JPEG ou PNG et ne pas dépasser 10MB',
            'cta_document.file' => 'Le CTA doit être un fichier',
            'cta_document.mimes' => 'Le CTA doit être un fichier PDF, JPG, JPEG ou PNG',
            'cta_document.max' => 'Le CTA ne doit pas dépasser 10MB',
        ];
    }

    /**
     * Fonction principale de validation pour la création complète
     */
    public static function validateComplete(array $data, bool $isRenewal = false, ?\App\Models\Client $client = null): \Illuminate\Validation\Validator
    {
        return Validator::make($data, self::getCompleteRules($isRenewal, $client), self::getMessages());
    }

    /**
     * Fonction principale de validation pour l'enregistrement en brouillon
     */
    public static function validateDraft(array $data, bool $isRenewal = false, ?\App\Models\Client $client = null): \Illuminate\Validation\Validator
    {
        return Validator::make($data, self::getDraftRules($isRenewal, $client), self::getMessages());
    }

    /**
     * Règles de validation pour la mise à jour d'un brouillon existant avec des documents
     * Les documents sont optionnels s'ils existent déjà
     */
    public static function getUpdateRules(bool $isRenewal = false, array $existingDocuments = [], ?\App\Models\Client $client = null): array
    {
        if ($isRenewal) {
            return [
                'last_activity_request_id' => 'required|integer|exists:activity_requests,id',
            ];
        }

        // Règles de base (identiques à getCompleteRules)
        $rules = [
            // Informations du responsable (obligatoires)
            'manager_firstname' => 'required|string|max:255',
            'manager_lastname' => 'required|string|max:255',
            'manager_email' => 'required|email|max:255',
            'manager_phone' => 'required|string|max:255',
            'manager_role' => 'required|string|max:255',

            // Informations sur l'activité (obligatoires)
            'airport' => 'required|in:ORY,CDG,LBG',
            'description' => 'required|string|max:2000',
            'customer_names' => 'required|string|max:2000',
            'person_count' => 'required|integer|min:1|max:1000',
            'vehicule_count' => 'required|integer|min:0|max:1000',
        ];

        // Gestion des documents : obligatoires seulement s'ils n'existent pas déjà
        $documentFields = [
            'aao_request_document',
            'kbis_document',
            'principals',
            'safety_referent_document',
            'security_referent_document',
        ];

        foreach ($documentFields as $field) {
            if ($field === 'principals') {
                // Gestion spéciale pour principals (array)
                if (isset($existingDocuments['principals']) && $existingDocuments['principals']) {
                    $rules['principals'] = 'nullable|array';
                    $rules['principals.*'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
                } else {
                    $rules['principals'] = 'required|array|min:1';
                    $rules['principals.*'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
                }
            } else {
                if (isset($existingDocuments[$field]) && $existingDocuments[$field]) {
                    // Document existe déjà, il est optionnel
                    $rules[$field] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
                } else {
                    // Document n'existe pas, il est obligatoire
                    $rules[$field] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
                }
            }
        }

        // CTA conditionnel : requis uniquement si le client est une compagnie aérienne
        if ($client && $client->is_airline_company) {
            if (isset($existingDocuments['cta_document']) && $existingDocuments['cta_document']) {
                $rules['cta_document'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240';
            } else {
                $rules['cta_document'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            }
        }

        return $rules;
    }

    /**
     * Validation pour la mise à jour complète d'un brouillon
     */
    public static function validateUpdate(array $data, bool $isRenewal = false, array $existingDocuments = [], ?\App\Models\Client $client = null): \Illuminate\Validation\Validator
    {
        return Validator::make($data, self::getUpdateRules($isRenewal, $existingDocuments, $client), self::getMessages());
    }
}
