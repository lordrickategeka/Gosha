<div>
    <div class="mb-6">
        <div class="badge badge-warning mb-2">Platform Admin</div>
        <h1 class="text-2xl font-bold">Billing Overview</h1>
        <p class="text-base-content/60">Platform revenue and subscription metrics</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Monthly Recurring Revenue</div>
            <div class="stat-value text-lg text-success">UGX {{ number_format($this->stats['mrr']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Trial Conversions</div>
            <div class="stat-value text-lg">{{ $this->stats['trial_conversions'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Active Trials</div>
            <div class="stat-value text-lg text-warning">{{ $this->stats['active_trials'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Churn Risk (3 days)</div>
            <div class="stat-value text-lg text-error">{{ $this->stats['churn_risk'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Vendors by Plan -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Vendors by Plan</h2>
                <div class="space-y-4">
                    @foreach(['trial' => 'Trial', 'basic' => 'Basic', 'professional' => 'Professional', 'enterprise' => 'Enterprise'] as $key => $label)
                        @php $count = $this->vendorsByPlan[$key] ?? 0; $total = array_sum($this->vendorsByPlan->toArray()) ?: 1; @endphp
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="font-medium">{{ $label }}</span>
                                <span>{{ $count }} vendors</span>
                            </div>
                            <progress class="progress progress-{{ $key === 'enterprise' ? 'primary' : ($key === 'trial' ? 'warning' : 'success') }} w-full" value="{{ ($count / $total) * 100 }}" max="100"></progress>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Expiring Trials -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Expiring Trials (7 days)</h2>
                @if($this->expiringTrials->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead><tr><th>Vendor</th><th>Expires</th><th></th></tr></thead>
                            <tbody>
                                @foreach($this->expiringTrials as $vendor)
                                    <tr class="{{ $vendor->trial_ends_at->isPast() ? 'bg-error/10' : ($vendor->trial_ends_at->diffInDays(now()) <= 3 ? 'bg-warning/10' : '') }}">
                                        <td>
                                            <a href="{{ route('platform.vendors.show', $vendor) }}" class="link link-hover font-medium">{{ $vendor->name }}</a>
                                        </td>
                                        <td>
                                            @if($vendor->trial_ends_at->isPast())
                                                <span class="text-error">Expired {{ $vendor->trial_ends_at->diffForHumans() }}</span>
                                            @else
                                                {{ $vendor->trial_ends_at->diffForHumans() }}
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('platform.vendors.show', $vendor) }}" class="btn btn-primary btn-xs">Convert</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center py-8 text-base-content/50">No trials expiring soon</p>
                @endif
            </div>
        </div>
    </div>
</div>
