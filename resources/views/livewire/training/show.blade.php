<div class="mt-8">
    @if($successMessage)
        <flux:callout variant="success" icon="check-circle" heading="{{ $successMessage }}" />
    @endif

    @if($errorMessage)
        <flux:callout variant="error" icon="x-circle" heading="{{ $errorMessage }}" />
    @endif

    <div class="grid grid-cols-1 gap-4 mt-4">
        <x-badge-info-card title="Arrivent à expiration (6 mois)" value="{{ $soonExpiringTrainings->count() }}" bg-color="yellow-200" />
    </div>
    <div class="flex items-center gap-3 mt-4">
        <flux:modal.trigger name="add-coworker-to-formation-modal">
            <flux:button variant="primary" icon="plus">Attribuer une formation</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200 relative">
        <flux:heading size="lg" class="px-4">Collaborateurs</flux:heading>
        <div class="mt-4 border-t border-gray-800/10">
            <table class="min-w-full divide-y divide-slate-800/10">
                <thead class="bg-gray-50">
                    <tr>
                        @if(auth()->user()->isAdmin())
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">SOCIETE</th>
                        @endif
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">NOM & PRENOM</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">CONTACT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">NOMBRE DE FORMATIONS</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @foreach($coworkers as $coworker)
                    <tr>
                        @if(auth()->user()->isAdmin())
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $coworker->client->company_name }}</p>
                            <flux:text>{{ $coworker->client->trade_name }}</flux:text>
                        </td>
                        @endif
                        <td class="px-3 py-2">
                            <p>{{ $coworker->firstname }} {{ $coworker->lastname }}</p>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $coworker->email }}</p>
                            <flux:text>{{ $coworker->phone }}</flux:text>
                        </td>
                        <td class="px-3 py-2">{{ $coworker->getActiveTrainings() }}</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center">
                                <flux:modal.trigger :name="'view-coworker-trainings-'.$coworker->id">
                                    <flux:button icon="eye" icon:variant="outline" variant="subtle" square="true" tooltip="Voir" class="hover:cursor-pointer"/>
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>
                    <flux:modal :name="'view-coworker-trainings-'.$coworker->id" wire:key="modal-coworker-{{ $coworker->id }}" class="min-w-2xl !max-w-4xl">
                        <flux:heading size="lg">{{ $coworker->firstname }} {{ $coworker->lastname }}</flux:heading>
                        <flux:text class="mt-1">Formations assignées</flux:text>
                        <div class="mt-4 space-y-3">
                            @forelse($coworker->trainings as $training)
                                @php
                                    $expiresAt = $training->pivot->expires_at ? \Carbon\Carbon::parse($training->pivot->expires_at) : null;
                                    $startedAt = \Carbon\Carbon::parse($training->pivot->started_at);
                                    if ($expiresAt === null) {
                                        $status = 'active';
                                    } elseif ($expiresAt->isPast()) {
                                        $status = 'expired';
                                    } elseif ($expiresAt->lessThanOrEqualTo(now()->addMonths(6))) {
                                        $status = 'expiring';
                                    } else {
                                        $status = 'active';
                                    }
                                @endphp
                                <div class="flex items-center justify-between gap-4 py-2 border-b border-zinc-100 last:border-0">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-gray-800 text-sm">{{ $training->title }}</p>
                                            @if($training->pivot->airport)
                                                <flux:badge color="blue" size="sm">{{ $training->pivot->airport }}</flux:badge>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Début : {{ $startedAt->format('d/m/Y') }}
                                            @if($expiresAt) · Expiration : {{ $expiresAt->format('d/m/Y') }}
                                            @else · Durée : À vie
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($status === 'active')
                                            <flux:badge color="green" size="sm">Actif</flux:badge>
                                        @elseif($status === 'expiring')
                                            <flux:badge color="yellow" size="sm">Expire bientôt</flux:badge>
                                        @else
                                            <flux:badge color="red" size="sm">Expiré</flux:badge>
                                        @endif
                                        @if($training->pivot->certificate_path)
                                            <flux:button
                                                icon="arrow-down-tray"
                                                icon:variant="outline"
                                                variant="subtle"
                                                square="true"
                                                tooltip="Télécharger le certificat"
                                                class="!text-blue-500 hover:cursor-pointer"
                                                wire:click="downloadCertificate({{ $training->pivot->id }})"
                                            />
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <flux:text>Aucune formation assignée à ce collaborateur.</flux:text>
                            @endforelse
                        </div>
                        <div class="flex justify-end mt-6">
                            <flux:modal.close>
                                <flux:button variant="ghost">Fermer</flux:button>
                            </flux:modal.close>
                        </div>
                    </flux:modal>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Formations actives -->
    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200 relative">
        <flux:heading size="lg" class="px-4">Formations actives</flux:heading>
        <div class="mt-4 border-t border-gray-800/10">
            <table class="min-w-full divide-y divide-slate-800/10">
                <thead class="bg-green-50">
                    <tr>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">NOM & PRENOM</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">FORMATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">AEROPORT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DUREE DE FORMATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE DE DEBUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE D'EXPIRATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @if($activeTrainings != null)
                        @foreach($activeTrainings as $training)
                        <tr>
                            <td class="px-3 py-2">
                                <p>{{ $training->coworker_firstname }} {{ $training->coworker_lastname }}</p>
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ $training->training_title }}</p>
                            </td>
                            <td class="px-3 py-2">
                                @if($training->airport)
                                    <flux:badge color="blue" size="sm">{{ $training->airport }}</flux:badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ $training->expires_at ? \Carbon\Carbon::parse($training->started_at)->diffInYears(\Carbon\Carbon::parse($training->expires_at)).' ans' : 'À vie' }}</p>
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ \Carbon\Carbon::parse($training->started_at)->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ $training->expires_at ? \Carbon\Carbon::parse($training->expires_at)->format('d/m/Y') : 'À vie' }}</p>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <flux:modal.trigger :name="'upload-certificate-modal-'.$training->id" wire:key="trigger-{{ $training->id }}">
                                        <flux:button icon="arrow-up-tray" icon:variant="outline" variant="subtle" square="true" tooltip="Déposer le certificat" class="!text-green-500 hover:cursor-pointer"/>
                                    </flux:modal.trigger>
                                    @if($training->certificate_path)
                                        <flux:button icon="arrow-down-tray" icon:variant="outline" variant="subtle" square="true" tooltip="Télécharger le certificat" class="!text-blue-500 hover:cursor-pointer" wire:click="downloadCertificate({{ $training->id }})"/>
                                    @endif
                                    @if($training->training_requires_airport)
                                        <flux:button
                                            icon="pencil-square"
                                            icon:variant="outline"
                                            variant="subtle"
                                            square="true"
                                            tooltip="Modifier l'aéroport"
                                            class="!text-amber-500 hover:cursor-pointer"
                                            wire:click="openEditAirport({{ $training->id }}, '{{ $training->airport }}')"
                                            wire:key="airport-trigger-{{ $training->id }}"
                                        />
                                    @endif
                                    <flux:modal :name="'upload-certificate-modal-'.$training->id" wire:key="modal-{{ $training->id }}" class="min-w-4xl !max-w-6xl">
                                        <flux:heading size="lg">Déposer le certificat</flux:heading>

                                        <form wire:submit="uploadCertificate({{ $training->id }})">
                                            <flux:field>
                                                <flux:label>Certificat de formation</flux:label>
                                                <flux:input type="file" wire:model="certificate" accept=".pdf,.jpg,.jpeg,.png" />
                                                <flux:description>Formats acceptés : PDF, JPG, JPEG, PNG (max 10MB)</flux:description>
                                            </flux:field>

                                            <div class="flex justify-end gap-3 mt-6">
                                                <flux:button type="button" variant="ghost" wire:click="$dispatch('close-modal', 'upload-certificate-modal')">
                                                    Annuler
                                                </flux:button>
                                                <flux:button type="submit" variant="primary">
                                                    Déposer le certificat
                                                </flux:button>
                                            </div>
                                        </form>
                                    </flux:modal>
                                    @if($training->training_requires_airport)
                                        @include('livewire.training.partials.edit-airport-modal', ['training' => $training, 'keyPrefix' => 'active'])
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else

                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Formations proches de l'expiration -->
    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200 relative">
        <flux:heading size="lg" class="px-4">Formations proches de l'expiration (6 mois)</flux:heading>
        <div class="mt-4 border-t border-gray-800/10">
            <table class="min-w-full divide-y divide-slate-800/10">
                <thead class="bg-yellow-50">
                    <tr>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">NOM & PRENOM</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">FORMATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">AEROPORT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DUREE DE FORMATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE DE DEBUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE D'EXPIRATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @if($soonExpiringTrainings != null)
                        @foreach($soonExpiringTrainings as $training)
                        <tr>
                            <td class="px-3 py-2">
                                <p>{{ $training->coworker_firstname }} {{ $training->coworker_lastname }}</p>
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ $training->training_title }}</p>
                            </td>
                            <td class="px-3 py-2">
                                @if($training->airport)
                                    <flux:badge color="blue" size="sm">{{ $training->airport }}</flux:badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ $training->expires_at ? \Carbon\Carbon::parse($training->started_at)->diffInYears(\Carbon\Carbon::parse($training->expires_at)).' ans' : 'À vie' }}</p>
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ \Carbon\Carbon::parse($training->started_at)->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ $training->expires_at ? \Carbon\Carbon::parse($training->expires_at)->format('d/m/Y') : 'À vie' }}</p>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <flux:modal.trigger :name="'upload-certificate-modal-'.$training->id" wire:key="trigger-expiring-{{ $training->id }}">
                                        <flux:button icon="arrow-up-tray" icon:variant="outline" variant="subtle" square="true" tooltip="Déposer le certificat" class="!text-green-500 hover:cursor-pointer"/>
                                    </flux:modal.trigger>
                                    @if($training->certificate_path)
                                        <flux:button icon="arrow-down-tray" icon:variant="outline" variant="subtle" square="true" tooltip="Télécharger le certificat" class="!text-blue-500 hover:cursor-pointer" wire:click="downloadCertificate({{ $training->id }})"/>
                                    @endif
                                    @if($training->training_requires_airport)
                                        <flux:button
                                            icon="pencil-square"
                                            icon:variant="outline"
                                            variant="subtle"
                                            square="true"
                                            tooltip="Modifier l'aéroport"
                                            class="!text-amber-500 hover:cursor-pointer"
                                            wire:click="openEditAirport({{ $training->id }}, '{{ $training->airport }}')"
                                            wire:key="airport-trigger-expiring-{{ $training->id }}"
                                        />
                                        @include('livewire.training.partials.edit-airport-modal', ['training' => $training, 'keyPrefix' => 'expiring'])
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else

                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Formations proches de l'expiration -->
    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200 relative">
        <flux:heading size="lg" class="px-4">Formations expirées</flux:heading>
        <div class="mt-4 border-t border-gray-800/10">
            <table class="min-w-full divide-y divide-slate-800/10">
                <thead class="bg-red-50">
                    <tr>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">NOM & PRENOM</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">FORMATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">AEROPORT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DUREE DE FORMATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE DE DEBUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE D'EXPIRATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @if($expiredTrainings != null)
                        @foreach($expiredTrainings as $training)
                        <tr>
                            <td class="px-3 py-2">
                                <p>{{ $training->coworker_firstname }} {{ $training->coworker_lastname }}</p>
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ $training->training_title }}</p>
                            </td>
                            <td class="px-3 py-2">
                                @if($training->airport)
                                    <flux:badge color="blue" size="sm">{{ $training->airport }}</flux:badge>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ $training->expires_at ? \Carbon\Carbon::parse($training->started_at)->diffInYears(\Carbon\Carbon::parse($training->expires_at)).' ans' : 'À vie' }}</p>
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ \Carbon\Carbon::parse($training->started_at)->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-3 py-2">
                                <p>{{ $training->expires_at ? \Carbon\Carbon::parse($training->expires_at)->format('d/m/Y') : 'À vie' }}</p>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <flux:modal.trigger :name="'upload-certificate-modal-'.$training->id" wire:key="trigger-expired-{{ $training->id }}">
                                        <flux:button icon="arrow-up-tray" icon:variant="outline" variant="subtle" square="true" tooltip="Déposer le certificat" class="!text-green-500 hover:cursor-pointer"/>
                                    </flux:modal.trigger>
                                    @if($training->certificate_path)
                                        <flux:button icon="arrow-down-tray" icon:variant="outline" variant="subtle" square="true" tooltip="Télécharger le certificat" class="!text-blue-500 hover:cursor-pointer" wire:click="downloadCertificate({{ $training->id }})"/>
                                    @endif
                                    @if($training->training_requires_airport)
                                        <flux:button
                                            icon="pencil-square"
                                            icon:variant="outline"
                                            variant="subtle"
                                            square="true"
                                            tooltip="Modifier l'aéroport"
                                            class="!text-amber-500 hover:cursor-pointer"
                                            wire:click="openEditAirport({{ $training->id }}, '{{ $training->airport }}')"
                                            wire:key="airport-trigger-expired-{{ $training->id }}"
                                        />
                                        @include('livewire.training.partials.edit-airport-modal', ['training' => $training, 'keyPrefix' => 'expired'])
                                    @endif
                                    <flux:modal :name="'upload-certificate-modal-'.$training->id" wire:key="modal-expired-{{ $training->id }}" class="min-w-4xl !max-w-6xl">
                                        <flux:heading size="lg">Déposer le certificat</flux:heading>

                                        <form wire:submit="uploadCertificate({{ $training->id }})">
                                            <flux:field>
                                                <flux:label>Certificat de formation</flux:label>
                                                <flux:input type="file" wire:model="certificate" accept=".pdf,.jpg,.jpeg,.png" />
                                                <flux:description>Formats acceptés : PDF, JPG, JPEG, PNG (max 10MB)</flux:description>
                                            </flux:field>

                                            <div class="flex justify-end gap-3 mt-6">
                                                <flux:button type="button" variant="ghost" wire:click="$dispatch('close-modal', 'upload-certificate-modal')">
                                                    Annuler
                                                </flux:button>
                                                <flux:button type="submit" variant="primary">
                                                    Déposer le certificat
                                                </flux:button>
                                            </div>
                                        </form>
                                    </flux:modal>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else

                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal attribution formation -->
    <flux:modal :dismissible="false" name="add-coworker-to-formation-modal" class="min-w-4xl !max-w-6xl">
        <livewire:training.add-coworker-to-training-form />
    </flux:modal>
</div>
