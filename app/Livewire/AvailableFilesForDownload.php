<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AvailableFiles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AvailableFilesForDownload extends Component
{
    public $availableFiles = [];
    public $showExpired = false;

    protected $listeners = [
        'fileDownloadReady' => 'loadAvailableFiles',
        'fileDownloadExpired' => 'loadAvailableFiles',
    ];

    public function mount()
    {
        $this->loadAvailableFiles();
    }

    public function loadAvailableFiles()
    {
        $query = AvailableFiles::with('file');
        // ->where('user_id', Auth::id())
        // ->where('is_downloaded', false);

        if (!$this->showExpired) {
            $query->where('expires_at', '>', now());
        }

        $this->availableFiles = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'file_id' => $file->file_id,
                    'name' => $file->file->name,
                    'size' => $file->file->formatted_size,
                    'status' => $file->isReady() ? 'ready' : 'preparing',
                    'expires_at' => $file->expires_at->diffForHumans(),
                    'temp_path' => $file->temp_path,
                ];
            })
            ->toArray();
    }

    public function download($fileId)
    {
        $file = AvailableFiles::with('file')
            ->where('id', $fileId)
            // ->where('user_id', Auth::id())
            // ->where('is_downloaded', false)
            ->first();

        if (!$file || !$file->isReady() || $file->isExpired() || $file->user_id !== Auth::id()) {
            session()->flash('error', 'File is not available for download.');
            return;
        }

        if (!file_exists($file->temp_path)) {
            session()->flash('error', 'File not found. Please try downloading again.');
            return;
        }

        // $file->markAsDownloaded();
        $this->loadAvailableFiles();

        return response()->download($file->temp_path, $file->file->name);
    }

    public function toggleExpired()
    {
        $this->showExpired = !$this->showExpired;
        $this->loadAvailableFiles();
    }

    public function render()
    {
        return view('livewire.available-files-for-download');
    }
}
