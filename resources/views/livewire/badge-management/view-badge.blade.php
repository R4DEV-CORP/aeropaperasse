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
        </flux:callout.text>
    </flux:callout>
    <div class="grid grid-cols-2 gap-2 border border-gray-800/10 p-4 rounded-lg">
        <div>
            <flux:heading size="lg">Informations sur le bénéficiaire</flux:heading>
            <p class="text-gray-800 font-medium mt-2">Nom</p>
            <flux:text>{{ $badge->badgeRequest->coworker->lastname }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Prénom</p>
            <flux:text>{{ $badge->badgeRequest->coworker->firstname }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Email</p>
            <flux:text>{{ $badge->badgeRequest->coworker->email }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Téléphone</p>
            <flux:text>{{ $badge->badgeRequest->coworker->phone }}</flux:text>
        </div>
        <div>
            <flux:heading size="lg">Information sur l'activité</flux:heading>
            <p class="text-gray-800 font-medium mt-2">Date de création</p>
            <flux:text>{{ $badge->badgeRequest->activityRequest->created_at->format('d/m/Y') }}</flux:text>
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
            <p class="text-gray-800 font-medium mt-2">Description</p>
            <flux:text>{{ $badge->badgeRequest->activityRequest->description }}</flux:text>
        </div>
    </div>
</div>
