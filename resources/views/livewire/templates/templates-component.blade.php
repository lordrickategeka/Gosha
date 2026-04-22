<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Templates & Packages</h1>
            <p class="text-base-content/60">Pre-configured services and wash packages</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs tabs-boxed mb-6 w-fit">
        <button wire:click="$set('tab', 'service')" class="tab {{ $tab === 'service' ? 'tab-active' : '' }}">Service Templates</button>
        <button wire:click="$set('tab', 'wash')" class="tab {{ $tab === 'wash' ? 'tab-active' : '' }}">Wash Packages</button>
    </div>

    @if($tab === 'service')
        <!-- Service Templates -->
        <div class="flex justify-end mb-4">
            <button wire:click="$set('showServiceModal', true)" class="btn btn-primary btn-sm">+ Add Template</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->serviceTemplates as $template)
                <div class="card bg-base-100 shadow-sm">
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
    @else
        <!-- Wash Packages -->
        <div class="flex justify-end mb-4">
            <button wire:click="$set('showWashModal', true)" class="btn btn-primary btn-sm">+ Add Package</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->washPackages as $package)
                <div class="card bg-base-100 shadow-sm">
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
    @endif

    <!-- Service Template Modal -->
    @if($showServiceModal)
        <div class="modal modal-open">
            <div class="modal-box max-w-2xl">
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
                    <div class="modal-action">
                        <button type="button" wire:click="$set('showServiceModal', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Template</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showServiceModal', false)"></div>
        </div>
    @endif

    <!-- Wash Package Modal -->
    @if($showWashModal)
        <div class="modal modal-open">
            <div class="modal-box">
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
                    <div class="modal-action">
                        <button type="button" wire:click="$set('showWashModal', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Package</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showWashModal', false)"></div>
        </div>
    @endif
</div>
