<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="garage">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'GarageHQ') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="min-h-screen bg-base-200">
    <div class="drawer lg:drawer-open">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />

        <!-- Page Content -->
        <div class="drawer-content flex flex-col">
            <!-- Navbar -->
            @include('components.partials.navbar')

            <!-- Main Content -->
            <main class="flex-1 p-4 lg:p-6">
                {{ $slot }}
            </main>
        </div>

        <!-- Sidebar -->
        @include('components.partials.sidebar')
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Toast Notifications (Global + Reusable) -->
    <div x-data="globalToasts()" x-init="init()" class="toast toast-end toast-top z-50 mt-3 gap-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="alert text-base-content shadow-lg transition-all duration-300" :class="toastClass(toast.type)">
                <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg x-show="toast.type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <svg x-show="toast.type === 'info'" xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <span x-text="toast.message"></span>
                <button type="button" class="btn btn-ghost btn-xs ml-2" @click="dismiss(toast.id)">✕</button>
            </div>
        </template>
    </div>

    <script>
        function globalToasts() {
            return {
                toasts: [],
                nextId: 1,

                init() {
                    this.enqueueFromSession();

                    document.addEventListener('livewire:init', () => {
                        Livewire.on('notify', (payload) => {
                            const data = this.normalizePayload(payload);
                            if (!data.message) {
                                return;
                            }

                            this.push(data.type || 'info', data.message);
                        });
                    });
                },

                enqueueFromSession() {
                    const flashes = [
                        { type: 'success', message: @json(session('success')) },
                        { type: 'error', message: @json(session('error')) },
                        { type: 'warning', message: @json(session('warning')) },
                        { type: 'info', message: @json(session('info')) },
                    ];

                    flashes.forEach((flash) => {
                        if (flash.message) {
                            this.push(flash.type, flash.message);
                        }
                    });
                },

                normalizePayload(payload) {
                    if (Array.isArray(payload)) {
                        const first = payload[0] ?? {};
                        if (typeof first === 'string') {
                            return { type: 'info', message: first };
                        }
                        return first;
                    }

                    if (payload && typeof payload === 'object' && Array.isArray(payload.detail)) {
                        return payload.detail[0] ?? {};
                    }

                    return payload && typeof payload === 'object'
                        ? payload
                        : { type: 'info', message: String(payload ?? '') };
                },

                push(type, message) {
                    const id = this.nextId++;
                    this.toasts.push({ id, type, message });
                    setTimeout(() => this.dismiss(id), 5000);
                },

                dismiss(id) {
                    this.toasts = this.toasts.filter((toast) => toast.id !== id);
                },

                toastClass(type) {
                    switch (type) {
                        case 'success':
                            return 'border-success/20 bg-base-100';
                        case 'error':
                            return 'border-error/20 bg-base-100';
                        case 'warning':
                            return 'border-warning/20 bg-base-100';
                        default:
                            return 'border-info/20 bg-base-100';
                    }
                },
            };
        }
    </script>

    @stack('scripts')
</body>
</html>
