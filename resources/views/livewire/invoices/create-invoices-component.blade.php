<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('invoices.index') }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Create Invoice</h1>
            <p class="text-base-content/60">Generate a new invoice</p>
        </div>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <h2 class="card-title text-lg">Customer</h2>
                        @unless($work_order_id || $wash_order_id)
                            <button type="button" wire:click="openCustomerModal" class="btn btn-outline btn-sm">+ New Customer</button>
                        @endunless
                    </div>
                    <div class="form-control">
                        <select wire:model="customer_id" class="select select-bordered w-full" {{ $work_order_id || $wash_order_id ? 'disabled' : '' }}>
                            <option value="">Select customer...</option>
                            @foreach($this->customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Line Items</h2>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="w-24">Qty</th>
                                    <th class="w-32">Unit Price</th>
                                    <th class="w-32 text-right">Total</th>
                                    <th class="w-12"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                    <tr>
                                        <td><input type="text" wire:model="items.{{ $index }}.description" class="input input-bordered input-sm w-full" /></td>
                                        <td><input type="number" wire:model="items.{{ $index }}.quantity" step="0.01" class="input input-bordered input-sm w-full" /></td>
                                        <td><input type="number" wire:model="items.{{ $index }}.unit_price" class="input input-bordered input-sm w-full" /></td>
                                        <td class="text-right font-medium">UGX {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)) }}</td>
                                        <td><button type="button" wire:click="removeItem({{ $index }})" class="btn btn-ghost btn-xs text-error">×</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" wire:click="addItem" class="btn btn-outline btn-sm mt-2">+ Add Item</button>
                    @error('items') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Notes -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Notes</h2>
                    <textarea wire:model="notes" rows="2" class="textarea textarea-bordered w-full" placeholder="Invoice notes..."></textarea>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Invoice Details</h2>
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Due Date</span></label>
                        <input type="date" wire:model="due_date" class="input input-bordered" />
                    </div>
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Tax Rate (%)</span></label>
                        <input type="number" wire:model.live="tax_rate" class="input input-bordered" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Discount</span></label>
                        <input type="number" wire:model.live="discount" class="input input-bordered" />
                    </div>
                </div>
            </div>

            <div class="card bg-primary text-primary-content shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Summary</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span>Subtotal:</span><span>UGX {{ number_format($this->subtotal) }}</span></div>
                        <div class="flex justify-between"><span>Tax ({{ $tax_rate }}%):</span><span>UGX {{ number_format($this->tax) }}</span></div>
                        @if($discount > 0)
                            <div class="flex justify-between"><span>Discount:</span><span>-UGX {{ number_format($discount) }}</span></div>
                        @endif
                        <div class="divider my-1"></div>
                        <div class="flex justify-between text-lg font-bold"><span>Total:</span><span>UGX {{ number_format($this->total) }}</span></div>
                    </div>
                    <button type="submit" class="btn btn-accent w-full mt-4">Create Invoice</button>
                </div>
            </div>
        </div>
    </form>

    @if($showCustomerModal)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box app-modal-shell">
                <h3 class="font-bold text-lg mb-4">Quick Add Customer</h3>

                <div class="space-y-3">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Full Name *</span>
                        </label>
                        <input type="text" wire:model="newCustomerName" placeholder="Enter customer name" class="input input-bordered w-full" />
                        @error('newCustomerName') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Phone Number *</span>
                        </label>
                        <input type="text" wire:model="newCustomerPhone" placeholder="e.g., 0700123456" class="input input-bordered w-full" />
                        @error('newCustomerPhone') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Email</span>
                            <span class="label-text-alt text-base-content/50">Optional</span>
                        </label>
                        <input type="email" wire:model="newCustomerEmail" placeholder="customer@example.com" class="input input-bordered w-full" />
                        @error('newCustomerEmail') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="modal-action app-modal-actions">
                    <button type="button" wire:click="closeCustomerModal" class="btn btn-ghost">Cancel</button>
                    <button type="button" wire:click="saveNewCustomer" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveNewCustomer">Create Customer</span>
                        <span wire:loading wire:target="saveNewCustomer" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop app-modal-backdrop" wire:click="closeCustomerModal"></div>
        </div>
    @endif
</div>
