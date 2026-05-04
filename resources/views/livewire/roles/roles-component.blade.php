<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Roles & Permissions</h1>
            <p class="text-base-content/60">Manage what each role can do in the system</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr class="hover">
                            <td>
                                <span class="font-semibold">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-outline badge-sm">{{ $role->permissions_count }} permissions</span>
                            </td>
                            <td>
                                <span class="text-sm text-base-content/70">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</span>
                            </td>
                            <td class="text-right">
                                @if($role->name !== 'super-admin')
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-ghost">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit Permissions
                                    </a>
                                @else
                                    <span class="text-base-content/40 text-sm">System role</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-base-content/50 py-8">No roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
