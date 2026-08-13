<!-- Sidebar -->
<aside class="gh-sidebar" :class="{ 'is-open': sidebarOpen }">
    <div class="gh-sidebar__brand">
        <a href="{{ route('dashboard') }}" class="gh-sidebar__mark" style="color:inherit;">G</a>
        <div>
            <div class="gh-sidebar__name">GarageHQ</div>
            @auth
                @if(auth()->user()->vendor)
                    <div class="gh-sidebar__tenant">{{ auth()->user()->vendor->name }}</div>
                @endif
            @endauth
        </div>
    </div>

    @php
        $currentModule = \App\Shared\Navigation\ModuleRegistry::current(request()->route()?->getName());
    @endphp

    <nav class="gh-sidebar__nav">
        @can('view_dashboard')
            <a href="{{ route('dashboard') }}" class="gh-nav-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                <span class="gh-nav-item__label"><x-icon name="dashboard" class="gh-icon" /> Dashboard</span>
            </a>
        @endcan

        <a href="{{ route('modules.index') }}" class="gh-nav-item gh-nav-item--all {{ request()->routeIs('modules.index') ? 'is-active' : '' }}">
            <span class="gh-nav-item__label"><x-icon name="modules" class="gh-icon" /> All modules</span>
        </a>

        @if ($currentModule)
            <div class="gh-module-chip">
                <div class="gh-module-chip__icon"><x-icon :name="$currentModule['icon']" class="gh-icon" /></div>
                <div>
                    <div class="gh-eyebrow">Module</div>
                    <div style="font-size:12.5px; font-weight:700;">{{ $currentModule['name'] }}</div>
                </div>
            </div>

            @foreach (\App\Shared\Navigation\ModuleRegistry::visibleItems($currentModule) as $item)
                <a href="{{ route($item['route']) }}" class="gh-nav-item {{ request()->routeIs($item['route']) ? 'is-active' : '' }}">
                    <span class="gh-nav-item__label"><x-icon :name="$item['icon']" class="gh-icon" /> {{ $item['label'] }}</span>
                </a>
            @endforeach
        @endif
    </nav>

    @auth
        <div class="gh-sidebar__foot">
            <div class="gh-sidebar__mark" style="width:33px; height:33px; border-radius:50%; font-size:12px;">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div>
                <div style="font-size:12.5px; font-weight:600;">{{ auth()->user()->name }}</div>
                <div style="font-size:11px; color:var(--gh-ink-subtle);">{{ \Illuminate\Support\Str::headline(auth()->user()->roles->first()?->name ?? 'Staff') }}</div>
            </div>
        </div>
    @endauth
</aside>
