<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Templates &amp; Packages</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Pre-configured services and wash packages</p>
    </div>

    <div style="display:flex; flex-wrap:wrap; gap:8px;">
        <button wire:click="$set('tab', 'service')" class="gh-chip gh-chip--round {{ $tab === 'service' ? 'is-active' : '' }}">Service Templates</button>
        <button wire:click="$set('tab', 'wash')" class="gh-chip gh-chip--round {{ $tab === 'wash' ? 'is-active' : '' }}">Wash Packages</button>
        <button wire:click="$set('tab', 'quality')" class="gh-chip gh-chip--round {{ $tab === 'quality' ? 'is-active' : '' }}">Quality Checklist</button>
    </div>

    @if($tab === 'service')
        <div style="display:flex; justify-content:flex-end;">
            <button wire:click="$set('showServiceModal', true)" class="gh-btn gh-btn--primary gh-btn--sm">+ Add template</button>
        </div>

        <div class="gh-grid-3">
            @foreach($this->serviceTemplates as $template)
                <div class="gh-card gh-card--pad" style="{{ $template->is_active ? 'border-color:var(--gh-success);' : 'opacity:.75;' }}">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:10px;">
                        <div>
                            <div style="font-weight:700; font-size:13.5px;">{{ $template->name }}</div>
                            <span class="gh-badge" style="margin-top:4px;">{{ ucfirst($template->type) }}</span>
                        </div>
                        <button type="button" wire:click="toggleServiceStatus({{ $template->id }})" class="gh-badge {{ $template->is_active ? 'gh-badge--success' : 'gh-badge--error' }}" style="cursor:pointer; border:none;">
                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </div>
                    @if($template->description)
                        <p class="gh-muted" style="font-size:11.5px; margin-top:8px;">{{ $template->description }}</p>
                    @endif
                    <p style="font-size:12px; margin-top:8px;">{{ $template->items_count }} items</p>
                </div>
            @endforeach
        </div>
    @elseif($tab === 'wash')
        <div style="display:flex; justify-content:flex-end;">
            <button wire:click="$set('showWashModal', true)" class="gh-btn gh-btn--primary gh-btn--sm">+ Add package</button>
        </div>

        <div class="gh-grid-3">
            @foreach($this->washPackages as $package)
                <div class="gh-card gh-card--pad" style="{{ $package->is_active ? 'border-color:var(--gh-success);' : 'opacity:.75;' }}">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:10px;">
                        <div>
                            <div style="font-weight:700; font-size:13.5px;">{{ $package->name }}</div>
                            <span class="gh-badge" style="margin-top:4px;">{{ ucfirst($package->wash_type) }}</span>
                        </div>
                        <button type="button" wire:click="toggleWashStatus({{ $package->id }})" class="gh-badge {{ $package->is_active ? 'gh-badge--success' : 'gh-badge--error' }}" style="cursor:pointer; border:none;">
                            {{ $package->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </div>
                    <p style="font-size:15px; font-weight:800; margin-top:8px;">UGX {{ number_format($package->price) }}</p>
                    @if($package->services)
                        <ul class="gh-muted" style="font-size:11.5px; margin-top:6px; display:flex; flex-direction:column; gap:2px;">
                            @foreach($package->services as $service)
                                <li>• {{ $service['name'] ?? 'Service' }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px;">
            <p class="gh-muted" style="font-size:12px;">Manage quality checklist items used in quality inspections.</p>
            <button wire:click="openQualityModal" class="gh-btn gh-btn--primary gh-btn--sm">+ Add checklist item</button>
        </div>

        <div class="gh-card gh-card--flush">
            <div class="gh-table-scroll">
                <table class="gh-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Checklist Item</th>
                            <th>Order</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->qualityTemplates as $template)
                            <tr style="{{ !$template->is_active ? 'opacity:.65;' : '' }}">
                                <td><span class="gh-badge">{{ $this->qualitySections[$template->section] ?? ucfirst(str_replace('_', ' ', $template->section)) }}</span></td>
                                <td style="font-weight:600;">{{ $template->item_name }}</td>
                                <td class="gh-muted">{{ $template->sort_order }}</td>
                                <td>
                                    @if(is_null($template->vendor_id))
                                        <span class="gh-badge gh-badge--info">System Default</span>
                                    @else
                                        <span class="gh-badge gh-badge--success">My Template</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="gh-badge {{ $template->is_active ? 'gh-badge--success' : 'gh-badge--warning' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td style="text-align:right;">
                                    @if($template->vendor_id === auth()->user()->vendor_id)
                                        <button wire:click="toggleQualityStatus({{ $template->id }})" class="gh-btn gh-btn--sm">{{ $template->is_active ? 'Disable' : 'Enable' }}</button>
                                        <button wire:click="deleteQualityTemplate({{ $template->id }})" wire:confirm="Delete this checklist item?" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">Delete</button>
                                    @else
                                        <span class="gh-hint">Read only</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No quality checklist templates found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Service Template Modal -->
    @if($showServiceModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad" style="max-width:42rem;">
                <div class="gh-card__title" style="margin-bottom:16px;">Create Service Template</div>
                <form wire:submit="createServiceTemplate">
                    <div class="gh-grid-2" style="margin-bottom:14px;">
                        <div class="gh-field" style="grid-column:1/-1;">
                            <span class="gh-label">Template name *</span>
                            <input type="text" wire:model="serviceName" class="gh-input" style="width:100%;">
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Type</span>
                            <select wire:model="serviceType" class="gh-select" style="width:100%;">
                                <option value="service">Service</option>
                                <option value="repair">Repair</option>
                                <option value="diagnostics">Diagnostics</option>
                            </select>
                        </div>
                    </div>
                    <div class="gh-field" style="margin-bottom:14px;">
                        <span class="gh-label">Description</span>
                        <textarea wire:model="serviceDescription" rows="2" class="gh-input" style="width:100%;"></textarea>
                    </div>
                    <div class="gh-eyebrow" style="margin-bottom:8px;">Items</div>
                    <div class="gh-table-scroll" style="margin-bottom:8px;">
                        <table class="gh-table">
                            <thead><tr><th>Type</th><th>Description</th><th>Qty</th><th>Price</th><th></th></tr></thead>
                            <tbody>
                                @foreach($serviceItems as $index => $item)
                                    <tr>
                                        <td><select wire:model="serviceItems.{{ $index }}.item_type" class="gh-select" style="width:100%;"><option value="labor">Labor</option><option value="part">Part</option></select></td>
                                        <td><input type="text" wire:model="serviceItems.{{ $index }}.description" class="gh-input" style="width:100%;"></td>
                                        <td><input type="number" wire:model="serviceItems.{{ $index }}.quantity" class="gh-input" style="width:64px;"></td>
                                        <td><input type="number" wire:model="serviceItems.{{ $index }}.unit_price" class="gh-input" style="width:96px;"></td>
                                        <td><button type="button" wire:click="removeServiceItem({{ $index }})" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">×</button></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" wire:click="addServiceItem" class="gh-btn gh-btn--sm">+ Add item</button>
                    <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                        <button type="button" wire:click="$set('showServiceModal', false)" class="gh-btn">Cancel</button>
                        <button type="submit" class="gh-btn gh-btn--primary">Create template</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showServiceModal', false)"></div>
        </div>
    @endif

    <!-- Wash Package Modal -->
    @if($showWashModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:16px;">Create Wash Package</div>
                <form wire:submit="createWashPackage">
                    <div class="gh-field" style="margin-bottom:14px;">
                        <span class="gh-label">Package name *</span>
                        <input type="text" wire:model="washName" class="gh-input" style="width:100%;">
                    </div>
                    <div class="gh-grid-2" style="margin-bottom:14px;">
                        <div class="gh-field">
                            <span class="gh-label">Wash type</span>
                            <select wire:model="washType" class="gh-select" style="width:100%;">
                                <option value="basic">Basic</option>
                                <option value="full">Full</option>
                                <option value="premium">Premium</option>
                                <option value="detailing">Detailing</option>
                            </select>
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Price (UGX) *</span>
                            <input type="number" wire:model="washPrice" class="gh-input" style="width:100%;">
                        </div>
                    </div>
                    <div class="gh-eyebrow" style="margin-bottom:8px;">Included services</div>
                    @foreach($washServices as $index => $service)
                        <div style="display:flex; gap:8px; margin-bottom:8px;">
                            <input type="text" wire:model="washServices.{{ $index }}.name" class="gh-input" style="flex:1;" placeholder="Service name">
                            <button type="button" wire:click="removeWashService({{ $index }})" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">×</button>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addWashService" class="gh-btn gh-btn--sm">+ Add service</button>
                    <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                        <button type="button" wire:click="$set('showWashModal', false)" class="gh-btn">Cancel</button>
                        <button type="submit" class="gh-btn gh-btn--primary">Create package</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showWashModal', false)"></div>
        </div>
    @endif

    <!-- Quality Checklist Template Modal -->
    @if($showQualityModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card" style="max-width:42rem; padding:0; overflow:hidden;">
                <div style="padding:20px 24px; background:var(--gh-base-200); border-bottom:1px solid var(--gh-hairline);">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:14px;">
                        <div>
                            <div class="gh-card__title">Add Quality Checklist Item</div>
                            <p class="gh-muted" style="font-size:12px; margin-top:4px;">Choose from seeded defaults or define your own section and item.</p>
                        </div>
                        <span class="gh-badge gh-badge--primary">Template Builder</span>
                    </div>
                </div>

                <div style="padding:20px 24px;">
                    <form wire:submit="createQualityTemplate">
                        <div class="gh-grid-2" style="margin-bottom:10px;">
                            <div class="gh-field">
                                <span class="gh-label">Section *</span>
                                <select wire:model.live="qualitySection" class="gh-select" style="width:100%;">
                                    @foreach($this->availableQualitySections as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                    <option value="__custom__">Other (Add My Own Section)</option>
                                </select>
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Sort order</span>
                                <input type="number" min="0" wire:model="qualitySortOrder" class="gh-input" style="width:100%;">
                            </div>
                        </div>

                        @if($qualitySection === '__custom__')
                            <div class="gh-field" style="margin-bottom:14px;">
                                <span class="gh-label">Custom section name *</span>
                                <input type="text" wire:model="qualityCustomSection" class="gh-input" style="width:100%;" placeholder="e.g. electrical_system">
                                @error('qualityCustomSection')
                                    <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span>
                                @else
                                    <span class="gh-hint">Use lowercase with underscores for consistency.</span>
                                @enderror
                            </div>
                        @endif

                        <div class="gh-field" style="margin-bottom:14px;">
                            <span class="gh-label">Checklist item *</span>
                            <select wire:key="quality-item-select-{{ $qualitySection }}-{{ $qualityCustomSection }}" wire:model.live="qualityItemName" class="gh-select" style="width:100%;">
                                @foreach($this->seededQualityItems as $item)
                                    <option value="{{ $item }}">{{ $item }}</option>
                                @endforeach
                                <option value="__custom__">Other (Add My Own Item)</option>
                            </select>
                        </div>

                        @if($qualityItemName === '__custom__')
                            <div class="gh-field" style="margin-bottom:14px;">
                                <span class="gh-label">Custom checklist item *</span>
                                <input type="text" wire:model="qualityCustomItemName" class="gh-input" style="width:100%;" placeholder="e.g. Fog lights condition">
                                @error('qualityCustomItemName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div style="margin-top:16px; padding:12px; border-radius:var(--gh-radius); background:var(--gh-base-200); border:1px solid var(--gh-hairline); font-size:12px;">
                            <p style="font-weight:600; margin-bottom:4px;">Preview</p>
                            <p class="gh-muted">
                                <strong>Section:</strong>
                                {{ $qualitySection === '__custom__' ? ($qualityCustomSection ?: 'Custom Section') : ($this->availableQualitySections[$qualitySection] ?? $qualitySection) }}
                            </p>
                            <p class="gh-muted">
                                <strong>Item:</strong>
                                {{ $qualityItemName === '__custom__' ? ($qualityCustomItemName ?: 'Custom Checklist Item') : ($qualityItemName ?: 'Select an item') }}
                            </p>
                        </div>

                        <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                            <button type="button" wire:click="$set('showQualityModal', false)" class="gh-btn">Cancel</button>
                            <button type="submit" class="gh-btn gh-btn--primary">Save item</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showQualityModal', false)"></div>
        </div>
    @endif
</div>
