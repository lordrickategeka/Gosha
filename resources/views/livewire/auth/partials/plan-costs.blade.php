<div class="space-y-2 text-sm">
    <p class="text-xl font-bold">
        @if($plan->is_trial){{ $plan->currency }} 0 <span class="text-sm font-normal">for 14 days</span>
        @elseif($plan->billing_model === 'free')Free
        @elseif($plan->billing_model === 'custom')Individually quoted
        @elseif($plan->billing_model === 'one_time'){{ $plan->currency }} {{ number_format($plan->base_price, 2) }} once
        @elseif($plan->hasSubscriptionComponent()){{ $plan->currency }} {{ number_format($plan->base_price, 2) }} / {{ $plan->getBillingCycleLabel() }}
        @else Usage-based billing
        @endif
    </p>
    @if($plan->description)<p class="text-base-content/60">{{ $plan->description }}</p>@endif
    @if($plan->hasCommissionComponent())
        <p>{{ number_format($plan->commission_rate, 2) }}% commission on {{ $plan->audience === 'supplier' ? 'completed order totals' : str_replace('_', ' ', $plan->commission_base ?? 'eligible revenue') }}.</p>
        @if($plan->commission_min > 0)<p>Minimum commission: {{ $plan->currency }} {{ number_format($plan->commission_min, 2) }}</p>@endif
        @if($plan->commission_max > 0)<p>Maximum commission: {{ $plan->currency }} {{ number_format($plan->commission_max, 2) }}</p>@endif
    @endif
    @if($plan->hasPayPerUseComponent())
        @foreach(['work_order' => 'work order', 'wash_order' => 'wash order', 'invoice' => 'invoice', 'user' => 'user', 'branch' => 'branch', 'sms' => 'SMS'] as $metric => $label)
            @if($plan->{'price_per_'.$metric} > 0)<p>{{ $plan->currency }} {{ number_format($plan->{'price_per_'.$metric}, 2) }} per {{ $label }}</p>@endif
        @endforeach
    @endif
    @if($plan->audience === 'supplier')
        <p>{{ $plan->max_active_listings ?? 'Unlimited' }} active listings · {{ $plan->max_users ?? 'Unlimited' }} team members</p>
        @if($plan->is_trial)<p class="text-success">Marketplace commission waived during trial. Starts after platform approval.</p>@endif
    @else
        <p>{{ $plan->max_branches === null ? 'Unlimited branches' : $plan->max_branches.' branch'.($plan->max_branches == 1 ? '' : 'es') }} · {{ $plan->max_users === null ? 'Unlimited users' : $plan->max_users.' user'.($plan->max_users == 1 ? '' : 's') }}</p>
    @endif
    @foreach(['max_work_orders_per_month' => 'work orders / month', 'max_wash_orders_per_month' => 'wash orders / month', 'max_customers' => 'customers', 'max_inventory_items' => 'inventory items'] as $limit => $label)
        @if($plan->$limit !== null)<p>{{ number_format($plan->$limit) }} {{ $label }}</p>@endif
    @endforeach
    @if($plan->is_trial)
        <p class="text-success font-medium">14-day trial · No card required</p>
        <p class="text-xs text-base-content/60">No setup fee or automatic charge. A paid subscription is required after 14 days.</p>
    @endif
    @if(!$plan->is_trial && $plan->setup_fee > 0)<p class="font-medium">Setup fee: {{ $plan->currency }} {{ number_format($plan->setup_fee, 2) }} (paid with your first subscription)</p>@endif
    @if($plan->billing_model !== 'free')<p class="text-xs text-base-content/60">Listed charges exclude applicable platform tax.</p>@endif
    @if($plan->audience === 'supplier')<p class="text-xs text-base-content/60">Any gateway processing fees are separate. Approval is required on every plan.</p>@endif
</div>
