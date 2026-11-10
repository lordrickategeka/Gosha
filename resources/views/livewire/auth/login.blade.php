<div class="grid gap-6 md:grid-cols-2 items-start">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body p-5 sm:p-7">
            <h1 class="card-title text-2xl mb-2">Welcome back</h1>
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

        </div>
    </div>

    <section aria-labelledby="registration-options" class="space-y-4">
        <div>
            <h2 id="registration-options" class="text-xl font-semibold">New to GarageHQ?</h2>
            <p class="text-sm text-base-content/60 mt-1">Choose the account that fits your business.</p>
        </div>

        <a href="{{ route('register') }}" class="group block rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm transition hover:border-primary hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
            <div class="flex items-center gap-3 mb-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21V8l9-5 9 5v13M7 21v-9h10v9M7 16h10M10 8h4" /></svg>
                </span>
                <h3 class="text-lg font-semibold">Garage owners</h3>
            </div>
            <p class="text-sm text-base-content/70">Run your garage or car wash. Manage jobs, customers, staff, and daily operations.</p>
            <span class="mt-4 flex items-center justify-between gap-3 text-sm font-semibold text-primary">
                Register your garage
                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7M4 12h12" /></svg>
            </span>
        </a>

        <a href="{{ route('supplier.register') }}" class="group block rounded-2xl border border-base-300 bg-base-100 p-5 shadow-sm transition hover:border-primary hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
            <div class="flex items-center gap-3 mb-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 9 5-9 5-9-5 9-5Zm-9 5v9l9 5 9-5V8M12 13v9M7.5 5.5l9 5" /></svg>
                </span>
                <h3 class="text-lg font-semibold">Parts suppliers</h3>
            </div>
            <p class="text-sm text-base-content/70">Sell parts to garages. Manage listings, stock, quotations, and supplier orders.</p>
            <span class="mt-4 flex items-center justify-between gap-3 text-sm font-semibold text-primary">
                Register as a supplier
                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7M4 12h12" /></svg>
            </span>
        </a>
        <p class="text-xs text-base-content/60">Already have an account? Use the same sign-in form for garage and supplier accounts.</p>
        <a href="{{ route('user-guide.index') }}" class="link link-primary text-sm">New here? Read the user guide →</a>
    </section>

    <!-- Demo Credentials -->
    @if(app()->environment('local'))
    <div class="p-4 bg-base-100 rounded-lg border border-base-300 md:col-span-2">
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
