<div id="callouts">
    @if (session()->has('status'))
        <flux:callout icon="information-circle" class="mb-2" variant="secondary" inline x-data="{ visible: true }" x-show="visible">
            <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">
                {{ session('status') }}
            </flux:callout.heading>
            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
            </x-slot>
        </flux:callout>
    @endif

    @if (session()->has('success'))
        <flux:callout icon="check-circle" class="mb-2" variant="success" inline x-data="{ visible: true }" x-show="visible">
            <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">
                {{ session('success') }}
            </flux:callout.heading>
            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
            </x-slot>
        </flux:callout>
    @endif

    @if (session()->has('error'))
        <flux:callout icon="x-circle" class="mb-2" variant="danger" inline x-data="{ visible: true }" x-show="visible">
            <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">
                {{ session('error') }}
            </flux:callout.heading>
            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
            </x-slot>
        </flux:callout>
    @endif

    @if (session()->has('message'))
        <flux:callout icon="check-circle" class="mb-2" variant="success" inline x-data="{ visible: true }" x-show="visible">
            <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">
                {{ session('message') }}
            </flux:callout.heading>
            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
            </x-slot>
        </flux:callout>
    @endif
</div>