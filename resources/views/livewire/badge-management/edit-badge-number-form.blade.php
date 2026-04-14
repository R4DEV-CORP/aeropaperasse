<form wire:submit.prevent="editBadgeNumber" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <div class="flex justify-between">
            <flux:heading size="xl">
                Modifier le numéro de badge
            </flux:heading>
            <flux:badge color="red" class="mr-8">Admin</flux:badge>
        </div>
    </div>

    @if($errorMessage)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <flux:icon.x-circle class="size-5 text-red-600 mr-2" />
                <flux:text class="text-red-800">{{ $errorMessage }}</flux:text>
            </div>
        </div>
    @endif

    @if($successMessage)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <flux:icon.check-circle class="size-5 text-green-600 mr-2" />
                <flux:text class="text-green-800">{{ $successMessage }}</flux:text>
            </div>
        </div>
    @endif

    <flux:field>
        <flux:label>Numéro de badge</flux:label>
        <flux:input wire:model="badgeNumber" placeholder="Ex : 123456" />
        @error('badgeNumber')
            <flux:error>{{ $message }}</flux:error>
        @enderror
    </flux:field>

    <div class="flex gap-2 mt-4">
        <flux:button wire:click="closeModal">Annuler</flux:button>
        <flux:spacer />
        <flux:button type="submit" icon="pencil" variant="primary" wire:loading.attr="disabled" wire:target="editBadgeNumber">Modifier le numéro</flux:button>
    </div>
</form>
