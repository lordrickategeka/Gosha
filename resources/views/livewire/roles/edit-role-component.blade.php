<div class="gh-page">
    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:14px;">
        <div>
            <a href="{{ route('roles.index') }}" class="gh-muted" style="font-size:12px; display:flex; align-items:center; gap:4px; margin-bottom:4px;">← Back to roles</a>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">{{ ucwords(str_replace('-', ' ', $role->name)) }}</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Assign permissions to this role</p>
        </div>
        <button wire:click="save" class="gh-btn gh-btn--primary" wire:loading.attr="disabled">
            <span wire:loading.remove>Save permissions</span>
            <span wire:loading>Saving…</span>
        </button>
    </div>

    <div class="gh-grid-2">
        @foreach($permissionGroups as $group => $perms)
            @php
                $available = \Spatie\Permission\Models\Permission::whereIn('name', $perms)->pluck('name')->toArray();
                $allChecked = count($available) > 0 && count(array_intersect($available, $selectedPermissions)) === count($available);
            @endphp

            @if(count($available) > 0)
                <div class="gh-card gh-card--pad">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                        <span style="font-weight:700; font-size:13px;">{{ $group }}</span>
                        <button type="button" wire:click="toggleGroup('{{ $group }}')" class="gh-btn gh-btn--sm {{ $allChecked ? 'gh-btn--primary' : '' }}">
                            {{ $allChecked ? 'Deselect all' : 'Select all' }}
                        </button>
                    </div>
                    <div class="gh-stack" style="gap:2px;">
                        @foreach($available as $perm)
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; padding:5px 8px; border-radius:6px; font-size:12.5px;">
                                <input type="checkbox" value="{{ $perm }}" wire:model="selectedPermissions">
                                {{ ucwords(str_replace('_', ' ', $perm)) }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div style="display:flex; justify-content:flex-end;">
        <button wire:click="save" class="gh-btn gh-btn--primary" wire:loading.attr="disabled">
            <span wire:loading.remove>Save permissions</span>
            <span wire:loading>Saving…</span>
        </button>
    </div>
</div>
