<div>
    <form wire:submit="changePassword" class="flex flex-col gap-6">
        <flux:field>
            <flux:input 
                type="password" 
                icon="lock-closed" 
                viewable
                placeholder="Ancien mot de passe"
                wire:model="current_password"
            >
            </flux:input>
        </flux:field>
        <flux:field>
            <flux:input 
                type="password" 
                icon="lock-closed" 
                viewable
                placeholder="Nouveau mot de passe"
                wire:model="password"
            >
            </flux:input>
        </flux:field>
        <flux:field>
            <flux:input 
                type="password" 
                icon="lock-closed" 
                viewable
                placeholder="Confirmation du mot de passe"
                wire:model="password_confirmation"
            >
            </flux:input>
        </flux:field>

        @error('password') 
        <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
        @enderror
        @error('current_password') 
        <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
        @enderror
        @error('password_confirmation') 
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
