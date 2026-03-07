<div x-on:confirmed-delete-user.window="$wire.delete()">
    <x-page-header :title="__('keywords.users')" :description="__('keywords.users_management')">
        <x-slot:actions>
            <x-button variant="primary" @click="$dispatch('open-modal-create-user')">
                <i class="fas fa-plus text-xs"></i>
                {{ __('keywords.add_user') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-search-toolbar />

    {{-- Users table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'name', 'label' => __('keywords.name')],
        ['key' => 'email', 'label' => __('keywords.email')],
        ['key' => 'phone', 'label' => __('keywords.phone')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->users as $user)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs font-medium text-emerald-700">
                            {{ $user->initials() }}
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                    </div>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500">{{ $user->email ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500">{{ $user->phone ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions editAction="openEdit({{ $user->id }})"
                        deleteAction="setDelete({{ $user->id }})" />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_users_found')" :colspan="4" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->users" />

    {{-- Create User Modal --}}
    <x-modal name="create-user" title="{{ __('keywords.create_user') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                <x-input name="form.name" label="{{ __('keywords.name') }}"
                    placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />
                <x-input name="form.email" label="{{ __('keywords.email') }}" type="email"
                    placeholder="{{ __('keywords.enter_your_email') }}" wire:model.blur="form.email" />
                <x-input name="form.phone" label="{{ __('keywords.phone') }}"
                    placeholder="{{ __('keywords.enter_your_phone') }}" wire:model.blur="form.phone" />
                <x-input name="form.password" label="{{ __('keywords.password') }}" type="password"
                    placeholder="{{ __('keywords.enter_password') }}" wire:model.blur="form.password" required />
                <x-input name="form.password_confirmation" label="{{ __('keywords.confirm_password') }}"
                    type="password" placeholder="{{ __('keywords.confirm_password') }}"
                    wire:model.blur="form.password_confirmation" required />

                <x-checkbox-group label="{{ __('keywords.permissions') }}" name="form.permissions" :options="$this->permissionOptions"
                    :selected="$form['permissions']" />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary"
                @click="$dispatch('close-modal-create-user')">{{ __('keywords.cancel') }}</x-button>
            <x-button variant="primary" wire:click="create">{{ __('keywords.add') }}</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Edit User Modal --}}
    <x-modal name="edit-user" title="{{ __('keywords.edit_user') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                <x-input name="form.name" label="{{ __('keywords.name') }}"
                    placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />
                <x-input name="form.email" label="{{ __('keywords.email') }}" type="email"
                    placeholder="{{ __('keywords.enter_your_email') }}" wire:model.blur="form.email" />
                <x-input name="form.phone" label="{{ __('keywords.phone') }}"
                    placeholder="{{ __('keywords.enter_your_phone') }}" wire:model.blur="form.phone" />
                <x-input name="form.password" label="{{ __('keywords.password') }}" type="password"
                    placeholder="{{ __('keywords.enter_password') }}" wire:model.blur="form.password" />
                <x-input name="form.password_confirmation" label="{{ __('keywords.confirm_password') }}"
                    type="password" placeholder="{{ __('keywords.confirm_password') }}"
                    wire:model.blur="form.password_confirmation" />

                <x-checkbox-group label="{{ __('keywords.permissions') }}" name="form.permissions" :options="$this->permissionOptions"
                    :selected="$form['permissions']" />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary"
                @click="$dispatch('close-modal-edit-user')">{{ __('keywords.cancel') }}</x-button>
            <x-button variant="primary" wire:click="updateUser">{{ __('keywords.update') }}</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-modal name="delete-user" title="{{ __('keywords.delete_user') }}"
        message="{{ __('keywords.delete_user_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
        variant="danger" />
</div>
