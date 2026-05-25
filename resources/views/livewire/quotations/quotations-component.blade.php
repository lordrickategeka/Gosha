<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Quotations</h1>
            <p class="text-base-content/60">Track all quotations and revisions</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by quotation #, customer, or work order..."
                    class="input input-bordered input-sm"
                />

                <select wire:model.live="status" class="select select-bordered select-sm">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="expired">Expired</option>
                </select>

                <select wire:model.live="perPage" class="select select-bordered select-sm">
                    <option value="15">15 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Quotation</th>
                        <th>Customer</th>
                        <th>Work Order</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th class="text-right">Total</th>
                        <th>Valid Until</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quotation)
                        <tr class="hover">
                            <td>
                                <a href="{{ route('quotations.show', $quotation) }}" class="link link-primary font-mono text-sm">
                                    {{ $quotation->quotation_number }}
                                </a>
                                @if($quotation->version > 1)
                                    <span class="badge badge-neutral badge-xs ml-1">v{{ $quotation->version }}</span>
                                @endif
                                @if($quotation->parentQuotation)
                                    <div class="text-xs text-base-content/50 mt-1">
                                        Revision of {{ $quotation->parentQuotation->quotation_number }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $quotation->customer?->name ?? '—' }}</td>
                            <td>
                                @if($quotation->workOrder)
                                    <a href="{{ route('work-orders.show', $quotation->workOrder) }}" class="link text-sm">
                                        {{ $quotation->workOrder->order_number }}
                                    </a>
                                @else
                                    <span class="text-base-content/40">—</span>
                                @endif
                            </td>
                            <td>{{ $quotation->createdBy?->name ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $quotation->status_color }} badge-sm">{{ ucfirst($quotation->status) }}</span>
                            </td>
                            <td class="text-right font-medium">UGX {{ number_format((float) $quotation->total) }}</td>
                            <td class="text-sm {{ $quotation->valid_until && $quotation->valid_until->isPast() ? 'text-error font-medium' : '' }}">
                                {{ $quotation->valid_until?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="text-sm">{{ $quotation->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">⋮</label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40">
                                        <li><a href="{{ route('quotations.show', $quotation) }}">View</a></li>
                                        @can('edit_quotations')
                                            <li><a href="{{ route('quotations.edit', $quotation) }}">Edit</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-base-content/50">No quotations found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($quotations->hasPages())
            <div class="p-4 border-t border-base-200">{{ $quotations->links() }}</div>
        @endif
    </div>
</div>
