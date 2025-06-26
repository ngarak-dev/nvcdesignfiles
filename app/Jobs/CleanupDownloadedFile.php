<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\AvailableFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\FileDownloadExpired;
use App\Notifications\FileStatusNotification;
use App\Models\User;

class CleanupDownloadedFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileId;

    public function __construct($fileId)
    {
        $this->fileId = $fileId;
    }

    public function handle()
    {
        try {
            $availableFile = AvailableFiles::where('file_id', $this->fileId)
                ->where('expires_at', '<=', now())
                ->first();

            if ($availableFile) {
                // Delete the temporary file
                if (file_exists($availableFile->temp_path)) {
                    unlink($availableFile->temp_path);
                }

                // Notify user download expired
                $user = User::find($availableFile->user_id);
                if ($user) {
                    $user->notify(new FileStatusNotification(
                        'File download link expired',
                        $availableFile->file_id,
                        $availableFile->file->name ?? 'Unknown',
                        'expired'
                    ));
                }

                // Delete the available file record
                $availableFile->delete();

                // Broadcast event for real-time updates
                broadcast(new FileDownloadExpired($this->fileId))->toOthers();
            }
        } catch (\Exception $e) {
            Log::error('File cleanup job failed', [
                'error' => $e->getMessage(),
                'file_id' => $this->fileId,
                'trace' => $e->getTraceAsString()
            ]);

            // session()->flash('error', 'Failed to cleanup downloaded file');

            throw $e;
        }
    }
}
