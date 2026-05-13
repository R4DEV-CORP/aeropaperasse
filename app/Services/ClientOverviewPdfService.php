<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Client;
use App\Models\CoworkerTraining;
use App\Models\VehiclePass;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

class ClientOverviewPdfService
{
    private const BADGE_ACTIVE_STATUSES = ['active', 'expiring_soon'];

    private const BADGE_TERMINATED_STATUSES = ['expired', 'returned', 'lost'];

    public function generateOverview(int $clientId)
    {
        $clientData = $this->getClientData($clientId);

        return Pdf::view('pdf.client-overview', ['client' => $clientData])
            ->format('a4')
            ->withBrowsershot(fn (Browsershot $browsershot) => $browsershot->noSandbox())
            ->name("Bilan_{$clientData['company_name']}_".date('Y-m-d').'.pdf');
    }

    private function getClientData(int $clientId): array
    {
        $client = Client::with([
            'contacts',
            'coworkers' => fn ($q) => $q->orderBy('has_leave')->orderBy('lastname')->orderBy('firstname'),
            'activityRequests' => fn ($q) => $q->where('status', 'approved')->orderBy('created_at', 'desc'),
        ])->findOrFail($clientId);

        $contacts = [];
        $safetyReferents = [];
        foreach ($client->contacts as $contact) {
            if ($contact->role === 'safety') {
                $safetyReferents[] = $contact;
            } else {
                $contacts[$contact->role] = $contact;
            }
        }

        $approvedActivityRequests = $client->activityRequests;
        $activityAirports = [];
        foreach ($approvedActivityRequests as $ar) {
            if ($ar->airport) {
                $activityAirports[$ar->airport] = ($activityAirports[$ar->airport] ?? 0) + 1;
            }
        }

        $badgeQuery = Badge::with(['coworker', 'badgeRequest.coworker'])
            ->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                    ->orWhereHas('badgeRequest.activityRequest', fn ($q2) => $q2->where('client_id', $clientId));
            })
            ->orderBy('expiry_date');

        $activeBadges = (clone $badgeQuery)->whereIn('status', self::BADGE_ACTIVE_STATUSES)->get();
        $terminatedBadges = (clone $badgeQuery)->whereIn('status', self::BADGE_TERMINATED_STATUSES)->get();

        $vehiclePasses = VehiclePass::with('activityRequest')
            ->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                    ->orWhereHas('activityRequest', fn ($q2) => $q2->where('client_id', $clientId));
            })
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        $coworkerIds = $client->coworkers->pluck('id');
        $trainings = CoworkerTraining::with(['coworker', 'training'])
            ->whereIn('coworker_id', $coworkerIds)
            ->orderByRaw('expires_at IS NULL, expires_at ASC')
            ->get();

        return [
            'id' => $client->id,
            'company_name' => $client->company_name,
            'trade_name' => $client->trade_name,
            'siret_number' => $client->siret_number,
            'address' => $client->address,
            'zip_code' => $client->zip_code,
            'city' => $client->city,
            'subcontractor_of' => $client->subcontractor_of,

            'safety_referents' => $safetyReferents,
            'contacts' => $contacts,

            'coworkers' => $client->coworkers,

            'approved_activity_requests' => $approvedActivityRequests,
            'activity_airports' => $activityAirports,

            'active_badges' => $activeBadges,
            'terminated_badges' => $terminatedBadges,

            'vehicle_passes' => $vehiclePasses,

            'trainings' => $trainings,
        ];
    }
}
