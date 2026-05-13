<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="form-control">
        <label class="label"><span class="label-text">SMTP Host</span></label>
        <input type="text" wire:model.defer="credentials.host" class="input input-bordered w-full" placeholder="smtp.mailprovider.com" />
        @error('credentials.host')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
    <div class="form-control">
        <label class="label"><span class="label-text">SMTP Port</span></label>
        <input type="number" wire:model.defer="credentials.port" class="input input-bordered w-full" placeholder="587" />
        @error('credentials.port')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
    <div class="form-control">
        <label class="label"><span class="label-text">Username</span></label>
        <input type="text" wire:model.defer="credentials.username" class="input input-bordered w-full" placeholder="SMTP username" />
        @error('credentials.username')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
    <div class="form-control">
        <label class="label"><span class="label-text">Password</span></label>
        <input type="password" wire:model.defer="credentials.password" class="input input-bordered w-full" placeholder="SMTP password" />
        @error('credentials.password')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
    <div class="form-control md:col-span-2">
        <label class="label"><span class="label-text">Encryption</span></label>
        <input type="text" wire:model.defer="credentials.encryption" class="input input-bordered w-full" placeholder="tls or ssl" />
        @error('credentials.encryption')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
    </div>
</div>
