<?php

namespace App\Domains\HR\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UsersComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $role = '';
    public $status = '';

    protected $queryString = ['search' => ['except' => ''], 'role' => ['except' => '']];

    public function toggleStatus(User $user)
    {
        $this->authorize('edit_users');
        $user->update(['is_active' => !$user->is_active]);
        session()->flash('success', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    public function render()
    {
        $users = User::with('roles', 'branches')
            ->where('vendor_id', auth()->user()->vendor_id)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->role, fn($q) => $q->role($this->role))
            ->when($this->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15);

        $roles = \Spatie\Permission\Models\Role::whereNotIn('name', ['super-admin', 'platform-support'])->get();

        return view('livewire.users.users-component', compact('users', 'roles'))
            ->layout('components.layouts.app', ['title' => 'Staff']);
    }
}
