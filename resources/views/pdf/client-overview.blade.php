@extends('pdf.layouts.base')

@section('document-title', 'BILAN DE SOCIÉTÉ')
@section('company-name', $client['company_name'] ?? 'Société')

@php
    $badgeStatusMeta = [
        'active' => ['label' => 'Actif', 'class' => 'pill-approved'],
        'expiring_soon' => ['label' => 'Expire bientôt', 'class' => 'pill-pending'],
        'expired' => ['label' => 'Expiré', 'class' => 'pill-rejected'],
        'returned' => ['label' => 'Restitué', 'class' => 'pill-default'],
        'lost' => ['label' => 'Perdu', 'class' => 'pill-rejected'],
    ];

    $vehiclePassStatusMeta = [
        'draft' => ['label' => 'Brouillon', 'class' => 'pill-default'],
        'pending' => ['label' => 'En attente', 'class' => 'pill-pending'],
        'approved' => ['label' => 'Approuvé', 'class' => 'pill-approved'],
        'rejected' => ['label' => 'Rejeté', 'class' => 'pill-rejected'],
    ];

    $contacts = $client['contacts'] ?? [];
    $safetyReferents = $client['safety_referents'] ?? [];
    $securityContact = $contacts['security'] ?? null;
    $hrContact = $contacts['hr'] ?? null;

    $coworkers = $client['coworkers'] ?? collect();
    $approvedActivityRequests = $client['approved_activity_requests'] ?? collect();
    $activityAirports = $client['activity_airports'] ?? [];
    $activeBadges = $client['active_badges'] ?? collect();
    $terminatedBadges = $client['terminated_badges'] ?? collect();
    $vehiclePasses = $client['vehicle_passes'] ?? collect();
    $trainings = $client['trainings'] ?? collect();
@endphp

