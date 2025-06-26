<div class="max-w-8xl mx-auto p-6">
    <div class="flex justify-between items-center">
        <flux:heading size="lg">Available Files for Download</flux:heading>
        <flux:button variant="ghost" wire:click="toggleExpired">
            {{ $showExpired ? 'Hide Expired' : 'Show Expired' }}
        </flux:button>
    </div>

    @if(count($availableFiles) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Name
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Size
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                    <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Expires
                    </th>
                    <th
                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($availableFiles as $file)
                <tr class="transition-colors duration-200 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-900">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        {{ $file['name'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $file['size'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($file['status'] === 'ready')
                        <flux:badge color="green" size="sm">Ready</flux:badge>
                        @else
                        <flux:badge color="yellow" size="sm">Preparing</flux:badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-amber-500">
                        {{ $file['expires_at'] }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if($file['status'] === 'ready')
                        <flux:button variant="primary" size="sm" wire:click="download({{ $file['id'] }})">
                            Download
                        </flux:button>
                        @else
                        <flux:button variant="ghost" size="sm" disabled>
                            Preparing...
                        </flux:button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-gray-500 dark:text-gray-400">No files available for download</p>
    </div>
    @endif
</div>
