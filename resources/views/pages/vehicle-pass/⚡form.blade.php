<?php

use App\Actions\VehiclePass\SaveVehiclePassAction;
use App\DataTransferObjects\CreateVehiclePassData;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\VehiclePassCreated;
use App\Models\ActivityRequest;
use App\Models\Client;
use App\Models\User;
use App\Validators\VehiclePassValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Laissez-passer', 'href' => '/vehicle-pass'],
        ['label' => 'Nouvelle demande'],
    ],
])]
#[Title('Nouvelle demande de laissez-passer')]
class extends Component
{
    use InteractsWithToasts, WithFileUploads;

    public string $mode = 'linked';

    public ?int $selected_client_id = null;

    public ?int $selected_activity_request_id = null;

    public ?string $plate_number = null;

    public ?string $car_brand = null;

    public ?string $airport = null;

    public $certificate_of_registration = null;

    public $company_stamp = null;

    public function mount(?string $mode = null): void
    {
        $authUser = auth()->user();

        if ($authUser->isClient()) {
            $this->redirect(route('companies.show', ['companyId' => $authUser->client_id]), navigate: true);

            return;
        }

        $this->mode = in_array($mode, ['linked', 'standalone'], true) ? $mode : 'linked';

        if (! $authUser->isAdmin()) {
            $this->selected_client_id = $authUser->client_id;
        }
    }

    #[Computed]
    public function clients()
    {
        return Client::orderBy('company_name')->get();
    }

    #[Computed]
    public function activityRequests()
    {
        if (! $this->selected_client_id) {
            return collect();
        }

        return ActivityRequest::where('client_id', $this->selected_client_id)
            ->whereIn('status', ['approved', 'pending'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function selectedActivityRequest(): ?ActivityRequest
    {
        if (! $this->selected_activity_request_id) {
            return null;
        }

        return ActivityRequest::find($this->selected_activity_request_id);
    }

    public function updatedSelectedClientId(): void
    {
        $this->selected_activity_request_id = null;
        unset($this->activityRequests, $this->selectedActivityRequest);
    }

    public function updatedSelectedActivityRequestId(): void
    {
        unset($this->selectedActivityRequest);
    }

    public function submit(): void
    {
        $authUser = auth()->user();

        if ($authUser->isClient()) {
            return;
        }

        if (! $authUser->isAdmin()) {
            $this->selected_client_id = $authUser->client_id;
        }

        if (! $this->selected_client_id) {
            $this->toast('Veuillez sélectionner une société.', 'danger');

            return;
        }

        if ($this->mode === 'linked') {
            if (! $this->selected_activity_request_id) {
                $this->toast("Veuillez sélectionner une demande d'activité.", 'danger');

                return;
            }

            $activityRequest = ActivityRequest::find($this->selected_activity_request_id);
            if (! $activityRequest) {
                $this->toast("Demande d'activité introuvable.", 'danger');

                return;
            }

            if ($activityRequest->client_id !== (int) $this->selected_client_id) {
                $this->toast("La demande d'activité sélectionnée n'appartient pas à cette société.", 'danger');

                return;
            }

            if (! $activityRequest->canCreateVehiclePass()) {
                $remaining = $activityRequest->getRemainingVehiclePassQuota();
                $this->toast("Quota de laissez-passer atteint pour cette demande. Reste : {$remaining} place(s).", 'danger');

                return;
            }
        } else {
            $this->selected_activity_request_id = null;
        }

        $data = [
            'plate_number' => $this->plate_number,
            'car_brand' => $this->car_brand,
            'airport' => $this->airport,
            'activity_request_id' => $this->mode === 'linked' ? $this->selected_activity_request_id : null,
            'certificate_of_registration' => $this->certificate_of_registration,
            'company_stamp' => $this->company_stamp,
        ];

        $validator = VehiclePassValidator::validate($data);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
            $this->toast($validator->errors()->first(), 'danger');

            return;
        }

        $client = Client::find($this->selected_client_id);

        try {
            $dto = CreateVehiclePassData::fromArray($data, $client->id, $authUser->id);

            $action = app(SaveVehiclePassAction::class);
            $result = $action->execute($dto, $client);

            if (! $result->isSuccessful()) {
                $this->toast($result->getMessage(), 'danger');

                return;
            }

            $this->sendCreationEmails($result->vehiclePass);

            $this->toast('Demande de laissez-passer créée.', 'success', 'Création réussie');
            $this->redirect(route('vehicle-pass.show', ['vehiclePassId' => $result->vehiclePass->id]), navigate: true);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du laissez-passer', [
                'error' => $e->getMessage(),
                'user_id' => $authUser->id,
            ]);
            $this->toast('Une erreur est survenue lors de la création.', 'danger');
        }
    }

