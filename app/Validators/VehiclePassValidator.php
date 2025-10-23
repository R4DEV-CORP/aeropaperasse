<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class VehiclePassValidator
{
    /**
     * Règles de validation pour la création complète d'un laissez-passer véhicule
     */
    public static function getCompleteRules(): array
    {
        return [
            // Informations du véhicule (obligatoires)
            'plate_number' => 'required|string|max:255',
            'car_brand' => 'required|string|max:255',
            'airport' => 'required|in:ORY,CDG,LBG',

            // Documents (obligatoires)
            'certificate_of_registration' => 'required|file|mimes:pdf|max:8192',
            'company_stamp' => 'required|file|mimes:pdf|max:8192',
        ];
    }

    /**
     * Messages d'erreur pour les règles de validation
     */
    public static function getMessages(): array
    {
        return [
            // Informations du véhicule
            'plate_number.required' => 'Le numéro de plaque est obligatoire et ne doit pas dépasser 255 caractères',
            'plate_number.string' => 'Le numéro de plaque doit être une chaîne de caractères',
            'plate_number.max' => 'Le numéro de plaque ne doit pas dépasser 255 caractères',
            'car_brand.required' => 'La marque du véhicule est obligatoire et ne doit pas dépasser 255 caractères',
            'car_brand.string' => 'La marque du véhicule doit être une chaîne de caractères',
            'car_brand.max' => 'La marque du véhicule ne doit pas dépasser 255 caractères',
            'airport.required' => 'L\'aéroport est obligatoire',
            'airport.in' => 'L\'aéroport sélectionné n\'est pas valide',

            // Documents
            'certificate_of_registration.required' => 'Le certificat d\'immatriculation est obligatoire, doit être un fichier PDF et ne pas dépasser 8MB',
            'certificate_of_registration.file' => 'Le certificat d\'immatriculation doit être un fichier',
            'certificate_of_registration.mimes' => 'Le certificat d\'immatriculation doit être un fichier PDF',
            'certificate_of_registration.max' => 'Le certificat d\'immatriculation ne doit pas dépasser 8MB',
            'company_stamp.required' => 'Le tampon de l\'entreprise est obligatoire, doit être un fichier PDF et ne pas dépasser 8MB',
            'company_stamp.file' => 'Le tampon de l\'entreprise doit être un fichier',
            'company_stamp.mimes' => 'Le tampon de l\'entreprise doit être un fichier PDF',
            'company_stamp.max' => 'Le tampon de l\'entreprise ne doit pas dépasser 8MB',
        ];
    }

    /**
     * Fonction principale de validation pour la création complète
     */
    public static function validate(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, self::getCompleteRules(), self::getMessages());
    }
}
