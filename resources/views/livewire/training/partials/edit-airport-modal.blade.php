<flux:modal :name="'edit-airport-modal-'.$training->id" wire:key="airport-modal-{{ $keyPrefix }}-{{ $training->id }}" class="min-w-md">
    <flux:heading size="lg">Modifier l'aéroport</flux:heading>
    <flux:text class="mt-1">{{ $training->coworker_firstname }} {{ $training->coworker_lastname }} — {{ $training->training_title }}</flux:text>

    <form wire:submit.prevent="updateAirport({{ $training->id }})" class="mt-4">
        <flux:radio.group wire:model="airportToUpdate" label="Aéroport">
            <flux:radio value="ORY" label="ORY" />
            <flux:radio value="CDG" label="CDG" />
            <flux:radio value="LBG" label="LBG" />
            <flux:radio value="" label="Aucun" />
        </flux:radio.group>
        <flux:error name="airportToUpdate" />

        <div class="flex justify-end gap-3 mt-6">
            <flux:button type="button" variant="ghost" wire:click="$dispatch('close-modal', 'edit-airport-modal-{{ $training->id }}')">
                Annuler
            </flux:button>
            <flux:button type="submit" variant="primary">Enregistrer</flux:button>
        </div>
    </form>
</flux:modal>
