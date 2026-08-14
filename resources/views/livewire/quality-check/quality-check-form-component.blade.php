<div class="gh-page">
    <div class="gh-card gh-card--pad">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:14px;">
            <div>
                <div style="font-size:19px; font-weight:700; letter-spacing:-0.02em;">Vehicle Quality Check</div>
                <p class="gh-muted" style="font-size:12px; margin-top:2px;">Work Order: {{ $workOrder->order_number }}</p>
            </div>
            <span class="gh-badge {{ $qualityCheck->status === 'passed' ? 'gh-badge--success' : ($qualityCheck->status === 'has_issues' ? 'gh-badge--warning' : 'gh-badge--info') }}">
                {{ strtoupper(str_replace('_', ' ', $qualityCheck->status)) }}
            </span>
        </div>

        <div class="gh-grid-3" style="font-size:12.5px;">
            <div><span style="font-weight:700;">Customer:</span> {{ $workOrder->customer->name }}</div>
            <div><span style="font-weight:700;">Vehicle:</span> {{ $workOrder->vehicle->model }} ({{ $workOrder->vehicle->registration_number }})</div>
            <div><span style="font-weight:700;">Mileage:</span> {{ number_format($workOrder->vehicle->current_mileage) }} km</div>
            <div><span style="font-weight:700;">VIN:</span> {{ $workOrder->vehicle->vin_number ?? 'N/A' }}</div>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="font-weight:700;">Inspection Date:</span>
                <input type="date" wire:model="inspectionDate" class="gh-input" style="padding:5px 8px;">
            </div>
            <div><span style="font-weight:700;">Inspector:</span> {{ auth()->user()->name }}</div>
        </div>
    </div>

    <form wire:submit.prevent="submit">
        @include('livewire.quality-check.partials._checklist-items')

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:12px;">F. General Notes / Observations</div>
            <textarea wire:model="generalNotes" rows="5" placeholder="Add any general observations, recommendations, or notes about the vehicle condition..." class="gh-input" style="width:100%;"></textarea>
        </div>

        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <a href="{{ route('work-orders.show', $workOrder) }}" class="gh-btn">Cancel</a>
            <button type="button" wire:click="saveAsDraft" class="gh-btn">Save draft</button>
            <button type="submit" class="gh-btn gh-btn--primary">Submit quality check</button>
        </div>
    </form>
</div>
