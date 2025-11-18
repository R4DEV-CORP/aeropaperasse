<?php

namespace App\Services;

use App\Models\ActivityRequest;
use App\Models\Client;
use Spatie\LaravelPdf\Facades\Pdf;

class ClientOverviewPdfService
{
    /**
     * Point d'entrée principal - génère le PDF
     */
    public function generateOverview(int $clientId)
    {
        // 1. Récupérer les données formatées
        $clientData = $this->getClientData($clientId);

        // 2. Générer le PDF
        return Pdf::view('pdf.client-overview', ['client' => $clientData])
            ->format('a4')
            ->name("Bilan_{$clientData['company_name']}_".date('Y-m-d').'.pdf');
    }

    /**
     * Récupère et formate les données client
     */
    private function getClientData(int $clientId): array
    {
        // Récupération du client
        $client = Client::with('contacts')->with('activityRequests')->findOrFail($clientId);

        // contacts
        $clientContacts = $client->contacts;
        $contacts = [];
        foreach ($clientContacts as $contact) {
            $contacts[$contact->role] = $contact;
        }

        // Activités
        $clientActivityRequests = $client->activityRequests;
        $activityRequests = [];
        $activityAirports = [
            "CDG" => 0,
            "ORY" => 0,
            "LBG" => 0,
        ];
        foreach ($clientActivityRequests as $activityRequest) {
            if($activityRequest->status !== 'approved') {
                $activityRequests[$activityRequest->id] = [
                    'description' => $activityRequest->description,
                ];
                $activityAirports[$activityRequest->airport]++;
            }
        }

        // Compilation des données (priorité aux données Client, fallback sur ActivityRequest)
        return [
            // Info sur la société
            'id' => $client->id,
            'company_name' => $client->company_name, // raison sociale
            'trade_name' => $client->trade_name, // nom commercial
            'siret_number' => $client->siret_number, // numéro SIRET
            'address' => $client->address, // adresse
            'zip_code' => $client->zip_code, // code postal
            'city' => $client->city, // ville
            'subcontractor_of' => $client->subcontractor_of, // sous traitant de
            'badge_limit' => $client->badge_limit, // limite de badges
            'vehicle_pass_limit' => $client->vehicle_pass_limit, // limite de véhicules

            // Contacts (sureté, hr et sécurité)
            'contacts' => $contacts,

            // Activités
            'activities' => $activityRequests,
            'activity_count' => count($activityRequests),
            'activity_airports' => $activityAirports, // aéroports concernés

            // Badges et laissez passer
            'badge_count' => $client->active_badges_count, // nombre de badges actifs
            'vehicle_pass_count' => $client->active_vehicle_passes_count, // nombre de véhicules actifs
        ];
    }
}
