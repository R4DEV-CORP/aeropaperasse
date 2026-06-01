<?php

use App\Actions\Coworker\CreateCoworkerAction;
use App\DataTransferObjects\CreateCoworkerData;
use App\Enums\Role;
use App\Mail\UserCreated;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\CoworkerTraining;
use App\Models\Training;
use App\Models\User;
use App\Validators\CoworkerValidator;
use App\Livewire\Concerns\InteractsWithToasts;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Collaborateurs & utilisateurs', 'href' => '/coworkers'],
        ['label' => 'Formulaire'],
    ],
])]
#[Title('Collaborateur')]
class extends Component
{
    use InteractsWithToasts;

    public ?int $coworkerId = null;

    public bool $isEdit = false;

    public ?int $selected_client_id = null;

    public string $firstname = '';

    public string $lastname = '';

    public ?string $email = null;

    public ?string $phone = null;

    public bool $has_leave = false;

    public ?string $departure_date = null;

    public bool $create_user = false;

    public bool $has_user_account = false;

    public ?string $password = null;

    public ?string $password_confirmation = null;

    public bool $can_access_formation = false;

    public string $role = 'client';

    /**
     * Format: ['training_id' => ['selected' => bool, 'start_date' => 'YYYY-MM-DD', 'validity_years' => '2|3|5|lifetime', 'pivot_id' => ?int]]
     */
    public array $selected_trainings = [];

    public function mount(?int $coworkerId = null): void
    {
        $authUser = auth()->user();

        if ($authUser->isClient()) {
            $this->redirect(route('companies.show', ['companyId' => $authUser->contextualClientId()]), navigate: true);

            return;
        }

        if ($coworkerId !== null) {
            // `coworkerTrainings` (HasMany, tenant) instead of `trainings` (belongsToMany cross-DB JOIN).
            // The tenant pivot rows hold everything we need here (training_id + dates).
            $coworker = Coworker::with(['user', 'client', 'coworkerTrainings'])->find($coworkerId);
            if (! $coworker) {
                abort(404);
            }

            if (! $authUser->isTenantManager() && $coworker->client_id !== $authUser->contextualClientId()) {
                abort(403);
            }

            $this->isEdit = true;
            $this->coworkerId = $coworker->id;
            $this->selected_client_id = $coworker->client_id;
            $this->firstname = $coworker->firstname;
            $this->lastname = $coworker->lastname;
            $this->email = $coworker->email;
            $this->phone = $coworker->phone;
            $this->has_leave = (bool) $coworker->has_leave;
            $this->departure_date = $coworker->departure_date?->format('Y-m-d');

            if ($coworker->user_id && $coworker->user) {
                $this->has_user_account = true;
                // Lit le flag formation sur le tenant courant via la méthode contextuelle
                // (pivot first, fallback colonne dépréciée).
                $this->can_access_formation = $coworker->user->canAccessFormation();
                $this->role = $coworker->user->role;
            }

            foreach ($coworker->coworkerTrainings as $ct) {
                $startedAt = $ct->started_at ? Carbon::parse($ct->started_at) : null;
                $expiresAt = $ct->expires_at ? Carbon::parse($ct->expires_at) : null;

                $validity = 'lifetime';
                if ($startedAt && $expiresAt) {
                    $diff = $startedAt->diffInYears($expiresAt);
                    if (in_array($diff, [2, 3, 5], true)) {
                        $validity = (string) $diff;
                    }
                }

                $this->selected_trainings[$ct->training_id] = [
                    'selected' => true,
                    'start_date' => $startedAt?->format('Y-m-d'),
                    'validity_years' => $validity,
                    'pivot_id' => $ct->id,
                ];
            }
        } else {
            if (! $authUser->isTenantManager()) {
                $this->selected_client_id = $authUser->contextualClientId();
            }
        }
    }

    #[Computed]
    public function clients()
    {
        return Client::orderBy('company_name')->get();
    }

    #[Computed]
    public function trainings()
    {
        return Training::orderBy('title')->get();
    }

