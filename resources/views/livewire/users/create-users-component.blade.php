<div class="gh-page">
    <div style="display:flex; align-items:center; gap:14px;">
        <a href="{{ route('users.index') }}" class="gh-btn gh-btn--sm">←</a>
        <div>
            <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">Add Staff</div>
            <p class="gh-muted" style="font-size:12.5px; margin-top:2px;">Create a new team member</p>
        </div>
    </div>

    <form wire:submit="save" style="max-width:42rem;">
        <div class="gh-card gh-card--pad">
            <div class="gh-grid-2">
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Full name *</span>
                    <input type="text" wire:model="name" class="gh-input" style="width:100%;">
                    @error('name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Email *</span>
                    <input type="email" wire:model="email" class="gh-input" style="width:100%;">
                    @error('email') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Phone</span>
                    <input type="text" wire:model="phone" class="gh-input" style="width:100%;">
                </div>
                <div class="gh-field">
                    <span class="gh-label">Password *</span>
                    <input type="password" wire:model="password" class="gh-input" style="width:100%;">
                    @error('password') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field">
                    <span class="gh-label">Role *</span>
                    <select wire:model="role" class="gh-select" style="width:100%;">
                        <option value="">Select role…</option>
                        @foreach($this->roles as $r)
                            <option value="{{ $r->name }}">{{ ucwords(str_replace('-', ' ', $r->name)) }}</option>
                        @endforeach
                    </select>
                    @error('role') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                </div>
                <div class="gh-field" style="grid-column:1/-1;">
                    <span class="gh-label">Assign to branches</span>
                    <div style="display:flex; flex-wrap:wrap; gap:8px;">
                        @foreach($this->branchesList as $branch)
                            <label style="display:flex; align-items:center; gap:8px; border:1px solid var(--gh-base-300); border-radius:var(--gh-radius); padding:7px 12px; cursor:pointer; font-size:12.5px;">
                                <input type="checkbox" wire:model="branches" value="{{ $branch->id }}">
                                {{ $branch->name }}
                            </label>
                        @endforeach
                    </div>
                    <span class="gh-hint">Leave empty to allow access to all branches</span>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; border-top:1px solid var(--gh-hairline); padding-top:16px; margin-top:20px;">
                <a href="{{ route('users.index') }}" class="gh-btn">Cancel</a>
                <button type="submit" class="gh-btn gh-btn--primary">Create staff</button>
            </div>
        </div>
    </form>
</div>