@section('content')

  {{-- Section 1: Informations de société --}}
  <div class="section">
    <h2 class="section-title">1. Informations de société</h2>

    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">Raison sociale</div>
        <div class="info-value {{ empty($client['company_name']) ? 'empty' : '' }}">
          {{ $client['company_name'] ?: 'Non renseigné' }}
        </div>
      </div>
      <div class="info-item">
        <div class="info-label">Nom commercial</div>
        <div class="info-value {{ empty($client['trade_name']) ? 'empty' : '' }}">
          {{ $client['trade_name'] ?: 'Non renseigné' }}
        </div>
      </div>
    </div>

    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">SIRET</div>
        <div class="info-value {{ empty($client['siret_number']) ? 'empty' : '' }}">
          {{ $client['siret_number'] ?: 'Non renseigné' }}
        </div>
      </div>
      <div class="info-item">
        <div class="info-label">Sous-traitant de</div>
        <div class="info-value {{ empty($client['subcontractor_of']) ? 'empty' : '' }}">
          {{ $client['subcontractor_of'] ?: 'Aucun' }}
        </div>
      </div>
    </div>

    <div class="info-grid-3">
      <div class="info-item">
        <div class="info-label">Adresse</div>
        <div class="info-value {{ empty($client['address']) ? 'empty' : '' }}">
          {{ $client['address'] ?: 'Non renseigné' }}
        </div>
      </div>
      <div class="info-item">
        <div class="info-label">Code postal</div>
        <div class="info-value {{ empty($client['zip_code']) ? 'empty' : '' }}">
          {{ $client['zip_code'] ?: 'Non renseigné' }}
        </div>
      </div>
      <div class="info-item">
        <div class="info-label">Ville</div>
        <div class="info-value {{ empty($client['city']) ? 'empty' : '' }}">
          {{ $client['city'] ?: 'Non renseigné' }}
        </div>
      </div>
    </div>
  </div>

  {{-- Section 2: Référents sûreté --}}
  <div class="section">
    <h2 class="section-title">2. Référents sûreté <span class="section-count">({{ count($safetyReferents) }})</span></h2>

    @if (empty($safetyReferents))
      <div class="empty-state">Aucun référent renseigné.</div>
    @else
      <table class="table">
        <thead>
          <tr>
            <th>Nom et Prénom</th>
            <th>Email</th>
            <th>Téléphone</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($safetyReferents as $ref)
            <tr>
              <td>{{ trim(($ref->firstname ?? '').' '.($ref->lastname ?? '')) ?: '—' }}</td>
              <td>{{ $ref->email ?: '—' }}</td>
              <td>{{ $ref->phone ?: '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  {{-- Section 3: Correspondant sécurité --}}
  <div class="section">
    <h2 class="section-title">3. Correspondant sécurité</h2>

    @if (! $securityContact)
      <div class="empty-state">Non renseigné.</div>
    @else
      <div class="info-grid-3">
        <div class="info-item">
          <div class="info-label">Nom et Prénom</div>
          <div class="info-value">
            {{ trim(($securityContact['firstname'] ?? '').' '.($securityContact['lastname'] ?? '')) ?: 'Non renseigné' }}
          </div>
        </div>
        <div class="info-item">
          <div class="info-label">Email</div>
          <div class="info-value {{ empty($securityContact['email']) ? 'empty' : '' }}">
            {{ $securityContact['email'] ?? 'Non renseigné' }}
          </div>
        </div>
        <div class="info-item">
          <div class="info-label">Téléphone</div>
          <div class="info-value {{ empty($securityContact['phone']) ? 'empty' : '' }}">
            {{ $securityContact['phone'] ?? 'Non renseigné' }}
          </div>
        </div>
      </div>
    @endif
  </div>

  {{-- Section 4: Contact RH --}}
  <div class="section">
    <h2 class="section-title">4. Contact RH</h2>

    @if (! $hrContact)
      <div class="empty-state">Non renseigné.</div>
    @else
      <div class="info-grid-3">
        <div class="info-item">
          <div class="info-label">Nom et Prénom</div>
          <div class="info-value">
            {{ trim(($hrContact['firstname'] ?? '').' '.($hrContact['lastname'] ?? '')) ?: 'Non renseigné' }}
          </div>
        </div>
        <div class="info-item">
          <div class="info-label">Email</div>
          <div class="info-value {{ empty($hrContact['email']) ? 'empty' : '' }}">
            {{ $hrContact['email'] ?? 'Non renseigné' }}
          </div>
        </div>
        <div class="info-item">
          <div class="info-label">Téléphone</div>
          <div class="info-value {{ empty($hrContact['phone']) ? 'empty' : '' }}">
            {{ $hrContact['phone'] ?? 'Non renseigné' }}
          </div>
        </div>
      </div>
    @endif
  </div>

  {{-- Section 5: Collaborateurs --}}
  <div class="section allow-break">
    <h2 class="section-title">5. Collaborateurs <span class="section-count">({{ $coworkers->count() }})</span></h2>

    @if ($coworkers->isEmpty())
      <div class="empty-state">Aucun collaborateur enregistré.</div>
    @else
      <table class="table compact">
        <thead>
          <tr>
            <th>Nom et Prénom</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($coworkers as $coworker)
            @php
              $departure = $coworker->departure_date ? $coworker->departure_date->format('d/m/Y') : null;
            @endphp
            <tr>
              <td>{{ trim(($coworker->firstname ?? '').' '.($coworker->lastname ?? '')) ?: '—' }}</td>
              <td>
                @if ($coworker->has_leave)
                  <span class="pill pill-rejected">{{ $departure ? 'Parti le '.$departure : 'Parti' }}</span>
                @else
                  <span class="pill pill-approved">Actif</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  {{-- Section 6: Demandes d'activité approuvées --}}
  <div class="section allow-break">
    <h2 class="section-title">6. Demandes d'activité approuvées <span class="section-count">({{ $approvedActivityRequests->count() }})</span></h2>

    @if (! empty($activityAirports))
      <div class="info-item" style="margin-bottom: 10px;">
        <div class="info-label">Répartition par aéroport</div>
        <div class="info-value">
          @foreach ($activityAirports as $airport => $count)
            <span class="pill pill-info">{{ $airport }} : {{ $count }}</span>
          @endforeach
        </div>
      </div>
    @endif

    @if ($approvedActivityRequests->isEmpty())
      <div class="empty-state">Aucune demande d'activité approuvée.</div>
    @else
      <table class="table compact">
        <thead>
          <tr>
            <th>N°</th>
            <th>Responsable</th>
            <th>Aéroport</th>
            <th>Description</th>
            <th>Approuvée le</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($approvedActivityRequests as $ar)
            <tr>
              <td class="num">#{{ $ar->id }}</td>
              <td>
                {{ trim(($ar->manager_firstname ?? '').' '.($ar->manager_lastname ?? '')) ?: '—' }}
                @if ($ar->manager_email)
                  <div class="muted">{{ $ar->manager_email }}</div>
                @endif
              </td>
              <td>{{ $ar->airport ?: '—' }}</td>
              <td>{{ \Illuminate\Support\Str::limit($ar->description ?? '', 120) ?: '—' }}</td>
              <td class="muted">{{ $ar->approved_at?->format('d/m/Y') ?? $ar->updated_at?->format('d/m/Y') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  {{-- Section 7: Badges --}}
  <div class="section allow-break">
    <h2 class="section-title">7. Badges <span class="section-count">({{ $activeBadges->count() + $terminatedBadges->count() }})</span></h2>

    <div class="group-title">Actifs <span class="section-count">({{ $activeBadges->count() }})</span></div>
    @if ($activeBadges->isEmpty())
      <div class="empty-state">Aucun badge actif.</div>
    @else
      <table class="table compact">
        <thead>
          <tr>
            <th>Numéro</th>
            <th>Porteur</th>
            <th>Aéroport</th>
            <th>Statut</th>
            <th>Expiration</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($activeBadges as $badge)
            @php
              $status = $badgeStatusMeta[$badge->status] ?? ['label' => $badge->status, 'class' => 'pill-default'];
              $bearer = $badge->coworker ?? $badge->badgeRequest?->coworker;
              $bearerName = $bearer ? trim($bearer->firstname.' '.$bearer->lastname) : null;
            @endphp
            <tr>
              <td class="num">{{ $badge->badge_number ?: '—' }}</td>
              <td>{{ $bearerName ?: '—' }}</td>
              <td>{{ $badge->airport ?: '—' }}</td>
              <td><span class="pill {{ $status['class'] }}">{{ $status['label'] }}</span></td>
              <td class="muted">{{ $badge->expiry_date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif

    <div class="group-title">Expirés / Restitués <span class="section-count">({{ $terminatedBadges->count() }})</span></div>
    @if ($terminatedBadges->isEmpty())
      <div class="empty-state">Aucun badge expiré ou restitué.</div>
    @else
      <table class="table compact">
        <thead>
          <tr>
            <th>Numéro</th>
            <th>Porteur</th>
            <th>Aéroport</th>
            <th>Statut</th>
            <th>Expiration</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($terminatedBadges as $badge)
            @php
              $status = $badgeStatusMeta[$badge->status] ?? ['label' => $badge->status, 'class' => 'pill-default'];
              $bearer = $badge->coworker ?? $badge->badgeRequest?->coworker;
              $bearerName = $bearer ? trim($bearer->firstname.' '.$bearer->lastname) : null;
            @endphp
            <tr>
              <td class="num">{{ $badge->badge_number ?: '—' }}</td>
              <td>{{ $bearerName ?: '—' }}</td>
              <td>{{ $badge->airport ?: '—' }}</td>
              <td><span class="pill {{ $status['class'] }}">{{ $status['label'] }}</span></td>
              <td class="muted">{{ $badge->expiry_date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  {{-- Section 8: Laissez-passer --}}
  <div class="section allow-break">
    <h2 class="section-title">8. Laissez-passer approuvés <span class="section-count">({{ $vehiclePasses->count() }})</span></h2>

    @if ($vehiclePasses->isEmpty())
      <div class="empty-state">Aucun laissez-passer approuvé.</div>
    @else
      <table class="table compact">
        <thead>
          <tr>
            <th>Plaque</th>
            <th>Marque</th>
            <th>Aéroport</th>
            <th>Demande d'activité</th>
            <th>Créé le</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($vehiclePasses as $vp)
            <tr>
              <td class="num">{{ $vp->plate_number ?: '—' }}</td>
              <td>{{ $vp->car_brand ?: '—' }}</td>
              <td>{{ $vp->airport ?: '—' }}</td>
              <td class="muted">{{ $vp->activity_request_id ? '#'.$vp->activity_request_id : 'Indépendant' }}</td>
              <td class="muted">{{ $vp->created_at?->format('d/m/Y') ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  {{-- Section 9: Formations --}}
  <div class="section allow-break">
    <h2 class="section-title">9. Formations <span class="section-count">({{ $trainings->count() }})</span></h2>

    @if ($trainings->isEmpty())
      <div class="empty-state">Aucune formation attribuée.</div>
    @else
      <table class="table compact">
        <thead>
          <tr>
            <th>Collaborateur</th>
            <th>Formation</th>
            <th>Aéroport</th>
            <th>Début</th>
            <th>Expiration</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($trainings as $ct)
            @php
              $expiresAt = $ct->expires_at;
              if ($expiresAt && $expiresAt->isPast()) {
                  $statusLabel = 'Expirée';
                  $statusClass = 'pill-rejected';
              } else {
                  $statusLabel = 'Active';
                  $statusClass = 'pill-approved';
              }
              $coworker = $ct->coworker;
              $coworkerName = $coworker ? trim($coworker->firstname.' '.$coworker->lastname) : '—';
            @endphp
            <tr>
              <td>{{ $coworkerName }}</td>
              <td>{{ $ct->training?->title ?? '—' }}</td>
              <td>{{ $ct->airport ?: '—' }}</td>
              <td class="muted">{{ $ct->started_at?->format('d/m/Y') ?? '—' }}</td>
              <td class="muted">{{ $expiresAt?->format('d/m/Y') ?? '—' }}</td>
              <td><span class="pill {{ $statusClass }}">{{ $statusLabel }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

@endsection
