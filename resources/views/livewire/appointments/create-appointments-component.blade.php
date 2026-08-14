<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('appointments.index') }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">New Appointment</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Schedule a service appointment</p>
        </div>
    </div>

    <form wire:submit="save" style="max-width:42rem;">
        <div class="gh-stack">
            <livewire:customer-vehicle-selector :key="'customer-selector'" />

            <div class="gh-card gh-card--pad">
                <div class="gh-grid-2">
                    <div class="gh-field">
                        <span class="gh-label">Service type *</span>
                        <select wire:model="type" class="gh-select" style="width:100%;">
                            <option value="service">General Service</option>
                            <option value="wash">Wash</option>
                            <option value="combo">Combo (Service + Wash)</option>
                            <option value="diagnostics">Diagnostics</option>
                            <option value="estimate">Estimate</option>
                        </select>
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Est. duration (mins)</span>
                        <select wire:model="duration_minutes" class="gh-select" style="width:100%;">
                            <option value="30">30 minutes</option>
                            <option value="60">1 hour</option>
                            <option value="120">2 hours</option>
                            <option value="180">3 hours</option>
                            <option value="240">4 hours</option>
                            <option value="480">Full day</option>
                        </select>
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Date *</span>
                        <input type="date" wire:model="scheduled_date" class="gh-input" style="width:100%;" min="{{ now()->format('Y-m-d') }}">
                        @error('scheduled_date') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Time *</span>
                        <input type="time" wire:model="scheduled_time" class="gh-input" style="width:100%;">
                    </div>
                </div>

                <div class="gh-field" style="margin-top:14px;">
                    <span class="gh-label">Notes</span>
                    <textarea wire:model="notes" rows="2" class="gh-input" style="width:100%;" placeholder="Any special requests or notes..."></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <a href="{{ route('appointments.index') }}" class="gh-btn">Cancel</a>
                    <button type="submit" class="gh-btn gh-btn--primary">Schedule appointment</button>
                </div>
            </div>
        </div>
    </form>
</div>
