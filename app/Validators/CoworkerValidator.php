<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

class CoworkerValidator
{
    /**
     * Règles de validation pour la création complète d'un collaborateur
     */
    public static function getCompleteRules(): array
    {
        return [
            // Informations du collaborateur (obligatoires)
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:coworkers,email',
            'phone' => 'nullable|string|max:255',
            'client_id' => 'required|integer|exists:clients,id',

            // Options du collaborateur
            'can_access_formation' => 'nullable|boolean',
            'has_leave' => 'nullable|boolean',
            'departure_date' => 'nullable|date|after:today',

            // Informations utilisateur (optionnelles si create_user = false)
            'create_user' => 'nullable|boolean',
            'password' => 'required_if:create_user,true|string|min:8|confirmed',
            'password_confirmation' => 'required_if:create_user,true|string|min:8',
            'role' => 'nullable|string|in:sclient,sadmin,client,admin,aclient',
        ];
    }

    /**
     * Messages d'erreur pour les règles de validation
     */
    public static function getMessages(): array
    {
        return [
            // Informations du collaborateur
            'firstname.required' => 'Le prénom est obligatoire',
            'firstname.string' => 'Le prénom doit être une chaîne de caractères',
            'firstname.max' => 'Le prénom ne doit pas dépasser 255 caractères',

            'lastname.required' => 'Le nom est obligatoire',
            'lastname.string' => 'Le nom doit être une chaîne de caractères',
            'lastname.max' => 'Le nom ne doit pas dépasser 255 caractères',

            'email.required' => 'L\'email est obligatoire pour créer un compte utilisateur',
            'email.email' => 'L\'email doit être une adresse email valide',
            'email.max' => 'L\'email ne doit pas dépasser 255 caractères',
            'email.unique' => 'Cette adresse email est déjà utilisée',

            'phone.string' => 'Le téléphone doit être une chaîne de caractères',
            'phone.max' => 'Le téléphone ne doit pas dépasser 255 caractères',

            'client_id.required' => 'Le client est obligatoire',
            'client_id.integer' => 'L\'identifiant du client doit être un nombre',
            'client_id.exists' => 'Le client sélectionné n\'existe pas',

            // Options du collaborateur
            'can_access_formation.boolean' => 'L\'accès à la formation doit être vrai ou faux',
            'has_leave.boolean' => 'Le statut de départ doit être vrai ou faux',
            'departure_date.date' => 'La date de départ doit être une date valide',
            'departure_date.after' => 'La date de départ doit être postérieure à aujourd\'hui',

            // Informations utilisateur
            'create_user.boolean' => 'La création d\'utilisateur doit être vraie ou fausse',
            'password.required_if' => 'Le mot de passe est obligatoire si vous créez un utilisateur',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas',

            'password_confirmation.required_if' => 'La confirmation du mot de passe est obligatoire si vous créez un utilisateur',
            'password_confirmation.string' => 'La confirmation du mot de passe doit être une chaîne de caractères',
            'password_confirmation.min' => 'La confirmation du mot de passe doit contenir au moins 8 caractères',

            'role.string' => 'Le rôle doit être une chaîne de caractères',
            'role.in' => 'Le rôle sélectionné n\'est pas valide',
        ];
    }

    /**
     * Fonction principale de validation pour la création complète
     */
    public static function validateComplete(array $data): \Illuminate\Validation\Validator
    {
        $rules = self::getCompleteRules();

        $createUser = $data['create_user'] ?? false;

        if ($createUser) {
            // Un email est requis pour créer un compte ; unique uniquement dans users (pas coworkers)
            $rules['email'] = 'required|email|max:255|unique:users,email';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
            $rules['password_confirmation'] = 'nullable|string|min:8';
        }

        return Validator::make($data, $rules, self::getMessages());
    }

    /**
     * Validation conditionnelle pour les données de création d'utilisateur
     */
    public static function validateUserCreation(array $data): \Illuminate\Validation\Validator
    {
        $rules = [
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'role' => 'nullable|string|in:sclient,sadmin,client,admin,aclient',
        ];

        $messages = [
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être une adresse email valide',
            'email.max' => 'L\'email ne doit pas dépasser 255 caractères',
            'email.unique' => 'Cette adresse email est déjà utilisée',

            'password.required' => 'Le mot de passe est obligatoire',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas',

            'password_confirmation.required' => 'La confirmation du mot de passe est obligatoire',
            'password_confirmation.string' => 'La confirmation du mot de passe doit être une chaîne de caractères',
            'password_confirmation.min' => 'La confirmation du mot de passe doit contenir au moins 8 caractères',

            'role.string' => 'Le rôle doit être une chaîne de caractères',
            'role.in' => 'Le rôle sélectionné n\'est pas valide',
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Validation pour les données du collaborateur uniquement (sans utilisateur)
     */
    public static function validateCoworkerOnly(array $data): \Illuminate\Validation\Validator
    {
        $rules = [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:coworkers,email',
            'phone' => 'nullable|string|max:255',
            'client_id' => 'required|integer|exists:clients,id',
            'can_access_formation' => 'nullable|boolean',
            'has_leave' => 'nullable|boolean',
            'departure_date' => 'nullable|date|after:today',
        ];

        return Validator::make($data, $rules, self::getMessages());
    }

    /**
     * Validation pour la mise à jour d'un collaborateur
     */
    public static function validateUpdate(array $data, $coworkerId): \Illuminate\Validation\Validator
    {
        $rules = [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:coworkers,email,'.$coworkerId,
            'phone' => 'nullable|string|max:255',
            'can_access_formation' => 'nullable|boolean',
            'role' => 'nullable|string|in:sclient,sadmin,client,admin,aclient',
        ];

        $messages = [
            'firstname.required' => 'Le prénom est obligatoire',
            'firstname.string' => 'Le prénom doit être une chaîne de caractères',
            'firstname.max' => 'Le prénom ne doit pas dépasser 255 caractères',

            'lastname.required' => 'Le nom est obligatoire',
            'lastname.string' => 'Le nom doit être une chaîne de caractères',
            'lastname.max' => 'Le nom ne doit pas dépasser 255 caractères',

            'email.email' => 'L\'email doit être une adresse email valide',
            'email.max' => 'L\'email ne doit pas dépasser 255 caractères',
            'email.unique' => 'Cette adresse email est déjà utilisée par un autre collaborateur',

            'phone.string' => 'Le téléphone doit être une chaîne de caractères',
            'phone.max' => 'Le téléphone ne doit pas dépasser 255 caractères',

            'can_access_formation.boolean' => 'L\'accès à la formation doit être vrai ou faux',

            'role.string' => 'Le rôle doit être une chaîne de caractères',
            'role.in' => 'Le rôle sélectionné n\'est pas valide',
        ];

        return Validator::make($data, $rules, $messages);
    }
}
