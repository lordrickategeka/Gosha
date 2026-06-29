<?php

namespace App\Domains\CRM\Livewire\Appointments;

use App\Domains\Operations\Models\Appointment;
use Livewire\Component;
use Livewire\WithPagination;

class AppointmentsComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $date = '';
    public $view = 'list';

    protected $queryString = ['status' => ['except' => ''], 'date' => ['except' => '']];

    public function confirm(Appointment $appointment)
    {
        $appointment->update(['status' => 'confirmed']);
        session()->flash('success', 'Appointment confirmed.');
    }

    public function checkIn(Appointment $appointment)
    {
        $appointment->update(['status' => 'checked_in', 'checked_in_at' => now()]);
        session()->flash('success', 'Customer checked in.');
    }

    public function cancel(Appointment $appointment)
    {
        $appointment->update(['status' => 'cancelled']);
        session()->flash('success', 'Appointment cancelled.');
    }

    public function noShow(Appointment $appointment)
    {
        $appointment->update(['status' => 'no_show']);
        session()->flash('success', 'Marked as no-show.');
    }

    public function render()
    {
        $query = Appointment::with(['customer', 'vehicle'])
            ->when($this->search, fn($q) => $q->whereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%")))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->date, fn($q) => $q->whereDate('scheduled_date', $this->date))
            ->when(session('current_branch_id'), fn($q) => $q->where('branch_id', session('current_branch_id')));

        $todayCount = Appointment::today()->whereIn('status', ['scheduled', 'confirmed'])->count();
        $upcomingCount = Appointment::upcoming()->count();

        $appointments = $query->orderBy('scheduled_date')->orderBy('scheduled_time')->paginate(15);

        return view('livewire.appointments.appointments-component', compact('appointments', 'todayCount', 'upcomingCount'))
            ->layout('components.layouts.app', ['title' => 'Appointments']);
    }
}
