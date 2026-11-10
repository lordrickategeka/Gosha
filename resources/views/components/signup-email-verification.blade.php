@props(['email', 'verified', 'resendAt'])
<section class="rounded-xl border border-base-300 p-4 space-y-3" aria-label="Verify your login email">
    @if($verified)
        <p class="text-success font-semibold" role="status">Login email verified</p>
    @else
        <h3 class="font-semibold">Verify your login email</h3>
        <p class="text-sm text-base-content/60">We'll send a six-digit code to {{ $email ?: 'your login email' }} to confirm you can access this address.</p>
        <div x-data="{ now: Math.floor(Date.now() / 1000) }" x-init="setInterval(() => now = Math.floor(Date.now() / 1000), 1000)">
            <button type="button" wire:click="sendVerificationCode" wire:loading.attr="disabled" x-bind:disabled="now < {{ $resendAt }}" class="btn btn-sm btn-outline btn-primary">{{ $resendAt ? 'Resend code' : 'Send verification code' }}</button>
            <span x-show="now < {{ $resendAt }}" class="text-xs ml-2" x-text="'Resend in ' + Math.max(0, {{ $resendAt }} - now) + 's'"></span>
        </div>
        @if($resendAt)
            <div class="flex flex-wrap gap-3 items-end">
                <div><label for="verificationCode" class="block text-sm mb-1">Verification code</label><input id="verificationCode" wire:model="verificationCode" type="text" inputmode="numeric" maxlength="6" autocomplete="one-time-code" class="input input-bordered w-40" /></div>
                <button type="button" wire:click="verifyEmail" wire:loading.attr="disabled" class="btn btn-primary">Verify email</button>
            </div>
        @endif
    @endif
    @error('verification')<p class="text-error text-sm" role="alert">{{ $message }}</p>@enderror
    @error('verificationCode')<p class="text-error text-sm" role="alert">{{ $message }}</p>@enderror
</section>
