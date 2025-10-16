<form wire:submit.prevent="editExpiryDate" novalidate class="space-y-6">
    <div class="border-b border-gray-800/10 pb-4">
        <div class="flex justify-between">
            <flux:heading size="xl">
                Modifier la date d'expiration
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

    @if(auth()->user()->isAdmin())
        <flux:field>
            <flux:label>Date d'expiration</flux:label>
            <flux:input wire:model="expiryDate" type="date" />
            @error('expiryDate')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>
    @endif

    <div class="mt-6 flex justify-end gap-3">
        <flux:button type="button" variant="subtle" wire:click="$dispatch('close-modal', { name: 'edit-badge-expiry-date-{{ $badge->id }}' })">
            Annuler
        </flux:button>
        <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
            <span wire:loading.remove>Modifier la date</span>
            <span wire:loading>Modification...</span>
        </flux:button>
    </div>
</form>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('close-modal', (event) => {
        // Fermer la modal spécifique
        const modalName = event.detail.name;
        const modal = document.querySelector(`[data-modal-name="${modalName}"]`);
        if (modal) {
            modal.dispatchEvent(new CustomEvent('close'));
        }
    });
});
</script>