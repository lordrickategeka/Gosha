@isset($subscriptionGraceBanner)
    <div class="alert alert-warning rounded-none justify-center text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <span>
            Your subscription payment is overdue. You have
            <strong>{{ $subscriptionGraceBanner->graceDaysRemaining() }} day(s)</strong>
            left to pay before your account access is restricted.
        </span>
        <a href="{{ route('billing.subscription') }}" class="btn btn-xs btn-warning">Pay Now</a>
    </div>
@endisset
