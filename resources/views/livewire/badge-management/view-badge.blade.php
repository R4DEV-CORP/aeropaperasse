<div class="space-y-4">
    <div class="border-b border-gray-800/10 pb-4">
        <flux:heading size="xl">Détails du badge</flux:heading>
    </div>
    <flux:callout icon="information-circle" variant="secondary">
        <flux:callout.heading>Statut</flux:callout.heading>
        <flux:callout.text>
            @switch($badge->status)
                @case('active')
                    <flux:badge icon="check-circle" color="green">Actif</flux:badge>
                    @break
                @case('expired')
                    <flux:badge icon="x-circle" color="red">Expiré</flux:badge>
                    @break
                @case('returned')
                    <flux:badge icon="check-circle" color="blue">Restitué</flux:badge>
                    @break
                @case('not_returned')
                    <flux:badge icon="x-circle" color="yellow">Non Restitué</flux:badge>
                    @break
            @endswitch
            <span>Expire le {{ $badge->expiry_date->format('d/m/Y')}}</span>
        </flux:callout.text>
    </flux:callout>

    <div class="grid grid-cols-4 gap-2 border border-gray-800/10 p-4 rounded-lg">
        <div class="col-span-4 flex justify-between">
            <flux:heading size="lg">Bénéficiaire</flux:heading>
            <flux:button href="/badge-requests" wire:navigate icon:trailing="arrow-top-right-on-square">Voir la demande de badge</flux:button>
        </div>
        <div>
            <p class="text-gray-800 font-medium mt-2">Nom</p>
            <flux:text>{{ $badge->badgeRequest->coworker->lastname }}</flux:text>
        </div>
        <div>
            <p class="text-gray-800 font-medium mt-2">Prénom</p>
            <flux:text>{{ $badge->badgeRequest->coworker->firstname}}</flux:text>
        </div>
        <div>
            <p class="text-gray-800 font-medium mt-2">Email</p>
            <flux:text>{{ $badge->badgeRequest->coworker->email}}</flux:text>
        </div>
        <div>
            <p class="text-gray-800 font-medium mt-2">Téléphone</p>
            <flux:text>{{ $badge->badgeRequest->coworker->phone }}</flux:text>
        </div>
    </div>
    <div class="grid grid-cols-4 gap-2 border border-gray-800/10 p-4 rounded-lg">
        <div class="col-span-4 flex justify-between">
            <flux:heading size="lg">Activité</flux:heading>
            <flux:button href="/activity-requests" wire:navigate icon:trailing="arrow-top-right-on-square">Voir l'activité</flux:button>
        </div>
        <div>
            <p class="text-gray-800 font-medium mt-2">Date de création</p>
            <flux:text>{{ $badge->badgeRequest->activityRequest->created_at->format('d/m/Y') }}</flux:text>
        </div>
        <div>
            <p class="text-gray-800 font-medium mt-2">Aéroport</p>
            @switch($badge->badgeRequest->activityRequest->airport)
                @case('CDG')
                    <flux:badge color="cyan" size="sm">CDG</flux:badge>
                    @break
                @case('ORY')
                    <flux:badge color="violet" size="sm">ORY</flux:badge>
                    @break
                @case('LBG')
                    <flux:badge color="lime" size="sm">LBG</flux:badge>
                    @break
            @endswitch
        </div>
        <div class="col-span-2">
            <p class="text-gray-800 font-medium mt-2">Description</p>
            <flux:text>{{ $badge->badgeRequest->activityRequest->description }}</flux:text>
        </div>
    </div>
</div>
