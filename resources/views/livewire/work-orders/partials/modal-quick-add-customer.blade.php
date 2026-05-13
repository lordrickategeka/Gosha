{{-- Quick Add Customer Modal --}}
<input type="checkbox" id="customer-modal" class="modal-toggle" @if($showCustomerModal) checked @endif />
<div class="modal" role="dialog">
    <div class="modal-box app-modal-shell">
        <h3 class="font-bold text-lg mb-4">Quick Add Customer</h3>

        <div class="space-y-3">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Full Name *</span>
                </label>
                <input
                    type="text"
                    wire:model="newCustomerName"
                    placeholder="Enter customer name"
                    class="input input-bordered w-full"
                />
                @error('newCustomerName')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Phone Number *</span>
                </label>
                <input
                    type="text"
                    wire:model="newCustomerPhone"
                    placeholder="e.g., 0700123456"
                    class="input input-bordered w-full"
                />
                @error('newCustomerPhone')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Email</span>
                    <span class="label-text-alt text-base-content/50">Optional</span>
                </label>
                <input
                    type="email"
                    wire:model="newCustomerEmail"
                    placeholder="customer@example.com"
                    class="input input-bordered w-full"
                />
                @error('newCustomerEmail')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="modal-action app-modal-actions">
            <button
                type="button"
                wire:click="closeCustomerModal"
                class="btn btn-ghost"
            >
                Cancel
            </button>
            <button
                type="button"
                wire:click="saveNewCustomer"
                class="btn btn-primary"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="saveNewCustomer">Create Customer</span>
                <span wire:loading wire:target="saveNewCustomer" class="loading loading-spinner loading-sm"></span>
            </button>
        </div>
    </div>
    <label class="modal-backdrop app-modal-backdrop" for="customer-modal" wire:click="closeCustomerModal">Close</label>
</div>
