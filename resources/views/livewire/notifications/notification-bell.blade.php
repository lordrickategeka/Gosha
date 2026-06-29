<div
    x-data="{ open: false }"
    @click.outside="open = false"
    wire:poll.30000ms="loadNotifications"
    class="relative"
>
    {{-- Bell button --}}
    <button
        @click="open = !open"
        class="btn btn-circle border border-base-300 bg-base-100 text-base-content shadow-none hover:bg-base-200"
        aria-label="Notifications"
    >
        <div class="indicator">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @if($unreadCount > 0)
                <span class="badge badge-xs badge-primary border-0 indicator-item font-bold">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </div>
    </button>

    {{-- Dropdown panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 top-full mt-2 z-50 w-96 rounded-[20px] border border-base-300 bg-base-100 shadow-xl"
        style="display:none"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-base-200 px-4 py-3">
            <div class="flex items-center gap-2">
                <h3 class="font-semibold text-base-content">Notifications</h3>
                @if($unreadCount > 0)
                    <span class="badge badge-primary badge-sm">{{ $unreadCount }} new</span>
                @endif
            </div>
            @if($unreadCount > 0)
                <button
                    wire:click="markAllRead"
                    class="btn btn-ghost btn-xs text-primary"
                >
                    Mark all read
                </button>
            @endif
        </div>

        {{-- Notification list --}}
        <div class="max-h-[420px] overflow-y-auto">
            @forelse($notifications as $n)
                <div
                    wire:key="bell-notif-{{ $n['id'] }}"
                    class="group flex items-start gap-3 border-b border-base-200 px-4 py-3 transition-colors hover:bg-base-200 {{ $n['read'] ? 'opacity-60' : '' }}"
                >
                    {{-- Severity icon --}}
                    <div class="mt-0.5 shrink-0">
                        @if($n['severity'] === 'error')
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-error/10">
                                <svg class="h-4 w-4 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        @elseif($n['severity'] === 'warning')
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-warning/10">
                                <svg class="h-4 w-4 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                            </div>
                        @elseif($n['severity'] === 'success')
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-success/10">
                                <svg class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        @else
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-info/10">
                                <svg class="h-4 w-4 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-medium text-base-content leading-snug">
                                {{ $n['title'] }}
                            </p>
                            @if(! $n['read'])
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary"></span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-xs text-base-content/60 leading-snug line-clamp-2">
                            {{ $n['message'] }}
                        </p>
                        <div class="mt-1.5 flex items-center gap-3">
                            <span class="text-xs text-base-content/40">{{ $n['time'] }}</span>
                            @if($n['url'])
                                <a
                                    href="{{ $n['url'] }}"
                                    @click="open = false"
                                    wire:click="markAsRead('{{ $n['id'] }}')"
                                    class="text-xs font-medium text-primary hover:underline"
                                >
                                    View →
                                </a>
                            @endif
                            @if(! $n['read'])
                                <button
                                    wire:click="markAsRead('{{ $n['id'] }}')"
                                    class="text-xs text-base-content/40 hover:text-base-content"
                                >
                                    Mark read
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center gap-2 px-4 py-10 text-center">
                    <svg class="h-10 w-10 text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-sm text-base-content/50">All caught up!</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if(count($notifications) > 0)
            <div class="border-t border-base-200 px-4 py-3">
                <a
                    href="{{ route('notifications.index') }}"
                    @click="open = false"
                    class="block text-center text-sm font-medium text-primary hover:underline"
                >
                    View all notifications
                </a>
            </div>
        @endif
    </div>
</div>
