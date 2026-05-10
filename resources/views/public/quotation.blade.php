<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quotation {{ $quotation->quotation_number }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-base-200" data-theme="light">

<div class="max-w-3xl mx-auto p-4 sm:p-8">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-6">{{ session('error') }}</div>
    @endif

    {{-- Header card --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold font-mono">{{ $quotation->quotation_number }}</h1>
                    <p class="text-base-content/60 mt-1">Quotation from your service centre</p>
                </div>
                <div class="text-right">
                    <span class="badge badge-{{ $quotation->status_color }} badge-lg">{{ ucfirst($quotation->status) }}</span>
                    @if($quotation->valid_until)
                        <p class="text-sm mt-2 {{ $quotation->valid_until->isPast() ? 'text-error' : 'text-base-content/60' }}">
                            Valid until: {{ $quotation->valid_until->format('d M Y') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="divider"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-base-content/60 font-medium mb-1">Customer</p>
                    <p class="font-semibold">{{ $quotation->customer->name }}</p>
                    <p class="text-base-content/60">{{ $quotation->customer->phone }}</p>
                    @if($quotation->customer->email)
                        <p class="text-base-content/60">{{ $quotation->customer->email }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-base-content/60 font-medium mb-1">Vehicle</p>
                    <p class="font-semibold">{{ $quotation->workOrder->vehicle->registration_number }}</p>
                    <p class="text-base-content/60">
                        {{ $quotation->workOrder->vehicle->year }}
                        {{ $quotation->workOrder->vehicle->make }}
                        {{ $quotation->workOrder->vehicle->model }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Line Items --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Items & Services</h2>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotation->items->sortBy('sort_order') as $item)
                            <tr>
                                <td>
                                    <div>
                                        <span>{{ $item->description }}</span>
                                        <span class="badge badge-{{ $item->item_type === 'labor' ? 'primary' : 'secondary' }} badge-xs ml-1">
                                            {{ ucfirst($item->item_type) }}
                                        </span>
                                    </div>
                                    @if($item->vat_applicable)
                                        <p class="text-xs text-warning mt-0.5">VAT applicable ({{ $item->vat_rate }}%)</p>
                                    @endif
                                </td>
                                <td class="text-right">{{ $item->quantity }}</td>
                                <td class="text-right">UGX {{ number_format($item->unit_price) }}</td>
                                <td class="text-right font-medium">UGX {{ number_format($item->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right font-semibold">Subtotal</td>
                            <td class="text-right font-semibold">UGX {{ number_format($quotation->subtotal) }}</td>
                        </tr>
                        @if($quotation->vat_amount > 0)
                            <tr>
                                <td colspan="3" class="text-right text-base-content/60">VAT ({{ $quotation->vat_rate }}%)</td>
                                <td class="text-right text-base-content/60">UGX {{ number_format($quotation->vat_amount) }}</td>
                            </tr>
                        @endif
                        <tr class="text-lg">
                            <td colspan="3" class="text-right font-bold">Total</td>
                            <td class="text-right font-bold">UGX {{ number_format($quotation->total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($quotation->notes)
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <h2 class="card-title text-base">Notes</h2>
                <p class="text-base-content/80 whitespace-pre-wrap">{{ $quotation->notes }}</p>
            </div>
        </div>
    @endif

    {{-- Terms --}}
    @if($quotation->terms_and_conditions)
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                <h2 class="card-title text-base">Terms & Conditions</h2>
                <p class="text-base-content/80 whitespace-pre-wrap text-sm">{{ $quotation->terms_and_conditions }}</p>
            </div>
        </div>
    @endif

    {{-- Customer Action Buttons --}}
    @if($quotation->canBeApproved())
        <div class="card bg-success/10 border border-success shadow-sm mb-6">
            <div class="card-body">
                <h2 class="font-bold text-lg">Your Response</h2>
                <p class="text-base-content/70 text-sm mb-4">
                    Please review the quotation above and let us know if you'd like to proceed.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="{{ route('quotations.public.approve', $quotation->approval_token) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="btn btn-success w-full">
                            ✓ Approve & Proceed
                        </button>
                    </form>
                    <div class="flex-1">
                        <button onclick="document.getElementById('reject-form').classList.toggle('hidden')"
                            class="btn btn-outline btn-error w-full">
                            ✕ Reject
                        </button>
                    </div>
                </div>

                <div id="reject-form" class="hidden mt-4">
                    <form method="POST" action="{{ route('quotations.public.reject', $quotation->approval_token) }}">
                        @csrf
                        @error('rejection_reason') <p class="text-error text-xs mb-2">{{ $message }}</p> @enderror
                        <textarea name="rejection_reason" rows="3" required minlength="5"
                            class="textarea textarea-bordered w-full mb-3"
                            placeholder="Please tell us why you're rejecting this quotation..."></textarea>
                        <button type="submit" class="btn btn-error w-full">Submit Rejection</button>
                    </form>
                </div>
            </div>
        </div>
    @elseif($quotation->isApproved())
        <div class="alert alert-success mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>This quotation has been approved. Our team will be in touch shortly.</span>
        </div>
    @elseif($quotation->isRejected())
        <div class="alert alert-error mb-6">
            <span>This quotation was rejected. Our team will contact you with a revised offer.</span>
        </div>
    @elseif($quotation->isExpired())
        <div class="alert alert-warning mb-6">
            <span>This quotation has expired. Please contact us for an updated quote.</span>
        </div>
    @endif

    <p class="text-center text-xs text-base-content/40 mt-8">
        This quotation is confidential and intended only for the addressee.
    </p>
</div>

</body>
</html>
