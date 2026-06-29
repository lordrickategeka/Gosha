<?php

namespace App\Domains\Notifications\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class NotificationCenter extends Component
{
    use WithPagination;

    public string $tab = 'all';

    protected $queryString = ['tab'];

    public function updatingTab(): void
    {
        $this->resetPage();
    }

    public function markAsRead(string $id): void
    {
        auth()->user()->notifications()->find($id)?->markAsRead();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function deleteNotification(string $id): void
    {
        auth()->user()->notifications()->find($id)?->delete();
    }

    public function deleteAll(): void
    {
        if ($this->tab === 'unread') {
            auth()->user()->unreadNotifications()->delete();
        } else {
            auth()->user()->notifications()->delete();
        }
    }

    public function render()
    {
        $user  = auth()->user();
        $query = $this->tab === 'unread'
            ? $user->unreadNotifications()
            : $user->notifications();

        $notifications = $query->latest()->paginate(15);
        $unreadCount   = $user->unreadNotifications()->count();

        return view('livewire.notifications.notification-center', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ])->layout('components.layouts.app', ['title' => 'Notifications']);
    }
}
