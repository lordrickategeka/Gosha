<?php

namespace App\Domains\CRM\Livewire\Calendar;

use App\Domains\Operations\Models\Appointment;
use App\Domains\Operations\Models\WorkOrder;
use Livewire\Component;

class CalendarComponent extends Component
{
    public string $currentMonth;
    public int $currentYear;
    public string $view = 'month'; // month | week

    public function mount(): void
    {
        $this->currentMonth = now()->format('Y-m');
        $this->currentYear  = now()->year;
    }

    public function previousMonth(): void
    {
        $this->currentMonth = \Carbon\Carbon::createFromFormat('Y-m', $this->currentMonth)
            ->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->currentMonth = \Carbon\Carbon::createFromFormat('Y-m', $this->currentMonth)
            ->addMonth()->format('Y-m');
    }

    public function getEventsProperty(): array
    {
        $branchId = session('current_branch_id');
        $start    = \Carbon\Carbon::createFromFormat('Y-m', $this->currentMonth)->startOfMonth();
        $end      = $start->copy()->endOfMonth();

        $events = [];

        // Appointments
        if (auth()->user()->can('view appointments')) {
            $appointments = Appointment::with(['customer', 'vehicle'])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereBetween('scheduled_date', [$start, $end])
                ->get();

            foreach ($appointments as $appt) {
                $events[] = [
                    'id'    => 'appt-' . $appt->id,
                    'date'  => $appt->scheduled_date->format('Y-m-d'),
                    'time'  => $appt->scheduled_time ? substr($appt->scheduled_time, 0, 5) : null,
                    'title' => ($appt->customer->name ?? 'Unknown') . ' — ' . ($appt->vehicle->plate_number ?? ''),
                    'color' => 'bg-info',
                    'url'   => route('appointments.index'),
                    'type'  => 'appointment',
                ];
            }
        }

        // Work Orders (by estimated_completion date)
        if (auth()->user()->canAny(['view work orders', 'view assigned work orders'])) {
            $workOrders = WorkOrder::with(['customer', 'vehicle'])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereNotNull('estimated_completion')
                ->whereBetween('estimated_completion', [$start, $end])
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->get();

            foreach ($workOrders as $wo) {
                $events[] = [
                    'id'    => 'wo-' . $wo->id,
                    'date'  => \Carbon\Carbon::parse($wo->estimated_completion)->format('Y-m-d'),
                    'time'  => \Carbon\Carbon::parse($wo->estimated_completion)->format('H:i'),
                    'title' => ($wo->customer->name ?? 'Unknown') . ' — ' . ($wo->vehicle->plate_number ?? ''),
                    'color' => 'bg-warning',
                    'url'   => route('work-orders.show', $wo->id),
                    'type'  => 'work_order',
                ];
            }
        }

        // Group by date
        $grouped = [];
        foreach ($events as $event) {
            $grouped[$event['date']][] = $event;
        }

        return $grouped;
    }

    public function render()
    {
        $start = \Carbon\Carbon::createFromFormat('Y-m', $this->currentMonth)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        return view('livewire.calendar.calendar-component', [
            'monthLabel'  => $start->format('F Y'),
            'startOfMonth' => $start,
            'endOfMonth'   => $end,
            'events'       => $this->events,
        ])->layout('components.layouts.app', ['title' => 'Calendar']);
    }
}
