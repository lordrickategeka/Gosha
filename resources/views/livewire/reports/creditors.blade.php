<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Creditors</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Supplier payables, aging, and bill settlement</p>
    </div>

    <div class="gh-grid-2" style="grid-template-columns:2fr 1fr;">
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Aging Filters</div>
            <div class="gh-grid-3">
                <div class="gh-field">
                    <span class="gh-label">As of date</span>
                    <input type="date" wire:model.live="asOfDate" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Branch</span>
                    <select wire:model.live="branchId" class="gh-select" style="width:100%;">
                        <option value="">All branches</option>
                        @foreach($this->availableBranches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="gh-field">
                    <span class="gh-label">Search supplier</span>
                    <input type="text" wire:model.live.debounce.300ms="search" class="gh-input" style="width:100%;" placeholder="Supplier, phone, or email">
                </div>
            </div>
        </div>

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Quick Vendor Bill Capture</div>
            <div class="gh-stack" style="gap:10px;">
                <div class="gh-field">
                    <span class="gh-label">Supplier</span>
                    <select wire:model="newSupplierId" class="gh-select" style="width:100%;">
                        <option value="">Select supplier</option>
                        @foreach($this->suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="gh-field">
                    <span class="gh-label">Bill date</span>
                    <input type="date" wire:model="newBillDate" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Due date</span>
                    <input type="date" wire:model="newDueDate" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Amount</span>
                    <input type="number" min="0" step="0.01" wire:model="newBillTotal" class="gh-input" style="width:100%;" placeholder="0.00">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Description</span>
                    <input type="text" wire:model="newDescription" class="gh-input" style="width:100%;" placeholder="Invoice reference or note">
                </div>
                @error('newSupplierId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                @error('newBillTotal') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                <button type="button" wire:click="createBill" class="gh-btn gh-btn--primary gh-btn--sm">Submit for approval</button>
            </div>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-card__title" style="margin-bottom:14px;">Vendor Bills Pending Approval</div>
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Bill</th><th>Supplier</th><th>Bill date</th><th style="text-align:right;">Amount</th><th></th></tr></thead>
                <tbody>
                    @forelse($this->pendingApprovalBills as $bill)
                        <tr>
                            <td class="is-ref">{{ $bill->bill_number }}</td>
                            <td>{{ $bill->supplier?->name }}</td>
                            <td class="gh-muted">{{ $bill->bill_date?->format('d M Y') ?? 'N/A' }}</td>
                            <td class="is-num">UGX {{ number_format($bill->balance_due) }}</td>
                            <td>
                                <div style="display:flex; gap:6px; justify-content:flex-end;">
                                    <button type="button" class="gh-btn gh-btn--sm" style="color:var(--gh-success);" wire:click="approveBill({{ $bill->id }})">Approve</button>
                                    <button type="button" class="gh-btn gh-btn--sm" wire:click="rejectBill({{ $bill->id }})">Reject</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center; padding:32px; color:var(--gh-ink-faint);">No vendor bills awaiting approval</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="gh-grid-4">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Total payables</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">UGX {{ number_format($this->totals['total_due']) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Suppliers owing</span>
            <span class="gh-stat__value">{{ $this->totals['suppliers'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Open bills</span>
            <span class="gh-stat__value">{{ $this->totals['bill_count'] }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">90+ days</span>
            <span class="gh-stat__value gh-stat__value--neg">UGX {{ number_format($this->totals['days_90_plus']) }}</span>
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr><th>Supplier</th><th style="text-align:right;">Current</th><th style="text-align:right;">1-30</th><th style="text-align:right;">31-60</th><th style="text-align:right;">61-90</th><th style="text-align:right;">90+</th><th style="text-align:right;">Total due</th></tr>
                </thead>
                <tbody>
                    @forelse($this->rows as $row)
                        <tr>
                            <td><div class="gh-cell-stack"><b>{{ $row['supplier_name'] }}</b><span>{{ $row['phone'] ?: $row['email'] ?: 'No contact' }}</span></div></td>
                            <td class="is-num">{{ number_format($row['buckets']['current']) }}</td>
                            <td class="is-num">{{ number_format($row['buckets']['days_1_30']) }}</td>
                            <td class="is-num">{{ number_format($row['buckets']['days_31_60']) }}</td>
                            <td class="is-num">{{ number_format($row['buckets']['days_61_90']) }}</td>
                            <td class="is-num" style="color:var(--gh-error);">{{ number_format($row['buckets']['days_90_plus']) }}</td>
                            <td class="is-num" style="font-weight:700;">{{ number_format($row['total_due']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No creditors for selected filters</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-card__title" style="margin-bottom:14px;">Approved Vendor Bills and Payment Posting</div>
        <div class="gh-table-scroll" style="margin-bottom:16px;">
            <table class="gh-table">
                <thead><tr><th>Bill</th><th>Supplier</th><th>Due date</th><th style="text-align:right;">Balance</th><th></th></tr></thead>
                <tbody>
                    @forelse($this->openBills as $bill)
                        <tr>
                            <td class="is-ref">{{ $bill->bill_number }}</td>
                            <td>{{ $bill->supplier?->name }}</td>
                            <td class="gh-muted">{{ $bill->due_date?->format('d M Y') ?? 'N/A' }}</td>
                            <td class="is-num">UGX {{ number_format($bill->balance_due) }}</td>
                            <td><button type="button" class="gh-btn gh-btn--sm" wire:click="startPayment({{ $bill->id }})">Post payment</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center; padding:32px; color:var(--gh-ink-faint);">No open supplier bills</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paymentBillId)
            <div class="gh-grid-4" style="border:1px solid var(--gh-base-300); border-radius:var(--gh-radius); padding:14px; align-items:end;">
                <input type="number" min="0" step="0.01" wire:model="paymentAmount" class="gh-input" placeholder="Amount">
                <select wire:model="paymentMethod" class="gh-select">
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="cheque">Cheque</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="other">Other</option>
                </select>
                <input type="date" wire:model="paymentDate" class="gh-input">
                <input type="text" wire:model="paymentReference" class="gh-input" placeholder="Reference">
                <div style="grid-column:1/-1; display:flex; gap:8px;">
                    <button type="button" wire:click="recordPayment" class="gh-btn gh-btn--primary gh-btn--sm">Confirm payment</button>
                    <button type="button" wire:click="$set('paymentBillId', null)" class="gh-btn gh-btn--sm">Cancel</button>
                </div>
                @error('paymentAmount') <span class="gh-hint" style="color:var(--gh-error); grid-column:1/-1;">{{ $message }}</span> @enderror
            </div>
        @endif
    </div>

    <div class="gh-grid-2">
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Staff Payable Capture</div>
            <div class="gh-stack" style="gap:10px;">
                <div class="gh-field">
                    <span class="gh-label">Staff member</span>
                    <select wire:model="newStaffUserId" class="gh-select" style="width:100%;">
                        <option value="">Select staff member</option>
                        @foreach($this->staffUsers as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="gh-field">
                    <span class="gh-label">Amount</span>
                    <input type="number" min="0" step="0.01" wire:model="newStaffAmount" class="gh-input" style="width:100%;" placeholder="0.00">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Description</span>
                    <input type="text" wire:model="newStaffDescription" class="gh-input" style="width:100%;" placeholder="Payout note">
                </div>
                @error('newStaffUserId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                @error('newStaffAmount') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                <button type="button" wire:click="createStaffPayable" class="gh-btn gh-btn--primary gh-btn--sm">Submit for approval</button>
            </div>
        </div>

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Staff Payables Pending Approval</div>
            <div class="gh-table-scroll">
                <table class="gh-table">
                    <thead><tr><th>Staff</th><th style="text-align:right;">Amount</th><th></th></tr></thead>
                    <tbody>
                        @forelse($this->pendingStaffPayables as $payable)
                            <tr>
                                <td><div class="gh-cell-stack"><b>{{ $payable->user?->name ?? 'Unknown' }}</b><span>{{ $payable->notes ?: 'No note' }}</span></div></td>
                                <td class="is-num">UGX {{ number_format($payable->commission_amount) }}</td>
                                <td style="text-align:right;"><button type="button" class="gh-btn gh-btn--sm" style="color:var(--gh-success);" wire:click="approveCommission({{ $payable->id }})">Approve</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center; padding:32px; color:var(--gh-ink-faint);">No staff payables awaiting approval</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-card__title" style="margin-bottom:14px;">Approved Staff Payables and Payment Posting</div>
        <div class="gh-table-scroll" style="margin-bottom:16px;">
            <table class="gh-table">
                <thead><tr><th>Staff</th><th style="text-align:right;">Approved amount</th><th></th></tr></thead>
                <tbody>
                    @forelse($this->approvedStaffPayables as $payable)
                        <tr>
                            <td><div class="gh-cell-stack"><b>{{ $payable->user?->name ?? 'Unknown' }}</b><span>{{ $payable->notes ?: 'No note' }}</span></div></td>
                            <td class="is-num">UGX {{ number_format($payable->commission_amount) }}</td>
                            <td style="text-align:right;"><button type="button" class="gh-btn gh-btn--sm" wire:click="startCommissionPayment({{ $payable->id }})">Post payment</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center; padding:32px; color:var(--gh-ink-faint);">No approved staff payables</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paymentCommissionId)
            <div class="gh-grid-3" style="border:1px solid var(--gh-base-300); border-radius:var(--gh-radius); padding:14px;">
                <input type="number" min="0" step="0.01" wire:model="staffPaymentAmount" class="gh-input" placeholder="Amount">
                <input type="text" wire:model="staffPaymentReference" class="gh-input" placeholder="Reference">
                <div style="display:flex; gap:8px;">
                    <button type="button" wire:click="recordCommissionPayment" class="gh-btn gh-btn--primary gh-btn--sm">Confirm payment</button>
                    <button type="button" wire:click="$set('paymentCommissionId', null)" class="gh-btn gh-btn--sm">Cancel</button>
                </div>
                @error('staffPaymentAmount') <span class="gh-hint" style="color:var(--gh-error); grid-column:1/-1;">{{ $message }}</span> @enderror
            </div>
        @endif
    </div>
</div>
