<div class="gh-stack">
    <div class="gh-card gh-card--pad">
        <p style="font-weight:600; font-size:13px;">Final Review</p>
        <p class="gh-muted" style="font-size:11.5px; margin-top:4px;">Confirm all captured details before creating the work order.</p>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-card__title" style="margin-bottom:14px;">Customer &amp; Vehicle</div>
        <div class="gh-grid-2">
            <div>
                <p class="gh-eyebrow" style="margin-bottom:4px;">Customer</p>
                @if($this->selectedCustomer)
                    <p style="font-weight:600; font-size:13px;">{{ $this->selectedCustomer->name }}</p>
                    <p class="gh-muted" style="font-size:12px;">{{ $this->selectedCustomer->phone }}</p>
                @else
                    <p class="gh-muted">—</p>
                @endif
            </div>
            <div>
                <p class="gh-eyebrow" style="margin-bottom:4px;">Vehicle</p>
                @if($this->selectedVehicle)
                    <p style="font-weight:600; font-size:13px;">{{ $this->selectedVehicle->registration_number }}</p>
                    <p class="gh-muted" style="font-size:12px;">{{ $this->selectedVehicle->make }} {{ $this->selectedVehicle->model }} @if($this->selectedVehicle->year) ({{ $this->selectedVehicle->year }}) @endif</p>
                @else
                    <p class="gh-muted">—</p>
                @endif
            </div>
            @if($customer_notes)
                <div style="grid-column:1/-1;">
                    <p class="gh-eyebrow" style="margin-bottom:4px;">Customer notes</p>
                    <p style="font-size:12.5px;">{{ $customer_notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-card__title" style="margin-bottom:14px;">Service Details</div>
        <div class="gh-grid-3">
            <div>
                <p class="gh-eyebrow" style="margin-bottom:4px;">Service type</p>
                <p style="font-weight:600; font-size:13px; text-transform:capitalize;">{{ $type }}</p>
            </div>
            <div>
                <p class="gh-eyebrow" style="margin-bottom:4px;">Priority</p>
                <span class="gh-badge {{ match($priority) { 'high','urgent' => 'gh-badge--'.($priority === 'urgent' ? 'error' : 'warning'), 'normal' => 'gh-badge--info', default => '' } }}" style="text-transform:capitalize;">{{ $priority }}</span>
            </div>
            @if($mileage_in)
                <div><p class="gh-eyebrow" style="margin-bottom:4px;">Mileage in</p><p style="font-weight:600; font-size:13px;">{{ number_format($mileage_in) }} km</p></div>
            @endif
            @if($service_bay_id)
                <div><p class="gh-eyebrow" style="margin-bottom:4px;">Service bay</p><p style="font-weight:600; font-size:13px;">{{ $this->serviceBays->firstWhere('id', $service_bay_id)?->name ?? 'N/A' }}</p></div>
            @endif
            @if($assigned_technician_id)
                <div><p class="gh-eyebrow" style="margin-bottom:4px;">Technician</p><p style="font-weight:600; font-size:13px;">{{ $this->technicians->firstWhere('id', $assigned_technician_id)?->name ?? 'N/A' }}</p></div>
            @endif
            @if($estimated_completion)
                <div><p class="gh-eyebrow" style="margin-bottom:4px;">Est. completion</p><p style="font-weight:600; font-size:13px;">{{ \Carbon\Carbon::parse($estimated_completion)->format('M j, g:i A') }}</p></div>
            @endif
            @if($is_combo)
                <div style="grid-column:1/-1;"><span class="gh-badge gh-badge--primary">✓ Combo service (will auto-queue for wash)</span></div>
            @endif
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-card__title" style="margin-bottom:14px;">Items ({{ count($items) }})</div>
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th>Type</th><th>Description</th><th style="text-align:right;">Qty</th>
                        @if(!$this->isJobcarder())
                            <th style="text-align:right;">Price</th><th style="text-align:right;">Total</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td><span class="gh-badge {{ $item['item_type'] === 'labor' ? 'gh-badge--info' : 'gh-badge--primary' }}">{{ ucfirst($item['item_type']) }}</span></td>
                            <td>{{ $item['description'] }}</td>
                            <td class="is-num">{{ $item['quantity'] }}</td>
                            @if(!$this->isJobcarder())
                                <td class="is-num">UGX {{ number_format($item['unit_price'] ?? 0) }}</td>
                                <td class="is-num">UGX {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)) }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                @if(!$this->isJobcarder())
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align:right; font-weight:700; padding:10px 18px;">Subtotal:</td>
                            <td class="is-num">UGX {{ number_format($this->subtotal) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if(count($vehicle_left_items) > 0)
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Items left in vehicle ({{ count($vehicle_left_items) }})</div>
            <div class="gh-table-scroll">
                <table class="gh-table">
                    <thead><tr><th>Item</th><th>Qty</th><th>Reference</th><th>Description</th></tr></thead>
                    <tbody>
                        @foreach($vehicle_left_items as $leftItem)
                            <tr>
                                <td>{{ $leftItem['item_name'] }}</td>
                                <td>{{ $leftItem['quantity'] }}</td>
                                <td>{{ $leftItem['reference'] ?: '—' }}</td>
                                <td>{{ $leftItem['description'] ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="gh-card gh-card--pad">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <p style="font-weight:600; font-size:13px;">Ready to create this work order?</p>
                <p class="gh-muted" style="font-size:12px;">A unique order number will be generated automatically.</p>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="button" wire:click="previousStep" class="gh-btn">← Back</button>
                <button type="button" wire:click="save" class="gh-btn gh-btn--primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Create work order</span>
                    <span wire:loading wire:target="save">Creating…</span>
                </button>
            </div>
        </div>
    </div>
</div>
