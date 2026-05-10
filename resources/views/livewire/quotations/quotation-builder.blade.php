<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">
                    {{ $quotation ? 'Edit Quotation ' . $quotation->quotation_number : 'New Quotation' }}
                </h1>
                <p class="text-base-content/60 text-sm">
                    Work Order: <span class="font-mono">{{ $workOrder->order_number }}</span>
                    &mdash; {{ $workOrder->customer->name }} &mdash; {{ $workOrder->vehicle->registration_number }}
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

        {{-- ── Line Items ──────────────────────────────────────────────────── --}}
        <div class="xl:col-span-3 space-y-4">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title text-lg">Line Items</h2>
                        <div class="flex gap-2">
                            <button wire:click="addItem('labor')" class="btn btn-outline btn-sm">+ Labor</button>
                            <button wire:click="addItem('part')" class="btn btn-outline btn-sm">+ Part</button>
                        </div>
                    </div>

                    @error('items') <p class="text-error text-sm mb-3">{{ $message }}</p> @enderror

                    <div class="space-y-3">
                        @forelse($items as $index => $item)
                            <div class="border border-base-300 rounded-lg p-4 bg-base-50">
                                <div class="grid grid-cols-12 gap-3">

                                    {{-- Type --}}
                                    <div class="col-span-12 sm:col-span-2">
                                        <label class="label label-text text-xs pb-1">Type</label>
                                        <select wire:model="items.{{ $index }}.item_type" class="select select-bordered select-sm w-full">
                                            <option value="labor">Labor</option>
                                            <option value="part">Part</option>
                                        </select>
                                    </div>

                                    {{-- Description with live search --}}
                                    <div class="col-span-12 sm:col-span-4 relative">
                                        <label class="label label-text text-xs pb-1">Description</label>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.300ms="items.{{ $index }}.description"
                                            placeholder="Search inventory or type description..."
                                            class="input input-bordered input-sm w-full"
                                        />
                                        @error("items.{$index}.description") <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror

                                        {{-- Linked badge --}}
                                        @if(!empty($item['inventory_item_id']))
                                            <div class="flex items-center gap-1 mt-1">
                                                <span class="badge badge-success badge-sm">Linked to inventory</span>
                                                <button wire:click="clearInventoryLink({{ $index }})" class="text-error text-xs hover:underline">&times; unlink</button>
                                            </div>
                                        @endif

                                        {{-- Suggestions dropdown --}}
                                        @if(!empty($itemSuggestions[$index]['my_branch']) || !empty($itemSuggestions[$index]['other_branches']))
                                            <div class="absolute z-50 left-0 right-0 mt-1 bg-base-100 border border-base-300 rounded-lg shadow-xl max-h-72 overflow-y-auto">
                                                @if(!empty($itemSuggestions[$index]['my_branch']))
                                                    <div class="px-3 py-1 text-xs font-semibold text-base-content/50 bg-base-200">This Branch</div>
                                                    @foreach($itemSuggestions[$index]['my_branch'] as $suggestion)
                                                        <button wire:click="selectInventoryItem({{ $index }}, {{ $suggestion['id'] }})"
                                                            class="w-full text-left px-3 py-2 hover:bg-base-200 flex justify-between items-center gap-2">
                                                            <div>
                                                                <span class="font-medium text-sm">{{ $suggestion['name'] }}</span>
                                                                @if($suggestion['sku'])
                                                                    <span class="text-xs text-base-content/50 ml-1">({{ $suggestion['sku'] }})</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-right shrink-0">
                                                                <div class="text-xs text-base-content/60">Qty: {{ $suggestion['quantity'] }}</div>
                                                                <div class="text-xs font-medium">UGX {{ number_format($suggestion['price']) }}</div>
                                                            </div>
                                                        </button>
                                                    @endforeach
                                                @endif
                                                @if(!empty($itemSuggestions[$index]['other_branches']))
                                                    <div class="px-3 py-1 text-xs font-semibold text-base-content/50 bg-base-200">Other Branches</div>
                                                    @foreach($itemSuggestions[$index]['other_branches'] as $suggestion)
                                                        <button wire:click="selectInventoryItem({{ $index }}, {{ $suggestion['id'] }})"
                                                            class="w-full text-left px-3 py-2 hover:bg-base-200 flex justify-between items-center gap-2 opacity-70">
                                                            <div>
                                                                <span class="font-medium text-sm">{{ $suggestion['name'] }}</span>
                                                                <span class="badge badge-xs badge-warning ml-1">{{ $suggestion['branch_name'] }}</span>
                                                            </div>
                                                            <div class="text-right shrink-0">
                                                                <div class="text-xs text-base-content/60">Qty: {{ $suggestion['quantity'] }}</div>
                                                            </div>
                                                        </button>
                                                    @endforeach
                                                @endif

                                                {{-- Add to inventory option --}}
                                                @if($item['item_type'] === 'part' && empty($item['inventory_item_id']) && strlen($item['description']) >= 2)
                                                    <div class="border-t border-base-300 px-3 py-2">
                                                        <button wire:click="promptAddToInventory({{ $index }})"
                                                            class="text-sm text-primary hover:underline">
                                                            + Add "{{ $item['description'] }}" to inventory
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($item['item_type'] === 'part' && empty($item['inventory_item_id']) && strlen($item['description'] ?? '') >= 2)
                                            <div class="mt-1">
                                                <button wire:click="promptAddToInventory({{ $index }})"
                                                    class="text-xs text-primary hover:underline">
                                                    + Add to inventory
                                                </button>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Supplier --}}
                                    <div class="col-span-12 sm:col-span-2">
                                        <label class="label label-text text-xs pb-1">Supplier</label>
                                        <select wire:model="items.{{ $index }}.supplier_id" class="select select-bordered select-sm w-full">
                                            <option value="">— none —</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Qty --}}
                                    <div class="col-span-4 sm:col-span-1">
                                        <label class="label label-text text-xs pb-1">Qty</label>
                                        <input type="number" wire:model="items.{{ $index }}.quantity"
                                            min="0.01" step="0.01"
                                            class="input input-bordered input-sm w-full" />
                                        @error("items.{$index}.quantity") <p class="text-error text-xs">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Unit Price --}}
                                    <div class="col-span-4 sm:col-span-1">
                                        <label class="label label-text text-xs pb-1">Unit Price</label>
                                        <input type="number" wire:model="items.{{ $index }}.unit_price"
                                            min="0" step="1"
                                            class="input input-bordered input-sm w-full" />
                                        @error("items.{$index}.unit_price") <p class="text-error text-xs">{{ $message }}</p> @enderror
                                    </div>

                                    {{-- Discount --}}
                                    <div class="col-span-4 sm:col-span-1">
                                        <label class="label label-text text-xs pb-1">Discount</label>
                                        <input type="number" wire:model="items.{{ $index }}.discount"
                                            min="0" step="1"
                                            class="input input-bordered input-sm w-full" />
                                    </div>

                                    {{-- VAT + Remove --}}
                                    <div class="col-span-12 sm:col-span-1 flex flex-col justify-end gap-2">
                                        <label class="label cursor-pointer gap-2 pb-0">
                                            <span class="label-text text-xs">VAT</span>
                                            <input type="checkbox" wire:model="items.{{ $index }}.vat_applicable"
                                                class="checkbox checkbox-sm checkbox-primary" />
                                        </label>
                                        <button wire:click="removeItem({{ $index }})"
                                            class="btn btn-ghost btn-xs text-error">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Row total --}}
                                <div class="flex justify-end mt-2 text-sm text-base-content/60">
                                    Line total: <span class="font-medium ml-1">UGX {{ number_format(max(0, ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0))) }}</span>
                                    @if(($item['vat_applicable'] ?? false) && $vatRate > 0)
                                        <span class="ml-2 text-warning">+VAT {{ $vatRate }}%</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-base-content/40">
                                <p>No items yet. Click "+ Labor" or "+ Part" to add a line.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Add row buttons --}}
                    @if(count($items))
                        <div class="flex gap-2 mt-4">
                            <button wire:click="addItem('labor')" class="btn btn-outline btn-sm">+ Add Labor</button>
                            <button wire:click="addItem('part')" class="btn btn-outline btn-sm">+ Add Part</button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notes & Terms --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Notes & Terms</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label"><span class="label-text">Internal / Customer Notes</span></label>
                            <textarea wire:model="notes" rows="4" class="textarea textarea-bordered w-full"
                                placeholder="Any notes for the customer..."></textarea>
                        </div>
                        <div>
                            <label class="label"><span class="label-text">Terms & Conditions</span></label>
                            <textarea wire:model="termsAndConditions" rows="4" class="textarea textarea-bordered w-full"
                                placeholder="Payment terms, warranty, etc."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Sidebar: Totals + Settings ──────────────────────────────────── --}}
        <div class="xl:col-span-1 space-y-4">

            {{-- Totals --}}
            <div class="card bg-base-100 shadow-sm sticky top-6">
                <div class="card-body">
                    <h2 class="card-title text-lg">Summary</h2>

                    <div class="space-y-2 mt-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">Subtotal</span>
                            <span class="font-medium">UGX {{ number_format($this->subtotal) }}</span>
                        </div>
                        @if($vatRate > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-base-content/60">VAT ({{ $vatRate }}%)</span>
                                <span class="font-medium">UGX {{ number_format($this->vatAmount) }}</span>
                            </div>
                        @endif
                        <div class="divider my-1"></div>
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span>UGX {{ number_format($this->total) }}</span>
                        </div>
                    </div>

                    {{-- VAT Rate --}}
                    <div class="mt-4">
                        <label class="label"><span class="label-text text-sm">VAT Rate (%)</span></label>
                        <input type="number" wire:model.live="vatRate" min="0" max="100" step="0.5"
                            class="input input-bordered input-sm w-full" placeholder="0" />
                        <p class="text-xs text-base-content/50 mt-1">Applied to lines with VAT toggled on.</p>
                    </div>

                    {{-- Valid Until --}}
                    <div class="mt-4">
                        <label class="label"><span class="label-text text-sm">Valid Until</span></label>
                        <input type="date" wire:model="validUntil" class="input input-bordered input-sm w-full" />
                        @error('validUntil') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="mt-6 space-y-2">
                        @can('send_quotations')
                            <button wire:click="saveAndSend" class="btn btn-primary w-full"
                                wire:loading.attr="disabled" wire:target="saveAndSend">
                                <span wire:loading.remove wire:target="saveAndSend">Save & Send to Customer</span>
                                <span wire:loading wire:target="saveAndSend" class="loading loading-spinner loading-sm"></span>
                            </button>
                        @endcan
                        @can('create_quotations')
                            <button wire:click="saveDraft" class="btn btn-outline w-full"
                                wire:loading.attr="disabled" wire:target="saveDraft">
                                <span wire:loading.remove wire:target="saveDraft">Save as Draft</span>
                                <span wire:loading wire:target="saveDraft" class="loading loading-spinner loading-sm"></span>
                            </button>
                        @endcan

                        @if($quotation && $quotation->isSent())
                            @can('edit_quotations')
                                <button wire:click="createRevision" class="btn btn-ghost btn-sm w-full">
                                    Create Revision (v{{ $quotation->version + 1 }})
                                </button>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ═══ Add-to-Inventory Modal ═══════════════════════════════════════ --}}
    @if($showAddToInventoryModal)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box max-w-md">
                <button wire:click="closeAddToInventoryModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                <h3 class="font-bold text-lg mb-4">Add to Inventory</h3>

                <div class="space-y-3">
                    <div>
                        <label class="label"><span class="label-text">Item Name *</span></label>
                        <input type="text" wire:model="newItemName" class="input input-bordered w-full" />
                        @error('newItemName') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label"><span class="label-text">SKU</span></label>
                            <input type="text" wire:model="newItemSku" class="input input-bordered input-sm w-full" />
                        </div>
                        <div>
                            <label class="label"><span class="label-text">Unit *</span></label>
                            <input type="text" wire:model="newItemUnit" class="input input-bordered input-sm w-full" placeholder="pcs, litre, kg..." />
                            @error('newItemUnit') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="label"><span class="label-text">Category</span></label>
                        <select wire:model="newItemCategoryId" class="select select-bordered w-full">
                            <option value="">— select —</option>
                            @foreach($this->inventoryCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label"><span class="label-text">Supplier</span></label>
                        <select wire:model="newItemSupplierId" class="select select-bordered w-full">
                            <option value="">— none —</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup['id'] }}">{{ $sup['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label"><span class="label-text">Cost Price</span></label>
                            <input type="number" wire:model="newItemCostPrice" min="0" step="1" class="input input-bordered input-sm w-full" />
                            @error('newItemCostPrice') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label"><span class="label-text">Selling Price</span></label>
                            <input type="number" wire:model="newItemSellingPrice" min="0" step="1" class="input input-bordered input-sm w-full" />
                            @error('newItemSellingPrice') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-action">
                    <button wire:click="closeAddToInventoryModal" class="btn btn-ghost">Cancel</button>
                    <button wire:click="saveAddToInventoryItem" class="btn btn-primary"
                        wire:loading.attr="disabled" wire:target="saveAddToInventoryItem">
                        <span wire:loading.remove wire:target="saveAddToInventoryItem">Save & Link</span>
                        <span wire:loading wire:target="saveAddToInventoryItem" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop bg-black/50" wire:click="closeAddToInventoryModal"></div>
        </div>
    @endif
</div>
