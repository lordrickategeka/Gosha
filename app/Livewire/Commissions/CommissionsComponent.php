<?php

namespace App\Livewire\Commissions;

use App\Models\Commission;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class CommissionsComponent extends Component
{
     use WithPagination;

    public $user = '';
    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = ['user' => ['except' => ''], 'status' => ['except' => '']];

    public function markPaid(Commission $commission)
    {
        $this->authorize('pay commissions');
        $commission->update(['status' => 'paid', 'paid_at' => now()]);
        session()->flash('success', 'Commission marked as paid.');
    }

    public function getTechniciansProperty()
    {
        return User::role(['technician', 'wash-attendant'])
            ->where('vendor_id', auth()->user()->vendor_id)
            ->get();
    }

    public function render()
    {
        $commissions = Commission::with(['user', 'workOrder', 'washOrder'])
            ->when($this->user, fn($q) => $q->where('user_id', $this->user))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);

        $totals = [
            'pending' => Commission::where('status', 'pending')->sum('amount'),
            'paid_month' => Commission::where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount'),
        ];

        return view('livewire.commissions.commissions-component', compact('commissions', 'totals'))
            ->layout('components.layouts.app', ['title' => 'Commissions']);
    }
}
