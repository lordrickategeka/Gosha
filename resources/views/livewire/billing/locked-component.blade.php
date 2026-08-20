<div>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex items-center gap-3 mb-2">
                <div class="rounded-full bg-error/10 p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="card-title text-2xl">Account Access Restricted</h2>
            </div>
            <p class="text-base-content/60 mb-6">
                Your grace period has ended and a subscription payment is still outstanding. Pay now to instantly restore full access.
            </p>

            @if(session('error'))
                <div class="alert alert-error mb-4">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            @if($outstandingInvoice)
                <div class="border-2 border-error/30 bg-error/5 rounded-lg p-4 mb-6">
                    <p class="text-sm text-base-content/60">Amount due</p>
                    <p class="text-3xl font-bold mb-3">{{ $outstandingInvoice->currency }} {{ number_format($outstandingInvoice->balance_due, 2) }}</p>
                    <p class="text-xs text-base-content/60 mb-4">Invoice {{ $outstandingInvoice->invoice_number }} — {{ $subscription?->plan?->name }}</p>
                    <form method="POST" action="{{ route('billing.pay', $outstandingInvoice) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">Pay Now & Reactivate</button>
                    </form>
                </div>
            @else
                <div class="space-y-4 mb-6">
                    <p class="text-sm font-medium">Choose a plan to reactivate your account:</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($plans as $plan)
                            <div class="card border-2 border-base-300">
                                <div class="card-body p-4">
                                    <h3 class="font-semibold">{{ $plan->name }}</h3>
                                    <p class="text-xl font-bold">{{ $plan->getFormattedPriceAttribute() }}</p>
                                    <button wire:click="subscribeToPlan({{ $plan->id }})" wire:loading.attr="disabled" class="btn btn-primary btn-sm mt-2">
                                        Choose Plan
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap gap-4 justify-center text-sm border-t border-base-300 pt-4">
                @if($exportEnabled)
                    <a href="{{ route('billing.export') }}" class="link link-hover">Export my data</a>
                @endif
                @if($supportEnabled)
                    <a href="{{ route('billing.support') }}" class="link link-hover">Contact support</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="link link-hover">Sign out</button>
                </form>
            </div>
        </div>
    </div>
</div>
