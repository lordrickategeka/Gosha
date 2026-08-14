<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('work-orders.show', $quotation->workOrder) }}" class="gh-btn gh-btn--sm">←</a>
            <div>
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span style="font-size:20px; font-weight:700;">{{ $quotation->quotation_number }}</span>
                    <span class="gh-badge {{ $quotation->status_color !== 'ghost' ? 'gh-badge--'.($quotation->status_color === 'accent' ? 'primary' : $quotation->status_color) : '' }}">{{ ucfirst($quotation->status) }}</span>
                    @if($quotation->version > 1)
                        <span class="gh-badge">v{{ $quotation->version }}</span>
                    @endif
                </div>
                <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">{{ $quotation->customer->name }} — {{ $quotation->workOrder->vehicle->registration_number }}</p>
            </div>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            @if($quotation->isDraft())
                @can('edit_quotations')
                    <a href="{{ route('quotations.edit', $quotation) }}" class="gh-btn gh-btn--sm">Edit</a>
                @endcan
                @can('send_quotations')
                    <button wire:click="sendToCustomer" class="gh-btn gh-btn--primary gh-btn--sm" wire:loading.attr="disabled" wire:target="sendToCustomer">
                        <span wire:loading.remove wire:target="sendToCustomer">Send to customer</span>
                        <span wire:loading wire:target="sendToCustomer">Sending…</span>
                    </button>
                @endcan
            @endif

            @if($quotation->canBeApproved())
                @can('approve_quotations')
                    <button wire:click="approveOnBehalf" class="gh-btn gh-btn--sm" style="color:var(--gh-success);" wire:loading.attr="disabled" wire:target="approveOnBehalf">Approve</button>
                    <button wire:click="openRejectModal" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">Reject</button>
                @endcan
            @endif

            @can('view_quotations')
                <button wire:click="downloadPdf" class="gh-btn gh-btn--sm" wire:loading.attr="disabled" wire:target="downloadPdf">
                    <span wire:loading.remove wire:target="downloadPdf">PDF</span>
                    <span wire:loading wire:target="downloadPdf">…</span>
                </button>
            @endcan
        </div>
    </div>

    <div class="gh-split">
        <div class="gh-stack">
            @if($quotation->isSent())
                <div class="gh-note">
                    <span class="gh-note__title">Share with customer</span>
                    <span class="gh-note__body">
                        <a href="{{ $this->approvalUrl }}" target="_blank" style="word-break:break-all;">{{ $this->approvalUrl }}</a>
                    </span>
                    <a href="{{ $this->whatsappUrl }}" target="_blank" class="gh-btn gh-btn--sm" style="align-self:flex-start; margin-top:6px; color:var(--gh-success);">WhatsApp</a>
                </div>
            @endif

            <!-- Line Items -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Line Items</div>
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead>
                            <tr><th>Type</th><th>Description</th><th>Supplier</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Unit price</th><th style="text-align:right;">Discount</th><th style="text-align:right;">VAT</th><th style="text-align:right;">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach($quotation->items->sortBy('sort_order') as $item)
                                <tr>
                                    <td><span class="gh-badge {{ $item->item_type === 'labor' ? '' : 'gh-badge--primary' }}">{{ ucfirst($item->item_type) }}</span></td>
                                    <td>
                                        {{ $item->description }}
                                        @if($item->inventoryItem)
                                            <span class="gh-badge" style="margin-left:4px;">{{ $item->inventoryItem->sku ?? 'inventory' }}</span>
                                        @endif
                                    </td>
                                    <td class="gh-muted">{{ $item->supplier?->name ?? '—' }}</td>
                                    <td class="is-num">{{ $item->quantity }}</td>
                                    <td class="is-num">UGX {{ number_format($item->unit_price) }}</td>
                                    <td class="is-num">{{ $item->discount > 0 ? 'UGX '.number_format($item->discount) : '—' }}</td>
                                    <td class="is-num">
                                        @if($item->vat_applicable)
                                            <span class="gh-badge gh-badge--warning">{{ $item->vat_rate }}%</span>
                                        @else
                                            <span class="gh-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="is-num">UGX {{ number_format($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="7" style="text-align:right; font-weight:700; padding:10px 18px;">Subtotal:</td><td class="is-num">UGX {{ number_format($quotation->subtotal) }}</td></tr>
                            @if($quotation->vat_amount > 0)
                                <tr><td colspan="7" style="text-align:right; color:var(--gh-ink-subtle); padding:6px 18px;">VAT ({{ $quotation->vat_rate }}%):</td><td class="is-num" style="color:var(--gh-ink-subtle);">UGX {{ number_format($quotation->vat_amount) }}</td></tr>
                            @endif
                            <tr><td colspan="7" style="text-align:right; font-weight:800; font-size:15px; padding:10px 18px;">Total:</td><td class="is-num" style="font-size:15px;">UGX {{ number_format($quotation->total) }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($quotation->notes)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:8px;">Notes</div>
                    <p style="font-size:13px; white-space:pre-wrap;">{{ $quotation->notes }}</p>
                </div>
            @endif

            @if($quotation->terms_and_conditions)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:8px;">Terms &amp; Conditions</div>
                    <p style="font-size:13px; white-space:pre-wrap;">{{ $quotation->terms_and_conditions }}</p>
                </div>
            @endif

            @if($quotation->isRejected() && $quotation->rejection_reason)
                <div class="gh-card gh-card--pad" style="background:var(--gh-error-bg); border-color:var(--gh-error);">
                    <div class="gh-card__title" style="color:var(--gh-error); margin-bottom:8px;">Rejection Reason</div>
                    <p style="font-size:13px;">{{ $quotation->rejection_reason }}</p>
                </div>
            @endif

            @if($quotation->revisions->count() || $quotation->parentQuotation)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:10px;">Version History</div>
                    @if($quotation->parentQuotation)
                        <p class="gh-muted" style="font-size:12px; margin-bottom:8px;">This is a revision of <a href="{{ route('quotations.show', $quotation->parentQuotation) }}">{{ $quotation->parentQuotation->quotation_number }} (v{{ $quotation->parentQuotation->version }})</a></p>
                    @endif
                    @foreach($quotation->revisions->sortByDesc('version') as $rev)
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:6px 0;">
                            <span class="is-ref">{{ $rev->quotation_number }}</span>
                            <span class="gh-badge {{ $rev->status_color !== 'ghost' ? 'gh-badge--'.$rev->status_color : '' }}">{{ $rev->status }}</span>
                            <a href="{{ route('quotations.show', $rev) }}" class="gh-btn gh-btn--sm">View</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="gh-stack">
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Details</div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Status</span><span class="gh-badge {{ $quotation->status_color !== 'ghost' ? 'gh-badge--'.($quotation->status_color === 'accent' ? 'primary' : $quotation->status_color) : '' }}">{{ ucfirst($quotation->status) }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Valid until</span><b style="{{ $quotation->valid_until && $quotation->valid_until->isPast() ? 'color:var(--gh-error);' : '' }}">{{ $quotation->valid_until?->format('d M Y') ?? '—' }}</b></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Created by</span><b>{{ $quotation->createdBy?->name ?? '—' }}</b></div>
                    @if($quotation->sent_at)
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Sent</span><span>{{ $quotation->sent_at->format('d M Y H:i') }}</span></div>
                    @endif
                    @if($quotation->approved_at)
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Approved</span><span style="color:var(--gh-success);">{{ $quotation->approved_at->format('d M Y H:i') }}</span></div>
                    @endif
                    @if($quotation->rejected_at)
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Rejected</span><span style="color:var(--gh-error);">{{ $quotation->rejected_at->format('d M Y H:i') }}</span></div>
                    @endif
                </div>
            </div>

            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Work Order</div>
                <a href="{{ route('work-orders.show', $quotation->workOrder) }}" class="is-ref">{{ $quotation->workOrder->order_number }}</a>
                <p class="gh-muted" style="font-size:12px; margin-top:4px;">{{ $quotation->workOrder->vehicle->registration_number }}</p>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    @if($showRejectModal)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box gh-card gh-card--pad" style="max-width:420px; position:relative;">
                <button wire:click="closeRejectModal" class="gh-btn gh-btn--sm" style="position:absolute; right:14px; top:14px;">✕</button>
                <div class="gh-card__title" style="margin-bottom:16px;">Reject Quotation</div>
                <div class="gh-field">
                    <span class="gh-label">Reason for rejection *</span>
                    <textarea wire:model="rejectionReason" rows="4" class="gh-input" style="width:100%;" placeholder="Explain why the quotation was rejected…"></textarea>
                    @error('rejectionReason') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <button wire:click="closeRejectModal" class="gh-btn">Cancel</button>
                    <button wire:click="rejectOnBehalf" class="gh-btn" style="color:var(--gh-error);" wire:loading.attr="disabled" wire:target="rejectOnBehalf">
                        <span wire:loading.remove wire:target="rejectOnBehalf">Confirm reject</span>
                        <span wire:loading wire:target="rejectOnBehalf">Rejecting…</span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeRejectModal"></div>
        </div>
    @endif
</div>
