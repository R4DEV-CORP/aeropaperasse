<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class ClientValidator
{
    /**
     * Règles de validation poru la création d'un client et ses contacts
     */
    public static function getRules(): array
    {
        return [
            // Informations de la société
            'company_name' => 'required|string|max:255',
            'trade_name' => 'required|string|max:255',
            'siret_number' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'subcontractor_of' => 'nullable|string|max:1023',
            
            // Documents obligatoires
            'kbis_document' => 'required|file|mimes:pdf|max:8192',
            'safety_document' => 'required|file|mimes:pdf|max:8192',
            'security_document' => 'required|file|mimes:pdf|max:8192',
            
            // Configuration des quotas
            'badge_limit' => 'required|integer|min:1|max:1000',
            'vehicle_pass_limit' => 'required|integer|min:0|max:1000',
            
            // Email de notification (optionnel)
            'notification_email' => 'nullable|email|max:255',
            
            // Référent sûreté 1 (obligatoire)
            'safety_referent_1_prenom' => 'required|string|max:255',
            'safety_referent_1_nom' => 'required|string|max:255',
            'safety_referent_1_email' => 'required|email|max:255',
            'safety_referent_1_phone' => 'required|string|max:255',
            
            // Référent sûreté 2 (optionnel)
            'safety_referent_2_prenom' => 'nullable|string|max:255',
            'safety_referent_2_nom' => 'nullable|string|max:255',
            'safety_referent_2_email' => 'nullable|email|max:255',
            'safety_referent_2_phone' => 'nullable|string|max:255',
            
            // Référent sûreté 3 (optionnel)
            'safety_referent_3_prenom' => 'nullable|string|max:255',
            'safety_referent_3_nom' => 'nullable|string|max:255',
            'safety_referent_3_email' => 'nullable|email|max:255',
            'safety_referent_3_phone' => 'nullable|string|max:255',
            
            // Correspondant sécurité (obligatoire)
            'security_correspondent_prenom' => 'required|string|max:255',
            'security_correspondent_nom' => 'required|string|max:255',
            'security_correspondent_email' => 'required|email|max:255',
            'security_correspondent_phone' => 'required|string|max:255',
            
            // Contact RH (obligatoire)
            'hr_contact_prenom' => 'required|string|max:255',
            'hr_contact_nom' => 'required|string|max:255',
            'hr_contact_email' => 'required|email|max:255',
            'hr_contact_phone' => 'required|string|max:255',
        ];
    }

    /**
     * Messages d'erreur pour les règles de validation
     */
    public static function getMessages(): array
    {
        return [
            // Informations de la société
            'company_name.required' => 'Le nom de l\'entreprise est obligatoire et ne doit pas dépasser 255 caractères',
            'trade_name.required' => 'Le nom commercial est obligatoire et ne doit pas dépasser 255 caractères',
            'siret_number.required' => 'Le numéro SIRET est obligatoire et ne doit pas dépasser 255 caractères',
            'address.required' => 'L\'adresse est obligatoire et ne doit pas dépasser 255 caractères',
            'zip_code.required' => 'Le code postal est obligatoire et ne doit pas dépasser 255 caractères',
            'city.required' => 'La ville est obligatoire et ne doit pas dépasser 255 caractères',
            
            // Documents
            'kbis_document.required' => 'Le document KBIS est obligatoire, doit être un fichier PDF et ne pas dépasser 8MB',
            'safety_document.required' => 'Le document de sécurité est obligatoire, doit être un fichier PDF et ne pas dépasser 8MB',
            'security_document.required' => 'Le document de sûreté est obligatoire, doit être un fichier PDF et ne pas dépasser 8MB',
            
            // Quotas
            'badge_limit.required' => 'La limite de badges est obligatoire et doit être un nombre entre 1 et 1000',
            'vehicle_pass_limit.required' => 'La limite de laissez-passer véhicules est obligatoire et doit être un nombre entre 0 et 1000',
            
            // Email de notification
            'notification_email.email' => 'L\'email de notification doit être une adresse email valide et ne pas dépasser 255 caractères',
            
            // Référent sûreté 1
            'safety_referent_1_prenom.required' => 'Le prénom du référent sûreté 1 est obligatoire et ne doit pas dépasser 255 caractères',
            'safety_referent_1_nom.required' => 'Le nom du référent sûreté 1 est obligatoire et ne doit pas dépasser 255 caractères',
            'safety_referent_1_email.required' => 'L\'email du référent sûreté 1 est obligatoire et doit être une adresse email valide et ne pas dépasser 255 caractères',
            'safety_referent_1_phone.required' => 'Le téléphone du référent sûreté 1 est obligatoire et ne doit pas dépasser 255 caractères',
            
            // Correspondant sécurité
            'security_correspondent_prenom.required' => 'Le prénom du correspondant sécurité est obligatoire et ne doit pas dépasser 255 caractères',
            'security_correspondent_nom.required' => 'Le nom du correspondant sécurité est obligatoire et ne doit pas dépasser 255 caractères',
            'security_correspondent_email.required' => 'L\'email du correspondant sécurité est obligatoire et doit être une adresse email valide et ne pas dépasser 255 caractères',
            'security_correspondent_phone.required' => 'Le téléphone du correspondant sécurité est obligatoire et ne doit pas dépasser 255 caractères',
            
            // Contact RH
            'hr_contact_prenom.required' => 'Le prénom du contact RH est obligatoire et ne doit pas dépasser 255 caractères',
            'hr_contact_nom.required' => 'Le nom du contact RH est obligatoire et ne doit pas dépasser 255 caractères',
            'hr_contact_email.required' => 'L\'email du contact RH est obligatoire et doit être une adresse email valide et ne pas dépasser 255 caractères',
            'hr_contact_phone.required' => 'Le téléphone du contact RH est obligatoire et ne doit pas dépasser 255 caractères',
        ];
    }

    /**
     * Fonction principale de validation des règles de créationdes clients et ses contacts
     */
    public static function validate(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, self::getRules(), self::getMessages());
    }
}