    /**
     * Auto-upgrade lookup: if the typed email matches an existing central user,
     * the form switches into "attach existing user" mode (no password, just pivot).
     * Returns null on create/edit when the email doesn't match anyone.
     */
    #[Computed]
    public function existingUser(): ?User
    {
        if ($this->isEdit || $this->email === null || $this->email === '') {
            return null;
        }

        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return User::with('tenants')->where('email', $this->email)->first();
    }

    /**
     * True when the matched existing user is already a member of the active tenant
     * — submit must be blocked, otherwise unique(user_id, tenant_id) would 500.
     */
    #[Computed]
    public function existingUserAlreadyInTenant(): bool
    {
        $existing = $this->existingUser;
        $activeTenant = tenant();

        if ($existing === null || $activeTenant === null) {
            return false;
        }

        return $existing->belongsToTenant($activeTenant->getTenantKey());
    }

    public function updatedHasLeave(): void
    {
        if (! $this->has_leave) {
            $this->departure_date = null;
        }
    }

    public function updatedCreateUser(): void
    {
        if (! $this->create_user) {
            $this->password = null;
            $this->password_confirmation = null;
        }
    }

    public function abandon(): void
    {
        $this->redirect(route('coworkers.index'), navigate: true);
    }

    public function submit(): void
    {
        $authUser = auth()->user();

        if ($authUser->isClient()) {
            return;
        }

        // Single-company users (sclient/aclient) can only act on their own company.
        // Tenant managers (admin REM, owner, tenant_admin) keep the picked company.
        if (! $authUser->isTenantManager()) {
            $this->selected_client_id = $authUser->contextualClientId();
        }

        if ($this->isEdit) {
            $this->submitUpdate();
        } else {
            $this->submitCreate();
        }
    }

    private function submitCreate(): void
    {
        $authUser = auth()->user();

        // Auto-upgrade: if the typed email matches an existing user, we attach them
        // instead of creating. They become the coworker's user — provided they're
        // not already a member of this tenant (would violate unique(user_id,tenant_id)).
        $existing = $this->existingUser;
        if ($existing !== null && $this->existingUserAlreadyInTenant) {
            $this->addError('email', 'Cet utilisateur a déjà accès à cet espace.');
            $this->toast('Cet utilisateur a déjà accès à cet espace.', 'warning');

            return;
        }

        $attachExisting = $existing !== null;
        $effectiveCreateUser = $attachExisting ? false : $this->create_user;

        $validationData = [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'client_id' => $this->selected_client_id,
            'can_access_formation' => $this->can_access_formation,
            'has_leave' => $this->has_leave,
            'departure_date' => $this->has_leave ? $this->departure_date : null,
            'create_user' => $effectiveCreateUser,
            'password' => $effectiveCreateUser ? $this->password : null,
            'password_confirmation' => $effectiveCreateUser ? $this->password_confirmation : null,
            'role' => $this->role,
            'existing_user_id' => $attachExisting ? $existing->id : null,
        ];

        $allowedRoles = Role::assignableValuesBy($authUser);

        $validator = CoworkerValidator::validateComplete($validationData, $allowedRoles);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
            $this->toast($validator->errors()->first(), 'danger');

            return;
        }

        if ($this->hasInvalidTrainingRows()) {
            $this->toast('Chaque formation sélectionnée doit avoir une date de début et une durée de validité.', 'danger');

            return;
        }

