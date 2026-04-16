<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public bool $showDropdown = false;

    public function toggleDropdown(): void
    {
        $this->showDropdown = ! $this->showDropdown;
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->showDropdown = false;
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function getUnreadCountProperty(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function getNotificationsProperty()
    {
        return auth()->user()->notifications()->latest()->take(10)->get();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
