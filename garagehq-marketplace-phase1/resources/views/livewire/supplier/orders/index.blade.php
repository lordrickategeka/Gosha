<div class="p-6 space-y-4">
    <h1 class="text-2xl font-bold">Incoming Orders</h1>
    <select class="select select-bordered w-56" wire:model.live="statusFilter">
        <option value="">All statuses</option>
        @foreach ($statuses as $s)
            <option value="{{ $s->value }}">{{ $s->label() }}</option>
        @endforeach
    </select>

    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>PO</th><th>Buyer</th><th>Total</th><th>Status</th><th>Payment</th><th></th></tr></thead>
            <tbody>
                @forelse ($orders as $po)
                    <tr>
                        <td class="font-mono">{{ $po->po_number }}</td>
                        <td>{{ $po->buyer?->name }}</td>
                        <td>{{ number_format($po->total) }} {{ $po->currency }}</td>
                        <td><span class="badge {{ $po->status->badge() }}">{{ $po->status->label() }}</span></td>
                        <td><span class="badge {{ $po->payment_status->badge() }}">{{ $po->payment_status->label() }}</span></td>
                        <td class="text-right">
                            @if ($po->status->value === 'sent')
                                <button class="btn btn-xs btn-primary" wire:click="accept({{ $po->id }})">Accept</button>
                            @endif
                            <a class="btn btn-xs btn-ghost" href="{{ route('supplier.orders.fulfill', $po) }}">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-base-content/60 py-6">No orders.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div>
