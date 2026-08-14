<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('customers.show', $customer) }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Edit Customer</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">{{ $customer->name }}</p>
        </div>
    </div>

    <form wire:submit="save" style="max-width:640px;">
        <div class="gh-card gh-card--pad">
            <div class="gh-grid-2">
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Full name *</span>
                    <input type="text" wire:model="name" class="gh-input" style="width:100%;">
                    @error('name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Phone *</span>
                    <input type="text" wire:model="phone" class="gh-input" style="width:100%;">
                    @error('phone') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Email</span>
                    <input type="email" wire:model="email" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Company</span>
                    <input type="text" wire:model="company" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Tax ID</span>
                    <input type="text" wire:model="tax_id" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Address</span>
                    <input type="text" wire:model="address" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Notes</span>
                    <textarea wire:model="notes" rows="2" class="gh-input" style="width:100%;"></textarea>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:16px; margin-top:20px;">
                <a href="{{ route('customers.show', $customer) }}" class="gh-btn">Cancel</a>
                <button type="submit" class="gh-btn gh-btn--primary">Save changes</button>
            </div>
        </div>
    </form>
</div>
