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
            <div class="space-y-3 mb-4">
                @foreach($items as $index => $item)
                    <div class="border border-base-300 rounded-lg p-4 relative bg-base-50">

                        {{-- Remove Button --}}
                        <button
                            type="button"
                            wire:click="removeItem({{ $index }})"
                            class="btn btn-ghost btn-xs text-error absolute top-2 right-2"
                        >✕</button>

                        <div class="grid grid-cols-12 gap-3 items-start">

                            {{-- Item Type --}}
                            <div class="form-control col-span-12 sm:col-span-2">
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

                            {{-- Description with live inventory search --}}
                            <div class="form-control col-span-12 sm:col-span-7"
                                 x-data="{ open: false }"
                                 x-on:focusin="open = true"
                                 x-on:focusout="setTimeout(() => { open = false }, 200)">
                                <label class="label py-1">
                                    <span class="label-text text-xs font-medium">Description / Search Inventory *</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        wire:model.live.debounce.350ms="items.{{ $index }}.description"
                                        placeholder="Type to search inventory or enter a custom item..."
                                        class="input input-bordered input-sm w-full"
                                        autocomplete="off"
                                    />

                                    {{-- Inventory suggestions dropdown --}}
                                    @php
                                        $mySuggestions    = $itemSuggestions[$index]['my_branch']      ?? [];
                                        $otherSuggestions = $itemSuggestions[$index]['other_branches']  ?? [];
                                        $hasSuggestions   = count($mySuggestions) > 0 || count($otherSuggestions) > 0;
                                    @endphp

                                    @if($hasSuggestions)
                                        <div
                                            x-show="open"
                                            class="absolute z-50 top-full left-0 right-0 bg-base-100 border border-base-300 rounded-lg shadow-xl mt-1 max-h-64 overflow-y-auto"
                                        >
                                            {{-- Current branch results --}}
                                            @if(count($mySuggestions) > 0)
                                                <div class="px-3 py-1 text-xs font-semibold text-base-content/50 bg-base-200 sticky top-0">
                                                    This Branch
                                                </div>
                                                @foreach($mySuggestions as $suggestion)
                                                    <button
                                                        type="button"
                                                        @mousedown.prevent
                                                        wire:click="selectInventoryItem({{ $index }}, {{ $suggestion['id'] }})"
                                                        class="w-full text-left px-3 py-2 hover:bg-base-200 flex items-center justify-between text-sm border-b border-base-200 last:border-0"
                                                    >
                                                        <span class="font-medium">{{ $suggestion['name'] }}</span>
                                                        <span class="text-xs text-base-content/50 flex items-center gap-1">
                                                            {{ $suggestion['sku'] }}
                                                            @if($suggestion['quantity'] > 0)
                                                                &middot; <span class="text-success font-medium">{{ $suggestion['quantity'] }} {{ $suggestion['unit'] }}</span>
                                                            @else
                                                                &middot; <span class="text-error font-medium">Out of stock</span>
                                                            @endif
                                                        </span>
                                                    </button>
                                                @endforeach
                                            @endif

                                            {{-- Other branches with stock --}}
                                            @if(count($otherSuggestions) > 0)
                                                <div class="px-3 py-1 text-xs font-semibold text-base-content/50 bg-base-200 sticky top-0">
                                                    Available at Other Branches
                                                </div>
                                                @foreach($otherSuggestions as $suggestion)
                                                    <div class="flex items-center justify-between px-3 py-2 border-b border-base-200 last:border-0 hover:bg-warning/10">
                                                        <div>
                                                            <span class="font-medium text-sm">{{ $suggestion['name'] }}</span>
                                                            <span class="text-xs text-base-content/50 ml-1">{{ $suggestion['sku'] }}</span>
                                                            <div class="text-xs text-warning font-medium mt-0.5">
                                                                {{ $suggestion['branch_name'] }} &middot; {{ $suggestion['quantity'] }} {{ $suggestion['unit'] }} in stock
                                                            </div>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            @mousedown.prevent
                                                            wire:click="requestItemFromBranch({{ $index }}, {{ $suggestion['id'] }}, {{ $suggestion['branch_id'] }})"
                                                            class="btn btn-warning btn-xs shrink-0 ml-2"
                                                        >
                                                            Request Transfer
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @elseif(strlen($item['description'] ?? '') >= 2 && empty($item['inventory_item_id'] ?? null))
                                        <div
                                            x-show="open"
                                            class="absolute z-50 top-full left-0 right-0 bg-base-100 border border-base-300 rounded-lg shadow-lg mt-1 px-3 py-2 text-sm text-base-content/55 italic"
                                        >
                                            No inventory match — will appear on quotation for pricing
                                        </div>
                                    @endif
                                </div>

                                {{-- Linked inventory badge --}}
                                @if(!empty($item['inventory_item_id']))
                                    <div class="flex items-center gap-2 mt-1">
                                        @if(!empty($item['source_branch_id']))
                                            <span class="badge badge-warning badge-sm">⇄ Transfer requested</span>
                                        @else
                                            <span class="badge badge-success badge-sm">✓ Linked to inventory</span>
                                        @endif
                                        <button
                                            type="button"
                                            wire:click="clearInventoryLink({{ $index }})"
                                            class="text-xs text-error hover:underline"
                                        >Unlink</button>
                                    </div>
                                @endif

                                @error("items.$index.description")
                                    <span class="text-error text-xs mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Quantity --}}
                            <div class="form-control col-span-6 sm:col-span-3">
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

                        </div>
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
            <button type="button" wire:click="addItem('labor')" class="btn btn-outline btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Labor
            </button>
            <button type="button" wire:click="addItem('part')" class="btn btn-outline btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Part
            </button>
        </div>

        {{-- Pricing notice --}}
        <div class="alert alert-info mt-4 py-2 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Prices will be set during the quotation stage. Just add items and quantities for now.</span>
        </div>
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