    private function sendCreationEmails(\App\Models\VehiclePass $vehiclePass): void
    {
        try {
            $recipient = $vehiclePass->createdBy?->email;
            if ($recipient) {
                Mail::to($recipient)->send(new VehiclePassCreated($vehiclePass, false));
            }

            $admins = User::whereIn('role', ['rem_admin', 'rem_super_admin'])->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new VehiclePassCreated($vehiclePass, true));
            }
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi des emails de création de laissez-passer", [
                'vehicle_pass_id' => $vehiclePass->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function abandon(): void
    {
        $this->redirect(route('vehicle-pass.index'), navigate: true);
    }
}; ?>

@php
    $authUser = auth()->user();
    $isAdmin = $authUser->isAdmin();

    $clientOptions = [];
    foreach ($this->clients as $c) {
        $clientOptions[] = [
            'value' => $c->id,
            'label' => $c->company_name,
            'hint' => $c->siret_number,
        ];
    }

    $arOptions = [];
    foreach ($this->activityRequests as $ar) {
        $remaining = $ar->getRemainingVehiclePassQuota();
        $arOptions[] = [
            'value' => $ar->id,
            'label' => "Demande #{$ar->id} — {$ar->airport}",
            'hint' => "{$remaining} place(s) restante(s)",
        ];
    }
@endphp

