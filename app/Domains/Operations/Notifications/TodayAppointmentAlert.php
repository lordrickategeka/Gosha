<?php

namespace App\Domains\Operations\Notifications;

use App\Domains\Operations\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TodayAppointmentAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Appointment Today: ' . $this->appointment->appointment_number)
            ->info()
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have an appointment scheduled for today.')
            ->line('**Customer:** ' . ($this->appointment->customer?->name ?? 'N/A'))
            ->line('**Vehicle:** ' . ($this->appointment->vehicle?->registration_number ?? 'N/A'))
            ->line('**Time:** ' . ($this->appointment->scheduled_time?->format('H:i') ?? 'N/A'))
            ->line('**Type:** ' . $this->appointment->type_display)
            ->action('View Appointment', url('/appointments/' . $this->appointment->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'               => 'today_appointment',
            'title'              => 'Appointment Today',
            'message'            => 'Appointment with ' . ($this->appointment->customer?->name ?? 'a customer') . ' at ' . ($this->appointment->scheduled_time?->format('H:i') ?? 'today'),
            'domain'             => 'crm',
            'severity'           => 'info',
            'appointment_id'     => $this->appointment->id,
            'appointment_number' => $this->appointment->appointment_number,
            'customer_name'      => $this->appointment->customer?->name,
            'vehicle'            => $this->appointment->vehicle?->registration_number,
            'scheduled_time'     => $this->appointment->scheduled_time?->format('H:i'),
            'appointment_type'   => $this->appointment->type_display,
            'branch_id'          => $this->appointment->branch_id,
            'branch_name'        => $this->appointment->branch?->name,
            'url'                => url('/appointments/' . $this->appointment->id),
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'today-appointment-alert';
    }
}
