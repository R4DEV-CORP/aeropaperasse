<?php

namespace App\Forms;

use App\Validators\ActivityRequestValidator;
use Illuminate\Validation\Validator;

/**
 * Gestionnaire de validation pour les formulaires de demande d'activité
 * Simplifie la logique de validation du composant Livewire
 */
class ActivityRequestFormValidator
{
    /**
     * Valide les données du formulaire selon le contexte
     *
     * @param  bool  $isDraft  Si c'est un brouillon
     * @param  bool  $isUpdate  Si c'est une mise à jour
     * @param  array  $existingDocuments  Documents existants (pour mise à jour)
     * @param  \App\Models\Client|null  $client  Client pour la validation conditionnelle du CTA
     */
    public static function validate(
        ActivityRequestFormData $formData,
        bool $isDraft = false,
        bool $isUpdate = false,
        array $existingDocuments = [],
        ?\App\Models\Client $client = null
    ): Validator {
        $data = $formData->toArray();

        // Déterminer la méthode de validation appropriée
        if ($isUpdate && ! $isDraft) {
            // Mise à jour complète d'un brouillon (soumission finale)
            return ActivityRequestValidator::validateUpdate(
                $data,
                $formData->isRenewal(),
                $existingDocuments,
                $client
            );
        } elseif ($isDraft) {
            // Enregistrement en brouillon (création ou mise à jour)
            return ActivityRequestValidator::validateDraft(
                $data,
                $formData->isRenewal(),
                $client
            );
        } else {
            // Création complète (nouvelle demande)
            return ActivityRequestValidator::validateComplete(
                $data,
                $formData->isRenewal(),
                $client
            );
        }
    }

    /**
     * Valide et retourne les erreurs formatées pour Livewire
     *
     * @return array|null Retourne null si pas d'erreurs, sinon array des erreurs
     */
    public static function validateAndGetErrors(
        ActivityRequestFormData $formData,
        bool $isDraft = false,
        bool $isUpdate = false,
        array $existingDocuments = []
    ): ?array {
        $validator = self::validate($formData, $isDraft, $isUpdate, $existingDocuments);

        if ($validator->fails()) {
            return $validator->errors()->messages();
        }

        return null;
    }

    /**
     * Valide rapidement sans retourner les détails
     */
    public static function isValid(
        ActivityRequestFormData $formData,
        bool $isDraft = false,
        bool $isUpdate = false,
        array $existingDocuments = []
    ): bool {
        $validator = self::validate($formData, $isDraft, $isUpdate, $existingDocuments);

        return ! $validator->fails();
    }
}
