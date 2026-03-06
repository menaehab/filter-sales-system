<x-details-page :title="$supplier->name" :subtitle="__('keywords.supplier_details_description')" :back-url="route('suppliers')" :badge="$this->statusLabel" :badge-color="$this->statusColor">
    <x-slot:actions>
        @can('manage_suppliers')
            <x-button variant="secondary" href="{{ route('suppliers', ['search' => $supplier->name]) }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                </svg>
                {{ __('keywords.edit_supplier') }}
            </x-button>
        @endcan
    </x-slot:actions>

    <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ __('keywords.supplier') }}</h3>
    </div>

    <dl class="divide-y divide-gray-100">
        <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">{{ __('keywords.name') }}</dt>
            <dd class="text-sm font-semibold text-gray-900 sm:col-span-2">{{ $supplier->name }}</dd>
        </div>

        <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">{{ __('keywords.phone') }}</dt>
            <dd class="text-sm text-gray-900 sm:col-span-2">
                {{ $supplier->phone ?: __('keywords.not_available') }}
            </dd>
        </div>

        <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">{{ __('keywords.created_at') }}</dt>
            <dd class="text-sm text-gray-900 sm:col-span-2">{{ $supplier->created_at?->format('Y-m-d h:i A') }}</dd>
        </div>
    </dl>

    <x-slot:aside>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    {{ __('keywords.quick_actions') }}
                </h3>
            </div>

            <div class="space-y-3 p-5">
                <x-button variant="secondary" href="{{ route('suppliers') }}" class="w-full">
                    {{ __('keywords.back_to_suppliers') }}
                </x-button>

                @can('manage_suppliers')
                    <x-button variant="primary" href="{{ route('suppliers', ['search' => $supplier->name]) }}"
                        class="w-full">
                        {{ __('keywords.edit_supplier') }}
                    </x-button>
                @endcan
            </div>
        </div>
    </x-slot:aside>
</x-details-page>
