{{-- Quick Add Vehicle Modal --}}
@if ($showVehicleModal)
    <div class="modal modal-open">
        <div class="modal-box gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:16px;">Quick Add Vehicle</div>

            <div class="gh-stack" style="gap:12px;">
                <div class="gh-field">
                    <span class="gh-label">Registration number *</span>
                    <input type="text" wire:model="newVehicleRegNumber" placeholder="e.g., UAH 123A" class="gh-input" style="width:100%; text-transform:uppercase;">
                    @error('newVehicleRegNumber') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>

                <div class="gh-grid-2">
                    <div class="gh-field">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <span class="gh-label">Make</span>
                            <span class="gh-hint">Optional</span>
                        </div>
                        <input type="text" wire:model="newVehicleMake" placeholder="e.g., Toyota" class="gh-input" style="width:100%;">
                    </div>
                    <div class="gh-field">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <span class="gh-label">Model</span>
                            <span class="gh-hint">Optional</span>
                        </div>
                        <input type="text" wire:model="newVehicleModel" placeholder="e.g., Corolla" class="gh-input" style="width:100%;">
                    </div>
                </div>

                <div class="gh-field">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <span class="gh-label">Year</span>
                        <span class="gh-hint">Optional</span>
                    </div>
                    <input type="number" wire:model="newVehicleYear" placeholder="e.g., 2020" min="1900" max="{{ date('Y') + 1 }}" class="gh-input" style="width:100%;">
                    @error('newVehicleYear') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                <button type="button" wire:click="closeVehicleModal" class="gh-btn">Cancel</button>
                <button type="button" wire:click="saveNewVehicle" class="gh-btn gh-btn--primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveNewVehicle">Add vehicle</span>
                    <span wire:loading wire:target="saveNewVehicle">Adding…</span>
                </button>
            </div>
        </div>
        <div class="modal-backdrop" wire:click="closeVehicleModal"></div>
    </div>
@endif
