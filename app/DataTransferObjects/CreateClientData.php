<?php

namespace App\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class CreateClientData
{
    public function __construct(
        // Informations de la société
        public string $company_name,
        public string $trade_name,
        public string $siret_number,
        public string $address,
        public string $zip_code,
        public string $city,
        public ?string $subcontractor_of,

        // Documents
        public UploadedFile $kbis_document,
        public UploadedFile $safety_document,
        public UploadedFile $security_document,

        // Configuration des quotas
        public int $badge_limit,
        public int $vehicle_pass_limit,

        // Email de notification
        public ?string $notification_email,

        // Contacts
        public array $contacts,
    ) {}

    /**
     * Créer le DTO à partir d'un array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            company_name: $data['company_name'],
            trade_name: $data['trade_name'],
            siret_number: $data['siret_number'],
            address: $data['address'],
            zip_code: $data['zip_code'],
            city: $data['city'],
            subcontractor_of: $data['subcontractor_of'] ?? null,
            kbis_document: $data['kbis_document'],
            safety_document: $data['safety_document'],
            security_document: $data['security_document'],
            badge_limit: (int) $data['badge_limit'],
            vehicle_pass_limit: (int) $data['vehicle_pass_limit'],
            notification_email: $data['notification_email'] ?? null,
            contacts: self::extractContacts($data),
        );
    }

    /**
     * Extraire les contacts du formulaire pour en faire un array
     */
    protected static function extractContacts(array $data): array
    {
        $contacts = [];

        // Référent sûreté 1 (obligatoire)
        $contacts[] = [
            'firstname' => $data['safety_referent_1_prenom'],
            'lastname' => $data['safety_referent_1_nom'],
            'email' => $data['safety_referent_1_email'],
            'phone' => $data['safety_referent_1_phone'],
            'role' => 'safety',
        ];

        // Référent sûreté 2 (optionnel)
        // SI au moins une donne n'est pas vide, on créer le contact
        if (! empty($data['safety_referent_2_prenom']) ||
            ! empty($data['safety_referent_2_nom']) ||
            ! empty($data['safety_referent_2_email']) ||
            ! empty($data['safety_referent_2_phone'])) {
            $contacts[] = [
                'firstname' => $data['safety_referent_2_prenom'] ?? '',
                'lastname' => $data['safety_referent_2_nom'] ?? '',
                'email' => $data['safety_referent_2_email'] ?? '',
                'phone' => $data['safety_referent_2_phone'] ?? '',
                'role' => 'safety',
            ];
        }

        // Référent sûreté 3 (optionnel)
        if (! empty($data['safety_referent_3_prenom']) ||
            ! empty($data['safety_referent_3_nom']) ||
            ! empty($data['safety_referent_3_email']) ||
            ! empty($data['safety_referent_3_phone'])) {
            $contacts[] = [
                'firstname' => $data['safety_referent_3_prenom'] ?? '',
                'lastname' => $data['safety_referent_3_nom'] ?? '',
                'email' => $data['safety_referent_3_email'] ?? '',
                'phone' => $data['safety_referent_3_phone'] ?? '',
                'role' => 'safety',
            ];
        }

        // Correspondant sécurité (obligatoire)
        $contacts[] = [
            'firstname' => $data['security_correspondent_prenom'],
            'lastname' => $data['security_correspondent_nom'],
            'email' => $data['security_correspondent_email'],
            'phone' => $data['security_correspondent_phone'],
            'role' => 'security',
        ];

        // Contact RH (obligatoire)
        $contacts[] = [
            'firstname' => $data['hr_contact_prenom'],
            'lastname' => $data['hr_contact_nom'],
            'email' => $data['hr_contact_email'],
            'phone' => $data['hr_contact_phone'],
            'role' => 'hr',
        ];

        return $contacts;
    }

    /**
     * Retourne les données du client pour la base de données
     */
    public function getClientData(): array
    {
        return [
            'company_name' => $this->company_name,
            'trade_name' => $this->trade_name,
            'siret_number' => $this->siret_number,
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'city' => $this->city,
            'subcontractor_of' => $this->subcontractor_of,
            'badge_limit' => $this->badge_limit,
            'vehicle_pass_limit' => $this->vehicle_pass_limit,
            'notification_email' => $this->notification_email,
        ];
    }

    /**
     * Retourn les document à enregistrer
     */
    public function getDocuments(): array
    {
        return [
            'kbis_document' => $this->kbis_document,
            'safety_document' => $this->safety_document,
            'security_document' => $this->security_document,
        ];
    }

    /**
     * Retourne les données des contacts
     */
    public function getContacts(): array
    {
        return $this->contacts;
    }
}
