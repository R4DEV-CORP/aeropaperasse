<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\BadgeReturned;
use App\Models\Badge;
use App\Models\BadgeRequest;
use App\Services\BadgeRequestDocumentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use InteractsWithToasts;
    use WithFileUploads;

    public ?int $badgeId = null;

    public $return_badge_document = null;

    #[On('return-badge')]
    public function open(int $id): void
    {
        if (auth()->user()->isClient()) {
            return;
        }

        $this->badgeId = $id;
        $this->return_badge_document = null;
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'return-badge');
    }

    #[Computed]
    public function badge(): ?Badge
    {
        if ($this->badgeId === null) {
            return null;
        }

        return Badge::with(['client', 'coworker', 'badgeRequest.coworker', 'badgeRequest.activityRequest.client'])
            ->find($this->badgeId);
    }

    public function submit(): void
    {
        if (auth()->user()->isClient() || $this->badgeId === null) {
            return;
        }

        $this->validate([
            'return_badge_document' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
        ], [
            'return_badge_document.required' => 'Le document de restitution est obligatoire.',
            'return_badge_document.file' => 'Le document est invalide.',
            'return_badge_document.mimes' => 'Le document doit être un PDF, PNG ou JPG.',
            'return_badge_document.max' => 'Le document ne doit pas dépasser 10 Mo.',
        ]);

        $badge = Badge::find($this->badgeId);
        if ($badge === null) {
            $this->toast('Badge introuvable.', 'danger');

            return;
        }

        $client = $badge->getEffectiveClient();
        if (! $client) {
            $this->toast('Impossible de déterminer le client du badge.', 'danger');

            return;
        }

        try {
            $service = new BadgeRequestDocumentService;

            if ($badge->badge_request_id) {
                $badgeRequest = BadgeRequest::findOrFail($badge->badge_request_id);
                $stored = $service->storeDocuments(
                    ['return_badge_document' => $this->return_badge_document],
                    $client,
                    $badgeRequest
                );
                $returnDocumentPath = $stored['return_badge_document'];
            } else {
                $returnDocumentPath = $service->storeBadgeReturnDocument(
                    $this->return_badge_document,
                    $client,
                    $badge->id
                );
            }

            $badge->update([
                'previous_status' => $badge->status,
                'status' => 'returned',
                'returned_at' => now(),
                'return_document' => $returnDocumentPath,
            ]);

            $email = $badge->getRecipientEmail();
            if ($email) {
                Mail::to($email)->send(new BadgeReturned($badge, $returnDocumentPath));
            }

            $this->toast('Badge marqué comme restitué.', 'success', 'Badge restitué');

            $this->dispatch('close-modal', name: 'return-badge');
            $this->dispatch('badge-updated');

            $this->reset(['badgeId', 'return_badge_document']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la restitution du badge', [
                'error' => $e->getMessage(),
                'badge_id' => $badge->id,
                'user_id' => auth()->user()->id,
            ]);

            $this->toast('Une erreur est survenue lors de la restitution.', 'danger');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'return-badge');
        $this->reset(['badgeId', 'return_badge_document']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="return-badge" maxWidth="2xl">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Restituer le badge</h2>
                <p class="text-sm text-foreground-muted">Téléversez le document de restitution signé pour clore le cycle de vie du badge.</p>
            </div>

            @if ($this->badge)
                @php
                    $b = $this->badge;
                    $cw = $b->getEffectiveCoworker();
                    $cl = $b->getEffectiveClient();
                @endphp
                <div class="grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50 p-4 text-sm sm:grid-cols-3">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">N° badge</div>
                        <div class="mt-0.5 font-mono text-foreground">{{ $b->badge_number ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Détenteur</div>
                        <div class="mt-0.5 text-foreground">{{ trim(($cw?->firstname ?? '').' '.($cw?->lastname ?? '')) ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Société</div>
                        <div class="mt-0.5 text-foreground">{{ $cl?->company_name ?? '—' }}</div>
                    </div>
                </div>
            @endif

            <x-ui.file-upload
                wire:model="return_badge_document"
                label="Document de restitution"
                accept="application/pdf,image/jpeg,image/png"
                hint="PDF, JPG ou PNG — 10 Mo max"
                :new-files="$return_badge_document?->getClientOriginalName()"
                :error="$errors->first('return_badge_document')"
                required
            />

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="submit">Confirmer la restitution</span>
                    <span wire:loading wire:target="submit">Envoi...</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
