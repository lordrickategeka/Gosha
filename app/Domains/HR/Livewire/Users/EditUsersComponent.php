<?php

namespace App\Domains\HR\Livewire\Users;

use App\Domains\Organization\Models\Branch;
use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class EditUsersComponent extends Component
{
    public User $user;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $role = '';
    public $branches = [];
    public $is_active = true;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required',
            'branches' => 'array',
        ];
    }

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->roles->first()?->name ?? '';
        $this->branches = $user->branches->pluck('id')->toArray();
        $this->is_active = $user->is_active;
    }

    public function getRolesProperty()
    {
        return Role::whereNotIn('name', ['super-admin', 'platform-support'])->get();
    }

    public function getBranchesListProperty()
    {
        return Branch::where('vendor_id', auth()->user()->vendor_id)->get();
    }

    public function save()
    {
        $this->validate();

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
        ]);

        $this->user->syncRoles([$this->role]);
        $this->user->branches()->sync($this->branches);

        session()->flash('success', 'User updated successfully.');
        return redirect()->route('users.show', $this->user);
    }

    public function render()
    {
        return view('livewire.users.edit-users-component')
            ->layout('components.layouts.app', ['title' => 'Edit User']);
    }
}
