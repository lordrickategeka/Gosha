<div class="gh-page">
    <div class="gh-card gh-card--pad">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:14px;">
            <div>
                <div style="font-size:19px; font-weight:700; letter-spacing:-0.02em;">Quality Check Report</div>
                <p class="gh-muted" style="font-size:12px; margin-top:2px;">Work Order: {{ $qualityCheck->workOrder->order_number }}</p>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <span class="gh-badge {{ $qualityCheck->status === 'passed' ? 'gh-badge--success' : 'gh-badge--warning' }}">
                    {{ strtoupper(str_replace('_', ' ', $qualityCheck->status)) }}
                </span>
                @can('quality-check.download-pdf')
                    <button wire:click="downloadPdf" class="gh-btn gh-btn--primary gh-btn--sm">Download PDF</button>
                @endcan
            </div>
        </div>

        <div class="gh-grid-3" style="font-size:12.5px;">
            <div><span style="font-weight:700;">Customer:</span> {{ $qualityCheck->customer->name }}</div>
            <div><span style="font-weight:700;">Vehicle:</span> {{ $qualityCheck->vehicle->model }} ({{ $qualityCheck->vehicle->registration_number }})</div>
            <div><span style="font-weight:700;">Mileage:</span> {{ number_format($qualityCheck->vehicle->current_mileage) }} km</div>
            <div><span style="font-weight:700;">VIN:</span> {{ $qualityCheck->vehicle->vin_number ?? 'N/A' }}</div>
            <div><span style="font-weight:700;">Inspection Date:</span> {{ $qualityCheck->inspection_date?->format('M d, Y') ?? 'N/A' }}</div>
            <div><span style="font-weight:700;">Inspector:</span> {{ $qualityCheck->inspector?->name ?? 'N/A' }}</div>
            <div><span style="font-weight:700;">Completed:</span> {{ $qualityCheck->completed_at?->format('M d, Y H:i') ?? 'In Progress' }}</div>
        </div>
    </div>

    @if($qualityCheck->status === 'has_issues')
        <div class="gh-card gh-card--pad" style="border-color:var(--gh-warning);">
            <div style="font-weight:700; font-size:13.5px; color:var(--gh-warning);">Items Requiring Attention</div>
            <ul style="margin-top:8px; padding-left:18px; list-style:disc; font-size:12.5px; display:flex; flex-direction:column; gap:4px;">
                @foreach($qualityCheck->issues as $issue)
                    <li>{{ $issue->item_name }}: {{ $issue->remarks }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @foreach($qualityCheck->getItemsBySection() as $section => $items)
        <div class="gh-card" style="overflow:hidden;">
            <div style="background:var(--gh-primary); color:var(--gh-primary-content); padding:14px 18px;">
                <h2 style="font-size:14.5px; font-weight:700;">{{ \App\Domains\ServiceConfig\Models\QualityCheckTemplate::SECTIONS[$section] ?? strtoupper($section) }}</h2>
            </div>

            <div class="gh-table-scroll">
                <table class="gh-table">
                    <thead>
                        <tr>
                            <th style="width:40%;">Item</th>
                            <th style="width:16.6%; text-align:center;">Status</th>
                            <th style="width:50%;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td style="font-weight:600;">{{ $item->item_name }}</td>
                                <td style="text-align:center;">
                                    @if($item->status === 'ok')
                                        <span class="gh-badge gh-badge--success">OK</span>
                                    @elseif($item->status === 'needs_attention')
                                        <span class="gh-badge gh-badge--warning">Needs Attention</span>
                                    @else
                                        <span class="gh-badge">N/A</span>
                                    @endif
                                </td>
                                <td style="{{ $item->status === 'needs_attention' ? 'color:var(--gh-warning); font-weight:600;' : '' }}">
                                    {{ $item->remarks ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    @if($qualityCheck->general_notes)
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:12px;">General Notes / Observations</div>
            <p style="font-size:12.5px; white-space:pre-wrap;">{{ $qualityCheck->general_notes }}</p>
        </div>
    @endif

    @can('quality-check.upload-signed')
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Signed Document</div>

            @if($qualityCheck->signed_file_path)
                <div style="display:flex; align-items:center; justify-content:space-between; background:var(--gh-base-200); border-radius:var(--gh-radius); padding:14px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="color:var(--gh-success); font-size:20px;">✓</span>
                        <div>
                            <p style="font-weight:600; font-size:13px;">Signed document uploaded</p>
                            <a href="{{ Storage::url($qualityCheck->signed_file_path) }}" target="_blank" style="font-size:11.5px; color:var(--gh-primary); text-decoration:underline;">
                                View document
                            </a>
                        </div>
                    </div>
                    <button wire:click="deleteSignedDocument" wire:confirm="Are you sure you want to delete this signed document?" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">
                        Delete
                    </button>
                </div>
            @else
                <div class="gh-field">
                    <span class="gh-label">Upload signed quality check document (PDF, JPG, PNG - Max 5MB)</span>
                    <input type="file" wire:model="signedDocument" accept=".pdf,.jpg,.jpeg,.png" class="gh-input" style="width:100%;">
                    @error('signedDocument') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>

                @if($signedDocument)
                    <button wire:click="uploadSignedDocument" class="gh-btn gh-btn--primary" style="margin-top:14px;">Upload document</button>
                @endif
            @endif
        </div>
    @endcan

    <div style="display:flex; justify-content:space-between; gap:8px;">
        <a href="{{ route('work-orders.show', $qualityCheck->workOrder) }}" class="gh-btn">← Back to Work Order</a>

        @if($qualityCheck->isEditable())
            <a href="{{ route('quality-checks.edit', $qualityCheck) }}" class="gh-btn gh-btn--primary">Edit quality check</a>
        @endif
    </div>
</div>
