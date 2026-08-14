{{-- Quick Add Customer Modal --}}
@if ($showCustomerModal)
    <div class="modal modal-open">
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
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <span class="gh-label">Email</span>
                        <span class="gh-hint">Optional</span>
                    </div>
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
