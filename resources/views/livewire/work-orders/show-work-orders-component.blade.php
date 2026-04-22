<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('work-orders.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-mono">{{ $workOrder->order_number }}</h1>
                    <span class="badge badge-{{ $workOrder->status_color }}">
                        {{ str_replace('_', ' ', ucfirst($workOrder->status)) }}
                    </span>
                    @if($workOrder->is_combo)
                        <span class="badge badge-accent">COMBO</span>
                    @endif
                    @if($workOrder->priority === 'urgent')
                        <span class="badge badge-error">URGENT</span>
                    @endif
                </div>
                <p class="text-base-content/60">Created {{ $workOrder->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="flex gap-2">
            @can('change work order status')
                @if($workOrder->canStart())
                    <button wire:click="startWork" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Start Work
                    </button>
                @endif
                @if($workOrder->status === 'in_progress')
                    <button wire:click="moveToQualityCheck" class="btn btn-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Quality Check
                    </button>
                @endif
                @if($workOrder->status === 'quality_check')
                    <button wire:click="markReady" class="btn btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Mark Ready
                    </button>
                @endif
                @if($workOrder->canDeliver())
                    <button wire:click="deliver" class="btn btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Deliver
                    </button>
                @endif
            @endcan

            @can('edit work orders')
                <a href="{{ route('work-orders.edit', $workOrder) }}" class="btn btn-ghost">Edit</a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Vehicle & Customer -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Vehicle -->
                        <div>
                            <h3 class="text-sm font-medium text-base-content/60 mb-2">Vehicle</h3>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 4h8m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-lg">{{ $workOrder->vehicle->registration_number }}</p>
                                    <p class="text-sm text-base-content/60">
                                        {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                                        @if($workOrder->vehicle->color)
                                            <span class="text-base-content/40">• {{ $workOrder->vehicle->color }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if($workOrder->mileage_in)
                                <p class="mt-2 text-sm">
                                    <span class="text-base-content/60">Mileage In:</span>
                                    <span class="font-medium">{{ number_format($workOrder->mileage_in) }} km</span>
                                </p>
                            @endif
                        </div>

                        <!-- Customer -->
                        <div>
                            <h3 class="text-sm font-medium text-base-content/60 mb-2">Customer</h3>
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-12">
                                        <span>{{ substr($workOrder->customer->name, 0, 2) }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-bold">{{ $workOrder->customer->name }}</p>
                                    <p class="text-sm text-base-content/60">{{ $workOrder->customer->phone }}</p>
                                    @if($workOrder->customer->email)
                                        <p class="text-sm text-base-content/60">{{ $workOrder->customer->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Notes -->
            @if($workOrder->customer_notes)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-lg">Customer Notes</h3>
                        <p class="text-base-content/80">{{ $workOrder->customer_notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Job Items -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Job Items</h3>

                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workOrder->items as $item)
                                    <tr>
                                        <td>
                                            <span class="badge badge-{{ $item->item_type === 'labor' ? 'primary' : 'secondary' }} badge-sm">
                                                {{ ucfirst($item->item_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-right">{{ $item->quantity }}</td>
                                        <td class="text-right">UGX {{ number_format($item->unit_price) }}</td>
                                        <td class="text-right font-medium">UGX {{ number_format($item->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-right font-bold">Subtotal:</td>
                                    <td class="text-right font-bold text-lg">UGX {{ number_format($workOrder->subtotal) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Technician Notes -->
            @if($workOrder->status === 'in_progress')
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-lg">Technician Notes</h3>
                        <textarea
                            wire:model="technicianNotes"
                            rows="3"
                            placeholder="Add notes about work done, findings, recommendations..."
                            class="textarea textarea-bordered w-full"
                        >{{ $workOrder->technician_notes }}</textarea>
                    </div>
                </div>
            @elseif($workOrder->technician_notes)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-lg">Technician Notes</h3>
                        <p class="text-base-content/80">{{ $workOrder->technician_notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Combo Wash Order -->
            @if($workOrder->washOrder)
                <div class="card bg-info/10 border border-info shadow-sm">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="card-title text-lg text-info">Combo Wash Order</h3>
                                <p class="text-sm">
                                    <span class="font-mono">{{ $workOrder->washOrder->order_number }}</span>
                                    <span class="badge badge-{{ $workOrder->washOrder->status_color }} badge-sm ml-2">
                                        {{ ucfirst($workOrder->washOrder->status) }}
                                    </span>
                                </p>
                            </div>
                            <a href="{{ route('wash-orders.show', $workOrder->washOrder) }}" class="btn btn-info btn-sm">
                                View Wash Order
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Timeline -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Timeline</h3>

                    <ul class="steps steps-vertical">
                        <li class="step {{ in_array($workOrder->status, ['open', 'in_progress', 'quality_check', 'ready', 'delivered']) ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Checked In</p>
                                @if($workOrder->checked_in_at)
                                    <p class="text-xs text-base-content/60">{{ $workOrder->checked_in_at->format('d M H:i') }}</p>
                                @endif
                            </div>
                        </li>
                        <li class="step {{ in_array($workOrder->status, ['in_progress', 'quality_check', 'ready', 'delivered']) ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Work Started</p>
                                @if($workOrder->started_at)
                                    <p class="text-xs text-base-content/60">{{ $workOrder->started_at->format('d M H:i') }}</p>
                                @endif
                            </div>
                        </li>
                        <li class="step {{ in_array($workOrder->status, ['quality_check', 'ready', 'delivered']) ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Quality Check</p>
                            </div>
                        </li>
                        <li class="step {{ in_array($workOrder->status, ['ready', 'delivered']) ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Ready</p>
                                @if($workOrder->completed_at)
                                    <p class="text-xs text-base-content/60">{{ $workOrder->completed_at->format('d M H:i') }}</p>
                                @endif
                            </div>
                        </li>
                        <li class="step {{ $workOrder->status === 'delivered' ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Delivered</p>
                                @if($workOrder->delivered_at)
                                    <p class="text-xs text-base-content/60">{{ $workOrder->delivered_at->format('d M H:i') }}</p>
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Assignment -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="card-title text-lg">Assignment</h3>
                        @can('assign work orders')
                            <button wire:click="$set('showAssignModal', true)" class="btn btn-ghost btn-xs">Edit</button>
                        @endcan
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-base-content/60">Service Bay</p>
                            <p class="font-medium">
                                {{ $workOrder->serviceBay?->name ?? 'Not assigned' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Technician</p>
                            @if($workOrder->assignedTechnician)
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                                            <span class="text-xs">{{ substr($workOrder->assignedTechnician->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <span class="font-medium">{{ $workOrder->assignedTechnician->name }}</span>
                                </div>
                            @else
                                <p class="font-medium text-base-content/50">Not assigned</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Status -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Invoice</h3>

                    @if($workOrder->invoice)
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Invoice #</span>
                                <a href="{{ route('invoices.show', $workOrder->invoice) }}" class="link link-primary font-mono">
                                    {{ $workOrder->invoice->invoice_number }}
                                </a>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Status</span>
                                <span class="badge badge-{{ $workOrder->invoice->status_color }}">
                                    {{ ucfirst($workOrder->invoice->status) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Total</span>
                                <span class="font-bold">UGX {{ number_format($workOrder->invoice->total) }}</span>
                            </div>
                            @if($workOrder->invoice->balance_due > 0)
                                <div class="flex justify-between text-warning">
                                    <span>Balance Due</span>
                                    <span class="font-bold">UGX {{ number_format($workOrder->invoice->balance_due) }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-base-content/50 mb-4">No invoice created yet</p>
                        @can('create invoices')
                            @if(in_array($workOrder->status, ['ready', 'delivered']))
                                <a href="{{ route('invoices.create', ['work_order' => $workOrder->id]) }}" class="btn btn-primary btn-sm w-full">
                                    Create Invoice
                                </a>
                            @endif
                        @endcan
                    @endif
                </div>
            </div>

            <!-- Job Details -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Details</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Type</span>
                            <span class="badge badge-ghost">{{ ucfirst($workOrder->type) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Priority</span>
                            <span class="badge badge-{{ $workOrder->priority_color }}">{{ ucfirst($workOrder->priority) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Created By</span>
                            <span>{{ $workOrder->createdBy?->name ?? 'System' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    @if($showAssignModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Update Assignment</h3>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Service Bay</span>
                    </label>
                    <select wire:model="selectedBay" class="select select-bordered w-full">
                        <option value="">Not assigned</option>
                        @foreach($this->availableBays as $bay)
                            <option value="{{ $bay->id }}">{{ $bay->name }} ({{ $bay->status }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Technician</span>
                    </label>
                    <select wire:model="selectedTechnician" class="select select-bordered w-full">
                        <option value="">Not assigned</option>
                        @foreach($this->technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-action">
                    <button wire:click="$set('showAssignModal', false)" class="btn btn-ghost">Cancel</button>
                    <button wire:click="assignBayAndTechnician" class="btn btn-primary">Save</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showAssignModal', false)"></div>
        </div>
    @endif
</div>
