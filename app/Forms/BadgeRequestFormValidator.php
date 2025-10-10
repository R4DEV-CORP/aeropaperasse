<?php

namespace App\Forms;

use App\Validators\BadgeRequestValidator;
use Illuminate\Validation\Validator;

/**
 * Gestionnaire de validation pour les formulaires de demande de badge
 * Simplifie la logique de validation du composant Livewire
 */
class BadgeRequestFormValidator
{
    /**
     * Valide les données du formulaire selon le contexte
     *
     * @param  bool  $isDraft  Si c'est un brouillon
     * @param  bool  $isUpdate  Si c'est une mise à jour
     * @param  array  $existingDocuments  Documents existants (pour mise à jour)
     */
    public static function validate(
        BadgeRequestFormData $formData,
        bool $isDraft = false,
        bool $isUpdate = false,
        array $existingDocuments = []
    ): Validator {
        $data = $formData->toArray();

        // Déterminer la méthode de validation appropriée
        if ($isUpdate && ! $isDraft) {
            // Mise à jour complète d'un brouillon (soumission finale)
            return BadgeRequestValidator::validateUpdate(
                $data,
                $existingDocuments
            );
        } elseif ($isDraft) {
            // Enregistrement en brouillon (création ou mise à jour)
            return BadgeRequestValidator::validateDraft($data);
        } else {
            // Création complète (nouvelle demande)
            return BadgeRequestValidator::validateComplete($data);
        }
    }

    /**
     * Valide et retourne les erreurs formatées pour Livewire
     *
     * @return array|null Retourne null si pas d'erreurs, sinon array des erreurs
     */
    public static function validateAndGetErrors(
        BadgeRequestFormData $formData,
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
        BadgeRequestFormData $formData,
        bool $isDraft = false,
        bool $isUpdate = false,
        array $existingDocuments = []
    ): bool {
        $validator = self::validate($formData, $isDraft, $isUpdate, $existingDocuments);

        return ! $validator->fails();
    }
}

