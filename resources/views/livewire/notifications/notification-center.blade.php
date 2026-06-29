<div>
    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Notifications</h1>
            <p class="mt-1 text-sm text-base-content/60">
                @if($unreadCount > 0)
                    You have <span class="font-semibold text-primary">{{ $unreadCount }}</span> unread notification{{ $unreadCount !== 1 ? 's' : '' }}
                @else
                    All caught up
                @endif
            </p>
        </div>
        <div class="flex gap-2">
            @if($unreadCount > 0)
                <button wire:click="markAllRead" class="btn btn-sm btn-outline btn-primary rounded-xl">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mark all read
                </button>
            @endif
            @if($notifications->total() > 0)
                <button
                    wire:click="deleteAll"
                    wire:confirm="Delete all {{ $tab === 'unread' ? 'unread' : '' }} notifications?"
                    class="btn btn-sm btn-outline btn-error rounded-xl"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete all
                </button>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="tabs tabs-boxed mb-4 w-fit rounded-xl bg-base-200 p-1">
        <button
            wire:click="$set('tab', 'all')"
            class="tab rounded-lg {{ $tab === 'all' ? 'tab-active bg-base-100 shadow-sm' : '' }}"
        >
            All
        </button>
        <button
            wire:click="$set('tab', 'unread')"
            class="tab rounded-lg {{ $tab === 'unread' ? 'tab-active bg-base-100 shadow-sm' : '' }}"
        >
            Unread
            @if($unreadCount > 0)
                <span class="badge badge-primary badge-sm ml-1">{{ $unreadCount }}</span>
            @endif
        </button>
    </div>

    {{-- Notifications list --}}
    <div class="card rounded-[20px] border border-base-300 bg-base-100 shadow-sm">
        @forelse($notifications as $notification)
            @php
                $data     = $notification->data;
                $isRead   = ! is_null($notification->read_at);
                $severity = $data['severity'] ?? 'info';
            @endphp
            <div
                wire:key="nc-{{ $notification->id }}"
                class="flex items-start gap-4 border-b border-base-200 px-5 py-4 transition-colors last:border-0 hover:bg-base-50 {{ $isRead ? '' : 'bg-primary/5' }}"
            >
                {{-- Severity icon --}}
                <div class="mt-0.5 shrink-0">
                    @if($severity === 'error')
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-error/10">
                            <svg class="h-5 w-5 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @elseif($severity === 'warning')
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-warning/10">
                            <svg class="h-5 w-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                    @elseif($severity === 'success')
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-success/10">
                            <svg class="h-5 w-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @else
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-info/10">
                            <svg class="h-5 w-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Body --}}
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-base-content text-sm">{{ $data['title'] ?? 'Notification' }}</p>
                                @if(! $isRead)
                                    <span class="badge badge-primary badge-xs">New</span>
                                @endif
                                @if(isset($data['domain']))
                                    <span class="badge badge-ghost badge-xs capitalize">{{ $data['domain'] }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-base-content/70">{{ $data['message'] ?? '' }}</p>

                            {{-- Extra context fields --}}
                            @if(isset($data['branch_name']) || isset($data['category']))
                                <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1">
                                    @if(isset($data['branch_name']))
                                        <span class="flex items-center gap-1 text-xs text-base-content/50">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                                            </svg>
                                            {{ $data['branch_name'] }}
                                        </span>
                                    @endif
                                    @if(isset($data['category']))
                                        <span class="text-xs text-base-content/50">{{ $data['category'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <span class="shrink-0 whitespace-nowrap text-xs text-base-content/40">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-2 flex items-center gap-3">
                        @if(isset($data['url']))
                            <a href="{{ $data['url'] }}"
                               wire:click="markAsRead('{{ $notification->id }}')"
                               class="text-xs font-medium text-primary hover:underline">
                                View →
                            </a>
                        @endif
                        @if(! $isRead)
                            <button wire:click="markAsRead('{{ $notification->id }}')"
                                    class="text-xs text-base-content/40 hover:text-base-content">
                                Mark as read
                            </button>
                        @endif
                        <button
                            wire:click="deleteNotification('{{ $notification->id }}')"
                            wire:confirm="Delete this notification?"
                            class="text-xs text-error/50 hover:text-error"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center gap-3 px-4 py-16 text-center">
                <svg class="h-14 w-14 text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-base font-medium text-base-content/50">No notifications here</p>
                <p class="text-sm text-base-content/30">
                    {{ $tab === 'unread' ? 'All notifications have been read.' : 'Notifications from all modules will appear here.' }}
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
