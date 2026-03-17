<div x-on:confirmed-delete-expense.window="$wire.delete()">
    <x-page-header :title="__('keywords.view_expenses')" :description="__('keywords.expenses_management')">
        @can('manage_expenses')
            <x-slot:actions>
                <x-button variant="primary" @click="$dispatch('open-modal-create-expense')">
                    {{ __('keywords.add_expense') }}
                </x-button>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-search-toolbar>
        <x-input type="date" name="dateFrom" wire:model.live="dateFrom"
            class="w-full sm:w-auto" placeholder="{{ __('keywords.from_date') }}" />
        <x-input type="date" name="dateTo" wire:model.live="dateTo"
            class="w-full sm:w-auto" placeholder="{{ __('keywords.to_date') }}" />
    </x-search-toolbar>

    {{-- expenses table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'amount', 'label' => __('keywords.amount')],
        ['key' => 'description', 'label' => __('keywords.description')],
        ['key' => 'user', 'label' => __('keywords.user')],
        ['key' => 'created_at', 'label' => __('keywords.date')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->expenses as $expense)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ number_format($expense->amount, 2) }} {{ __('keywords.currency') }}</span>
                </td>
                <td class="px-4 py-3 max-w-md">
                    <span class="text-sm text-gray-900 truncate block">{{ $expense->description ?: '-' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-900">{{ $expense->user?->name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-900">{{ $expense->created_at?->format('Y-m-d') }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions editAction="openEdit({{ $expense->id }})" :canEdit="auth()->user()->can('manage_expenses')" :canDelete="auth()->user()->can('manage_expenses')"
                        deleteAction="setDelete({{ $expense->id }})" />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_expenses_found')" :colspan="5" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->expenses" />

    {{-- Total Expenses Section --}}
    <div class="mt-4 rounded-lg border border-gray-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">{{ __('keywords.total_expenses') }}</span>
            <span class="text-lg font-bold text-gray-900">{{ number_format($this->totalExpenses, 2) }} {{ __('keywords.currency') }}</span>
        </div>
    </div>

    @can('manage_expenses')
        {{-- Create expense Modal --}}
        <x-modal name="create-expense" title="{{ __('keywords.create_expense') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input type="number" name="form.amount" label="{{ __('keywords.amount') }}"
                        placeholder="{{ __('keywords.enter_amount') }}" wire:model.blur="form.amount" step="0.01" min="0.01" required />

                    <x-textarea name="form.description" label="{{ __('keywords.description') }}"
                        placeholder="{{ __('keywords.enter_description') }}" wire:model.blur="form.description" rows="3" />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-create-expense')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="create">{{ __('keywords.add') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Edit expense Modal --}}
        <x-modal name="edit-expense" title="{{ __('keywords.edit_expense') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input type="number" name="form.amount" label="{{ __('keywords.amount') }}"
                        placeholder="{{ __('keywords.enter_amount') }}" wire:model.blur="form.amount" step="0.01" min="0.01" required />

                    <x-textarea name="form.description" label="{{ __('keywords.description') }}"
                        placeholder="{{ __('keywords.enter_description') }}" wire:model.blur="form.description" rows="3" />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-edit-expense')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="updateExpense">{{ __('keywords.update') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Delete Confirmation Modal --}}
        <x-confirm-modal name="delete-expense" title="{{ __('keywords.delete_expense') }}"
            message="{{ __('keywords.delete_expense_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan

</div>
