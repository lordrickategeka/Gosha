<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('work-orders.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold font-mono">{{ $workOrder->order_number }}</h1>
                    <span class="badge badge-{{ $workOrder->status_color }}">
                        {{ str_replace('_', ' ', ucfirst($workOrder->status)) }}
                    </span>
                    @if ($workOrder->is_combo)
                        <span class="badge badge-accent">COMBO</span>
                    @endif
                    @if ($workOrder->priority === 'urgent')
                        <span class="badge badge-error">URGENT</span>
                    @endif
                </div>
                <p class="text-base-content/60">Created {{ $workOrder->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @can('change_work_order_status')
                @if ($workOrder->canStart())
                    <button wire:click="startWork" class="btn btn-primary btn-sm">
                        Start Work
                    </button>
                @elseif(in_array($workOrder->status, ['open', 'quoted']) && $this->latestQuotation && !$this->latestQuotation->isApproved())
                    <div class="tooltip tooltip-left" data-tip="Waiting for customer to approve the quotation">
                        <button disabled class="btn btn-primary btn-sm btn-disabled">
                            Start Work
                        </button>
                    </div>
                @endif
                @if ($workOrder->status === 'in_progress')
                    <button wire:click="moveToQualityCheck" class="btn btn-warning btn-sm">
                        Quality Check
                    </button>
                @endif
                @if ($workOrder->status === 'quality_check')
                    <button wire:click="markReady" class="btn btn-success btn-sm">
                        Mark Ready
                    </button>
                @endif
                @if ($workOrder->canDeliver())
                    <button wire:click="deliver" class="btn btn-success btn-sm">
                        Deliver
                    </button>
                @endif
            @endcan

            @can('edit_work_orders')
                <a href="{{ route('work-orders.edit', $workOrder) }}" class="app-btn app-btn-ghost app-btn-sm">Edit</a>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7h8m-8 4h8m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-lg">{{ $workOrder->vehicle->registration_number }}</p>
                                    <p class="text-sm text-base-content/60">
                                        {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }}
                                        {{ $workOrder->vehicle->model }}
                                        @if ($workOrder->vehicle->color)
                                            <span class="text-base-content/40">•
                                                {{ $workOrder->vehicle->color }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if ($workOrder->mileage_in)
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
                                    @if ($workOrder->customer->email)
                                        <p class="text-sm text-base-content/60">{{ $workOrder->customer->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Notes -->
            @if ($workOrder->customer_notes)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-lg">Customer Notes</h3>
                        <p class="text-base-content/80">{{ $workOrder->customer_notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Tabs for Job Details and Quality Check -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div role="tablist" class="app-pill-tabs mb-6">
                        <button type="button"
                                wire:click="$set('activeTab', 'job-items')"
                                class="app-pill-tab {{ $activeTab === 'job-items' ? 'is-active' : '' }}">
                            Job Items
                            <span class="app-pill-count">{{ $workOrder->items->count() }}</span>
                        </button>

                        <button type="button"
                                wire:click="$set('activeTab', 'parts-intelligence')"
                                class="app-pill-tab {{ $activeTab === 'parts-intelligence' ? 'is-active' : '' }}">
                            Parts Intelligence
                        </button>

                        @if ($workOrder->status === 'quality_check')
                            @can('quality-check.view')
                                <button type="button"
                                        wire:click="$set('activeTab', 'quality-checklist')"
                                        class="app-pill-tab {{ $activeTab === 'quality-checklist' ? 'is-active' : '' }}">
                                    Quality Checklist
                                </button>
                            @else
                                <button type="button"
                                    class="app-pill-tab cursor-not-allowed opacity-50"
                                    disabled
                                    title="You do not have permission to access the quality checklist">
                                    Quality Checklist
                                </button>
                            @endcan
                        @else
                            <button type="button"
                                class="app-pill-tab cursor-not-allowed opacity-50"
                                disabled
                                title="Quality checklist is available only when the work order is in Quality Check status">
                                Quality Checklist
                            </button>
                        @endif
                    </div>

                    @if ($activeTab === 'job-items')
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="card-title text-lg">Job Items</h3>
                        @can('create_quotations')
                            @if (in_array($workOrder->status, ['open', 'quoted']))
                                @if ($this->latestQuotation)
                                    <a href="{{ route('quotations.show', $this->latestQuotation) }}"
                                        class="btn btn-outline btn-sm">
                                        View Quotation
                                        <span
                                            class="badge badge-{{ $this->latestQuotation->status_color }} badge-sm ml-1">{{ ucfirst($this->latestQuotation->status) }}</span>
                                    </a>
                                @else
                                    @php
                                        $quotationCreateRouteName = null;

                                        if (\Illuminate\Support\Facades\Route::has('work-orders.quotations.create')) {
                                            $quotationCreateRouteName = 'work-orders.quotations.create';
                                        } elseif (\Illuminate\Support\Facades\Route::has('quotations.create')) {
                                            $quotationCreateRouteName = 'quotations.create';
                                        }
                                    @endphp

                                    @if ($quotationCreateRouteName)
                                        <a href="{{ route($quotationCreateRouteName, $workOrder) }}"
                                            class="btn btn-primary btn-sm">
                                            Create Quotation
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-primary btn-sm" disabled
                                            title="Quotation create route is not available">
                                            Create Quotation
                                        </button>
                                    @endif
                                @endif
                            @endif
                        @endcan
                    </div>

                            {{-- Quotation approval notice --}}
                            @if ($this->latestQuotation && !$this->latestQuotation->isApproved() && in_array($workOrder->status, ['open', 'quoted']))
                                <div class="alert alert-warning mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Awaiting customer approval on quotation
                                        <a href="{{ route('quotations.show', $this->latestQuotation) }}"
                                            class="link font-mono">
                                            {{ $this->latestQuotation->quotation_number }}
                                        </a>.
                                        Work cannot start until approved.
                                    </span>
                                </div>
                            @endif

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @foreach ($workOrder->items as $index => $item)
                                        <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-200' }} hover:bg-blue-200 transition-colors duration-150">
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <span class="badge badge-{{ $item->item_type === 'labor' ? 'primary' : 'secondary' }} badge-sm">
                                                    {{ ucfirst($item->item_type) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2">
                                                <div class="text-xs text-gray-900">{{ $item->description }}</div>
                                                @if ($item->images->count())
                                                    <div class="flex gap-1 mt-1 flex-wrap">
                                                        @foreach ($item->images as $img)
                                                            <a href="{{ $img->url }}" target="_blank">
                                                                <img src="{{ $img->url }}"
                                                                    class="w-12 h-12 rounded object-cover border border-base-300"
                                                                    alt="item image" />
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-right text-xs text-gray-900">{{ $item->quantity }}</td>
                                            <td class="px-4 py-2 text-right text-xs text-gray-900">
                                                @if ($item->unit_price > 0)
                                                    UGX {{ number_format($item->unit_price) }}
                                                @else
                                                    <span class="badge badge-warning badge-sm">Unpriced</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-right text-xs font-medium text-gray-900">
                                                UGX {{ number_format($item->total) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 border-t border-gray-200">
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-right text-xs font-bold text-gray-700">Subtotal:</td>
                                        <td class="px-4 py-2 text-right text-sm font-bold text-gray-900">
                                            UGX {{ number_format($workOrder->subtotal) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if ($activeTab === 'parts-intelligence')
                        <livewire:work-orders.parts-intelligence-panel :workOrder="$workOrder" :key="'parts-intelligence-'.$workOrder->id" />
                    @endif

                    @if ($activeTab === 'quality-checklist' && $workOrder->qualityCheck)
                        <!-- Quality Checklist Tab Content -->
                        <div>
                            <h3 class="card-title text-lg mb-4">Quality Checklist</h3>

                            @if ($workOrder->qualityCheck->items->isEmpty())
                                <div class="alert alert-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>No quality check items available</span>
                                </div>
                            @else
                                @foreach ($this->groupedQualityCheckItems as $sectionKey => $sectionName)
                                    <div class="mb-6">
                                        <div class="bg-primary text-primary-content p-3 rounded-t mb-2">
                                            <h4 class="font-bold">{{ $sectionName }}</h4>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th class="w-1/3">Item</th>
                                                        <th class="w-1/6 text-center">Status</th>
                                                        <th class="w-1/2">Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($workOrder->qualityCheck->items->where('section', $sectionKey) as $item)
                                                        <tr>
                                                            <td>{{ $item->item_name }}</td>
                                                            <td class="text-center">
                                                                @if ($item->status === 'ok')
                                                                    <span class="badge badge-success badge-sm">✓ OK</span>
                                                                @elseif ($item->status === 'needs_attention')
                                                                    <span class="badge badge-warning badge-sm">⚠ Needs Attention</span>
                                                                @elseif ($item->status === 'n_a')
                                                                    <span class="badge badge-ghost badge-sm">N/A</span>
                                                                @else
                                                                    <span class="badge badge-outline badge-sm">Pending</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-sm">{{ $item->remarks ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- General Notes --}}
                                @if ($workOrder->qualityCheck)
                                    <div class="mt-6 p-4 bg-base-200 rounded">
                                        <h4 class="font-bold mb-2">General Notes</h4>
                                        <p class="text-sm">{{ $workOrder->qualityCheck->general_notes ?? 'No general notes recorded.' }}</p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Debit Notes -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="card-title text-lg">Debit Note Requests</h3>
                        @if ($this->canCreateDebitNote)
                            <button wire:click="openDebitNoteModal" class="btn btn-warning btn-sm">
                                Create Debit Note Request
                            </button>
                        @else
                            <button class="btn btn-warning btn-sm btn-disabled" disabled
                                    title="Available when work is in progress, quality check, or ready">
                                Create Debit Note Request
                            </button>
                        @endif
                    </div>

                    @if ($workOrder->debitNotes->isEmpty())
                        <div class="alert">
                            <span class="text-sm text-base-content/70">No debit note requests raised yet for this work order.</span>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Debit Note</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Total</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->debitNotes as $debitNote)
                                        <tr>
                                            <td class="font-mono">{{ $debitNote->debit_note_number }}</td>
                                            <td>
                                                <span class="badge badge-{{ $debitNote->status_color }}">
                                                    {{ ucfirst(str_replace('_', ' ', $debitNote->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $debitNote->created_at?->format('d M Y H:i') }}</td>
                                            <td>UGX {{ number_format($debitNote->total) }}</td>
                                            <td class="text-right">
                                                <div class="flex justify-end gap-2">
                                                    <a href="{{ $debitNote->approvalUrl() }}" target="_blank" class="btn btn-xs btn-outline">
                                                        Open Public Review
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-xs btn-ghost"
                                                        onclick="navigator.clipboard.writeText('{{ $debitNote->approvalUrl() }}')">
                                                        Copy Link
                                                    </button>
                                                    @if (in_array($debitNote->status, ['draft', 'rejected']))
                                                        <button wire:click="resendDebitNoteRequest({{ $debitNote->id }})"
                                                            class="btn btn-xs btn-warning">
                                                            Resend
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Technician Notes -->
            @if ($workOrder->status === 'in_progress')
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title text-lg">Technician Notes</h3>
                        <textarea wire:model="technicianNotes" rows="3"
                            placeholder="Add notes about work done, findings, recommendations..." class="textarea textarea-bordered w-full">{{ $workOrder->technician_notes }}</textarea>
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
            @if ($workOrder->washOrder)
                <div class="card bg-info/10 border border-info shadow-sm">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="card-title text-lg text-info">Combo Wash Order</h3>
                                <p class="text-sm">
                                    <span class="font-mono">{{ $workOrder->washOrder->order_number }}</span>
                                    <span class="badge badge-{{ $workOrder->washOrder->status_color }} badge-sm ml-2">
                                        {{ $workOrder->washOrder->status->label() }}
                                    </span>
                                </p>
                            </div>
                            <a href="{{ route('wash-orders.show', $workOrder->washOrder) }}"
                                class="btn btn-info btn-sm">
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
                        <li
                            class="step {{ in_array($workOrder->status, ['open', 'in_progress', 'quality_check', 'ready', 'delivered']) ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Checked In</p>
                                @if ($workOrder->checked_in_at)
                                    <p class="text-xs text-base-content/60">
                                        {{ $workOrder->checked_in_at->format('d M H:i') }}</p>
                                @endif
                            </div>
                        </li>
                        <li
                            class="step {{ in_array($workOrder->status, ['in_progress', 'quality_check', 'ready', 'delivered']) ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Work Started</p>
                                @if ($workOrder->started_at)
                                    <p class="text-xs text-base-content/60">
                                        {{ $workOrder->started_at->format('d M H:i') }}</p>
                                @endif
                            </div>
                        </li>
                        <li
                            class="step {{ in_array($workOrder->status, ['quality_check', 'ready', 'delivered']) ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Quality Check</p>
                            </div>
                        </li>
                        <li
                            class="step {{ in_array($workOrder->status, ['ready', 'delivered']) ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Ready</p>
                                @if ($workOrder->completed_at)
                                    <p class="text-xs text-base-content/60">
                                        {{ $workOrder->completed_at->format('d M H:i') }}</p>
                                @endif
                            </div>
                        </li>
                        <li class="step {{ $workOrder->status === 'delivered' ? 'step-primary' : '' }}">
                            <div class="text-left">
                                <p class="font-medium">Delivered</p>
                                @if ($workOrder->delivered_at)
                                    <p class="text-xs text-base-content/60">
                                        {{ $workOrder->delivered_at->format('d M H:i') }}</p>
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
                        @can('assign_work_orders')
                            <button wire:click="$set('showAssignModal', true)" class="app-btn app-btn-ghost app-btn-xs">Edit</button>
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
                            @if ($workOrder->assignedTechnician)
                                <div class="flex items-center gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                                            <span
                                                class="text-xs">{{ substr($workOrder->assignedTechnician->name, 0, 1) }}</span>
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

                    @if ($workOrder->invoice)
                        <div class="space-y-2">
                            @php
                                $latestDebitNote = $workOrder->debitNotes->first();
                            @endphp
                            @if ($latestDebitNote && in_array($latestDebitNote->status, ['sent', 'partially_approved', 'approved']))
                                <div class="alert alert-warning py-2 text-xs">
                                    Debit note {{ $latestDebitNote->debit_note_number }} is
                                    {{ str_replace('_', ' ', $latestDebitNote->status) }}.
                                    Invoice may be updated after customer response.
                                </div>
                            @elseif($latestDebitNote && $latestDebitNote->status === 'applied')
                                <div class="alert alert-success py-2 text-xs">
                                    Latest debit note {{ $latestDebitNote->debit_note_number }} has been applied to billing.
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Invoice #</span>
                                <a href="{{ route('invoices.show', $workOrder->invoice) }}"
                                    class="link link-primary font-mono">
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
                            @if ($workOrder->invoice->balance_due > 0)
                                <div class="flex justify-between text-warning">
                                    <span>Balance Due</span>
                                    <span class="font-bold">UGX
                                        {{ number_format($workOrder->invoice->balance_due) }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-base-content/50 mb-4">No invoice created yet</p>
                        @can('create_invoices')
                            @if (in_array($workOrder->status, ['ready', 'delivered']))
                                <a href="{{ route('invoices.create', ['work_order' => $workOrder->id]) }}"
                                    class="btn btn-primary btn-sm w-full">
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
                            <span
                                class="badge badge-{{ $workOrder->priority_color }}">{{ ucfirst($workOrder->priority) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Created By</span>
                            <span>{{ $workOrder->createdBy?->name ?? 'System' }}</span>
                        </div>

                        @if(!empty($workOrder->vehicle_items_left))
                            <div class="pt-2">
                                <span class="text-base-content/60 block mb-1">Items Left In Vehicle</span>
                                <div class="space-y-1">
                                    @foreach($workOrder->vehicle_items_left as $leftItem)
                                        <div class="text-xs border border-base-300 rounded px-2 py-1">
                                            <span class="font-medium">{{ $leftItem['item_name'] ?? 'Item' }}</span>
                                            <span class="text-base-content/60">&middot; Qty {{ $leftItem['quantity'] ?? 1 }}</span>
                                            @if(!empty($leftItem['reference']))
                                                <span class="text-base-content/60">&middot; {{ $leftItem['reference'] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Debit Note Modal -->
    @if ($showDebitNoteModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-5xl">
                <h3 class="font-bold text-lg mb-4">Create Debit Note Request</h3>

                <div class="alert alert-info mb-4 text-sm">
                    Additional work discovered during ongoing work. Add the new items below, then submit to customer for review.
                </div>

                <div class="form-control mb-4">
                    <label class="label"><span class="label-text">Notes (optional)</span></label>
                    <textarea wire:model="debitNoteNotes" class="textarea textarea-bordered w-full"
                              rows="2" placeholder="Explain why this additional work is required"></textarea>
                    @error('debitNoteNotes') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="overflow-x-auto mb-3">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Discount</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($debitNoteItems as $index => $row)
                                <tr>
                                    <td>
                                        <select wire:model="debitNoteItems.{{ $index }}.item_type"
                                                class="select select-bordered select-sm w-full">
                                            <option value="labor">Labor</option>
                                            <option value="part">Part</option>
                                        </select>
                                        @error('debitNoteItems.'.$index.'.item_type') <span class="text-error text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="text"
                                            wire:model="debitNoteItems.{{ $index }}.description"
                                            class="input input-bordered input-sm w-full"
                                            placeholder="Describe additional work/item" />
                                        @error('debitNoteItems.'.$index.'.description') <span class="text-error text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01"
                                            wire:model="debitNoteItems.{{ $index }}.quantity"
                                            class="input input-bordered input-sm w-24" />
                                        @error('debitNoteItems.'.$index.'.quantity') <span class="text-error text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            wire:model="debitNoteItems.{{ $index }}.unit_price"
                                            class="input input-bordered input-sm w-32" />
                                        @error('debitNoteItems.'.$index.'.unit_price') <span class="text-error text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0"
                                            wire:model="debitNoteItems.{{ $index }}.discount"
                                            class="input input-bordered input-sm w-28" />
                                        @error('debitNoteItems.'.$index.'.discount') <span class="text-error text-xs">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="text-sm font-medium">
                                        UGX
                                        {{ number_format((float)($row['quantity'] ?? 0) * (float)($row['unit_price'] ?? 0) - (float)($row['discount'] ?? 0), 2) }}
                                    </td>
                                    <td>
                                        <button type="button" wire:click="removeDebitNoteItemRow({{ $index }})"
                                                class="btn btn-ghost btn-xs">Remove</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @error('debitNoteItems') <div class="text-error text-xs mb-2">{{ $message }}</div> @enderror

                <div class="flex justify-between items-center">
                    <button type="button" wire:click="addDebitNoteItemRow" class="btn btn-outline btn-sm">
                        + Add Line
                    </button>

                    <div class="flex gap-2">
                        <button type="button" wire:click="closeDebitNoteModal" class="btn btn-ghost">
                            Cancel
                        </button>
                        <button type="button" wire:click="createDebitNoteRequest" class="btn btn-warning">
                            Send Debit Note Request
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeDebitNoteModal"></div>
        </div>
    @endif

    <!-- Assignment Modal -->
    @if ($showAssignModal)
        <div class="modal modal-open">
            <div class="modal-box app-modal-shell border border-[var(--cream-border)] rounded-xl">
                <h3 class="font-bold text-lg mb-4">Update Assignment</h3>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Service Bay</span>
                    </label>
                    <select wire:model="selectedBay" class="app-select w-full">
                        <option value="">Not assigned</option>
                        @foreach ($this->availableBays as $bay)
                            <option value="{{ $bay->id }}">{{ $bay->name }} ({{ $bay->status }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Technician</span>
                    </label>
                    <select wire:model="selectedTechnician" class="app-select w-full">
                        <option value="">Not assigned</option>
                        @foreach ($this->technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-action app-modal-actions">
                    <button wire:click="$set('showAssignModal', false)" class="app-btn app-btn-ghost">Cancel</button>
                    <button wire:click="assignBayAndTechnician" class="app-btn app-btn-primary">Save</button>
                </div>
            </div>
            <div class="modal-backdrop app-modal-backdrop" wire:click="$set('showAssignModal', false)"></div>
        </div>
    @endif
</div>
