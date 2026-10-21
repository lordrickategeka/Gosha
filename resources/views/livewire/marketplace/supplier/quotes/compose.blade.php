<div class="gh-page" style="max-width:52rem;">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Submit Quote — {{ $rfq->title ?: $rfq->reference }}</div>
        <div class="gh-muted" style="font-size:12.5px; margin-top:2px;">
            {{ $rfq->buyer?->name ?? 'Buyer' }} &middot; {{ $rfq->reference }}
            @if ($rfq->closes_at) &middot; closes {{ $rfq->closes_at->diffForHumans() }} @endif
        </div>
    </div>

    @if ($readonly)
        <div class="gh-badge gh-badge--{{ $rfq->status->value !== 'published' ? 'warning' : 'info' }}" style="display:block; padding:10px 12px; font-size:12px;">
            @if ($quoteId && $rfq->status->value === 'published')
                You already submitted a quote for this RFQ. It can no longer be edited here.
            @else
                This RFQ is no longer accepting quotes.
            @endif
        </div>
    @endif

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Requested</th>
                        <th>Target price</th>
                        <th>Your qty</th>
                        <th>Unit price</th>
                        <th>Tax %</th>
                        <th>Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rfq->items as $item)
                        @php
                            $row = $lines[$item->id];
                            $lineTotal = (int) ($row['qty'] ?? 0) * (float) ($row['unit_price'] ?? 0) * (1 + ((float) ($row['tax_rate'] ?? 0) / 100));
                        @endphp
                        <tr>
                            <td style="font-weight:700;">{{ $item->label() }}</td>
                            <td class="gh-muted">{{ $item->qty }}</td>
                            <td class="gh-muted">{{ $item->target_price ? number_format($item->target_price) : '—' }}</td>
                            <td>
                                <input type="number" min="1" class="gh-input" style="width:80px;"
                                    wire:model.live="lines.{{ $item->id }}.qty" @disabled($readonly)>
                                @error("lines.{$item->id}.qty") <div class="gh-hint" style="color:var(--gh-error);">{{ $message }}</div> @enderror
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" class="gh-input" style="width:110px;"
                                    wire:model.live="lines.{{ $item->id }}.unit_price" @disabled($readonly)>
                                @error("lines.{$item->id}.unit_price") <div class="gh-hint" style="color:var(--gh-error);">{{ $message }}</div> @enderror
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" max="100" class="gh-input" style="width:80px;"
                                    wire:model.live="lines.{{ $item->id }}.tax_rate" @disabled($readonly)>
                            </td>
                            <td class="gh-muted">{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-grid-2">
            <div class="gh-field">
                <span class="gh-label">Valid until</span>
                <input type="date" class="gh-input" style="width:100%;" wire:model="valid_until" @disabled($readonly)>
                @error('valid_until') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
            </div>
            <div class="gh-field" style="grid-column:1/-1;">
                <span class="gh-label">Notes</span>
                <textarea class="gh-input" style="width:100%;" wire:model="notes" @disabled($readonly)></textarea>
            </div>
        </div>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:15px; font-weight:700;">
            Total: {{ number_format($this->total, 2) }} {{ config('marketplace.default_currency') }}
        </div>

        @unless ($readonly)
            <div style="display:flex; gap:8px;">
                <a class="gh-btn" href="{{ route('supplier.quotes.inbox') }}">Cancel</a>
                <button class="gh-btn" wire:click="saveDraft">Save draft</button>
                <button class="gh-btn gh-btn--primary" wire:click="submit">Submit quote</button>
            </div>
        @endunless
    </div>
</div>
