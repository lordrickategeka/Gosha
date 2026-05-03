<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="garage">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'GarageHQ') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body class="app-shell">
    <div class="drawer lg:drawer-open">
        <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />

        <!-- Page Content -->
        <div class="drawer-content flex flex-col">
            <!-- Navbar -->
            @include('components.partials.navbar')

            <!-- Main Content -->
            <main class="app-main">
                <div class="app-content-wrap">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <!-- Sidebar -->
        @include('components.partials.sidebar')
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Toast Notifications -->
    <div class="toast toast-end toast-top z-50 mt-3">
        @if (session('success'))
            <div class="alert border-success/20 bg-base-100 text-base-content shadow-lg" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert border-error/20 bg-base-100 text-base-content shadow-lg" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    @stack('scripts')
</body>

</html>
