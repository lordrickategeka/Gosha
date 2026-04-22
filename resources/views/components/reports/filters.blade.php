@props([
    'period' => 'month',
    'showYear' => true,
    'showStaff' => true,
])

<div class="card bg-base-100 shadow-sm mb-6">
    <div class="card-body p-4 space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <div class="join">
                <button wire:click="setPeriod('today')" class="join-item btn btn-sm {{ $period === 'today' ? 'btn-primary' : 'btn-ghost' }}">Today</button>
                <button wire:click="setPeriod('week')" class="join-item btn btn-sm {{ $period === 'week' ? 'btn-primary' : 'btn-ghost' }}">Week</button>
                <button wire:click="setPeriod('month')" class="join-item btn btn-sm {{ $period === 'month' ? 'btn-primary' : 'btn-ghost' }}">Month</button>
                @if($showYear)
                    <button wire:click="setPeriod('year')" class="join-item btn btn-sm {{ $period === 'year' ? 'btn-primary' : 'btn-ghost' }}">Year</button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <label class="form-control">
                <span class="label-text text-xs mb-1">From</span>
                <input type="date" wire:model.live="dateFrom" class="input input-bordered input-sm" />
            </label>

            <label class="form-control">
                <span class="label-text text-xs mb-1">To</span>
                <input type="date" wire:model.live="dateTo" class="input input-bordered input-sm" />
            </label>

            <label class="form-control">
                <span class="label-text text-xs mb-1">Branch</span>
                <select wire:model.live="branchId" class="select select-bordered select-sm">
                    <option value="">All Branches</option>
                    @foreach($this->availableBranches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>

            @if($showStaff)
                <label class="form-control">
                    <span class="label-text text-xs mb-1">Staff</span>
                    <select wire:model.live="staffId" class="select select-bordered select-sm">
                        <option value="">All Staff</option>
                        @foreach($this->availableStaff as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
        </div>
    </div>
</div>
