{{-- Quick Add Item Modal --}}
<input type="checkbox" id="item-modal" class="modal-toggle" @if($showItemModal) checked @endif />
<div class="modal" role="dialog">
    <div class="modal-box app-modal-shell">
        <h3 class="font-bold text-lg mb-4">Add Item</h3>

        <div class="space-y-3">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Type *</span>
                </label>
                <select wire:model="newItemType" class="select select-bordered w-full">
                    <option value="labor">Labor</option>
                    <option value="part">Part</option>
                </select>
                @error('newItemType')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Description *</span>
                </label>
                <input
                    type="text"
                    wire:model="newItemDescription"
                    placeholder="Enter description or search inventory"
                    class="input input-bordered w-full"
                />
                @error('newItemDescription')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Quantity *</span>
                </label>
                <input
                    type="number"
                    wire:model="newItemQuantity"
                    step="0.01"
                    min="0.01"
                    class="input input-bordered w-full"
                />
                @error('newItemQuantity')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="modal-action app-modal-actions">
            <button
                type="button"
                wire:click="closeItemModal"
                class="btn btn-ghost"
            >
                Cancel
            </button>
            <button
                type="button"
                wire:click="saveNewItem"
                class="btn btn-primary"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="saveNewItem">Add Item</span>
                <span wire:loading wire:target="saveNewItem" class="loading loading-spinner loading-sm"></span>
            </button>
        </div>
    </div>
    <label class="modal-backdrop app-modal-backdrop" for="item-modal" wire:click="closeItemModal">Close</label>
</div>
