<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Payments</h1>
            <p class="text-base-content/60">Payment history and records</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Today</div>
            <div class="stat-value text-lg text-success">UGX {{ number_format($totals['today']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">This Week</div>
            <div class="stat-value text-lg">UGX {{ number_format($totals['week']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">This Month</div>
            <div class="stat-value text-lg">UGX {{ number_format($totals['month']) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search customer or reference..." class="input input-bordered input-sm" />
                <select wire:model.live="method" class="select select-bordered select-sm">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="card">Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
                <input type="date" wire:model.live="dateFrom" class="input input-bordered input-sm" />
                <input type="date" wire:model.live="dateTo" class="input input-bordered input-sm" />
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Invoice</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th class="text-right">Amount</th>
                        <th>Received By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr class="hover">
                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('customers.show', $payment->invoice->customer) }}" class="link link-hover">
                                    {{ $payment->invoice->customer->name }}
                                </a>
                            </td>
                            <td>
                                @if($payment->invoice)
                                    <a href="{{ route('invoices.show', $payment->invoice) }}" class="link link-primary font-mono text-sm">
                                        {{ $payment->invoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-ghost badge-sm">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                            </td>
                            <td class="font-mono text-sm">{{ $payment->reference_number ?? '-' }}</td>
                            <td class="text-right font-medium text-success">UGX {{ number_format($payment->amount) }}</td>
                            <td>{{ $payment->receivedBy?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-8 text-base-content/50">No payments found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="p-4 border-t border-base-200">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
