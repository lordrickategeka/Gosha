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

    <!-- Filters (in table header) -->
    <div class="card bg-base-100 shadow-sm mb-4">
        <div class="flex flex-wrap items-center gap-2 p-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="input input-bordered input-sm w-40" />
            <select wire:model.live="method" class="select select-bordered select-sm w-36">
                <option value="">All Methods</option>
                <option value="cash">Cash</option>
                <option value="mobile_money">Mobile Money</option>
                <option value="card">Card</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>
            <input type="date" wire:model.live="dateFrom" class="input input-bordered input-sm w-36" />
            <input type="date" wire:model.live="dateTo" class="input input-bordered input-sm w-36" />
            @if($search || $method || $dateFrom || $dateTo)
            <button wire:click="clearFilters" class="btn btn-xs btn-ghost" title="Clear filters">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            @endif
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
