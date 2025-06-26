<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class FileStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;
    public $file_id;
    public $file_name;
    public $status;

    public function __construct($message, $file_id, $file_name, $status)
    {
        $this->message = $message;
        $this->file_id = $file_id;
        $this->file_name = $file_name;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'file_id' => $this->file_id,
            'file_name' => $this->file_name,
            'status' => $this->status,
        ];
    }

    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public static function upsert($user, $file_id, $file_name, $status, $message)
    {
        $existing = $user->notifications()
            ->where('type', self::class)
            ->where('data->file_id', $file_id)
            ->whereNull('read_at')
            ->first();
        if ($existing) {
            $existing->update([
                'data' => [
                    'message' => $message,
                    'file_id' => $file_id,
                    'file_name' => $file_name,
                    'status' => $status,
                ]
            ]);
        } else {
            $user->notify(new self($message, $file_id, $file_name, $status));
        }
    }
}
