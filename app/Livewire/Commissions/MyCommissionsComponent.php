<?php

namespace App\Livewire\Commissions;

use App\Models\Commission;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class MyCommissionsComponent extends Component
{
    use WithPagination;

    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = ['status' => ['except' => '']];

    public function render()
    {
        $userId = auth()->id();

        $commissions = Commission::with(['commissionRule', 'user'])
            ->where('user_id', $userId)
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(15);

        $totals = [
            'pending' => Commission::where('user_id', $userId)
                ->where('status', 'pending')
                ->sum('commission_amount'),
            'approved' => Commission::where('user_id', $userId)
                ->where('status', 'approved')
                ->sum('commission_amount'),
            'total_paid' => Commission::where('user_id', $userId)
                ->where('status', 'paid')
                ->sum('commission_amount'),
            'this_month' => Commission::where('user_id', $userId)
                ->whereMonth('created_at', now()->month)
                ->sum('commission_amount'),
            'pending_count' => Commission::where('user_id', $userId)
                ->where('status', 'pending')
                ->count(),
        ];

        return view('livewire.commissions.my-commissions-component', compact('commissions', 'totals'))
            ->layout('components.layouts.app', ['title' => 'My Commissions']);
    }
}
