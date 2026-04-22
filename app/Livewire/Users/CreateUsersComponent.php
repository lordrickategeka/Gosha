<?php

namespace App\Livewire\Users;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class CreateUsersComponent extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $role = '';
    public $branches = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:20',
        'password' => 'required|min:8',
        'role' => 'required',
        'branches' => 'array',
    ];

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

        $user = User::create([
            'vendor_id' => auth()->user()->vendor_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'is_active' => true,
        ]);

        $user->assignRole($this->role);

        if (!empty($this->branches)) {
            $user->branches()->sync($this->branches);
        }

        session()->flash('success', 'Staff member created.');
        return redirect()->route('users.show', $user);
    }

    public function render()
    {
        return view('livewire.users.create-users-component')
            ->layout('components.layouts.app', ['title' => 'Add Staff']);
    }
}
