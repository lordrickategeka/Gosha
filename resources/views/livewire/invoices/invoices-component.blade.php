<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Invoices</h1>
            <p class="text-base-content/60">Manage billing and payments</p>
        </div>
        @can('create_invoices')
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Invoice
        </a>
        @endcan
    </div>

<!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Total Invoiced</div>
            <div class="stat-value text-lg">UGX {{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Paid</div>
            <div class="stat-value text-lg text-success">UGX {{ number_format($stats['paid']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Pending</div>
            <div class="stat-value text-lg text-warning">UGX {{ number_format($stats['pending']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Overdue</div>
            <div class="stat-value text-lg text-error">UGX {{ number_format($stats['overdue']) }}</div>
        </div>
    </div>

<div class="card bg-base-100 shadow-sm">
        <div class="flex flex-col gap-4 border-b border-base-200 px-4 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold">Invoices</h2>
                <p class="text-sm text-base-content/60">{{ $invoices->total() }} total records</p>
            </div>

            <!-- Table Filters -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Search -->
                <div class="form-control">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search invoices..."
                        class="input input-bordered input-sm w-40"
                    />
                </div>

                <!-- Status Filter -->
                <div class="form-control">
                    <select wire:model.live="status" class="select select-bordered select-sm w-32">
                        <option value="">All Status</option>
                        <option value="sent">Pending</option>
                        <option value="partial">Partially Paid</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="form-control">
                    <input type="date" wire:model.live="dateFrom" class="input input-bordered input-sm w-36" placeholder="From" />
                </div>

                <!-- Date To -->
                <div class="form-control">
                    <input type="date" wire:model.live="dateTo" class="input input-bordered input-sm w-36" placeholder="To" />
                </div>

                <!-- Clear Filters -->
                @if($search || $status || $dateFrom || $dateTo)
                <button wire:click="clearFilters" class="btn btn-xs btn-ghost" title="Clear filters">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Related Order</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Balance</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr class="hover">
                            <td>
                                <a href="{{ route('invoices.show', $invoice) }}" class="link link-primary font-mono text-sm">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td>{{ $invoice->customer->name }}</td>
                            <td>
                                @if($invoice->workOrder)
                                    <a href="{{ route('work-orders.show', $invoice->workOrder) }}" class="link text-sm">{{ $invoice->workOrder->order_number }}</a>
                                @elseif($invoice->washOrder)
                                    <a href="{{ route('wash-orders.show', $invoice->washOrder) }}" class="link text-sm">{{ $invoice->washOrder->order_number }}</a>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ $invoice->created_at->format('d M Y') }}</td>
                            <td class="text-sm {{ $invoice->isOverdue() ? 'text-error font-medium' : '' }}">
                                {{ $invoice->due_date->format('d M Y') }}
                            </td>
                            <td class="text-right font-medium">UGX {{ number_format($invoice->total) }}</td>
                            <td class="text-right {{ $invoice->balance_due > 0 ? 'text-warning font-medium' : 'text-success' }}">
                                UGX {{ number_format($invoice->balance_due) }}
                            </td>
                            <td><span class="badge badge-{{ $invoice->status_color }} badge-sm">{{ ucfirst($invoice->status) }}</span></td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">⋮</label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40">
                                        <li><a href="{{ route('invoices.show', $invoice) }}">View</a></li>
                                        @if($invoice->balance_due > 0)
                                            <li><a href="{{ route('invoices.show', $invoice) }}?record_payment=1">Record Payment</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-8 text-base-content/50">No invoices found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="p-4 border-t border-base-200">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
