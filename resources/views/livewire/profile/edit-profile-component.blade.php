<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">My Profile</h1>
        <p class="text-base-content/60">Manage your account settings</p>
    </div>

    <div class="max-w-2xl space-y-6">
        <!-- Profile Info -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Profile Information</h2>
                <form wire:submit="updateProfile">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control sm:col-span-2">
                            <label class="label"><span class="label-text font-medium">Name</span></label>
                            <input type="text" wire:model="name" class="input input-bordered" />
                            @error('name') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Email</span></label>
                            <input type="email" wire:model="email" class="input input-bordered" />
                            @error('email') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="card-actions justify-end mt-4">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Change Password</h2>
                <form wire:submit="updatePassword">
                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Current Password</span></label>
                            <input type="password" wire:model="current_password" class="input input-bordered" />
                            @error('current_password') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">New Password</span></label>
                            <input type="password" wire:model="new_password" class="input input-bordered" />
                            @error('new_password') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Confirm New Password</span></label>
                            <input type="password" wire:model="new_password_confirmation" class="input input-bordered" />
                        </div>
                    </div>
                    <div class="card-actions justify-end mt-4">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Info -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Account Information</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Role</span>
                        <span>
                            @foreach(auth()->user()->roles as $role)
                                <span class="badge badge-primary badge-sm">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
                            @endforeach
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Organization</span>
                        <span>{{ auth()->user()->vendor?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Member Since</span>
                        <span>{{ auth()->user()->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Last Login</span>
                        <span>{{ auth()->user()->last_login_at?->diffForHumans() ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
