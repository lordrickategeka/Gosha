<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('invoices.index') }}" class="gh-btn gh-btn--sm no-print">←</a>
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:20px; font-weight:700;">{{ $invoice->invoice_number }}</span>
                    <span class="gh-badge {{ $invoice->status_color !== 'ghost' ? 'gh-badge--'.($invoice->status_color === 'accent' ? 'primary' : $invoice->status_color) : '' }}">{{ ucfirst($invoice->status) }}</span>
                </div>
                <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">{{ $invoice->created_at->format('d M Y') }}</p>
            </div>
        </div>
        <div style="display:flex; gap:8px;" class="no-print">
            @if($invoice->balance_due > 0)
                @can('receive_payments')
                    <button wire:click="$set('showPaymentModal', true)" class="gh-btn gh-btn--primary">Record payment</button>
                @endcan
            @endif
            <button onclick="window.print()" class="gh-btn">Print</button>
        </div>
    </div>

    <div class="gh-split">
        <div class="gh-stack">
            <!-- Invoice Details -->
            <div class="gh-card gh-card--pad">
                <div class="gh-grid-2" style="margin-bottom:20px;">
                    <div>
                        <p class="gh-eyebrow" style="margin-bottom:6px;">Bill to</p>
                        <p style="font-weight:700; font-size:16px;">{{ $invoice->customer->name }}</p>
                        <p style="font-size:12.5px;">{{ $invoice->customer->phone }}</p>
                        @if($invoice->customer->email)<p style="font-size:12.5px;">{{ $invoice->customer->email }}</p>@endif
                        @if($invoice->customer->address)<p class="gh-muted" style="font-size:12px;">{{ $invoice->customer->address }}</p>@endif
                    </div>
                    <div style="text-align:right;">
                        <p class="gh-eyebrow">Invoice date</p>
                        <p style="font-weight:600; font-size:13px;">{{ $invoice->created_at->format('d M Y') }}</p>
                        <p class="gh-eyebrow" style="margin-top:10px;">Due date</p>
                        <p style="font-weight:600; font-size:13px; {{ $invoice->isOverdue() ? 'color:var(--gh-error);' : '' }}">{{ $invoice->due_date->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Description</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Unit price</th><th style="text-align:right;">Total</th></tr></thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="is-num">{{ $item->quantity }}</td>
                                    <td class="is-num">UGX {{ number_format($item->unit_price) }}</td>
                                    <td class="is-num">UGX {{ number_format($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="display:flex; justify-content:flex-end; padding:15px 18px; background:var(--gh-surface-header); border-top:1px solid var(--gh-hairline); margin-top:12px; border-radius:0 0 var(--gh-radius-lg) var(--gh-radius-lg);">
                    <div style="text-align:right; display:flex; flex-direction:column; gap:7px; font-size:12.5px; min-width:220px;">
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Subtotal</span><b>UGX {{ number_format($invoice->subtotal) }}</b></div>
                        @if($invoice->tax_amount > 0)
                            <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Tax ({{ $invoice->tax_rate }}%)</span><b>UGX {{ number_format($invoice->tax_amount) }}</b></div>
                        @endif
                        @if($invoice->discount > 0)
                            <div style="display:flex; justify-content:space-between; color:var(--gh-success);"><span>Discount</span><b>-UGX {{ number_format($invoice->discount) }}</b></div>
                        @endif
                        <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:800; border-top:1px solid var(--gh-hairline); padding-top:7px;"><span>Total</span><span>UGX {{ number_format($invoice->total) }}</span></div>
                        @if($invoice->amount_paid > 0)
                            <div style="display:flex; justify-content:space-between; color:var(--gh-success);"><span>Paid</span><b>-UGX {{ number_format($invoice->amount_paid) }}</b></div>
                        @endif
                        @if($invoice->balance_due > 0)
                            <div style="display:flex; justify-content:space-between; color:var(--gh-warning); font-size:15px; font-weight:800;"><span>Balance due</span><span>UGX {{ number_format($invoice->balance_due) }}</span></div>
                        @endif
                    </div>
                </div>

                @if($invoice->notes)
                    <div class="gh-note" style="margin-top:14px;"><span class="gh-note__body"><b>Notes:</b> {{ $invoice->notes }}</span></div>
                @endif
            </div>

            <!-- Payments -->
            @if($invoice->payments->count() > 0)
                <div class="gh-card gh-card--flush">
                    <div class="gh-card__head"><span class="gh-card__title">Payment History</span></div>
                    <div class="gh-table-scroll">
                        <table class="gh-table">
                            <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th style="text-align:right;">Amount</th></tr></thead>
                            <tbody>
                                @foreach($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                        <td>{{ $payment->reference_number ?? '—' }}</td>
                                        <td class="is-num" style="color:var(--gh-success);">UGX {{ number_format($payment->amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="gh-stack no-print">
            <!-- Related Order -->
            @if($invoice->workOrder || $invoice->washOrder)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:14px;">Related Order</div>
                    @if($invoice->workOrder)
                        <a href="{{ route('work-orders.show', $invoice->workOrder) }}" class="gh-card" style="display:block; padding:12px;">
                            <p class="is-ref">{{ $invoice->workOrder->order_number }}</p>
                            <p class="gh-muted" style="font-size:11.5px;">Work Order</p>
                        </a>
                    @endif
                    @if($invoice->washOrder)
                        <a href="{{ route('wash-orders.show', $invoice->washOrder) }}" class="gh-card" style="display:block; padding:12px; margin-top:10px;">
                            <p class="is-ref">{{ $invoice->washOrder->order_number }}</p>
                            <p class="gh-muted" style="font-size:11.5px;">Wash Order</p>
                        </a>
                    @endif
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Summary</div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Total</span><b>UGX {{ number_format($invoice->total) }}</b></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Paid</span><span style="color:var(--gh-success);">UGX {{ number_format($invoice->amount_paid) }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Balance</span><b style="{{ $invoice->balance_due > 0 ? 'color:var(--gh-warning);' : 'color:var(--gh-success);' }}">UGX {{ number_format($invoice->balance_due) }}</b></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    @if($showPaymentModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:16px;">Record Payment</div>
                <div class="gh-field" style="margin-bottom:14px;">
                    <span class="gh-label">Amount</span>
                    <input type="number" wire:model="paymentAmount" class="gh-input" style="width:100%;" max="{{ $invoice->balance_due }}">
                    <span class="gh-hint">Balance due: UGX {{ number_format($invoice->balance_due) }}</span>
                </div>
                <div class="gh-field" style="margin-bottom:14px;">
                    <span class="gh-label">Payment method</span>
                    <select wire:model="paymentMethod" class="gh-select" style="width:100%;">
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div class="gh-field" style="margin-bottom:18px;">
                    <span class="gh-label">Reference number</span>
                    <input type="text" wire:model="paymentReference" class="gh-input" style="width:100%;" placeholder="Transaction ID, receipt #, etc.">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px;">
                    <button wire:click="$set('showPaymentModal', false)" class="gh-btn">Cancel</button>
                    <button wire:click="recordPayment" class="gh-btn gh-btn--primary">Record payment</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showPaymentModal', false)"></div>
        </div>
    @endif
</div>
