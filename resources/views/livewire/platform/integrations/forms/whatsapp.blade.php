<div class="gh-grid-2">
    <div class="gh-field">
        <span class="gh-label">Base URL</span>
        <input type="text" wire:model.defer="credentials.base_url" class="gh-input" style="width:100%;" placeholder="https://graph.facebook.com">
        @error('credentials.base_url') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
    <div class="gh-field">
        <span class="gh-label">API version</span>
        <input type="text" wire:model.defer="credentials.api_version" class="gh-input" style="width:100%;" placeholder="v22.0">
        @error('credentials.api_version') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
    <div class="gh-field">
        <span class="gh-label">Phone number ID</span>
        <input type="text" wire:model.defer="credentials.phone_number_id" class="gh-input" style="width:100%;" placeholder="Phone number ID">
        @error('credentials.phone_number_id') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
    <div class="gh-field" style="grid-column:1/-1;">
        <span class="gh-label">Access token</span>
        <input type="password" wire:model.defer="credentials.access_token" class="gh-input" style="width:100%;" placeholder="Access token">
        @error('credentials.access_token') <span class="gh-hint" style="color:var(--gh-error);">{{ $message }}</span> @enderror
    </div>
</div>
