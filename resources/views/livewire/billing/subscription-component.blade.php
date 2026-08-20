<div class="max-w-4xl mx-auto p-6 space-y-6">
    <h1 class="text-2xl font-bold">My Subscription</h1>

    @if($subscription)
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-start justify-between flex-wrap gap-2">
                    <div>
                        <h2 class="card-title">{{ $subscription->plan->name }}</h2>
                        <p class="text-base-content/60">{{ $subscription->plan->getFormattedPriceAttribute() }}</p>
                    </div>
                    <span class="badge {{ match($subscription->status) {
                        'active' => 'badge-success',
                        'trial' => 'badge-info',
                        'past_due' => 'badge-warning',
                        default => 'badge-ghost',
                    } }}">
                        {{ ucfirst(str_replace('_', ' ', $subscription->status)) }}
                    </span>
                </div>

                @if($subscription->isTrialing())
                    <p class="text-sm mt-2">Trial ends {{ $subscription->trial_ends_at->format('d M Y') }}
                        ({{ now()->diffInDays($subscription->trial_ends_at) }} days left).</p>
                @elseif($subscription->isInGracePeriod())
                    <div class="alert alert-warning mt-3">
                        <span>Payment overdue. You have {{ $subscription->graceDaysRemaining() }} day(s) left before your account is restricted.</span>
                    </div>
                @elseif($subscription->isLocked())
                    <div class="alert alert-error mt-3">
                        <span>Your account is currently restricted. Pay the outstanding invoice below to restore access.</span>
                    </div>
                @elseif($subscription->current_period_end)
                    <p class="text-sm mt-2">Current period ends {{ $subscription->current_period_end->format('d M Y') }}.</p>
                @endif

                <div class="form-control mt-4">
                    <label class="label cursor-pointer justify-start gap-3 w-fit">
                        <input type="checkbox" class="toggle toggle-primary" wire:click="toggleAutoRenew" @checked($subscription->auto_renew) />
                        <span class="label-text">Auto-renew subscription</span>
                    </label>
                </div>

                <div class="mt-4">
                    <button wire:click="togglePlans" class="btn btn-ghost btn-sm">
                        {{ $showPlans ? 'Hide plans' : 'Change plan' }}
                    </button>
                </div>

                @if($showPlans)
                    <div class="grid gap-3 sm:grid-cols-2 mt-3">
                        @foreach($plans as $plan)
                            <div class="card border-2 {{ $subscription->pricing_plan_id === $plan->id ? 'border-primary' : 'border-base-300' }}">
                                <div class="card-body p-4">
                                    <h3 class="font-semibold">{{ $plan->name }}</h3>
                                    <p class="font-bold">{{ $plan->getFormattedPriceAttribute() }}</p>
                                    @if($subscription->pricing_plan_id === $plan->id)
                                        <span class="badge badge-primary badge-sm w-fit">Current plan</span>
                                    @else
                                        <button wire:click="changePlan({{ $plan->id }})" wire:loading.attr="disabled" class="btn btn-sm btn-outline btn-primary mt-1">
                                            Switch
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-info">You don't have an active subscription. Choose a plan to get started.</div>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach($plans as $plan)
                <div class="card border-2 border-base-300">
                    <div class="card-body p-4">
                        <h3 class="font-semibold">{{ $plan->name }}</h3>
                        <p class="font-bold">{{ $plan->getFormattedPriceAttribute() }}</p>
                        <button wire:click="changePlan({{ $plan->id }})" wire:loading.attr="disabled" class="btn btn-sm btn-primary mt-1">Choose</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title mb-2">Invoices</h2>
            @if($invoices->isEmpty())
                <p class="text-base-content/60 text-sm">No invoices yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Issued</th>
                                <th>Due</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->issue_date?->format('d M Y') }}</td>
                                    <td>{{ $invoice->due_date?->format('d M Y') }}</td>
                                    <td>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                                    <td>
                                        <span class="badge badge-sm {{ match($invoice->status) {
                                            'paid' => 'badge-success',
                                            'overdue' => 'badge-error',
                                            'partially_paid' => 'badge-warning',
                                            default => 'badge-ghost',
                                        } }}">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span>
                                    </td>
                                    <td>
                                        @if(!$invoice->isPaid())
                                            <form method="POST" action="{{ route('billing.pay', $invoice) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-primary">Pay Now</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
