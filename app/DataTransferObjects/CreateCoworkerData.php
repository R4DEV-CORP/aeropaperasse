<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

class CreateCoworkerData
{
    public function __construct(
        // Informations du collaborateur
        public string $firstname,
        public string $lastname,
        public string $email,
        public string $phone,
        public int $client_id,
        
        // Options du collaborateur
        public bool $can_access_formation = false,
        public bool $has_leave = false,
        public ?Carbon $departure_date = null,
        
        // Informations utilisateur (optionnelles)
        public bool $create_user = false,
        public ?string $password = null,
        public ?string $role = null,
        
        // Metadata
        public int $created_by,
    ) {}

    /**
     * Créer le DTO à partir d'un array
     */
    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            firstname: $data['firstname'],
            lastname: $data['lastname'],
            email: $data['email'],
            phone: $data['phone'],
            client_id: (int) $data['client_id'],
            can_access_formation: (bool) ($data['can_access_formation'] ?? false),
            has_leave: (bool) ($data['has_leave'] ?? false),
            departure_date: isset($data['departure_date']) && $data['departure_date'] 
                ? Carbon::parse($data['departure_date']) 
                : null,
            create_user: (bool) ($data['create_user'] ?? false),
            password: $data['password'] ?? null,
            role: $data['role'] ?? 'client',
            created_by: $userId,
        );
    }

    /**
     * Retourne les données du collaborateur pour la base de données
     */
    public function getCoworkerData(): array
    {
        $data = [
            'client_id' => $this->client_id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'can_access_formation' => $this->can_access_formation,
            'has_leave' => $this->has_leave,
            'departure_date' => $this->departure_date,
        ];

        // Filtrer uniquement les valeurs null (garder false et 0)
        return array_filter($data, function ($value) {
            return ! is_null($value);
        });
    }

    /**
     * Retourne les données de l'utilisateur pour la base de données
     * Retourne null si create_user = false
     */
    public function getUserData(): ?array
    {
        if (!$this->create_user) {
            return null;
        }

        return [
            'name' => $this->firstname . ' ' . $this->lastname,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role ?? 'client',
            'is_new' => true,
            'client_id' => $this->client_id,
        ];
    }

    /**
     * Vérifie si un utilisateur doit être créé
     */
    public function shouldCreateUser(): bool
    {
        return $this->create_user && !empty($this->password);
    }

    /**
     * Retourne le nom complet du collaborateur
     */
    public function getFullName(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Vérifie si le collaborateur a une date de départ définie
     */
    public function hasDepartureDate(): bool
    {
        return !is_null($this->departure_date);
    }

    /**
     * Retourne les données pour le log
     */
    public function getLogData(): array
    {
        return [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'client_id' => $this->client_id,
            'create_user' => $this->create_user,
            'can_access_formation' => $this->can_access_formation,
            'has_leave' => $this->has_leave,
            'departure_date' => $this->departure_date?->format('Y-m-d'),
            'created_by' => $this->created_by,
        ];
    }
}
