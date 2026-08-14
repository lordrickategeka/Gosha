<div
    x-data="{ open: false }"
    @click.outside="open = false"
    wire:poll.30000ms="loadNotifications"
    style="position:relative;"
>
    <button
        @click="open = !open"
        class="gh-btn gh-btn--sm"
        style="position:relative; width:36px; height:36px; padding:0; justify-content:center; border-radius:50%;"
        aria-label="Notifications"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($unreadCount > 0)
            <span style="position:absolute; top:-2px; right:-2px; min-width:16px; height:16px; padding:0 3px; border-radius:999px; background:var(--gh-primary); color:var(--gh-primary-content); font-size:9px; font-weight:800; display:flex; align-items:center; justify-content:center;">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="gh-card"
        style="display:none; position:absolute; right:0; top:calc(100% + 8px); z-index:50; width:24rem; box-shadow:0 20px 40px rgba(20,16,13,.16);"
    >
        <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--gh-hairline); padding:12px 16px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <h3 style="font-weight:700; font-size:13.5px;">Notifications</h3>
                @if($unreadCount > 0)
                    <span class="gh-badge gh-badge--primary">{{ $unreadCount }} new</span>
                @endif
            </div>
            @if($unreadCount > 0)
                <button wire:click="markAllRead" class="gh-btn gh-btn--sm" style="color:var(--gh-primary);">Mark all read</button>
            @endif
        </div>

        <div style="max-height:420px; overflow-y:auto;">
            @forelse($notifications as $n)
                <div wire:key="bell-notif-{{ $n['id'] }}" style="display:flex; align-items:flex-start; gap:10px; border-bottom:1px solid var(--gh-hairline); padding:12px 16px; {{ $n['read'] ? 'opacity:.6;' : '' }}">
                    <div style="margin-top:2px; flex-shrink:0;">
                        @if($n['severity'] === 'error')
                            <div style="display:flex; height:32px; width:32px; align-items:center; justify-content:center; border-radius:50%; background:var(--gh-error-bg);">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--gh-error)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        @elseif($n['severity'] === 'warning')
                            <div style="display:flex; height:32px; width:32px; align-items:center; justify-content:center; border-radius:50%; background:var(--gh-warning-bg);">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--gh-warning)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            </div>
                        @elseif($n['severity'] === 'success')
                            <div style="display:flex; height:32px; width:32px; align-items:center; justify-content:center; border-radius:50%; background:var(--gh-success-bg);">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--gh-success)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        @else
                            <div style="display:flex; height:32px; width:32px; align-items:center; justify-content:center; border-radius:50%; background:var(--gh-info-bg);">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--gh-info)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        @endif
                    </div>

                    <div style="min-width:0; flex:1;">
                        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:8px;">
                            <p style="font-size:12.5px; font-weight:600; line-height:1.35;">{{ $n['title'] }}</p>
                            @if(! $n['read'])
                                <span style="margin-top:4px; height:8px; width:8px; flex-shrink:0; border-radius:50%; background:var(--gh-primary);"></span>
                            @endif
                        </div>
                        <p class="gh-muted" style="margin-top:2px; font-size:11.5px; line-height:1.35; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
                            {{ $n['message'] }}
                        </p>
                        <div style="margin-top:6px; display:flex; align-items:center; gap:12px;">
                            <span class="gh-hint">{{ $n['time'] }}</span>
                            @if($n['url'])
                                <a href="{{ $n['url'] }}" @click="open = false" wire:click="markAsRead('{{ $n['id'] }}')" style="font-size:11px; font-weight:600; color:var(--gh-primary);">
                                    View →
                                </a>
                            @endif
                            @if(! $n['read'])
                                <button wire:click="markAsRead('{{ $n['id'] }}')" class="gh-hint" style="border:none; background:none; cursor:pointer;">Mark read</button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:40px 16px; text-align:center;">
                    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="var(--gh-ink-faint)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <p class="gh-muted" style="font-size:12.5px;">All caught up!</p>
                </div>
            @endforelse
        </div>

        @if(count($notifications) > 0)
            <div style="border-top:1px solid var(--gh-hairline); padding:12px 16px;">
                <a href="{{ route('notifications.index') }}" @click="open = false" style="display:block; text-align:center; font-size:12.5px; font-weight:600; color:var(--gh-primary);">
                    View all notifications
                </a>
            </div>
        @endif
    </div>
</div>
