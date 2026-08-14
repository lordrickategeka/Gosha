@props([
    'period' => 'month',
    'showYear' => true,
    'showStaff' => true,
])

<div class="gh-card gh-card--pad">
    <div class="gh-segmented" style="margin-bottom:14px;">
        <button wire:click="setPeriod('today')" class="{{ $period === 'today' ? 'is-active' : '' }}">Today</button>
        <button wire:click="setPeriod('week')" class="{{ $period === 'week' ? 'is-active' : '' }}">Week</button>
        <button wire:click="setPeriod('month')" class="{{ $period === 'month' ? 'is-active' : '' }}">Month</button>
        @if($showYear)
            <button wire:click="setPeriod('year')" class="{{ $period === 'year' ? 'is-active' : '' }}">Year</button>
        @endif
    </div>

    <div class="gh-grid-4">
        <div class="gh-field">
            <span class="gh-label">From</span>
            <input type="date" wire:model.live="dateFrom" class="gh-input" style="width:100%;">
        </div>
        <div class="gh-field">
            <span class="gh-label">To</span>
            <input type="date" wire:model.live="dateTo" class="gh-input" style="width:100%;">
        </div>
        <div class="gh-field">
            <span class="gh-label">Branch</span>
            <select wire:model.live="branchId" class="gh-select" style="width:100%;">
                <option value="">All branches</option>
                @foreach($this->availableBranches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @if($showStaff)
            <div class="gh-field">
                <span class="gh-label">Staff</span>
                <select wire:model.live="staffId" class="gh-select" style="width:100%;">
                    <option value="">All staff</option>
                    @foreach($this->availableStaff as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
</div>
