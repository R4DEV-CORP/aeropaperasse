<?php

use App\Enums\Role;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\UserCreated;
use App\Models\Coworker;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $coworkerId = null;

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $can_access_formation = false;

    public string $role = 'client';

    /**
     * Acknowledgement that the typed email matches a user already member of other
     * tenants and that attaching them here extends their access cross-tenant. Only
     * required (and surfaced) when the actor is REM staff — see
     * {@see requiresCrossTenantConfirmation()}.
     */
    public bool $confirmCrossTenant = false;

    #[On('open-make-user')]
    public function open(int $id): void
    {
        if (! auth()->user()->isTenantManager()) {
            return;
        }

        $coworker = Coworker::find($id);
        if (! $coworker) {
            return;
        }

        if ($coworker->user_id) {
            $this->toast('Ce collaborateur a déjà un compte utilisateur.', 'warning');

            return;
        }

        $this->coworkerId = $id;
        $this->email = $coworker->email ?? '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->can_access_formation = false;
        $this->role = 'client';
        $this->confirmCrossTenant = false;
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'coworker-make-user');
    }

    #[Computed]
    public function coworker(): ?Coworker
    {
        if ($this->coworkerId === null) {
            return null;
        }

        return Coworker::with('client')->find($this->coworkerId);
    }

    /**
     * Auto-upgrade lookup: if the typed email matches an existing central user,
     * the form switches into "attach existing user" mode (no password, just pivot).
     */
    #[Computed]
    public function existingUser(): ?User
    {
        if ($this->email === '' || ! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return User::with('tenants')->where('email', $this->email)->first();
    }

    /**
     * True when the matched existing user already has access to the active tenant —
     * in that case attaching them again is a no-op + a UX trap, so we block submit.
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

    /**
     * REM staff sees the existing user's cross-tenant scope (the "Accès actuels"
     * line), so they're the only role whose attach action visibly broadens that
     * scope. Require an explicit acknowledgement before the submit goes through —
     * silent extension of access across tenants was how `owner@client1.test`
     * silently grew a REM pivot during browser testing.
     */
    #[Computed]
    public function requiresCrossTenantConfirmation(): bool
    {
        $existing = $this->existingUser;

        if ($existing === null || $this->existingUserAlreadyInTenant) {
            return false;
        }

        if (! auth()->user()->isRemStaff()) {
            return false;
        }

        return $existing->tenants->isNotEmpty();
    }

    public function submit(): void
    {
        $authUser = auth()->user();
        if (! $authUser->isTenantManager() || $this->coworkerId === null) {
            return;
        }

        $coworker = Coworker::find($this->coworkerId);
        if (! $coworker || $coworker->user_id) {
            $this->toast('Action non disponible.', 'danger');

            return;
        }

        $allowedRoles = Role::assignableValuesBy($authUser);

        $existing = $this->existingUser;

        if ($existing !== null && $this->existingUserAlreadyInTenant) {
            $this->addError('email', 'Cet utilisateur a déjà accès à cet espace.');
            $this->toast('Cet utilisateur a déjà accès à cet espace.', 'warning');

            return;
        }

        if ($this->requiresCrossTenantConfirmation && ! $this->confirmCrossTenant) {
            $this->addError('confirmCrossTenant', 'Confirmez l\'extension d\'accès cross-tenant pour continuer.');
            $this->toast('Confirmation requise pour étendre l\'accès cross-tenant.', 'warning');

            return;
        }

        if ($existing !== null) {
            $this->validate([
                'email' => 'required|email|max:255',
                'role' => 'required|in:'.implode(',', $allowedRoles),
            ], [
                'email.required' => 'L\'email est requis.',
                'email.email' => 'L\'email doit être une adresse email valide.',
                'role.required' => 'Le rôle est requis.',
                'role.in' => 'Le rôle sélectionné n\'est pas valide.',
            ]);
        } else {
            $this->validate([
                'email' => 'required|email|max:255|unique:central.users,email',
                'password' => ['required', 'confirmed', Password::defaults()],
                'password_confirmation' => 'required',
                'can_access_formation' => 'boolean',
                'role' => 'required|in:'.implode(',', $allowedRoles),
            ], [
                'email.required' => 'L\'email est requis.',
                'email.email' => 'L\'email doit être une adresse email valide.',
                'email.unique' => 'Cette adresse email est déjà utilisée par un autre compte.',
                'password.required' => 'Le mot de passe est requis.',
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
                'password_confirmation.required' => 'La confirmation du mot de passe est requise.',
                'role.required' => 'Le rôle est requis.',
                'role.in' => 'Le rôle sélectionné n\'est pas valide.',
            ]);
        }

        try {
            if ($existing !== null) {
                $this->attachExistingUserToCoworker($existing, $coworker);
                $message = $existing->name.' a été rattaché à cet espace en tant que '.Role::from($this->role)->label().'.';
                $title = 'Utilisateur rattaché';
            } else {
                $message = $this->createUserForCoworker($coworker);
                $title = 'Compte créé';
            }

            $this->toast($message, 'success', $title);
            $this->dispatch('close-modal', name: 'coworker-make-user');
            $this->dispatch('coworker-updated');

            $this->reset(['coworkerId', 'email', 'password', 'password_confirmation', 'can_access_formation', 'confirmCrossTenant']);
            $this->role = 'client';
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création/rattachement du compte utilisateur', [
                'error' => $e->getMessage(),
                'coworker_id' => $coworker->id,
                'user_id' => $authUser->id,
            ]);

            $this->toast('Une erreur est survenue.', 'danger');
        }
    }

    private function createUserForCoworker(Coworker $coworker): string
    {
        $newUser = User::create([
            'name' => $coworker->firstname.' '.$coworker->lastname,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'client_id' => $coworker->client_id,
            'coworker_id' => $coworker->id,
            'can_access_formation' => $this->can_access_formation,
            'is_new' => true,
        ]);

        $coworker->update(['user_id' => $newUser->id]);

        $this->attachUserToActiveTenant($newUser, $coworker->client_id);

        Mail::to($this->email)->send(new UserCreated($newUser, $this->password));

        return 'Compte utilisateur créé pour '.$coworker->firstname.' '.$coworker->lastname.'.';
    }

    private function attachExistingUserToCoworker(User $existing, Coworker $coworker): void
    {
        $coworker->update(['user_id' => $existing->id]);
        $this->attachUserToActiveTenant($existing, $coworker->client_id);
    }

    /**
     * Idempotent attach: skips REM roles (they bypass the pivot) and skips users
     * who already have a row on this tenant — keeps the unique(user_id,tenant_id)
     * constraint safe when {@see existingUserAlreadyInTenant} slipped through.
     */
    private function attachUserToActiveTenant(User $user, int $clientId): void
    {
        $activeTenant = tenant();
        if ($activeTenant === null) {
            return;
        }

        if (in_array($this->role, [Role::RemAdmin->value, Role::RemSuperAdmin->value], true)) {
            return;
        }

        $tenantKey = $activeTenant->getTenantKey();
        if ($user->belongsToTenant($tenantKey) && ! $user->isRemStaff()) {
            return;
        }

        $user->tenants()->attach($tenantKey, [
            'role' => $this->role,
            'client_id' => $clientId,
            'can_access_formation' => $this->can_access_formation,
        ]);
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'coworker-make-user');
        $this->reset(['coworkerId', 'email', 'password', 'password_confirmation', 'can_access_formation', 'confirmCrossTenant']);
        $this->role = 'client';
        $this->resetErrorBag();
    }
}; ?>

