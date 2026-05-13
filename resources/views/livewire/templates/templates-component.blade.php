<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Templates & Packages</h1>
            <p class="text-base-content/60">Pre-configured services and wash packages</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6 overflow-x-auto">
        <div class="inline-flex items-center gap-2 rounded-2xl border border-base-300 bg-base-200/70 p-2 shadow-sm min-w-max">
            <button wire:click="$set('tab', 'service')"
                class="btn btn-sm rounded-xl border-0 {{ $tab === 'service' ? 'btn-primary shadow-md' : 'btn-ghost hover:bg-base-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Service Templates
            </button>

            <button wire:click="$set('tab', 'wash')"
                class="btn btn-sm rounded-xl border-0 {{ $tab === 'wash' ? 'btn-primary shadow-md' : 'btn-ghost hover:bg-base-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                Wash Packages
            </button>

            <button wire:click="$set('tab', 'quality')"
                class="btn btn-sm rounded-xl border-0 {{ $tab === 'quality' ? 'btn-primary shadow-md' : 'btn-ghost hover:bg-base-100' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Quality Checklist
            </button>
        </div>
    </div>

    @if($tab === 'service')
        <!-- Service Templates -->
        <div class="flex justify-end mb-4">
            <button wire:click="$set('showServiceModal', true)" class="btn btn-primary btn-sm">+ Add Template</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->serviceTemplates as $template)
                <div class="card shadow-sm border {{ $template->is_active ? 'border-success/30 bg-success/5' : 'border-base-300 bg-base-100 opacity-80' }}">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-bold">{{ $template->name }}</h3>
                                <span class="badge badge-ghost badge-sm">{{ ucfirst($template->type) }}</span>
                            </div>
                            <label class="swap">
                                <input type="checkbox" wire:click="toggleServiceStatus({{ $template->id }})" {{ $template->is_active ? 'checked' : '' }} />
                                <span class="swap-on badge badge-success badge-sm">Active</span>
                                <span class="swap-off badge badge-error badge-sm">Inactive</span>
                            </label>
                        </div>
                        @if($template->description)
                            <p class="text-sm text-base-content/60 mt-2">{{ $template->description }}</p>
                        @endif
                        <p class="text-sm mt-2">{{ $template->items_count }} items</p>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($tab === 'wash')
        <!-- Wash Packages -->
        <div class="flex justify-end mb-4">
            <button wire:click="$set('showWashModal', true)" class="btn btn-primary btn-sm">+ Add Package</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->washPackages as $package)
                <div class="card shadow-sm border {{ $package->is_active ? 'border-success/30 bg-success/5' : 'border-base-300 bg-base-100 opacity-80' }}">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-bold">{{ $package->name }}</h3>
                                <span class="badge badge-ghost badge-sm">{{ ucfirst($package->wash_type) }}</span>
                            </div>
                            <label class="swap">
                                <input type="checkbox" wire:click="toggleWashStatus({{ $package->id }})" {{ $package->is_active ? 'checked' : '' }} />
                                <span class="swap-on badge badge-success badge-sm">Active</span>
                                <span class="swap-off badge badge-error badge-sm">Inactive</span>
                            </label>
                        </div>
                        <p class="text-lg font-bold mt-2">UGX {{ number_format($package->price) }}</p>
                        @if($package->services)
                            <ul class="text-sm text-base-content/60 mt-2 space-y-1">
                                @foreach($package->services as $service)
                                    <li>• {{ $service['name'] ?? 'Service' }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Quality Checklist Templates -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <p class="text-sm text-base-content/60">Manage quality checklist items used in quality inspections.</p>
            <button wire:click="openQualityModal" class="btn btn-primary btn-sm">+ Add Checklist Item</button>
        </div>

        <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Checklist Item</th>
                        <th>Order</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->qualityTemplates as $template)
                        <tr class="{{ $template->is_active ? 'bg-success/5' : 'opacity-70' }}">
                            <td>
                                <span class="badge badge-ghost">{{ $this->qualitySections[$template->section] ?? ucfirst(str_replace('_', ' ', $template->section)) }}</span>
                            </td>
                            <td class="font-medium">{{ $template->item_name }}</td>
                            <td>{{ $template->sort_order }}</td>
                            <td>
                                @if(is_null($template->vendor_id))
                                    <span class="badge badge-info badge-sm">System Default</span>
                                @else
                                    <span class="badge badge-success badge-sm">My Template</span>
                                @endif
                            </td>
                            <td>
                                @if($template->is_active)
                                    <span class="badge badge-success badge-sm">Active</span>
                                @else
                                    <span class="badge badge-warning badge-sm">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    @if($template->vendor_id === auth()->user()->vendor_id)
                                        <button wire:click="toggleQualityStatus({{ $template->id }})" class="btn btn-ghost btn-xs">
                                            {{ $template->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                        <button wire:click="deleteQualityTemplate({{ $template->id }})" wire:confirm="Delete this checklist item?" class="btn btn-ghost btn-xs text-error">
                                            Delete
                                        </button>
                                    @else
                                        <span class="text-xs text-base-content/50">Read only</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-base-content/60">No quality checklist templates found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <!-- Service Template Modal -->
    @if($showServiceModal)
        <div class="modal modal-open">
            <div class="modal-box app-modal-shell max-w-2xl">
                <h3 class="font-bold text-lg mb-4">Create Service Template</h3>
                <form wire:submit="createServiceTemplate">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="form-control col-span-2">
                            <label class="label"><span class="label-text">Template Name *</span></label>
                            <input type="text" wire:model="serviceName" class="input input-bordered" />
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Type</span></label>
                            <select wire:model="serviceType" class="select select-bordered">
                                <option value="service">Service</option>
                                <option value="repair">Repair</option>
                                <option value="diagnostics">Diagnostics</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Description</span></label>
                        <textarea wire:model="serviceDescription" rows="2" class="textarea textarea-bordered"></textarea>
                    </div>
                    <h4 class="font-medium mb-2">Items</h4>
                    <table class="table table-sm mb-2">
                        <thead><tr><th>Type</th><th>Description</th><th>Qty</th><th>Price</th><th></th></tr></thead>
                        <tbody>
                            @foreach($serviceItems as $index => $item)
                                <tr>
                                    <td><select wire:model="serviceItems.{{ $index }}.item_type" class="select select-bordered select-xs"><option value="labor">Labor</option><option value="part">Part</option></select></td>
                                    <td><input type="text" wire:model="serviceItems.{{ $index }}.description" class="input input-bordered input-xs w-full" /></td>
                                    <td><input type="number" wire:model="serviceItems.{{ $index }}.quantity" class="input input-bordered input-xs w-16" /></td>
                                    <td><input type="number" wire:model="serviceItems.{{ $index }}.unit_price" class="input input-bordered input-xs w-24" /></td>
                                    <td><button type="button" wire:click="removeServiceItem({{ $index }})" class="btn btn-ghost btn-xs text-error">×</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" wire:click="addServiceItem" class="btn btn-ghost btn-xs">+ Add Item</button>
                    <div class="modal-action app-modal-actions">
                        <button type="button" wire:click="$set('showServiceModal', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Template</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop app-modal-backdrop" wire:click="$set('showServiceModal', false)"></div>
        </div>
    @endif

    <!-- Wash Package Modal -->
    @if($showWashModal)
        <div class="modal modal-open">
            <div class="modal-box app-modal-shell">
                <h3 class="font-bold text-lg mb-4">Create Wash Package</h3>
                <form wire:submit="createWashPackage">
                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text">Package Name *</span></label>
                        <input type="text" wire:model="washName" class="input input-bordered" />
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Wash Type</span></label>
                            <select wire:model="washType" class="select select-bordered">
                                <option value="basic">Basic</option>
                                <option value="full">Full</option>
                                <option value="premium">Premium</option>
                                <option value="detailing">Detailing</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Price (UGX) *</span></label>
                            <input type="number" wire:model="washPrice" class="input input-bordered" />
                        </div>
                    </div>
                    <h4 class="font-medium mb-2">Included Services</h4>
                    @foreach($washServices as $index => $service)
                        <div class="flex gap-2 mb-2">
                            <input type="text" wire:model="washServices.{{ $index }}.name" class="input input-bordered input-sm flex-1" placeholder="Service name" />
                            <button type="button" wire:click="removeWashService({{ $index }})" class="btn btn-ghost btn-sm text-error">×</button>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addWashService" class="btn btn-ghost btn-xs">+ Add Service</button>
                    <div class="modal-action app-modal-actions">
                        <button type="button" wire:click="$set('showWashModal', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Package</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop app-modal-backdrop" wire:click="$set('showWashModal', false)"></div>
        </div>
    @endif

    <!-- Quality Checklist Template Modal -->
    @if($showQualityModal)
        <div class="modal modal-open">
            <div class="modal-box app-modal-shell max-w-2xl rounded-2xl border border-base-300 shadow-xl p-0 overflow-hidden">
                <div class="px-6 py-5 bg-base-200/70 border-b border-base-300">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="font-bold text-xl">Add Quality Checklist Item</h3>
                            <p class="text-sm text-base-content/60 mt-1">Choose from seeded defaults or define your own section and item.</p>
                        </div>
                        <span class="badge badge-primary badge-outline">Template Builder</span>
                    </div>
                </div>

                <div class="px-6 py-5">
                <form wire:submit="createQualityTemplate">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Section *</span></label>
                            <select wire:model.live="qualitySection" class="select select-bordered w-full {{ $errors->has('qualitySection') ? 'select-error' : '' }}">
                            @foreach($this->availableQualitySections as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                            <option value="__custom__">Other (Add My Own Section)</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Sort Order</span></label>
                            <input type="number" min="0" wire:model="qualitySortOrder" class="input input-bordered w-full {{ $errors->has('qualitySortOrder') ? 'input-error' : '' }}" />
                        </div>
                    </div>

                    @if($qualitySection === '__custom__')
                        <div class="form-control mb-4">
                            <label class="label"><span class="label-text font-medium">Custom Section Name *</span></label>
                            <input type="text" wire:model="qualityCustomSection" class="input input-bordered w-full {{ $errors->has('qualityCustomSection') ? 'input-error' : '' }}" placeholder="e.g. electrical_system" />
                            @error('qualityCustomSection')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @else
                                <label class="label"><span class="label-text-alt text-base-content/60">Use lowercase with underscores for consistency.</span></label>
                            @enderror
                        </div>
                    @endif

                    <div class="form-control mb-4">
                        <label class="label"><span class="label-text font-medium">Checklist Item *</span></label>
                        <select wire:key="quality-item-select-{{ $qualitySection }}-{{ $qualityCustomSection }}" wire:model.live="qualityItemName" class="select select-bordered w-full {{ $errors->has('qualityItemName') ? 'select-error' : '' }}">
                            @foreach($this->seededQualityItems as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                            <option value="__custom__">Other (Add My Own Item)</option>
                        </select>
                    </div>

                    @if($qualityItemName === '__custom__')
                        <div class="form-control mb-4">
                            <label class="label"><span class="label-text font-medium">Custom Checklist Item *</span></label>
                            <input type="text" wire:model="qualityCustomItemName" class="input input-bordered w-full {{ $errors->has('qualityCustomItemName') ? 'input-error' : '' }}" placeholder="e.g. Fog lights condition" />
                            @error('qualityCustomItemName')
                                <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>
                    @endif

                    <div class="mt-5 p-3 rounded-xl bg-base-200/60 border border-base-300 text-sm">
                        <p class="font-medium mb-1">Preview</p>
                        <p class="text-base-content/70">
                            <span class="font-medium">Section:</span>
                            {{ $qualitySection === '__custom__' ? ($qualityCustomSection ?: 'Custom Section') : ($this->availableQualitySections[$qualitySection] ?? $qualitySection) }}
                        </p>
                        <p class="text-base-content/70">
                            <span class="font-medium">Item:</span>
                            {{ $qualityItemName === '__custom__' ? ($qualityCustomItemName ?: 'Custom Checklist Item') : ($qualityItemName ?: 'Select an item') }}
                        </p>
                    </div>

                    <div class="modal-action app-modal-actions mt-6">
                        <button type="button" wire:click="$set('showQualityModal', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary px-6">Save Item</button>
                    </div>
                </form>
                </div>
            </div>
            <div class="modal-backdrop app-modal-backdrop" wire:click="$set('showQualityModal', false)"></div>
        </div>
    @endif
</div>
