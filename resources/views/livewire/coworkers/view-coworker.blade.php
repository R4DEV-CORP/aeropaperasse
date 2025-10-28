<div class="space-y-4">
    <flux:heading size="lg">Informations sur {{ $coworker->firstname }} {{ $coworker->lastname }}</flux:heading>
    <div class="grid grid-cols-2 gap-4 border border-gray-800/10 p-4 rounded-lg">
        <div>
            <flux:heading size="lg">Informations sur le collaborateur</flux:heading>
            <p class="text-gray-800 font-medium mt-2">Nom</p>
            <flux:text>{{ $coworker->lastname }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Prénom</p>
            <flux:text>{{ $coworker->firstname }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Email</p>
            <flux:text>{{ $coworker->email }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Téléphone</p>
            <flux:text>{{ $coworker->phone }}</flux:text>
            <p class="text-gray-800 font-medium mt-2">Statut</p>
            @if($coworker->has_leave)
                <flux:badge icon="x-circle" color="red" size="sm">A quitté le {{ $coworker->departure_date->format('d/m/Y') }}</flux:badge>
            @else
                <flux:badge icon="check-circle" color="green" size="sm">Actif</flux:badge>
            @endif
        </div>
        <div>
            <flux:heading size="lg">Compte utilisateur</flux:heading>
            @if($coworker->user_id)
                <p class="text-gray-800 font-medium mt-2">Role</p>
                @switch($coworker->user->role)
                    @case('admin')
                        <flux:badge icon="user" color="red" size="sm">Admin</flux:badge>
                        @break
                    @case('sadmin')
                        <flux:badge icon="user" color="red" size="sm">Sadmin</flux:badge>
                        @break
                    @case('sclient')
                        <flux:badge icon="user" color="teal" size="sm">SClient</flux:badge>
                        @break
                    @case('client')
                        <flux:badge icon="user" color="teal" size="sm">Client</flux:badge>
                        @break
                @endswitch
                <p class="text-gray-800 font-medium mt-2">Est nouveau</p>
                @if($coworker->user->is_new)
                    <flux:badge icon="check-circle" color="green" size="sm">Oui</flux:badge>
                @else
                    <flux:badge icon="x-circle" color="red" size="sm">Non</flux:badge>
                @endif
                <p class="text-gray-800 font-medium mt-2">Accès à la formation</p>
                @if($coworker->user->can_access_formation)
                    <flux:badge icon="check-circle" color="green" size="sm">Oui</flux:badge>
                @else
                    <flux:badge icon="x-circle" color="red" size="sm">Non</flux:badge>
                @endif
                <p class="text-gray-800 font-medium mt-2">Double authentification</p>
                @if($coworker->user->two_factor_enabled)
                    <flux:badge icon="check-circle" color="green" size="sm">Oui</flux:badge>
                @else
                    <flux:badge icon="x-circle" color="red" size="sm">Non</flux:badge>
                @endif
            @else
                <flux:text>Aucun compte utilisateur associé</flux:text>
            @endif
        </div>
    </div>

    <!-- Formations -->
    <div class="grid grid-cols-2 gap-4 border border-gray-800/10 p-4 rounded-lg">
        <flux:heading size="lg">Formations</flux:heading>
        <div class="grid grid-cols-2 gap-4">
            @foreach($coworker->trainings as $training)
                <div>
                    <flux:heading size="md">{{ $training->title }}</flux:heading>
                </div>
            @endforeach
        </div>
    </div>
</div>
