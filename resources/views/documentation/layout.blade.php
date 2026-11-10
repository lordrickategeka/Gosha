<!DOCTYPE html>
<html lang="en" data-theme="garage">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="GarageHQ user guide and API reference for garage owners, staff, parts suppliers and platform administrators.">
    @vite(['resources/css/app.css', 'resources/css/documentation.css'])
    @yield('assets')
</head>
<body class="docs-body">
    <a href="#main-content" class="docs-skip">Skip to content</a>
    <header class="docs-header">
        <a href="{{ route('user-guide.index') }}" class="docs-brand"><span class="docs-mark">G</span><span>GarageHQ <small>Help centre</small></span></a>
        <nav aria-label="Help centre navigation">
            <a href="{{ route('user-guide.index') }}" @if(request()->routeIs('user-guide.*')) aria-current="page" @endif>User guide</a>
            <a href="{{ route('api-docs.index') }}" @if(request()->routeIs('api-docs.*')) aria-current="page" @endif>API reference</a>
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="docs-app-link">{{ auth()->check() ? 'Back to app' : 'Sign in' }} <span aria-hidden="true">↗</span></a>
        </nav>
    </header>
    @yield('content')
    <footer class="docs-footer">GarageHQ help centre · Screens and actions depend on your role, branch and current plan.</footer>
</body>
</html>
