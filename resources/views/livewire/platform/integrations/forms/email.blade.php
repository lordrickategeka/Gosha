<div class="gh-grid-2">
    <div class="gh-field">
        <span class="gh-label">SMTP host</span>
        <input type="text" wire:model.defer="credentials.host" class="gh-input" style="width:100%;" placeholder="smtp.mailprovider.com">
        @error('credentials.host') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
    <div class="gh-field">
        <span class="gh-label">SMTP port</span>
        <input type="number" wire:model.defer="credentials.port" class="gh-input" style="width:100%;" placeholder="587">
        @error('credentials.port') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
    <div class="gh-field">
        <span class="gh-label">Username</span>
        <input type="text" wire:model.defer="credentials.username" class="gh-input" style="width:100%;" placeholder="SMTP username">
        @error('credentials.username') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
    <div class="gh-field">
        <span class="gh-label">Password</span>
        <input type="password" wire:model.defer="credentials.password" class="gh-input" style="width:100%;" placeholder="SMTP password">
        @error('credentials.password') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
    <div class="gh-field" style="grid-column:1/-1;">
        <span class="gh-label">Encryption</span>
        <input type="text" wire:model.defer="credentials.encryption" class="gh-input" style="width:100%;" placeholder="tls or ssl">
        @error('credentials.encryption') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
</div>
