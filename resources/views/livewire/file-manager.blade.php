<div class="max-w-8xl mx-auto p-6">

    @include('partials.callouts')

    <div class="rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <flux:heading size="lg">File Manager</flux:heading>
                @if($folder)
                <span class="text-gray-500 dark:text-gray-400">/ {{ $folder }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <flux:modal.trigger name="upload-file">
                    <flux:button variant="primary" icon="arrow-up">
                        Upload File
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal.trigger name="new-folder">
                    <flux:button variant="filled" icon="folder-plus">
                        New Folder
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <div class="mb-6">
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search"
                        placeholder="Search files ..." />
                </div>
                @if(count($selectedFiles) > 0)
                <flux:button wire:click="deleteSelected" variant="danger" icon="trash">
                    Delete Selected ({{ count($selectedFiles) }})
                </flux:button>
                @endif
            </div>
        </div>

        @if(count($folders) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach($folders as $folderName)
            <a href="?folder={{ $folder ? $folder . '/' : '' }}{{ $folderName }}"
                class="flex items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-neutral-100 dark:hover:bg-neutral-900 transition-colors duration-200">
                <svg class="w-6 h-6 text-gray-500 dark:text-gray-400 mr-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                <span class="text-gray-900 dark:text-white">{{ $folderName }}</span>
            </a>
            @endforeach
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <input type="checkbox"
                                wire:click="$set('selectedFiles', {{ empty($selectedFiles) ? 'files->pluck(\'id\')' : '[]' }})"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('name')">
                            Name
                            @if($sortField === 'name')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('mime_type')">
                            Type
                            @if($sortField === 'mime_type')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('size')">
                            Folder
                            @if($sortField === 'folder')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('size')">
                            Size
                            @if($sortField === 'size' || $sortField === 'formatted_size')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('created_at')">
                            Uploaded
                            @if($sortField === 'created_at')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('user_id')">
                            Uploaded By
                            @if($sortField === 'user_id')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($files as $file)
                    <tr
                        class="transition-colors duration-200 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-900">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" wire:click="toggleFileSelection({{ $file->id }})"
                                class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                @checked(in_array($file->id, $selectedFiles))>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <svg class="h-10 w-10 text-gray-400 dark:text-gray-500 mr-3"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $file->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $file->extension }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            @php
                            $mime = $file->mime_type;
                            [$shortMime, $mimeColor] = match(true) {
                            str_contains($mime, 'photoshop') => ['PSD', 'purple'],
                            str_contains($mime, 'xml') => ['XML', 'blue'],
                            str_contains($mime, 'illustrator') => ['AI', 'orange'],
                            str_contains($mime, 'pdf') => ['PDF', 'red'],
                            str_contains($mime, 'zip') => ['ZIP', 'indigo'],
                            str_contains($mime, 'rar') => ['RAR', 'indigo'],
                            str_contains($mime, 'mp4') => ['MP4', 'emerald'],
                            str_contains($mime, 'mp3') => ['MP3', 'sky'],
                            str_contains($mime, 'jpeg') => ['JPEG', 'cyan'],
                            str_contains($mime, 'png') => ['PNG', 'cyan'],
                            str_contains($mime, 'gif') => ['GIF', 'cyan'],
                            str_contains($mime, 'svg') => ['SVG', 'cyan'],
                            default => [$mime, 'zinc']
                            };
                            @endphp
                            <flux:badge color="{{ $mimeColor }}" size="sm">{{ $shortMime }}</flux:badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-amber-500 dark:text-amber-400">
                            {{ $file->folder ?? 'Root' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $file->formatted_size }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $file->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $file->user->name ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                @if(isset($downloadStatus[$file->id]) && $downloadStatus[$file->id] === 'preparing')
                                <flux:button variant="primary" class="mr-2" disabled>
                                    Preparing...
                                </flux:button>
                                @elseif(isset($file->metadata['temp_path']) &&
                                isset($file->metadata['download_expires_at']) &&
                                now()->lt($file->metadata['download_expires_at']))
                                <flux:button variant="primary" class="mr-2" wire:click="download({{ $file->id }})">
                                    Download
                                </flux:button>
                                @else
                                <flux:button variant="primary" class="mr-2" wire:click="download({{ $file->id }})">
                                    Download
                                </flux:button>
                                @endif

                                <flux:modal.trigger name="delete-file" wire:click="$set('deleteFile', {{ $file }})">
                                    <flux:button variant="danger">Delete</flux:button>
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No files found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $files->links() }}
        </div>
    </div>

    <!-- Upload Modal -->
    <flux:modal name="upload-file" class="md:min-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Upload File</flux:heading>
            </div>

            <form wire:submit.prevent="sendToTelegram" class="space-y-4" enctype="multipart/form-data">
                <flux:input type="file" wire:model="file" label="File" />
                <flux:text size="xs" class="text-gray-500">Allowed types: jpg, jpeg, png, gif, pdf, zip, rar, ai, psd,
                    svg, mp4, mp3. Max size: 50MB.</flux:text>

                <flux:input type="text" wire:model="name" label="File Name" />

                @if($uploadProgress > 0 && $uploadProgress < 100) <div
                    class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4">
                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $uploadProgress }}%"></div>
        </div>
        <flux:text variant="subtle">Uploading... {{ $uploadProgress }}%</flux:text>
        @endif

        <div class="flex justify-end space-x-3 mt-6">
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Upload</flux:button>
            </div>
        </div>
        </form>
</div>
</flux:modal>

<!-- New Folder Modal -->
<flux:modal name="new-folder" class="md:min-w-2xl">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Create New Folder</flux:heading>
        </div>

        <form wire:submit.prevent="createFolder" class="space-y-4">

            <flux:input type="text" wire:model="newFolderName" label="Folder Name" />

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">Create Folder</flux:button>
            </div>
        </form>
    </div>
</flux:modal>

<!-- Delete File Modal -->
<flux:modal name="delete-file" class="md:min-w-2xl">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Delete File</flux:heading>
        </div>

        <flux:text>
            <p>Are you sure you want to delete this file ?</p>
            <span class="font-bold text-red-500">{{ $deleteFile['name'] ?? 'Unknown' }}</span>
        </flux:text>

        <div class="flex gap-2">
            <flux:spacer />

            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>

            @if($deletingFile)
            <flux:button variant="danger" disabled>
                Deleting...
            </flux:button>
            @else
            <flux:button variant="danger" wire:click="delete({{ $deleteFile['id'] ?? 0 }})">Delete</flux:button>
            @endif
        </div>
    </div>
</flux:modal>

</div>