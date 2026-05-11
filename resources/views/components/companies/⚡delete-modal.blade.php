<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    use InteractsWithToasts;

    public ?int $companyId = null;

    public string $confirmation = '';

    #[On('open-delete-company')]
    public function open(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $company = Client::find($id);
        if (! $company) {
            return;
        }

        $this->companyId = $id;
        $this->confirmation = '';
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'company-delete');
    }

    #[Computed]
    public function company(): ?Client
    {
        if ($this->companyId === null) {
            return null;
        }

        return Client::withCount(['coworkers', 'users', 'activityRequests'])->find($this->companyId);
    }

    public function submit(): void
    {
        if (! auth()->user()->isAdmin() || $this->companyId === null) {
            return;
        }

        $company = Client::find($this->companyId);
        if (! $company) {
            $this->toast('Société introuvable.', 'danger');

            return;
        }

        $expected = mb_strtolower(trim($company->company_name));
        if (mb_strtolower(trim($this->confirmation)) !== $expected) {
            $this->addError('confirmation', 'Le nom saisi ne correspond pas.');

            return;
        }

        try {
            $company->delete();

            $this->toast(
                $company->company_name.' supprimée.',
                'success',
                'Société supprimée'
            );

            $this->dispatch('close-modal', name: 'company-delete');
            $this->dispatch('company-deleted');

            $this->reset(['companyId', 'confirmation']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la société', [
                'error' => $e->getMessage(),
                'company_id' => $company->id,
                'user_id' => auth()->user()->id,
            ]);

            $this->toast(
                'Impossible de supprimer la société. Elle a sans doute des collaborateurs, demandes ou badges associés.',
                'danger',
                'Suppression impossible'
            );
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'company-delete');
        $this->reset(['companyId', 'confirmation']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="company-delete" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Supprimer la société</h2>
                <p class="text-sm text-foreground-muted">Action irréversible. La suppression échouera s'il existe des collaborateurs, demandes ou badges associés.</p>
            </div>

            @if ($this->company)
                @php $co = $this->company; @endphp
                <div class="grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</div>
                        <div class="mt-0.5 font-medium text-foreground">{{ $co->company_name }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">SIRET</div>
                        <div class="mt-0.5 font-mono text-foreground">{{ $co->siret_number }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Collaborateurs</div>
                        <div class="mt-0.5 text-foreground">{{ $co->coworkers_count }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Demandes d'activité</div>
                        <div class="mt-0.5 text-foreground">{{ $co->activity_requests_count }}</div>
                    </div>
                </div>

                <x-ui.alert variant="danger">
                    Cette action est définitive.
                </x-ui.alert>

                <x-ui.input
                    label="Saisissez « {{ $co->company_name }} » pour confirmer"
                    wire:model.live="confirmation"
                    placeholder="{{ $co->company_name }}"
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
