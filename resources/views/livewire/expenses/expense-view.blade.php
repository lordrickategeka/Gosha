<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold">{{ $expense->expense_number }}</h1>
                <p class="text-base-content/60">{{ $expense->description }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            @if($expense->isDraft() || $expense->isPendingApproval())
                @can('edit_expenses')
                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-ghost btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                @endcan
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title">Details</h2>
                        {!! $expense->status_badge !!}
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-base-content/60">Type</p>
                            <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $expense->expense_type)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Category</p>
                            <p class="font-medium">{!! $expense->category?->badge_html ?? 'N/A' !!}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Date</p>
                            <p class="font-medium">{{ $expense->expense_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-base-content/60">Payment Method</p>
                            <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</p>
                        </div>
                        @if($expense->payment_reference)
                            <div>
                                <p class="text-sm text-base-content/60">Reference</p>
                                <p class="font-medium font-mono text-sm">{{ $expense->payment_reference }}</p>
                            </div>
                        @endif
                        @if($expense->supplier)
                            <div>
                                <p class="text-sm text-base-content/60">Supplier</p>
                                <p class="font-medium">{{ $expense->supplier->name }}</p>
                            </div>
                        @endif
                        @if($expense->branch)
                            <div>
                                <p class="text-sm text-base-content/60">Branch</p>
                                <p class="font-medium">{{ $expense->branch->name }}</p>
                            </div>
                        @endif
                    </div>

                    @if($expense->notes)
                        <div class="mt-4">
                            <p class="text-sm text-base-content/60 mb-1">Notes</p>
                            <p class="text-sm">{{ $expense->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Amount Breakdown -->
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title">Amount Breakdown</h2>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Amount</span>
                            <span class="font-medium">{{ $expense->formatted_amount }}</span>
                        </div>

                        @if($expense->tax_amount)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Tax ({{ $expense->tax_percentage }}%) {{ $expense->tax_inclusive ? '(Inclusive)' : '' }}</span>
                                <span class="font-medium">{{ $expense->currency }} {{ number_format($expense->tax_amount, 2) }}</span>
                            </div>
                        @endif

                        <div class="divider my-2"></div>

                        <div class="flex justify-between text-lg">
                            <span class="font-semibold">Total</span>
                            <span class="font-bold">{{ $expense->formatted_total }}</span>
                        </div>

                        @if($expense->currency !== 'UGX')
                            <div class="flex justify-between text-sm">
                                <span class="text-base-content/60">Exchange Rate</span>
                                <span>1 {{ $expense->currency }} = {{ $expense->exchange_rate }} UGX</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Amount in UGX</span>
                                <span class="font-medium">UGX {{ number_format($expense->amount_in_base_currency, 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            @if($expense->attachments->count() > 0)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title">Attachments</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($expense->attachments as $attachment)
                                <div class="flex items-center justify-between p-3 border border-base-300 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">{{ $attachment->icon }}</span>
                                        <div>
                                            <p class="font-medium text-sm">{{ $attachment->file_name }}</p>
                                            <p class="text-xs text-base-content/60">{{ $attachment->formatted_size }}</p>
                                        </div>
                                    </div>
                                    <button wire:click="downloadAttachment({{ $attachment->id }})" class="btn btn-ghost btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Approval History -->
            @if(count($approvalHistory) > 0)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title">Approval History</h2>

                        <div class="space-y-4">
                            @foreach($approvalHistory as $level)
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="badge badge-outline">Level {{ $level['level'] }}</span>
                                        <span class="font-medium">{{ $level['description'] }}</span>
                                    </div>

                                    @foreach($level['approvals'] as $approval)
                                        <div class="flex items-start gap-3 ml-4 py-2">
                                            <div class="mt-1">
                                                @if($approval['status'] === 'approved')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                @elseif($approval['status'] === 'rejected')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium">{{ $approval['approver'] }}</p>
                                                <p class="text-sm text-base-content/60">{{ ucfirst($approval['status']) }} · {{ $approval['timestamp']->diffForHumans() }}</p>
                                                @if($approval['comments'])
                                                    <p class="text-sm mt-1 italic">"{{ $approval['comments'] }}"</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-sm sticky top-4">
                <div class="card-body">
                    <h2 class="card-title text-lg">Activity</h2>

                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-base-content/60">Created by</p>
                            <p class="font-medium">{{ $expense->createdBy?->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-base-content/60">{{ $expense->created_at->format('d M Y, H:i') }}</p>
                        </div>

                        @if($expense->approvedBy)
                            <div>
                                <p class="text-base-content/60">Approved by</p>
                                <p class="font-medium">{{ $expense->approvedBy->name }}</p>
                            </div>
                        @endif

                        @if($expense->rejectedBy)
                            <div>
                                <p class="text-base-content/60">Rejected by</p>
                                <p class="font-medium">{{ $expense->rejectedBy->name }}</p>
                                @if($expense->rejection_reason)
                                    <p class="text-xs mt-1 italic">"{{ $expense->rejection_reason }}"</p>
                                @endif
                            </div>
                        @endif

                        @if($expense->paidBy)
                            <div>
                                <p class="text-base-content/60">Paid by</p>
                                <p class="font-medium">{{ $expense->paidBy->name }}</p>
                                <p class="text-xs text-base-content/60">{{ $expense->paid_at->format('d M Y, H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
