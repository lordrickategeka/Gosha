<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="form-control md:col-span-2">
        <label class="label"><span class="label-text">Base URL</span></label>
        <input type="text" wire:model.defer="credentials.base_url" class="input input-bordered w-full" placeholder="https://api.flutterwave.com" />
        @error('credentials.base_url')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
    <div class="form-control">
        <label class="label"><span class="label-text">Public Key</span></label>
        <input type="password" wire:model.defer="credentials.public_key" class="input input-bordered w-full" placeholder="Flutterwave public key" />
        @error('credentials.public_key')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
    <div class="form-control">
        <label class="label"><span class="label-text">Secret Key</span></label>
        <input type="password" wire:model.defer="credentials.secret_key" class="input input-bordered w-full" placeholder="Flutterwave secret key" />
        @error('credentials.secret_key')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
    <div class="form-control md:col-span-2">
        <label class="label"><span class="label-text">Encryption Key</span></label>
        <input type="password" wire:model.defer="credentials.encryption_key" class="input input-bordered w-full" placeholder="Optional encryption key" />
        @error('credentials.encryption_key')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
</div>