@php
    $authUser = auth()->user();
    $roleOptions = array_map(
        fn (\App\Enums\Role $r) => ['value' => $r->value, 'label' => $r->label()],
        \App\Enums\Role::assignableBy($authUser),
    );
    $existing = $this->existingUser;
    $alreadyInTenant = $this->existingUserAlreadyInTenant;
    $needsCrossTenantConfirmation = $this->requiresCrossTenantConfirmation;
    $submitDisabled = $alreadyInTenant || ($needsCrossTenantConfirmation && ! $confirmCrossTenant);
@endphp

<div>
    <x-ui.modal name="coworker-make-user" maxWidth="2xl">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">
                    {{ $existing ? 'Rattacher un utilisateur existant' : 'Créer un compte utilisateur' }}
                </h2>
                <p class="text-sm text-foreground-muted">
                    @if ($existing && $alreadyInTenant)
                        Cet utilisateur a déjà accès à cet espace.
                    @elseif ($existing)
                        Cet email correspond à un utilisateur existant. Il sera rattaché à cet espace avec le rôle choisi (pas de mot de passe à fournir).
                    @else
                        Le collaborateur recevra un email avec ses identifiants.
                    @endif
                </p>
            </div>

            @if ($this->coworker)
                @php $cw = $this->coworker; @endphp
                <div class="grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Collaborateur</div>
                        <div class="mt-0.5 font-medium text-foreground">{{ $cw->firstname }} {{ $cw->lastname }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</div>
                        <div class="mt-0.5 text-foreground">{{ $cw->client?->company_name ?? '—' }}</div>
                    </div>
                </div>
            @endif

            @if ($existing)
                <div class="rounded-md border border-blue-200 bg-blue-50/60 p-4 text-sm">
                    <div class="font-medium text-blue-900">Utilisateur trouvé : {{ $existing->name }}</div>
                    <div class="mt-1 text-blue-800">{{ $existing->email }}</div>
                    @if ($authUser->isRemStaff() && $existing->tenants->isNotEmpty())
                        {{-- Liste des autres espaces — réservée au staff REM, qui a la visibilité cross-tenant.
                             Pour un owner/tenant_admin, l'info appartient à un autre tenant et ne doit pas fuiter. --}}
                        <div class="mt-2 text-xs text-blue-800">
                            Accès actuels :
                            {{ $existing->tenants->map(fn ($t) => $t->name ?? $t->id)->implode(', ') }}
                        </div>
                    @endif
                </div>
            @endif

            @if ($needsCrossTenantConfirmation)
                <div class="rounded-md border border-amber-300 bg-amber-50/70 p-4 text-sm">
                    <x-ui.checkbox
                        wire:model.live="confirmCrossTenant"
                        :checked="$confirmCrossTenant"
                        label="Je confirme étendre l'accès de cet utilisateur à cet espace"
                        description="Cet utilisateur est déjà membre d'au moins un autre espace. Le rattacher ici lui donnera accès à cet espace en plus, sans le retirer des autres."
                        :error="$errors->first('confirmCrossTenant')"
                    />
                </div>
            @endif

            <div class="space-y-4">
                <x-ui.input
                    label="Email du compte"
                    type="email"
                    wire:model.live.debounce.500ms="email"
                    required
                    :error="$errors->first('email')"
                />

                @if (! $existing)
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
                @endif

                <x-ui.select
                    label="Rôle"
                    :value="$role"
                    wire:model.live="role"
                    :options="$roleOptions"
                    required
                    :error="$errors->first('role')"
                />

                @if (! $existing)
                    <x-ui.checkbox
                        wire:model.live="can_access_formation"
                        :checked="$can_access_formation"
                        label="Accès aux formations"
                        description="L'utilisateur pourra accéder à l'onglet formations."
                    />
                @endif
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary" :disabled="$submitDisabled">
                    <span wire:loading.remove wire:target="submit">
                        {{ $existing ? 'Rattacher' : 'Créer le compte' }}
                    </span>
                    <span wire:loading wire:target="submit">Enregistrement…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
