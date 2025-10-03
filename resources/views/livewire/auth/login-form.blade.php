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
                viewable
                placeholder="Mot de passe"
                wire:model="password"
            >
            </flux:input>
        </flux:field>

        @error('email') 
        <flux:callout variant="danger" icon="x-circle" heading="{!! $message !!}" />
        @enderror
        @error('password') 
        <flux:callout variant="danger" icon="x-circle" heading="{!! $message !!}" />
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
