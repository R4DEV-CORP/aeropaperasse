<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::auth')]
#[Title('Accès refusé')]
class extends Component
{
    public ?string $space = null;

    public function mount(): void
    {
        $this->space = tenant()?->getTenantKey();
    }
}; ?>

<div class="space-y-6">
    <div class="space-y-1 text-center">
        <h1 class="text-2xl font-semibold text-foreground">Accès à cet espace refusé</h1>
        <p class="text-sm text-foreground-muted">
            Votre compte n'a pas accès à cet espace
            @if ($space)
                (<span class="font-medium">{{ $space }}</span>)
            @endif
            . Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur de cet espace.
        </p>
    </div>

    <x-ui.alert variant="warning">
        Vous êtes bien connecté(e), mais vous n'êtes pas rattaché(e) à cet espace. Vous pouvez vous
        déconnecter et vous reconnecter sur l'espace qui vous est attribué.
    </x-ui.alert>

    <x-ui.button :href="route('auth.logout')" variant="primary" class="w-full">
        Se déconnecter
    </x-ui.button>
</div>
