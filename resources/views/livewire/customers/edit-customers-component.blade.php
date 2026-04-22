<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Edit Customer</h1>
            <p class="text-base-content/60">{{ $customer->name }}</p>
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
                        <label class="label"><span class="label-text font-medium">Phone *</span></label>
                        <input type="text" wire:model="phone" class="input input-bordered" />
                        @error('phone') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Email</span></label>
                        <input type="email" wire:model="email" class="input input-bordered" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Company</span></label>
                        <input type="text" wire:model="company" class="input input-bordered" />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Tax ID</span></label>
                        <input type="text" wire:model="tax_id" class="input input-bordered" />
                    </div>
                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Address</span></label>
                        <input type="text" wire:model="address" class="input input-bordered" />
                    </div>
                    <div class="form-control sm:col-span-2">
                        <label class="label"><span class="label-text font-medium">Notes</span></label>
                        <textarea wire:model="notes" rows="2" class="textarea textarea-bordered"></textarea>
                    </div>
                </div>
                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </form>
</div>
