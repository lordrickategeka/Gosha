<?php

namespace App\Domains\HR\Livewire\Users;

use App\Models\User;
use App\Domains\Operations\Models\WorkOrder;
use App\Domains\Operations\Models\WashOrder;
use Livewire\Component;

class ShowUsersComponent extends Component
{
    public User $user;

    public function mount(User $user)
    {
        $this->user = $user->load(['roles', 'branches', 'commissions' => fn($q) => $q->latest()->take(5)]);
    }

    public function getStatsProperty()
    {
        return [
            'work_orders' => WorkOrder::where('assigned_technician_id', $this->user->id)->count(),
            'wash_orders' => WashOrder::where('assigned_attendant_id', $this->user->id)->count(),
            'completed_today' => WorkOrder::where('assigned_technician_id', $this->user->id)->where('status', 'delivered')->whereDate('delivered_at', today())->count(),
            'commissions_total' => $this->user->commissions()->sum('commission_amount'),
        ];
    }

    public function render()
    {
        return view('livewire.users.show-users-component')
            ->layout('components.layouts.app', ['title' => $this->user->name]);
    }
}
