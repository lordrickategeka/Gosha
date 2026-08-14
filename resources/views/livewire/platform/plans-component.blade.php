<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <span class="gh-badge gh-badge--warning">Platform Admin</span>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em; margin-top:8px;">Pricing Plans</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Configure billing models and pricing tiers</p>
        </div>
        <button wire:click="openCreate" class="gh-btn gh-btn--primary">+ Create plan</button>
    </div>

    <div class="gh-grid-3">
        @foreach($this->plans as $plan)
            <div class="gh-card gh-card--pad" style="{{ $plan->is_featured ? 'border-color:var(--gh-primary); border-width:2px;' : '' }} {{ !$plan->is_active ? 'opacity:.6;' : '' }}">
                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:10px;">
                    <div>
                        <div style="font-weight:700; font-size:15px;">{{ $plan->name }}</div>
                        <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:6px;">
                            <span class="gh-badge">{{ ucfirst(str_replace('_', ' ', $plan->billing_model)) }}</span>
                            @if($plan->is_default)
                                <span class="gh-badge gh-badge--primary">Default</span>
                            @endif
                            @if($plan->is_featured)
                                <span class="gh-badge gh-badge--info">Featured</span>
                            @endif
                            @if(!$plan->is_active)
                                <span class="gh-badge gh-badge--error">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="dropdown dropdown-end">
                        <button tabindex="0" type="button" class="gh-btn gh-btn--sm">⋯</button>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-48 border border-base-300">
                            <li><button wire:click="openEdit({{ $plan->id }})">Edit</button></li>
                            <li><button wire:click="toggleStatus({{ $plan->id }})">{{ $plan->is_active ? 'Deactivate' : 'Activate' }}</button></li>
                            <li><button wire:click="toggleFeatured({{ $plan->id }})">{{ $plan->is_featured ? 'Unfeature' : 'Feature' }}</button></li>
                            @if(!$plan->is_default)
                                <li><button wire:click="setDefault({{ $plan->id }})">Set as default</button></li>
                            @endif
                            @if($plan->subscriptions_count === 0)
                                <li><button wire:click="delete({{ $plan->id }})" style="color:var(--gh-error);">Delete</button></li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if($plan->description)
                    <p class="gh-muted" style="font-size:11.5px; margin-top:8px;">{{ $plan->description }}</p>
                @endif

                <div style="margin-top:14px;">
                    @if($plan->billing_model === 'free')
                        <p style="font-size:24px; font-weight:800; color:var(--gh-success);">Free</p>
                    @else
                        <p style="font-size:24px; font-weight:800;">
                            {{ $plan->currency }} {{ number_format($plan->base_price) }}
                            @if($plan->billing_model !== 'one_time')
                                <span class="gh-muted" style="font-size:13px; font-weight:400;">/{{ $plan->getBillingCycleLabel() }}</span>
                            @endif
                        </p>
                    @endif
                    @if($plan->commission_rate > 0)
                        <p style="font-size:11.5px; color:var(--gh-warning); margin-top:2px;">+ {{ $plan->commission_rate }}% commission</p>
                    @endif
                </div>

                <div class="gh-stack" style="gap:5px; margin-top:14px; font-size:12.5px;">
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Branches</span><span>{{ $plan->max_branches ?? '∞' }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Users</span><span>{{ $plan->max_users ?? '∞' }}</span></div>
                    <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Work orders/mo</span><span>{{ $plan->max_work_orders_per_month ?? '∞' }}</span></div>
                </div>

                <div class="gh-stack" style="gap:5px; margin-top:14px; padding-top:12px; border-top:1px solid var(--gh-hairline); font-size:12.5px;">
                    @if($plan->has_trial)
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Trial</span><span>{{ $plan->trial_days }} days</span></div>
                    @endif
                    @if($plan->setup_fee > 0)
                        <div style="display:flex; justify-content:space-between;"><span class="gh-muted">Setup fee</span><span>{{ $plan->currency }} {{ number_format($plan->setup_fee) }}</span></div>
                    @endif
                </div>

                <div style="margin-top:14px; padding-top:12px; border-top:1px solid var(--gh-hairline);">
                    <span class="gh-muted" style="font-size:11.5px;">{{ $plan->subscriptions_count }} active subscriptions</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create/Edit Modal -->
    @if($showCreateModal)
        <div class="modal modal-open">
            <div class="modal-box gh-card gh-card--pad" style="max-width:56rem; max-height:90vh; overflow-y:auto;">
                <div class="gh-card__title" style="margin-bottom:16px;">{{ $editingPlan ? 'Edit Plan' : 'Create Pricing Plan' }}</div>

                <form wire:submit="save">
                    <div class="gh-grid-2" style="margin-bottom:20px;">
                        <div class="gh-field">
                            <span class="gh-label">Plan name *</span>
                            <input type="text" wire:model.live="name" class="gh-input" style="width:100%;" placeholder="Professional">
                        </div>
                        <div class="gh-field">
                            <span class="gh-label">Slug</span>
                            <input type="text" wire:model="slug" class="gh-input" style="width:100%;" placeholder="professional">
                        </div>
                        <div class="gh-field" style="grid-column:1/-1;">
                            <span class="gh-label">Description</span>
                            <textarea wire:model="description" rows="2" class="gh-input" style="width:100%;" placeholder="Plan description..."></textarea>
                        </div>
                    </div>

                    <div style="margin-bottom:20px;">
                        <p class="gh-eyebrow" style="margin-bottom:10px;">Billing model</p>
                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(120px, 1fr)); gap:8px;">
                            @foreach(['free' => 'Free', 'one_time' => 'One-Time', 'subscription' => 'Subscription', 'commission' => 'Commission Only', 'hybrid' => 'Hybrid', 'pay_per_use' => 'Pay-per-Use', 'custom' => 'Custom'] as $value => $label)
                                <button type="button" wire:click="$set('billing_model', '{{ $value }}')" class="gh-btn {{ $billing_model === $value ? 'gh-btn--primary' : '' }}" style="justify-content:center;">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @if(in_array($billing_model, ['one_time', 'subscription', 'hybrid']))
                        <div style="margin-bottom:20px; padding:14px; background:var(--gh-base-200); border-radius:var(--gh-radius);">
                            <p class="gh-eyebrow" style="margin-bottom:10px;">{{ $billing_model === 'one_time' ? 'One-Time' : 'Subscription' }} pricing</p>
                            <div class="gh-grid-3">
                                <div class="gh-field">
                                    <span class="gh-label">Base price *</span>
                                    <input type="number" wire:model="base_price" class="gh-input" style="width:100%;" min="0">
                                </div>
                                <div class="gh-field">
                                    <span class="gh-label">Currency</span>
                                    <select wire:model="currency" class="gh-select" style="width:100%;">
                                        <option value="UGX">UGX</option>
                                        <option value="USD">USD</option>
                                        <option value="KES">KES</option>
                                        <option value="TZS">TZS</option>
                                    </select>
                                </div>
                                @if($billing_model !== 'one_time')
                                    <div class="gh-field">
                                        <span class="gh-label">Billing cycle</span>
                                        <select wire:model="billing_cycle" class="gh-select" style="width:100%;">
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="semi_annual">Semi-Annual</option>
                                            <option value="yearly">Yearly</option>
                                            <option value="custom">Custom</option>
                                        </select>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(in_array($billing_model, ['commission', 'hybrid']))
                        <div style="margin-bottom:20px; padding:14px; background:var(--gh-warning-bg); border-radius:var(--gh-radius);">
                            <p class="gh-eyebrow" style="margin-bottom:10px;">Commission settings</p>
                            <div class="gh-grid-2">
                                <div class="gh-field">
                                    <span class="gh-label">Commission rate (%)</span>
                                    <input type="number" wire:model="commission_rate" class="gh-input" style="width:100%;" min="0" max="100" step="0.1">
                                </div>
                                <div class="gh-field">
                                    <span class="gh-label">Commission base</span>
                                    <select wire:model="commission_base" class="gh-select" style="width:100%;">
                                        <option value="payment_received">Payment Received</option>
                                        <option value="gross_revenue">Gross Revenue</option>
                                        <option value="invoice_total">Invoice Total</option>
                                        <option value="work_order_total">Work Order Total</option>
                                        <option value="wash_order_total">Wash Order Total</option>
                                    </select>
                                </div>
                                <div class="gh-field">
                                    <span class="gh-label">Min commission/period</span>
                                    <input type="number" wire:model="commission_min" class="gh-input" style="width:100%;" min="0" placeholder="No minimum">
                                </div>
                                <div class="gh-field">
                                    <span class="gh-label">Max commission cap</span>
                                    <input type="number" wire:model="commission_max" class="gh-input" style="width:100%;" min="0" placeholder="No cap">
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($billing_model === 'pay_per_use')
                        <div style="margin-bottom:20px; padding:14px; background:var(--gh-info-bg); border-radius:var(--gh-radius);">
                            <p class="gh-eyebrow" style="margin-bottom:10px;">Pay-per-use pricing</p>
                            <div class="gh-grid-3">
                                <div class="gh-field">
                                    <span class="gh-label">Per work order</span>
                                    <input type="number" wire:model="price_per_work_order" class="gh-input" style="width:100%;" min="0">
                                </div>
                                <div class="gh-field">
                                    <span class="gh-label">Per wash order</span>
                                    <input type="number" wire:model="price_per_wash_order" class="gh-input" style="width:100%;" min="0">
                                </div>
                                <div class="gh-field">
                                    <span class="gh-label">Per invoice</span>
                                    <input type="number" wire:model="price_per_invoice" class="gh-input" style="width:100%;" min="0">
                                </div>
                                <div class="gh-field">
                                    <span class="gh-label">Per user/month</span>
                                    <input type="number" wire:model="price_per_user" class="gh-input" style="width:100%;" min="0">
                                </div>
                                <div class="gh-field">
                                    <span class="gh-label">Per branch/month</span>
                                    <input type="number" wire:model="price_per_branch" class="gh-input" style="width:100%;" min="0">
                                </div>
                                <div class="gh-field">
                                    <span class="gh-label">Per SMS</span>
                                    <input type="number" wire:model="price_per_sms" class="gh-input" style="width:100%;" min="0">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div style="margin-bottom:20px;">
                        <p class="gh-eyebrow" style="margin-bottom:10px;">Usage limits <span style="font-weight:400;">(leave empty for unlimited)</span></p>
                        <div class="gh-grid-3">
                            <div class="gh-field">
                                <span class="gh-label">Max branches</span>
                                <input type="number" wire:model="max_branches" class="gh-input" style="width:100%;" min="1" placeholder="∞">
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Max users</span>
                                <input type="number" wire:model="max_users" class="gh-input" style="width:100%;" min="1" placeholder="∞">
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Max customers</span>
                                <input type="number" wire:model="max_customers" class="gh-input" style="width:100%;" min="1" placeholder="∞">
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Work orders/month</span>
                                <input type="number" wire:model="max_work_orders_per_month" class="gh-input" style="width:100%;" min="1" placeholder="∞">
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Wash orders/month</span>
                                <input type="number" wire:model="max_wash_orders_per_month" class="gh-input" style="width:100%;" min="1" placeholder="∞">
                            </div>
                            <div class="gh-field">
                                <span class="gh-label">Inventory items</span>
                                <input type="number" wire:model="max_inventory_items" class="gh-input" style="width:100%;" min="1" placeholder="∞">
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom:20px;">
                        <p class="gh-eyebrow" style="margin-bottom:10px;">Trial &amp; setup</p>
                        <div class="gh-grid-3">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="checkbox" wire:model="has_trial">
                                <span style="font-weight:600; font-size:12.5px;">Enable trial period</span>
                            </label>
                            @if($has_trial)
                                <div class="gh-field">
                                    <span class="gh-label">Trial days</span>
                                    <input type="number" wire:model="trial_days" class="gh-input" style="width:100%;" min="1">
                                </div>
                            @endif
                            <div class="gh-field">
                                <span class="gh-label">Setup fee</span>
                                <input type="number" wire:model="setup_fee" class="gh-input" style="width:100%;" min="0">
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:20px; margin-bottom:16px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" wire:model="is_active">
                            <span style="font-weight:600; font-size:12.5px;">Active</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" wire:model="is_featured">
                            <span style="font-weight:600; font-size:12.5px;">Featured</span>
                        </label>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:14px;">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="gh-btn">Cancel</button>
                        <button type="submit" class="gh-btn gh-btn--primary">{{ $editingPlan ? 'Update plan' : 'Create plan' }}</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop" wire:click="$set('showCreateModal', false)"></div>
        </div>
    @endif
</div>
