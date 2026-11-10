<?php

namespace App\Domains\Marketplace\Livewire\Supplier;

use App\Domains\Marketplace\Services\SupplierPlanAccess;
use App\Domains\Platform\Models\Vendor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Team extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    private function vendor(): Vendor
    {
        abort_unless(auth()->user()?->hasRole('supplier_admin') && auth()->user()->vendor?->vendor_type === 'supplier', 403);

        return auth()->user()->vendor;
    }

    public function mount(): void
    {
        $this->vendor();
    }

    public function addMember(): void
    {
        $this->resetValidation();
        $vendor = $this->vendor();
        app(SupplierPlanAccess::class)->assertSelling($vendor);
        $this->name = trim($this->name);
        $this->email = mb_strtolower(trim($this->email));
        $data = $this->validate([
            'name' => 'required|string|max:191', 'email' => 'required|email|max:191|unique:users,email',
            'password' => ['required', 'string', 'max:255', 'confirmed', Password::defaults()],
        ]);
        DB::transaction(function () use ($vendor, $data) {
            Vendor::whereKey($vendor->id)->lockForUpdate()->firstOrFail();
            $user = User::create(['vendor_id' => $vendor->id, 'name' => $data['name'], 'email' => $data['email'],
                'password' => Hash::make($data['password']), 'is_active' => true, 'must_change_password' => true]);
            $user->assignRole('supplier_staff');
        });
        $this->reset('name', 'email', 'password', 'password_confirmation');
        session()->flash('success', 'Team member created. Share the initial password securely; they must change it at first sign-in.');
    }

    public function removeMember(int $id): void
    {
        $this->resetValidation();
        $vendor = $this->vendor();
        $user = User::where('vendor_id', $vendor->id)->findOrFail($id);
        abort_unless($user->hasRole('supplier_staff') && ! $user->hasRole('supplier_admin') && $user->id !== auth()->id(), 403);
        $user->update(['is_active' => false]);
        session()->flash('success', 'Team member access removed.');
    }

    public function render()
    {
        $vendor = $this->vendor();
        $subscription = app(\App\Domains\Platform\Services\SelfServiceSubscriptionService::class)->current($vendor);

        return view('livewire.marketplace.supplier.team', [
            'members' => User::where('vendor_id', $vendor->id)->where('is_active', true)->orderBy('name')->get(),
            'seatLimit' => $subscription?->plan?->max_users,
            'canAdd' => app(SupplierPlanAccess::class)->canSell($vendor),
        ])->layout('components.layouts.app', ['title' => 'Supplier team']);
    }
}
