<!-- Topbar -->
<header class="gh-topbar">
    <div style="display:flex; align-items:center; gap:14px; min-width:0;">
        <button type="button" class="gh-btn gh-btn--sm gh-menu-toggle" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle menu">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:18px; height:18px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <div style="min-width:0;">
            <div class="gh-eyebrow">Garage operations</div>
            <div class="gh-topbar__title" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $title ?? 'Dashboard' }}</div>
        </div>
    </div>

    <div class="gh-topbar__actions">
        <label class="gh-search hidden md:flex">
            ⌕ <input type="search" placeholder="Search…">
        </label>

        <!-- Branch selector -->
        @if(auth()->check() && auth()->user() && auth()->user()->branches && auth()->user()->branches->count() >= 1)
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="gh-btn gh-btn--sm">
                    <x-icon name="branches" class="gh-icon" style="width:15px; height:15px;" />
                    <span class="hidden md:inline">{{ session('current_branch_name', 'Select Branch') }}</span>
                    @if(auth()->user()->branches->count() > 1)
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:13px; height:13px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    @endif
                </label>
                @if(auth()->user()->branches->count() > 1)
                    <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-56 gh-card p-2 shadow-xl">
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
        @auth
            <livewire:notifications.bell />
        @endauth

        <!-- User menu -->
        @auth
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="gh-sidebar__mark" style="width:33px; height:33px; border-radius:50%; font-size:12px; cursor:pointer;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </label>
                <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-56 gh-card p-2 shadow-xl">
                    <li class="menu-title"><span>{{ auth()->user()->name }}</span></li>
                    <li><a href="{{ route('profile') }}">Profile</a></li>
                    @can('view_settings')
                        <li><a href="{{ route('settings') }}">Settings</a></li>
                    @endcan
                    <div class="divider my-0"></div>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left" style="color:var(--gh-error);">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            <a href="{{ route('login') }}" class="gh-btn gh-btn--sm">Login</a>
        @endauth
    </div>
</header>
