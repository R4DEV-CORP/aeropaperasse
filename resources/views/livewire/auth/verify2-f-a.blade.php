<div class="flex flex-col gap-6">
    <form wire:submit="verifyCode" class="flex flex-col gap-6">
        <flux:input 
            type="text" 
            icon="key" 
            placeholder="Entrez le code à 6 chiffres"
            wire:model="code"
            maxlength="6"
            pattern="[0-9]*"
            @input="this.value = this.value.replace(/[^0-9]/g, '')"
        />
        
        @error('code') 
        <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
        @enderror
        
        <flux:button 
            type="submit"
            variant="primary" 
            color="zinc" 
            class="w-full hover:cursor-pointer"
        >
            <span wire:loading.remove wire:target="verifyCode">Vérifier le code</span>
            <span wire:loading wire:target="verifyCode">Vérification...</span>
        </flux:button>
    </form>
</div>