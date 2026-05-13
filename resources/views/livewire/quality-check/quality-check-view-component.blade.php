<div class="container mx-auto px-4 py-6">
    {{-- Header Section --}}
    <div class="bg-base-200 rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold">Quality Check Report</h1>
                <p class="text-sm text-base-content/70">Work Order: {{ $qualityCheck->workOrder->order_number }}</p>
            </div>
            <div class="flex gap-2">
                <div class="badge badge-lg {{ $qualityCheck->status === 'passed' ? 'badge-success' : 'badge-warning' }}">
                    {{ strtoupper(str_replace('_', ' ', $qualityCheck->status)) }}
                </div>
                @can('quality-check.download-pdf')
                    <button wire:click="downloadPdf" class="btn btn-sm btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PDF
                    </button>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="font-semibold">Customer:</span> {{ $qualityCheck->customer->name }}
            </div>
            <div>
                <span class="font-semibold">Vehicle:</span> {{ $qualityCheck->vehicle->model }} ({{ $qualityCheck->vehicle->registration_number }})
            </div>
            <div>
                <span class="font-semibold">Mileage:</span> {{ number_format($qualityCheck->vehicle->current_mileage) }} km
            </div>
            <div>
                <span class="font-semibold">VIN:</span> {{ $qualityCheck->vehicle->vin_number ?? 'N/A' }}
            </div>
            <div>
                <span class="font-semibold">Inspection Date:</span> {{ $qualityCheck->inspection_date?->format('M d, Y') ?? 'N/A' }}
            </div>
            <div>
                <span class="font-semibold">Inspector:</span> {{ $qualityCheck->inspector?->name ?? 'N/A' }}
            </div>
            <div>
                <span class="font-semibold">Completed:</span> {{ $qualityCheck->completed_at?->format('M d, Y H:i') ?? 'In Progress' }}
            </div>
        </div>
    </div>

    {{-- Issues Summary (if any) --}}
    @if($qualityCheck->status === 'has_issues')
        <div class="alert alert-warning mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <h3 class="font-bold">Items Requiring Attention</h3>
                <ul class="list-disc list-inside text-sm mt-2">
                    @foreach($qualityCheck->issues as $issue)
                        <li>{{ $issue->item_name }}: {{ $issue->remarks }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Checklist Results --}}
    @foreach($qualityCheck->getItemsBySection() as $section => $items)
        <div class="bg-base-100 rounded-lg shadow mb-6">
            <div class="bg-primary text-primary-content p-4 rounded-t-lg">
                <h2 class="text-lg font-bold">{{ \App\Models\QualityCheckTemplate::SECTIONS[$section] ?? strtoupper($section) }}</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th class="w-2/5">Item</th>
                            <th class="w-1/6 text-center">Status</th>
                            <th class="w-1/2">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td class="text-center">
                                    @if($item->status === 'ok')
                                        <span class="badge badge-success">OK</span>
                                    @elseif($item->status === 'needs_attention')
                                        <span class="badge badge-warning">Needs Attention</span>
                                    @else
                                        <span class="badge badge-ghost">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $item->status === 'needs_attention' ? 'text-warning font-semibold' : '' }}">
                                        {{ $item->remarks ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    {{-- General Notes --}}
    @if($qualityCheck->general_notes)
        <div class="bg-base-100 rounded-lg shadow mb-6 p-6">
            <h3 class="text-lg font-bold mb-4">General Notes / Observations</h3>
            <p class="text-base-content/80 whitespace-pre-wrap">{{ $qualityCheck->general_notes }}</p>
        </div>
    @endif

    {{-- Signed Document Section --}}
    @can('quality-check.upload-signed')
        <div class="bg-base-100 rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-bold mb-4">Signed Document</h3>
            
            @if($qualityCheck->signed_file_path)
                <div class="flex items-center justify-between bg-base-200 p-4 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold">Signed document uploaded</p>
                            <a href="{{ Storage::url($qualityCheck->signed_file_path) }}" target="_blank" class="text-sm text-primary hover:underline">
                                View Document
                            </a>
                        </div>
                    </div>
                    <button wire:click="deleteSignedDocument" 
                            wire:confirm="Are you sure you want to delete this signed document?"
                            class="btn btn-sm btn-error btn-outline">
                        Delete
                    </button>
                </div>
            @else
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Upload signed quality check document (PDF, JPG, PNG - Max 5MB)</span>
                    </label>
                    <input type="file" 
                           wire:model="signedDocument" 
                           accept=".pdf,.jpg,.jpeg,.png"
                           class="file-input file-input-bordered w-full">
                    @error('signedDocument') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                @if($signedDocument)
                    <button wire:click="uploadSignedDocument" class="btn btn-primary mt-4">
                        Upload Document
                    </button>
                @endif
            @endif
        </div>
    @endcan

    {{-- Actions --}}
    <div class="flex gap-4 justify-between">
        <a href="{{ route('work-orders.show', $qualityCheck->workOrder) }}" class="btn btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Work Order
        </a>

        @if($qualityCheck->isEditable())
            <a href="{{ route('quality-checks.edit', $qualityCheck) }}" class="btn btn-primary">
                Edit Quality Check
            </a>
        @endif
    </div>
</div>