        try {
            $data = CreateCoworkerData::fromArray($validationData, $authUser->id);
            $action = new CreateCoworkerAction;
            $result = $action->execute($data);

            if (! $result->isSuccessful()) {
                $this->toast($result->getMessage(), 'danger');

                return;
            }

            if ($result->coworker) {
                $this->syncTrainings($result->coworker->id);
            }

            if (! $attachExisting && $effectiveCreateUser && $result->user && $this->password) {
                Mail::to($result->user->email)->send(new UserCreated($result->user, $this->password));
            }

            $successMessage = $attachExisting
                ? 'Collaborateur créé et utilisateur '.$existing->name.' rattaché à cet espace.'
                : 'Collaborateur créé avec succès.';
            $this->toast($successMessage, 'success', 'Création réussie');
            $this->redirect(route('coworkers.show', ['coworkerId' => $result->coworker->id]), navigate: true);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du collaborateur', [
                'error' => $e->getMessage(),
                'user_id' => $authUser->id,
            ]);
            $this->toast('Une erreur est survenue lors de la création.', 'danger');
        }
    }

    private function submitUpdate(): void
    {
        $authUser = auth()->user();

        $validationData = [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'can_access_formation' => $this->can_access_formation,
            'role' => $this->role,
        ];

        $allowedRoles = Role::assignableValuesBy($authUser);
        $validator = CoworkerValidator::validateUpdate($validationData, $this->coworkerId, $allowedRoles);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
            $this->toast($validator->errors()->first(), 'danger');

            return;
        }

        if ($this->has_leave) {
            $depValidator = \Illuminate\Support\Facades\Validator::make(
                ['departure_date' => $this->departure_date],
                ['departure_date' => 'required|date|before_or_equal:today'],
                [
                    'departure_date.required' => 'La date de départ est requise.',
                    'departure_date.date' => 'La date de départ doit être une date valide.',
                    'departure_date.before_or_equal' => 'La date de départ ne peut pas être dans le futur.',
                ]
            );

            if ($depValidator->fails()) {
                $this->addError('departure_date', $depValidator->errors()->first('departure_date'));
                $this->toast($depValidator->errors()->first(), 'danger');

                return;
            }
        }

        if ($this->hasInvalidTrainingRows()) {
            $this->toast('Chaque formation sélectionnée doit avoir une date de début et une durée de validité.', 'danger');

            return;
        }

        $coworker = Coworker::with('user')->find($this->coworkerId);
        if (! $coworker) {
            abort(404);
        }

        if (! $authUser->isTenantManager() && $coworker->client_id !== $authUser->contextualClientId()) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($coworker) {
                $coworker->update([
                    'firstname' => $this->firstname,
                    'lastname' => $this->lastname,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'has_leave' => $this->has_leave,
                    'departure_date' => $this->has_leave ? $this->departure_date : null,
                ]);

                if ($coworker->user) {
                    // Name/role restent sur la colonne users (le rôle global est encore
                    // utilisé en fallback, le rename est Q-ROLES différé).
                    $coworker->user->update([
                        'name' => $this->firstname.' '.$this->lastname,
                        'role' => $this->role,
                    ]);

                    // `can_access_formation` est désormais par tenant : on écrit sur la pivot.
                    $activeTenant = tenant();
                    if ($activeTenant !== null) {
                        $coworker->user->tenants()->updateExistingPivot($activeTenant->getTenantKey(), [
                            'can_access_formation' => $this->can_access_formation,
                        ]);
                    }
                }

                $this->syncTrainings($coworker->id);
            });

            $this->toast('Collaborateur mis à jour.', 'success', 'Mise à jour réussie');
            $this->redirect(route('coworkers.show', ['coworkerId' => $coworker->id]), navigate: true);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du collaborateur', [
                'error' => $e->getMessage(),
                'coworker_id' => $coworker->id,
                'user_id' => $authUser->id,
            ]);
            $this->toast('Une erreur est survenue lors de la mise à jour.', 'danger');
        }
    }

    private function hasInvalidTrainingRows(): bool
    {
        foreach ($this->selected_trainings as $row) {
            if (! ($row['selected'] ?? false)) {
                continue;
            }
            if (empty($row['start_date']) || empty($row['validity_years'])) {
                return true;
            }
        }

        return false;
    }

    private function syncTrainings(int $coworkerId): void
    {
        // `coworkerTrainings` (HasMany, tenant) — avoids the cross-DB JOIN that `trainings` would emit.
        $coworker = Coworker::with('coworkerTrainings')->find($coworkerId);
        if (! $coworker) {
            return;
        }

        $existing = $coworker->coworkerTrainings->keyBy('training_id');
        $kept = [];

        foreach ($this->selected_trainings as $trainingId => $row) {
            if (! ($row['selected'] ?? false)) {
                continue;
            }

            $startDate = Carbon::parse($row['start_date']);
            $expiresAt = $row['validity_years'] === 'lifetime'
                ? null
                : $startDate->copy()->addYears((int) $row['validity_years']);

            if ($existing->has($trainingId)) {
                $existing[$trainingId]->update([
                    'started_at' => $startDate,
                    'expires_at' => $expiresAt,
                ]);
            } else {
                CoworkerTraining::create([
                    'coworker_id' => $coworkerId,
                    'training_id' => (int) $trainingId,
                    'started_at' => $startDate,
                    'expires_at' => $expiresAt,
                ]);
            }

            $kept[] = (int) $trainingId;
        }

        $toRemove = $existing->keys()->diff($kept);
        if ($toRemove->isNotEmpty()) {
            CoworkerTraining::query()
                ->where('coworker_id', $coworkerId)
                ->whereIn('training_id', $toRemove->all())
                ->delete();
        }
    }
}; ?>

