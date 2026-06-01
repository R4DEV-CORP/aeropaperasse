<?php

use App\Actions\ActivityRequest\SaveActivityRequestAction;
use App\DataTransferObjects\CreateActivityRequestData;
use App\Forms\ActivityRequestFormData;
use App\Forms\ActivityRequestFormValidator;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\ActivityRequestCreated;
use App\Models\ActivityRequest;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

    public ?int $activityRequestId = null;

    // Responsable
    public ?string $manager_firstname = null;

    public ?string $manager_lastname = null;

    public ?string $manager_email = null;

    public ?string $manager_phone = null;

    public ?string $manager_role = null;

    // Activité
    public ?string $airport = null;

    public ?string $description = null;

    public ?string $customer_names = null;

    public $person_count = null;

    public $vehicule_count = null;

    // Documents
    public $aao_request_document;

    public $kbis_document;

    public $principals = [];

    public $safety_referent_document;

    public $security_referent_document;

    public $cta_document;

    // Noms des fichiers nouvellement sélectionnés (affichage)
    public ?string $aao_request_document_name = null;

    public ?string $kbis_document_name = null;

    public array $principals_names = [];

    public ?string $safety_referent_document_name = null;

    public ?string $security_referent_document_name = null;

    public ?string $cta_document_name = null;

    // Indicateurs de documents existants (édition de brouillon)
    public bool $hasExistingAaoRequest = false;

    public bool $hasExistingKbis = false;

    public bool $hasExistingPrincipals = false;

    public bool $hasExistingSafetyReferent = false;

    public bool $hasExistingSecurityReferent = false;

    public bool $hasExistingCta = false;

    public ?string $existing_aao_request_document_name = null;

    public ?string $existing_kbis_document_name = null;

    public array $existing_principals_names = [];

    public ?string $existing_safety_referent_document_name = null;

    public ?string $existing_security_referent_document_name = null;

    public ?string $existing_cta_document_name = null;

    public function mount(?int $activityRequestId = null): void
    {
        $this->user = auth()->user();
        $this->activityRequestId = $activityRequestId;

        if ($this->user->isTenantManager()) {
            $this->allClients = Client::orderBy('company_name')->get();
        } else {
            $this->client = $this->user->client;
        }

        if ($this->activityRequestId) {
            $this->loadDraft($this->activityRequestId);
        }
    }

    protected function loadDraft(int $activityRequestId): void
    {
        try {
            $query = ActivityRequest::where('id', $activityRequestId)->where('status', 'draft');

            if (! $this->user->isTenantManager()) {
                $query->where('client_id', $this->client->id);
            }

            $activityRequest = $query->firstOrFail();

            if ($this->user->isTenantManager()) {
                $this->client = $activityRequest->client;
                $this->selected_client_id = $this->client->id;
            }

            $formData = new ActivityRequestFormData;
            $formData->fillFromActivityRequest($activityRequest);
            $this->fillFromFormData($formData);

            $flags = $formData->getExistingDocumentsFlags($activityRequest);
            $this->hasExistingAaoRequest = $flags['hasExistingAaoRequest'];
            $this->hasExistingKbis = $flags['hasExistingKbis'];
            $this->hasExistingPrincipals = $flags['hasExistingPrincipals'];
            $this->hasExistingSafetyReferent = $flags['hasExistingSafetyReferent'];
            $this->hasExistingSecurityReferent = $flags['hasExistingSecurityReferent'];
            $this->hasExistingCta = $flags['hasExistingCta'];

            $this->loadExistingDocumentNames($activityRequest);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du brouillon', [
                'error' => $e->getMessage(),
                'activity_request_id' => $activityRequestId,
                'user_id' => $this->user->id,
            ]);

            $this->toast('Erreur lors du chargement du brouillon.', 'danger');
        }
    }

    protected function loadExistingDocumentNames(ActivityRequest $activityRequest): void
    {
        if (! $activityRequest->relationLoaded('attachments')) {
            $activityRequest->load('attachments');
        }

        $this->existing_aao_request_document_name = optional($activityRequest->getAaoRequestDocument())->name;
        $this->existing_kbis_document_name = optional($activityRequest->getKbisDocument())->name;
        $this->existing_principals_names = $activityRequest->getPrincipalsDocuments()
            ->map(fn ($attachment) => $attachment->name)->toArray();
        $this->existing_safety_referent_document_name = optional($activityRequest->getSafetyReferentDocument())->name;
        $this->existing_security_referent_document_name = optional($activityRequest->getSecurityReferentDocument())->name;
        $this->existing_cta_document_name = optional($activityRequest->getCtaDocument())->name;
    }

    public function updatedSelectedClientId($value): void
    {
        if ($value === '' || $value === null) {
            $this->selected_client_id = null;
            $this->client = null;

            return;
        }

        $this->selected_client_id = (int) $value;
        $this->client = Client::find($this->selected_client_id);
    }

    public function updatedAaoRequestDocument($value): void
    {
        $this->aao_request_document_name = $value ? $value->getClientOriginalName() : null;
    }

    public function updatedKbisDocument($value): void
    {
        $this->kbis_document_name = $value ? $value->getClientOriginalName() : null;
    }

    public function updatedPrincipals($value): void
    {
        $this->principals_names = [];

        if (! $value) {
            return;
        }

        $files = is_array($value) ? $value : (is_iterable($value) ? iterator_to_array($value) : [$value]);

        foreach ($files as $file) {
            if (is_object($file) && method_exists($file, 'getClientOriginalName')) {
                $this->principals_names[] = $file->getClientOriginalName();
            }
        }
    }

    public function updatedSafetyReferentDocument($value): void
    {
        $this->safety_referent_document_name = $value ? $value->getClientOriginalName() : null;
    }

    public function updatedSecurityReferentDocument($value): void
    {
        $this->security_referent_document_name = $value ? $value->getClientOriginalName() : null;
    }

    public function updatedCtaDocument($value): void
    {
        $this->cta_document_name = $value ? $value->getClientOriginalName() : null;
    }

    public function saveDraft(): void
    {
        $this->processActivityRequest(isDraft: true);
    }

    public function submit(): void
    {
        $this->processActivityRequest(isDraft: false);
    }

    public function abandon(): void
    {
        $this->redirect(route('activity-requests.index'), navigate: true);
    }

    #[Computed]
    public function documentsProgress(): array
    {
        $checks = [
            ['aao_request_document', 'hasExistingAaoRequest', false],
            ['kbis_document', 'hasExistingKbis', false],
            ['principals', 'hasExistingPrincipals', true],
            ['safety_referent_document', 'hasExistingSafetyReferent', false],
            ['security_referent_document', 'hasExistingSecurityReferent', false],
        ];

        if ($this->client?->is_airline_company) {
            $checks[] = ['cta_document', 'hasExistingCta', false];
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
    public function canSubmit(): bool
    {
        if ($this->user?->isTenantManager() && ! $this->client) {
            return false;
        }

        if (! $this->client) {
            return false;
        }

        $validator = ActivityRequestFormValidator::validate(
            $this->createFormData(),
            isDraft: false,
            isUpdate: ! is_null($this->activityRequestId),
            existingDocuments: $this->getExistingDocumentsArray(),
            client: $this->client,
        );

        return ! $validator->fails();
    }

    protected function processActivityRequest(bool $isDraft): void
    {
        try {
            if ($this->user->isTenantManager() && ! $this->client) {
                $this->toast('Veuillez sélectionner un client.', 'warning');

                return;
            }

            $formData = $this->createFormData();
            $isUpdate = ! is_null($this->activityRequestId);
            $existingDocs = $this->getExistingDocumentsArray();

            $validator = ActivityRequestFormValidator::validate(
                $formData,
                $isDraft,
                $isUpdate,
                $existingDocs,
                $this->client
            );

            if ($validator->fails()) {
                $this->resetErrorBag();
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $this->addError($field, $messages[0]);
                }
                $this->toast('Veuillez corriger les erreurs du formulaire.', 'danger');

                return;
            }

            $data = CreateActivityRequestData::fromFormData(
                $formData,
                $this->client->id,
                $this->user->id,
                $isDraft
            );

            $action = app(SaveActivityRequestAction::class);
            $result = $action->execute($data, $this->client, $this->activityRequestId);

            if (! $result->isSuccessful()) {
                $this->toast($result->getMessage(), 'danger');

                return;
            }

            if (! $isDraft) {
                $email = $this->client->notification_email ?: $this->user->email;
                Mail::to($email)->send(new ActivityRequestCreated($result->getActivityRequest()));
            }

            session()->flash('toast', [
                'message' => $result->getMessage(),
                'variant' => 'success',
            ]);

            $this->redirect(route('activity-requests.index'), navigate: true);
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement de la demande d\'activité', [
                'error' => $e->getMessage(),
                'user_id' => $this->user->id,
                'is_draft' => $isDraft,
            ]);

            $this->toast('Une erreur est survenue. Veuillez réessayer.', 'danger');
        }
    }

    protected function createFormData(): ActivityRequestFormData
    {
        return ActivityRequestFormData::fromArray([
            'manager_firstname' => $this->manager_firstname,
            'manager_lastname' => $this->manager_lastname,
            'manager_email' => $this->manager_email,
            'manager_phone' => $this->manager_phone,
            'manager_role' => $this->manager_role,
            'airport' => $this->airport,
            'description' => $this->description,
            'customer_names' => $this->customer_names,
            'person_count' => $this->person_count,
            'vehicule_count' => $this->vehicule_count,
            'aao_request_document' => $this->aao_request_document,
            'kbis_document' => $this->kbis_document,
            'principals' => $this->principals,
            'safety_referent_document' => $this->safety_referent_document,
            'security_referent_document' => $this->security_referent_document,
            'cta_document' => $this->cta_document,
            'renewal' => false,
            'last_activity_request_id' => null,
        ]);
    }

    protected function fillFromFormData(ActivityRequestFormData $formData): void
    {
        $this->manager_firstname = $formData->manager_firstname;
        $this->manager_lastname = $formData->manager_lastname;
        $this->manager_email = $formData->manager_email;
        $this->manager_phone = $formData->manager_phone;
        $this->manager_role = $formData->manager_role;
        $this->airport = $formData->airport;
        $this->description = $formData->description;
        $this->customer_names = $formData->customer_names;
        $this->person_count = $formData->person_count;
        $this->vehicule_count = $formData->vehicule_count;
    }

    protected function getExistingDocumentsArray(): array
    {
        return [
            'aao_request_document' => $this->hasExistingAaoRequest,
            'kbis_document' => $this->hasExistingKbis,
            'principals' => $this->hasExistingPrincipals,
            'safety_referent_document' => $this->hasExistingSafetyReferent,
            'security_referent_document' => $this->hasExistingSecurityReferent,
            'cta_document' => $this->hasExistingCta,
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

    $isAdmin = $user->isTenantManager();
    $hasClient = (bool) $client;
    $clientPickerVisible = $isAdmin && ! $activityRequestId;

    $documentsCount = ($client && $client->is_airline_company) ? 6 : 5;
@endphp

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
        {{-- Section : Informations sur la société --}}
        <div x-data="{ open: {{ $clientPickerVisible ? 'false' : 'true' }} }" class="{{ $accordionWrapper }}">
            <button type="button" @click="open = !open" :aria-expanded="open" class="{{ $accordionHeader }}">
                <span class="{{ $accordionIconWrap }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="{{ $iconClass }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <div class="{{ $accordionTitle }}">Informations sur la société</div>
                    <div class="{{ $accordionSubtitle }}">{{ $client->company_name }}</div>
                </div>
                <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $accordionChevron }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div x-show="open" x-collapse class="{{ $accordionBody }} space-y-4">
                @if (! $isAdmin)
                    <x-ui.alert variant="info">
                        Pour modifier les informations de la société, rendez-vous sur la page société.
                    </x-ui.alert>
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.input label="Raison sociale" :value="$client->company_name" disabled />
                    <x-ui.input label="Nom commercial" :value="$client->trade_name" disabled />
                    <x-ui.input label="Numéro SIRET" :value="$client->siret_number" disabled />
                    <x-ui.input label="Adresse" :value="$client->address" disabled />
                    <x-ui.input label="Code postal" :value="$client->zip_code" disabled />
                    <x-ui.input label="Ville" :value="$client->city" disabled />
                </div>
            </div>
        </div>

        {{-- Section : Responsable --}}
        <div x-data="{ open: false }" class="{{ $accordionWrapper }}">
            <button type="button" @click="open = !open" :aria-expanded="open" class="{{ $accordionHeader }}">
                <span class="{{ $accordionIconWrap }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="{{ $iconClass }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <div class="{{ $accordionTitle }}">Responsable</div>
                    <div class="{{ $accordionSubtitle }}">Coordonnées du responsable</div>
                </div>
                <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $accordionChevron }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div x-show="open" x-collapse class="{{ $accordionBody }}">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.input
                        label="Prénom"
                        wire:model.blur="manager_firstname"
                        :error="$errors->first('manager_firstname')"
                        required
                    />
                    <x-ui.input
                        label="Nom"
                        wire:model.blur="manager_lastname"
                        :error="$errors->first('manager_lastname')"
                        required
                    />
                    <x-ui.input
                        label="Email"
                        type="email"
                        wire:model.blur="manager_email"
                        :error="$errors->first('manager_email')"
                        required
                    />
                    <x-ui.input
                        label="Téléphone"
                        wire:model.blur="manager_phone"
                        :error="$errors->first('manager_phone')"
                        required
                    />
                    <x-ui.input
                        label="Fonction du responsable"
                        wire:model.blur="manager_role"
                        :error="$errors->first('manager_role')"
                        class="sm:col-span-2"
                        required
                    />
                </div>
            </div>
        </div>

        {{-- Section : Informations sur l'activité --}}
        <div x-data="{ open: false }" class="{{ $accordionWrapper }}">
            <button type="button" @click="open = !open" :aria-expanded="open" class="{{ $accordionHeader }}">
                <span class="{{ $accordionIconWrap }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="{{ $iconClass }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.42 48.42 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <div class="{{ $accordionTitle }}">Informations sur l'activité</div>
                    <div class="{{ $accordionSubtitle }}">Aéroport, description, effectifs</div>
                </div>
                <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="{{ $accordionChevron }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div x-show="open" x-collapse class="{{ $accordionBody }} space-y-5">
                <x-ui.radio-cards
                    label="Aéroport"
                    name="airport"
                    wire:model.change="airport"
                    :value="$airport"
                    :error="$errors->first('airport')"
                    :options="[
                        ['value' => 'CDG', 'label' => 'CDG', 'description' => 'Roissy Charles de Gaulle'],
                        ['value' => 'ORY', 'label' => 'ORY', 'description' => 'Paris Orly'],
                        ['value' => 'LBG', 'label' => 'LBG', 'description' => 'Le Bourget'],
                    ]"
                    columns="3"
                    required
                />

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.textarea
                        label="Description de l'activité"
                        wire:model.blur="description"
                        :error="$errors->first('description')"
                        :rows="3"
                        required
                    />
                    <x-ui.textarea
                        label="Dénomination des clients"
                        wire:model.blur="customer_names"
                        :error="$errors->first('customer_names')"
                        :rows="3"
                        required
                    />
                    <x-ui.input
                        label="Nombre de personnes"
                        type="number"
                        min="1"
                        max="1000"
                        wire:model.blur="person_count"
                        :error="$errors->first('person_count')"
                        required
                    />
                    <x-ui.input
                        label="Nombre de véhicules"
                        type="number"
                        min="0"
                        max="1000"
                        wire:model.blur="vehicule_count"
                        :error="$errors->first('vehicule_count')"
                        required
                    />
                </div>
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
                @if ($activityRequestId && ($hasExistingAaoRequest || $hasExistingKbis || $hasExistingPrincipals || $hasExistingSafetyReferent || $hasExistingSecurityReferent || $hasExistingCta))
                    <x-ui.alert variant="info">
                        Des documents existent déjà pour ce brouillon. Téléversez de nouveaux fichiers pour les remplacer, sinon les documents existants seront conservés.
                    </x-ui.alert>
                @endif

                <div class="grid grid-cols-1 items-start gap-3 sm:grid-cols-2">
                    <x-ui.file-upload
                        name="aao_request_document"
                        wire:model="aao_request_document"
                        label="Demande AAO"
                        :required="! $hasExistingAaoRequest"
                        :new-files="$aao_request_document_name"
                        :existing-files="$existing_aao_request_document_name"
                        :error="$errors->first('aao_request_document')"
                    />

                    <x-ui.file-upload
                        name="kbis_document"
                        wire:model="kbis_document"
                        label="Extrait KBIS"
                        :required="! $hasExistingKbis"
                        :new-files="$kbis_document_name"
                        :existing-files="$existing_kbis_document_name"
                        :error="$errors->first('kbis_document')"
                    />

                    <div class="sm:col-span-2">
                        <x-ui.file-upload
                            name="principals"
                            wire:model="principals"
                            label="Donneurs d'ordre"
                            :required="! $hasExistingPrincipals"
                            :new-files="$principals_names"
                            :existing-files="$existing_principals_names"
                            :error="$errors->first('principals') ?: $errors->first('principals.*')"
                            multiple
                        />
                    </div>

                    <x-ui.file-upload
                        name="safety_referent_document"
                        wire:model="safety_referent_document"
                        label="Référent sûreté"
                        :required="! $hasExistingSafetyReferent"
                        :new-files="$safety_referent_document_name"
                        :existing-files="$existing_safety_referent_document_name"
                        :error="$errors->first('safety_referent_document')"
                    />

                    <x-ui.file-upload
                        name="security_referent_document"
                        wire:model="security_referent_document"
                        label="Référent sécurité"
                        :required="! $hasExistingSecurityReferent"
                        :new-files="$security_referent_document_name"
                        :existing-files="$existing_security_referent_document_name"
                        :error="$errors->first('security_referent_document')"
                    />

                    @if ($client && $client->is_airline_company)
                        <div class="sm:col-span-2">
                            <x-ui.file-upload
                                name="cta_document"
                                wire:model="cta_document"
                                label="CTA"
                                hint="Requis pour les compagnies aériennes."
                                :required="! $hasExistingCta"
                                :new-files="$cta_document_name"
                                :existing-files="$existing_cta_document_name"
                                :error="$errors->first('cta_document')"
                            />
                        </div>
                    @endif
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
                        {{ $activityRequestId ? 'Mettre à jour le brouillon' : 'Brouillon' }}
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
                        {{ $activityRequestId ? 'Soumettre la demande' : 'Créer la demande' }}
                    </span>
                    <span wire:loading wire:target="submit">Envoi...</span>
                </x-ui.button>
            </div>
        </div>
    </div>
</form>
