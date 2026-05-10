<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('work-orders.show', $quotation->workOrder) }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-bold font-mono">{{ $quotation->quotation_number }}</h1>
                    <span class="badge badge-{{ $quotation->status_color }}">{{ ucfirst($quotation->status) }}</span>
                    @if($quotation->version > 1)
                        <span class="badge badge-neutral">v{{ $quotation->version }}</span>
                    @endif
                </div>
                <p class="text-base-content/60 text-sm">
                    {{ $quotation->customer->name }} &mdash; {{ $quotation->workOrder->vehicle->registration_number }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            {{-- Edit (draft only) --}}
            @if($quotation->isDraft())
                @can('edit_quotations')
                    <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-outline btn-sm">Edit</a>
                @endcan
            @endif

            {{-- Send (draft only) --}}
            @if($quotation->isDraft())
                @can('send_quotations')
                    <button wire:click="sendToCustomer" class="btn btn-primary btn-sm"
                        wire:loading.attr="disabled" wire:target="sendToCustomer">
                        <span wire:loading.remove wire:target="sendToCustomer">Send to Customer</span>
                        <span wire:loading wire:target="sendToCustomer" class="loading loading-spinner loading-xs"></span>
                    </button>
                @endcan
            @endif

            {{-- Approve / Reject on behalf (sent or admin) --}}
            @if($quotation->canBeApproved())
                @can('approve_quotations')
                    <button wire:click="approveOnBehalf" class="btn btn-success btn-sm"
                        wire:loading.attr="disabled" wire:target="approveOnBehalf">Approve</button>
                    <button wire:click="openRejectModal" class="btn btn-error btn-sm">Reject</button>
                @endcan
            @endif

            {{-- PDF download --}}
            @can('view_quotations')
                <button wire:click="downloadPdf" class="btn btn-ghost btn-sm"
                    wire:loading.attr="disabled" wire:target="downloadPdf">
                    <span wire:loading.remove wire:target="downloadPdf">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 10v10a2 2 0 01-2 2z" />
                        </svg>
                        PDF
                    </span>
                    <span wire:loading wire:target="downloadPdf" class="loading loading-spinner loading-xs"></span>
                </button>
            @endcan
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Customer share link --}}
            @if($quotation->isSent())
                <div class="alert alert-info">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium">Share this link with the customer to approve the quotation:</p>
                        <a href="{{ $this->approvalUrl }}" target="_blank"
                            class="text-xs break-all underline">{{ $this->approvalUrl }}</a>
                    </div>
                    <a href="{{ $this->whatsappUrl }}" target="_blank"
                        class="btn btn-sm btn-success shrink-0">WhatsApp</a>
                </div>
            @endif

            {{-- Line Items --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Line Items</h2>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Supplier</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Discount</th>
                                    <th class="text-right">VAT</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($quotation->items->sortBy('sort_order') as $item)
                                    <tr>
                                        <td>
                                            <span class="badge badge-{{ $item->item_type === 'labor' ? 'primary' : 'secondary' }} badge-sm">
                                                {{ ucfirst($item->item_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $item->description }}
                                            @if($item->inventoryItem)
                                                <span class="badge badge-ghost badge-xs ml-1">{{ $item->inventoryItem->sku ?? 'inventory' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-sm text-base-content/60">{{ $item->supplier?->name ?? '—' }}</td>
                                        <td class="text-right">{{ $item->quantity }}</td>
                                        <td class="text-right">UGX {{ number_format($item->unit_price) }}</td>
                                        <td class="text-right">{{ $item->discount > 0 ? 'UGX ' . number_format($item->discount) : '—' }}</td>
                                        <td class="text-right">
                                            @if($item->vat_applicable)
                                                <span class="badge badge-warning badge-sm">{{ $item->vat_rate }}%</span>
                                            @else
                                                <span class="text-base-content/40">—</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-medium">UGX {{ number_format($item->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-right font-semibold">Subtotal:</td>
                                    <td class="text-right font-semibold">UGX {{ number_format($quotation->subtotal) }}</td>
                                </tr>
                                @if($quotation->vat_amount > 0)
                                    <tr>
                                        <td colspan="7" class="text-right text-base-content/60">VAT ({{ $quotation->vat_rate }}%):</td>
                                        <td class="text-right text-base-content/60">UGX {{ number_format($quotation->vat_amount) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="7" class="text-right font-bold text-lg">Total:</td>
                                    <td class="text-right font-bold text-lg">UGX {{ number_format($quotation->total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if($quotation->notes)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Notes</h2>
                        <p class="text-base-content/80 whitespace-pre-wrap">{{ $quotation->notes }}</p>
                    </div>
                </div>
            @endif

            {{-- Terms --}}
            @if($quotation->terms_and_conditions)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Terms & Conditions</h2>
                        <p class="text-base-content/80 whitespace-pre-wrap">{{ $quotation->terms_and_conditions }}</p>
                    </div>
                </div>
            @endif

            {{-- Rejection reason --}}
            @if($quotation->isRejected() && $quotation->rejection_reason)
                <div class="card bg-error/10 border border-error shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-error">Rejection Reason</h2>
                        <p class="text-base-content/80">{{ $quotation->rejection_reason }}</p>
                    </div>
                </div>
            @endif

            {{-- Version history --}}
            @if($quotation->revisions->count() || $quotation->parentQuotation)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Version History</h2>
                        @if($quotation->parentQuotation)
                            <p class="text-sm text-base-content/60">
                                This is a revision of
                                <a href="{{ route('quotations.show', $quotation->parentQuotation) }}" class="link">
                                    {{ $quotation->parentQuotation->quotation_number }} (v{{ $quotation->parentQuotation->version }})
                                </a>
                            </p>
                        @endif
                        @foreach($quotation->revisions->sortByDesc('version') as $rev)
                            <div class="flex items-center justify-between py-1">
                                <span class="text-sm font-mono">{{ $rev->quotation_number }}</span>
                                <span class="badge badge-{{ $rev->status_color }} badge-sm">{{ $rev->status }}</span>
                                <a href="{{ route('quotations.show', $rev) }}" class="btn btn-ghost btn-xs">View</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">

            {{-- Status & Dates --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Details</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Status</span>
                            <span class="badge badge-{{ $quotation->status_color }}">{{ ucfirst($quotation->status) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Valid Until</span>
                            <span class="{{ $quotation->valid_until && $quotation->valid_until->isPast() ? 'text-error' : '' }} font-medium">
                                {{ $quotation->valid_until?->format('d M Y') ?? '—' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Created by</span>
                            <span class="font-medium">{{ $quotation->createdBy?->name ?? '—' }}</span>
                        </div>
                        @if($quotation->sent_at)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Sent</span>
                                <span>{{ $quotation->sent_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                        @if($quotation->approved_at)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Approved</span>
                                <span class="text-success">{{ $quotation->approved_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                        @if($quotation->rejected_at)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Rejected</span>
                                <span class="text-error">{{ $quotation->rejected_at->format('d M Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Work Order link --}}
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-lg">Work Order</h2>
                    <a href="{{ route('work-orders.show', $quotation->workOrder) }}" class="link font-mono">
                        {{ $quotation->workOrder->order_number }}
                    </a>
                    <p class="text-sm text-base-content/60 mt-1">{{ $quotation->workOrder->vehicle->registration_number }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Reject Modal ═══════════════════════════════════════════════════ --}}
    @if($showRejectModal)
        <div class="modal modal-open" role="dialog">
            <div class="modal-box max-w-md">
                <button wire:click="closeRejectModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                <h3 class="font-bold text-lg mb-4">Reject Quotation</h3>
                <div>
                    <label class="label"><span class="label-text">Reason for rejection *</span></label>
                    <textarea wire:model="rejectionReason" rows="4" class="textarea textarea-bordered w-full"
                        placeholder="Explain why the quotation was rejected..."></textarea>
                    @error('rejectionReason') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="modal-action">
                    <button wire:click="closeRejectModal" class="btn btn-ghost">Cancel</button>
                    <button wire:click="rejectOnBehalf" class="btn btn-error"
                        wire:loading.attr="disabled" wire:target="rejectOnBehalf">
                        <span wire:loading.remove wire:target="rejectOnBehalf">Confirm Reject</span>
                        <span wire:loading wire:target="rejectOnBehalf" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop bg-black/50" wire:click="closeRejectModal"></div>
        </div>
    @endif
</div>
