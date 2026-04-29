<div class="p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Calendar</h1>
            <p class="text-base-content/60 text-sm">Work orders &amp; appointments overview</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="previousMonth" class="btn btn-ghost btn-sm btn-square">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <span class="font-semibold text-lg min-w-[160px] text-center">{{ $monthLabel }}</span>
            <button wire:click="nextMonth" class="btn btn-ghost btn-sm btn-square">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-4 mb-4 text-sm">
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-info inline-block"></span> Appointment
        </span>
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-warning inline-block"></span> Work Order (due)
        </span>
    </div>

    {{-- Calendar Grid --}}
    <div class="card bg-base-100 shadow-sm overflow-hidden">
        {{-- Day headers --}}
        <div class="grid grid-cols-7 bg-base-200">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                <div class="py-2 text-center text-xs font-semibold text-base-content/60 uppercase tracking-wide">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        {{-- Calendar days --}}
        @php
            $startDay = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
            $endDay   = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
            $today    = \Carbon\Carbon::today()->format('Y-m-d');
        @endphp

        <div class="grid grid-cols-7 border-t border-base-300">
            @for($day = $startDay->copy(); $day->lte($endDay); $day->addDay())
                @php
                    $dateKey      = $day->format('Y-m-d');
                    $isToday      = $dateKey === $today;
                    $isCurrentMonth = $day->month === $startOfMonth->month;
                    $dayEvents    = $events[$dateKey] ?? [];
                @endphp
                <div class="min-h-[100px] p-1 border-r border-b border-base-300 {{ $isCurrentMonth ? '' : 'bg-base-200/40' }}">
                    <div class="flex items-center justify-center mb-1">
                        <span class="text-sm font-medium w-7 h-7 flex items-center justify-center rounded-full
                            {{ $isToday ? 'bg-primary text-primary-content' : ($isCurrentMonth ? '' : 'text-base-content/30') }}">
                            {{ $day->day }}
                        </span>
                    </div>
                    <div class="space-y-0.5">
                        @foreach(array_slice($dayEvents, 0, 3) as $event)
                            <a href="{{ $event['url'] }}" class="block rounded px-1 py-0.5 text-xs truncate text-white {{ $event['color'] }} hover:opacity-80" title="{{ $event['title'] }}{{ $event['time'] ? ' @ ' . $event['time'] : '' }}">
                                {{ $event['time'] ? $event['time'] . ' ' : '' }}{{ $event['title'] }}
                            </a>
                        @endforeach
                        @if(count($dayEvents) > 3)
                            <span class="text-xs text-base-content/50 pl-1">+{{ count($dayEvents) - 3 }} more</span>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
