<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Staff</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage team members and roles</p>
        </div>
        @can('create_users')
            <a href="{{ route('users.create') }}" class="gh-btn gh-btn--primary">+ Add staff</a>
        @endcan
    </div>

    <div class="gh-table-toolbar">
        <div class="gh-table-toolbar__filters">
            <label class="gh-search" style="width:190px;">⌕ <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search…"></label>
            <select wire:model.live="role" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}">{{ ucwords(str_replace('-', ' ', $r->name)) }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" class="gh-select" style="padding:6px 10px; font-size:12px;">
                <option value="">All status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            @if($search || $role || $status)
                <button wire:click="clearFilters" class="gh-btn gh-btn--sm">Clear</button>
            @endif
        </div>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Branches</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($users as $user)
                        <tr data-href="{{ route('users.show', $user) }}">
                            <td>
                                <div class="gh-cell-stack">
                                    <a href="{{ route('users.show', $user) }}" class="is-ref">{{ $user->name }}</a>
                                    @if($user->phone)<span>{{ $user->phone }}</span>@endif
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="gh-badge gh-badge--primary">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($user->branches->count() > 0)
                                    {{ $user->branches->pluck('name')->join(', ') }}
                                @else
                                    <span class="gh-muted">All branches</span>
                                @endif
                            </td>
                            <td><span class="gh-badge {{ $user->is_active ? 'gh-badge--success' : 'gh-badge--error' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td onclick="event.stopPropagation()">
                                <div class="dropdown dropdown-end">
                                    <label tabindex="0" class="gh-btn gh-btn--sm">⋮</label>
                                    <ul tabindex="0" class="dropdown-content menu z-[1] mt-2 w-40 gh-card p-2 shadow-xl">
                                        <li><a href="{{ route('users.show', $user) }}">View</a></li>
                                        @can('edit_users')
                                            <li><a href="{{ route('users.edit', $user) }}">Edit</a></li>
                                            <li><button wire:click="toggleStatus({{ $user->id }})" style="color:{{ $user->is_active ? 'var(--gh-error)' : 'var(--gh-success)' }};">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button></li>
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No staff found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="gh-pagination">{{ $users->links() }}</div>
        @endif
    </div>
</div>
