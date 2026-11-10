<div class="max-w-4xl mx-auto p-6 space-y-5">
    <h1 class="text-2xl font-bold">Supplier team</h1>
    <p>{{ $members->count() }} / {{ $seatLimit ?? 'unlimited' }} team members, including administrators. <a href="{{ route('billing.subscription') }}" class="link link-primary">Manage your plan</a></p>
    @if($errors->any())<div class="alert alert-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="card bg-base-100 shadow"><div class="card-body">
        <h2 class="card-title">Current members</h2>
        @foreach($members as $member)<div class="flex flex-wrap justify-between items-center gap-3 border-b border-base-300 py-3" wire:key="member-{{ $member->id }}">
            <div>{{ $member->name }} <span class="text-sm text-base-content/60">{{ $member->email }}</span> @if($member->hasRole('supplier_admin'))<span class="badge badge-sm">Administrator</span>@endif</div>
            @if($member->hasRole('supplier_staff') && !$member->hasRole('supplier_admin'))<button type="button" wire:click="removeMember({{ $member->id }})" wire:confirm="Remove this team member's access?" class="btn btn-sm btn-ghost">Remove access</button>@endif
        </div>@endforeach
    </div></div>
    @if($canAdd)
        <form wire:submit="addMember" class="card bg-base-100 shadow"><div class="card-body space-y-4">
            <h2 class="card-title">Add a team member</h2>
            <p class="text-sm text-base-content/60">Team members get supplier staff permissions. They must change the initial password on first sign-in.</p>
            <div class="grid sm:grid-cols-2 gap-4"><x-registration-field name="name" label="Full name" required /><x-registration-field name="email" label="Email" type="email" required /></div>
            <div class="grid sm:grid-cols-2 gap-4"><x-registration-field name="password" label="Initial password" type="password" required autocomplete="new-password" /><x-registration-field name="password_confirmation" label="Confirm password" type="password" required autocomplete="new-password" /></div>
            <button type="submit" wire:loading.attr="disabled" class="btn btn-primary">Add team member</button>
        </div></form>
    @else<p>Your plan or business approval does not currently allow adding members. Existing member access can still be removed.</p>@endif
    <a href="{{ route('supplier.dashboard') }}" class="link link-primary">Back to supplier dashboard</a>
</div>
