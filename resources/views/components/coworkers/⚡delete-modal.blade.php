<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Coworker;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $coworkerId = null;

    public string $confirmation = '';

    #[On('open-delete-coworker')]
    public function open(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $coworker = Coworker::find($id);
        if (! $coworker) {
            return;
        }

        $this->coworkerId = $id;
        $this->confirmation = '';
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'coworker-delete');
    }

    #[Computed]
    public function coworker(): ?Coworker
    {
        if ($this->coworkerId === null) {
            return null;
        }

        return Coworker::with(['client', 'user'])->find($this->coworkerId);
    }

    public function submit(): void
    {
        if (! auth()->user()->isAdmin() || $this->coworkerId === null) {
            return;
        }

        $coworker = Coworker::with('user')->find($this->coworkerId);
        if (! $coworker) {
            $this->toast('Collaborateur introuvable.', 'danger');

            return;
        }

        $expected = mb_strtolower(trim($coworker->firstname.' '.$coworker->lastname));
        if (mb_strtolower(trim($this->confirmation)) !== $expected) {
            $this->addError('confirmation', 'Le nom saisi ne correspond pas.');

            return;
        }

        try {
            $coworker->delete();

            $this->toast(
                $coworker->firstname.' '.$coworker->lastname.' supprimé.',
                'success',
                'Collaborateur supprimé'
            );

            $this->dispatch('close-modal', name: 'coworker-delete');
            $this->dispatch('coworker-deleted');

            $this->reset(['coworkerId', 'confirmation']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du collaborateur', [
                'error' => $e->getMessage(),
                'coworker_id' => $coworker->id,
                'user_id' => auth()->user()->id,
            ]);

            $this->toast(
                'Impossible de supprimer le collaborateur. Il existe sans doute des demandes ou des badges associés.',
                'danger',
                'Suppression impossible'
            );
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'coworker-delete');
        $this->reset(['coworkerId', 'confirmation']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="coworker-delete" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Supprimer le collaborateur</h2>
                <p class="text-sm text-foreground-muted">Action irréversible. La suppression échouera s'il existe des demandes ou badges associés.</p>
            </div>

            @if ($this->coworker)
                @php $cw = $this->coworker; @endphp
                <div class="grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Collaborateur</div>
                        <div class="mt-0.5 font-medium text-foreground">{{ $cw->firstname }} {{ $cw->lastname }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</div>
                        <div class="mt-0.5 text-foreground">{{ $cw->client?->company_name ?? '—' }}</div>
                    </div>
                </div>

                <x-ui.alert variant="danger">
                    @if ($cw->user_id)
                        Le compte utilisateur associé sera également supprimé.
                    @else
                        Cette action est définitive.
                    @endif
                </x-ui.alert>

                <x-ui.input
                    label="Saisissez « {{ $cw->firstname }} {{ $cw->lastname }} » pour confirmer"
                    wire:model.live="confirmation"
                    placeholder="{{ $cw->firstname }} {{ $cw->lastname }}"
                    :error="$errors->first('confirmation')"
                />
            @endif

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="danger">
                    <span wire:loading.remove wire:target="submit">Supprimer</span>
                    <span wire:loading wire:target="submit">Suppression…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
