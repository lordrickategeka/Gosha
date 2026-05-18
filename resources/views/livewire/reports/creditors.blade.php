<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Creditors</h1>
            <p class="text-base-content/60">Supplier payables, aging, and bill settlement</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <div class="card bg-base-100 shadow-sm xl:col-span-2">
            <div class="card-body">
                <h2 class="card-title text-base">Aging Filters</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="form-control">
                        <span class="label-text text-xs">As Of Date</span>
                        <input type="date" wire:model.live="asOfDate" class="input input-bordered" />
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs">Branch</span>
                        <select wire:model.live="branchId" class="select select-bordered">
                            <option value="">All Branches</option>
                            @foreach($this->availableBranches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs">Search Supplier</span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="input input-bordered" placeholder="Supplier, phone, or email" />
                    </label>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-base">Quick Bill Capture</h2>
                <label class="form-control">
                    <span class="label-text text-xs">Supplier</span>
                    <select wire:model="newSupplierId" class="select select-bordered">
                        <option value="">Select supplier</option>
                        @foreach($this->suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-control">
                    <span class="label-text text-xs">Bill Date</span>
                    <input type="date" wire:model="newBillDate" class="input input-bordered" />
                </label>
                <label class="form-control">
                    <span class="label-text text-xs">Due Date</span>
                    <input type="date" wire:model="newDueDate" class="input input-bordered" />
                </label>
                <label class="form-control">
                    <span class="label-text text-xs">Amount</span>
                    <input type="number" min="0" step="0.01" wire:model="newBillTotal" class="input input-bordered" placeholder="0.00" />
                </label>
                <label class="form-control">
                    <span class="label-text text-xs">Description</span>
                    <input type="text" wire:model="newDescription" class="input input-bordered" placeholder="Invoice reference or note" />
                </label>
                @error('newSupplierId') <span class="text-error text-xs">{{ $message }}</span> @enderror
                @error('newBillTotal') <span class="text-error text-xs">{{ $message }}</span> @enderror
                <button type="button" wire:click="createBill" class="btn btn-primary btn-sm mt-2">Create Bill</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Total Payables</div>
            <div class="stat-value text-lg text-warning">UGX {{ number_format($this->totals['total_due']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Suppliers Owing</div>
            <div class="stat-value text-lg">{{ $this->totals['suppliers'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">Open Bills</div>
            <div class="stat-value text-lg">{{ $this->totals['bill_count'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-lg shadow-sm p-4">
            <div class="stat-title text-xs">90+ Days</div>
            <div class="stat-value text-lg text-error">UGX {{ number_format($this->totals['days_90_plus']) }}</div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th class="text-right">Current</th>
                            <th class="text-right">1-30</th>
                            <th class="text-right">31-60</th>
                            <th class="text-right">61-90</th>
                            <th class="text-right">90+</th>
                            <th class="text-right">Total Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->rows as $row)
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $row['supplier_name'] }}</div>
                                    <div class="text-xs opacity-60">{{ $row['phone'] ?: $row['email'] ?: 'No contact' }}</div>
                                </td>
                                <td class="text-right">{{ number_format($row['buckets']['current']) }}</td>
                                <td class="text-right">{{ number_format($row['buckets']['days_1_30']) }}</td>
                                <td class="text-right">{{ number_format($row['buckets']['days_31_60']) }}</td>
                                <td class="text-right">{{ number_format($row['buckets']['days_61_90']) }}</td>
                                <td class="text-right text-error">{{ number_format($row['buckets']['days_90_plus']) }}</td>
                                <td class="text-right font-semibold">{{ number_format($row['total_due']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-base-content/50">No creditors for selected filters</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <h2 class="card-title text-base">Open Bills and Payment Posting</h2>
            <div class="overflow-x-auto mb-4">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Bill</th>
                            <th>Supplier</th>
                            <th>Due Date</th>
                            <th class="text-right">Balance</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->openBills as $bill)
                            <tr>
                                <td>{{ $bill->bill_number }}</td>
                                <td>{{ $bill->supplier?->name }}</td>
                                <td>{{ $bill->due_date?->format('d M Y') ?? 'N/A' }}</td>
                                <td class="text-right">UGX {{ number_format($bill->balance_due) }}</td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-outline" wire:click="startPayment({{ $bill->id }})">Post Payment</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-6 text-base-content/50">No open supplier bills</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($paymentBillId)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 border border-base-300 rounded-lg p-3">
                    <input type="number" min="0" step="0.01" wire:model="paymentAmount" class="input input-bordered input-sm" placeholder="Amount" />
                    <select wire:model="paymentMethod" class="select select-bordered select-sm">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="cheque">Cheque</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="date" wire:model="paymentDate" class="input input-bordered input-sm" />
                    <input type="text" wire:model="paymentReference" class="input input-bordered input-sm" placeholder="Reference" />
                    <div class="md:col-span-4 flex gap-2">
                        <button type="button" wire:click="recordPayment" class="btn btn-primary btn-sm">Confirm Payment</button>
                        <button type="button" wire:click="$set('paymentBillId', null)" class="btn btn-ghost btn-sm">Cancel</button>
                    </div>
                    @error('paymentAmount') <span class="text-error text-xs md:col-span-4">{{ $message }}</span> @enderror
                </div>
            @endif
        </div>
    </div>
</div>
