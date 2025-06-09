<div class="max-w-8xl mx-auto p-6">
    @if (session()->has('message'))
        <flux:callout variant="success" class="mb-4" icon="check-circle" heading="{{ session('message') }}" />
    @endif

    @if (session()->has('error'))
        <flux:callout variant="danger" class="mb-4" icon="x-circle" heading="{{ session('error') }}" />
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
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
                    class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200">
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
                <thead class="bg-gray-50 dark:bg-gray-700">
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
                            wire:click="sortBy('size')">
                            Size
                            @if($sortField === 'size')
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
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($files as $file)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
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
                                <button wire:click="download({{ $file->id }})"
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 focus:outline-none focus:underline transition-colors duration-200">
                                    Download
                                </button>
                                <button wire:click="delete({{ $file->id }})"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 focus:outline-none focus:underline transition-colors duration-200">
                                    Delete
                                </button>
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
                <flux:input type="file" wire:model="file" label="File"/>

                <flux:input type="text" wire:model="name" label="File Name" />

                <div class="flex justify-end space-x-3 mt-6">

                    @if($uploadProgress > 0 && $uploadProgress < 100)
                        <flux:text variant="subtle">Uploading... {{ $uploadProgress }}%</flux:text>
                    @endif

                    @if($uploadError)
                        <flux:callout variant="danger" icon="x-circle" heading="{{ $uploadError }}" />
                    @endif

                    @error('file')
                        <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
                    @enderror

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

</div>