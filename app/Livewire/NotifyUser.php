<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AvailableFiles;
use Illuminate\Support\Facades\Auth;

class NotifyUser extends Component
{
    public $notifications = [];
    public $hasUnreadNotifications = false;

    public $showNotifications = true;

    protected $listeners = [
        'fileUploadStarted' => 'handleFileUploadStarted',
        'fileUploadCompleted' => 'handleFileUploadCompleted',
        'fileDownloadReady' => 'handleFileDownloadReady',
        'fileDownloadExpired' => 'handleFileDownloadExpired',
    ];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = AvailableFiles::with('file')
            // ->where('user_id', Auth::id())
            // ->where('is_read', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_name' => $file->file->name,
                    'status' => $file->isReady() ? 'ready' : 'preparing',
                    'expires_at' => $file->expires_at->diffForHumans(),
                ];
            })
            ->toArray();

        $this->hasUnreadNotifications = count($this->notifications) > 0;
    }

    public function handleFileUploadStarted($fileId)
    {
        $this->notifications[] = [
            'id' => $fileId,
            'file_name' => 'File upload started',
            'status' => 'preparing',
            'expires_at' => null,
        ];
        $this->hasUnreadNotifications = true;
    }

    public function handleFileUploadCompleted($fileId)
    {
        $this->loadNotifications();
    }

    public function handleFileDownloadReady($fileId)
    {
        $this->loadNotifications();
    }

    public function handleFileDownloadExpired($fileId)
    {
        $this->loadNotifications();
    }

    public function markAsRead($notificationId)
    {
        AvailableFiles::where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->update(['is_read' => true]);

        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notify-user');
    }
}
