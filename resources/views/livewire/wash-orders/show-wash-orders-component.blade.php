<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('wash-orders.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-mono">{{ $washOrder->order_number }}</h1>
                    <span class="badge badge-{{ $washOrder->status_color }}">{{ ucfirst($washOrder->status) }}</span>
                    @if($washOrder->source === 'combo')
                        <span class="badge badge-accent">COMBO</span>
                    @endif
                </div>
                <p class="text-base-content/60">{{ $washOrder->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="flex gap-2">
            @can('change wash order status')
                @if($washOrder->canStart())
                    @if($this->availableBays->count() > 0)
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-primary">Start Wash</label>
                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-48">
                                @foreach($this->availableBays as $bay)
                                    <li><button wire:click="start({{ $bay->id }})">{{ $bay->name }}</button></li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <button class="btn btn-disabled" disabled>No Bay Available</button>
                    @endif
                @endif
                @if($washOrder->canComplete())
                    <button wire:click="complete" class="btn btn-success">Complete Wash</button>
                @endif
                @if($washOrder->status === 'queued')
                    <button wire:click="cancel" class="btn btn-ghost text-error">Cancel</button>
                @endif
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Vehicle & Customer -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-base-content/60 mb-2">Vehicle</h3>
                            <p class="font-bold text-lg">{{ $washOrder->vehicle->registration_number }}</p>
                            <p class="text-sm text-base-content/60">{{ $washOrder->vehicle->make }} {{ $washOrder->vehicle->model }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-base-content/60 mb-2">Customer</h3>
                            <p class="font-bold">{{ $washOrder->customer->name }}</p>
                            <p class="text-sm text-base-content/60">{{ $washOrder->customer->phone }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Services</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th class="text-right">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($washOrder->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-right">UGX {{ number_format($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-right font-bold">Total:</td>
                                <td class="text-right font-bold text-lg">UGX {{ number_format($washOrder->total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($washOrder->customer_notes)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-lg">Notes</h3>
                        <p>{{ $washOrder->customer_notes }}</p>
                    </div>
                </div>
            @endif

            @if($washOrder->workOrder)
                <div class="card bg-accent/10 border border-accent shadow-sm">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="card-title text-lg">Linked Work Order</h3>
                                <p class="font-mono">{{ $washOrder->workOrder->order_number }}</p>
                            </div>
                            <a href="{{ route('work-orders.show', $washOrder->workOrder) }}" class="btn btn-accent btn-sm">View</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <!-- Status -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Timeline</h3>
                    <ul class="steps steps-vertical">
                        <li class="step {{ in_array($washOrder->status, ['queued', 'in_progress', 'completed']) ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Queued</p>
                                @if($washOrder->queued_at)
                                    <p class="text-xs text-base-content/60">{{ $washOrder->queued_at->format('H:i') }}</p>
                                @endif
                            </div>
                        </li>
                        <li class="step {{ in_array($washOrder->status, ['in_progress', 'completed']) ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Started</p>
                                @if($washOrder->started_at)
                                    <p class="text-xs text-base-content/60">{{ $washOrder->started_at->format('H:i') }}</p>
                                @endif
                            </div>
                        </li>
                        <li class="step {{ $washOrder->status === 'completed' ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Completed</p>
                                @if($washOrder->completed_at)
                                    <p class="text-xs text-base-content/60">{{ $washOrder->completed_at->format('H:i') }}</p>
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Details -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Details</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Wash Type</span>
                            <span class="badge badge-ghost">{{ ucfirst($washOrder->wash_type) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Source</span>
                            <span>{{ $washOrder->source_badge }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Bay</span>
                            <span>{{ $washOrder->washBay?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Attendant</span>
                            <span>{{ $washOrder->assignedAttendant?->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
