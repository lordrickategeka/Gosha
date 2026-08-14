<div class="gh-page">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Notifications</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">
                @if($unreadCount > 0)
                    You have <strong style="color:var(--gh-primary);">{{ $unreadCount }}</strong> unread notification{{ $unreadCount !== 1 ? 's' : '' }}
                @else
                    All caught up
                @endif
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            @if($unreadCount > 0)
                <button wire:click="markAllRead" class="gh-btn gh-btn--sm">Mark all read</button>
            @endif
            @if($notifications->total() > 0)
                <button wire:click="deleteAll" wire:confirm="Delete all {{ $tab === 'unread' ? 'unread' : '' }} notifications?" class="gh-btn gh-btn--sm" style="color:var(--gh-error);">
                    Delete all
                </button>
            @endif
        </div>
    </div>

    <div style="display:flex; gap:6px; padding:4px; background:var(--gh-base-200); border-radius:var(--gh-radius-pill); width:fit-content;">
        <button wire:click="$set('tab', 'all')" class="gh-chip gh-chip--round {{ $tab === 'all' ? 'is-active' : '' }}" style="border:none;">All</button>
        <button wire:click="$set('tab', 'unread')" class="gh-chip gh-chip--round {{ $tab === 'unread' ? 'is-active' : '' }}" style="border:none;">
            Unread
            @if($unreadCount > 0)
                <span class="gh-badge gh-badge--primary" style="margin-left:4px;">{{ $unreadCount }}</span>
            @endif
        </button>
    </div>

    <div class="gh-card">
        @forelse($notifications as $notification)
            @php
                $data     = $notification->data;
                $isRead   = ! is_null($notification->read_at);
                $severity = $data['severity'] ?? 'info';
            @endphp
            <div wire:key="nc-{{ $notification->id }}" style="display:flex; align-items:flex-start; gap:14px; border-bottom:1px solid var(--gh-hairline); padding:16px 20px; {{ $isRead ? '' : 'background:var(--gh-primary-tint);' }}">
                <div style="margin-top:2px; flex-shrink:0;">
                    @if($severity === 'error')
                        <div style="display:flex; height:38px; width:38px; align-items:center; justify-content:center; border-radius:50%; background:var(--gh-error-bg);">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--gh-error)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    @elseif($severity === 'warning')
                        <div style="display:flex; height:38px; width:38px; align-items:center; justify-content:center; border-radius:50%; background:var(--gh-warning-bg);">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--gh-warning)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        </div>
                    @elseif($severity === 'success')
                        <div style="display:flex; height:38px; width:38px; align-items:center; justify-content:center; border-radius:50%; background:var(--gh-success-bg);">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--gh-success)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    @else
                        <div style="display:flex; height:38px; width:38px; align-items:center; justify-content:center; border-radius:50%; background:var(--gh-info-bg);">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--gh-info)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    @endif
                </div>

                <div style="min-width:0; flex:1;">
                    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:14px;">
                        <div style="min-width:0;">
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <p style="font-weight:700; font-size:13px;">{{ $data['title'] ?? 'Notification' }}</p>
                                @if(! $isRead)
                                    <span class="gh-badge gh-badge--primary">New</span>
                                @endif
                                @if(isset($data['domain']))
                                    <span class="gh-badge" style="text-transform:capitalize;">{{ $data['domain'] }}</span>
                                @endif
                            </div>
                            <p class="gh-muted" style="margin-top:4px; font-size:12.5px;">{{ $data['message'] ?? '' }}</p>

                            @if(isset($data['branch_name']) || isset($data['category']))
                                <div style="margin-top:6px; display:flex; flex-wrap:wrap; gap:10px;">
                                    @if(isset($data['branch_name']))
                                        <span class="gh-hint">{{ $data['branch_name'] }}</span>
                                    @endif
                                    @if(isset($data['category']))
                                        <span class="gh-hint">{{ $data['category'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <span class="gh-hint" style="flex-shrink:0; white-space:nowrap;">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>

                    <div style="margin-top:8px; display:flex; align-items:center; gap:12px;">
                        @if(isset($data['url']))
                            <a href="{{ $data['url'] }}" wire:click="markAsRead('{{ $notification->id }}')" style="font-size:11.5px; font-weight:600; color:var(--gh-primary);">
                                View →
                            </a>
                        @endif
                        @if(! $isRead)
                            <button wire:click="markAsRead('{{ $notification->id }}')" class="gh-hint" style="border:none; background:none; cursor:pointer;">Mark as read</button>
                        @endif
                        <button wire:click="deleteNotification('{{ $notification->id }}')" wire:confirm="Delete this notification?" style="font-size:11.5px; color:var(--gh-error); opacity:.6; border:none; background:none; cursor:pointer;">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div style="display:flex; flex-direction:column; align-items:center; gap:10px; padding:60px 16px; text-align:center;">
                <svg width="52" height="52" fill="none" viewBox="0 0 24 24" stroke="var(--gh-ink-faint)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <p style="font-size:14px; font-weight:600; color:var(--gh-ink-faint);">No notifications here</p>
                <p class="gh-muted" style="font-size:12px;">
                    {{ $tab === 'unread' ? 'All notifications have been read.' : 'Notifications from all modules will appear here.' }}
                </p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif
</div>