@php
    $authUser = auth()->user();
    $isTenantManager = $authUser->isTenantManager();

    $clientOptions = [];
    foreach ($this->clients as $c) {
        $clientOptions[] = [
            'value' => $c->id,
            'label' => $c->company_name,
            'hint' => $c->siret_number,
        ];
    }

    $roleOptions = array_map(
        fn (\App\Enums\Role $r) => ['value' => $r->value, 'label' => $r->label()],
        \App\Enums\Role::assignableBy($authUser),
    );

    $existingUser = $this->existingUser;
    $alreadyInTenant = $this->existingUserAlreadyInTenant;

    $validityOptions = [
        ['value' => '2', 'label' => '2 ans'],
        ['value' => '3', 'label' => '3 ans'],
        ['value' => '5', 'label' => '5 ans'],
        ['value' => 'lifetime', 'label' => 'À vie'],
    ];
@endphp

<div class="flex min-h-full flex-col">
    {{-- Header --}}
    <div class="px-4 pt-8 pb-4 sm:px-6 lg:px-8">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('coworkers.index') }}"
                    wire:navigate
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-foreground-muted transition hover:text-foreground"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Retour
                </a>
                <span class="text-foreground-subtle">/</span>
                <h1 class="text-base font-semibold text-foreground">
                    {{ $isEdit ? 'Modifier le collaborateur' : 'Nouveau collaborateur' }}
                </h1>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="submit" class="flex flex-1 flex-col">
        <div class="flex-1 px-4 pb-8 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl space-y-4">

                {{-- Société (admin REM, owner, tenant_admin) --}}
                @if ($isTenantManager && ! $isEdit)
                    <div x-data="{ open: true }" class="overflow-hidden rounded-lg bg-amber-50/50 ring-1 ring-amber-200/70 ring-inset">
                        <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-amber-100/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                            <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-md bg-amber-100 text-amber-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-foreground">Société</div>
                                <div class="text-xs text-foreground-muted">Sélection client</div>
                            </div>
                            <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-amber-700/80 transition-transform duration-200">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="open" x-collapse class="border-t border-amber-200/70 p-5">
                            <x-ui.select
                                label="Client"
                                :value="$selected_client_id"
                                wire:model.live="selected_client_id"
                                :options="$clientOptions"
                                placeholder="Sélectionnez un client…"
                                search-placeholder="Filtrer par société ou SIRET…"
                                empty-text="Aucun client trouvé."
                                searchable
                                required
                                :error="$errors->first('client_id')"
                            />
                        </div>
                    </div>
                @endif

                {{-- Identité --}}
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-foreground-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Identité</div>
                            <div class="text-xs text-foreground-muted">Coordonnées du collaborateur</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-ui.input
                                label="Prénom"
                                wire:model.blur="firstname"
                                required
                                :error="$errors->first('firstname')"
                            />
                            <x-ui.input
                                label="Nom"
                                wire:model.blur="lastname"
                                required
                                :error="$errors->first('lastname')"
                            />
                            <x-ui.input
                                label="Email"
                                type="email"
                                wire:model.live.debounce.500ms="email"
                                :error="$errors->first('email')"
                            />
                            <x-ui.input
                                label="Téléphone"
                                wire:model.blur="phone"
                                :error="$errors->first('phone')"
                            />
                        </div>
                    </div>
                </div>

                {{-- Compte utilisateur --}}
                <div x-data="{ open: {{ $isEdit && $has_user_account ? 'true' : ($create_user ? 'true' : 'false') }} }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-blue-50 text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Compte utilisateur</div>
                            <div class="text-xs text-foreground-muted">
                                @if ($isEdit)
                                    {{ $has_user_account ? 'Compte associé — rôle et accès modifiables' : 'Aucun compte associé' }}
                                @else
                                    Optionnel — créer un accès à l'application
                                @endif
                            </div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        @if ($isEdit)
                            @if ($has_user_account)
                                <div class="space-y-4">
                                    <x-ui.select
                                        label="Rôle"
                                        :value="$role"
                                        wire:model.live="role"
                                        :options="$roleOptions"
                                        required
                                    />

                                    <x-ui.checkbox
                                        wire:model.live="can_access_formation"
                                        :checked="$can_access_formation"
                                        label="Accès aux formations"
                                        description="L'utilisateur pourra accéder à l'onglet formations."
                                    />
                                </div>
                            @else
                                <x-ui.alert variant="info">
                                    Ce collaborateur n'a pas de compte utilisateur. Pour en créer un, utilisez l'action « Créer un compte » depuis la liste ou la fiche détail.
                                </x-ui.alert>
                            @endif
                        @elseif ($existingUser)
                            {{-- Auto-upgrade : un utilisateur central existe déjà avec cet email. --}}
                            <div class="space-y-4">
                                @if ($alreadyInTenant)
                                    <x-ui.alert variant="danger">
                                        L'utilisateur <strong>{{ $existingUser->name }}</strong> ({{ $existingUser->email }})
                                        a déjà accès à cet espace. Veuillez choisir un autre email ou utiliser sa fiche existante.
                                    </x-ui.alert>
                                @else
                                    <div class="rounded-md border border-blue-200 bg-blue-50/60 p-4 text-sm">
                                        <div class="font-medium text-blue-900">
                                            Utilisateur existant : {{ $existingUser->name }}
                                        </div>
                                        <div class="mt-0.5 text-blue-800">{{ $existingUser->email }}</div>
                                        @if ($authUser->isRemStaff() && $existingUser->tenants->isNotEmpty())
                                            {{-- Liste des autres espaces — réservée au staff REM, qui a la visibilité cross-tenant.
                                                 Pour un owner/tenant_admin, l'info appartient à un autre tenant et ne doit pas fuiter. --}}
                                            <div class="mt-2 text-xs text-blue-800">
                                                Accès actuels :
                                                {{ $existingUser->tenants->map(fn ($t) => $t->name ?? $t->id)->implode(', ') }}
                                            </div>
                                        @endif
                                        <div class="mt-3 text-xs text-blue-800">
                                            Il sera rattaché à cet espace avec le rôle choisi ci-dessous (pas de mot de passe à fournir).
                                        </div>
                                    </div>

                                    <x-ui.select
                                        label="Rôle dans cet espace"
                                        :value="$role"
                                        wire:model.live="role"
                                        :options="$roleOptions"
                                        required
                                    />
                                @endif
                            </div>
                        @else
                            <div class="space-y-4">
                                <x-ui.checkbox
                                    wire:model.live="create_user"
                                    :checked="$create_user"
                                    label="Créer un compte utilisateur pour ce collaborateur"
                                    description="Le collaborateur recevra un email avec ses identifiants."
                                />

                                @if ($create_user)
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <x-ui.input
                                            label="Mot de passe"
                                            type="password"
                                            wire:model.blur="password"
                                            required
                                            togglePassword
                                            :error="$errors->first('password')"
                                        />
                                        <x-ui.input
                                            label="Confirmer le mot de passe"
                                            type="password"
                                            wire:model.blur="password_confirmation"
                                            required
                                            togglePassword
                                            :error="$errors->first('password_confirmation')"
                                        />
                                    </div>

                                    <x-ui.select
                                        label="Rôle"
                                        :value="$role"
                                        wire:model.live="role"
                                        :options="$roleOptions"
                                        required
                                    />

                                    <x-ui.checkbox
                                        wire:model.live="can_access_formation"
                                        :checked="$can_access_formation"
                                        label="Accès aux formations"
                                    />
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Formations --}}
                <div x-data="{ open: false }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-violet-50 text-violet-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Formations</div>
                            <div class="text-xs text-foreground-muted">
                                @php
                                    $countSelected = collect($selected_trainings)->filter(fn ($r) => $r['selected'] ?? false)->count();
                                @endphp
                                {{ $countSelected }} formation(s) attribuée(s)
                            </div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        @if ($this->trainings->isEmpty())
                            <x-ui.alert variant="info">Aucune formation disponible.</x-ui.alert>
                        @else
                            <div class="space-y-3">
                                @foreach ($this->trainings as $training)
                                    @php
                                        $tid = $training->id;
                                        $row = $selected_trainings[$tid] ?? null;
                                        $isSelected = (bool) ($row['selected'] ?? false);
                                    @endphp
                                    <div wire:key="training-row-{{ $tid }}" class="rounded border border-border p-4">
                                        <x-ui.checkbox
                                            wire:model.live="selected_trainings.{{ $tid }}.selected"
                                            :checked="$isSelected"
                                            :label="$training->title"
                                        />

                                        @if ($isSelected)
                                            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                <x-ui.input
                                                    label="Date de début"
                                                    type="date"
                                                    wire:model.blur="selected_trainings.{{ $tid }}.start_date"
                                                    required
                                                />
                                                <x-ui.select
                                                    label="Durée de validité"
                                                    :value="$selected_trainings[$tid]['validity_years'] ?? null"
                                                    wire:model.live="selected_trainings.{{ $tid }}.validity_years"
                                                    :options="$validityOptions"
                                                    placeholder="Sélectionner…"
                                                    required
                                                />
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Statut (édition uniquement) --}}
                @if ($isEdit)
                    <div x-data="{ open: false }" class="overflow-hidden rounded-lg border border-border bg-white">
                        <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                            <span class="flex h-9 w-9 items-center justify-center rounded-md bg-red-50 text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-foreground">Statut</div>
                                <div class="text-xs text-foreground-muted">{{ $has_leave ? 'Marqué comme parti' : 'Actif' }}</div>
                            </div>
                            <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="open" x-collapse class="border-t border-border p-5">
                            <div class="space-y-4">
                                <x-ui.checkbox
                                    wire:model.live="has_leave"
                                    :checked="$has_leave"
                                    label="Le collaborateur a quitté l'entreprise"
                                />

                                @if ($has_leave)
                                    <x-ui.input
                                        label="Date de départ"
                                        type="date"
                                        wire:model.blur="departure_date"
                                        required
                                        :error="$errors->first('departure_date')"
                                    />
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        {{-- Sticky action bar --}}
        <div class="sticky bottom-0 z-10 border-t border-border bg-white shadow-[0_-4px_12px_-4px_rgba(15,23,42,0.08)]">
            <div class="mx-auto flex w-full max-w-3xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <x-ui.button
                    type="button"
                    variant="ghost"
                    wire:click="abandon"
                    wire:confirm="Abandonner les modifications ?"
                >
                    Annuler
                </x-ui.button>

                <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="submit" :disabled="$alreadyInTenant">
                    <span wire:loading.remove wire:target="submit">{{ $isEdit ? 'Enregistrer' : 'Créer le collaborateur' }}</span>
                    <span wire:loading wire:target="submit">Enregistrement…</span>
                </x-ui.button>
            </div>
        </div>
    </form>
</div>
