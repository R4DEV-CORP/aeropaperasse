@extends('pdf.layouts.base')

@section('document-title', 'BILAN DE SOCIÉTÉ')
@section('company-name', $client['raison_sociale'] ?? $client['name'] ?? 'Société')

@section('content')

  {{-- Section 1: Informations de société --}}
  <div class="section">
    <h2 class="section-title">1. Informations de société</h2>

    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">Raison sociale</div>
      <div class="info-value {{ empty($client['raison_sociale']) ? 'empty' : '' }}">
      {{ $client['raison_sociale'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Nom commercial</div>
      <div class="info-value {{ empty($client['nom_commercial']) ? 'empty' : '' }}">
      {{ $client['nom_commercial'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>

    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">SIRET</div>
      <div class="info-value {{ empty($client['siret']) ? 'empty' : '' }}">
      {{ $client['siret'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Numéro d'identification</div>
      <div class="info-value {{ empty($client['numero_identification']) ? 'empty' : '' }}">
      {{ $client['numero_identification'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>

    <div class="info-grid-3">
    <div class="info-item">
      <div class="info-label">Adresse</div>
      <div class="info-value {{ empty($client['adresse']) ? 'empty' : '' }}">
      {{ $client['adresse'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Code postal</div>
      <div class="info-value {{ empty($client['code_postal']) ? 'empty' : '' }}">
      {{ $client['code_postal'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Ville</div>
      <div class="info-value {{ empty($client['ville']) ? 'empty' : '' }}">
      {{ $client['ville'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>
  </div>

  {{-- Section 2: Responsable principal --}}
  <div class="section">
    <h2 class="section-title">2. Responsable principal</h2>

    <div class="info-grid-3">
    <div class="info-item">
      <div class="info-label">Nom et Prénom</div>
      <div
      class="info-value {{ empty($client['responsable_nom']) && empty($client['responsable_prenom']) ? 'empty' : '' }}">
      {{ trim(($client['responsable_nom'] ?? '') . ' ' . ($client['responsable_prenom'] ?? '')) ?: 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Email</div>
      <div class="info-value {{ empty($client['responsable_email']) ? 'empty' : '' }}">
      {{ $client['responsable_email'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Téléphone</div>
      <div class="info-value {{ empty($client['responsable_telephone']) ? 'empty' : '' }}">
      {{ $client['responsable_telephone'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>

    <div class="info-item">
    <div class="info-label">Fonction</div>
    <div class="info-value {{ empty($client['responsable_fonction']) ? 'empty' : '' }}">
      {{ $client['responsable_fonction'] ?? 'Non renseigné' }}
    </div>
    </div>
  </div>

  {{-- Section 3: Référents sûreté --}}
  <div class="section">
    <h2 class="section-title">3. Référents sûreté</h2>

    {{-- Référent 1 --}}
    <h3 class="subsection-title">Référent 1</h3>
    <div class="info-grid-3">
    <div class="info-item">
      <div class="info-label">Nom et Prénom</div>
      <div
      class="info-value {{ empty($client['safety_referent_name_1']) && empty($client['safety_referent_prenom_1']) ? 'empty' : '' }}">
      {{ trim(($client['safety_referent_name_1'] ?? '') . ' ' . ($client['safety_referent_prenom_1'] ?? '')) ?: 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Email</div>
      <div class="info-value {{ empty($client['safety_referent_email_1']) ? 'empty' : '' }}">
      {{ $client['safety_referent_email_1'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Téléphone</div>
      <div class="info-value {{ empty($client['safety_referent_phone_1']) ? 'empty' : '' }}">
      {{ $client['safety_referent_phone_1'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>

    {{-- Référent 2 --}}
    @if($client['safety_referent_name_2'] || $client['safety_referent_prenom_2'] || $client['safety_referent_email_2'] || $client['safety_referent_phone_2'])
    <h3 class="subsection-title">Référent 2</h3>
    <div class="info-grid-3">
    <div class="info-item">
      <div class="info-label">Nom et Prénom</div>
      <div
      class="info-value {{ empty($client['safety_referent_name_2']) && empty($client['safety_referent_prenom_2']) ? 'empty' : '' }}">
      {{ trim(($client['safety_referent_name_2'] ?? '') . ' ' . ($client['safety_referent_prenom_2'] ?? '')) ?: 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Email</div>
      <div class="info-value {{ empty($client['safety_referent_email_2']) ? 'empty' : '' }}">
      {{ $client['safety_referent_email_2'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Téléphone</div>
      <div class="info-value {{ empty($client['safety_referent_phone_2']) ? 'empty' : '' }}">
      {{ $client['safety_referent_phone_2'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>
    @endif

    {{-- Référent 3 --}}
    @if($client['safety_referent_name_3'] || $client['safety_referent_prenom_3'] || $client['safety_referent_email_3'] || $client['safety_referent_phone_3'])
    <h3 class="subsection-title">Référent 3</h3>
    <div class="info-grid-3">
    <div class="info-item">
      <div class="info-label">Nom et Prénom</div>
      <div
      class="info-value {{ empty($client['safety_referent_name_3']) && empty($client['safety_referent_prenom_3']) ? 'empty' : '' }}">
      {{ trim(($client['safety_referent_name_3'] ?? '') . ' ' . ($client['safety_referent_prenom_3'] ?? '')) ?: 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Email</div>
      <div class="info-value {{ empty($client['safety_referent_email_3']) ? 'empty' : '' }}">
      {{ $client['safety_referent_email_3'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Téléphone</div>
      <div class="info-value {{ empty($client['safety_referent_phone_3']) ? 'empty' : '' }}">
      {{ $client['safety_referent_phone_3'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>
    @endif
  </div>

  {{-- Section 4: Correspondant sécurité --}}
  <div class="section">
    <h2 class="section-title">4. Correspondant sécurité</h2>

    <div class="info-grid-3">
    <div class="info-item">
      <div class="info-label">Nom et Prénom</div>
      <div
      class="info-value {{ empty($client['security_correspondent_name']) && empty($client['security_correspondent_prenom']) ? 'empty' : '' }}">
      {{ trim(($client['security_correspondent_name'] ?? '') . ' ' . ($client['security_correspondent_prenom'] ?? '')) ?: 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Email</div>
      <div class="info-value {{ empty($client['security_correspondent_email']) ? 'empty' : '' }}">
      {{ $client['security_correspondent_email'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Téléphone</div>
      <div class="info-value {{ empty($client['security_correspondent_phone']) ? 'empty' : '' }}">
      {{ $client['security_correspondent_phone'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>
  </div>

  {{-- Section 5: Contact RH --}}
  <div class="section">
    <h2 class="section-title">5. Contact RH</h2>

    <div class="info-grid-3">
    <div class="info-item">
      <div class="info-label">Nom et Prénom</div>
      <div
      class="info-value {{ empty($client['hr_contact_name']) && empty($client['hr_contact_prenom']) ? 'empty' : '' }}">
      {{ trim(($client['hr_contact_name'] ?? '') . ' ' . ($client['hr_contact_prenom'] ?? '')) ?: 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Email</div>
      <div class="info-value {{ empty($client['hr_contact_email']) ? 'empty' : '' }}">
      {{ $client['hr_contact_email'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Téléphone</div>
      <div class="info-value {{ empty($client['hr_contact_phone']) ? 'empty' : '' }}">
      {{ $client['hr_contact_phone'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>
  </div>

  {{-- Section 6: Informations d'activité --}}
  <div class="section">
    <h2 class="section-title">6. Informations d'activité</h2>

    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">Description de l'activité</div>
      <div class="info-value {{ empty($client['activite_description']) ? 'empty' : '' }}">
      {{ $client['activite_description'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Sous-traitant de</div>
      <div class="info-value {{ empty($client['sous_traitant_de']) ? 'empty' : '' }}">
      {{ $client['sous_traitant_de'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>

    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">Nombre de demandes d'activité</div>
      <div class="info-value {{ empty($client['nombre_demandes_activite']) ? 'empty' : '' }}">
      {{ $client['nombre_demandes_activite'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Numéros des demandes d'activité</div>
      <div class="info-value {{ empty($client['numeros_demandes_activite']) ? 'empty' : '' }}">
      {{ $client['numeros_demandes_activite'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>

    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">Date de début de validité</div>
      <div class="info-value {{ empty($client['date_debut_validite']) ? 'empty' : '' }}">
      {{ $client['date_debut_validite'] ? \Carbon\Carbon::parse($client['date_debut_validite'])->format('d/m/Y') : 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Date de fin de validité</div>
      <div class="info-value {{ empty($client['date_fin_validite']) ? 'empty' : '' }}">
      {{ $client['date_fin_validite'] ? \Carbon\Carbon::parse($client['date_fin_validite'])->format('d/m/Y') : 'Non renseigné' }}
      </div>
    </div>
    </div>

    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">Aéroports concernés</div>
      <div class="info-value {{ empty($client['aeroports_concernes']) ? 'empty' : '' }}">
      {{ is_array($client['aeroports_concernes']) && count($client['aeroports_concernes']) > 0 ? implode(', ', $client['aeroports_concernes']) : 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Zones concernées</div>
      <div class="info-value {{ empty($client['zones_concernees']) ? 'empty' : '' }}">
      {{ is_array($client['zones_concernees']) && count($client['zones_concernees']) > 0 ? implode(', ', $client['zones_concernees']) : 'Non renseigné' }}
      </div>
    </div>
    </div>
  </div>

  {{-- Section 7: Quotas et statistiques --}}
  <div class="section">
    <h2 class="section-title">7. Quotas et statistiques</h2>

    <h3 class="subsection-title">Badges</h3>
    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">Nombre de badges actifs</div>
      <div class="info-value {{ empty($client['nombre_badges_actifs']) ? 'empty' : '' }}">
      {{ $client['nombre_badges_actifs'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Nombre de badges autorisés (maximum)</div>
      <div class="info-value {{ empty($client['badge_limit']) ? 'empty' : '' }}">
      {{ $client['badge_limit'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>

    <h3 class="subsection-title">Véhicules</h3>
    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">Nombre de véhicules actifs</div>
      <div class="info-value {{ empty($client['nombre_vehicules_actifs']) ? 'empty' : '' }}">
      {{ $client['nombre_vehicules_actifs'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Nombre de véhicules autorisés (maximum)</div>
      <div class="info-value {{ empty($client['vehicle_pass_limit']) ? 'empty' : '' }}">
      {{ $client['vehicle_pass_limit'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>
  </div>

@endsection
