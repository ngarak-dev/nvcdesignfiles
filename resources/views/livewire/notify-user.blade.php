<div id="notify-user">
    <flux:navbar class="me-4 justify-end mr-6">
        <flux:dropdown rounded="none" class="rounded-0">
            <flux:navbar.item icon:trailing="chevron-down" icon="bell">
                Notifications
                @if($hasUnreadNotifications)
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                @endif
            </flux:navbar.item>
            <flux:navmenu class="w-100 max-height[32rem] overflow-y-auto">
                <div class="p-4">
                    <div class="flex justify-between items-center mb-2">
                        <flux:heading size="sm">Notifications</flux:heading>
                        <flux:button variant="ghost" size="sm" wire:click="markAllAsRead">Mark all as read</flux:button>
                    </div>
                    @if(count($notifications) > 0)
                    <div class="space-y-4">
                        @foreach($notifications as $notification)
                        <div class="flex items-start space-x-3">
                            <div class="flex-1">
                                <flux:text size="sm" class="text-gray-900 font-medium dark:text-white mb-1">
                                    {{ $notification['file_name'] }}
                                </flux:text>
                                <flux:text size="xs" class="text-gray-500 dark:text-gray-400 mb-1">
                                    @if($notification['status'] === 'ready')
                                    Ready for download
                                    @else
                                    Preparing for download
                                    @endif
                                </flux:text>
                                @if($notification['expires_at'])
                                <flux:text size="xs" class="text-amber-500">
                                    Expires {{ $notification['expires_at'] }}
                                </flux:text>
                                @endif
                            </div>
                            <flux:button variant="ghost" size="sm" wire:click="markAsRead({{ $notification['id'] }})"
                                icon="x-mark" />
                        </div>
                        @endforeach
                    </div>
                    @else
                    <flux:text size="sm" class="text-gray-500 dark:text-gray-400">No notifications</flux:text>
                    @endif
                </div>
            </flux:navmenu>
        </flux:dropdown>

    </flux:navbar>

    <div class="space-y-4">
        @foreach($notifications as $notification)
        <flux:callout icon="information-circle" class="mb-2 mr-6 ml-6" variant="secondary" inline
            x-data="{ visible: true }" x-show="visible">
            <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">
                {{ $notification['file_name'] }}
            </flux:callout.heading>
            <flux:text size="xs" class="text-gray-500 dark:text-gray-400 mb-1">
                @if($notification['status'] === 'ready')
                Is ready for download.
                @else
                Is preparing for download.
                @endif
                @if($notification['expires_at'])
                <span class="text-amber-500">
                    Download link will expire in {{ $notification['expires_at'] }}.
                </span>
                @endif
            </flux:text>

            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" wire:click="markAsRead({{ $notification['id'] }})" />

            </x-slot>
        </flux:callout>
        @endforeach
    </div>
</div>
