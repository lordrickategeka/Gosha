<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Roles &amp; Permissions</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage what each role can do in the system</p>
    </div>

    <div class="gh-card gh-card--flush">
        <div class="gh-table-scroll">
            <table class="gh-table">
                <thead><tr><th>Role</th><th>Permissions</th><th>Users</th><th></th></tr></thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td style="font-weight:700;">{{ ucwords(str_replace('-', ' ', $role->name)) }}</td>
                            <td><span class="gh-badge">{{ $role->permissions_count }} permissions</span></td>
                            <td class="gh-muted">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</td>
                            <td style="text-align:right;">
                                @if($role->name !== 'super-admin')
                                    <a href="{{ route('roles.edit', $role->id) }}" class="gh-btn gh-btn--sm">Edit permissions</a>
                                @else
                                    <span class="gh-hint">System role</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center; padding:40px; color:var(--gh-ink-faint);">No roles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
