<div>
    <!-- Header -->
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('wash-orders.index') }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">New Wash Order</h1>
            <p class="text-base-content/60">Add a vehicle to the wash queue</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- ─── Main Column ─── -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Customer & Vehicle Card -->
                 <livewire:customer-vehicle-selector
                    wire:model.live="customer_id"
                    :customerId="$customer_id"
                    :vehicleId="$vehicle_id"
                />

                <!-- Wash Items Card -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="card-title text-lg">Wash Items</h2>
                            <button type="button" wire:click="addItem" class="btn btn-outline btn-sm gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Item
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th class="w-36 text-right">Price (UGX)</th>
                                        <th class="w-12"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $index => $item)
                                        <tr>
                                            <td>
                                                <input
                                                    type="text"
                                                    wire:model="items.{{ $index }}.description"
                                                    placeholder="e.g. Exterior wash, Interior vacuum..."
                                                    class="input input-bordered input-sm w-full"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    wire:model="items.{{ $index }}.price"
                                                    min="0"
                                                    step="500"
                                                    class="input input-bordered input-sm w-full text-right"
                                                />
                                            </td>
                                            <td>
                                                <button type="button" wire:click="removeItem({{ $index }})" class="btn btn-ghost btn-xs text-error">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-base-content/50 py-6">
                                                No items yet — select a package above or add items manually.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(count($items) > 0)
                                    <tfoot>
                                        <tr>
                                            <td class="text-right font-medium">Total:</td>
                                            <td class="text-right font-bold">UGX {{ number_format($this->total) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notes Card -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Customer Notes</h2>
                        <textarea
                            wire:model="customer_notes"
                            rows="3"
                            placeholder="Any special instructions, paint condition notes, specific requests..."
                            class="textarea textarea-bordered w-full"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- ─── Sidebar ─── -->
            <div class="space-y-6">

                <!-- Wash Details Card -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Wash Details</h2>

                        <!-- Wash Package -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Package</span>
                                <span class="label-text-alt text-base-content/50">optional</span>
                            </label>
                            <select wire:model.live="wash_package_id" class="select select-bordered w-full">
                                <option value="">— No package —</option>
                                @foreach($this->packages as $package)
                                    <option value="{{ $package->id }}">
                                        {{ $package->name }}
                                        @if($package->price)
                                            (UGX {{ number_format($package->price) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">Selecting a package pre-fills the items.</span>
                            </label>
                        </div>

                        <!-- Wash Type -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Wash Type *</span>
                            </label>
                            <select wire:model="wash_type" class="select select-bordered w-full">
                                <option value="basic">Basic</option>
                                <option value="full">Full</option>
                                <option value="premium">Premium</option>
                                <option value="interior">Interior</option>
                                <option value="exterior">Exterior</option>
                                <option value="engine">Engine</option>
                                <option value="detailing">Detailing</option>
                            </select>
                            @error('wash_type') <span class="label-text-alt text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Priority -->
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Priority</span>
                            </label>
                            <div class="flex gap-2">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" wire:model="priority" value="normal" class="hidden peer" />
                                    <div class="btn btn-outline btn-sm w-full peer-checked:btn-primary peer-checked:border-primary">Normal</div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" wire:model="priority" value="priority" class="hidden peer" />
                                    <div class="btn btn-outline btn-sm w-full peer-checked:btn-accent peer-checked:border-accent peer-checked:text-accent-content">Priority</div>
                                </label>
                            </div>
                        </div>

                        <!-- Source -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Source</span>
                            </label>
                            <select wire:model="source" class="select select-bordered w-full">
                                <option value="walk_in">Walk-in</option>
                                <option value="appointment">Appointment</option>
                                <option value="combo">Combo (with Work Order)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Queue Position Card -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-info/10 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-sm">Will be added to queue</p>
                                <p class="text-xs text-base-content/50">Queue position assigned automatically</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add to Queue
                    </span>
                    <span wire:loading>
                        <span class="loading loading-spinner loading-sm mr-2"></span>
                        Saving...
                    </span>
                </button>

                <a href="{{ route('wash-orders.index') }}" class="btn btn-ghost w-full">Cancel</a>
            </div>
        </div>
    </form>
</div>
