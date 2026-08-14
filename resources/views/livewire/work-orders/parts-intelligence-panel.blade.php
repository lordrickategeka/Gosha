<div class="gh-stack">
    <div>
        <div style="font-weight:700; font-size:15px;">Parts Intelligence</div>
        <p class="gh-muted" style="font-size:12px; margin-top:2px;">Compare supplier sources, landed costs, and fitment confidence per part line.</p>
    </div>

    @if (session()->has('success'))
        <div class="gh-badge gh-badge--success" style="display:block; padding:8px 12px; font-size:12px;">{{ session('success') }}</div>
    @endif
    @if (session()->has('warning'))
        <div class="gh-badge gh-badge--warning" style="display:block; padding:8px 12px; font-size:12px;">{{ session('warning') }}</div>
    @endif

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th class="is-index">#</th>
                        <th>Part</th>
                        <th>Qty</th>
                        <th>Saved Sources</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->partItems as $index => $item)
                        <tr>
                            <td class="is-index">{{ $index + 1 }}</td>
                            <td>
                                <div style="font-weight:700;">{{ $item->description }}</div>
                                <div class="gh-muted" style="font-size:10.5px;">Item #{{ $item->id }}</div>
                            </td>
                            <td class="gh-muted">{{ $item->quantity }}</td>
                            <td>
                                <div class="gh-muted">{{ $item->partSources->count() }} source(s)</div>
                                @if($item->partSources->where('is_recommended', true)->count())
                                    <span class="gh-badge gh-badge--success" style="margin-top:4px;">Recommended source selected</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; align-items:center; gap:6px;">
                                    <button wire:click="openSourceModal({{ $item->id }})" class="gh-btn gh-btn--sm">Add source</button>
                                    <button wire:click="getRecommendation({{ $item->id }})" class="gh-btn gh-btn--primary gh-btn--sm">Recommend</button>
                                </div>
                            </td>
                        </tr>

                        @if($item->partSources->count())
                            <tr>
                                <td colspan="5" style="background:var(--gh-base-200); border-top:1px solid var(--gh-hairline); padding:12px;">
                                    <div class="gh-card" style="overflow:hidden;">
                                        <div class="gh-table-scroll">
                                            <table class="gh-table">
                                                <thead>
                                                    <tr>
                                                        <th>Supplier</th>
                                                        <th>Part #</th>
                                                        <th>Price</th>
                                                        <th>Landed</th>
                                                        <th>Lead Time</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($item->partSources as $sIndex => $source)
                                                        <tr>
                                                            <td>
                                                                <div>{{ $source->supplier?->name ?? ($source->source_name ?? 'Manual Source') }}</div>
                                                                @if($source->is_recommended)
                                                                    <span class="gh-badge gh-badge--primary" style="margin-top:4px;">Recommended</span>
                                                                @endif
                                                            </td>
                                                            <td class="gh-muted">{{ $source->source_part_number ?? '-' }}</td>
                                                            <td>{{ number_format((float)($source->supplier_price ?? 0), 2) }}</td>
                                                            <td>{{ number_format((float)($source->total_landed_cost ?? 0), 2) }}</td>
                                                            <td class="gh-muted">{{ $source->lead_time_days ?? '-' }} day(s)</td>
                                                            <td style="text-align:right;">
                                                                <button wire:click="markRecommended({{ $source->id }})" class="gh-btn gh-btn--sm">Mark recommended</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    @if(isset($recommendations[$item->id]['recommended']) && $recommendations[$item->id]['recommended'])
                                        @php $r = $recommendations[$item->id]['recommended']; @endphp
                                        <div style="margin-top:10px; padding:10px 12px; border-radius:var(--gh-radius); border:1px solid var(--gh-primary); background:var(--gh-primary-tint);">
                                            <p style="font-size:11.5px;">
                                                <strong>Recommendation:</strong>
                                                {{ $r['is_local'] ? 'Local Source' : 'Import Source' }}
                                                &middot;
                                                <strong>Confidence:</strong> {{ $r['confidence_score'] }}%
                                                &middot;
                                                <strong>Total Landed:</strong> {{ number_format((float)$r['total_landed_cost'], 2) }}
                                            </p>
                                            @if(!empty($r['reason_codes']))
                                                <p class="gh-muted" style="font-size:11px; margin-top:4px;">Reason: {{ implode(', ', $r['reason_codes']) }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:48px 20px;">
                                <div style="font-size:15px; font-weight:600; color:var(--gh-ink-faint);">No part items found</div>
                                <p class="gh-muted" style="font-size:12px; margin-top:4px;">Add part-type line items on this work order to begin sourcing.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showSourceModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card" style="max-width:42rem; padding:0; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid var(--gh-hairline);">
                    <div class="gh-card__title">Add Supplier Source</div>
                    <p class="gh-muted" style="font-size:12px; margin-top:2px;">Manual link-based source entry (v1).</p>
                </div>

                <div class="gh-grid-2" style="padding:20px;">
                    <div class="gh-field">
                        <span class="gh-label">Supplier</span>
                        <select wire:model="sourceSupplierId" class="gh-select" style="width:100%;">
                            <option value="">Select supplier (optional)</option>
                            @foreach($this->suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('sourceSupplierId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Source name</span>
                        <input type="text" wire:model="sourceName" class="gh-input" style="width:100%;" placeholder="PartSouq / 7zap / Local Supplier">
                        @error('sourceName') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field" style="grid-column:1/-1;">
                        <span class="gh-label">Source link</span>
                        <input type="url" wire:model="sourceLink" class="gh-input" style="width:100%;" placeholder="https://...">
                        @error('sourceLink') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Part number</span>
                        <input type="text" wire:model="sourcePartNumber" class="gh-input" style="width:100%;">
                        @error('sourcePartNumber') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Supplier price</span>
                        <input type="number" step="0.01" wire:model="sourceSupplierPrice" class="gh-input" style="width:100%;">
                        @error('sourceSupplierPrice') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Shipping</span>
                        <input type="number" step="0.01" wire:model="sourceShippingCost" class="gh-input" style="width:100%;">
                        @error('sourceShippingCost') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Duty</span>
                        <input type="number" step="0.01" wire:model="sourceDutyCost" class="gh-input" style="width:100%;">
                        @error('sourceDutyCost') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Clearing</span>
                        <input type="number" step="0.01" wire:model="sourceClearingCost" class="gh-input" style="width:100%;">
                        @error('sourceClearingCost') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Margin amount</span>
                        <input type="number" step="0.01" wire:model="sourceMarginAmount" class="gh-input" style="width:100%;">
                        @error('sourceMarginAmount') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Margin %</span>
                        <input type="number" step="0.01" wire:model="sourceMarginPercent" class="gh-input" style="width:100%;">
                        @error('sourceMarginPercent') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Availability</span>
                        <input type="text" wire:model="sourceAvailability" class="gh-input" style="width:100%;" placeholder="in_stock / preorder">
                        @error('sourceAvailability') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Lead time (days)</span>
                        <input type="number" wire:model="sourceLeadTimeDays" class="gh-input" style="width:100%;">
                        @error('sourceLeadTimeDays') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div style="grid-column:1/-1;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" wire:model="sourceIsLocal">
                            <span style="font-weight:600; font-size:12.5px;">Local source</span>
                        </label>
                    </div>

                    <div class="gh-field" style="grid-column:1/-1;">
                        <span class="gh-label">Warranty</span>
                        <input type="text" wire:model="sourceWarrantyText" class="gh-input" style="width:100%;">
                        @error('sourceWarrantyText') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field" style="grid-column:1/-1;">
                        <span class="gh-label">Notes</span>
                        <textarea rows="3" wire:model="sourceNotes" class="gh-input" style="width:100%;"></textarea>
                        @error('sourceNotes') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="background:var(--gh-base-200); padding:14px 20px; display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline);">
                    <button wire:click="closeSourceModal" class="gh-btn gh-btn--sm">Cancel</button>
                    <button wire:click="saveSource" class="gh-btn gh-btn--primary gh-btn--sm">Save source</button>
                </div>
            </div>
        </div>
    @endif
</div>
