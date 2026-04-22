<div>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title text-2xl mb-2">Change Your Password</h2>
            <p class="text-base-content/60 mb-6">Your account requires a password change before you can continue.</p>

            <form wire:submit="changePassword" class="space-y-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">New Password</span>
                    </label>
                    <input
                        type="password"
                        wire:model="password"
                        class="input input-bordered w-full @error('password') input-error @enderror"
                        placeholder="Min. 8 characters"
                        autofocus
                    />
                    @error('password')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Confirm New Password</span>
                    </label>
                    <input
                        type="password"
                        wire:model="password_confirmation"
                        class="input input-bordered w-full"
                        placeholder="Repeat password"
                    />
                </div>

                <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="changePassword">Update Password</span>
                    <span wire:loading wire:target="changePassword" class="loading loading-spinner loading-sm"></span>
                </button>
            </form>
        </div>
    </div>
</div>
