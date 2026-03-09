<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\JobCard;
use App\Models\Invoice;
use App\Models\Customer;

class DashboardComponent extends Component
{
    public $monthlyRevenue;
    public $activeJobs;
    public $completedJobs;
    public $customerRating;

    public function mount()
    {
        $this->monthlyRevenue = Invoice::whereMonth('created_at', now()->month)->sum('total_amount');
        $this->activeJobs = JobCard::where('status', 'active')->count();
        $this->completedJobs = JobCard::where('status', 'completed')->count();
        $this->customerRating = 4.8; // Placeholder for actual calculation
    }

    public function render()
    {
        return view('livewire.dashboard-component');
    }
}
