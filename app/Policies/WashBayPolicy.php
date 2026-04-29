<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WashBay;

class WashBayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view wash bays');
    }

    public function view(User $user, WashBay $washBay): bool
    {
        return $user->hasPermissionTo('view wash bays');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create wash bays');
    }

    public function update(User $user, WashBay $washBay): bool
    {
        return $user->hasPermissionTo('edit wash bays');
    }

    public function delete(User $user, WashBay $washBay): bool
    {
        return $user->hasPermissionTo('delete wash bays') && ! $washBay->isOccupied();
    }
}
