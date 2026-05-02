<x-details-page :title="$product->name" :subtitle="__('keywords.product_details_description')" :back-url="route('products')" :back-label="__('keywords.back_to_products')">

    <x-slot:stats>
        <x-stat-card :label="__('keywords.total_purchased')" :value="$this->totalPurchased" iconClass="fas fa-truck-loading" color="sky" />
        <x-stat-card :label="__('keywords.total_sold')" :value="$this->totalSold" iconClass="fas fa-shopping-cart" color="emerald" />
        <x-stat-card :label="__('keywords.total_damaged')" :value="$this->totalDamaged" iconClass="fas fa-exclamation-triangle" color="rose" />
        <x-stat-card :label="__('keywords.current_stock')" :value="$product->quantity" iconClass="fas fa-boxes" color="indigo" />
    </x-slot:stats>

    {{-- Product Info Section --}}
    <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
            {{ __('keywords.product_info') }}
        </h3>
    </div>

    <dl class="divide-y">
        <div class="mb-8  divide-gray-100">
            <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('keywords.name') }}</dt>
                <dd class="text-sm font-semibold text-gray-900 sm:col-span-2">{{ $product->name }}</dd>
            </div>
            <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('keywords.category') }}</dt>
                <dd class="text-sm text-gray-900 sm:col-span-2">
                    {{ $product->category?->name ?? __('keywords.not_available') }}</dd>
            </div>
            <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('keywords.cost_price') }}</dt>
                <dd class="text-sm text-gray-900 sm:col-span-2">{{ number_format($product->cost_price, 2) }}
                    {{ __('keywords.currency') }}</dd>
            </div>
            <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('keywords.sell_price') }}</dt>
                <dd class="text-sm text-gray-900 sm:col-span-2">{{ number_format($product->sell_price, 2) }}
                    {{ __('keywords.currency') }}</dd>
            </div>
            <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('keywords.min_quantity') }}</dt>
                <dd class="text-sm text-gray-900 sm:col-span-2">{{ $product->min_quantity }}</dd>
            </div>
            @if ($product->description)
                <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">{{ __('keywords.description') }}</dt>
                    <dd class="text-sm text-gray-900 sm:col-span-2">{{ $product->description }}</dd>
                </div>
            @endif
            <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('keywords.created_at') }}</dt>
                <dd class="text-sm text-gray-900 sm:col-span-2">{{ $product->created_at?->format('Y/m/d h:i A') }}</dd>
            </div>
        </div>

        {{-- Movement History Card --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    {{ __('keywords.movement_history') }}
                </h3>
            </div>
            <div class="max-h-96 overflow-y-auto">
                @forelse($this->movements as $movement)
                    <div
                        class="flex items-center justify-between border-b border-gray-50 px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                <x-badge :label="$this->getMovementTypeLabel($movement->movable_type)" :color="$this->getMovementTypeColor($movement->movable_type)" />
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $movement->created_at->format('Y/m/d H:i') }}</p>
                        </div>
                        <span
                            class="text-sm font-medium {{ $movement->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-6 text-center text-sm text-gray-500">
                        {{ __('keywords.no_movements_found') }}
                    </div>
                @endforelse
            </div>
            @if ($this->movements->hasPages())
                <div class="border-t border-gray-100 px-5 py-3">
                    {{ $this->movements->links() }}
                </div>
            @endif
        </div>
    </dl>

    <x-slot:aside>
        {{-- Profit & Loss Summary Card --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    {{ __('keywords.profit_loss_summary') }}
                </h3>
            </div>
            <dl class="divide-y divide-gray-100">
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.total_profit') }}</dt>
                    <dd class="text-sm font-medium text-emerald-600">{{ number_format($this->totalProfit, 2) }}
                        {{ __('keywords.currency') }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.total_loss') }}</dt>
                    <dd class="text-sm font-medium text-rose-600">{{ number_format($this->totalLoss, 2) }}
                        {{ __('keywords.currency') }}</dd>
                </div>
                <div
                    class="flex justify-between px-5 py-3 {{ $this->netProfit >= 0 ? 'bg-emerald-50' : 'bg-rose-50' }}">
                    <dt class="text-sm font-medium {{ $this->netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ __('keywords.net_profit') }}</dt>
                    <dd class="text-sm font-bold {{ $this->netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ number_format($this->netProfit, 2) }} {{ __('keywords.currency') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Stock Summary Card --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    {{ __('keywords.stock_summary') }}
                </h3>
            </div>
            <dl class="divide-y divide-gray-100">
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.total_purchased') }}</dt>
                    <dd class="text-sm font-medium text-emerald-600">+{{ $this->totalPurchased }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.total_sold') }}</dt>
                    <dd class="text-sm font-medium text-rose-600">-{{ $this->totalSold }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.total_damaged') }}</dt>
                    <dd class="text-sm font-medium text-rose-600">-{{ $this->totalDamaged }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.sale_returns') }}</dt>
                    <dd class="text-sm font-medium text-emerald-600">+{{ $this->totalSaleReturns }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.purchase_returns') }}</dt>
                    <dd class="text-sm font-medium text-rose-600">-{{ $this->totalPurchaseReturns }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3 bg-gray-50">
                    <dt class="text-sm font-medium text-gray-700">{{ __('keywords.calculated_stock') }}</dt>
                    <dd class="text-sm font-bold text-gray-900">{{ $this->calculatedStock }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3 bg-emerald-50">
                    <dt class="text-sm font-medium text-emerald-700">{{ __('keywords.current_stock') }}</dt>
                    <dd class="text-sm font-bold text-emerald-700">{{ $product->quantity }}</dd>
                </div>
            </dl>
        </div>

    </x-slot:aside>
</x-details-page>
