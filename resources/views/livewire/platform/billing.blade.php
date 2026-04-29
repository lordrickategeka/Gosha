<div>
    <div class="mb-6">
        <div class="badge badge-warning mb-2">Platform Admin</div>
        <h1 class="text-2xl font-bold">Billing Overview</h1>
        <p class="text-base-content/60">Platform revenue and subscription metrics</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Estimated MRR</div>
            <div class="stat-value text-lg text-success">UGX {{ number_format($this->stats['mrr']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Active Subscriptions</div>
            <div class="stat-value text-lg">{{ $this->stats['active_subscriptions'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Active Trials</div>
            <div class="stat-value text-lg text-warning">{{ $this->stats['active_trials'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Past Due</div>
            <div class="stat-value text-lg text-error">{{ $this->stats['past_due'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Total Vendors</div>
            <div class="stat-value text-lg">{{ $this->stats['total_vendors'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Outstanding Balance</div>
            <div class="stat-value text-lg text-warning">UGX {{ number_format($this->stats['outstanding_balance']) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Vendors by Plan -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Active Subscriptions by Plan</h2>
                @if($this->vendorsByPlan->isEmpty())
                    <p class="text-sm text-base-content/50 py-4 text-center">No active subscriptions yet</p>
                @else
                    @php $total = $this->vendorsByPlan->sum() ?: 1; @endphp
                    <div class="space-y-3">
                        @foreach($this->vendorsByPlan as $planName => $count)
                            <div>
                                <div class="flex justify-between mb-1 text-sm">
                                    <span class="font-medium">{{ $planName }}</span>
                                    <span>{{ $count }} {{ Str::plural('vendor', $count) }}</span>
                                </div>
                                <progress class="progress progress-primary w-full" value="{{ ($count / $total) * 100 }}" max="100"></progress>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Expiring Trials -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Expiring Trials (7 days)</h2>
                @if($this->expiringTrials->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead><tr><th>Vendor</th><th>Plan</th><th>Expires</th><th></th></tr></thead>
                            <tbody>
                                @foreach($this->expiringTrials as $sub)
                                    <tr class="{{ $sub->trial_ends_at->diffInDays(now()) <= 3 ? 'bg-warning/10' : '' }}">
                                        <td>
                                            <a href="{{ route('platform.vendors.show', $sub->vendor) }}" class="link link-hover font-medium">{{ $sub->vendor->name }}</a>
                                        </td>
                                        <td class="text-sm text-base-content/60">{{ $sub->plan->name }}</td>
                                        <td class="text-sm">{{ $sub->trial_ends_at->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('platform.vendors.show', $sub->vendor) }}" class="btn btn-primary btn-xs">Manage</a>
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

    <!-- Recent Invoices -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Recent Invoices</h2>
            @if($this->recentInvoices->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Vendor</th>
                                <th>Type</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->recentInvoices as $invoice)
                                <tr>
                                    <td class="font-mono text-xs">{{ $invoice->invoice_number }}</td>
                                    <td>
                                        <a href="{{ route('platform.vendors.show', $invoice->vendor) }}" class="link link-hover">{{ $invoice->vendor->name }}</a>
                                    </td>
                                    <td><span class="badge badge-ghost badge-sm">{{ ucfirst($invoice->type) }}</span></td>
                                    <td class="font-medium">UGX {{ number_format($invoice->total) }}</td>
                                    <td>
                                        <span class="badge badge-sm {{ match($invoice->status) {
                                            'paid' => 'badge-success',
                                            'pending' => 'badge-warning',
                                            'overdue' => 'badge-error',
                                            'cancelled' => 'badge-ghost',
                                            default => 'badge-ghost'
                                        } }}">{{ ucfirst($invoice->status) }}</span>
                                    </td>
                                    <td class="text-sm {{ $invoice->isOverdue() ? 'text-error font-medium' : '' }}">
                                        {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center py-8 text-base-content/50">No invoices yet</p>
            @endif
        </div>
    </div>
</div>

