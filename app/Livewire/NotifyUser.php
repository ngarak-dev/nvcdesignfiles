<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotifyUser extends Component
{
    public $notifications = [];
    public $hasUnreadNotifications = false;
    public $showNotifications = true;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        logger('NotifyUser polling at ' . now());
        
        $this->notifications = Auth::user()->unreadNotifications()
            ->where('type', 'App\\Notifications\\FileStatusNotification')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'file_name' => $notification->data['file_name'],
                    'status' => $notification->data['status'],
                    'message' => $notification->data['message'],
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            })
            ->toArray();

        $this->hasUnreadNotifications = count($this->notifications) > 0;
    }

    public function markAsRead($notificationId)
    {
        Auth::user()->unreadNotifications()->find($notificationId)?->markAsRead();
        $this->loadNotifications();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notify-user');
    }
}
