<?php

use App\Actions\Client\CreateClientAction;
use App\Actions\Client\UpdateClientAction;
use App\DataTransferObjects\CreateClientData;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Client;
use App\Validators\ClientValidator;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
#[Layout('layouts::app', [
    'breadcrumb' => [
        ['label' => 'Sociétés', 'href' => '/companies'],
        ['label' => 'Formulaire'],
    ],
])]
#[Title('Société')]
class extends Component
{
    use InteractsWithToasts, WithFileUploads;

    public ?int $companyId = null;

    public bool $isEdit = false;

    public ?Client $client = null;

    // Société
    public string $company_name = '';

    public string $trade_name = '';

    public string $siret_number = '';

    public string $address = '';

    public string $zip_code = '';

    public string $city = '';

    public ?string $subcontractor_of = null;

    public bool $is_airline_company = false;

    public ?string $notification_email = null;

    // Documents
    public $kbis_document = null;

    public $safety_document = null;

    public $security_document = null;

    public bool $hasExistingKbis = false;

    public bool $hasExistingSafety = false;

    public bool $hasExistingSecurity = false;

    // Référents sûreté
    public ?string $safety_referent_1_prenom = null;

    public ?string $safety_referent_1_nom = null;

    public ?string $safety_referent_1_email = null;

    public ?string $safety_referent_1_phone = null;

    public ?string $safety_referent_2_prenom = null;

    public ?string $safety_referent_2_nom = null;

    public ?string $safety_referent_2_email = null;

    public ?string $safety_referent_2_phone = null;

    public ?string $safety_referent_3_prenom = null;

    public ?string $safety_referent_3_nom = null;

    public ?string $safety_referent_3_email = null;

    public ?string $safety_referent_3_phone = null;

    // Correspondant sécurité
    public ?string $security_correspondent_prenom = null;

    public ?string $security_correspondent_nom = null;

    public ?string $security_correspondent_email = null;

    public ?string $security_correspondent_phone = null;

    // Contact RH
    public ?string $hr_contact_prenom = null;

    public ?string $hr_contact_nom = null;

    public ?string $hr_contact_email = null;

    public ?string $hr_contact_phone = null;

    public function mount(?int $companyId = null): void
    {
        $authUser = auth()->user();

        if (! $authUser->isTenantManager()) {
            $this->redirect(route('companies.show', ['companyId' => $authUser->contextualClientId()]), navigate: true);

            return;
        }

        if ($companyId !== null) {
            $client = Client::with('contacts')->find($companyId);
            if (! $client) {
                abort(404);
            }

            $this->isEdit = true;
            $this->companyId = $client->id;
            $this->client = $client;

            $this->loadClientData();
            $this->loadContacts();
            $this->checkExistingDocuments();
        }
    }

    private function loadClientData(): void
    {
        $this->company_name = (string) $this->client->company_name;
        $this->trade_name = (string) $this->client->trade_name;
        $this->siret_number = (string) $this->client->siret_number;
        $this->address = (string) $this->client->address;
        $this->zip_code = (string) $this->client->zip_code;
        $this->city = (string) $this->client->city;
        $this->subcontractor_of = $this->client->subcontractor_of;
        $this->notification_email = $this->client->notification_email;
        $this->is_airline_company = (bool) $this->client->is_airline_company;
    }

    private function loadContacts(): void
    {
        $contacts = $this->client->contacts;
        $safety = $contacts->where('role', 'safety')->values();

        if ($safety->count() >= 1) {
            $r = $safety->get(0);
            $this->safety_referent_1_prenom = $r->firstname;
            $this->safety_referent_1_nom = $r->lastname;
            $this->safety_referent_1_email = $r->email;
            $this->safety_referent_1_phone = $r->phone;
        }
        if ($safety->count() >= 2) {
            $r = $safety->get(1);
            $this->safety_referent_2_prenom = $r->firstname;
            $this->safety_referent_2_nom = $r->lastname;
            $this->safety_referent_2_email = $r->email;
            $this->safety_referent_2_phone = $r->phone;
        }
        if ($safety->count() >= 3) {
            $r = $safety->get(2);
            $this->safety_referent_3_prenom = $r->firstname;
            $this->safety_referent_3_nom = $r->lastname;
            $this->safety_referent_3_email = $r->email;
            $this->safety_referent_3_phone = $r->phone;
        }

        $sec = $contacts->where('role', 'security')->first();
        if ($sec) {
            $this->security_correspondent_prenom = $sec->firstname;
            $this->security_correspondent_nom = $sec->lastname;
            $this->security_correspondent_email = $sec->email;
            $this->security_correspondent_phone = $sec->phone;
        }

        $hr = $contacts->where('role', 'hr')->first();
        if ($hr) {
            $this->hr_contact_prenom = $hr->firstname;
            $this->hr_contact_nom = $hr->lastname;
            $this->hr_contact_email = $hr->email;
            $this->hr_contact_phone = $hr->phone;
        }
    }

