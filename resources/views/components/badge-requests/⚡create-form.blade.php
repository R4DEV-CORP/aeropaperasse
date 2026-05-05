<?php

use App\Actions\BadgeRequest\SaveBadgeRequestAction;
use App\Actions\Coworker\CreateCoworkerAction;
use App\DataTransferObjects\CreateBadgeRequestData;
use App\DataTransferObjects\CreateCoworkerData;
use App\Forms\BadgeRequestFormData;
use App\Forms\BadgeRequestFormValidator;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\BadgeRequest\BadgeRequestCreated;
use App\Models\ActivityRequest;
use App\Models\BadgeRequest;
use App\Models\Client;
use App\Models\Coworker;
use App\Validators\CoworkerValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use InteractsWithToasts;
    use WithFileUploads;

    public $user;

    public ?Client $client = null;

    public $allClients;

    public ?int $selected_client_id = null;

    public ?int $badgeRequestId = null;

    // Activité
    public $activityRequests;

    public ?int $selected_activity_request_id = null;

    public ?ActivityRequest $activityRequest = null;

    // Collaborateur
    public $coworkers;

    public ?int $selected_coworker_id = null;

    // Flags
    public bool $application_authorization = false;

    public bool $validate_training = false;

    // Documents
    public $selfie_photo;

    public $identification_card;

    public $activity_authorization;

    public $for_document;

    public $formation_certificate_document;

    public $invoice_document;

    // Noms des fichiers nouvellement sélectionnés (affichage)
    public ?string $selfie_photo_name = null;

    public ?string $identification_card_name = null;

    public ?string $activity_authorization_name = null;

    public ?string $for_document_name = null;

    public ?string $formation_certificate_document_name = null;

    public ?string $invoice_document_name = null;

    // Indicateurs de documents existants (édition de brouillon)
    public bool $hasExistingSelfiePhoto = false;

    public bool $hasExistingIdentificationCard = false;

    public bool $hasExistingActivityAuthorization = false;

    public bool $hasExistingForDocument = false;

    public bool $hasExistingFormationCertificate = false;

    public bool $hasExistingInvoice = false;

    public ?string $existing_selfie_photo_name = null;

    public ?string $existing_identification_card_name = null;

    public ?string $existing_activity_authorization_name = null;

    public ?string $existing_for_document_name = null;

    public ?string $existing_formation_certificate_document_name = null;

    public ?string $existing_invoice_document_name = null;

    // Quick-create coworker (flyout)
    public ?string $new_coworker_firstname = null;

    public ?string $new_coworker_lastname = null;

    public ?string $new_coworker_email = null;

    public ?string $new_coworker_phone = null;

    public function mount(?int $badgeRequestId = null): void
    {
        $this->user = auth()->user();
        $this->badgeRequestId = $badgeRequestId;
        $this->activityRequests = collect();
        $this->coworkers = collect();

        if ($this->user->isAdmin()) {
            $this->allClients = Client::orderBy('company_name')->get();
        } else {
            $this->client = $this->user->client;
            $this->loadActivityRequests();
            $this->loadCoworkers();
        }

        if ($this->badgeRequestId) {
            $this->loadDraft($this->badgeRequestId);
        }
    }

    protected function loadActivityRequests(): void
    {
        if (! $this->client) {
            $this->activityRequests = collect();

            return;
        }

        $this->activityRequests = ActivityRequest::where('client_id', $this->client->id)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    protected function loadCoworkers(): void
    {
        $this->coworkers = $this->client
            ? Coworker::where('client_id', $this->client->id)->orderBy('lastname')->get()
            : collect();
    }

    protected function buildCoworkerOptions(): array
    {
        return collect($this->coworkers)->map(fn ($c) => [
            'value' => $c->id,
            'label' => trim("{$c->firstname} {$c->lastname}"),
            'hint' => $c->email ?: $c->phone,
        ])->all();
    }

    protected function loadDraft(int $badgeRequestId): void
    {
        try {
            $query = BadgeRequest::with(['activityRequest.client', 'coworker'])
                ->where('id', $badgeRequestId)
                ->where('status', 'draft');

            if (! $this->user->isAdmin()) {
                $query->whereHas('activityRequest', function ($q) {
                    $q->where('client_id', $this->client->id);
                });
            }

            $badgeRequest = $query->firstOrFail();

            if ($this->user->isAdmin()) {
                $this->client = $badgeRequest->activityRequest->client;
                $this->selected_client_id = $this->client->id;
                $this->loadActivityRequests();
            }

            $formData = new BadgeRequestFormData;
            $formData->fillFromBadgeRequest($badgeRequest);
            $this->fillFromFormData($formData);

            $this->selected_activity_request_id = $badgeRequest->activity_request_id;
            $this->activityRequest = $badgeRequest->activityRequest;
            $this->loadCoworkers();
            $this->selected_coworker_id = $badgeRequest->coworker_id;

            $flags = $formData->getExistingDocumentsFlags($badgeRequest);
            $this->hasExistingSelfiePhoto = $flags['hasExistingSelfiePhoto'];
            $this->hasExistingIdentificationCard = $flags['hasExistingIdentificationCard'];
            $this->hasExistingActivityAuthorization = $flags['hasExistingActivityAuthorization'];
            $this->hasExistingForDocument = $flags['hasExistingForDocument'];
            $this->hasExistingFormationCertificate = $flags['hasExistingFormationCertificate'];
            $this->hasExistingInvoice = $flags['hasExistingInvoice'];

            $this->loadExistingDocumentNames($badgeRequest);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du brouillon de badge', [
                'error' => $e->getMessage(),
                'badge_request_id' => $badgeRequestId,
                'user_id' => $this->user->id,
            ]);

            $this->toast('Erreur lors du chargement du brouillon.', 'danger');
        }
    }

    protected function loadExistingDocumentNames(BadgeRequest $badgeRequest): void
    {
        $this->existing_selfie_photo_name = $this->basename($badgeRequest->selfie_photo);
        $this->existing_identification_card_name = $this->basename($badgeRequest->identification_card);
        $this->existing_activity_authorization_name = $this->basename($badgeRequest->activity_authorization);
        $this->existing_for_document_name = $this->basename($badgeRequest->for_document);
        $this->existing_formation_certificate_document_name = $this->basename($badgeRequest->formation_certificate_document);
        $this->existing_invoice_document_name = $this->basename($badgeRequest->invoice_document);
    }

    protected function basename(?string $path): ?string
    {
        return $path ? basename($path) : null;
    }

    public function updatedSelectedClientId($value): void
    {
        if ($value === '' || $value === null) {
            $this->selected_client_id = null;
            $this->client = null;
            $this->activityRequests = collect();
            $this->coworkers = collect();
            $this->selected_activity_request_id = null;
            $this->activityRequest = null;
            $this->selected_coworker_id = null;

            return;
        }

        $this->selected_client_id = (int) $value;
        $this->client = Client::find($this->selected_client_id);
        $this->loadActivityRequests();
        $this->loadCoworkers();
        $this->selected_activity_request_id = null;
        $this->activityRequest = null;
        $this->selected_coworker_id = null;
    }

    public function updatedSelectedActivityRequestId($value): void
    {
        if ($value === '' || $value === null || ! $this->client) {
            $this->selected_activity_request_id = null;
            $this->activityRequest = null;

            return;
        }

        $activityRequestId = (int) $value;
        $this->selected_activity_request_id = $activityRequestId;

        $this->activityRequest = ActivityRequest::with('client')
            ->where('id', $activityRequestId)
            ->where('client_id', $this->client->id)
            ->first();

        if (! $this->activityRequest) {
            $this->selected_activity_request_id = null;
            $this->toast("La demande d'activité sélectionnée n'existe pas pour ce client.", 'danger');

            return;
        }

        $this->loadCoworkers();
    }

    public function updatedSelfiePhoto($value): void
    {
        $this->selfie_photo_name = $value ? $value->getClientOriginalName() : null;
    }

    public function updatedIdentificationCard($value): void
    {
        $this->identification_card_name = $value ? $value->getClientOriginalName() : null;
    }

    public function updatedActivityAuthorization($value): void
    {
        $this->activity_authorization_name = $value ? $value->getClientOriginalName() : null;
    }

    public function updatedForDocument($value): void
    {
        $this->for_document_name = $value ? $value->getClientOriginalName() : null;
    }

    public function updatedFormationCertificateDocument($value): void
    {
        $this->formation_certificate_document_name = $value ? $value->getClientOriginalName() : null;
    }

    public function updatedInvoiceDocument($value): void
    {
        $this->invoice_document_name = $value ? $value->getClientOriginalName() : null;
    }

    public function saveDraft(): void
    {
        $this->processBadgeRequest(isDraft: true);
    }

    public function submit(): void
    {
        $this->processBadgeRequest(isDraft: false);
    }

    public function abandon(): void
    {
        $this->redirect(route('badge-requests.index'), navigate: true);
    }

    public function downloadForTemplate(string $airport)
    {
        $airport = strtoupper($airport);
        $fileName = "template-for-{$airport}.xlsx";
        $filePath = "templates/{$fileName}";

        if (! Storage::disk('public')->exists($filePath)) {
            $this->toast("Le template pour {$airport} n'existe pas.", 'warning');

            return null;
        }

        return response()->download(Storage::disk('public')->path($filePath), $fileName);
    }

    public function createNewCoworker(): void
    {
        if (! $this->client) {
            $this->toast("Sélectionnez d'abord un client.", 'warning');

            return;
        }

        $validationData = [
            'firstname' => $this->new_coworker_firstname,
            'lastname' => $this->new_coworker_lastname,
            'email' => $this->new_coworker_email,
            'phone' => $this->new_coworker_phone,
            'client_id' => $this->client->id,
            'has_leave' => false,
            'departure_date' => null,
            'create_user' => false,
        ];

        $validator = CoworkerValidator::validateCoworkerOnly($validationData);

        if ($validator->fails()) {
            $this->resetErrorBag([
                'new_coworker_firstname',
                'new_coworker_lastname',
                'new_coworker_email',
                'new_coworker_phone',
            ]);
            foreach ($validator->errors()->messages() as $field => $messages) {
                $this->addError("new_coworker_{$field}", $messages[0]);
            }
            $this->toast('Veuillez corriger les erreurs du formulaire.', 'danger');

            return;
        }

        try {
            $data = CreateCoworkerData::fromArray(
                array_merge($validationData, ['created_by' => $this->user->id]),
                $this->user->id
            );

            $action = app(CreateCoworkerAction::class);
            $result = $action->execute($data);

            if (! $result->isSuccessful()) {
                $this->toast($result->getMessage(), 'danger');

                return;
            }

            $this->loadCoworkers();
            $this->selected_coworker_id = $result->coworker->id;
            $this->resetNewCoworkerForm();

            // Pousse les nouvelles options + la valeur sélectionnée vers <x-ui.select options-target="coworker">
            // sans forcer Livewire à détruire/recréer le nœud (préserve l'état Alpine local).
            $this->dispatch(
                'select-options-updated',
                target: 'coworker',
                options: $this->buildCoworkerOptions(),
                value: $this->selected_coworker_id,
            );

            $this->dispatch('close-flyout', name: 'create-coworker');
            $this->toast('Collaborateur créé.', 'success');
        } catch (\Exception $e) {
            Log::error('Erreur création rapide collaborateur', [
                'error' => $e->getMessage(),
                'user_id' => $this->user->id,
                'client_id' => $this->client->id,
            ]);

            $this->toast('Erreur lors de la création du collaborateur.', 'danger');
        }
    }

    protected function resetNewCoworkerForm(): void
    {
        $this->reset([
            'new_coworker_firstname',
            'new_coworker_lastname',
            'new_coworker_email',
            'new_coworker_phone',
        ]);
        $this->resetErrorBag([
            'new_coworker_firstname',
            'new_coworker_lastname',
            'new_coworker_email',
            'new_coworker_phone',
        ]);
    }

    #[Computed]
    public function documentsProgress(): array
    {
        $checks = [
            ['selfie_photo', 'hasExistingSelfiePhoto', false],
            ['identification_card', 'hasExistingIdentificationCard', false],
            ['activity_authorization', 'hasExistingActivityAuthorization', false],
            ['for_document', 'hasExistingForDocument', false],
        ];

        if (! $this->validate_training) {
            $checks[] = ['formation_certificate_document', 'hasExistingFormationCertificate', false];
        }

        $filled = 0;
        foreach ($checks as [$prop, $existingFlag, $isMulti]) {
            $hasNew = $isMulti
                ? ! empty($this->{$prop})
                : ! is_null($this->{$prop});
            if ($hasNew || $this->{$existingFlag}) {
                $filled++;
            }
        }

        return ['filled' => $filled, 'total' => count($checks)];
    }

    #[Computed]
    public function quotaInfo(): ?array
    {
        if (! $this->activityRequest) {
            return null;
        }

        $remaining = $this->activityRequest->getRemainingBadgeQuota();
        $total = (int) $this->activityRequest->person_count;
        $used = max(0, $total - $remaining);

        return [
            'used' => $used,
            'total' => $total,
            'remaining' => $remaining,
            'canCreate' => $this->activityRequest->canCreateBadgeRequest(),
        ];
    }

    #[Computed]
    public function canSubmit(): bool
    {
        if ($this->user?->isAdmin() && ! $this->client) {
            return false;
        }

        if (! $this->client || ! $this->selected_activity_request_id || ! $this->selected_coworker_id) {
            return false;
        }

        $validator = BadgeRequestFormValidator::validate(
            $this->createFormData(),
            isDraft: false,
            isUpdate: ! is_null($this->badgeRequestId),
            existingDocuments: $this->getExistingDocumentsArray(),
        );

        if ($validator->fails()) {
            return false;
        }

        $quota = $this->quotaInfo;
        if ($quota && ! $quota['canCreate'] && ! $this->badgeRequestId) {
            return false;
        }

        return true;
    }

    protected function processBadgeRequest(bool $isDraft): void
    {
        try {
            if ($this->user->isAdmin() && ! $this->client) {
                $this->toast('Veuillez sélectionner un client.', 'warning');

                return;
            }

            if (! $this->selected_activity_request_id) {
                $this->toast("Veuillez sélectionner une demande d'activité.", 'warning');

                return;
            }

            if (! $this->selected_coworker_id) {
                $this->toast('Veuillez sélectionner un collaborateur.', 'warning');

                return;
            }

            $isUpdate = ! is_null($this->badgeRequestId);

            // Quota check (sauf pour les brouillons : ils ne sont pas comptés dans le quota).
            // Pour une promotion draft → pending_rem on doit vérifier : le draft passe en "compté".
            if (! $isDraft) {
                $activityRequest = ActivityRequest::findOrFail($this->selected_activity_request_id);

                if (! $activityRequest->canCreateBadgeRequest()) {
                    $remaining = $activityRequest->getRemainingBadgeQuota();
                    $this->toast(
                        "Quota de badges atteint pour cette demande d'activité ({$remaining} place(s) restante(s)).",
                        'danger'
                    );

                    return;
                }
            }

            $formData = $this->createFormData();

            // Pas de doublon actif pour ce coworker (drafts et rejetés sont OK).
            // En mode update on s'exclut soi-même (le draft qu'on est en train de promouvoir).
            if (! $isDraft) {
                $duplicateQuery = BadgeRequest::where('coworker_id', $formData->coworker_id)
                    ->whereNotIn('status', ['draft', 'rejected_rem', 'rejected_adp']);

                if ($isUpdate) {
                    $duplicateQuery->where('id', '!=', $this->badgeRequestId);
                }

                if ($duplicateQuery->exists()) {
                    $this->toast('Une demande de badge est déjà en cours pour ce collaborateur.', 'danger');

                    return;
                }
            }

            $existingDocs = $this->getExistingDocumentsArray();

            $validator = BadgeRequestFormValidator::validate(
                $formData,
                $isDraft,
                $isUpdate,
                $existingDocs
            );

            if ($validator->fails()) {
                $this->resetErrorBag();
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $this->addError($field, $messages[0]);
                }
                $this->toast('Veuillez corriger les erreurs du formulaire.', 'danger');

                return;
            }

            $data = CreateBadgeRequestData::fromFormData(
                $formData,
                $this->client->id,
                $this->user->id,
                $isDraft
            );

            $action = app(SaveBadgeRequestAction::class);
            $result = $action->execute($data, $this->client, $this->badgeRequestId);

            if (! $result->isSuccessful()) {
                $this->toast($result->getMessage(), 'danger');

                return;
            }

            if (! $isDraft) {
                $email = $this->client->notification_email ?: $this->user->email;
                Mail::to($email)->send(new BadgeRequestCreated($result->getBadgeRequest()));
            }

            session()->flash('toast', [
                'message' => $result->getMessage(),
                'variant' => 'success',
            ]);

            // Pas `navigate: true` ici : wire:navigate prefetche / mémorise les pages
            // côté client, donc le toast-stack rendu serait celui d'avant le flash
            // (pas de toast visible). Un redirect HTTP classique force un fresh GET,
            // mount() du toast-stack tourne, lit la session, pousse le toast → affiché.
            $this->redirect(route('badge-requests.index'));
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement de la demande de badge', [
                'error' => $e->getMessage(),
                'user_id' => $this->user->id,
                'is_draft' => $isDraft,
            ]);

            $this->toast('Une erreur est survenue. Veuillez réessayer.', 'danger');
        }
    }

    protected function createFormData(): BadgeRequestFormData
    {
        return BadgeRequestFormData::fromArray([
            'activity_request_id' => $this->selected_activity_request_id,
            'coworker_id' => $this->selected_coworker_id,
            'selfie_photo' => $this->selfie_photo,
            'identification_card' => $this->identification_card,
            'activity_authorization' => $this->activity_authorization,
            'for_document' => $this->for_document,
            'formation_certificate_document' => $this->formation_certificate_document,
            'invoice_document' => $this->invoice_document,
            'application_authorization' => $this->application_authorization,
            'validate_training' => $this->validate_training,
        ]);
    }

    protected function fillFromFormData(BadgeRequestFormData $formData): void
    {
        $this->application_authorization = $formData->application_authorization;
        $this->validate_training = $formData->validate_training;
    }

    protected function getExistingDocumentsArray(): array
    {
        return [
            'selfie_photo' => $this->hasExistingSelfiePhoto,
            'identification_card' => $this->hasExistingIdentificationCard,
            'activity_authorization' => $this->hasExistingActivityAuthorization,
            'for_document' => $this->hasExistingForDocument,
            'formation_certificate_document' => $this->hasExistingFormationCertificate,
            'invoice_document' => $this->hasExistingInvoice,
        ];
    }
}; ?>

