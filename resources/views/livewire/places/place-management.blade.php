<div x-on:confirmed-delete-place.window="$wire.delete()">
    <x-page-header :title="__('keywords.places')" :description="__('keywords.places_management')">
        <x-slot:actions>
            <x-button variant="primary" @click="$dispatch('open-modal-create-place')">
                <i class="fas fa-plus text-xs"></i>
                {{ __('keywords.add_place') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-search-toolbar />

    {{-- Places table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'name', 'label' => __('keywords.name')],
        ['key' => 'users', 'label' => __('keywords.users')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->places as $place)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $place->name }}</span>
                </td>
                <td class="px-4 py-3">
                    @if ($place->users->isEmpty())
                        <span class="text-sm text-gray-500">—</span>
                    @else
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($place->users->take(2) as $user)
                                <span
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                    {{ $user->name }}
                                </span>
                            @endforeach

                            @if ($place->users->count() > 2)
                                <span
                                    class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                    +{{ $place->users->count() - 2 }}
                                </span>
                            @endif
                        </div>
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions editAction="openEdit({{ $place->id }})"
                        deleteAction="setDelete({{ $place->id }})" />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_places_found')" :colspan="3" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->places" />

    {{-- Create Place Modal --}}
    <x-modal name="create-place" title="{{ __('keywords.create_place') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                <x-input name="form.name" label="{{ __('keywords.name') }}"
                    placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />

                <x-select name="form.user_ids" label="{{ __('keywords.users') }}" wire:model="form.user_ids"
                    :options="$this->userOptions" :selected="$form['user_ids']" multiple />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary"
                @click="$dispatch('close-modal-create-place')">{{ __('keywords.cancel') }}</x-button>
            <x-button variant="primary" wire:click="create">{{ __('keywords.add') }}</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Edit Place Modal --}}
    <x-modal name="edit-place" title="{{ __('keywords.edit_place') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                <x-input name="form.name" label="{{ __('keywords.name') }}"
                    placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />

                <x-select name="form.user_ids" label="{{ __('keywords.users') }}" wire:model="form.user_ids"
                    :options="$this->userOptions" :selected="$form['user_ids']" multiple />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary"
                @click="$dispatch('close-modal-edit-place')">{{ __('keywords.cancel') }}</x-button>
            <x-button variant="primary" wire:click="updatePlace">{{ __('keywords.update') }}</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-modal name="delete-place" title="{{ __('keywords.delete_place') }}"
        message="{{ __('keywords.delete_place_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
        variant="danger" />
</div>
