<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <a href="{{ route('platform.vendors.index') }}" class="gh-btn gh-btn--sm">←</a>
            <div>
                <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">{{ $vendor->name }}</div>
                <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Vendor Details</p>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            @if($vendor->status === 'trial')
                <button wire:click="activateFromTrial" wire:confirm="Activate this vendor and end their trial?" class="gh-btn gh-btn--primary gh-btn--sm">Activate</button>
            @endif
            <button wire:click="toggleStatus" wire:confirm="Are you sure?" class="gh-btn gh-btn--sm" style="{{ $vendor->status === 'suspended' ? 'color:var(--gh-success);' : 'color:var(--gh-error);' }}">
                {{ $vendor->status === 'suspended' ? 'Reactivate' : 'Suspend' }}
            </button>
        </div>
    </div>

    <div class="gh-split">
        <div class="gh-stack">
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Business Information</div>
                <div class="gh-grid-2">
                    <div>
                        <p class="gh-muted" style="font-size:10.5px;">Email</p>
                        <p style="font-weight:600; font-size:12.5px;">{{ $vendor->email }}</p>
                    </div>
                    <div>
                        <p class="gh-muted" style="font-size:10.5px;">Phone</p>
                        <p style="font-weight:600; font-size:12.5px;">{{ $vendor->phone ?: '—' }}</p>
                    </div>
                    <div style="grid-column:1/-1;">
                        <p class="gh-muted" style="font-size:10.5px;">Address</p>
                        <p style="font-weight:600; font-size:12.5px;">{{ $vendor->address ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="gh-muted" style="font-size:10.5px;">Slug</p>
                        <p style="font-family:monospace; font-size:12px;">{{ $vendor->slug }}</p>
                    </div>
                    <div>
                        <p class="gh-muted" style="font-size:10.5px;">Created</p>
                        <p style="font-size:12px;">{{ $vendor->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Branches ({{ $vendor->branches->count() }})</div>
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Name</th><th>Contact</th><th>Users</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($vendor->branches as $branch)
                                <tr>
                                    <td style="font-weight:700;">
                                        {{ $branch->name }}
                                        @if($branch->is_main) <span class="gh-badge gh-badge--primary" style="margin-left:4px;">Main</span> @endif
                                    </td>
                                    <td class="gh-muted">{{ $branch->email ?: $branch->phone ?: '—' }}</td>
                                    <td class="gh-muted">{{ $branch->users_count }}</td>
                                    <td><span class="gh-badge {{ $branch->is_active ? 'gh-badge--success' : 'gh-badge--error' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align:center; color:var(--gh-ink-faint); padding:20px;">No branches</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:14px;">Users ({{ $vendor->users->count() }})</div>
                <div class="gh-table-scroll">
                    <table class="gh-table">
                        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($vendor->users as $user)
                                <tr>
                                    <td style="font-weight:700;">{{ $user->name }}</td>
                                    <td class="gh-muted">{{ $user->email }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="gh-badge">{{ $role->name }}</span>
                                        @endforeach
                                    </td>
                                    <td><span class="gh-badge {{ $user->is_active ? 'gh-badge--success' : 'gh-badge--error' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align:center; color:var(--gh-ink-faint); padding:20px;">No users</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="gh-stack">
            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:12px;">Status</div>
                <div class="gh-stack" style="gap:10px;">
                    <div>
                        @if($vendor->status === 'active')
                            <span class="gh-badge gh-badge--success">Active</span>
                        @elseif($vendor->status === 'trial')
                            <span class="gh-badge gh-badge--warning">Trial</span>
                        @elseif($vendor->status === 'suspended')
                            <span class="gh-badge gh-badge--error">Suspended</span>
                        @endif
                    </div>

                    @if($vendor->status === 'trial' && $vendor->trial_ends_at)
                        <div style="font-size:12.5px;">
                            <p class="gh-muted" style="font-size:10.5px;">Trial ends</p>
                            <p style="font-weight:600; {{ $vendor->isTrialExpired() ? 'color:var(--gh-error);' : '' }}">
                                {{ $vendor->trial_ends_at->format('d M Y') }}
                                ({{ $vendor->isTrialExpired() ? 'Expired' : $vendor->trial_ends_at->diffForHumans() }})
                            </p>
                        </div>

                        @if($editingTrialDays)
                            <div style="display:flex; align-items:flex-end; gap:8px;">
                                <div class="gh-field" style="flex:1;">
                                    <span class="gh-label">Days from now</span>
                                    <input type="number" wire:model="trialDays" class="gh-input" style="width:100%;" min="0" max="365">
                                </div>
                                <button wire:click="updateTrialPeriod" class="gh-btn gh-btn--primary gh-btn--sm">Save</button>
                                <button wire:click="$set('editingTrialDays', false)" class="gh-btn gh-btn--sm">Cancel</button>
                            </div>
                        @else
                            <button wire:click="showTrialEdit" class="gh-btn gh-btn--sm">Adjust trial</button>
                        @endif
                    @endif
                </div>
            </div>

            <div class="gh-card gh-card--pad">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                    <div class="gh-card__title">Subscription Plan</div>
                    <button wire:click="openAssignPlan" class="gh-btn gh-btn--primary gh-btn--sm">{{ $vendor->activeSubscription ? 'Change plan' : 'Assign plan' }}</button>
                </div>

                @if($vendor->activeSubscription)
                    @php $sub = $vendor->activeSubscription; @endphp
                    <div class="gh-stack" style="gap:8px; font-size:12.5px;">
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Plan</span><span style="font-weight:700;">{{ $sub->plan->name }}</span></div>
                        <div style="display:flex; justify-content:space-between;">
                            <span class="gh-muted">Status</span>
                            <span class="gh-badge {{ match($sub->status) {
                                'active' => 'gh-badge--success',
                                'trial' => 'gh-badge--warning',
                                'past_due' => 'gh-badge--error',
                                default => '',
                            } }}">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Billing model</span><span>{{ ucfirst(str_replace('_', ' ', $sub->plan->billing_model)) }}</span></div>
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Effective price</span><span style="font-weight:600;">{{ $sub->plan->currency }} {{ number_format($sub->getEffectivePrice()) }}</span></div>
                        @if($sub->discount_percent > 0)
                            <div style="display:flex; justify-content:space-between; color:var(--gh-success);"><span>Discount</span><span>{{ $sub->discount_percent }}% — {{ $sub->discount_reason }}</span></div>
                        @endif
                        @if($sub->isTrialing())
                            <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Trial ends</span><span style="{{ $sub->trial_ends_at->diffInDays(now()) <= 3 ? 'color:var(--gh-error);' : '' }}">{{ $sub->trial_ends_at->format('d M Y') }}</span></div>
                        @endif
                        @if($sub->next_billing_date)
                            <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Next billing</span><span>{{ $sub->next_billing_date->format('d M Y') }}</span></div>
                        @endif
                        @if(!$sub->isCancelled())
                            <div style="padding-top:8px; border-top:1px solid var(--gh-hairline);">
                                <button wire:click="cancelSubscription" wire:confirm="Cancel this vendor's subscription immediately?" class="gh-btn gh-btn--block gh-btn--sm" style="color:var(--gh-error);">Cancel subscription</button>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="gh-muted" style="font-size:12px;">No active subscription. Assign a plan to start billing.</p>
                @endif
            </div>

            @if($vendor->billingConfig && !$vendor->activeSubscription)
                <div class="gh-card gh-card--pad">
                    <div class="gh-card__title" style="margin-bottom:12px;">Legacy Billing Config</div>
                    <dl class="gh-stack" style="gap:8px; font-size:12.5px;">
                        <div>
                            <dt class="gh-muted" style="font-size:10.5px;">Model</dt>
                            <dd style="font-weight:600;">{{ ucfirst(str_replace('_', ' ', $vendor->billingConfig->billing_model)) }}</dd>
                        </div>
                        @if($vendor->billingConfig->subscription_amount)
                            <div>
                                <dt class="gh-muted" style="font-size:10.5px;">Subscription</dt>
                                <dd>UGX {{ number_format($vendor->billingConfig->subscription_amount) }} / {{ $vendor->billingConfig->subscription_interval }}</dd>
                            </div>
                        @endif
                        @if($vendor->billingConfig->commission_percent)
                            <div>
                                <dt class="gh-muted" style="font-size:10.5px;">Commission</dt>
                                <dd>{{ $vendor->billingConfig->commission_percent }}%</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            <div class="gh-card gh-card--pad">
                <div class="gh-card__title" style="margin-bottom:12px;">Summary</div>
                <div class="gh-stack" style="gap:8px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Branches</span><span style="font-weight:600;">{{ $vendor->branches->count() }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Users</span><span style="font-weight:600;">{{ $vendor->users->count() }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Created</span><span style="font-weight:600;">{{ $vendor->created_at->diffForHumans() }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Plan Modal -->
    @if($showAssignPlanModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad" style="max-width:32rem;">
                <div class="gh-card__title" style="margin-bottom:16px;">Assign Pricing Plan — {{ $vendor->name }}</div>

                <div class="gh-stack">
                    <div class="gh-field">
                        <span class="gh-label">Pricing plan</span>
                        <select wire:model="selectedPlanId" class="gh-select" style="width:100%;">
                            <option value="">Select a plan...</option>
                            @foreach($this->plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->name }}
                                    ({{ ucfirst($plan->billing_model) }} — {{ $plan->currency }} {{ number_format($plan->base_price) }}/{{ $plan->billing_cycle }})
                                </option>
                            @endforeach
                        </select>
                        @error('selectedPlanId') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-field">
                        <span class="gh-label">Custom price override</span>
                        <input type="number" wire:model="customPrice" class="gh-input" style="width:100%;" placeholder="e.g. 90000" min="0" step="1000">
                        <span class="gh-hint">Leave blank to use plan default</span>
                        @error('customPrice') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>

                    <div class="gh-grid-2">
                        <div class="gh-field">
                            <span class="gh-label">Discount %</span>
                            <input type="number" wire:model="discountPercent" class="gh-input" style="width:100%;" placeholder="0" min="0" max="100" step="1">
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Discount reason</span>
                            <input type="text" wire:model="discountReason" class="gh-input" style="width:100%;" placeholder="e.g. Early adopter">
                        </div>
                    </div>

                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" wire:model="waiveSetupFee">
                        <span style="font-weight:600; font-size:12.5px;">Waive setup fee</span>
                    </label>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px; margin-top:16px;">
                    <button wire:click="closeAssignPlan" class="gh-btn">Cancel</button>
                    <button wire:click="assignPlan" wire:loading.attr="disabled" wire:target="assignPlan" class="gh-btn gh-btn--primary">
                        <span wire:loading.remove wire:target="assignPlan">Assign plan</span>
                        <span wire:loading wire:target="assignPlan">Assigning…</span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="closeAssignPlan"></div>
        </div>
    @endif
</div>