    private function checkExistingDocuments(): void
    {
        $this->hasExistingKbis = ! empty($this->client->kbis_document);
        $this->hasExistingSafety = ! empty($this->client->safety_document);
        $this->hasExistingSecurity = ! empty($this->client->security_document);
    }

    private function getFormData(): array
    {
        return [
            'company_name' => $this->company_name,
            'trade_name' => $this->trade_name,
            'siret_number' => $this->siret_number,
            'address' => $this->address,
            'zip_code' => $this->zip_code,
            'city' => $this->city,
            'subcontractor_of' => $this->subcontractor_of,
            'is_airline_company' => $this->is_airline_company,
            'kbis_document' => $this->kbis_document,
            'safety_document' => $this->safety_document,
            'security_document' => $this->security_document,
            'notification_email' => $this->notification_email,
            'safety_referent_1_prenom' => $this->safety_referent_1_prenom,
            'safety_referent_1_nom' => $this->safety_referent_1_nom,
            'safety_referent_1_email' => $this->safety_referent_1_email,
            'safety_referent_1_phone' => $this->safety_referent_1_phone,
            'safety_referent_2_prenom' => $this->safety_referent_2_prenom,
            'safety_referent_2_nom' => $this->safety_referent_2_nom,
            'safety_referent_2_email' => $this->safety_referent_2_email,
            'safety_referent_2_phone' => $this->safety_referent_2_phone,
            'safety_referent_3_prenom' => $this->safety_referent_3_prenom,
            'safety_referent_3_nom' => $this->safety_referent_3_nom,
            'safety_referent_3_email' => $this->safety_referent_3_email,
            'safety_referent_3_phone' => $this->safety_referent_3_phone,
            'security_correspondent_prenom' => $this->security_correspondent_prenom,
            'security_correspondent_nom' => $this->security_correspondent_nom,
            'security_correspondent_email' => $this->security_correspondent_email,
            'security_correspondent_phone' => $this->security_correspondent_phone,
            'hr_contact_prenom' => $this->hr_contact_prenom,
            'hr_contact_nom' => $this->hr_contact_nom,
            'hr_contact_email' => $this->hr_contact_email,
            'hr_contact_phone' => $this->hr_contact_phone,
        ];
    }

    public function submit(): void
    {
        if (! auth()->user()->isTenantManager()) {
            return;
        }

        if ($this->isEdit) {
            $this->submitUpdate();
        } else {
            $this->submitCreate();
        }
    }

    private function submitCreate(): void
    {
        $data = $this->getFormData();
        $data['slug'] = (string) \Illuminate\Support\Str::uuid();

        $validator = ClientValidator::validate($data);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
            $this->toast($validator->errors()->first(), 'danger');

            return;
        }

        try {
            $dto = CreateClientData::fromArray($data);
            $action = app(CreateClientAction::class);
            $result = $action->execute($dto);

            if (! $result->isSuccessful()) {
                $this->toast($result->getMessage(), 'danger');

                return;
            }

            $this->toast('Société créée avec succès.', 'success', 'Création réussie');
            $this->redirect(route('companies.show', ['companyId' => $result->client->id]), navigate: true);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la société', [
                'error' => $e->getMessage(),
                'user_id' => auth()->user()->id,
            ]);
            $this->toast('Une erreur est survenue lors de la création.', 'danger');
        }
    }

    private function submitUpdate(): void
    {
        $data = $this->getFormData();

        $validator = ClientValidator::validateUpdate($data);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }
            $this->toast($validator->errors()->first(), 'danger');

            return;
        }

        try {
            $action = app(UpdateClientAction::class);
            $result = $action->execute($this->client, $data);

            if (! $result->isSuccessful()) {
                $this->toast($result->getMessage(), 'danger');

                return;
            }

            $this->toast('Société mise à jour.', 'success', 'Mise à jour réussie');
            $this->redirect(route('companies.show', ['companyId' => $this->client->id]), navigate: true);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la société', [
                'error' => $e->getMessage(),
                'company_id' => $this->client?->id,
                'user_id' => auth()->user()->id,
            ]);
            $this->toast('Une erreur est survenue lors de la mise à jour.', 'danger');
        }
    }

    public function abandon(): void
    {
        if ($this->isEdit && $this->companyId) {
            $this->redirect(route('companies.show', ['companyId' => $this->companyId]), navigate: true);
        } else {
            $this->redirect(route('companies.index'), navigate: true);
        }
    }
}; ?>

