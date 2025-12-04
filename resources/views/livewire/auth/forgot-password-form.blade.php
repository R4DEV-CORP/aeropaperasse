<div>
    <form wire:submit="forgotPassword" class="flex flex-col gap-6">
        <flux:input 
            type="email" 
            icon="envelope" 
            placeholder="Email"
            wire:model="email"
        />
        <flux:button 
            type="submit"
            variant="primary" 
            color="zinc" 
            class="w-full hover:cursor-pointer mt-4"
        >
            Recevoir le lien de réinitialisation
        </flux:button>
    </form>

    @error('email') 
    <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" class="mt-4"/>
    @enderror

    @if($successMessage)
    <flux:callout variant="success" icon="check-circle" heading="{{ $successMessage }}" class="mt-4"/>
    @endif

    <flux:button 
        wire:navigate href="/login"    
        icon:trailing="arrow-right"
        variant="ghost"
        class="w-full text-blue-500 hover:text-blue-600 hover:cursor-pointer mt-4"
    >
        Retour à la connexion
    </flux:button>
</div>