@php
    $iconClass = 'h-5 w-5';
    $accordionWrapper = 'overflow-hidden rounded-lg border border-border bg-white shadow-sm';
    $accordionHeader = 'flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent';
    $accordionIconWrap = 'flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md bg-slate-100 text-foreground-muted';
    $accordionTitle = 'text-sm font-semibold text-foreground';
    $accordionSubtitle = 'text-xs text-foreground-muted';
    $accordionChevron = 'ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200';
    $accordionBody = 'border-t border-border p-5';

    $isAdmin = $user->isAdmin();
    $hasClient = (bool) $client;
    $clientPickerVisible = $isAdmin && ! $badgeRequestId;

    $airportMeta = [
        'CDG' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'ORY' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'LBG' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ];

    $activityOptions = collect($activityRequests)->map(fn ($ar) => [
        'value' => $ar->id,
        'label' => "Demande #{$ar->id} — {$ar->airport}",
        'hint' => trim((string) ($ar->description ?? '')),
    ])->all();

    $coworkerOptions = $this->buildCoworkerOptions();
@endphp

<div class="flex flex-1 flex-col">
<form wire:submit.prevent="submit" class="flex flex-1 flex-col">
    {{-- Zone de contenu (centrée, prend toute la hauteur restante) --}}
    <div class="flex-1 px-4 pb-8 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-3xl space-y-3">
            {{-- Section : Sélection du client (admin uniquement, mode création) --}}
            @if ($clientPickerVisible)
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg bg-amber-50/50 ring-1 ring-amber-200/70 ring-inset">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-amber-100/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md bg-amber-100 text-amber-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="{{ $iconClass }}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.249-8.25-3.285Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="{{ $accordionTitle }}">Sélection du client</div>
                            <div class="{{ $accordionSubtitle }}">Administration</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-amber-700/80 transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-amber-200/70 p-5">
                        <x-ui.select
                            id="select-client"
                            label="Client"
                            :value="$selected_client_id"
                            wire:model.live="selected_client_id"
                            :options="collect($allClients)->map(fn ($c) => ['value' => $c->id, 'label' => $c->company_name, 'hint' => 'SIRET '.$c->siret_number])->all()"
                            placeholder="Sélectionnez un client…"
                            search-placeholder="Filtrer par société ou SIRET…"
                            empty-text="Aucun client trouvé."
                            searchable
                            required
                        />
                        <p class="mt-2 text-xs text-amber-700/80 italic">
                            Cette demande sera créée au nom du client sélectionné.
                        </p>
                    </div>
                </div>
            @endif

            @if ($hasClient)
                {{-- Section : Demande d'activité --}}
                <div x-data="{ open: {{ $clientPickerVisible ? 'false' : 'true' }} }" class="{{ $accordionWrapper }}">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="{{ $accordionHeader }}">
                        <span class="{{ $accordionIconWrap }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="{{ $iconClass }}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.42 48.42 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="{{ $accordionTitle }}">Demande d'activité</div>
                            <div class="{{ $accordionSubtitle }}">
                                @if ($activityRequest)
                                    Demande #{{ $activityRequest->id }} — {{ $activityRequest->airport }}
                                @else
                                    Sélectionnez la demande d'activité de référence
                                @endif
                            </div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $accordionChevron }}">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="{{ $accordionBody }} space-y-4">
                        @if (count($activityOptions) === 0)
                            <x-ui.alert variant="warning">
                                Aucune demande d'activité approuvée n'est disponible pour cette société.
                            </x-ui.alert>
                        @else
                            <x-ui.select
                                id="select-activity-request"
                                label="Demande d'activité"
                                :value="$selected_activity_request_id"
                                wire:model.live="selected_activity_request_id"
                                :options="$activityOptions"
                                placeholder="Sélectionnez une demande…"
                                search-placeholder="Filtrer par numéro ou description…"
                                empty-text="Aucune demande trouvée."
                                searchable
                                required
                                :error="$errors->first('activity_request_id')"
                            />
                        @endif

                        @if ($activityRequest)
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="rounded-md border border-border bg-slate-50 p-3">
                                    <div class="text-xs font-medium text-foreground-subtle uppercase tracking-wide">Aéroport</div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $airportMeta[$activityRequest->airport] ?? 'bg-slate-50 text-slate-700 ring-slate-200' }}">
                                            {{ $activityRequest->airport }}
                                        </span>
                                    </div>
                                </div>

                                @php $quota = $this->quotaInfo; @endphp
                                @if ($quota)
                                    <div class="rounded-md border border-border bg-slate-50 p-3 sm:col-span-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="text-xs font-medium text-foreground-subtle uppercase tracking-wide">Quota badges</div>
                                            <span @class([
                                                'text-xs font-semibold',
                                                'text-emerald-700' => $quota['remaining'] > 0,
                                                'text-red-700' => $quota['remaining'] === 0,
                                            ])>
                                                {{ $quota['used'] }} / {{ $quota['total'] }} utilisés
                                            </span>
                                        </div>
                                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                                            <div
                                                @class([
                                                    'h-full transition-all',
                                                    'bg-emerald-500' => $quota['remaining'] > 0,
                                                    'bg-red-500' => $quota['remaining'] === 0,
                                                ])
                                                style="width: {{ $quota['total'] > 0 ? min(100, ($quota['used'] / $quota['total']) * 100) : 0 }}%"
                                            ></div>
                                        </div>
                                        @if (! $quota['canCreate'] && ! $badgeRequestId)
                                            <p class="mt-2 text-xs text-red-600">Quota atteint — aucun badge supplémentaire ne peut être créé pour cette demande.</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Section : Collaborateur --}}
                <div x-data="{ open: false }" class="{{ $accordionWrapper }}">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="{{ $accordionHeader }}">
                        <span class="{{ $accordionIconWrap }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="{{ $iconClass }}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="{{ $accordionTitle }}">Collaborateur</div>
                            <div class="{{ $accordionSubtitle }}">
                                @if ($selected_coworker_id && $coworker = $coworkers->firstWhere('id', $selected_coworker_id))
                                    {{ $coworker->firstname }} {{ $coworker->lastname }}
                                @else
                                    Choisissez le collaborateur concerné
                                @endif
                            </div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $accordionChevron }}">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="{{ $accordionBody }} space-y-3">
                        <x-ui.select
                            id="select-coworker"
                            label="Collaborateur"
                            :value="$selected_coworker_id"
                            wire:model.live="selected_coworker_id"
                            :options="$coworkerOptions"
                            options-target="coworker"
                            placeholder="Sélectionnez un collaborateur…"
                            search-placeholder="Filtrer par nom, email…"
                            empty-text="Aucun collaborateur trouvé pour ce client."
                            searchable
                            required
                            :error="$errors->first('coworker_id')"
                        />

                        <button
                            type="button"
                            @click="$dispatch('open-flyout', { name: 'create-coworker' })"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-accent transition hover:text-accent-content"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Créer un nouveau collaborateur
                        </button>
                    </div>
                </div>

                {{-- Section : Options --}}
                <div x-data="{ open: false }" class="{{ $accordionWrapper }}">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="{{ $accordionHeader }}">
                        <span class="{{ $accordionIconWrap }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="{{ $iconClass }}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="{{ $accordionTitle }}">Options</div>
                            <div class="{{ $accordionSubtitle }}">Habilitation et formation</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $accordionChevron }}">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="{{ $accordionBody }} space-y-3">
                        <x-ui.checkbox
                            wire:model.live="application_authorization"
                            :checked="$application_authorization"
                            label="Demande d'habilitation"
                            description="Cocher si une demande d'habilitation préfectorale est nécessaire pour ce collaborateur."
                        />

                        <x-ui.checkbox
                            wire:model.live="validate_training"
                            :checked="$validate_training"
                            label="Valider la formation"
                            description="Cocher si la formation est validée. Sinon, un certificat de formation devra être joint dans les documents."
                        />
                    </div>
                </div>

                {{-- Section : Documents --}}
                <div x-data="{ open: false }" class="{{ $accordionWrapper }}">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="{{ $accordionHeader }}">
                        <span class="{{ $accordionIconWrap }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="{{ $iconClass }}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="{{ $accordionTitle }}">Documents</div>
                            @php
                                $progress = $this->documentsProgress;
                                $isComplete = $progress['filled'] === $progress['total'];
                            @endphp
                            <div class="{{ $accordionSubtitle }} flex items-center gap-1.5">
                                <span @class(['text-emerald-700 font-semibold' => $isComplete])>
                                    {{ $progress['filled'] }} / {{ $progress['total'] }}
                                </span>
                                <span>documents fournis</span>
                                @if ($isComplete)
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor" class="h-3.5 w-3.5 text-emerald-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $accordionChevron }}">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="{{ $accordionBody }} space-y-4">
                        @if ($badgeRequestId && ($hasExistingSelfiePhoto || $hasExistingIdentificationCard || $hasExistingActivityAuthorization || $hasExistingForDocument || $hasExistingFormationCertificate || $hasExistingInvoice))
                            <x-ui.alert variant="info">
                                Des documents existent déjà pour ce brouillon. Téléversez de nouveaux fichiers pour les remplacer, sinon les documents existants seront conservés.
                            </x-ui.alert>
                        @endif

                        <div class="grid grid-cols-1 items-start gap-3 sm:grid-cols-2">
                            <x-ui.file-upload
                                name="selfie_photo"
                                wire:model="selfie_photo"
                                label="Photo d'identité"
                                hint="JPG, PNG ou PDF — 10 Mo max"
                                accept=".jpg,.jpeg,.png,.pdf"
                                :required="! $hasExistingSelfiePhoto"
                                :new-files="$selfie_photo_name"
                                :existing-files="$existing_selfie_photo_name"
                                :error="$errors->first('selfie_photo')"
                            />

                            <x-ui.file-upload
                                name="identification_card"
                                wire:model="identification_card"
                                label="Carte d'identité"
                                hint="PDF, JPG ou PNG — 10 Mo max"
                                accept=".pdf,.jpg,.jpeg,.png"
                                :required="! $hasExistingIdentificationCard"
                                :new-files="$identification_card_name"
                                :existing-files="$existing_identification_card_name"
                                :error="$errors->first('identification_card')"
                            />

                            <div class="sm:col-span-2">
                                <x-ui.file-upload
                                    name="activity_authorization"
                                    wire:model="activity_authorization"
                                    label="Autorisation d'activité"
                                    hint="PDF, JPG ou PNG — 10 Mo max"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    :required="! $hasExistingActivityAuthorization"
                                    :new-files="$activity_authorization_name"
                                    :existing-files="$existing_activity_authorization_name"
                                    :error="$errors->first('activity_authorization')"
                                />
                            </div>

                            <div class="sm:col-span-2 space-y-2">
                                <x-ui.file-upload
                                    name="for_document"
                                    wire:model="for_document"
                                    label="Document FOR"
                                    hint="XLSX ou XLS — 10 Mo max"
                                    accept=".xlsx,.xls"
                                    :required="! $hasExistingForDocument"
                                    :new-files="$for_document_name"
                                    :existing-files="$existing_for_document_name"
                                    :error="$errors->first('for_document')"
                                />
                                @if ($activityRequest)
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="text-foreground-muted">Modèle FOR pour {{ $activityRequest->airport }} :</span>
                                        <button
                                            type="button"
                                            wire:click="downloadForTemplate('{{ $activityRequest->airport }}')"
                                            class="inline-flex items-center gap-1 font-semibold text-accent transition hover:text-accent-content"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                            Télécharger le template
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if (! $validate_training)
                                <div class="sm:col-span-2">
                                    <x-ui.file-upload
                                        name="formation_certificate_document"
                                        wire:model="formation_certificate_document"
                                        label="Certificat de formation"
                                        hint="PDF — 8 Mo max"
                                        accept=".pdf"
                                        :required="! $hasExistingFormationCertificate"
                                        :new-files="$formation_certificate_document_name"
                                        :existing-files="$existing_formation_certificate_document_name"
                                        :error="$errors->first('formation_certificate_document')"
                                    />
                                </div>
                            @endif

                            <div class="sm:col-span-2">
                                <x-ui.file-upload
                                    name="invoice_document"
                                    wire:model="invoice_document"
                                    label="Facture (optionnel)"
                                    hint="PDF, JPG ou PNG — 10 Mo max"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    :new-files="$invoice_document_name"
                                    :existing-files="$existing_invoice_document_name"
                                    :error="$errors->first('invoice_document')"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Barre d'actions fixe en bas, pleine largeur --}}
    <div class="sticky bottom-0 z-10 border-t border-border bg-white shadow-[0_-4px_12px_-4px_rgba(15,23,42,0.08)]">
        <div class="mx-auto flex w-full max-w-3xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
            <x-ui.button
                type="button"
                variant="ghost"
                wire:click="abandon"
                wire:confirm="Abandonner cette demande ? Les modifications non enregistrées seront perdues."
                wire:loading.attr="disabled"
                wire:target="abandon,saveDraft,submit"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                Abandonner
            </x-ui.button>

            <div class="flex items-center gap-2">
                <x-ui.button
                    type="button"
                    variant="secondary"
                    wire:click="saveDraft"
                    wire:loading.attr="disabled"
                    wire:target="abandon,saveDraft,submit"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 21h-15a1.5 1.5 0 0 1-1.5-1.5v-15A1.5 1.5 0 0 1 4.5 3h11.379a1.5 1.5 0 0 1 1.06.44l3.122 3.12a1.5 1.5 0 0 1 .439 1.061V19.5a1.5 1.5 0 0 1-1.5 1.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3v5.25a.75.75 0 0 0 .75.75h6a.75.75 0 0 0 .75-.75V3M7.5 21v-6.75a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 .75.75V21" />
                    </svg>
                    <span wire:loading.remove wire:target="saveDraft">
                        {{ $badgeRequestId ? 'Mettre à jour le brouillon' : 'Brouillon' }}
                    </span>
                    <span wire:loading wire:target="saveDraft">Enregistrement...</span>
                </x-ui.button>

                <x-ui.button
                    type="submit"
                    variant="primary"
                    :disabled="! $this->canSubmit"
                    wire:loading.attr="disabled"
                    wire:target="abandon,saveDraft,submit"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <span wire:loading.remove wire:target="submit">
                        {{ $badgeRequestId ? 'Soumettre la demande' : 'Créer la demande' }}
                    </span>
                    <span wire:loading wire:target="submit">Envoi...</span>
                </x-ui.button>
            </div>
        </div>
    </div>
