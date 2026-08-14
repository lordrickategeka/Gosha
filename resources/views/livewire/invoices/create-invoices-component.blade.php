<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('invoices.index') }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Create Invoice</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">Generate a new invoice</p>
        </div>
    </div>

    <form wire:submit="save" class="gh-split">
        <div class="gh-stack">
            <!-- Customer -->
            <div class="gh-card gh-card--pad">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <div class="gh-card__title">Customer</div>
                    @unless($work_order_id || $wash_order_id)
                        <button type="button" wire:click="openCustomerModal" class="gh-btn gh-btn--sm">+ New customer</button>
                    @endunless
                </div>
                <div class="gh-field">
                    <select wire:model="customer_id" class="gh-select" style="width:100%;" {{ $work_order_id || $wash_order_id ? 'disabled' : '' }}>
                        <option value="">Select customer…</option>
                        @foreach($this->customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Line Items -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Line Items</div>
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Description</th><th style="width:100px;">Qty</th><th style="width:140px;">Unit price</th><th style="width:140px; text-align:right;">Total</th><th style="width:48px;"></th></tr></thead>
                        <tbody>
                            @foreach($items as $index => $item)
                                <tr>
                                    <td><input type="text" wire:model="items.{{ $index }}.description" class="gh-input" style="width:100%;"></td>
                                    <td><input type="number" wire:model="items.{{ $index }}.quantity" step="0.01" class="gh-input" style="width:100%;"></td>
                                    <td><input type="number" wire:model="items.{{ $index }}.unit_price" class="gh-input" style="width:100%;"></td>
                                    <td class="is-num">UGX {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)) }}</td>
                                    <td><button type="button" wire:click="removeItem({{ $index }})" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">×</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" wire:click="addItem" class="gh-btn gh-btn--sm" style="margin-top:12px;">+ Add item</button>
                @error('items') <div class="gh-hint" style="color:var(--gh-error);">{{ $message }}</div> @enderror
            </div>

            <!-- Notes -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:10px;">Notes</div>
                <textarea wire:model="notes" rows="2" class="gh-input" style="width:100%;" placeholder="Invoice notes…"></textarea>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="gh-stack">
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Invoice Details</div>
                <div class="gh-field" style="margin-bottom:14px;">
                    <span class="gh-label">Due date</span>
                    <input type="date" wire:model="due_date" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field" style="margin-bottom:14px;">
                    <span class="gh-label">Tax rate (%)</span>
                    <input type="number" wire:model.live="tax_rate" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Discount</span>
                    <input type="number" wire:model.live="discount" class="gh-input" style="width:100%;">
                </div>
            </div>

            <div class="gh-card gh-card--pad" style="background:var(--gh-primary); color:var(--gh-primary-content); border-color:var(--gh-primary);">
                <div style="font-weight:700; font-size:15px; margin-bottom:12px;">Summary</div>
                <div class="gh-stack" style="gap:8px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span>Subtotal:</span><span>UGX {{ number_format($this->subtotal) }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span>Tax ({{ $tax_rate }}%):</span><span>UGX {{ number_format($this->tax) }}</span></div>
                    @if($discount > 0)
                        <div style="display:flex; justify-content:space-between;"><span>Discount:</span><span>-UGX {{ number_format($discount) }}</span></div>
                    @endif
                    <div style="border-top:1px solid rgba(255,255,255,.25); padding-top:8px; display:flex; justify-content:space-between; font-size:15px; font-weight:800;"><span>Total:</span><span>UGX {{ number_format($this->total) }}</span></div>
                </div>
                <button type="submit" class="gh-btn gh-btn--dark gh-btn--block" style="margin-top:16px;">Create invoice</button>
            </div>
        </div>
    </form>

    @if($showCustomerModal)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:16px;">Quick Add Customer</div>
                <div class="gh-stack" style="gap:12px;">
                    <div class="gh-field">
                        <span class="gh-label">Full name *</span>
                        <input type="text" wire:model="newCustomerName" placeholder="Enter customer name" class="gh-input" style="width:100%;">
                        @error('newCustomerName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Phone number *</span>
                        <input type="text" wire:model="newCustomerPhone" placeholder="e.g., 0700123456" class="gh-input" style="width:100%;">
                        @error('newCustomerPhone') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="gh-label">Email</span><span class="gh-hint">Optional</span></div>
                        <input type="email" wire:model="newCustomerEmail" placeholder="customer@example.com" class="gh-input" style="width:100%;">
                        @error('newCustomerEmail') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <button type="button" wire:click="closeCustomerModal" class="gh-btn">Cancel</button>
                    <button type="button" wire:click="saveNewCustomer" class="gh-btn gh-btn--primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveNewCustomer">Create customer</span>
                        <span wire:loading wire:target="saveNewCustomer">Creating…</span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeCustomerModal"></div>
        </div>
    @endif
</div>
