<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Calendar</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Work orders &amp; appointments overview</p>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <button wire:click="previousMonth" class="gh-btn gh-btn--sm">←</button>
            <span style="font-weight:700; font-size:14px; min-width:150px; text-align:center;">{{ $monthLabel }}</span>
            <button wire:click="nextMonth" class="gh-btn gh-btn--sm">→</button>
        </div>
    </div>

    <div style="display:flex; align-items:center; gap:16px; font-size:12px;">
        <span style="display:flex; align-items:center; gap:6px;">
            <span style="width:10px; height:10px; border-radius:50%; background:var(--gh-info); display:inline-block;"></span> Appointment
        </span>
        <span style="display:flex; align-items:center; gap:6px;">
            <span style="width:10px; height:10px; border-radius:50%; background:var(--gh-warning); display:inline-block;"></span> Work Order (due)
        </span>
    </div>

    <div class="gh-card" style="overflow:hidden;">
        <div style="display:grid; grid-template-columns:repeat(7, 1fr); background:var(--gh-base-200);">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                <div style="padding:8px 0; text-align:center; font-size:10.5px; font-weight:700; color:var(--gh-ink-subtle); text-transform:uppercase; letter-spacing:.04em;">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        @php
            $startDay = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
            $endDay   = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
            $today    = \Carbon\Carbon::today()->format('Y-m-d');
        @endphp

        <div style="display:grid; grid-template-columns:repeat(7, 1fr); border-top:1px solid var(--gh-hairline);">
            @for($day = $startDay->copy(); $day->lte($endDay); $day->addDay())
                @php
                    $dateKey      = $day->format('Y-m-d');
                    $isToday      = $dateKey === $today;
                    $isCurrentMonth = $day->month === $startOfMonth->month;
                    $dayEvents    = $events[$dateKey] ?? [];
                @endphp
                <div class="gh-cal-cell" style="min-height:100px; padding:4px; border-right:1px solid var(--gh-hairline); border-bottom:1px solid var(--gh-hairline); {{ $isCurrentMonth ? '' : 'background:var(--gh-base-200);' }}">
                    <div style="display:flex; align-items:center; justify-content:center; margin-bottom:4px;">
                        <span style="font-size:12px; font-weight:600; width:26px; height:26px; display:flex; align-items:center; justify-content:center; border-radius:50%; {{ $isToday ? 'background:var(--gh-primary); color:var(--gh-primary-content);' : ($isCurrentMonth ? '' : 'color:var(--gh-ink-faint);') }}">
                            {{ $day->day }}
                        </span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        @foreach(array_slice($dayEvents, 0, 3) as $event)
                            <a href="{{ $event['url'] }}" style="display:block; border-radius:4px; padding:2px 4px; font-size:10.5px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#fff; background:{{ $event['color'] === 'bg-warning' ? 'var(--gh-warning)' : 'var(--gh-info)' }};" title="{{ $event['title'] }}{{ $event['time'] ? ' @ ' . $event['time'] : '' }}">
                                {{ $event['time'] ? $event['time'] . ' ' : '' }}{{ $event['title'] }}
                            </a>
                        @endforeach
                        @if(count($dayEvents) > 3)
                            <span class="gh-hint" style="padding-left:2px;">+{{ count($dayEvents) - 3 }} more</span>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
