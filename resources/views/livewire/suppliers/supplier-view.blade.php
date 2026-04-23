<div x-on:confirmed-delete-supplier-view-payment.window="$wire.deletePayment()">
    <x-page-header :title="$supplier->name" :description="__('keywords.supplier_details_description')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('suppliers') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_suppliers') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.supplier') }}</h3>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.name') }}</span>
                    <span class="font-medium text-gray-900">{{ $supplier->name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.phone') }}</span>
                    <span class="font-medium text-gray-900">
                        {{ $supplier->phone_numbers !== [] ? implode(' - ', $supplier->phone_numbers) : __('keywords.not_specified') }}
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.created_at') }}</span>
                    <span class="font-medium text-gray-900">{{ $supplier->created_at?->format('Y/m/d H:i') }}</span>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.return_info') }}</h3>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.total_purchases') }}</span>
                    <span class="font-bold text-gray-900">{{ number_format($supplier->total_purchases, 2) }}
                        {{ __('keywords.currency') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.total_payments') }}</span>
                    <span class="font-medium text-emerald-600">{{ number_format($supplier->total_payments, 2) }}
                        {{ __('keywords.currency') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.total_returns_without_cash') }}</span>
                    <span
                        class="font-medium text-amber-600">{{ number_format($supplier->total_returns_without_cash, 2) }}
                        {{ __('keywords.currency') }}</span>
                </div>
                <div class="flex justify-between text-sm border-t pt-3">
                    <span class="text-gray-700 font-medium">{{ __('keywords.balance') }}</span>
                    <span
                        class="font-bold {{ $supplier->balance > 0 ? 'text-red-600' : ($supplier->balance < 0 ? 'text-emerald-600' : 'text-gray-500') }}">
                        {{ number_format($supplier->balance, 2) }} {{ __('keywords.currency') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="xl:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="flex border-b bg-white">
                    <button wire:click="setActiveTab('purchases')"
                        class="px-6 py-4 text-sm font-medium transition-colors {{ $activeTab === 'purchases' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ __('keywords.purchases') }} ({{ $supplier->purchases()->count() }})
                    </button>
                    <button wire:click="setActiveTab('payments')"
                        class="px-6 py-4 text-sm font-medium transition-colors {{ $activeTab === 'payments' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ __('keywords.payments') }} ({{ $supplier->payments()->count() }})
                    </button>
                    <button wire:click="setActiveTab('returns')"
                        class="px-6 py-4 text-sm font-medium transition-colors {{ $activeTab === 'returns' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ __('keywords.purchase_returns') }}
                        ({{ $supplier->purchases()->with('returns')->get()->flatMap(fn($p) => $p->returns()->where('cash_refund', false)->pluck('id'))->count() }})
                    </button>
                </div>

                @if ($activeTab === 'purchases')
                    <div class="overflow-x-auto">
                        @if ($purchases->isEmpty())
                            <div class="px-4 py-8 text-center text-sm text-gray-500">
                                {{ __('keywords.no_purchases_found') }}</div>
                        @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.number') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.total_price') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.items_count') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.created_at') }}</th>
                                        <th
                                            class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($purchases as $purchase)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ $purchase->number }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700 font-medium">
                                                {{ number_format($purchase->total_price, 2) }}
                                                {{ __('keywords.currency') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $purchase->items->count() }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $purchase->created_at->format('Y/m/d H:i') }}</td>
                                            <td class="px-4 py-3 text-end text-sm">
                                                <div class="flex items-center justify-end gap-3">
                                                    <a href="{{ route('purchases.show', $purchase) }}"
                                                        class="text-blue-600 hover:text-blue-900">
                                                        {{ __('keywords.view') }}
                                                    </a>
                                                    @canany(['manage_purchases', 'edit_purchases'])
                                                        <a href="{{ route('purchases.edit', $purchase) }}"
                                                            class="text-emerald-600 hover:text-emerald-700">
                                                            {{ __('keywords.edit') }}
                                                        </a>
                                                    @endcanany
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="border-t border-gray-200 px-5 py-4">{{ $purchases->links() }}</div>
                        @endif
                    </div>
                @endif

                @if ($activeTab === 'payments')
                    <div class="overflow-x-auto">
                        @if ($payments->isEmpty())
                            <div class="px-4 py-8 text-center text-sm text-gray-500">
                                {{ __('keywords.no_payments_found') }}</div>
                        @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            #</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.amount') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.payment_method') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.note') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.purchase') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.date') }}</th>
                                        <th
                                            class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($payments as $i => $payment)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $payments->firstItem() + $i - 1 }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-emerald-600">
                                                {{ number_format($payment->amount, 2) }} {{ __('keywords.currency') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $payment->payment_method === 'supplier_credit' ? __('keywords.applied_supplier_credit') : __('keywords.' . $payment->payment_method) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $payment->note ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $payment->allocations->pluck('purchase.number')->filter()->join(', ') ?: '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $payment->created_at->format('Y/m/d H:i') }}</td>
                                            <td class="px-4 py-3 text-end text-sm">
                                                <div class="flex items-center justify-end gap-3">
                                                    <a href="{{ route('supplier-payments.print', $payment) }}"
                                                        class="text-blue-600 hover:text-blue-900">
                                                        {{ __('keywords.view') }}
                                                    </a>
                                                    @can('manage_supplier_payment_allocations')
                                                        <button type="button"
                                                            wire:click="openEditPayment({{ $payment->id }})"
                                                            class="text-emerald-600 hover:text-emerald-700">
                                                            {{ __('keywords.edit') }}
                                                        </button>
                                                        <button type="button"
                                                            wire:click="setDeletePayment({{ $payment->id }})"
                                                            class="text-red-600 hover:text-red-700">
                                                            {{ __('keywords.delete') }}
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="border-t border-gray-200 px-5 py-4">{{ $payments->links() }}</div>
                        @endif
                    </div>
                @endif

                @if ($activeTab === 'returns')
                    <div class="overflow-x-auto">
                        @if ($returns->isEmpty())
                            <div class="px-4 py-8 text-center text-sm text-gray-500">
                                {{ __('keywords.no_purchase_returns_found') }}</div>
                        @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.number') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.purchase_number') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.total_return_price') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.reason') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.user') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.created_at') }}</th>
                                        <th
                                            class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($returns as $return)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ $return->number }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $return->purchase->number }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-amber-600">
                                                {{ number_format($return->total_price, 2) }}
                                                {{ __('keywords.currency') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $return->reason ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $return->user->name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $return->created_at->format('Y/m/d H:i') }}</td>
                                            <td class="px-4 py-3 text-end text-sm">
                                                <div class="flex items-center justify-end gap-3">
                                                    <a href="{{ route('purchase-returns.show', $return) }}"
                                                        class="text-blue-600 hover:text-blue-900">
                                                        {{ __('keywords.view') }}
                                                    </a>
                                                    @canany(['manage_purchase_returns', 'edit_purchase_returns'])
                                                        <a href="{{ route('purchase-returns.edit', $return) }}"
                                                            class="text-emerald-600 hover:text-emerald-700">
                                                            {{ __('keywords.edit') }}
                                                        </a>
                                                    @endcanany
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="border-t border-gray-200 px-5 py-4">{{ $returns->links() }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @can('manage_supplier_payment_allocations')
        <x-modal name="edit-supplier-view-payment" title="{{ __('keywords.edit_supplier_payment') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input type="number" name="form.amount" label="{{ __('keywords.amount') }}"
                        placeholder="{{ __('keywords.enter_amount') }}" wire:model.blur="form.amount" step="0.01"
                        min="0.01" required />

                    <x-select name="form.payment_method" label="{{ __('keywords.payment_method') }}"
                        wire:model.blur="form.payment_method" :options="$this->paymentMethodOptions" :placeholder="__('keywords.select_payment_method')" required />

                    <x-textarea name="form.note" label="{{ __('keywords.note') }}"
                        placeholder="{{ __('keywords.enter_note') }}" wire:model.blur="form.note" rows="3" />

                    @if ($this->canManageCreatedAt)
                        <x-input type="datetime-local" name="form.created_at" label="{{ __('keywords.created_at') }}"
                            wire:model.blur="form.created_at" />
                    @endif
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-edit-supplier-view-payment')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="updatePayment">{{ __('keywords.update') }}</x-button>
            </x-slot:footer>
        </x-modal>

        <x-confirm-modal name="delete-supplier-view-payment" title="{{ __('keywords.delete_supplier_payment') }}"
            message="{{ __('keywords.delete_supplier_payment_confirmation') }}"
            confirmText="{{ __('keywords.delete') }}" variant="danger" />
    @endcan
</div>
