<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\File;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Exception;

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
        $path = null;
        $fullPath = null;

        try {
            if (!$this->file) {
                throw new Exception('No file selected');
            }

            // Validate file
            $this->validate([
                'file' => 'required|file',
                'name' => 'required|string|max:255',
            ]);

            // Generate unique temporary filename
            $tempFilename = Str::uuid() . '_' . $this->file->getClientOriginalName();

            // Store file temporarily with detailed error handling
            try {
                $path = $this->file->storeAs('temp', $tempFilename);
                if (!$path) {
                    throw new Exception('Failed to store file in temporary location');
                }
            } catch (Exception $e) {
                Log::error('File storage error', [
                    'error' => $e->getMessage(),
                    'file' => $tempFilename,
                    'original_name' => $this->file->getClientOriginalName(),
                    'size' => $this->file->getSize()
                ]);
                throw new Exception('Failed to store file: ' . $e->getMessage());
            }

            $this->uploadProgress = 30;

            // Get the full path using Storage facade
            $fullPath = Storage::path($path);

            // Verify file exists and is readable
            if (!Storage::exists($path)) {
                throw new Exception('Temporary file not found after storage');
            }

            // Calculate file hash
            $hash = hash_file('sha256', $fullPath);
            if (!$hash) {
                throw new Exception('Failed to calculate file hash');
            }

            $this->uploadProgress = 50;

            // Check for duplicate
            $existingFile = File::where('hash', $hash)->first();
            if ($existingFile) {
                Storage::delete($path); // Clean up temp file
                throw new Exception('A file with the same properties already exists');
            }

            // Upload to Telegram
            $responseData = $this->telegramService->upload(
                $fullPath,
                $this->file->getClientOriginalName(),
                $this->name,
            );

            // Log::info('Telegram response', ['response' => $responseData]);
            $this->uploadProgress = 70;

            if (!$responseData) {
                throw new Exception('Failed to upload file to Telegram');
            }

            $this->uploadProgress = 80;

            // Create file record
            File::create([
                'name' => $this->name,
                'file_id' => $responseData['document']['file_id'],
                'file_unique_id' => $responseData['document']['file_unique_id'],
                'size' => $this->file->getSize(),
                'mime_type' => $this->file->getMimeType(),
                'hash' => $hash,
                'folder' => $this->folder ?? 'Root',
                'message_id' => $responseData['message_id'],
                'user_id' => Auth::user()->id,
                'metadata' => [
                    'caption' => $this->name,
                    'original_name' => $this->file->getClientOriginalName(),
                ],
            ]);

            $this->uploadProgress = 100;

            // Clean up temp file
            Storage::delete($path);

            $this->reset(['file', 'name']);
            session()->flash('message', 'File uploaded successfully');
            $this->modal('upload-file')->close();
        }
        catch (Exception $e) {
            Log::error('File upload error', [
                'error' => $e->getMessage(),
                'file' => $this->file ? $this->file->getClientOriginalName() : 'No file',
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up temp file if it exists
            if ($path && Storage::exists($path)) {
                Storage::delete($path);
            }

            $this->uploadError = 'Failed to upload file: ' . $e->getMessage();
            session()->flash('error', $this->uploadError);
            $this->modal('upload-file')->close();
        }
    }

    public function download($id)
    {
        try {
            $file = File::findOrFail($id);

            $telegram = new TelegramService();
            $downloadUrl = $telegram->getDownloadUrl($file->file_id);

            if (!$downloadUrl) {
                throw new Exception('Failed to generate download URL');
            }

            // Log download
            Log::info('File downloaded', [
                'file_id' => $file->id,
                'user_id' => Auth::id(),
                'downloaded_at' => now()->toIso8601String()
            ]);

            return redirect()->to($downloadUrl);
        } catch (Exception $e) {
            Log::error('File download error: ' . $e->getMessage());
            session()->flash('error', 'Failed to download file. Please try again.');
        }
    }

    public function delete($id)
    {
        $this->deletingFile = true;
        $file = File::findOrFail($id);
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
