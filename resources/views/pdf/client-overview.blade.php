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
      <div class="info-value {{ empty($client['company_name']) ? 'empty' : '' }}">
      {{ $client['company_name'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Nom commercial</div>
      <div class="info-value {{ empty($client['trade_name']) ? 'empty' : '' }}">
      {{ $client['trade_name'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>

    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">SIRET</div>
      <div class="info-value {{ empty($client['siret_number']) ? 'empty' : '' }}">
      {{ $client['siret_number'] ?? 'Non renseigné' }}
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
      <div class="info-value {{ empty($client['address']) ? 'empty' : '' }}">
      {{ $client['address'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Code postal</div>
      <div class="info-value {{ empty($client['zip_code']) ? 'empty' : '' }}">
      {{ $client['zip_code'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Ville</div>
      <div class="info-value {{ empty($client['city']) ? 'empty' : '' }}">
      {{ $client['city'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>
  </div>

  {{-- Section 2: Référents sûreté --}}
  <div class="section">
    <h2 class="section-title">3. Référents sûreté</h2>

    @if($client['contacts']['safety'] ?? null)
    <div class="info-grid-3">
    <div class="info-item">
      <div class="info-label">Nom et Prénom</div>
      <div
      class="info-value {{ empty($client['contacts']['safety']['firstname']) && empty($client['contacts']['safety']['lastname']) ? 'empty' : '' }}">
      {{ trim(($client['contacts']['safety']['firstname'] ?? '') . ' ' . ($client['contacts']['safety']['lastname'] ?? '')) ?: 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Email</div>
      <div class="info-value {{ empty($client['contacts']['safety']['email']) ? 'empty' : '' }}">
      {{ $client['contacts']['safety']['email'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Téléphone</div>
      <div class="info-value {{ empty($client['contacts']['safety']['phone']) ? 'empty' : '' }}">
      {{ $client['contacts']['safety']['phone'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>
    @endif
  </div>

  {{-- Section 3: Correspondant sécurité --}}
  <div class="section">


    <div class="info-grid-3">
    <div class="info-item">
      <div class="info-label">Nom et Prénom</div>
      <div
      class="info-value {{ empty($client['contacts']['security']['firstname']) && empty($client['contacts']['security']['lastname']) ? 'empty' : '' }}">
      {{ trim(($client['contacts']['security']['firstname'] ?? '') . ' ' . ($client['contacts']['security']['lastname'] ?? '')) ?: 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Email</div>
      <div class="info-value {{ empty($client['contacts']['security']['email']) ? 'empty' : '' }}">
      {{ $client['contacts']['security']['email'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Téléphone</div>
      <div class="info-value {{ empty($client['contacts']['security']['phone']) ? 'empty' : '' }}">
      {{ $client['contacts']['security']['phone'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>
  </div>

  {{-- Section 4: Contact RH --}}
  <div class="section">
    <h2 class="section-title">5. Contact RH</h2>

    <div class="info-grid-3">
    <div class="info-item">
      <div class="info-label">Nom et Prénom</div>
      <div
      class="info-value {{ empty($client['contacts']['hr']['firstname']) && empty($client['contacts']['hr']['lastname']) ? 'empty' : '' }}">
      {{ trim(($client['contacts']['hr']['firstname'] ?? '') . ' ' . ($client['contacts']['hr']['lastname'] ?? '')) ?: 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Email</div>
      <div class="info-value {{ empty($client['contacts']['hr']['email']) ? 'empty' : '' }}">
      {{ $client['contacts']['hr']['email'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Téléphone</div>
      <div class="info-value {{ empty($client['contacts']['hr']['phone']) ? 'empty' : '' }}">
      {{ $client['contacts']['hr']['phone'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>
  </div>

  {{-- Section 5: Informations d'activité --}}
  <div class="section">
    <h2 class="section-title">6. Informations d'activité</h2>

    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">Description des activités</div>
      <div class="info-value {{ empty($client['activities']) ? 'empty' : '' }}">
        @foreach($client['activities'] as $activity)
        {{ $activity['description'] }}<br>
        @endforeach
      </div>
    </div>

    <div class="info-item">
      <div class="info-label">Sous-traitant de</div>
      <div class="info-value {{ empty($client['subcontractor_of']) ? 'empty' : '' }}">
      {{ $client['subcontractor_of'] ?? 'Non renseigné' }}
      </div>
    </div>
    </div>

    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">Nombre de demandes d'activité</div>
      <div class="info-value {{ empty($client['activity_count']) ? 'empty' : '' }}">
      {{ $client['activity_count'] ?? 'Non renseigné' }}
      </div>
    </div>

    <div class="info-grid">
    <div class="info-item">
      <div class="info-label">Aéroports concernés</div>
      <div class="info-value {{ empty($client['activity_airports']) ? 'empty' : '' }}">
        @foreach($client['activity_airports'] as $airport => $count)
        @if($count > 0)
        {{ $airport.': '.$count }}<br>
        @endif
        @endforeach
      </div>
    </div>
    </div>
  </div>

  {{-- Note: Les quotas sont maintenant gérés au niveau de chaque demande d'activité --}}

@endsection