</form>

{{-- Flyout : sorti du <form> principal pour éviter que ses inputs `required` cachés
     bloquent la validation HTML5 native du form parent ("invalid form control not focusable"). --}}
    <x-ui.flyout
        name="create-coworker"
        title="Nouveau collaborateur"
        description="Crée un collaborateur rattaché au client en cours."
        max-width="md"
    >
        <div class="space-y-4">
            @if (! $client)
                <x-ui.alert variant="warning">
                    Sélectionnez d'abord un client.
                </x-ui.alert>
            @else
                <div class="rounded-md border border-border bg-slate-50 px-3 py-2 text-xs text-foreground-muted">
                    Société : <span class="font-semibold text-foreground">{{ $client->company_name }}</span>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <x-ui.input
                        label="Prénom"
                        wire:model.blur="new_coworker_firstname"
                        :error="$errors->first('new_coworker_firstname')"
                        required
                    />
                    <x-ui.input
                        label="Nom"
                        wire:model.blur="new_coworker_lastname"
                        :error="$errors->first('new_coworker_lastname')"
                        required
                    />
                    <x-ui.input
                        label="Email"
                        type="email"
                        wire:model.blur="new_coworker_email"
                        :error="$errors->first('new_coworker_email')"
                        class="sm:col-span-2"
                    />
                    <x-ui.input
                        label="Téléphone"
                        wire:model.blur="new_coworker_phone"
                        :error="$errors->first('new_coworker_phone')"
                        class="sm:col-span-2"
                    />
                </div>
            @endif
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-end gap-2">
                <x-ui.button
                    type="button"
                    variant="ghost"
                    @click="$dispatch('close-flyout', { name: 'create-coworker' })"
                >
                    Annuler
                </x-ui.button>
                <x-ui.button
                    type="button"
                    variant="primary"
                    wire:click="createNewCoworker"
                    wire:loading.attr="disabled"
                    wire:target="createNewCoworker"
                    :disabled="! $client"
                >
                    <span wire:loading.remove wire:target="createNewCoworker">Créer le collaborateur</span>
                    <span wire:loading wire:target="createNewCoworker">Création...</span>
                </x-ui.button>
            </div>
        </x-slot:footer>
    </x-ui.flyout>
</div>
