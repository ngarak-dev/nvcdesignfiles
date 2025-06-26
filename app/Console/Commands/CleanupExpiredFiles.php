<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AvailableFiles;

class CleanupExpiredFiles extends Command
{
    protected $signature = 'files:cleanup-expired';
    protected $description = 'Clean up expired downloaded files and their records.';

    public function handle()
    {
        $expired = AvailableFiles::where('expires_at', '<', now())->get();
        $count = 0;
        foreach ($expired as $file) {
            if (file_exists($file->temp_path)) {
                unlink($file->temp_path);
            }
            $file->delete();
            $count++;
        }
        $this->info("Cleaned up {$count} expired files.");
    }
}
