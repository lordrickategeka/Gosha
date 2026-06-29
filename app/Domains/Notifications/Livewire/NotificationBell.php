<?php

namespace App\Domains\Notifications\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;
    public array $notifications = [];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = auth()->user();

        $this->unreadCount = $user->unreadNotifications()->count();

        $this->notifications = $user->notifications()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($n) => [
                'id'       => $n->id,
                'type'     => $n->data['type']     ?? 'info',
                'title'    => $n->data['title']    ?? 'Notification',
                'message'  => $n->data['message']  ?? '',
                'severity' => $n->data['severity'] ?? 'info',
                'url'      => $n->data['url']      ?? null,
                'domain'   => $n->data['domain']   ?? null,
                'read'     => ! is_null($n->read_at),
                'time'     => $n->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function markAsRead(string $id): void
    {
        auth()->user()->notifications()->find($id)?->markAsRead();
        $this->loadNotifications();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notifications.notification-bell');
    }
}
