<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ActivityRequest;
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
      ->name("Bilan_{$clientData['name']}_" . date('Y-m-d') . ".pdf");
  }

  /**
   * Récupère et formate les données client
   */
  private function getClientData(int $clientId): array
  {
    // Récupération du client
    $client = Client::findOrFail($clientId);

    // Récupération de la dernière demande d'activité approuvée
    $activityRequest = ActivityRequest::whereHas('user', function ($query) use ($clientId) {
      $query->where('client_id', $clientId);
    })->where('status', 'approved')
      ->orderBy('created_at', 'desc')
      ->first();

    // Compilation des données (priorité aux données Client, fallback sur ActivityRequest)
    return [
      // Infos de base
      'id' => $client->id,
      'name' => $client->name,
      'badge_limit' => $client->badge_limit,
      'vehicle_pass_limit' => $client->vehicle_pass_limit,

      // Informations société (priorité Client)
      'raison_sociale' => $client->raison_sociale ?: ($activityRequest ? $activityRequest->raison_sociale : $client->name),
      'nom_commercial' => $client->nom_commercial ?: ($activityRequest ? $activityRequest->nom_commercial : null),
      'siret' => $client->siret ?: ($activityRequest ? $activityRequest->siret : null),
      'adresse' => $client->adresse ?: ($activityRequest ? $activityRequest->adresse : null),
      'code_postal' => $client->code_postal,
      'ville' => $client->ville,
      'numero_identification' => $client->numero_identification,

      // Responsable principal (priorité Client)
      'responsable_nom' => $client->responsable_nom ?: ($activityRequest ? $activityRequest->responsable_nom : null),
      'responsable_prenom' => $client->responsable_prenom ?: ($activityRequest ? $activityRequest->responsable_prenom : null),
      'responsable_email' => $client->responsable_email ?: ($activityRequest ? $activityRequest->responsable_email : null),
      'responsable_telephone' => $client->responsable_telephone ?: ($activityRequest ? $activityRequest->responsable_telephone : null),
      'responsable_fonction' => $client->responsable_fonction ?: ($activityRequest ? $activityRequest->responsable_fonction : null),

      // Activité
      'activite_description' => $client->activite_description ?: ($activityRequest ? $activityRequest->activite_description : null),
      'sous_traitant_de' => $client->sous_traitant_de,
      'nombre_demandes_activite' => $client->nombre_demandes_activite,
      'numeros_demandes_activite' => $client->numeros_demandes_activite,
      'date_debut_validite' => $client->date_debut_validite,
      'date_fin_validite' => $client->date_fin_validite,
      'aeroports_concernes' => $client->aeroports_concernes,
      'zones_concernees' => $client->zones_concernees,
      'nombre_badges_actifs' => $client->nombre_badges_actifs,
      'nombre_vehicules_actifs' => $client->nombre_vehicules_actifs,

      // Référents sûreté
      'safety_referent_name_1' => $client->safety_referent_name_1,
      'safety_referent_prenom_1' => $client->safety_referent_prenom_1,
      'safety_referent_email_1' => $client->safety_referent_email_1,
      'safety_referent_phone_1' => $client->safety_referent_phone_1,

      'safety_referent_name_2' => $client->safety_referent_name_2,
      'safety_referent_prenom_2' => $client->safety_referent_prenom_2,
      'safety_referent_email_2' => $client->safety_referent_email_2,
      'safety_referent_phone_2' => $client->safety_referent_phone_2,

      'safety_referent_name_3' => $client->safety_referent_name_3,
      'safety_referent_prenom_3' => $client->safety_referent_prenom_3,
      'safety_referent_email_3' => $client->safety_referent_email_3,
      'safety_referent_phone_3' => $client->safety_referent_phone_3,

      // Correspondant sécurité
      'security_correspondent_name' => $client->security_correspondent_name,
      'security_correspondent_prenom' => $client->security_correspondent_prenom,
      'security_correspondent_email' => $client->security_correspondent_email,
      'security_correspondent_phone' => $client->security_correspondent_phone,

      // Contact RH
      'hr_contact_name' => $client->hr_contact_name,
      'hr_contact_prenom' => $client->hr_contact_prenom,
      'hr_contact_email' => $client->hr_contact_email,
      'hr_contact_phone' => $client->hr_contact_phone,
    ];
  }
}
