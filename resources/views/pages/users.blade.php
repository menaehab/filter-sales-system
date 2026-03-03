<x-layouts.app title="User Management">
    {{-- Page header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">User Management</h2>
            <p class="mt-1 text-sm text-gray-500">Manage your team members and their account permissions.</p>
        </div>
        <x-button variant="primary" @click="$dispatch('open-modal-create-user')">
            <svg class="-ms-0.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
            Add User
        </x-button>
    </div>

    {{-- Users table --}}
    <x-data-table
        :headers="[
            ['key' => 'user', 'label' => 'User', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'role', 'label' => 'Role', 'sortable' => true],
            ['key' => 'status', 'label' => 'Status', 'sortable' => true],
            ['key' => 'joined', 'label' => 'Joined', 'sortable' => true],
            ['key' => 'actions', 'label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ]"
    >
        <x-slot:filters>
            <select class="rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
                <option value="cashier">Cashier</option>
            </select>
            <select class="rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </x-slot:filters>

        @php
            $users = [
                ['name' => 'John Smith', 'email' => 'john@example.com', 'role' => 'Admin', 'role_color' => 'emerald', 'status' => 'Active', 'status_color' => 'green', 'joined' => 'Jan 15, 2026', 'initials' => 'JS'],
                ['name' => 'Emma Wilson', 'email' => 'emma@example.com', 'role' => 'Manager', 'role_color' => 'purple', 'status' => 'Active', 'status_color' => 'green', 'joined' => 'Feb 3, 2026', 'initials' => 'EW'],
                ['name' => 'Michael Brown', 'email' => 'michael@example.com', 'role' => 'Cashier', 'role_color' => 'blue', 'status' => 'Active', 'status_color' => 'green', 'joined' => 'Feb 10, 2026', 'initials' => 'MB'],
                ['name' => 'Sarah Johnson', 'email' => 'sarah@example.com', 'role' => 'Cashier', 'role_color' => 'blue', 'status' => 'Inactive', 'status_color' => 'red', 'joined' => 'Feb 20, 2026', 'initials' => 'SJ'],
                ['name' => 'David Lee', 'email' => 'david@example.com', 'role' => 'Manager', 'role_color' => 'purple', 'status' => 'Active', 'status_color' => 'green', 'joined' => 'Mar 1, 2026', 'initials' => 'DL'],
            ];
        @endphp

        @foreach($users as $user)
            <tr class="hover:bg-gray-50">
                {{-- User info --}}
                <td class="whitespace-nowrap px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-sm font-medium text-emerald-700">
                            {{ $user['initials'] }}
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $user['name'] }}</span>
                    </div>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $user['email'] }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-sm">
                    <x-badge :label="$user['role']" :color="$user['role_color']" />
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm">
                    <x-badge :label="$user['status']" :color="$user['status_color']" />
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $user['joined'] }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <div class="flex items-center justify-end gap-2">
                        <button class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-emerald-600" title="Edit">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                        <button class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600" title="Delete" @click="$dispatch('open-modal-delete-user')">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>

    {{-- Create User Modal --}}
    <x-modal name="create-user" title="Create New User" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-input name="first_name" label="First Name" placeholder="John" required />
                    <x-input name="last_name" label="Last Name" placeholder="Smith" required />
                </div>
                <x-input name="email" label="Email" type="email" placeholder="john@example.com" required />
                <x-input name="password" label="Password" type="password" placeholder="••••••••" required />
                <x-select name="role" label="Role" :options="['admin' => 'Admin', 'manager' => 'Manager', 'cashier' => 'Cashier']" placeholder="Select role" required />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary" @click="$dispatch('close-modal-create-user')">Cancel</x-button>
            <x-button variant="primary">Create User</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-modal
        name="delete-user"
        title="Delete User"
        message="Are you sure you want to delete this user? This action cannot be undone and all associated data will be permanently removed."
        confirmText="Delete"
        variant="danger"
    />
</x-layouts.app>
