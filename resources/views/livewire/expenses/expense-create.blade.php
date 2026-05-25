<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Create Expense</h1>
            <p class="text-base-content/60">Record a new expense</p>
        </div>
    </div>

    @if (session()->has('error'))
        <div class="alert alert-error mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Basic Information</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Expense Type *</span></label>
                                <select wire:model.live="expense_type" class="select select-bordered">
                                    @foreach($expenseTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('expense_type') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Category *</span></label>
                                <select wire:model.live="category_id" class="select select-bordered">
                                    <option value="">Select category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Date *</span></label>
                                <input type="date" wire:model="expense_date" class="input input-bordered" />
                                @error('expense_date') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Supplier</span></label>
                                <select wire:model="supplier_id" class="select select-bordered">
                                    <option value="">None</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="form-control mt-4">
                            <label class="label"><span class="label-text font-medium">Description *</span></label>
                            <textarea wire:model="description" rows="3" class="textarea textarea-bordered" placeholder="What was this expense for?"></textarea>
                            @error('description') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Amount & Currency -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Amount & Currency</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Amount *</span></label>
                                <input type="number" wire:model.live="amount" step="0.01" class="input input-bordered" placeholder="0.00" />
                                @error('amount') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Currency *</span></label>
                                <select wire:model.live="currency" class="select select-bordered">
                                    @foreach($currencies as $curr)
                                        <option value="{{ $curr->code }}">{{ $curr->code }} - {{ $curr->name }}</option>
                                    @endforeach
                                </select>
                                @error('currency') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            @if($currency !== 'UGX')
                                <div class="form-control">
                                    <label class="label"><span class="label-text font-medium">Exchange Rate (to UGX)</span></label>
                                    <input type="number" wire:model="exchange_rate" step="0.0001" class="input input-bordered" />
                                    @error('exchange_rate') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                                    <label class="label">
                                        <span class="label-text-alt">1 {{ $currency }} = {{ $exchange_rate }} UGX</span>
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tax Information -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Tax Information</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Tax Percentage (%)</span></label>
                                <input type="number" wire:model="tax_percentage" step="0.01" class="input input-bordered" placeholder="0" />
                                @error('tax_percentage') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-4">
                                    <input type="checkbox" wire:model="tax_inclusive" class="checkbox" />
                                    <span class="label-text">Tax Inclusive</span>
                                </label>
                                <label class="label">
                                    <span class="label-text-alt">Check if amount already includes tax</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Payment Information</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Payment Method *</span></label>
                                <select wire:model="payment_method" class="select select-bordered">
                                    @foreach($paymentMethods as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control">
                                <label class="label"><span class="label-text font-medium">Reference Number</span></label>
                                <input type="text" wire:model="payment_reference" class="input input-bordered" placeholder="Receipt or transaction number" />
                                @error('payment_reference') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Attachments</h2>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Upload Files</span></label>
                            <input type="file" wire:model="attachments" multiple class="file-input file-input-bordered" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" />
                            <label class="label">
                                <span class="label-text-alt">Supported: JPG, PNG, PDF, DOC, DOCX (Max 10MB each)</span>
                            </label>
                            @error('attachments.*') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                        </div>

                        @if($attachments)
                            <div class="mt-4">
                                <p class="text-sm font-medium mb-2">Selected Files:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($attachments as $attachment)
                                        <li class="text-sm">{{ $attachment->getClientOriginalName() }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Additional Notes</h2>
                        <div class="form-control">
                            <textarea wire:model="notes" rows="3" class="textarea textarea-bordered" placeholder="Any additional information..."></textarea>
                            @error('notes') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Sidebar -->
            <div class="lg:col-span-1">
                <div class="card bg-base-100 shadow-sm sticky top-4">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Summary</h2>

                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-base-content/60">Amount:</span>
                                <span class="font-medium">{{ $currency }} {{ number_format($amount ?: 0, 2) }}</span>
                            </div>

                            @if($tax_percentage)
                                <div class="flex justify-between text-sm">
                                    <span class="text-base-content/60">Tax ({{ $tax_percentage }}%):</span>
                                    <span class="font-medium">
                                        {{ $currency }}
                                        @php
                                            $taxAmount = $tax_inclusive
                                                ? ($amount * $tax_percentage / (100 + $tax_percentage))
                                                : ($amount * $tax_percentage / 100);
                                        @endphp
                                        {{ number_format($taxAmount, 2) }}
                                    </span>
                                </div>
                            @endif

                            <div class="divider my-2"></div>

                            <div class="flex justify-between">
                                <span class="font-semibold">Total:</span>
                                <span class="font-bold text-lg">
                                    {{ $currency }}
                                    @php
                                        $total = $amount ?: 0;
                                        if ($tax_percentage && !$tax_inclusive) {
                                            $total += ($amount * $tax_percentage / 100);
                                        }
                                    @endphp
                                    {{ number_format($total, 2) }}
                                </span>
                            </div>

                            @if($currency !== 'UGX' && $exchange_rate)
                                <div class="flex justify-between text-sm">
                                    <span class="text-base-content/60">In UGX:</span>
                                    <span class="font-medium">UGX {{ number_format($total * $exchange_rate, 2) }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="divider"></div>

                        <button type="button" wire:click="previewApproval" class="btn btn-outline btn-sm w-full mb-2">
                            Preview Approval Path
                        </button>

                        @if($show_approval_preview && $approval_preview)
                            <div class="alert alert-info text-sm">
                                @if($approval_preview['auto_approved'] ?? false)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Will be auto-approved: {{ $approval_preview['reason'] }}</span>
                                @else
                                    <div class="w-full">
                                        <p class="font-semibold mb-2">{{ $approval_preview['chain_name'] }}</p>
                                        @foreach($approval_preview['levels'] as $level)
                                            <div class="mb-2">
                                                <p class="font-medium">Level {{ $level['level'] }}: {{ $level['description'] }}</p>
                                                <p class="text-xs">{{ implode(', ', $level['approvers']) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="card-actions flex-col mt-4">
                            <button type="submit" wire:click="$set('save_as_draft', false)" class="btn btn-primary w-full">
                                Submit for Approval
                            </button>
                            <button type="submit" wire:click="$set('save_as_draft', true)" class="btn btn-ghost w-full">
                                Save as Draft
                            </button>
                            <a href="{{ route('expenses.index') }}" class="btn btn-ghost btn-sm w-full">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
