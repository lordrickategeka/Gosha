<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Add Expense</h1>
            <p class="text-base-content/60">Record a new expense</p>
        </div>
    </div>

    <form wire:submit="save" class="max-w-xl">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-medium">Description *</span></label>
                    <input type="text" wire:model="description" class="input input-bordered" placeholder="What was this expense for?" />
                    @error('description') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Amount (UGX) *</span></label>
                        <input type="number" wire:model="amount" class="input input-bordered" min="0" />
                        @error('amount') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Category *</span></label>
                        <select wire:model="category_id" class="select select-bordered">
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Date *</span></label>
                        <input type="date" wire:model="expense_date" class="input input-bordered" />
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Payment Method</span></label>
                        <select wire:model="payment_method" class="select select-bordered">
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                </div>

                <div class="form-control mt-4">
                    <label class="label"><span class="label-text font-medium">Reference Number</span></label>
                    <input type="text" wire:model="reference_number" class="input input-bordered" placeholder="Receipt or transaction number" />
                </div>

                <div class="form-control mt-4">
                    <label class="label"><span class="label-text font-medium">Notes</span></label>
                    <textarea wire:model="notes" rows="2" class="textarea textarea-bordered" placeholder="Additional details..."></textarea>
                </div>

                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </div>
            </div>
        </div>
    </form>
</div>
