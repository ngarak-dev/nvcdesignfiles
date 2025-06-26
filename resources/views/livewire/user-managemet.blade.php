<div class="max-w-8xl mx-auto p-6">

    @include('partials.callouts')

    <div class="rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <flux:heading size="lg">User Management</flux:heading>
            </div>
            <div class="flex items-center gap-2">
                <flux:modal.trigger name="add-user">
                    <flux:button variant="primary" icon="user-plus">
                        Add User
                    </flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('name')">
                            Name
                            {{-- @if($sortField === 'name')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif --}}
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('email')">
                            Email Address
                            {{-- @if($sortField === 'email')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif --}}
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer">
                            No Files Uploaded
                        </th>

                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                    <tr class="transition-colors duration-200 bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-900">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <flux:icon.user variant="mini" class="mr-4"></flux:icon.user>
                                <flux:text variant='strong'>{{ $user->name }}</flux:text>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-amber-500 dark:text-amber-400">
                            {{ $user->email ?? 'NULL' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            0000
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-3">
                                @if($user->id !== Auth::id())
                                    <flux:modal.trigger name="delete-user" wire:click="$set('deleteUser', {{ $user }})">
                                        <flux:button variant="danger">Remove User</flux:button>
                                    </flux:modal.trigger>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No users found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>

    <!-- New User Modal -->
    <flux:modal name="add-user" class="md:min-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Register New User</flux:heading>
            </div>

            <form wire:submit.prevent="registerUser" class="space-y-4">
                <flux:input type="text" wire:model="name" label="Full Name" />
                <flux:input type="email" wire:model="email" label="Email Address" />

                <div class="flex justify-end space-x-3 mt-6">
                    <div class="flex gap-2">
                        <flux:spacer />

                        <flux:modal.close>
                            <flux:button variant="ghost">Cancel</flux:button>
                        </flux:modal.close>

                        <flux:button type="submit" variant="primary">Create user</flux:button>
                    </div>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Delete User Modal -->
    <flux:modal name="delete-user" class="md:min-w-2xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-red-600">Remeving User</flux:heading>
            </div>

            <flux:text>
                <p>Are you sure you want to remove this user from the channel ?</p>
                <span class="font-bold text-red-500">{{ $deleteUser['name'] ?? 'Unknown' }}</span>
            </flux:text>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                @if($deletingUser)
                    <flux:button variant="danger" disabled>
                        Removing...
                    </flux:button>
                @else
                    <flux:button variant="danger" wire:click="delete({{ $deletingUser['id'] ?? 0 }})">Remove</flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

</div>