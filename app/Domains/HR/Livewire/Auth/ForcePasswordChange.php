<?php

namespace App\Domains\HR\Livewire\Auth;

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class ForcePasswordChange extends Component
{
    public $password = '';
    public $password_confirmation = '';

    public function changePassword()
    {
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        session()->flash('success', 'Password changed successfully.');

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.force-password-change');
    }
}
