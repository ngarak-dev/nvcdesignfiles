<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        <livewire:notify-user />
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
