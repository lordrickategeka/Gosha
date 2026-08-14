<div class="gh-page">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('expenses.index') }}" class="gh-btn gh-btn--sm">←</a>
            <div>
                <div style="font-size:20px; font-weight:700; letter-spacing:-0.02em;">{{ $expense->expense_number }}</div>
                <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">{{ $expense->description }}</p>
            </div>
        </div>
        @if($expense->isDraft() || $expense->isPendingApproval())
            @can('edit_expenses')
                <a href="{{ route('expenses.edit', $expense) }}" class="gh-btn gh-btn--sm">Edit</a>
            @endcan
        @endif
    </div>

    <div class="gh-split">
        <div class="gh-stack">
            <div class="gh-card gh-card--pad">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                    <div class="gh-card__title">Details</div>
                    {!! str_replace('badge badge-', 'gh-badge gh-badge--', $expense->status_badge) !!}
                </div>

                <div class="gh-grid-2">
                    <div>
                        <p class="gh-muted" style="font-size:10.5px;">Type</p>
                        <p style="font-weight:600; font-size:12.5px;">{{ ucfirst(str_replace('_', ' ', $expense->expense_type)) }}</p>
                    </div>
                    <div>
                        <p class="gh-muted" style="font-size:10.5px;">Category</p>
                        <p style="font-weight:600; font-size:12.5px;">{!! $expense->category ? str_replace('badge badge-', 'gh-badge gh-badge--', $expense->category->badge_html) : 'N/A' !!}</p>
                    </div>
                    <div>
                        <p class="gh-muted" style="font-size:10.5px;">Date</p>
                        <p style="font-weight:600; font-size:12.5px;">{{ $expense->expense_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="gh-muted" style="font-size:10.5px;">Payment Method</p>
                        <p style="font-weight:600; font-size:12.5px;">{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</p>
                    </div>
                    @if($expense->payment_reference)
                        <div>
                            <p class="gh-muted" style="font-size:10.5px;">Reference</p>
                            <p style="font-weight:600; font-size:12px; font-family:monospace;">{{ $expense->payment_reference }}</p>
                        </div>
                    @endif
                    @if($expense->supplier)
                        <div>
                            <p class="gh-muted" style="font-size:10.5px;">Supplier</p>
                            <p style="font-weight:600; font-size:12.5px;">{{ $expense->supplier->name }}</p>
                        </div>
                    @endif
                    @if($expense->branch)
                        <div>
                            <p class="gh-muted" style="font-size:10.5px;">Branch</p>
                            <p style="font-weight:600; font-size:12.5px;">{{ $expense->branch->name }}</p>
                        </div>
                    @endif
                </div>

                @if($expense->notes)
                    <div style="margin-top:14px;">
                        <p class="gh-muted" style="font-size:10.5px; margin-bottom:2px;">Notes</p>
                        <p style="font-size:12.5px;">{{ $expense->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Amount Breakdown</div>

                <div class="gh-stack" style="gap:9px;">
                    <div style="display:flex; justify-content:space-between; font-size:12.5px;">
                        <span class="gh-muted">Amount</span>
                        <span style="font-weight:600;">{{ $expense->formatted_amount }}</span>
                    </div>

                    @if($expense->tax_amount)
                        <div style="display:flex; justify-content:space-between; font-size:12.5px;">
                            <span class="gh-muted">Tax ({{ $expense->tax_percentage }}%) {{ $expense->tax_inclusive ? '(Inclusive)' : '' }}</span>
                            <span style="font-weight:600;">{{ $expense->currency }} {{ number_format($expense->tax_amount, 2) }}</span>
                        </div>
                    @endif

                    <div style="border-top:1px solid var(--gh-hairline); margin:4px 0;"></div>

                    <div style="display:flex; justify-content:space-between; font-size:15px;">
                        <span style="font-weight:700;">Total</span>
                        <span style="font-weight:800;">{{ $expense->formatted_total }}</span>
                    </div>

                    @if($expense->currency !== 'UGX')
                        <div style="display:flex; justify-content:space-between; font-size:11.5px;">
                            <span class="gh-muted">Exchange Rate</span>
                            <span>1 {{ $expense->currency }} = {{ $expense->exchange_rate }} UGX</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:12.5px;">
                            <span class="gh-muted">Amount in UGX</span>
                            <span style="font-weight:600;">UGX {{ number_format($expense->amount_in_base_currency, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            @if($expense->attachments->count() > 0)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:14px;">Attachments</div>
                    <div class="gh-grid-2">
                        @foreach($expense->attachments as $attachment)
                            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border:1px solid var(--gh-hairline); border-radius:var(--gh-radius);">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="font-size:22px;">{{ $attachment->icon }}</span>
                                    <div>
                                        <p style="font-weight:600; font-size:12.5px;">{{ $attachment->file_name }}</p>
                                        <p class="gh-muted" style="font-size:10.5px;">{{ $attachment->formatted_size }}</p>
                                    </div>
                                </div>
                                <button wire:click="downloadAttachment({{ $attachment->id }})" class="gh-btn gh-btn--sm">↓</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(count($approvalHistory) > 0)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:14px;">Approval History</div>

                    <div class="gh-stack">
                        @foreach($approvalHistory as $level)
                            <div>
                                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                                    <span class="gh-badge">Level {{ $level['level'] }}</span>
                                    <span style="font-weight:600; font-size:12.5px;">{{ $level['description'] }}</span>
                                </div>

                                @foreach($level['approvals'] as $approval)
                                    <div style="display:flex; align-items:flex-start; gap:10px; margin-left:16px; padding:8px 0;">
                                        <div style="margin-top:2px; font-size:15px;">
                                            @if($approval['status'] === 'approved')
                                                <span style="color:var(--gh-success);">✓</span>
                                            @elseif($approval['status'] === 'rejected')
                                                <span style="color:var(--gh-error);">✕</span>
                                            @else
                                                <span style="color:var(--gh-warning);">◷</span>
                                            @endif
                                        </div>
                                        <div style="flex:1;">
                                            <p style="font-weight:600; font-size:12.5px;">{{ $approval['approver'] }}</p>
                                            <p class="gh-muted" style="font-size:11.5px;">{{ ucfirst($approval['status']) }} &middot; {{ $approval['timestamp']->diffForHumans() }}</p>
                                            @if($approval['comments'])
                                                <p style="font-size:12px; font-style:italic; margin-top:4px;">"{{ $approval['comments'] }}"</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="gh-stack">
            <div class="gh-card gh-card--pad" style="position:sticky; top:14px;">
                <div class="gh-card__title" style="margin-bottom:12px;">Activity</div>

                <div class="gh-stack" style="gap:12px; font-size:12.5px;">
                    <div>
                        <p class="gh-muted" style="font-size:10.5px;">Created by</p>
                        <p style="font-weight:600;">{{ $expense->createdBy?->name ?? 'Unknown' }}</p>
                        <p class="gh-hint">{{ $expense->created_at->format('d M Y, H:i') }}</p>
                    </div>

                    @if($expense->approvedBy)
                        <div>
                            <p class="gh-muted" style="font-size:10.5px;">Approved by</p>
                            <p style="font-weight:600;">{{ $expense->approvedBy->name }}</p>
                        </div>
                    @endif

                    @if($expense->rejectedBy)
                        <div>
                            <p class="gh-muted" style="font-size:10.5px;">Rejected by</p>
                            <p style="font-weight:600;">{{ $expense->rejectedBy->name }}</p>
                            @if($expense->rejection_reason)
                                <p style="font-size:11px; font-style:italic; margin-top:4px;">"{{ $expense->rejection_reason }}"</p>
                            @endif
                        </div>
                    @endif

                    @if($expense->paidBy)
                        <div>
                            <p class="gh-muted" style="font-size:10.5px;">Paid by</p>
                            <p style="font-weight:600;">{{ $expense->paidBy->name }}</p>
                            <p class="gh-hint">{{ $expense->paid_at->format('d M Y, H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
