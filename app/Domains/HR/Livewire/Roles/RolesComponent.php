<?php

namespace App\Domains\HR\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class RolesComponent extends Component
{
    public function render()
    {
        $platformRoles = ['super-admin', 'platform-support'];

        $roles = Role::withCount('permissions', 'users')
            ->when(
                ! auth()->user()->hasRole('super-admin'),
                fn ($q) => $q->whereNotIn('name', $platformRoles)
            )
            ->orderBy('name')
            ->get();

        return view('livewire.roles.roles-component', compact('roles'))
            ->layout('components.layouts.app', ['title' => 'Roles & Permissions']);
    }
}
