<div class="gh-page">
    <div>
        <div style="font-size:21px; font-weight:700; letter-spacing:-0.02em;">My Profile</div>
        <p class="gh-muted" style="font-size:12.5px; margin-top:4px;">Manage your account settings</p>
    </div>

    <div class="gh-stack" style="max-width:42rem;">
        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Profile Information</div>
            <form wire:submit="updateProfile">
                <div class="gh-grid-2">
                    <div class="gh-field" style="grid-column:1/-1;">
                        <span class="gh-label">Name</span>
                        <input type="text" wire:model="name" class="gh-input" style="width:100%;">
                        @error('name') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Email</span>
                        <input type="email" wire:model="email" class="gh-input" style="width:100%;">
                        @error('email') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; margin-top:16px;">
                    <button type="submit" class="gh-btn gh-btn--primary">Save changes</button>
                </div>
            </form>
        </div>

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Change Password</div>
            <form wire:submit="updatePassword">
                <div class="gh-stack">
                    <div class="gh-field">
                        <span class="gh-label">Current password</span>
                        <input type="password" wire:model="current_password" class="gh-input" style="width:100%;">
                        @error('current_password') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">New password</span>
                        <input type="password" wire:model="new_password" class="gh-input" style="width:100%;">
                        @error('new_password') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
                    </div>
                    <div class="gh-field">
                        <span class="gh-label">Confirm new password</span>
                        <input type="password" wire:model="new_password_confirmation" class="gh-input" style="width:100%;">
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; margin-top:16px;">
                    <button type="submit" class="gh-btn gh-btn--primary">Update password</button>
                </div>
            </form>
        </div>

        <div class="gh-card gh-card--pad">
            <div class="gh-card__title" style="margin-bottom:14px;">Account Information</div>
            <div class="gh-stack" style="gap:9px; font-size:12.5px;">
                <div style="display:flex; justify-content:space-between;">
                    <span class="gh-muted">Role</span>
                    <span>
                        @foreach(auth()->user()->roles as $role)
                            <span class="gh-badge gh-badge--primary">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
                        @endforeach
                    </span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="gh-muted">Organization</span>
                    <span>{{ auth()->user()->vendor?->name ?? 'N/A' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="gh-muted">Member since</span>
                    <span>{{ auth()->user()->created_at->format('d M Y') }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="gh-muted">Last login</span>
                    <span>{{ auth()->user()->last_login_at?->diffForHumans() ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
