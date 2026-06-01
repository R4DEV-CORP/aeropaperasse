<?php

use App\Livewire\Concerns\InteractsWithToasts;
use App\Services\CertificateTrainingDocumentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use InteractsWithToasts, WithFileUploads;

    public ?int $coworkerTrainingId = null;

    public $certificate = null;

    #[On('open-upload-certificate')]
    public function open(int $coworkerTrainingId): void
    {
        $authUser = auth()->user();
        if ($authUser->isClient() && ! $authUser->canAccessFormation()) {
            return;
        }

        $this->coworkerTrainingId = $coworkerTrainingId;
        $this->certificate = null;
        $this->resetErrorBag();

        $this->dispatch('open-modal', name: 'upload-certificate');
    }

    #[Computed]
    public function context(): ?object
    {
        if (! $this->coworkerTrainingId) {
            return null;
        }

        return DB::table('coworker_trainings')
            ->join('coworkers', 'coworker_trainings.coworker_id', '=', 'coworkers.id')
            ->join('trainings', 'coworker_trainings.training_id', '=', 'trainings.id')
            ->where('coworker_trainings.id', $this->coworkerTrainingId)
            ->select(
                'coworker_trainings.certificate_path',
                'coworkers.firstname',
                'coworkers.lastname',
                'trainings.title as training_title',
            )
            ->first();
    }

    public function submit(): void
    {
        if ($this->coworkerTrainingId === null) {
            return;
        }

        $this->validate([
            'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'certificate.required' => 'Veuillez sélectionner un fichier.',
            'certificate.mimes' => 'Le fichier doit être un PDF, JPG ou PNG.',
            'certificate.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
        ]);

        try {
            $service = new CertificateTrainingDocumentService;
            $service->uploadCertificate($this->coworkerTrainingId, $this->certificate);

            $this->toast('Certificat uploadé avec succès.', 'success', 'Upload réussi');
            $this->dispatch('close-modal', name: 'upload-certificate');
            $this->dispatch('training-certificate-uploaded');

            $this->reset(['coworkerTrainingId', 'certificate']);
        } catch (\Exception $e) {
            Log::error('Erreur upload certificat', [
                'error' => $e->getMessage(),
                'coworker_training_id' => $this->coworkerTrainingId,
                'user_id' => auth()->user()->id,
            ]);
            $this->toast("Erreur lors de l'upload : ".$e->getMessage(), 'danger');
        }
    }

    public function cancel(): void
    {
        $this->dispatch('close-modal', name: 'upload-certificate');
        $this->reset(['coworkerTrainingId', 'certificate']);
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-ui.modal name="upload-certificate" maxWidth="lg">
        <form wire:submit.prevent="submit" class="space-y-5 p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-foreground">Uploader un certificat</h2>
                <p class="text-sm text-foreground-muted">PDF, JPG ou PNG — max 10 Mo.</p>
            </div>

            @if ($this->context)
                @php $ctx = $this->context; @endphp
                <div class="grid grid-cols-1 gap-3 rounded-md border border-border bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Collaborateur</div>
                        <div class="mt-0.5 font-medium text-foreground">{{ $ctx->firstname }} {{ $ctx->lastname }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-medium uppercase tracking-wide text-foreground-muted">Formation</div>
                        <div class="mt-0.5 text-foreground">{{ $ctx->training_title }}</div>
                    </div>
                </div>

                <x-ui.file-upload
                    label="Certificat"
                    wire:model.live="certificate"
                    accept="application/pdf,image/jpeg,image/png"
                    :newFiles="$certificate?->getClientOriginalName()"
                    :existingFiles="$ctx->certificate_path ? basename($ctx->certificate_path) : null"
                    :required="! $ctx->certificate_path"
                    :error="$errors->first('certificate')"
                    hint="PDF / JPG / PNG, max 10 Mo"
                />
            @endif

            <div class="flex items-center justify-end gap-2 border-t border-border pt-4">
                <x-ui.button type="button" variant="ghost" wire:click="cancel">
                    Annuler
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    <span wire:loading.remove wire:target="submit">Uploader</span>
                    <span wire:loading wire:target="submit">Upload…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
