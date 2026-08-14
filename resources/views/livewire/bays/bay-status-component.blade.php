<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div class="gh-eyebrow">Floor management</div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em; margin-top:4px;">Bay Status</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Real-time view of service and wash bays, their occupancy, and current assignments.</p>
        </div>
        <div style="display:flex; gap:8px;">
            @can('view_wash_orders')
                <a href="{{ route('wash-orders.index') }}" class="gh-btn gh-btn--sm">View queue</a>
            @endcan
            @can('create_wash_orders')
                <a href="{{ route('wash-orders.create') }}" class="gh-btn gh-btn--primary gh-btn--sm">+ New wash order</a>
            @endcan
        </div>
    </div>

    <div class="gh-grid-2">
        <!-- Service Bays -->
        <div class="gh-stack">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight:700; font-size:14.5px;">Service Bays ({{ $this->serviceBays->count() }})</div>
                @can('manage_bays')
                    <button wire:click="createServiceBay" class="gh-btn gh-btn--primary gh-btn--sm">+ Add bay</button>
                @endcan
            </div>

            @if($this->serviceBays->count() === 0)
                <div class="gh-card gh-card--pad" style="text-align:center; padding:48px 20px;">
                    <p class="gh-muted" style="font-size:12.5px;">No service bays yet</p>
                    @can('manage_bays')
                        <button wire:click="createServiceBay" class="gh-btn gh-btn--primary gh-btn--sm" style="margin-top:10px;">Add your first service bay</button>
                    @endcan
                </div>
            @endif

            <div class="gh-stack">
                @foreach($this->serviceBays as $bay)
                    @php
                        $borderColor = $bay->status === 'available' ? 'var(--gh-success)' : ($bay->status === 'occupied' ? 'var(--gh-warning)' : 'var(--gh-error)');
                        $badgeClass = $bay->status === 'available' ? 'gh-badge--success' : ($bay->status === 'occupied' ? 'gh-badge--warning' : 'gh-badge--error');
                    @endphp
                    <div class="gh-card gh-card--pad" style="border-left:4px solid {{ $borderColor }};">
                        <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                            <div>
                                <div style="font-weight:700; font-size:13.5px;">{{ $bay->name }}</div>
                                <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                    <span class="gh-badge {{ $badgeClass }}">{{ ucfirst($bay->status) }}</span>
                                    <span class="gh-hint">{{ ucfirst($bay->bay_type) }}</span>
                                </div>
                            </div>

                            @can('manage_bays')
                                <div class="dropdown dropdown-end">
                                    <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44 border border-base-300">
                                        <li><button wire:click="editServiceBay({{ $bay->id }})">Edit</button></li>
                                        @if($bay->status !== 'available')
                                            <li><button wire:click="markServiceBayAvailable({{ $bay->id }})">Mark Available</button></li>
                                        @endif
                                        @if($bay->status !== 'maintenance')
                                            <li><button wire:click="markServiceBayMaintenance({{ $bay->id }})">Set Maintenance</button></li>
                                        @endif
                                        @if(!$bay->isOccupied())
                                            <li><button wire:click="confirmDelete({{ $bay->id }}, 'service')" style="color:var(--gh-error);">Delete</button></li>
                                        @endif
                                    </ul>
                                </div>
                            @endcan
                        </div>

                        @if($bay->currentWorkOrder)
                            <div style="margin-top:12px; padding:10px 12px; background:var(--gh-base-200); border-radius:var(--gh-radius); border:1px solid var(--gh-hairline);">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <div>
                                        <p class="is-ref" style="font-family:monospace; font-size:11px;">{{ $bay->currentWorkOrder->order_number }}</p>
                                        <p style="font-weight:700; font-size:13px;">{{ $bay->currentWorkOrder->vehicle?->registration_number }}</p>
                                        <p class="gh-muted" style="font-size:11px;">{{ $bay->currentWorkOrder->vehicle?->make }} {{ $bay->currentWorkOrder->vehicle?->model }}</p>
                                    </div>
                                    <a href="{{ route('work-orders.show', $bay->currentWorkOrder) }}" class="gh-btn gh-btn--primary gh-btn--sm">View</a>
                                </div>
                                @if($bay->currentWorkOrder->assignedTechnician)
                                    <div style="display:flex; align-items:center; gap:8px; margin-top:8px; font-size:12px;">
                                        <div class="gh-sidebar__mark" style="width:22px; height:22px; border-radius:50%; font-size:10px;">{{ substr($bay->currentWorkOrder->assignedTechnician->name, 0, 1) }}</div>
                                        <span>{{ $bay->currentWorkOrder->assignedTechnician->name }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="gh-muted" style="font-size:12px; margin-top:10px;">Ready for next vehicle</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Wash Bays -->
        <div class="gh-stack">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="font-weight:700; font-size:14.5px;">Wash Bays ({{ $this->washBays->count() }})</div>
                <div style="display:flex; gap:8px;">
                    @can('create_wash_orders')
                        <a href="{{ route('wash-orders.create') }}" class="gh-btn gh-btn--primary gh-btn--sm">+ New wash order</a>
                    @endcan
                    @can('manage_bays')
                        <button wire:click="createWashBay" class="gh-btn gh-btn--sm">+ Add bay</button>
                    @endcan
                </div>
            </div>

            @if($this->washBays->count() === 0)
                <div class="gh-card gh-card--pad" style="text-align:center; padding:48px 20px;">
                    <p class="gh-muted" style="font-size:12.5px;">No wash bays yet</p>
                    @can('manage_bays')
                        <button wire:click="createWashBay" class="gh-btn gh-btn--primary gh-btn--sm" style="margin-top:10px;">Add your first wash bay</button>
                    @endcan
                </div>
            @endif

            <div class="gh-stack">
                @foreach($this->washBays as $bay)
                    @php
                        $statusValue = $bay->status instanceof \App\Domains\Operations\Enums\WashBayStatus ? $bay->status->value : $bay->status;
                        $statusLabel = $bay->status instanceof \App\Domains\Operations\Enums\WashBayStatus ? $bay->status->label() : ucfirst($bay->status);
                        $typeLabel   = $bay->bay_type instanceof \App\Domains\Operations\Enums\WashBayType   ? $bay->bay_type->label()   : ucfirst(str_replace('_', ' ', $bay->bay_type));
                        $borderColor = $statusValue === 'available' ? 'var(--gh-success)' : ($statusValue === 'occupied' ? 'var(--gh-info)' : 'var(--gh-error)');
                        $badgeClass = $statusValue === 'available' ? 'gh-badge--success' : ($statusValue === 'occupied' ? 'gh-badge--info' : 'gh-badge--error');
                    @endphp
                    <div class="gh-card gh-card--pad" style="border-left:4px solid {{ $borderColor }};">
                        <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                            <div>
                                <div style="font-weight:700; font-size:13.5px;">{{ $bay->name }}</div>
                                <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                    <span class="gh-badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                    <span class="gh-hint">{{ $typeLabel }}</span>
                                </div>
                            </div>

                            @can('manage_bays')
                                <div class="dropdown dropdown-end">
                                    <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-44 border border-base-300">
                                        <li><button wire:click="editWashBay({{ $bay->id }})">Edit</button></li>
                                        @if($statusValue !== 'available')
                                            <li><button wire:click="markWashBayAvailable({{ $bay->id }})">Mark Available</button></li>
                                        @endif
                                        @if($statusValue !== 'maintenance')
                                            <li><button wire:click="markWashBayMaintenance({{ $bay->id }})">Set Maintenance</button></li>
                                        @endif
                                        @if(!$bay->isOccupied())
                                            <li><button wire:click="confirmDelete({{ $bay->id }}, 'wash')" style="color:var(--gh-error);">Delete</button></li>
                                        @endif
                                    </ul>
                                </div>
                            @endcan
                        </div>

                        @if($bay->currentWashOrder)
                            <div style="margin-top:12px; padding:10px 12px; background:var(--gh-base-200); border-radius:var(--gh-radius); border:1px solid var(--gh-hairline);">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <div>
                                        <p class="is-ref" style="font-family:monospace; font-size:11px;">{{ $bay->currentWashOrder->order_number }}</p>
                                        <p style="font-weight:700; font-size:13px;">{{ $bay->currentWashOrder->vehicle?->registration_number }}</p>
                                        <p class="gh-muted" style="font-size:11px;">{{ ucfirst($bay->currentWashOrder->wash_type) }}</p>
                                    </div>
                                    <a href="{{ route('wash-orders.show', $bay->currentWashOrder) }}" class="gh-btn gh-btn--primary gh-btn--sm">View</a>
                                </div>
                                @if($bay->currentWashOrder->assignedAttendant)
                                    <div style="display:flex; align-items:center; gap:8px; margin-top:8px; font-size:12px;">
                                        <div class="gh-sidebar__mark" style="width:22px; height:22px; border-radius:50%; font-size:10px;">{{ substr($bay->currentWashOrder->assignedAttendant->name, 0, 1) }}</div>
                                        <span>{{ $bay->currentWashOrder->assignedAttendant->name }}</span>
                                    </div>
                                @endif
                                @if($bay->currentWashOrder->started_at)
                                    <p class="gh-hint" style="margin-top:6px;">Started {{ $bay->currentWashOrder->started_at->diffForHumans() }}</p>
                                @endif
                            </div>
                        @else
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:10px;">
                                <p class="gh-muted" style="font-size:12px;">Ready for next vehicle</p>
                                @can('create_wash_orders')
                                    @if($statusValue === 'available')
                                        <a href="{{ route('wash-orders.create') }}" class="gh-btn gh-btn--primary gh-btn--sm">+ New wash</a>
                                    @endif
                                @endcan
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Service Bay Modal -->
    @if($showServiceBayModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">{{ $editingServiceBayId ? 'Edit Service Bay' : 'Add Service Bay' }}</div>
                <div class="gh-stack">
                    <div class="gh-field">
                        <span class="gh-label">Bay name *</span>
                        <input type="text" wire:model="serviceBayName" class="gh-input" style="width:100%;" placeholder="e.g. Service Bay 1">
                        @error('serviceBayName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Bay type *</span>
                        <select wire:model="serviceBayType" class="gh-select" style="width:100%;">
                            <option value="general">General</option>
                            <option value="electrical">Electrical</option>
                            <option value="bodywork">Bodywork</option>
                            <option value="diagnostics">Diagnostics</option>
                            <option value="ac">AC</option>
                            <option value="tyres">Tyres</option>
                        </select>
                        @error('serviceBayType') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Notes</span>
                        <textarea wire:model="serviceBayNotes" rows="2" class="gh-input" style="width:100%;" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <button wire:click="$set('showServiceBayModal', false)" class="gh-btn">Cancel</button>
                    <button wire:click="saveServiceBay" class="gh-btn gh-btn--primary">{{ $editingServiceBayId ? 'Update' : 'Create' }}</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showServiceBayModal', false)"></div>
        </div>
    @endif

    <!-- Wash Bay Modal -->
    @if($showWashBayModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">{{ $editingWashBayId ? 'Edit Wash Bay' : 'Add Wash Bay' }}</div>
                <div class="gh-stack">
                    <div class="gh-field">
                        <span class="gh-label">Bay name *</span>
                        <input type="text" wire:model="washBayName" class="gh-input" style="width:100%;" placeholder="e.g. Wash Bay 1">
                        @error('washBayName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Bay type *</span>
                        <select wire:model="washBayType" class="gh-select" style="width:100%;">
                            <option value="basic">Basic</option>
                            <option value="standard">Standard</option>
                            <option value="premium">Premium</option>
                            <option value="full_service">Full Service</option>
                            <option value="detailing">Detailing</option>
                            <option value="automated">Automated</option>
                        </select>
                        @error('washBayType') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Notes</span>
                        <textarea wire:model="washBayNotes" rows="2" class="gh-input" style="width:100%;" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <button wire:click="$set('showWashBayModal', false)" class="gh-btn">Cancel</button>
                    <button wire:click="saveWashBay" class="gh-btn gh-btn--primary">{{ $editingWashBayId ? 'Update' : 'Create' }}</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showWashBayModal', false)"></div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($confirmingDeleteId)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="color:var(--gh-error); margin-bottom:10px;">Delete Bay</div>
                <p class="gh-muted" style="font-size:12.5px;">Are you sure you want to delete this bay? This action cannot be undone.</p>
                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <button wire:click="cancelDelete" class="gh-btn">Cancel</button>
                    <button wire:click="deleteBay" class="gh-btn gh-btn--primary" style="background:var(--gh-error); border-color:var(--gh-error);">Delete</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="cancelDelete"></div>
        </div>
    @endif
</div>
