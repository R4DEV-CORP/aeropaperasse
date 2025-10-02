<div>
    <form wire:submit.prevent="login" class="space-y-4">
        <div class="relative">
            <input
                type="email"
                wire:model="email"
                required
                placeholder="Email"
                class="pl-10 w-full px-3 py-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-2 focus:border-gray-500"
            />
        </div>

        <div className="relative">
            <input
                type="password"
                wire:model="password"
                required
                placeholder="Mot de passe"
                class="pl-10 w-full px-3 py-2 bg-white border border-gray-300 rounded-md focus:outline-none focus:border-2 focus:border-gray-500"
            />
        </div>
        <button type="submit" class="w-full bg-gray-600 text-white py-2 px-4 rounded-md cursor-pointer hover:bg-gray-700 focus:outline-none">
            Se connecter
        </button>
    </form>
</div>
