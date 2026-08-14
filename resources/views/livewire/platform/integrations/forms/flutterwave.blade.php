<div class="gh-grid-2">
    <div class="gh-field" style="grid-column:1/-1;">
        <span class="gh-label">Base URL</span>
        <input type="text" wire:model.defer="credentials.base_url" class="gh-input" style="width:100%;" placeholder="https://api.flutterwave.com">
        @error('credentials.base_url') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
    <div class="gh-field">
        <span class="gh-label">Public key</span>
        <input type="password" wire:model.defer="credentials.public_key" class="gh-input" style="width:100%;" placeholder="Flutterwave public key">
        @error('credentials.public_key') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
    <div class="gh-field">
        <span class="gh-label">Secret key</span>
        <input type="password" wire:model.defer="credentials.secret_key" class="gh-input" style="width:100%;" placeholder="Flutterwave secret key">
        @error('credentials.secret_key') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
    <div class="gh-field" style="grid-column:1/-1;">
        <span class="gh-label">Encryption key</span>
        <input type="password" wire:model.defer="credentials.encryption_key" class="gh-input" style="width:100%;" placeholder="Optional encryption key">
        @error('credentials.encryption_key') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
</div>
