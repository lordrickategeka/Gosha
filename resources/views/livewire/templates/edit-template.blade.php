<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('templates.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">{{ $template->name }}</h1>
                <p class="text-base-content/60">{{ ucfirst(str_replace('_', ' ', $template->document_type)) }} Template</p>
            </div>
        </div>

        <div class="flex gap-2">
            {{-- Settings Modal Trigger --}}
            <button onclick="settings_modal.showModal()" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                </svg>
                Settings
            </button>
        </div>
    </div>

    {{-- React Editor Container --}}
    <div
        id="react-template-editor"
        data-template-id="{{ $template->id }}"
        data-template-name="{{ $template->name }}"
        data-document-type="{{ $template->document_type }}"
        data-template-schema="{{ json_encode($template->template_schema) }}"
        data-page-size="{{ $template->page_size }}"
        data-orientation="{{ $template->orientation }}"
        data-primary-color="{{ $template->primary_color }}"
        data-secondary-color="{{ $template->secondary_color }}"
        data-font-family="{{ $template->font_family }}"
        data-font-size="{{ $template->font_size }}"
    >
        {{-- Loading State --}}
        <div class="flex items-center justify-center h-screen">
            <div class="text-center">
                <span class="loading loading-spinner loading-lg"></span>
                <p class="mt-4 text-base-content/60">Loading template editor...</p>
            </div>
        </div>
    </div>

    {{-- Settings Modal --}}
    <dialog id="settings_modal" class="modal">
        <div class="modal-box app-modal-shell">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>

            <h3 class="font-bold text-lg mb-4">Template Settings</h3>

            <form wire:submit="updateSettings">
                <div class="space-y-4">
                    {{-- Name --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Template Name</span>
                        </label>
                        <input
                            type="text"
                            wire:model="name"
                            class="input input-bordered"
                        />
                        @error('name')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Page Settings --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Page Size</span>
                            </label>
                            <select wire:model="page_size" class="select select-bordered select-sm">
                                <option value="A4">A4</option>
                                <option value="Letter">Letter</option>
                                <option value="A5">A5</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Orientation</span>
                            </label>
                            <select wire:model="orientation" class="select select-bordered select-sm">
                                <option value="portrait">Portrait</option>
                                <option value="landscape">Landscape</option>
                            </select>
                        </div>
                    </div>

                    {{-- Colors --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Primary Color</span>
                            </label>
                            <input
                                type="color"
                                wire:model="primary_color"
                                class="input input-bordered h-12"
                            />
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Secondary Color</span>
                            </label>
                            <input
                                type="color"
                                wire:model="secondary_color"
                                class="input input-bordered h-12"
                            />
                        </div>
                    </div>

                    {{-- Font --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Font Family</span>
                            </label>
                            <select wire:model="font_family" class="select select-bordered select-sm">
                                <option value="Inter">Inter</option>
                                <option value="Arial">Arial</option>
                                <option value="Helvetica">Helvetica</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Roboto">Roboto</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Base Font Size</span>
                            </label>
                            <input
                                type="number"
                                wire:model="font_size"
                                min="8"
                                max="16"
                                class="input input-bordered input-sm"
                            />
                        </div>
                    </div>
                </div>

                <div class="modal-action app-modal-actions">
                    <button type="button" onclick="settings_modal.close()" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save Settings</span>
                        <span wire:loading class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop app-modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</div>

@push('scripts')
    @vite(['resources/js/template-builder/index.jsx'])
@endpush
