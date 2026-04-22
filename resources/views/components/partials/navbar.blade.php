<!-- Navbar -->
<div class="navbar bg-base-100 shadow-sm border-b border-base-300 sticky top-0 z-30">
    <!-- Mobile menu button -->
    <div class="flex-none lg:hidden">
        <label for="sidebar-drawer" class="btn btn-square btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </label>
    </div>

    <!-- Breadcrumb / Page Title -->
    <div class="flex-1 px-2">
        <div class="text-sm breadcrumbs hidden sm:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                @if(isset($breadcrumbs))
                    @foreach($breadcrumbs as $label => $url)
                        @if($url)
                            <li><a href="{{ $url }}">{{ $label }}</a></li>
                        @else
                            <li>{{ $label }}</li>
                        @endif
                    @endforeach
                @endif
            </ul>
        </div>
    </div>

    <!-- Right side items -->
    <div class="flex-none gap-2">
        <!-- Branch Selector -->
        @if(auth()->user()->branches->count() >= 1)
        <div class="dropdown dropdown-end">
            <label tabindex="0" class="btn btn-ghost btn-sm gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="hidden md:inline">{{ session('current_branch_name', 'Select Branch') }}</span>
                @if(auth()->user()->branches->count() > 1)
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                @endif
            </label>
            @if(auth()->user()->branches->count() > 1)
            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                @foreach(auth()->user()->branches as $branch)
                    <li>
                        <a href="{{ route('branch.switch', $branch) }}" class="{{ session('current_branch_id') == $branch->id ? 'active' : '' }}">
                            {{ $branch->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
            @endif
        </div>
        @endif

        <!-- Notifications -->
        <div class="dropdown dropdown-end">
            <label tabindex="0" class="btn btn-ghost btn-circle">
                <div class="indicator">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="badge badge-xs badge-primary indicator-item"></span>
                </div>
            </label>
            <div tabindex="0" class="dropdown-content z-[1] card card-compact w-80 p-2 shadow-lg bg-base-100">
                <div class="card-body">
                    <h3 class="font-bold text-lg">Notifications</h3>
                    <div class="divider my-0"></div>
                    <div class="text-sm text-base-content/70">
                        No new notifications
                    </div>
                </div>
            </div>
        </div>

        <!-- User Menu -->
        <div class="dropdown dropdown-end">
            <label tabindex="0" class="btn btn-ghost btn-circle avatar placeholder">
                <div class="bg-primary text-primary-content rounded-full w-10">
                    <span class="text-sm">{{ substr(auth()->user()->name, 0, 2) }}</span>
                </div>
            </label>
            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                <li class="menu-title">
                    <span>{{ auth()->user()->name }}</span>
                </li>
                <li><a href="{{ route('profile') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                </a></li>
                @can('view settings')
                <li><a href="{{ route('settings') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a></li>
                @endcan
                <div class="divider my-0"></div>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left text-error">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
