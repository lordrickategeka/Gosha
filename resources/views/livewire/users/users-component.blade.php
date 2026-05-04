<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Staff</h1>
            <p class="text-base-content/60">Manage team members and roles</p>
        </div>
        @can('create_users')
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Staff
        </a>
        @endcan
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name or email..." class="input input-bordered input-sm" />
                <select wire:model.live="role" class="select select-bordered select-sm">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ ucwords(str_replace('-', ' ', $r->name)) }}</option>
                    @endforeach
                </select>
                <select wire:model.live="status" class="select select-bordered select-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Branches</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="hover">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-10">
                                            <span>{{ substr($user->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('users.show', $user) }}" class="font-bold link link-hover">{{ $user->name }}</a>
                                        @if($user->phone)
                                            <p class="text-xs text-base-content/60">{{ $user->phone }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge badge-primary badge-sm">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($user->branches->count() > 0)
                                    <span class="text-sm">{{ $user->branches->pluck('name')->join(', ') }}</span>
                                @else
                                    <span class="text-base-content/40">All branches</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->is_active ? 'success' : 'error' }} badge-sm">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="btn btn-ghost btn-xs">⋮</label>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-40">
                                        <li><a href="{{ route('users.show', $user) }}">View</a></li>
                                        @can('edit_users')
                                            <li><a href="{{ route('users.edit', $user) }}">Edit</a></li>
                                            <li>
                                                <button wire:click="toggleStatus({{ $user->id }})" class="{{ $user->is_active ? 'text-error' : 'text-success' }}">
                                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 text-base-content/50">No staff found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-4 border-t border-base-200">{{ $users->links() }}</div>
        @endif
    </div>
</div>
