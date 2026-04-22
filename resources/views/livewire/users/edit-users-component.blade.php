<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('users.show', $user) }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Edit User</h1>
            <p class="text-base-content/60">{{ $user->name }}</p>
        </div>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Full Name *</span></label>
                        <input type="text" wire:model="name" class="input input-bordered" />
                        @error('name') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email *</span></label>
                        <input type="email" wire:model="email" class="input input-bordered" />
                        @error('email') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Phone</span></label>
                        <input type="text" wire:model="phone" class="input input-bordered" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Role *</span></label>
                        <select wire:model="role" class="select select-bordered">
                            <option value="">Select role...</option>
                            @foreach($this->roles as $r)
                                <option value="{{ $r->name }}">{{ ucwords(str_replace('-', ' ', $r->name)) }}</option>
                            @endforeach
                        </select>
                        @error('role') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Status</span></label>
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" wire:model="is_active" class="toggle toggle-primary" />
                            <span class="label-text">{{ $is_active ? 'Active' : 'Inactive' }}</span>
                        </label>
                    </div>
                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Assign to Branches</span></label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($this->branchesList as $branch)
                                <label class="label cursor-pointer gap-2 border border-base-300 rounded-lg px-3 py-2">
                                    <input type="checkbox" wire:model="branches" value="{{ $branch->id }}" class="checkbox checkbox-sm checkbox-primary" />
                                    <span class="label-text">{{ $branch->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('users.show', $user) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </form>
</div>
