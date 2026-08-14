<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Customers</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage your customer database</p>
        </div>
        @can('create_customers')
            <a href="{{ route('customers.create') }}" class="gh-btn gh-btn--primary">+ Add customer</a>
        @endcan
    </div>

    <div class="gh-table-toolbar">
        <span class="gh-hint">{{ $customers->total() }} total records</span>
        <div style="display:flex; align-items:center; gap:8px;">
            <label class="gh-search" style="width:220px;">
                ⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, phone, email…">
            </label>
            @if($search)
                <button wire:click="$set('search', '')" class="gh-btn gh-btn--sm">Clear</button>
            @endif
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr><th>Customer</th><th>Branch</th><th>Contact</th><th>Vehicles</th><th>Orders</th><th style="text-align:right;">Total spent</th><th>Loyalty</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr data-href="{{ route('customers.show', $customer) }}">
                            <td>
                                <div class="gh-cell-stack">
                                    <a href="{{ route('customers.show', $customer) }}" class="is-ref">{{ $customer->name }}</a>
                                    @if($customer->company)
                                        <span>{{ $customer->company }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($customer->branch)
                                    <span class="gh-badge">{{ $customer->branch->name }}</span>
                                @else
                                    <span class="gh-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="gh-cell-stack">
                                    <b>{{ $customer->phone }}</b>
                                    @if($customer->email)
                                        <span>{{ $customer->email }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $customer->vehicles_count }}</td>
                            <td>{{ $customer->work_orders_count + $customer->wash_orders_count }}</td>
                            <td class="is-num">UGX {{ number_format($customer->invoices_sum_total ?? 0) }}</td>
                            <td><span class="gh-badge">{{ number_format($customer->loyalty_points) }} pts</span></td>
                            <td onclick="event.stopPropagation()">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="gh-btn gh-btn--sm">⋮</label>
                                    <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-44 gh-card p-2 shadow-xl">
                                        <li><a href="{{ route('customers.show', $customer) }}">View</a></li>
                                        @can('edit_customers')
                                            <li><a href="{{ route('customers.edit', $customer) }}">Edit</a></li>
                                        @endcan
                                        <li><a href="{{ route('work-orders.create', ['customer' => $customer->id]) }}">New work order</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No customers found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="gh-pagination">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
