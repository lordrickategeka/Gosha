<div>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-2">Welcome back</h2>
            <p class="text-base-content/60 mb-6">Sign in to your account to continue</p>

            <form method="POST" action="{{ route('login') }}" wire:submit="login" class="space-y-4">
                @csrf
                <!-- Email -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Email address</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        wire:model="email"
                        value="{{ old('email') }}"
                        class="input input-bordered w-full @error('email') input-error @enderror"
                        placeholder="you@example.com"
                        autofocus
                    />
                    @error('email')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Password</span>
                    </label>
                    <input
                        type="password"
                        name="password"
                        wire:model="password"
                        class="input input-bordered w-full @error('password') input-error @enderror"
                        placeholder="••••••••"
                    />
                    @error('password')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="label cursor-pointer gap-2">
                        <input type="checkbox" name="remember" wire:model="remember" class="checkbox checkbox-sm checkbox-primary" />
                        <span class="label-text">Remember me</span>
                    </label>
                    <a href="#" class="link link-primary text-sm">Forgot password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="login">Sign in</span>
                    <span wire:loading wire:target="login" class="loading loading-spinner loading-sm"></span>
                </button>
            </form>

            <div class="divider text-xs text-base-content/50">OR</div>

            <p class="text-center text-sm text-base-content/60">
                Don't have an account?
                <a href="{{ route('register') }}" class="link link-primary font-medium">Contact us</a>
            </p>
        </div>
    </div>

    <!-- Demo Credentials -->
    @if(app()->environment('local'))
    <div class="mt-6 p-4 bg-base-100 rounded-lg border border-base-300">
        <h3 class="font-semibold text-sm mb-2">Demo Credentials</h3>
        <div class="text-xs space-y-1 text-base-content/70">
            <p><span class="font-medium">Platform Admin:</span> admin@garageplatform.com</p>
            <p><span class="font-medium">Vendor Owner:</span> john@autocaregarage.com</p>
            <p><span class="font-medium">Manager:</span> sarah@autocaregarage.com</p>
            <p><span class="font-medium">Technician:</span> peter@autocaregarage.com</p>
            <p><span class="font-medium">Cashier:</span> mary@autocaregarage.com</p>
            <p class="mt-2 text-base-content/50">Password: <code class="bg-base-200 px-1 rounded">password</code></p>
        </div>
    </div>
    @endif
</div>
