<div class="gh-page">
    <!-- Header -->
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('work-orders.index') }}" class="gh-btn gh-btn--sm">←</a>
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">{{ $workOrder->order_number }}</span>
                    <span class="gh-badge {{ $workOrder->status_color !== 'ghost' ? 'gh-badge--'.($workOrder->status_color === 'accent' ? 'primary' : ($workOrder->status_color === 'secondary' ? 'info' : $workOrder->status_color)) : '' }}">{{ str_replace('_', ' ', ucfirst($workOrder->status)) }}</span>
                    @if ($workOrder->is_combo)
                        <span class="gh-badge gh-badge--primary">COMBO</span>
                    @endif
                    @if ($workOrder->priority === 'urgent')
                        <span class="gh-badge gh-badge--error">URGENT</span>
                    @endif
                </div>
                <p class="gh-muted" style="font-size:12px; margin:4px 0 0;">Created {{ $workOrder->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:8px;">
            @can('change_work_order_status')
                @if ($workOrder->canStart())
                    <button wire:click="startWork" class="gh-btn gh-btn--primary">Start work</button>
                @elseif(in_array($workOrder->status, ['open', 'quoted']) && $this->latestQuotation && !$this->latestQuotation->isApproved())
                    <button disabled class="gh-btn" title="Waiting for customer to approve the quotation">Start work</button>
                @endif
                @if ($workOrder->status === 'in_progress')
                    <button wire:click="moveToQualityCheck" class="gh-btn gh-btn--primary">Quality check</button>
                @endif
                @if ($workOrder->status === 'quality_check')
                    <button wire:click="markReady" class="gh-btn gh-btn--primary">Mark ready</button>
                @endif
                @if ($workOrder->canDeliver())
                    <button wire:click="deliver" class="gh-btn gh-btn--primary">Deliver</button>
                @endif
            @endcan

            @can('edit_work_orders')
                <a href="{{ route('work-orders.edit', $workOrder) }}" class="gh-btn gh-btn--sm">Edit</a>
            @endcan
        </div>
    </div>

    <div class="gh-split">
        <!-- Main Content -->
        <div class="gh-stack">
            <!-- Vehicle & Customer -->
            <div class="gh-card gh-card--pad">
                <div class="gh-grid-2">
                    <div>
                        <div class="gh-eyebrow" style="margin-bottom:8px;">Vehicle</div>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div class="gh-module-card__icon"><x-icon name="vehicles" class="gh-icon" /></div>
                            <div>
                                <p style="font-weight:700; font-size:16px;">{{ $workOrder->vehicle->registration_number }}</p>
                                <p class="gh-muted" style="font-size:12px;">
                                    {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                                    @if ($workOrder->vehicle->color)
                                        · {{ $workOrder->vehicle->color }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if ($workOrder->mileage_in)
                            <p style="font-size:12px; margin-top:8px;"><span class="gh-muted">Mileage in:</span> <b>{{ number_format($workOrder->mileage_in) }} km</b></p>
                        @endif
                    </div>

                    <div>
                        <div class="gh-eyebrow" style="margin-bottom:8px;">Customer</div>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div class="gh-module-card__icon"><x-icon name="customers" class="gh-icon" /></div>
                            <div>
                                <p style="font-weight:700;">{{ $workOrder->customer->name }}</p>
                                <p class="gh-muted" style="font-size:12px;">{{ $workOrder->customer->phone }}</p>
                                @if ($workOrder->customer->email)
                                    <p class="gh-muted" style="font-size:12px;">{{ $workOrder->customer->email }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Notes -->
            @if ($workOrder->customer_notes)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:8px;">Customer notes</div>
                    <p style="font-size:13px; color:var(--gh-ink-body);">{{ $workOrder->customer_notes }}</p>
                </div>
            @endif

            <!-- Tabs -->
            <div class="gh-card gh-card--pad">
                <div class="gh-table-toolbar__filters" style="margin-bottom:16px;">
                    <button type="button" wire:click="$set('activeTab', 'job-items')" class="gh-chip {{ $activeTab === 'job-items' ? 'is-active' : '' }}">
                        Job items <span class="gh-badge" style="margin-left:4px;">{{ $workOrder->items->count() }}</span>
                    </button>
                    <button type="button" wire:click="$set('activeTab', 'parts-intelligence')" class="gh-chip {{ $activeTab === 'parts-intelligence' ? 'is-active' : '' }}">Parts intelligence</button>
                    @if ($workOrder->status === 'quality_check')
                        @can('quality-check.view')
                            <button type="button" wire:click="$set('activeTab', 'quality-checklist')" class="gh-chip {{ $activeTab === 'quality-checklist' ? 'is-active' : '' }}">Quality checklist</button>
                        @else
                            <button type="button" class="gh-chip" disabled style="opacity:.5; cursor:not-allowed;" title="You do not have permission to access the quality checklist">Quality checklist</button>
                        @endcan
                    @else
                        <button type="button" class="gh-chip" disabled style="opacity:.5; cursor:not-allowed;" title="Quality checklist is available only when the work order is in Quality Check status">Quality checklist</button>
                    @endif
                </div>

                @if ($activeTab === 'job-items')
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                        <div class="gh-card__title">Job items</div>
                        @can('create_quotations')
                            @if (in_array($workOrder->status, ['open', 'quoted']))
                                @if ($this->latestQuotation)
                                    <a href="{{ route('quotations.show', $this->latestQuotation) }}" class="gh-btn gh-btn--sm">
                                        View quotation
                                        <span class="gh-badge gh-badge--{{ $this->latestQuotation->status_color === 'accent' ? 'primary' : $this->latestQuotation->status_color }}">{{ ucfirst($this->latestQuotation->status) }}</span>
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
                                        <a href="{{ route($quotationCreateRouteName, $workOrder) }}" class="gh-btn gh-btn--primary gh-btn--sm">Create quotation</a>
                                    @else
                                        <button type="button" class="gh-btn gh-btn--sm" disabled title="Quotation create route is not available">Create quotation</button>
                                    @endif
                                @endif
                            @endif
                        @endcan
                    </div>

                    @if ($this->latestQuotation && !$this->latestQuotation->isApproved() && in_array($workOrder->status, ['open', 'quoted']))
                        <div class="gh-note" style="margin-bottom:14px;">
                            <span class="gh-note__body">Awaiting customer approval on quotation
                                <a href="{{ route('quotations.show', $this->latestQuotation) }}" style="font-weight:700;">{{ $this->latestQuotation->quotation_number }}</a>.
                                Work cannot start until approved.
                            </span>
                        </div>
                    @endif

                    <div class="gh-card gh-card--flush">
                        <div class="gh-table-scroll">
                            <table class="gh-table">
                                <thead>
                                    <tr><th>Type</th><th>Description</th><th style="text-align:right;">Qty</th><th style="text-align:right;">Unit price</th><th style="text-align:right;">Total</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->items as $item)
                                        <tr>
                                            <td><span class="gh-badge {{ $item->item_type === 'labor' ? '' : 'gh-badge--primary' }}">{{ ucfirst($item->item_type) }}</span></td>
                                            <td>
                                                {{ $item->description }}
                                                @if ($item->images->count())
                                                    <div style="display:flex; gap:4px; margin-top:6px; flex-wrap:wrap;">
                                                        @foreach ($item->images as $img)
                                                            <a href="{{ $img->url }}" target="_blank"><img src="{{ $img->url }}" style="width:44px; height:44px; border-radius:6px; object-fit:cover; border:1px solid var(--gh-base-300);" alt="item image"></a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="is-num">{{ $item->quantity }}</td>
                                            <td class="is-num">
                                                @if ($item->unit_price > 0)
                                                    UGX {{ number_format($item->unit_price) }}
                                                @else
                                                    <span class="gh-badge gh-badge--warning">Unpriced</span>
                                                @endif
                                            </td>
                                            <td class="is-num">UGX {{ number_format($item->total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:16px; padding:12px var(--gh-pad); background:var(--gh-surface-header); border-top:1px solid var(--gh-hairline);">
                            <span style="font-weight:700; font-size:12.5px;">Subtotal</span>
                            <span style="font-weight:800; font-size:14px;">UGX {{ number_format($workOrder->subtotal) }}</span>
                        </div>
                    </div>
                @endif

                @if ($activeTab === 'parts-intelligence')
                    <livewire:work-orders.parts-intelligence-panel :workOrder="$workOrder" :key="'parts-intelligence-'.$workOrder->id" />
                @endif

                @if ($activeTab === 'quality-checklist' && $workOrder->qualityCheck)
                    <div>
                        <div class="gh-card__title" style="margin-bottom:14px;">Quality checklist</div>

                        @if ($workOrder->qualityCheck->items->isEmpty())
                            <div class="gh-note"><span class="gh-note__body">No quality check items available</span></div>
                        @else
                            @foreach ($this->groupedQualityCheckItems as $sectionKey => $sectionName)
                                <div style="margin-bottom:20px;">
                                    <div style="background:var(--gh-primary); color:var(--gh-primary-content); padding:9px 12px; border-radius:var(--gh-radius) var(--gh-radius) 0 0; font-weight:700; font-size:13px;">{{ $sectionName }}</div>
                                    <div class="gh-table-scroll">
                                        <table class="gh-table">
                                            <thead><tr><th>Item</th><th style="text-align:center;">Status</th><th>Remarks</th></tr></thead>
                                            <tbody>
                                                @foreach ($workOrder->qualityCheck->items->where('section', $sectionKey) as $item)
                                                    <tr>
                                                        <td>{{ $item->item_name }}</td>
                                                        <td style="text-align:center;">
                                                            @if ($item->status === 'ok')
                                                                <span class="gh-badge gh-badge--success">✓ OK</span>
                                                            @elseif ($item->status === 'needs_attention')
                                                                <span class="gh-badge gh-badge--warning">⚠ Needs attention</span>
                                                            @elseif ($item->status === 'n_a')
                                                                <span class="gh-badge">N/A</span>
                                                            @else
                                                                <span class="gh-badge">Pending</span>
                                                            @endif
                                                        </td>
                                                        <td style="font-size:12px;">{{ $item->remarks ?? '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach

                            <div style="padding:14px; background:var(--gh-base-200); border-radius:var(--gh-radius);">
                                <div style="font-weight:700; margin-bottom:6px; font-size:13px;">General notes</div>
                                <p style="font-size:12.5px;">{{ $workOrder->qualityCheck->general_notes ?? 'No general notes recorded.' }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Debit Notes -->
            <div class="gh-card gh-card--pad">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <div class="gh-card__title">Debit note requests</div>
                    @if ($this->canCreateDebitNote)
                        <button wire:click="openDebitNoteModal" class="gh-btn gh-btn--sm" style="color:var(--gh-warning);">Create debit note request</button>
                    @else
                        <button class="gh-btn gh-btn--sm" disabled title="Available when work is in progress, quality check, or ready">Create debit note request</button>
                    @endif
                </div>

                @if ($workOrder->debitNotes->isEmpty())
                    <p class="gh-muted" style="font-size:12.5px;">No debit note requests raised yet for this work order.</p>
                @else
                    <div class="gh-table-scroll">
                        <table class="gh-table">
                            <thead><tr><th>Debit note</th><th>Status</th><th>Created</th><th>Total</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($workOrder->debitNotes as $debitNote)
                                    <tr>
                                        <td class="is-ref">{{ $debitNote->debit_note_number }}</td>
                                        <td><span class="gh-badge gh-badge--{{ $debitNote->status_color === 'accent' ? 'primary' : $debitNote->status_color }}">{{ ucfirst(str_replace('_', ' ', $debitNote->status)) }}</span></td>
                                        <td class="gh-muted">{{ $debitNote->created_at?->format('d M Y H:i') }}</td>
                                        <td>UGX {{ number_format($debitNote->total) }}</td>
                                        <td>
                                            <div style="display:flex; justify-content:flex-end; gap:6px;">
                                                <a href="{{ $debitNote->approvalUrl() }}" target="_blank" class="gh-btn gh-btn--sm">Open review</a>
                                                <button type="button" class="gh-btn gh-btn--sm" onclick="navigator.clipboard.writeText('{{ $debitNote->approvalUrl() }}')">Copy link</button>
                                                @if (in_array($debitNote->status, ['draft', 'rejected']))
                                                    <button wire:click="resendDebitNoteRequest({{ $debitNote->id }})" class="gh-btn gh-btn--sm" style="color:var(--gh-warning);">Resend</button>
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

            <!-- Technician Notes -->
            @if ($workOrder->status === 'in_progress')
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:10px;">Technician notes</div>
                    <textarea wire:model="technicianNotes" rows="3" placeholder="Add notes about work done, findings, recommendations…" class="gh-input" style="width:100%;">{{ $workOrder->technician_notes }}</textarea>
                </div>
            @elseif($workOrder->technician_notes)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:8px;">Technician notes</div>
                    <p style="font-size:13px; color:var(--gh-ink-body);">{{ $workOrder->technician_notes }}</p>
                </div>
            @endif

            <!-- Combo Wash Order -->
            @if ($workOrder->washOrder)
                <div class="gh-card gh-card--pad" style="background:var(--gh-info-bg); border-color:var(--gh-info);">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <div class="gh-card__title" style="color:var(--gh-info);">Combo wash order</div>
                            <p style="font-size:12.5px; margin-top:4px;">
                                {{ $workOrder->washOrder->order_number }}
                                <span class="gh-badge gh-badge--info" style="margin-left:6px;">{{ $workOrder->washOrder->status->label() }}</span>
                            </p>
                        </div>
                        <a href="{{ route('wash-orders.show', $workOrder->washOrder) }}" class="gh-btn gh-btn--sm">View wash order</a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="gh-stack">
            <!-- Status Timeline -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Timeline</div>
                @php
                    $steps = [
                        ['label' => 'Checked in', 'time' => $workOrder->checked_in_at, 'done' => in_array($workOrder->status, ['open', 'in_progress', 'quality_check', 'ready', 'delivered'])],
                        ['label' => 'Work started', 'time' => $workOrder->started_at, 'done' => in_array($workOrder->status, ['in_progress', 'quality_check', 'ready', 'delivered'])],
                        ['label' => 'Quality check', 'time' => null, 'done' => in_array($workOrder->status, ['quality_check', 'ready', 'delivered'])],
                        ['label' => 'Ready', 'time' => $workOrder->completed_at, 'done' => in_array($workOrder->status, ['ready', 'delivered'])],
                        ['label' => 'Delivered', 'time' => $workOrder->delivered_at, 'done' => $workOrder->status === 'delivered'],
                    ];
                @endphp
                <div class="gh-timeline">
                    @foreach ($steps as $step)
                        <div class="gh-timeline__row">
                            <span class="gh-timeline__time">{{ $step['time']?->format('H:i') ?? ($step['done'] ? '' : '—') }}</span>
                            <div class="gh-timeline__body">
                                <span class="gh-timeline__what" style="{{ $step['done'] ? '' : 'color:var(--gh-ink-faint);' }}">{{ $step['label'] }}</span>
                                @if ($step['time'])
                                    <span class="gh-timeline__who">{{ $step['time']->format('d M Y') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Assignment -->
            <div class="gh-card gh-card--pad">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <div class="gh-card__title">Assignment</div>
                    @can('assign_work_orders')
                        <button wire:click="$set('showAssignModal', true)" class="gh-btn gh-btn--sm">Edit</button>
                    @endcan
                </div>
                <div class="gh-stack" style="gap:12px;">
                    <div>
                        <p class="gh-muted" style="font-size:11px;">Service bay</p>
                        <p style="font-weight:600; font-size:13px;">{{ $workOrder->serviceBay?->name ?? 'Not assigned' }}</p>
                    </div>
                    <div>
                        <p class="gh-muted" style="font-size:11px;">Technician</p>
                        <p style="font-weight:600; font-size:13px;">{{ $workOrder->assignedTechnician?->name ?? 'Not assigned' }}</p>
                    </div>
                </div>
            </div>

            <!-- Invoice Status -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Invoice</div>

                @if ($workOrder->invoice)
                    @php $latestDebitNote = $workOrder->debitNotes->first(); @endphp
                    @if ($latestDebitNote && in_array($latestDebitNote->status, ['sent', 'partially_approved', 'approved']))
                        <div class="gh-note" style="margin-bottom:10px;"><span class="gh-note__body" style="font-size:11.5px;">Debit note {{ $latestDebitNote->debit_note_number }} is {{ str_replace('_', ' ', $latestDebitNote->status) }}. Invoice may be updated after customer response.</span></div>
                    @elseif($latestDebitNote && $latestDebitNote->status === 'applied')
                        <div class="gh-note" style="margin-bottom:10px; background:var(--gh-success-bg); border-color:var(--gh-success);"><span class="gh-note__body" style="font-size:11.5px; color:var(--gh-success);">Latest debit note {{ $latestDebitNote->debit_note_number }} has been applied to billing.</span></div>
                    @endif
                    <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Invoice #</span><a href="{{ route('invoices.show', $workOrder->invoice) }}" class="is-ref">{{ $workOrder->invoice->invoice_number }}</a></div>
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Status</span><span class="gh-badge gh-badge--{{ $workOrder->invoice->status_color === 'accent' ? 'primary' : $workOrder->invoice->status_color }}">{{ ucfirst($workOrder->invoice->status) }}</span></div>
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Total</span><b>UGX {{ number_format($workOrder->invoice->total) }}</b></div>
                        @if ($workOrder->invoice->balance_due > 0)
                            <div style="display:flex; justify-content:space-between; color:var(--gh-warning);"><span>Balance due</span><b>UGX {{ number_format($workOrder->invoice->balance_due) }}</b></div>
                        @endif
                    </div>
                @else
                    <p class="gh-muted" style="font-size:12.5px; margin-bottom:12px;">No invoice created yet</p>
                    @can('create_invoices')
                        @if (in_array($workOrder->status, ['ready', 'delivered']))
                            <a href="{{ route('invoices.create', ['work_order' => $workOrder->id]) }}" class="gh-btn gh-btn--primary gh-btn--block">Create invoice</a>
                        @endif
                    @endcan
                @endif
            </div>

            <!-- Job Details -->
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Details</div>
                <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Type</span><span class="gh-badge">{{ ucfirst($workOrder->type) }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Priority</span><span class="gh-badge gh-badge--{{ $workOrder->priority_color === 'ghost' ? '' : $workOrder->priority_color }}">{{ ucfirst($workOrder->priority) }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Created by</span><span>{{ $workOrder->createdBy?->name ?? 'System' }}</span></div>

                    @if(!empty($workOrder->vehicle_items_left))
                        <div style="border-top:1px solid var(--gh-hairline); padding-top:9px;">
                            <span class="gh-muted" style="display:block; margin-bottom:6px;">Items left in vehicle</span>
                            <div class="gh-stack" style="gap:6px;">
                                @foreach($workOrder->vehicle_items_left as $leftItem)
                                    <div style="font-size:11px; border:1px solid var(--gh-base-300); border-radius:6px; padding:6px 8px;">
                                        <b>{{ $leftItem['item_name'] ?? 'Item' }}</b>
                                        <span class="gh-muted"> · Qty {{ $leftItem['quantity'] ?? 1 }}</span>
                                        @if(!empty($leftItem['reference']))
                                            <span class="gh-muted"> · {{ $leftItem['reference'] }}</span>
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

    <!-- Debit Note Modal -->
    @if ($showDebitNoteModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad" style="max-width:64rem;">
                <div class="gh-card__title" style="margin-bottom:14px;">Create debit note request</div>

                <div class="gh-note" style="margin-bottom:14px;"><span class="gh-note__body">Additional work discovered during ongoing work. Add the new items below, then submit to customer for review.</span></div>

                <div class="gh-field" style="margin-bottom:14px;">
                    <span class="gh-label">Notes (optional)</span>
                    <textarea wire:model="debitNoteNotes" class="gh-input" rows="2" placeholder="Explain why this additional work is required"></textarea>
                    @error('debitNoteNotes') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>

                <div class="gh-table-scroll" style="margin-bottom:12px;">
                    <table class="gh-table">
                        <thead><tr><th>Type</th><th>Description</th><th>Qty</th><th>Unit price</th><th>Discount</th><th>Total</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($debitNoteItems as $index => $row)
                                <tr>
                                    <td>
                                        <select wire:model="debitNoteItems.{{ $index }}.item_type" class="gh-select" style="width:100%;">
                                            <option value="labor">Labor</option>
                                            <option value="part">Part</option>
                                        </select>
                                        @error('debitNoteItems.'.$index.'.item_type') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="text" wire:model="debitNoteItems.{{ $index }}.description" class="gh-input" style="width:100%;" placeholder="Describe additional work/item">
                                        @error('debitNoteItems.'.$index.'.description') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" wire:model="debitNoteItems.{{ $index }}.quantity" class="gh-input" style="width:90px;">
                                        @error('debitNoteItems.'.$index.'.quantity') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" wire:model="debitNoteItems.{{ $index }}.unit_price" class="gh-input" style="width:120px;">
                                        @error('debitNoteItems.'.$index.'.unit_price') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" wire:model="debitNoteItems.{{ $index }}.discount" class="gh-input" style="width:110px;">
                                        @error('debitNoteItems.'.$index.'.discount') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                                    </td>
                                    <td class="is-num">UGX {{ number_format((float)($row['quantity'] ?? 0) * (float)($row['unit_price'] ?? 0) - (float)($row['discount'] ?? 0), 2) }}</td>
                                    <td><button type="button" wire:click="removeDebitNoteItemRow({{ $index }})" class="gh-btn gh-btn--sm">Remove</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @error('debitNoteItems') <div class="gh-hint" style="color:var(--gh-error); margin-bottom:10px;">{{ $message }}</div> @enderror

                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--gh-hairline); padding-top:14px;">
                    <button type="button" wire:click="addDebitNoteItemRow" class="gh-btn gh-btn--sm">+ Add line</button>
                    <div style="display:flex; gap:8px;">
                        <button type="button" wire:click="closeDebitNoteModal" class="gh-btn">Cancel</button>
                        <button type="button" wire:click="createDebitNoteRequest" class="gh-btn gh-btn--primary">Send debit note request</button>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeDebitNoteModal"></div>
        </div>
    @endif

    <!-- Assignment Modal -->
    @if ($showAssignModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad" style="max-width:420px;">
                <div class="gh-card__title" style="margin-bottom:16px;">Update assignment</div>

                <div class="gh-field" style="margin-bottom:14px;">
                    <span class="gh-label">Service bay</span>
                    <select wire:model="selectedBay" class="gh-select" style="width:100%;">
                        <option value="">Not assigned</option>
                        @foreach ($this->availableBays as $bay)
                            <option value="{{ $bay->id }}">{{ $bay->name }} ({{ $bay->status }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="gh-field" style="margin-bottom:18px;">
                    <span class="gh-label">Technician</span>
                    <select wire:model="selectedTechnician" class="gh-select" style="width:100%;">
                        <option value="">Not assigned</option>
                        @foreach ($this->technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px;">
                    <button wire:click="$set('showAssignModal', false)" class="gh-btn">Cancel</button>
                    <button wire:click="assignBayAndTechnician" class="gh-btn gh-btn--primary">Save</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showAssignModal', false)"></div>
        </div>
    @endif
</div>
