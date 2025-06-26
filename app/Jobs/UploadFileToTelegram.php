<?php

namespace App\Jobs;

use App\Models\File;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Masmerise\Toaster\Toaster;
use App\Notifications\FileStatusNotification;
use App\Models\User;

class UploadFileToTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $fileName;
    protected $caption;
    protected $userId;
    protected $folder;

    public function __construct($filePath, $fileName, $caption, $userId, $folder = null)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->caption = $caption;
        $this->userId = $userId;
        $this->folder = $folder;
    }

    public function handle(TelegramService $telegramService)
    {
        $user = User::find($this->userId);
        try {
            // Notify user upload to Telegram started
            if ($user) {
                FileStatusNotification::upsert(
                    $user,
                    null,
                    $this->fileName,
                    'telegram_uploading',
                    'Uploading to Telegram...'
                );
            }

            $responseData = $telegramService->upload(
                $this->filePath,
                $this->fileName,
                $this->caption
            );

            if (!$responseData) {
                throw new \Exception('Failed to upload file to Telegram');
            }

            // Create file record
            File::create([
                'name' => $this->caption,
                'file_id' => $responseData['document']['file_id'],
                'file_unique_id' => $responseData['document']['file_unique_id'],
                'size' => filesize($this->filePath),
                'mime_type' => mime_content_type($this->filePath),
                'hash' => hash_file('sha256', $this->filePath),
                'folder' => $this->folder ?? 'Root',
                'message_id' => $responseData['message_id'],
                'user_id' => $this->userId,
                'metadata' => [
                    'caption' => $this->caption,
                    'original_name' => $this->fileName,
                ],
            ]);

            // Clean up the temporary file
            Storage::delete($this->filePath);

            // Notify user upload complete
            if ($user) {
                FileStatusNotification::upsert(
                    $user,
                    null,
                    $this->fileName,
                    'ready',
                    'File uploaded successfully.'
                );
            }
        } catch (\Exception $e) {
            // Notify user upload failed
            if ($user) {
                FileStatusNotification::upsert(
                    $user,
                    null,
                    $this->fileName,
                    'failed',
                    'File upload to Telegram failed: ' . $e->getMessage()
                );
            }

            Log::error('Telegram upload job failed', [
                'error' => $e->getMessage(),
                'file' => $this->fileName,
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up the temporary file in case of error
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }

            throw $e;
        }
    }
}
