<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Customers</h1>
            <p class="text-base-content/60">Manage your customer database</p>
        </div>
        @can('create_customers')
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Customer
        </a>
        @endcan
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by name, phone, email..." class="input input-bordered w-full max-w-md" />
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Vehicles</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Loyalty</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr class="hover">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-10">
                                            <span>{{ substr($customer->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('customers.show', $customer) }}" class="font-bold link link-hover">{{ $customer->name }}</a>
                                        @if($customer->company)
                                            <p class="text-xs text-base-content/60">{{ $customer->company }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p>{{ $customer->phone }}</p>
                                @if($customer->email)
                                    <p class="text-xs text-base-content/60">{{ $customer->email }}</p>
                                @endif
                            </td>
                            <td>{{ $customer->vehicles_count }}</td>
                            <td>{{ $customer->work_orders_count + $customer->wash_orders_count }}</td>
                            <td class="font-medium">UGX {{ number_format($customer->invoices_sum_total ?? 0) }}</td>
                            <td>
                                <span class="badge badge-ghost">{{ number_format($customer->loyalty_points) }} pts</span>
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40">
                                        <li><a href="{{ route('customers.show', $customer) }}">View</a></li>
                                        @can('edit_customers')
                                            <li><a href="{{ route('customers.edit', $customer) }}">Edit</a></li>
                                        @endcan
                                        <li><a href="{{ route('work-orders.create', ['customer' => $customer->id]) }}">New Work Order</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-base-content/50">No customers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="p-4 border-t border-base-200">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
