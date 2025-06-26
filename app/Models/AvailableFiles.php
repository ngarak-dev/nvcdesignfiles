<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvailableFiles extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'file_id',
        'temp_path',
        'download_ready_at',
        'expires_at',
        'is_read',
        // 'user_id',
        // 'is_downloaded',
        // 'downloaded_at',
    ];

    protected $casts = [
        'download_ready_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_read' => 'boolean',
        // 'downloaded_at' => 'datetime',
        // 'is_downloaded' => 'boolean',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function isExpired(): bool
    {
        return now()->gt($this->expires_at);
    }

    public function isReady(): bool
    {
        return $this->download_ready_at && now()->gte($this->download_ready_at);
    }

    // public function markAsDownloaded(): void
    // {
    //     $this->update([
    //         'is_downloaded' => true,
    //         'downloaded_at' => now(),
    //     ]);
    // }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}
