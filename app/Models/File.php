<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'file_id',
        'file_unique_id',
        'mime_type',
        'extension',
        'size',
        'hash',
        'metadata',
        'user_id',
        'message_id',
        'folder',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $appends = [
        'formatted_size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2) . ' ' . $units[$index];
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->isImage()) {
            // return route('files.thumbnail', $this->id);
        }
        return null;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function scopeInFolder($query, $folder)
    {
        return $query->where('folder', $folder);
    }
}
