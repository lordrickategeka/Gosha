<div class="space-y-4">
    {{-- Customer & Vehicle Summary --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Customer &amp; Vehicle</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-base-content/50 uppercase mb-1">Customer</p>
                    @if($this->selectedCustomer)
                        <p class="font-medium">{{ $this->selectedCustomer->name }}</p>
                        <p class="text-sm text-base-content/70">{{ $this->selectedCustomer->phone }}</p>
                    @else
                        <p class="text-base-content/50">—</p>
                    @endif
                </div>

                <div>
                    <p class="text-xs text-base-content/50 uppercase mb-1">Vehicle</p>
                    @if($this->selectedVehicle)
                        <p class="font-medium">{{ $this->selectedVehicle->registration_number }}</p>
                        <p class="text-sm text-base-content/70">
                            {{ $this->selectedVehicle->make }} {{ $this->selectedVehicle->model }}
                            @if($this->selectedVehicle->year)
                                ({{ $this->selectedVehicle->year }})
                            @endif
                        </p>
                    @else
                        <p class="text-base-content/50">—</p>
                    @endif
                </div>

                @if($customer_notes)
                    <div class="sm:col-span-2">
                        <p class="text-xs text-base-content/50 uppercase mb-1">Customer Notes</p>
                        <p class="text-sm">{{ $customer_notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Job Details Summary --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Job Details</h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-base-content/50 uppercase mb-1">Type</p>
                    <p class="font-medium capitalize">{{ $type }}</p>
                </div>

                <div>
                    <p class="text-xs text-base-content/50 uppercase mb-1">Priority</p>
                    <span class="badge badge-{{ match($priority) {
                        'low' => 'ghost',
                        'normal' => 'info',
                        'high' => 'warning',
                        'urgent' => 'error',
                        default => 'ghost'
                    } }} capitalize">
                        {{ $priority }}
                    </span>
                </div>

                @if($mileage_in)
                    <div>
                        <p class="text-xs text-base-content/50 uppercase mb-1">Mileage In</p>
                        <p class="font-medium">{{ number_format($mileage_in) }} km</p>
                    </div>
                @endif

                @if($service_bay_id)
                    <div>
                        <p class="text-xs text-base-content/50 uppercase mb-1">Service Bay</p>
                        <p class="font-medium">
                            {{ $this->serviceBays->firstWhere('id', $service_bay_id)?->name ?? 'N/A' }}
                        </p>
                    </div>
                @endif

                @if($assigned_technician_id)
                    <div>
                        <p class="text-xs text-base-content/50 uppercase mb-1">Technician</p>
                        <p class="font-medium">
                            {{ $this->technicians->firstWhere('id', $assigned_technician_id)?->name ?? 'N/A' }}
                        </p>
                    </div>
                @endif

                @if($estimated_completion)
                    <div>
                        <p class="text-xs text-base-content/50 uppercase mb-1">Est. Completion</p>
                        <p class="font-medium">
                            {{ \Carbon\Carbon::parse($estimated_completion)->format('M j, g:i A') }}
                        </p>
                    </div>
                @endif

                @if($is_combo)
                    <div class="sm:col-span-3">
                        <span class="badge badge-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Combo Service (will auto-queue for wash)
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Items Summary --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Items ({{ count($items) }})</h2>

            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-right">Qty</th>
                            @if(!$this->isJobcarder())
                                <th class="text-right">Price</th>
                                <th class="text-right">Total</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>
                                    <span class="badge badge-sm {{ $item['item_type'] === 'labor' ? 'badge-info' : 'badge-accent' }}">
                                        {{ ucfirst($item['item_type']) }}
                                    </span>
                                </td>
                                <td>{{ $item['description'] }}</td>
                                <td class="text-right">{{ $item['quantity'] }}</td>
                                @if(!$this->isJobcarder())
                                    <td class="text-right">UGX {{ number_format($item['unit_price'] ?? 0) }}</td>
                                    <td class="text-right font-medium">
                                        UGX {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0)) }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    @if(!$this->isJobcarder())
                        <tfoot>
                            <tr class="font-bold">
                                <td colspan="4" class="text-right">Subtotal:</td>
                                <td class="text-right">UGX {{ number_format($this->subtotal) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Submit Actions --}}
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-medium">Ready to create this work order?</h3>
                    <p class="text-sm text-base-content/60">A unique order number will be generated automatically.</p>
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        wire:click="previousStep"
                        class="btn btn-ghost"
                    >
                        ← Back
                    </button>
                    <button
                        type="button"
                        wire:click="save"
                        class="btn btn-primary"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="save">Create Work Order</span>
                        <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
