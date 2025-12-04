<div>
    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <flux:input 
            type="password" 
            icon="lock-closed" 
            placeholder="Nouveau mot de passe"
            wire:model="password"
        />
        <flux:input 
            type="password" 
            icon="lock-closed" 
            placeholder="Confirmation du nouveau mot de passe"
            wire:model="password_confirmation"
        />
        <flux:button 
            type="submit"
            variant="primary" 
            color="zinc" 
            class="w-full hover:cursor-pointer mt-4"
        >
            Réinitialiser le mot de passe
        </flux:button>
    </form>

    @error('password') 
    <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" class="mt-4"/>
    @enderror

    @error('password_confirmation') 
    <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" class="mt-4"/>
    @enderror

    @if($successMessage)
    <flux:callout variant="success" icon="check-circle" heading="{{ $successMessage }}" class="mt-4"/>
    @endif
</div>
