<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('roles.index') }}" class="text-base-content/60 hover:text-base-content text-sm flex items-center gap-1 mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Roles
            </a>
            <h1 class="text-2xl font-bold">{{ ucwords(str_replace('-', ' ', $role->name)) }}</h1>
            <p class="text-base-content/60">Assign permissions to this role</p>
        </div>
        <button wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" wire:loading.remove>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span wire:loading.remove>Save Permissions</span>
            <span wire:loading>Saving...</span>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($permissionGroups as $group => $perms)
            @php
                $available = \Spatie\Permission\Models\Permission::whereIn('name', $perms)->pluck('name')->toArray();
                $allChecked = count($available) > 0 && count(array_intersect($available, $selectedPermissions)) === count($available);
                $someChecked = ! $allChecked && count(array_intersect($available, $selectedPermissions)) > 0;
            @endphp

            @if(count($available) > 0)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-sm">{{ $group }}</h3>
                            <button
                                type="button"
                                wire:click="toggleGroup('{{ $group }}')"
                                class="btn btn-xs {{ $allChecked ? 'btn-primary' : 'btn-ghost' }}"
                            >
                                {{ $allChecked ? 'Deselect All' : 'Select All' }}
                            </button>
                        </div>
                        <div class="grid grid-cols-1 gap-1">
                            @foreach($available as $perm)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-base-200 rounded px-2 py-1">
                                    <input
                                        type="checkbox"
                                        class="checkbox checkbox-sm checkbox-primary"
                                        value="{{ $perm }}"
                                        wire:model="selectedPermissions"
                                    />
                                    <span class="text-sm">{{ ucwords(str_replace('_', ' ', $perm)) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="mt-6 flex justify-end">
        <button wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove>Save Permissions</span>
            <span wire:loading>Saving...</span>
        </button>
    </div>
</div>
