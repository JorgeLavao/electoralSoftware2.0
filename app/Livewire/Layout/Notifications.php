<?php

namespace App\Livewire\Layout;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notifications extends Component
{
    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications()->update(['read_at' => now()]);
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()?->notifications()->whereKey($notificationId)->first();

        if ($notification && ! $notification->read_at) {
            $notification->markAsRead();
        }
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.layout.notifications', [
            'notifications' => $user?->notifications()->latest()->limit(8)->get() ?? collect(),
            'unreadCount' => $user?->unreadNotifications()->count() ?? 0,
        ]);
    }
}