<div class="flex min-h-full flex-col">
    {{-- Header --}}
    <div class="px-4 pt-8 pb-4 sm:px-6 lg:px-8">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a
                    href="{{ $isEdit && $companyId ? route('companies.show', ['companyId' => $companyId]) : route('companies.index') }}"
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
                    {{ $isEdit ? 'Modifier '.$company_name : 'Nouvelle société' }}
                </h1>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="submit" class="flex flex-1 flex-col">
        <div class="flex-1 px-4 pb-8 sm:px-6 lg:px-8">
            <div class="mx-auto w-full max-w-3xl space-y-4">

                {{-- Identité société --}}
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-foreground-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Identité société</div>
                            <div class="text-xs text-foreground-muted">Raison sociale, SIRET, statut</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-ui.input
                                label="Raison sociale"
                                wire:model.blur="company_name"
                                required
                                :error="$errors->first('company_name')"
                            />
                            <x-ui.input
                                label="Nom commercial"
                                wire:model.blur="trade_name"
                                required
                                :error="$errors->first('trade_name')"
                            />
                            <x-ui.input
                                label="SIRET"
                                wire:model.blur="siret_number"
                                required
                                :error="$errors->first('siret_number')"
                            />
                            <x-ui.input
                                label="Sous-traitant de"
                                wire:model.blur="subcontractor_of"
                                :error="$errors->first('subcontractor_of')"
                            />
                            <div class="sm:col-span-2">
                                <x-ui.checkbox
                                    wire:model.live="is_airline_company"
                                    :checked="$is_airline_company"
                                    label="Compagnie aérienne"
                                    description="Cocher si cette société est une compagnie aérienne."
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Adresse --}}
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-50 text-emerald-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Adresse</div>
                            <div class="text-xs text-foreground-muted">Adresse postale du siège</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-ui.input
                                    label="Adresse"
                                    wire:model.blur="address"
                                    required
                                    :error="$errors->first('address')"
                                />
                            </div>
                            <x-ui.input
                                label="Code postal"
                                wire:model.blur="zip_code"
                                required
                                :error="$errors->first('zip_code')"
                            />
                            <x-ui.input
                                label="Ville"
                                wire:model.blur="city"
                                required
                                :error="$errors->first('city')"
                            />
                        </div>
                    </div>
                </div>

                {{-- Notifications --}}
                <div x-data="{ open: false }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-blue-50 text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Notifications</div>
                            <div class="text-xs text-foreground-muted">Email de notification (optionnel)</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <x-ui.input
                            label="Email de notification"
                            type="email"
                            wire:model.blur="notification_email"
                            hint="Si renseigné, recevra les notifications liées à cette société."
                            :error="$errors->first('notification_email')"
                        />
                    </div>
                </div>

                {{-- Référent sûreté 1 --}}
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-50 text-amber-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Référent sûreté <span class="font-mono">#1</span></div>
                            <div class="text-xs text-foreground-muted">Obligatoire</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-ui.input label="Prénom" wire:model.blur="safety_referent_1_prenom" required :error="$errors->first('safety_referent_1_prenom')" />
                            <x-ui.input label="Nom" wire:model.blur="safety_referent_1_nom" required :error="$errors->first('safety_referent_1_nom')" />
                            <x-ui.input label="Email" type="email" wire:model.blur="safety_referent_1_email" required :error="$errors->first('safety_referent_1_email')" />
                            <x-ui.input label="Téléphone" wire:model.blur="safety_referent_1_phone" required :error="$errors->first('safety_referent_1_phone')" />
                        </div>
                    </div>
                </div>

                {{-- Référent sûreté 2 --}}
                <div x-data="{ open: false }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-50/60 text-amber-700/80">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Référent sûreté <span class="font-mono">#2</span></div>
                            <div class="text-xs text-foreground-muted">Optionnel</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-ui.input label="Prénom" wire:model.blur="safety_referent_2_prenom" :error="$errors->first('safety_referent_2_prenom')" />
                            <x-ui.input label="Nom" wire:model.blur="safety_referent_2_nom" :error="$errors->first('safety_referent_2_nom')" />
                            <x-ui.input label="Email" type="email" wire:model.blur="safety_referent_2_email" :error="$errors->first('safety_referent_2_email')" />
                            <x-ui.input label="Téléphone" wire:model.blur="safety_referent_2_phone" :error="$errors->first('safety_referent_2_phone')" />
                        </div>
                    </div>
                </div>

                {{-- Référent sûreté 3 --}}
                <div x-data="{ open: false }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-50/60 text-amber-700/80">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Référent sûreté <span class="font-mono">#3</span></div>
                            <div class="text-xs text-foreground-muted">Optionnel</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-ui.input label="Prénom" wire:model.blur="safety_referent_3_prenom" :error="$errors->first('safety_referent_3_prenom')" />
                            <x-ui.input label="Nom" wire:model.blur="safety_referent_3_nom" :error="$errors->first('safety_referent_3_nom')" />
                            <x-ui.input label="Email" type="email" wire:model.blur="safety_referent_3_email" :error="$errors->first('safety_referent_3_email')" />
                            <x-ui.input label="Téléphone" wire:model.blur="safety_referent_3_phone" :error="$errors->first('safety_referent_3_phone')" />
                        </div>
                    </div>
                </div>

                {{-- Correspondant sécurité --}}
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-red-50 text-red-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Correspondant sécurité</div>
                            <div class="text-xs text-foreground-muted">Obligatoire</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-ui.input label="Prénom" wire:model.blur="security_correspondent_prenom" required :error="$errors->first('security_correspondent_prenom')" />
                            <x-ui.input label="Nom" wire:model.blur="security_correspondent_nom" required :error="$errors->first('security_correspondent_nom')" />
                            <x-ui.input label="Email" type="email" wire:model.blur="security_correspondent_email" required :error="$errors->first('security_correspondent_email')" />
                            <x-ui.input label="Téléphone" wire:model.blur="security_correspondent_phone" required :error="$errors->first('security_correspondent_phone')" />
                        </div>
                    </div>
                </div>

                {{-- Contact RH --}}
                <div x-data="{ open: true }" class="overflow-hidden rounded-lg border border-border bg-white">
                    <button type="button" @click="open = !open" :aria-expanded="open" class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-slate-50 focus:outline-none">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-violet-50 text-violet-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-foreground">Contact RH</div>
                            <div class="text-xs text-foreground-muted">Obligatoire</div>
                        </div>
                        <svg :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="ml-auto h-4 w-4 text-foreground-muted transition-transform duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="border-t border-border p-5">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <x-ui.input label="Prénom" wire:model.blur="hr_contact_prenom" required :error="$errors->first('hr_contact_prenom')" />
                            <x-ui.input label="Nom" wire:model.blur="hr_contact_nom" required :error="$errors->first('hr_contact_nom')" />
                            <x-ui.input label="Email" type="email" wire:model.blur="hr_contact_email" required :error="$errors->first('hr_contact_email')" />
                            <x-ui.input label="Téléphone" wire:model.blur="hr_contact_phone" required :error="$errors->first('hr_contact_phone')" />
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
                                label="KBIS"
                                wire:model.live="kbis_document"
                                accept="application/pdf"
                                :newFiles="$kbis_document?->getClientOriginalName()"
                                :existingFiles="$hasExistingKbis ? basename($client?->kbis_document ?? '') : null"
                                :required="! $hasExistingKbis"
                                :error="$errors->first('kbis_document')"
                                hint="PDF uniquement, max 8 Mo"
                            />

                            <x-ui.file-upload
                                label="Document référents sûreté"
                                wire:model.live="safety_document"
                                accept="application/pdf"
                                :newFiles="$safety_document?->getClientOriginalName()"
                                :existingFiles="$hasExistingSafety ? basename($client?->safety_document ?? '') : null"
                                :required="! $hasExistingSafety"
                                :error="$errors->first('safety_document')"
                                hint="PDF uniquement, max 8 Mo"
                            />

                            <x-ui.file-upload
                                label="Document correspondant sécurité"
                                wire:model.live="security_document"
                                accept="application/pdf"
                                :newFiles="$security_document?->getClientOriginalName()"
                                :existingFiles="$hasExistingSecurity ? basename($client?->security_document ?? '') : null"
                                :required="! $hasExistingSecurity"
                                :error="$errors->first('security_document')"
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
                    wire:confirm="Abandonner les modifications ?"
                >
                    Annuler
                </x-ui.button>

                <x-ui.button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">{{ $isEdit ? 'Enregistrer' : 'Créer la société' }}</span>
                    <span wire:loading wire:target="submit">Enregistrement…</span>
                </x-ui.button>
            </div>
        </div>
    </form>
</div>
