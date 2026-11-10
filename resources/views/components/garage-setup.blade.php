@if(session('garage_setup') && auth()->user()?->isVendorOwner())
    <section class="rounded-xl border border-base-300 bg-base-100 p-5 mb-5" aria-label="Garage setup checklist">
        <h2 class="font-semibold text-lg">Your garage is ready. Here's where to start.</h2>
        <p class="text-sm text-base-content/60 mt-1">Add the details you need for your first job. You can return to these pages any time.</p>
        <ul class="flex flex-wrap gap-4 mt-4 text-sm">
            @can('view_service_templates')<li><a href="{{ route('templates.index') }}" class="link link-primary">1. Set up service templates</a></li>@endcan
            @can('create_users')<li><a href="{{ route('users.create') }}" class="link link-primary">2. Invite your staff</a></li>@endcan
            @can('create_customers')<li><a href="{{ route('customers.create') }}" class="link link-primary">3. Add your first customer</a></li>@endcan
        </ul>
    </section>
@endif
