<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="badge badge-warning mb-2">Platform Admin</div>
            <h1 class="text-2xl font-bold">Pricing Plans</h1>
            <p class="text-base-content/60">Configure billing models and pricing tiers</p>
        </div>
        <button wire:click="openCreate" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Plan
        </button>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($this->plans as $plan)
            <div class="card bg-base-100 shadow-sm {{ $plan->is_featured ? 'ring-2 ring-primary' : '' }} {{ !$plan->is_active ? 'opacity-60' : '' }}">
                <div class="card-body">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="card-title">{{ $plan->name }}</h2>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <span class="badge badge-ghost badge-sm">{{ ucfirst(str_replace('_', ' ', $plan->billing_model)) }}</span>
                                @if($plan->is_default)
                                    <span class="badge badge-primary badge-sm">Default</span>
                                @endif
                                @if($plan->is_featured)
                                    <span class="badge badge-accent badge-sm">Featured</span>
                                @endif
                                @if(!$plan->is_active)
                                    <span class="badge badge-error badge-sm">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-ghost btn-sm">⋮</label>
                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-48">
                                <li><button wire:click="openEdit({{ $plan->id }})">Edit</button></li>
                                <li><button wire:click="toggleStatus({{ $plan->id }})">{{ $plan->is_active ? 'Deactivate' : 'Activate' }}</button></li>
                                <li><button wire:click="toggleFeatured({{ $plan->id }})">{{ $plan->is_featured ? 'Unfeature' : 'Feature' }}</button></li>
                                @if(!$plan->is_default)
                                    <li><button wire:click="setDefault({{ $plan->id }})">Set as Default</button></li>
                                @endif
                                @if($plan->subscriptions_count === 0)
                                    <li><button wire:click="delete({{ $plan->id }})" class="text-error">Delete</button></li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    @if($plan->description)
                        <p class="text-sm text-base-content/60 mt-2">{{ $plan->description }}</p>
                    @endif

                    <!-- Pricing Display -->
                    <div class="mt-4">
                        @if($plan->billing_model === 'free')
                            <p class="text-3xl font-bold text-success">Free</p>
                        @else
                            <p class="text-3xl font-bold">
                                {{ $plan->currency }} {{ number_format($plan->base_price) }}
                                @if($plan->billing_model !== 'one_time')
                                    <span class="text-base font-normal text-base-content/60">/{{ $plan->getBillingCycleLabel() }}</span>
                                @endif
                            </p>
                        @endif

                        @if($plan->commission_rate > 0)
                            <p class="text-sm text-warning mt-1">+ {{ $plan->commission_rate }}% commission</p>
                        @endif
                    </div>

                    <!-- Key Limits -->
                    <div class="mt-4 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Branches</span>
                            <span>{{ $plan->max_branches ?? '∞' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Users</span>
                            <span>{{ $plan->max_users ?? '∞' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">Work Orders/mo</span>
                            <span>{{ $plan->max_work_orders_per_month ?? '∞' }}</span>
                        </div>
                    </div>

                    <!-- Trial & Setup -->
                    <div class="mt-4 pt-4 border-t border-base-200 space-y-1 text-sm">
                        @if($plan->has_trial)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Trial</span>
                                <span>{{ $plan->trial_days }} days</span>
                            </div>
                        @endif
                        @if($plan->setup_fee > 0)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Setup Fee</span>
                                <span>{{ $plan->currency }} {{ number_format($plan->setup_fee) }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Subscriptions Count -->
                    <div class="mt-4 pt-4 border-t border-base-200">
                        <span class="text-sm text-base-content/60">{{ $plan->subscriptions_count }} active subscriptions</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Create/Edit Modal -->
    @if($showCreateModal)
        <div class="modal modal-open">
            <div class="modal-box app-modal-shell max-w-4xl max-h-[90vh] overflow-y-auto">
                <h3 class="font-bold text-lg mb-4">{{ $editingPlan ? 'Edit Plan' : 'Create Pricing Plan' }}</h3>

                <form wire:submit="save">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Plan Name *</span></label>
                            <input type="text" wire:model.live="name" class="input input-bordered" placeholder="Professional" />
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Slug</span></label>
                            <input type="text" wire:model="slug" class="input input-bordered" placeholder="professional" />
                        </div>
                        <div class="form-control md:col-span-2">
                            <label class="label"><span class="label-text font-medium">Description</span></label>
                            <textarea wire:model="description" class="textarea textarea-bordered" rows="2" placeholder="Plan description..."></textarea>
                        </div>
                    </div>

                    <!-- Billing Model -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-3">Billing Model</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach(['free' => 'Free', 'one_time' => 'One-Time', 'subscription' => 'Subscription', 'commission' => 'Commission Only', 'hybrid' => 'Hybrid', 'pay_per_use' => 'Pay-per-Use', 'custom' => 'Custom'] as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="billing_model" value="{{ $value }}" class="hidden peer" />
                                    <div class="p-3 border rounded-lg text-center peer-checked:border-primary peer-checked:bg-primary/10 hover:bg-base-200">
                                        <span class="text-sm">{{ $label }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Subscription Pricing -->
                    @if(in_array($billing_model, ['one_time', 'subscription', 'hybrid']))
                        <div class="mb-6 p-4 bg-base-200 rounded-lg">
                            <h4 class="font-medium mb-3">{{ $billing_model === 'one_time' ? 'One-Time' : 'Subscription' }} Pricing</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Base Price *</span></label>
                                    <input type="number" wire:model="base_price" class="input input-bordered" min="0" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Currency</span></label>
                                    <select wire:model="currency" class="select select-bordered">
                                        <option value="UGX">UGX</option>
                                        <option value="USD">USD</option>
                                        <option value="KES">KES</option>
                                        <option value="TZS">TZS</option>
                                    </select>
                                </div>
                                @if($billing_model !== 'one_time')
                                    <div class="form-control">
                                        <label class="label"><span class="label-text">Billing Cycle</span></label>
                                        <select wire:model="billing_cycle" class="select select-bordered">
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

                    <!-- Commission Settings -->
                    @if(in_array($billing_model, ['commission', 'hybrid']))
                        <div class="mb-6 p-4 bg-warning/10 rounded-lg">
                            <h4 class="font-medium mb-3">Commission Settings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Commission Rate (%)</span></label>
                                    <input type="number" wire:model="commission_rate" class="input input-bordered" min="0" max="100" step="0.1" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Commission Base</span></label>
                                    <select wire:model="commission_base" class="select select-bordered">
                                        <option value="payment_received">Payment Received</option>
                                        <option value="gross_revenue">Gross Revenue</option>
                                        <option value="invoice_total">Invoice Total</option>
                                        <option value="work_order_total">Work Order Total</option>
                                        <option value="wash_order_total">Wash Order Total</option>
                                    </select>
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Min Commission/Period</span></label>
                                    <input type="number" wire:model="commission_min" class="input input-bordered" min="0" placeholder="No minimum" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Max Commission Cap</span></label>
                                    <input type="number" wire:model="commission_max" class="input input-bordered" min="0" placeholder="No cap" />
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Pay-per-Use Pricing -->
                    @if($billing_model === 'pay_per_use')
                        <div class="mb-6 p-4 bg-info/10 rounded-lg">
                            <h4 class="font-medium mb-3">Pay-per-Use Pricing</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Per Work Order</span></label>
                                    <input type="number" wire:model="price_per_work_order" class="input input-bordered input-sm" min="0" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Per Wash Order</span></label>
                                    <input type="number" wire:model="price_per_wash_order" class="input input-bordered input-sm" min="0" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Per Invoice</span></label>
                                    <input type="number" wire:model="price_per_invoice" class="input input-bordered input-sm" min="0" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Per User/Month</span></label>
                                    <input type="number" wire:model="price_per_user" class="input input-bordered input-sm" min="0" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Per Branch/Month</span></label>
                                    <input type="number" wire:model="price_per_branch" class="input input-bordered input-sm" min="0" />
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Per SMS</span></label>
                                    <input type="number" wire:model="price_per_sms" class="input input-bordered input-sm" min="0" />
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Usage Limits -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-3">Usage Limits <span class="text-base-content/60 font-normal">(leave empty for unlimited)</span></h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label"><span class="label-text">Max Branches</span></label>
                                <input type="number" wire:model="max_branches" class="input input-bordered input-sm" min="1" placeholder="∞" />
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text">Max Users</span></label>
                                <input type="number" wire:model="max_users" class="input input-bordered input-sm" min="1" placeholder="∞" />
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text">Max Customers</span></label>
                                <input type="number" wire:model="max_customers" class="input input-bordered input-sm" min="1" placeholder="∞" />
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text">Work Orders/Month</span></label>
                                <input type="number" wire:model="max_work_orders_per_month" class="input input-bordered input-sm" min="1" placeholder="∞" />
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text">Wash Orders/Month</span></label>
                                <input type="number" wire:model="max_wash_orders_per_month" class="input input-bordered input-sm" min="1" placeholder="∞" />
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text">Inventory Items</span></label>
                                <input type="number" wire:model="max_inventory_items" class="input input-bordered input-sm" min="1" placeholder="∞" />
                            </div>
                        </div>
                    </div>

                    <!-- Trial & Setup -->
                    <div class="mb-6">
                        <h4 class="font-medium mb-3">Trial & Setup</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="checkbox" wire:model="has_trial" class="checkbox checkbox-primary" />
                                    <span class="label-text">Enable Trial Period</span>
                                </label>
                            </div>
                            @if($has_trial)
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Trial Days</span></label>
                                    <input type="number" wire:model="trial_days" class="input input-bordered input-sm" min="1" />
                                </div>
                            @endif
                            <div class="form-control">
                                <label class="label"><span class="label-text">Setup Fee</span></label>
                                <input type="number" wire:model="setup_fee" class="input input-bordered input-sm" min="0" />
                            </div>
                        </div>
                    </div>

                    <!-- Visibility -->
                    <div class="flex gap-6 mb-6">
                        <label class="label cursor-pointer gap-3">
                            <input type="checkbox" wire:model="is_active" class="checkbox checkbox-primary" />
                            <span class="label-text">Active</span>
                        </label>
                        <label class="label cursor-pointer gap-3">
                            <input type="checkbox" wire:model="is_featured" class="checkbox checkbox-accent" />
                            <span class="label-text">Featured</span>
                        </label>
                    </div>

                    <div class="modal-action app-modal-actions">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-primary">{{ $editingPlan ? 'Update Plan' : 'Create Plan' }}</button>
                    </div>
                </form>
            </div>
            <div class="modal-backdrop app-modal-backdrop" wire:click="$set('showCreateModal', false)"></div>
        </div>
    @endif
</div>
