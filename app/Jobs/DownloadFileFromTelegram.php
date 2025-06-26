<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Models\AvailableFiles;
use App\Events\FileDownloadReady;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\FileStatusNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\User;

class DownloadFileFromTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileId;
    protected $userId;

    public function __construct($fileId, $userId)
    {
        $this->fileId = $fileId;
        $this->userId = $userId;
    }

    public function handle(TelegramService $telegramService)
    {
        try {
            $file = File::findOrFail($this->fileId);
            $downloadUrl = $telegramService->getDownloadUrl($file->file_id);

            if (!$downloadUrl) {
                session()->flash('error', 'Failed to generate download URL');
                throw new \Exception('Failed to generate download URL');
            }

            // Create a temporary directory if it doesn't exist
            $tempDir = storage_path('app/temp/downloads');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Generate a unique filename
            $tempFilename = Str::uuid() . '_' . $file->name;
            $tempPath = $tempDir . '/' . $tempFilename;

            // Check for existing available file for this user and file
            $existing = AvailableFiles::where('file_id', $file->id)
                ->where('user_id', $this->userId)
                ->where('expires_at', '>', now())
                ->first();
            if ($existing) {
                // Notify user file is already ready
                $user = User::find($this->userId);
                if ($user) {
                    $user->notify(new FileStatusNotification(
                        'File is already ready for download',
                        $file->id,
                        $file->name,
                        'ready'
                    ));
                }
                return;
            }

            // Download the file
            $fileContent = file_get_contents($downloadUrl);
            if ($fileContent === false) {
                session()->flash('error', 'Failed to download file from Telegram');
                throw new \Exception('Failed to download file from Telegram');
            }

            // Save the file
            if (file_put_contents($tempPath, $fileContent) === false) {
                session()->flash('error', 'Failed to save downloaded file');
                throw new \Exception('Failed to save downloaded file');
            }

            // Create available file record
            $available = AvailableFiles::create([
                'file_id' => $file->id,
                'temp_path' => $tempPath,
                'download_ready_at' => now(),
                'expires_at' => now()->addHours(24),
                'user_id' => $this->userId,
            ]);

            // Schedule cleanup job
            CleanupDownloadedFile::dispatch($file->id)->delay(now()->addHours(24));

            // Notify user download ready
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new FileStatusNotification(
                    'File is ready for download',
                    $file->id,
                    $file->name,
                    'ready'
                ));
            }

            // Broadcast event for real-time updates
            broadcast(new FileDownloadReady($file->id))->toOthers();
        } catch (\Exception $e) {
            Log::error('Telegram download job failed', [
                'error' => $e->getMessage(),
                'file_id' => $this->fileId,
                'trace' => $e->getTraceAsString()
            ]);

            // Notify user download failed
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new FileStatusNotification(
                    'File download failed: ' . $e->getMessage(),
                    $this->fileId,
                    $file->name ?? 'Unknown',
                    'failed'
                ));
            }

            session()->flash('error', 'Failed to download file from Telegram');

            throw $e;
        }
    }
}
