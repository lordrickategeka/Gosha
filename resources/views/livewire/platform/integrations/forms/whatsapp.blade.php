<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="form-control">
        <label class="label"><span class="label-text">Base URL</span></label>
        <input type="text" wire:model.defer="credentials.base_url" class="input input-bordered w-full" placeholder="https://graph.facebook.com" />
        @error('credentials.base_url')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
    <div class="form-control">
        <label class="label"><span class="label-text">API Version</span></label>
        <input type="text" wire:model.defer="credentials.api_version" class="input input-bordered w-full" placeholder="v22.0" />
        @error('credentials.api_version')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
    <div class="form-control">
        <label class="label"><span class="label-text">Phone Number ID</span></label>
        <input type="text" wire:model.defer="credentials.phone_number_id" class="input input-bordered w-full" placeholder="Phone number ID" />
        @error('credentials.phone_number_id')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
    <div class="form-control md:col-span-2">
        <label class="label"><span class="label-text">Access Token</span></label>
        <input type="password" wire:model.defer="credentials.access_token" class="input input-bordered w-full" placeholder="Access token" />
        @error('credentials.access_token')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
</div>