<div class="flex min-h-full flex-col">
    {{-- Header --}}
    <div class="px-4 pt-8 pb-4 sm:px-6 lg:px-8">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('vehicle-pass.index') }}"
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
                    {{ $mode === 'linked' ? "Lier à une demande d'activité" : 'Créer un laissez-passer indépendant' }}
                </h1>
            </div>

            {{-- Switch de mode --}}
            <div class="inline-flex rounded-md border border-border bg-white p-0.5 text-xs font-medium">
                <a
                    href="{{ route('vehicle-pass.form', ['mode' => 'linked']) }}"
                    wire:navigate
                    class="rounded px-3 py-1.5 transition {{ $mode === 'linked' ? 'bg-accent text-accent-foreground' : 'text-foreground-muted hover:text-foreground' }}"
                >
                    Lier à une demande
                </a>
                <a
                    href="{{ route('vehicle-pass.form', ['mode' => 'standalone']) }}"
                    wire:navigate
                    class="rounded px-3 py-1.5 transition {{ $mode === 'standalone' ? 'bg-accent text-accent-foreground' : 'text-foreground-muted hover:text-foreground' }}"
                >
                    Indépendant
                </a>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="submit" class="flex flex-1 flex-col">
        <div class="flex-1 px-4 pb-8 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl space-y-4">

                {{-- Société (admin) --}}
                @if ($isAdmin)
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
                                label="Société"
                                :value="$selected_client_id"
                                wire:model.live="selected_client_id"
                                :options="$clientOptions"
                                placeholder="Sélectionnez une société…"
                                search-placeholder="Filtrer par société ou SIRET…"
                                empty-text="Aucune société trouvée."
                                searchable
                                required
                            />
                        </div>
                    </div>
                @endif

                {{-- Demande d'activité (linked only) --}}
                @if ($mode === 'linked')
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-blue-50 text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Demande d'activité</div>
                            <div class="text-xs text-foreground-muted">Le laissez-passer sera rattaché à une demande approuvée ou en attente</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        @if (! $selected_client_id)
                            <x-ui.alert variant="info">
                                Sélectionnez d'abord une société pour voir les demandes d'activité disponibles.
                            </x-ui.alert>
                        @elseif (count($arOptions) === 0)
                            <x-ui.alert variant="warning">
                                Cette société n'a aucune demande d'activité approuvée ou en attente. Créez d'abord une demande d'activité.
                            </x-ui.alert>
                        @else
                            <x-ui.select
                                label="Demande d'activité"
                                :value="$selected_activity_request_id"
                                wire:model.live="selected_activity_request_id"
                                :options="$arOptions"
                                placeholder="Sélectionnez une demande…"
                                empty-text="Aucune demande."
                                required
                            />

                            @if ($this->selectedActivityRequest)
                                @php
                                    $ar = $this->selectedActivityRequest;
                                    $remaining = $ar->getRemainingVehiclePassQuota();
                                    $active = $ar->getActiveVehiclePassesCount();
                                @endphp
                                <div class="mt-3 grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50/50 p-3 text-sm sm:grid-cols-3">
                                    <div>
                                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Aéroport</div>
                                        <div class="mt-0.5 font-medium text-foreground">{{ $ar->airport }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Quota véhicules</div>
                                        <div class="mt-0.5 font-medium text-foreground">{{ $active }} / {{ $ar->vehicule_count }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Restant</div>
                                        <div class="mt-0.5 font-semibold {{ $remaining > 0 ? 'text-emerald-700' : 'text-red-700' }}">{{ $remaining }} place(s)</div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                @endif

                {{-- Véhicule --}}
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-foreground-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Véhicule</div>
                            <div class="text-xs text-foreground-muted">Immatriculation, marque, aéroport</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-ui.input
                                label="Immatriculation"
                                wire:model.blur="plate_number"
                                placeholder="AB-123-CD"
                                required
                                :error="$errors->first('plate_number')"
                            />
                            <x-ui.input
                                label="Marque du véhicule"
                                wire:model.blur="car_brand"
                                placeholder="Renault Trafic"
                                required
                                :error="$errors->first('car_brand')"
                            />
                            <div class="sm:col-span-2">
                                <x-ui.select
                                    label="Aéroport"
                                    :value="$airport"
                                    wire:model.live="airport"
                                    :options="[
                                        ['value' => 'CDG', 'label' => 'CDG — Paris-Charles-de-Gaulle'],
                                        ['value' => 'ORY', 'label' => 'ORY — Paris-Orly'],
                                        ['value' => 'LBG', 'label' => 'LBG — Paris-Le Bourget'],
                                    ]"
                                    placeholder="Sélectionnez un aéroport…"
                                    required
                                    :error="$errors->first('airport')"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Documents --}}
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-foreground-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Documents</div>
                            <div class="text-xs text-foreground-muted">PDF — max 8 Mo par fichier</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <div class="space-y-4">
                            <x-ui.file-upload
                                label="Carte grise"
                                wire:model.live="certificate_of_registration"
                                accept="application/pdf"
                                :newFiles="$certificate_of_registration?->getClientOriginalName()"
                                required
                                :error="$errors->first('certificate_of_registration')"
                                hint="PDF uniquement, max 8 Mo"
                            />

                            <x-ui.file-upload
                                label="Tampon de l'entreprise"
                                wire:model.live="company_stamp"
                                accept="application/pdf"
                                :newFiles="$company_stamp?->getClientOriginalName()"
                                required
                                :error="$errors->first('company_stamp')"
                                hint="PDF uniquement, max 8 Mo"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Sticky action bar --}}
        <div class="sticky bottom-0 z-10 border-t border-border bg-white shadow-[0_-4px_12px_-4px_rgba(15,23,42,0.08)]">
            <div class="mx-auto flex w-full max-w-3xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <x-ui.button
                    type="button"
                    variant="ghost"
                    wire:click="abandon"
                    wire:confirm="Abandonner la création ?"
                >
                    Annuler
                </x-ui.button>

                <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Créer la demande</span>
                    <span wire:loading wire:target="submit">Création…</span>
                </x-ui.button>
            </div>
        </div>
    </form>
</div>
