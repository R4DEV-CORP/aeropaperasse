@blaze

@props([
    'name' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'id' => null,
    'required' => false,
    'multiple' => false,
    'accept' => null,
    'newFiles' => null,
    'existingFiles' => null,
])

@php
    if (! $id && $label) {
        $id = 'file-'.md5($label);
    }
    $hasError = ! empty($error);

    $new = is_array($newFiles) ? array_values(array_filter($newFiles)) : ($newFiles ? [$newFiles] : []);
    $existing = is_array($existingFiles) ? array_values(array_filter($existingFiles)) : ($existingFiles ? [$existingFiles] : []);
    $hasNew = count($new) > 0;
    $hasExisting = count($existing) > 0;

    $defaultHint = 'PDF, JPG ou PNG'.($multiple ? ' — plusieurs fichiers acceptés' : '');

    $wireModelProperty = null;
    foreach (['wire:model.live', 'wire:model.blur', 'wire:model.change', 'wire:model'] as $key) {
        if ($attributes->has($key)) {
            $wireModelProperty = $attributes->get($key);
            break;
        }
    }
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-foreground-muted">
            {{ $label }}
            @if ($required && ! $hasExisting)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div
        x-data="{
            isDragging: false,
            isUploading: false,
            sessionFiles: [],
            onDragOver(e) {
                e.preventDefault();
                this.isDragging = true;
            },
            onDragLeave(e) {
                if (! this.$el.contains(e.relatedTarget)) {
                    this.isDragging = false;
                }
            },
            onDrop(e) {
                e.preventDefault();
                this.isDragging = false;
                const files = Array.from(e.dataTransfer?.files || []);
                if (files.length === 0) return;
                this.handleNewFiles(files);
            },
            onChange(e) {
                const files = Array.from(e.target.files || []);
                e.target.value = '';
                if (files.length === 0) return;
                this.handleNewFiles(files);
            },
            handleNewFiles(files) {
                @if ($wireModelProperty)
                    @if ($multiple)
                        this.sessionFiles = [...this.sessionFiles, ...files];
                        this.isUploading = true;
                        const done = () => { this.isUploading = false; };
                        // Livewire append les fichiers au tableau côté serveur :
                        // n'envoyer QUE les nouveaux pour éviter la duplication.
                        this.$wire.uploadMultiple(@js($wireModelProperty), files, done, done);
                    @else
                        this.sessionFiles = files.slice(0, 1);
                        this.isUploading = true;
                        const done = () => { this.isUploading = false; };
                        this.$wire.upload(@js($wireModelProperty), files[0], done, done);
                    @endif
                @endif
            },
            removeFile(index) {
                const remaining = this.sessionFiles.filter((_, i) => i !== index);
                this.sessionFiles = remaining;
                @if ($wireModelProperty)
                    if (remaining.length === 0) {
                        this.$wire.set(@js($wireModelProperty), []);
                        return;
                    }
                    // Reset le tableau côté serveur puis réuploade les restants.
                    this.isUploading = true;
                    const done = () => { this.isUploading = false; };
                    this.$wire.set(@js($wireModelProperty), []).then(() => {
                        this.$wire.uploadMultiple(@js($wireModelProperty), remaining, done, done);
                    });
                @endif
            },
            clearAll() {
                this.sessionFiles = [];
                @if ($wireModelProperty)
                    this.$wire.set(@js($wireModelProperty), @if ($multiple) [] @else null @endif);
                @endif
            },
            openPicker(e) {
                if (e && e.target.closest('button')) return;
                this.$refs.input?.click();
            },
        }"
        :class="isDragging && 'border-accent ring-2 ring-accent/20'"
        @class([
            'group overflow-hidden rounded-lg border-2 transition',
            'border-dashed border-slate-300 bg-white' => ! $hasNew && ! $hasExisting && ! $hasError,
            'border-dashed border-red-400 bg-red-50/40' => ! $hasNew && ! $hasExisting && $hasError,
            'border-solid border-accent bg-accent/5' => $hasNew,
            'border-solid border-border bg-slate-50' => ! $hasNew && $hasExisting,
        ])
    >
        {{-- Dropzone (top section: clickable, drag-droppable) --}}
        <div
            @dragover="onDragOver($event)"
            @dragleave="onDragLeave($event)"
            @drop="onDrop($event)"
            @click="openPicker($event)"
            @keydown.enter.prevent="$refs.input?.click()"
            @keydown.space.prevent="$refs.input?.click()"
            role="button"
            tabindex="0"
            class="relative flex min-h-[84px] cursor-pointer items-center gap-3 px-4 py-3 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-inset"
        >
            <input
                type="file"
                x-ref="input"
                id="{{ $id }}"
                class="sr-only"
                @if ($name) name="{{ $name }}" @endif
                @if ($accept) accept="{{ $accept }}" @endif
                @if ($multiple) multiple @endif
                @change="onChange($event)"
            >

            {{-- Icon column --}}
            <div class="flex-shrink-0">
                @if ($hasNew)
                    <span class="flex h-10 w-10 items-center justify-center rounded-md bg-accent/10 text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                @elseif ($hasExisting)
                    <span class="flex h-10 w-10 items-center justify-center rounded-md bg-slate-100 text-foreground-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </span>
                @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-md bg-slate-50 text-slate-400 transition group-hover:bg-slate-100 group-hover:text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9 4.5-4.5m0 0 4.5 4.5m-4.5-4.5v12" />
                        </svg>
                    </span>
                @endif
            </div>

            {{-- Content column --}}
            <div class="min-w-0 flex-1">
                @if ($hasNew && $multiple)
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-foreground">
                            {{ count($new) }} {{ count($new) > 1 ? 'fichiers sélectionnés' : 'fichier sélectionné' }}
                        </span>
                        <button
                            type="button"
                            @click.stop="clearAll()"
                            class="text-xs font-medium text-foreground-muted underline-offset-2 transition hover:text-red-600 hover:underline"
                        >
                            Tout effacer
                        </button>
                    </div>
                    <div class="mt-0.5 text-xs text-foreground-subtle">Cliquer ou glisser pour en ajouter</div>
                @elseif ($hasNew)
                    <div class="truncate text-sm font-medium text-foreground">{{ $new[0] }}</div>
                    <div class="text-xs text-accent">Nouveau fichier — cliquer pour remplacer</div>
                @elseif ($hasExisting && $multiple)
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-medium text-foreground">
                            {{ count($existing) }} {{ count($existing) > 1 ? 'documents' : 'document' }}
                        </span>
                        <span class="inline-flex rounded-full bg-slate-200/70 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-foreground-muted uppercase">Existant</span>
                    </div>
                    <div class="mt-0.5 text-xs text-foreground-subtle">Cliquer ou glisser pour ajouter de nouveaux fichiers</div>
                @elseif ($hasExisting)
                    <div class="flex items-center gap-1.5">
                        <span class="truncate text-sm font-medium text-foreground">{{ $existing[0] }}</span>
                        <span class="inline-flex flex-shrink-0 rounded-full bg-slate-200/70 px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-foreground-muted uppercase">Existant</span>
                    </div>
                    <div class="mt-0.5 text-xs text-foreground-subtle">Cliquer pour remplacer</div>
                @else
                    <div class="text-sm font-medium text-foreground">
                        {{ $multiple ? 'Cliquer ou glisser des fichiers' : 'Cliquer ou glisser un fichier' }}
                    </div>
                    <div class="mt-0.5 text-xs text-foreground-subtle">
                        {{ $hint ?? $defaultHint }}
                    </div>
                @endif
            </div>

            {{-- "+" affordance for multi when files are present --}}
            @if ($multiple && ($hasNew || $hasExisting))
                <div class="flex flex-shrink-0 flex-col items-center gap-0.5 self-center">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full border border-dashed border-slate-300 bg-white text-foreground-subtle transition group-hover:border-accent group-hover:text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </span>
                    <span class="text-[10px] font-medium tracking-wide text-foreground-subtle uppercase">Ajouter</span>
                </div>
            @endif

            {{-- Drop overlay --}}
            <div
                x-show="isDragging"
                x-cloak
                class="pointer-events-none absolute inset-0 flex items-center justify-center bg-accent/10 text-sm font-medium text-accent"
            >
                Déposer {{ $multiple ? 'les fichiers' : 'le fichier' }} ici
            </div>
        </div>

        {{-- Chips strip (multi mode, files present): wraps naturally, no scrollbar --}}
        @if ($multiple && $hasNew)
            <div class="border-t border-accent/20 bg-white/70 px-3 py-2.5">
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($new as $i => $n)
                        <span
                            wire:key="chip-new-{{ $i }}-{{ md5($n) }}"
                            class="group/chip inline-flex max-w-full items-center gap-1.5 rounded-md bg-white py-1 pr-1 pl-2 text-xs text-foreground ring-1 ring-accent/20 transition hover:ring-accent/40"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-3.5 w-3.5 flex-shrink-0 text-foreground-subtle">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span class="max-w-[200px] truncate font-medium">{{ $n }}</span>
                            <button
                                type="button"
                                @click.stop="removeFile({{ $i }})"
                                class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full text-foreground-subtle transition hover:bg-red-100 hover:text-red-600"
                                aria-label="Retirer {{ $n }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="h-3 w-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                    @endforeach
                </div>
            </div>
        @elseif ($multiple && $hasExisting && ! $hasNew)
            <div class="border-t border-border bg-white/70 px-3 py-2.5">
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($existing as $i => $e)
                        <span
                            wire:key="chip-existing-{{ $i }}-{{ md5($e) }}"
                            class="inline-flex max-w-full items-center gap-1.5 rounded-md bg-white px-2 py-1 text-xs text-foreground ring-1 ring-border"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-3.5 w-3.5 flex-shrink-0 text-foreground-subtle">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span class="max-w-[200px] truncate font-medium">{{ $e }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if ($hasError)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
