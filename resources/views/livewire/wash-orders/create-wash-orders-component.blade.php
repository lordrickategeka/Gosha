<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('wash-orders.index') }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">New Wash Order</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">Add a vehicle to the wash queue</p>
        </div>
    </div>

    <form wire:submit="save">
        <div class="gh-split">
            <div class="gh-stack">
                <livewire:customer-vehicle-selector :key="'customer-selector'" />
                @error('customer_id') <p class="gh-hint" style="color:var(--gh-error);">{{ $message }}</p> @enderror
                @error('vehicle_id') <p class="gh-hint" style="color:var(--gh-error);">{{ $message }}</p> @enderror

                <!-- Wash Items -->
                <div class="gh-card gh-card--flush">
                    <div class="gh-card__head">
                        <span class="gh-card__title">Wash Items</span>
                        <button type="button" wire:click="addItem" class="gh-btn gh-btn--sm">+ Add item</button>
                    </div>
                    <div class="gh-table-scroll">
                        <table class="gh-table">
                            <thead><tr><th>Description</th><th style="width:150px; text-align:right;">Price (UGX)</th><th style="width:48px;"></th></tr></thead>
                            <tbody>
                                @forelse($items as $index => $item)
                                    <tr>
                                        <td><input type="text" wire:model="items.{{ $index }}.description" placeholder="e.g. Exterior wash, Interior vacuum…" class="gh-input" style="width:100%;"></td>
                                        <td><input type="number" wire:model="items.{{ $index }}.price" min="0" step="500" class="gh-input" style="width:100%; text-align:right;"></td>
                                        <td><button type="button" wire:click="removeItem({{ $index }})" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">✕</button></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" style="text-align:center; padding:24px; color:var(--gh-ink-faint);">No items yet — select a package above or add items manually.</td></tr>
                                @endforelse
                            </tbody>
                            @if(count($items) > 0)
                                <tfoot>
                                    <tr><td style="text-align:right; font-weight:600; padding:10px 18px;">Total:</td><td class="is-num">UGX {{ number_format($this->total) }}</td><td></td></tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Notes -->
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:10px;">Customer Notes</div>
                    <textarea wire:model="customer_notes" rows="3" placeholder="Any special instructions, paint condition notes, specific requests…" class="gh-input" style="width:100%;"></textarea>
                </div>
            </div>

            <div class="gh-stack">
                <!-- Wash Details -->
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:14px;">Wash Details</div>

                    <div class="gh-field" style="margin-bottom:14px;">
                        <div style="display:flex; align-items:center; justify-content:space-between;"><span class="gh-label">Package</span><span class="gh-hint">optional</span></div>
                        <select wire:model.live="wash_package_id" class="gh-select" style="width:100%;">
                            <option value="">— No package —</option>
                            @foreach($this->packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }} @if($package->price) (UGX {{ number_format($package->price) }}) @endif</option>
                            @endforeach
                        </select>
                        <span class="gh-hint">Selecting a package pre-fills the items.</span>
                    </div>

                    <div class="gh-field" style="margin-bottom:14px;">
                        <span class="gh-label">Wash type *</span>
                        <select wire:model="wash_type" class="gh-select" style="width:100%;">
                            <option value="basic">Basic</option>
                            <option value="standard">Standard</option>
                            <option value="premium">Premium</option>
                            <option value="interior">Interior</option>
                            <option value="exterior">Exterior</option>
                            <option value="engine">Engine</option>
                            <option value="full_detail">Full Detail</option>
                            <option value="custom">Custom</option>
                        </select>
                        @error('wash_type') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field" style="margin-bottom:14px;">
                        <span class="gh-label">Priority</span>
                        <div style="display:flex; gap:8px;">
                            <button type="button" wire:click="$set('priority', 'normal')" class="gh-btn gh-btn--block {{ $priority === 'normal' ? 'gh-btn--primary' : '' }}">Normal</button>
                            <button type="button" wire:click="$set('priority', 'priority')" class="gh-btn gh-btn--block {{ $priority === 'priority' ? 'gh-btn--primary' : '' }}">Priority</button>
                        </div>
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Source</span>
                        <select wire:model="source" class="gh-select" style="width:100%;">
                            <option value="walk_in">Walk-in</option>
                            <option value="appointment">Appointment</option>
                            <option value="combo">Combo (with Work Order)</option>
                        </select>
                    </div>
                </div>

                <!-- Queue Position -->
                <div class="gh-card gh-card--pad">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="gh-module-card__icon gh-module-card__icon--info"><x-icon name="washBay" class="gh-icon" /></div>
                        <div>
                            <p style="font-weight:600; font-size:12.5px;">Will be added to queue</p>
                            <p class="gh-muted" style="font-size:11px;">Queue position assigned automatically</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="gh-btn gh-btn--primary gh-btn--block" wire:loading.attr="disabled">
                    <span wire:loading.remove>+ Add to queue</span>
                    <span wire:loading>Saving…</span>
                </button>

                <a href="{{ route('wash-orders.index') }}" class="gh-btn gh-btn--block">Cancel</a>
            </div>
        </div>
    </form>
</div>
