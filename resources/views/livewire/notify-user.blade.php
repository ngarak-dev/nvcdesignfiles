<div id="notify-user" wire:poll.2000ms>
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-2xl flex flex-col gap-2 items-center">
        @foreach($notifications as $notification)
        <flux:callout
            :variant="$notification['status'] === 'ready' ? 'success' : ($notification['status'] === 'failed' ? 'danger' : ($notification['status'] === 'expired' ? 'warning' : 'info'))"
            icon="information-circle" class="w-full" inline>
            <div class="flex items-center justify-between w-full">
                <div>
                    <flux:callout.heading class="font-semibold">
                        {{ $notification['file_name'] }}
                    </flux:callout.heading>
                    <flux:text size="sm" class="block">
                        {{ $notification['message'] }}
                    </flux:text>
                    <flux:text size="xs" class="text-gray-400 block mt-1">
                        {{ $notification['created_at'] }}
                    </flux:text>
                </div>
                <flux:button icon="x-mark" variant="ghost" size="sm"
                    wire:click="markAsRead('{{ $notification['id'] }}')" />
            </div>
        </flux:callout>
        @endforeach
    </div>
</div>
