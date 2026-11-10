<div class="gh-page">
    <div><div class="gh-eyebrow">Master data</div><h1 style="font-size:24px;font-weight:700;">Templates, packages and quality checks</h1><p class="gh-muted" style="font-size:13px;margin-top:6px;">Prepare reusable job content, customer offers and inspection prompts. Choose a tab to see what belongs in each list.</p></div>
    @if($errors->any())<div class="alert alert-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(session('success'))<p role="status" class="alert alert-success">{{ session('success') }}</p>@endif
    <div style="display:flex;flex-wrap:wrap;gap:8px;" aria-label="Template lists">
        <button type="button" wire:click="$set('tab', 'service')" class="gh-chip gh-chip--round {{ $tab === 'service' ? 'is-active' : '' }}" aria-pressed="{{ $tab === 'service' ? 'true' : 'false' }}">Service templates</button>
        <button type="button" wire:click="$set('tab', 'wash')" class="gh-chip gh-chip--round {{ $tab === 'wash' ? 'is-active' : '' }}" aria-pressed="{{ $tab === 'wash' ? 'true' : 'false' }}">Wash packages</button>
        <button type="button" wire:click="$set('tab', 'quality')" class="gh-chip gh-chip--round {{ $tab === 'quality' ? 'is-active' : '' }}" aria-pressed="{{ $tab === 'quality' ? 'true' : 'false' }}">Quality checklist</button>
    </div>
    <x-service-configuration-help :topic="$tab" />
    @if($tab === 'service')
        <div style="display:flex;justify-content:flex-end;"><button type="button" wire:click="$set('showServiceModal', true)" class="gh-btn gh-btn--primary gh-btn--sm">+ Add template</button></div>
        <div class="gh-card gh-card--flush"><div class="gh-table-scroll"><table class="gh-table"><caption class="sr-only">Service templates</caption><thead><tr><th>Template</th><th>Group</th><th>Saved items</th><th>Lines total (UGX)</th><th>Status</th><th>Action</th></tr></thead><tbody>
        @forelse($this->serviceTemplates as $template)<tr wire:key="service-{{ $template->id }}"><td style="max-width:420px;white-space:normal;"><strong>{{ $template->name }}</strong><p class="gh-muted" style="margin-top:4px;">{{ $template->description ?: 'Add a clear description when preparing your next template.' }}</p></td><td>{{ ucfirst($template->category) }}</td><td>{{ $template->items_count }} labour / part lines</td><td>{{ number_format($template->total_price, 2) }}</td><td><span class="gh-badge {{ $template->is_active ? 'gh-badge--success' : 'gh-badge--warning' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span></td><td><button type="button" wire:click="toggleServiceStatus({{ $template->id }})" class="gh-btn gh-btn--sm" aria-label="{{ $template->is_active ? 'Deactivate' : 'Activate' }} {{ $template->name }}">{{ $template->is_active ? 'Deactivate' : 'Activate' }}</button></td></tr>
        @empty<tr><td colspan="6" style="padding:32px;">No service templates yet. Add a repeat job such as an oil service, with its labour and parts lines.</td></tr>@endforelse
        </tbody></table></div></div>
    @elseif($tab === 'wash')
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:flex-end;"><a href="{{ route('packages.index') }}" class="gh-btn gh-btn--sm">Manage prices, time and inclusions</a><button type="button" wire:click="$set('showWashModal', true)" class="gh-btn gh-btn--primary gh-btn--sm">+ Add package</button></div>
        <div class="gh-card gh-card--flush"><div class="gh-table-scroll"><table class="gh-table"><caption class="sr-only">Wash packages</caption><thead><tr><th>Package</th><th>Type</th><th>Included tasks</th><th>Price (UGX)</th><th>Status</th><th>Action</th></tr></thead><tbody>
        @forelse($this->washPackages as $package)<tr wire:key="wash-{{ $package->id }}"><td><strong>{{ $package->name }}</strong></td><td>{{ ucfirst(str_replace('_', ' ', $package->wash_type)) }}</td><td style="max-width:350px;white-space:normal;">{{ implode(', ', $package->includes_list) ?: 'No inclusions recorded' }}</td><td>{{ number_format($package->price, 2) }}</td><td><span class="gh-badge {{ $package->is_active ? 'gh-badge--success' : 'gh-badge--warning' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span></td><td><button type="button" wire:click="toggleWashStatus({{ $package->id }})" class="gh-btn gh-btn--sm" aria-label="{{ $package->is_active ? 'Deactivate' : 'Activate' }} {{ $package->name }}">{{ $package->is_active ? 'Deactivate' : 'Activate' }}</button></td></tr>
        @empty<tr><td colspan="6" style="padding:32px;">No wash packages yet. Start with one clearly described offer and its total price.</td></tr>@endforelse
        </tbody></table></div></div>
    @else
        <div style="display:flex;justify-content:flex-end;"><button type="button" wire:click="openQualityModal" class="gh-btn gh-btn--primary gh-btn--sm">+ Add checklist item</button></div>
        <div class="gh-card gh-card--flush"><div class="gh-table-scroll"><table class="gh-table"><caption class="sr-only">Quality checklist items</caption><thead><tr><th>Section</th><th>Check to perform</th><th>Order</th><th>Source</th><th>Status</th><th>Action</th></tr></thead><tbody>
        @forelse($this->qualityTemplates as $template)<tr wire:key="quality-{{ $template->id }}"><td>{{ $this->qualitySections[$template->section] ?? ucfirst(str_replace('_', ' ', $template->section)) }}</td><td style="white-space:normal;">{{ $template->item_name }}</td><td>{{ $template->sort_order }}</td><td>{{ is_null($template->vendor_id) ? 'System default' : 'Your business' }}</td><td><span class="gh-badge {{ $template->is_active ? 'gh-badge--success' : 'gh-badge--warning' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span></td><td>@if($template->vendor_id === auth()->user()->vendor_id)<button type="button" wire:click="toggleQualityStatus({{ $template->id }})" class="gh-btn gh-btn--sm">{{ $template->is_active ? 'Disable' : 'Enable' }}</button> <button type="button" wire:click="deleteQualityTemplate({{ $template->id }})" wire:confirm="Delete this checklist item?" class="gh-btn gh-btn--sm">Delete</button>@else<span class="gh-hint">Read only</span>@endif</td></tr>
        @empty<tr><td colspan="6" style="padding:32px;">No checklist items available. Add observable checks, such as lights working or no visible leaks.</td></tr>@endforelse
        </tbody></table></div></div>
    @endif

    <!-- Service Template Modal -->
    @if($showServiceModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad" style="max-width:42rem;">
                <div class="gh-card__title" style="margin-bottom:16px;">Create Service Template</div><p class="gh-muted" style="margin:8px 0 16px;">Add the labour and parts that should be copied to a new job. Quantities and prices can be reviewed on that job.</p>
                <form wire:submit="createServiceTemplate">@if($errors->any())<div class="alert alert-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                    <div class="gh-grid-2" style="margin-bottom:14px;">
                        <div class="gh-field" style="grid-column:1/-1;">
                            <span class="gh-label">Template name * (for example, Small-car oil service)</span>
                            <input type="text" wire:model="serviceName" class="gh-input" style="width:100%;">
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Built-in template group</span>
                            <select wire:model="serviceType" class="gh-select" style="width:100%;">
                                <option value="service">Service</option>
                                <option value="repair">Repair</option>
                                <option value="diagnostics">Diagnostics</option><option value="bodywork">Bodywork</option><option value="electrical">Electrical</option><option value="ac">Air conditioning</option><option value="tyres">Tyres</option><option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="gh-field" style="margin-bottom:14px;">
                        <span class="gh-label">Description</span>
                        <textarea wire:model="serviceDescription" rows="2" class="gh-input" style="width:100%;"></textarea>
                    </div>
                    <div class="gh-eyebrow" style="margin-bottom:8px;">Labour and parts ? add at least one line</div>
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
                <form wire:submit="createWashPackage"><div class="gh-field" style="margin-bottom:12px;"><label for="wash-duration" class="gh-label">Estimated time (minutes)</label><input id="wash-duration" type="number" wire:model="washDuration" min="5" class="gh-input" /></div>@if($errors->any())<div class="alert alert-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif<p class="gh-muted" style="margin-bottom:12px;">The price covers the whole package. Add included task names below; they are descriptions, without separate charges.</p>
                    <div class="gh-field" style="margin-bottom:14px;">
                        <span class="gh-label">Package name *</span>
                        <input type="text" wire:model="washName" class="gh-input" style="width:100%;">
                    </div>
                    <div class="gh-grid-2" style="margin-bottom:14px;">
                        <div class="gh-field">
                            <span class="gh-label">Wash type</span>
                            <select wire:model="washType" class="gh-select" style="width:100%;">
                                <option value="basic">Basic</option><option value="standard">Standard</option><option value="interior">Interior</option><option value="exterior">Exterior</option><option value="engine">Engine</option><option value="full_detail">Full detail</option><option value="custom">Custom</option>

                                <option value="premium">Premium</option>

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
                            <input type="text" placeholder="Included task, e.g. Interior vacuum" wire:model="washServices.{{ $index }}.name" class="gh-input" style="flex:1;">
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
