<x-layouts.guest>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-1">Contact Support</h2>
            <p class="text-base-content/60 mb-6">
                Need help with your subscription or a payment issue? Reach out and we'll sort it out.
            </p>

            <div class="space-y-2 text-sm">
                @if($email = \App\Domains\Platform\Models\PlatformSetting::get(\App\Domains\Platform\Models\PlatformSetting::PLATFORM_EMAIL))
                    <p><span class="text-base-content/60">Email:</span> <a href="mailto:{{ $email }}" class="link link-primary">{{ $email }}</a></p>
                @endif
                @if($phone = \App\Domains\Platform\Models\PlatformSetting::get(\App\Domains\Platform\Models\PlatformSetting::PLATFORM_PHONE))
                    <p><span class="text-base-content/60">Phone:</span> {{ $phone }}</p>
                @endif
            </div>

            <a href="{{ route('billing.locked') }}" class="btn btn-ghost mt-6">Back</a>
        </div>
    </div>
</x-layouts.guest>
