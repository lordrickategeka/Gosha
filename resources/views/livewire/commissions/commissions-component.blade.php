<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Commissions</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage and track staff commissions</p>
        </div>
        @if($this->canManageRules())
            <button wire:click="$toggle('showRuleModal')" class="gh-btn gh-btn--primary gh-btn--sm">Manage rules</button>
        @endif
    </div>

    <div class="gh-grid-4">
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Pending</span>
            <span class="gh-stat__value" style="color:var(--gh-warning);">UGX {{ number_format($totals['pending'] ?? 0) }}</span>
            <span class="gh-hint">{{ $totals['pending_count'] ?? 0 }} commissions</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Approved</span>
            <span class="gh-stat__value" style="color:var(--gh-info);">UGX {{ number_format($totals['approved'] ?? 0) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Paid this month</span>
            <span class="gh-stat__value" style="color:var(--gh-success);">UGX {{ number_format($totals['paid_month'] ?? 0) }}</span>
        </div>
        <div class="gh-card gh-stat">
            <span class="gh-stat__label">Total earned</span>
            <span class="gh-stat__value">UGX {{ number_format($stats['total_paid'] ?? 0) }}</span>
        </div>
    </div>

    <div class="gh-card gh-card--pad">
        <div class="gh-grid-2" style="grid-template-columns:repeat(5, 1fr);">
            <div class="gh-field">
                <span class="gh-label">Technician</span>
                <select wire:model="user" class="gh-select" style="width:100%;">
                    <option value="">All Technicians</option>
                    @foreach($this->technicians as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="gh-field">
                <span class="gh-label">Status</span>
                <select wire:model="status" class="gh-select" style="width:100%;">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="paid">Paid</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="gh-field">
                <span class="gh-label">From date</span>
                <input type="date" wire:model="dateFrom" class="gh-input" style="width:100%;">
            </div>
            <div class="gh-field">
                <span class="gh-label">To date</span>
                <input type="date" wire:model="dateTo" class="gh-input" style="width:100%;">
            </div>
            <div style="display:flex; align-items:flex-end; gap:8px;">
                <button wire:click="$refresh" class="gh-btn gh-btn--primary gh-btn--sm">Filter</button>
                <button wire:click="resetFilter" class="gh-btn gh-btn--sm">Clear</button>
            </div>
        </div>

        @if($this->canApprove() || $this->canPay())
            <div style="display:flex; gap:8px; margin-top:16px; padding-top:16px; border-top:1px solid var(--gh-hairline);">
                @if($this->canApprove())
                    <button wire:click="bulkApprove" class="gh-btn gh-btn--sm" style="color:var(--gh-success);">Bulk approve pending</button>
                @endif
                @if($this->canPay())
                    <button wire:click="bulkMarkPaid" class="gh-btn gh-btn--sm" style="color:var(--gh-warning);">Bulk mark as paid</button>
                @endif
            </div>
        @endif
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Staff</th>
                        <th>Reference</th>
                        <th>Rule</th>
                        <th>Base Amount</th>
                        <th>Commission</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $commission)
                        <tr>
                            <td class="gh-muted" style="font-family:monospace;">#{{ $commission->id }}</td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div class="gh-sidebar__mark" style="width:26px; height:26px; border-radius:50%; font-size:10px;">{{ substr($commission->user?->name ?? 'U', 0, 1) }}</div>
                                    <span>{{ $commission->user?->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td>
                                @switch($commission->reference_type)
                                    @case('work_order')
                                        <a href="{{ route('work-orders.show', $commission->reference_id) }}" class="is-ref">WO #{{ $commission->reference_id }}</a>
                                        @break
                                    @case('wash_order')
                                        <a href="{{ route('wash-orders.show', $commission->reference_id) }}" class="is-ref">Wash #{{ $commission->reference_id }}</a>
                                        @break
                                    @case('invoice')
                                        <span>Inv #{{ $commission->reference_id }}</span>
                                        @break
                                    @default
                                        <span>{{ $commission->reference_type }}</span>
                                @endswitch
                            </td>
                            <td><span class="gh-badge">{{ $commission->commissionRule?->name ?? 'N/A' }}</span></td>
                            <td class="gh-muted">UGX {{ number_format($commission->base_amount) }}</td>
                            <td class="is-num">UGX {{ number_format($commission->commission_amount) }}</td>
                            <td><span class="gh-badge gh-badge--{{ $commission->status_color }}">{{ ucfirst($commission->status) }}</span></td>
                            <td class="gh-muted">{{ $commission->created_at?->format('d M Y') }}</td>
                            <td style="text-align:right;">
                                <div style="display:flex; justify-content:flex-end; gap:4px;">
                                    @if($commission->status === 'pending' && $this->canApprove())
                                        <button wire:click="approve({{ $commission->id }})" class="gh-btn gh-btn--sm" style="color:var(--gh-success);" title="Approve">✓</button>
                                    @endif
                                    @if(in_array($commission->status, ['pending', 'approved']) && $this->canPay())
                                        <button wire:click="markPaid({{ $commission->id }})" class="gh-btn gh-btn--sm" style="color:var(--gh-warning);" title="Mark as Paid">💰</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No commissions found. Try adjusting your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 16px; border-top:1px solid var(--gh-hairline);">{{ $commissions->links() }}</div>
    </div>

    <!-- Commission Rule Modal -->
    @if($showRuleModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad" style="max-width:42rem;">
                <div class="gh-card__title" style="margin-bottom:16px;">Commission Rules</div>

                <div style="margin-bottom:20px;">
                    <p class="gh-eyebrow" style="margin-bottom:8px;">Existing rules</p>
                    @if($this->rules->isEmpty())
                        <p class="gh-muted" style="font-size:12px;">No rules defined yet.</p>
                    @else
                        <div class="gh-table-scroll" style="max-height:15rem; overflow-y:auto;">
                            <table class="gh-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Type</th>
                                        <th>Applies To</th>
                                        <th>Value</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->rules as $rule)
                                        <tr>
                                            <td style="font-weight:700;">{{ $rule->name }}</td>
                                            <td><span class="gh-badge">{{ $rule->role }}</span></td>
                                            <td class="gh-muted">{{ $rule->type }}</td>
                                            <td class="gh-muted">{{ $rule->applies_to }}</td>
                                            <td style="font-family:monospace;">{{ $rule->display_value }}</td>
                                            <td>
                                                <button wire:click="toggleRuleStatus({{ $rule->id }})" class="gh-badge {{ $rule->is_active ? 'gh-badge--success' : 'gh-badge--error' }}" style="cursor:pointer; border:none;">
                                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </td>
                                            <td>
                                                <div style="display:flex; gap:4px;">
                                                    <button wire:click="openRuleModal({{ $rule->id }})" class="gh-btn gh-btn--sm">Edit</button>
                                                    <button wire:click="deleteRule({{ $rule->id }})" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div style="border-top:1px solid var(--gh-hairline); padding-top:16px;">
                    <p class="gh-eyebrow" style="margin-bottom:10px;">{{ $selectedRule ? 'Edit Rule' : 'Add New Rule' }}</p>

                    <div class="gh-grid-2">
                        <div class="gh-field">
                            <span class="gh-label">Rule name</span>
                            <input type="text" wire:model="ruleName" class="gh-input" style="width:100%;" placeholder="e.g., Main Technician Rate">
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Role</span>
                            <select wire:model="ruleRole" class="gh-select" style="width:100%;">
                                <option value="technician">Technician</option>
                                <option value="wash-attendant">Wash Attendant</option>
                                <option value="advisor">Advisor</option>
                            </select>
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Type</span>
                            <select wire:model="ruleType" class="gh-select" style="width:100%;">
                                <option value="percentage">Percentage</option>
                                <option value="flat">Flat Rate</option>
                            </select>
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Value</span>
                            <input type="number" step="0.01" wire:model="ruleValue" class="gh-input" style="width:100%;" placeholder="{{ $ruleType === 'percentage' ? 'e.g., 10 for 10%' : 'e.g., 5000' }}">
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Applies to</span>
                            <select wire:model="ruleAppliesTo" class="gh-select" style="width:100%;">
                                <option value="labor">Labor Charges</option>
                                <option value="parts">Parts Sales</option>
                                <option value="total">Total Invoice</option>
                                <option value="wash">Wash Services</option>
                            </select>
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Minimum threshold</span>
                            <input type="number" step="0.01" wire:model="ruleMinimumThreshold" class="gh-input" style="width:100%;" placeholder="Optional minimum">
                        </div>
                    </div>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-top:14px;">
                        <input type="checkbox" wire:model="ruleIsActive">
                        <span style="font-weight:600; font-size:12.5px;">Active</span>
                    </label>

                    <div class="gh-field" style="margin-top:14px;">
                        <span class="gh-label">Description (optional)</span>
                        <textarea wire:model="ruleDescription" rows="2" class="gh-input" style="width:100%;"></textarea>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <button wire:click="closeRuleModal" class="gh-btn">Close</button>
                    <button wire:click="saveRule" class="gh-btn gh-btn--primary">Save rule</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeRuleModal"></div>
        </div>
    @endif
</div>
