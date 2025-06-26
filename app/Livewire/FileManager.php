<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\File;
use App\Services\TelegramService;
use App\Jobs\UploadFileToTelegram;
use App\Jobs\DownloadFileFromTelegram;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Config;
use App\Notifications\FileStatusNotification;

class FileManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $file;
    public $name = '';
    public $folder = '';
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedFiles = [];
    public $newFolderName = '';
    public $uploadProgress = 0;
    public $uploadError = null;
    public $deleteFile = null;
    public $deletingFile = false;
    public $downloadStatus = [];

    protected $listeners = ['refreshFiles' => '$refresh'];

    protected $telegramService;

    public function boot(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function mount()
    {
        $this->folder = request()->query('folder', '');
    }

    // public function updatedFile()
    // {
    //     $this->uploadError = null;
    //     $this->uploadProgress = 0;
    // }

    public function sendToTelegram()
    {
        try {
            if (!$this->file) {
                throw new Exception('No file selected');
            }

            $maxSize = Config::get('notify.telegram_max_file_size', 52428800); // 50MB default
            if ($this->file->getSize() > $maxSize) {
                throw new Exception('File exceeds Telegram upload limit of 50MB. Please upload a smaller file.');
            }

            // Validate file
            $this->validate([
                'file' => 'required|file|max:' . intval($maxSize / 1024) . '|mimes:jpg,jpeg,png,gif,pdf,zip,rar,ai,psd,svg,mp4,mp3',
                'name' => 'required|string|max:255',
            ]);

            // Generate unique temporary filename
            $tempFilename = Str::uuid() . '_' . $this->file->getClientOriginalName();

            // Store file temporarily
            $path = $this->file->storeAs('temp', $tempFilename);
            if (!$path) {
                throw new Exception('Failed to store file in temporary location');
            }

            $this->uploadProgress = 30;

            // Get the full path
            $fullPath = Storage::path($path);

            // Calculate file hash
            $hash = hash_file('sha256', $fullPath);
            if (!$hash) {
                throw new Exception('Failed to calculate file hash');
            }

            $this->uploadProgress = 50;

            // Check for duplicate
            $existingFile = File::where('hash', $hash)->first();
            if ($existingFile) {
                Storage::delete($path);
                throw new Exception('A file with the same properties already exists');
            }

            // Dispatch upload job
            UploadFileToTelegram::dispatch(
                $fullPath,
                $this->file->getClientOriginalName(),
                $this->name,
                Auth::id(),
                $this->folder
            );

            // Notify user upload started
            Auth::user()->notify(new FileStatusNotification(
                'File upload started',
                null,
                $this->file->getClientOriginalName(),
                'uploading'
            ));

            $this->uploadProgress = 100;

            $this->reset(['file', 'name']);
            session()->flash('message', 'File upload started. You will be notified when it\'s complete.');
            $this->modal('upload-file')->close();
        } catch (Exception $e) {
            Log::error('File upload error', [
                'error' => $e->getMessage(),
                'file' => $this->file ? $this->file->getClientOriginalName() : 'No file',
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }

            // Notify user upload failed
            Auth::user()->notify(new FileStatusNotification(
                'File upload failed: ' . $e->getMessage(),
                null,
                $this->file ? $this->file->getClientOriginalName() : 'No file',
                'failed'
            ));

            $this->uploadError = 'Failed to upload file: ' . $e->getMessage();
            session()->flash('error', $this->uploadError);
            $this->modal('upload-file')->close();
        }
    }

    public function download($id)
    {
        try {
            $file = File::findOrFail($id);
            if ($file->user_id !== Auth::id()) {
                abort(403, 'Unauthorized');
            }

            // Check if file is already downloaded and not expired
            if (
                isset($file->metadata['temp_path']) &&
                isset($file->metadata['download_expires_at']) &&
                now()->lt($file->metadata['download_expires_at'])
            ) {
                return response()->download($file->metadata['temp_path'], $file->name);
            }

            // Start download process
            DownloadFileFromTelegram::dispatch($file->id, Auth::id());

            $this->downloadStatus[$id] = 'preparing';
            session()->flash('message', 'File download started. You will be notified when it is ready.');
        } catch (Exception $e) {
            Log::error('File download error: ' . $e->getMessage());
            session()->flash('error', 'Failed to start file download. Please try again.');
        }
    }

    public function delete($id)
    {
        $this->deletingFile = true;
        $file = File::findOrFail($id);
        if ($file->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        $this->telegramService->deleteFile($file->message_id, $file->file_id);
        Log::info('File deleted successfully : ' . $file->name);
        $file->delete();

        $this->modal('delete-file')->close();
        session()->flash('message', 'File deleted successfully.');
        $this->deletingFile = false;
    }

    public function createFolder()
    {
        $this->validate([
            'newFolderName' => 'required|string|max:255',
        ]);

        // Create a folder marker file
        $folderPath = 'folders/' . $this->folder . '/' . $this->newFolderName;
        Storage::put($folderPath . '/.folder', '');

        $this->reset('newFolderName');
        session()->flash('message', 'Folder created successfully.');
        $this->modal('new-folder')->close();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleFileSelection($id)
    {
        if (in_array($id, $this->selectedFiles)) {
            $this->selectedFiles = array_diff($this->selectedFiles, [$id]);
        } else {
            $this->selectedFiles[] = $id;
        }
    }

    public function deleteSelected()
    {
        $files = File::whereIn('id', $this->selectedFiles)->get();
        foreach ($files as $file) {
            $this->telegramService->deleteFile($file->message_id, $file->file_id);
            Log::info('File deleted successfully : ' . $file->name);
            $file->delete();
        }

        $this->selectedFiles = [];
        $this->reset('selectedFiles');
        session()->flash('message', 'Selected files deleted successfully.');
    }

    public function render()
    {
        $query = File::query()
            ->when($this->folder, fn($q) => $q->where('folder', $this->folder))
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            // ->when(Auth::check(), fn($q) => $q->where('user_id', Auth::id()))
            ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.file-manager', [
            'files' => $query->paginate(10),
            'folders' => $this->getFolders(),
            'downloadStatus' => $this->downloadStatus,
        ]);
    }

    protected function getFolders()
    {
        $folders = Storage::directories('folders/' . $this->folder);
        return collect($folders)->map(function ($folder) {
            return basename($folder);
        });
    }
}
