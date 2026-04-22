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

    @livewireStyles
</head>
<body class="min-h-screen bg-base-200">
    <div class="min-h-screen flex">
        <!-- Left side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-primary items-center justify-center p-12">
            <div class="max-w-md text-primary-content">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-16 h-16 bg-primary-content/20 rounded-2xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold">GarageHQ</h1>
                        <p class="text-primary-content/70">Garage Management Platform</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-primary-content/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Work Order Management</h3>
                            <p class="text-sm text-primary-content/70">Track repairs from check-in to delivery with full history</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-primary-content/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Integrated Wash Bay</h3>
                            <p class="text-sm text-primary-content/70">Seamless combo flows from service to wash</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-primary-content/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Real-time Reports</h3>
                            <p class="text-sm text-primary-content/70">Track revenue, expenses, and staff performance</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right side - Auth Form -->
        <div class="flex-1 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-8">
                    <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-primary-content" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold">GarageHQ</span>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
