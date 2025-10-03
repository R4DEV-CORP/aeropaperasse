<div>
    <form wire:submit="login" class="flex flex-col gap-6">
        <flux:input 
            type="email" 
            icon="envelope" 
            placeholder="Email"
            wire:model="email"
        />
        
        <flux:field>
            <flux:input 
                type="password" 
                icon="lock-closed" 
                placeholder="Mot de passe"
                wire:model="password"
            >
                <x-slot name="iconTrailing">
                    <flux:button size="sm" variant="subtle" icon="eye" class="-mr-1" />
                </x-slot>
            </flux:input>
        </flux:field>

        @error('email') 
        <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
        @enderror
        
        <flux:button 
            type="submit"
            variant="primary" 
            color="zinc" 
            class="w-full hover:cursor-pointer"
        >
            Se connecter
        </flux:button>
    </form>
</div>
