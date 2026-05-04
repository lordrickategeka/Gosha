<div class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <div class="flex items-center justify-between mb-4">
            <h2 class="card-title text-lg">Job Items</h2>

            {{-- Template Selector --}}
            @if($this->templates->isNotEmpty())
                <select wire:model="selectedTemplate" wire:change="applyTemplate" class="select select-bordered select-sm">
                    <option value="">Apply Template...</option>
                    @foreach($this->templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        {{-- Items List --}}
        @if(count($items) > 0)
            <div class="space-y-4 mb-4">
                @foreach($items as $index => $item)
                    <div class="border border-base-300 rounded-lg p-4 relative bg-base-50">
                        {{-- Remove Button --}}
                        <button
                            type="button"
                            wire:click="removeItem({{ $index }})"
                            class="btn btn-ghost btn-xs text-error absolute top-2 right-2"
                        >
                            ✕
                        </button>

                        <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                            {{-- Item Type --}}
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text text-xs font-medium">Type</span>
                                </label>
                                <select
                                    wire:model="items.{{ $index }}.item_type"
                                    class="select select-bordered select-sm"
                                >
                                    <option value="labor">Labor</option>
                                    <option value="part">Part</option>
                                </select>
                            </div>

                            {{-- Description --}}
                            <div class="form-control sm:col-span-3">
                                <label class="label py-1">
                                    <span class="label-text text-xs font-medium">Description *</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model="items.{{ $index }}.description"
                                    placeholder="e.g., Oil change, Brake pads..."
                                    class="input input-bordered input-sm"
                                />
                                @error("items.$index.description")
                                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Quantity --}}
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text text-xs font-medium">Qty *</span>
                                </label>
                                <input
                                    type="number"
                                    wire:model="items.{{ $index }}.quantity"
                                    step="0.01"
                                    min="0.01"
                                    class="input input-bordered input-sm"
                                />
                                @error("items.$index.quantity")
                                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Unit Price (hidden for jobcarder) --}}
                            @if(!$this->isJobcarder())
                                <div class="form-control">
                                    <label class="label py-1">
                                        <span class="label-text text-xs font-medium">Price</span>
                                    </label>
                                    <input
                                        type="number"
                                        wire:model="items.{{ $index }}.unit_price"
                                        step="1"
                                        min="0"
                                        placeholder="0"
                                        class="input input-bordered input-sm"
                                    />
                                    @error("items.$index.unit_price")
                                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        {{-- Inventory Item Selection (for parts only) --}}
                        @if(($item['item_type'] ?? 'labor') === 'part')
                            <div class="form-control mt-3">
                                <label class="label py-1">
                                    <span class="label-text text-xs font-medium">Link to Inventory (optional)</span>
                                </label>
                                <select
                                    wire:model="items.{{ $index }}.inventory_item_id"
                                    class="select select-bordered select-sm"
                                >
                                    <option value="">None</option>
                                    @foreach($this->inventoryParts as $part)
                                        <option value="{{ $part->id }}">
                                            {{ $part->name }} ({{ $part->sku }}) - Stock: {{ $part->quantity }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-xs text-base-content/50 mt-1">
                                    Linking will automatically deduct inventory
                                </span>
                            </div>
                        @endif

                        {{-- Line Total --}}
                        @if(!$this->isJobcarder())
                            <div class="flex justify-end mt-3">
                                <div class="badge badge-lg">
                                    Total: UGX {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)) }}
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-base-content/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="font-medium">No items added yet</p>
                <p class="text-sm">Add labor or parts to this work order</p>
            </div>
        @endif

        @error('items')
            <div class="alert alert-error py-2 mb-4">
                <span class="text-sm">{{ $message }}</span>
            </div>
        @enderror

        {{-- Add Item Buttons --}}
        <div class="flex gap-2">
            <button
                type="button"
                wire:click="addItem('labor')"
                class="btn btn-outline btn-sm"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Labor
            </button>
            <button
                type="button"
                wire:click="addItem('part')"
                class="btn btn-outline btn-sm"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Part
            </button>
        </div>

        {{-- Subtotal --}}
        @if(!$this->isJobcarder() && count($items) > 0)
            <div class="flex justify-end mt-4 pt-4 border-t border-base-300">
                <div class="text-right">
                    <div class="text-sm text-base-content/60">Subtotal</div>
                    <div class="text-2xl font-bold">UGX {{ number_format($this->subtotal) }}</div>
                </div>
            </div>
        @endif

        {{-- Jobcarder Notice --}}
        @if($this->isJobcarder())
            <div class="alert alert-info mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm">Pricing will be set by the quoter after job card creation.</span>
            </div>
        @endif
    </div>
</div>

{{-- Navigation --}}
<div class="flex justify-between mt-6">
    <button type="button" wire:click="previousStep" class="btn btn-ghost">
        ← Back
    </button>
    <button type="button" wire:click="nextStep" class="btn btn-primary">
        Review & Submit →
    </button>
</div>
