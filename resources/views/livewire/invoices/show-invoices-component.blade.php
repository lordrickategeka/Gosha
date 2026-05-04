<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('invoices.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-mono">{{ $invoice->invoice_number }}</h1>
                    <span class="badge badge-{{ $invoice->status_color }}">{{ ucfirst($invoice->status) }}</span>
                </div>
                <p class="text-base-content/60">{{ $invoice->created_at->format('d M Y') }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            @if($invoice->balance_due > 0)
                @can('receive_payments')
                    <button wire:click="$set('showPaymentModal', true)" class="btn btn-primary">Record Payment</button>
                @endcan
            @endif
            <button onclick="window.print()" class="btn btn-ghost no-print">Print</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Invoice Details -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-sm font-medium text-base-content/60 mb-2">Bill To</h3>
                            <p class="font-bold text-lg">{{ $invoice->customer->name }}</p>
                            <p>{{ $invoice->customer->phone }}</p>
                            @if($invoice->customer->email)<p>{{ $invoice->customer->email }}</p>@endif
                            @if($invoice->customer->address)<p class="text-sm text-base-content/60">{{ $invoice->customer->address }}</p>@endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-base-content/60">Invoice Date</p>
                            <p class="font-medium">{{ $invoice->created_at->format('d M Y') }}</p>
                            <p class="text-sm text-base-content/60 mt-2">Due Date</p>
                            <p class="font-medium {{ $invoice->isOverdue() ? 'text-error' : '' }}">{{ $invoice->due_date->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-right">{{ $item->quantity }}</td>
                                        <td class="text-right">UGX {{ number_format($item->unit_price) }}</td>
                                        <td class="text-right">UGX {{ number_format($item->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right">Subtotal:</td>
                                    <td class="text-right">UGX {{ number_format($invoice->subtotal) }}</td>
                                </tr>
                                @if($invoice->tax_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-right">Tax ({{ $invoice->tax_rate }}%):</td>
                                        <td class="text-right">UGX {{ number_format($invoice->tax_amount) }}</td>
                                    </tr>
                                @endif
                                @if($invoice->discount > 0)
                                    <tr>
                                        <td colspan="3" class="text-right">Discount:</td>
                                        <td class="text-right text-success">-UGX {{ number_format($invoice->discount) }}</td>
                                    </tr>
                                @endif
                                <tr class="text-lg font-bold">
                                    <td colspan="3" class="text-right">Total:</td>
                                    <td class="text-right">UGX {{ number_format($invoice->total) }}</td>
                                </tr>
                                @if($invoice->amount_paid > 0)
                                    <tr class="text-success">
                                        <td colspan="3" class="text-right">Paid:</td>
                                        <td class="text-right">-UGX {{ number_format($invoice->amount_paid) }}</td>
                                    </tr>
                                @endif
                                @if($invoice->balance_due > 0)
                                    <tr class="text-warning text-lg font-bold">
                                        <td colspan="3" class="text-right">Balance Due:</td>
                                        <td class="text-right">UGX {{ number_format($invoice->balance_due) }}</td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>

                    @if($invoice->notes)
                        <div class="mt-4 p-3 bg-base-200 rounded-lg">
                            <p class="text-sm"><span class="font-medium">Notes:</span> {{ $invoice->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payments -->
            @if($invoice->payments->count() > 0)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Payment History</h2>
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Date</th><th>Method</th><th>Reference</th><th class="text-right">Amount</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->payments as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                            <td>{{ $payment->reference_number ?? '-' }}</td>
                                            <td class="text-right font-medium text-success">UGX {{ number_format($payment->amount) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6 no-print">
            <!-- Related Order -->
            @if($invoice->workOrder || $invoice->washOrder)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Related Order</h2>
                        @if($invoice->workOrder)
                            <a href="{{ route('work-orders.show', $invoice->workOrder) }}" class="block p-3 border border-base-300 rounded-lg hover:border-primary">
                                <p class="font-mono font-bold">{{ $invoice->workOrder->order_number }}</p>
                                <p class="text-sm text-base-content/60">Work Order</p>
                            </a>
                        @endif
                        @if($invoice->washOrder)
                            <a href="{{ route('wash-orders.show', $invoice->washOrder) }}" class="block p-3 border border-base-300 rounded-lg hover:border-primary">
                                <p class="font-mono font-bold">{{ $invoice->washOrder->order_number }}</p>
                                <p class="text-sm text-base-content/60">Wash Order</p>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">Summary</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-base-content/60">Total</span><span class="font-bold">UGX {{ number_format($invoice->total) }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Paid</span><span class="text-success">UGX {{ number_format($invoice->amount_paid) }}</span></div>
                        <div class="flex justify-between"><span class="text-base-content/60">Balance</span><span class="{{ $invoice->balance_due > 0 ? 'text-warning font-bold' : 'text-success' }}">UGX {{ number_format($invoice->balance_due) }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    @if($showPaymentModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Record Payment</h3>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Amount</span></label>
                    <input type="number" wire:model="paymentAmount" class="input input-bordered" max="{{ $invoice->balance_due }}" />
                    <span class="label-text-alt">Balance due: UGX {{ number_format($invoice->balance_due) }}</span>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Payment Method</span></label>
                    <select wire:model="paymentMethod" class="select select-bordered">
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Reference Number</span></label>
                    <input type="text" wire:model="paymentReference" class="input input-bordered" placeholder="Transaction ID, receipt #, etc." />
                </div>
                <div class="modal-action">
                    <button wire:click="$set('showPaymentModal', false)" class="btn btn-ghost">Cancel</button>
                    <button wire:click="recordPayment" class="btn btn-primary">Record Payment</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showPaymentModal', false)"></div>
        </div>
    @endif
</div>
