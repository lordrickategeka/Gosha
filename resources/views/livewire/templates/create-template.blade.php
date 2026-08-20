<div>
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('templates.index') }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">New Template</h1>
            <p class="text-base-content/60">Create a custom document template</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm max-w-2xl">
        <div class="card-body">
            <form wire:submit="save">
                <div class="space-y-4">
                    {{-- Template Name --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Template Name *</span>
                        </label>
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="e.g., Modern Invoice Blue"
                            class="input input-bordered"
                            required
                        />
                        @error('name')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Document Type --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Document Type *</span>
                        </label>
                        <select wire:model="document_type" class="select select-bordered" required>
                            <option value="invoice">Invoice</option>
                            <option value="work_order">Work Order</option>
                            <option value="quotation">Quotation</option>
                            <option value="receipt">Receipt</option>
                            <option value="report">Report</option>
                        </select>
                        @error('document_type')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="divider">Page Settings</div>

                    {{-- Page Size & Orientation --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Page Size</span>
                            </label>
                            <select wire:model="page_size" class="select select-bordered">
                                <option value="A4">A4</option>
                                <option value="Letter">Letter</option>
                                <option value="A5">A5</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Orientation</span>
                            </label>
                            <select wire:model="orientation" class="select select-bordered">
                                <option value="portrait">Portrait</option>
                                <option value="landscape">Landscape</option>
                            </select>
                        </div>
                    </div>

                    <div class="divider">Styling</div>

                    {{-- Colors --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Primary Color</span>
                            </label>
                            <div class="flex gap-2">
                                <input
                                    type="color"
                                    wire:model.live="primary_color"
                                    class="input input-bordered w-20"
                                />
                                <input
                                    type="text"
                                    wire:model="primary_color"
                                    class="input input-bordered flex-1"
                                    placeholder="#3B82F6"
                                />
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Secondary Color</span>
                            </label>
                            <div class="flex gap-2">
                                <input
                                    type="color"
                                    wire:model.live="secondary_color"
                                    class="input input-bordered w-20"
                                />
                                <input
                                    type="text"
                                    wire:model="secondary_color"
                                    class="input input-bordered flex-1"
                                    placeholder="#1E40AF"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Font --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Font Family</span>
                            </label>
                            <select wire:model="font_family" class="select select-bordered">
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
                                <span class="label-text font-medium">Base Font Size</span>
                            </label>
                            <input
                                type="number"
                                wire:model="font_size"
                                min="8"
                                max="16"
                                class="input input-bordered"
                            />
                            <span class="label-text-alt">Recommended: 10-12pt</span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('templates.index') }}" class="btn btn-ghost">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Create Template</span>
                        <span wire:loading class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
