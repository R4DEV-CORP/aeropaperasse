<div class="mt-8">
    @if (session()->has('message'))
        <flux:callout variant="success" icon="check-circle" heading="{{ session('message') }}" />
    @endif

    <div class="grid grid-cols-5 gap-4 mt-4">
        <x-badge-info-card title="Total" value="1" bg-color="violet-200" />
        <x-badge-info-card title="Actifs" value="1" bg-color="green-200" />
        <x-badge-info-card title="Expirés" value="1" bg-color="red-200" />
        <x-badge-info-card title="Restitués" value="1" bg-color="blue-200" />
        <x-badge-info-card title="Non Restitués" value="1" bg-color="yellow-200" />
    </div>

    <div class="flex items-center gap-3 mt-4">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="Rechercher un badge..." />
        <flux:modal.trigger name="add-badge">
            <flux:button variant="primary" icon="plus">Ajouter un badge</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="mt-4 py-4 bg-white rounded-lg border border-zinc-200">
        <flux:heading size="lg" class="px-4">Liste des badges</flux:heading>
        <div class="mt-4 border-t border-gray-800/10">
            <table class="min-w-full divide-y divide-slate-800/10">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DETENTEUR</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">CONTACT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">STATUT</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE DE CREATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">DATE D'EXPIRATION</th>
                        <th class="px-3 py-3 text-start text-sm font-medium text-gray-800">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class=" divide-slate-800/10 text-sm text-gray-700">
                    @if($badges->count() > 0)
                    @foreach($badges as $badge)
                    <tr wire:loading.remove wire:target="search" wire:key="badge-{{ $badge->id }}">
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $badge->badgeRequest->coworker->firstname }} {{ $badge->badgeRequest->coworker->lastname }}</p>
                            <flux:text>{{ $badge->badgeRequest->activityRequest->client->company_name }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            <p class="text-gray-800 font-medium">{{ $badge->badgeRequest->coworker->email }}</p>
                            <flux:text>{{ $badge->badgeRequest->coworker->phone }}</flux:text>
                        </td>
                        <td class="px-3 py-2">
                            @switch($badge->status)
                                @case('active')
                                    <flux:badge icon="check-circle" color="green" size="sm">Actif</flux:badge>
                                    @break
                                @case('expired')
                                    <flux:badge icon="x-circle" color="red" size="sm">Expiré</flux:badge>
                                    @break
                                @case('returned')
                                    <flux:badge icon="check-circle" color="green" size="sm">Restitué</flux:badge>
                                    @break
                                @case('not_returned')
                                    <flux:badge icon="x-circle" color="yellow" size="sm">Non Restitué</flux:badge>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-3 py-2">{{ $badge->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">{{ $badge->expiry_date->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
                            <flux:button variant="primary" icon="eye">Voir</flux:button>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td class="px-3 py-2" colspan="6">
                            <flux:text class="text-gray-500">Aucun badge trouvé</flux:text>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>   
        </div>
    </div>
</div>
