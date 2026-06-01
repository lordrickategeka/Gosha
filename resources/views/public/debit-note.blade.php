<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Debit Note {{ $debitNote->debit_note_number }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-base-200" data-theme="light">

<div class="max-w-4xl mx-auto p-4 sm:p-8">

    @if(session('success'))
        <div class="alert alert-success mb-6">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-6">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="alert alert-info mb-6">
        <span>
            Additional work was discovered during repair. Please review each debit note item below and approve or reject per item before submitting your response.
        </span>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold font-mono">{{ $debitNote->debit_note_number }}</h1>
                    <p class="text-base-content/60 mt-1">Debit note request for additional work</p>
                </div>
                <div class="text-right">
                    <span class="badge badge-{{ $debitNote->status_color }} badge-lg">{{ ucfirst(str_replace('_', ' ', $debitNote->status)) }}</span>
                </div>
            </div>

            <div class="divider"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-base-content/60 font-medium mb-1">Customer</p>
                    <p class="font-semibold">{{ $debitNote->customer->name }}</p>
                    <p class="text-base-content/60">{{ $debitNote->customer->phone }}</p>
                </div>
                <div>
                    <p class="text-base-content/60 font-medium mb-1">Vehicle</p>
                    <p class="font-semibold">{{ $debitNote->workOrder->vehicle->registration_number }}</p>
                    <p class="text-base-content/60">
                        {{ $debitNote->workOrder->vehicle->year }}
                        {{ $debitNote->workOrder->vehicle->make }}
                        {{ $debitNote->workOrder->vehicle->model }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Debit Note Items</h2>

            @if($debitNote->canBeRespondedTo())
                <form method="POST" action="{{ route('debit-notes.public.submit', $debitNote->approval_token) }}">
                    @csrf

                    <div class="mb-4">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" name="approve_all" value="1" class="checkbox checkbox-success" />
                            <span class="label-text font-medium">Approve all items</span>
                        </label>
                    </div>

                    <div class="space-y-4">
                        @foreach($debitNote->items as $item)
                            <div class="border border-base-300 rounded-xl p-4">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">{{ $item->description }}</p>
                                        <p class="text-xs text-base-content/60">
                                            {{ ucfirst($item->item_type) }} • Qty: {{ number_format($item->quantity, 2) }} •
                                            Unit Price: UGX {{ number_format($item->unit_price) }} •
                                            Line Total: UGX {{ number_format($item->total) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="label cursor-pointer justify-start gap-2 border rounded-lg px-3 py-2">
                                        <input type="radio" name="items[{{ $item->id }}][decision]" value="approved" class="radio radio-success" required />
                                        <span>Approve</span>
                                    </label>
                                    <label class="label cursor-pointer justify-start gap-2 border rounded-lg px-3 py-2">
                                        <input type="radio" name="items[{{ $item->id }}][decision]" value="rejected" class="radio radio-error" required />
                                        <span>Reject</span>
                                    </label>
                                </div>

                                <div class="mt-3">
                                    <textarea
                                        name="items[{{ $item->id }}][rejection_reason]"
                                        class="textarea textarea-bordered w-full"
                                        rows="2"
                                        placeholder="If rejecting, provide reason (optional here but recommended)."></textarea>
                                </div>

                                @error('items.'.$item->id.'.decision')
                                    <p class="text-error text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn btn-primary">Submit Response</button>
                    </div>
                </form>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Decision</th>
                                <th>Reason</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($debitNote->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td>
                                        <span class="badge {{ $item->customer_decision === 'approved' ? 'badge-success' : ($item->customer_decision === 'rejected' ? 'badge-error' : 'badge-ghost') }}">
                                            {{ ucfirst($item->customer_decision) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->rejection_reason ?: '—' }}</td>
                                    <td class="text-right">UGX {{ number_format($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="text-base">
                                <td colspan="3" class="text-right font-bold">Total</td>
                                <td class="text-right font-bold">UGX {{ number_format($debitNote->total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

</body>
</html>
