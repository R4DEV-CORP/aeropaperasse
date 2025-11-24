<form wire:submit.prevent="returnBadge" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <div class="flex justify-between">
            <flux:heading size="xl">
                Restituer le badge
            </flux:heading>
            <flux:badge color="red" class="mr-8">Admin</flux:badge>
        </div>
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
    <!-- Document -->
    <div class="border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Document de restitution</flux:heading>
        <flux:field class="mt-2">
            <flux:label>Document de restitution<span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="return_badge_document" type="file" icon="document-plus" name="return_badge_document" />
            <flux:error name="return_badge_document" />
        </flux:field>
    </div>
    <flux:button type="submit" variant="primary" icon:trailing="inbox-arrow-down">Restituer le badge</flux:button>
</form>
