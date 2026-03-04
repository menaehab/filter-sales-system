@php
    $products = [
        ['id' => 1, 'name' => 'Espresso', 'price' => 3.50, 'category' => 'Coffee', 'image' => null],
        ['id' => 2, 'name' => 'Cappuccino', 'price' => 4.50, 'category' => 'Coffee', 'image' => null],
        ['id' => 3, 'name' => 'Latte', 'price' => 4.99, 'category' => 'Coffee', 'image' => null],
        ['id' => 4, 'name' => 'Green Tea', 'price' => 3.00, 'category' => 'Tea', 'image' => null],
        ['id' => 5, 'name' => 'Croissant', 'price' => 2.99, 'category' => 'Bakery', 'image' => null],
        ['id' => 6, 'name' => 'Muffin', 'price' => 3.49, 'category' => 'Bakery', 'image' => null],
        ['id' => 7, 'name' => 'Sandwich', 'price' => 6.99, 'category' => 'Food', 'image' => null],
        ['id' => 8, 'name' => 'Orange Juice', 'price' => 3.99, 'category' => 'Beverages', 'image' => null],
        ['id' => 9, 'name' => 'Americano', 'price' => 3.75, 'category' => 'Coffee', 'image' => null],
        ['id' => 10, 'name' => 'Bagel', 'price' => 2.49, 'category' => 'Bakery', 'image' => null],
        ['id' => 11, 'name' => 'Smoothie', 'price' => 5.49, 'category' => 'Beverages', 'image' => null],
        ['id' => 12, 'name' => 'Salad', 'price' => 7.99, 'category' => 'Food', 'image' => null],
    ];

    $categories = collect($products)->pluck('category')->unique()->values()->all();
@endphp

<x-layouts.app title="app">
    <div
        x-data="{
            cart: [],
            activeCategory: 'All',
            searchQuery: '',
            taxRate: 0.10,

            addToCart(product) {
                const existing = this.cart.find(item => item.id === product.id);
                if (existing) {
                    existing.qty++;
                } else {
                    this.cart.push({ ...product, qty: 1 });
                }
            },

            removeFromCart(index) {
                this.cart.splice(index, 1);
            },

            updateQty(index, delta) {
                this.cart[index].qty += delta;
                if (this.cart[index].qty <= 0) {
                    this.cart.splice(index, 1);
                }
            },

            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            },

            get tax() {
                return this.subtotal * this.taxRate;
            },

            get total() {
                return this.subtotal + this.tax;
            },

            get cartCount() {
                return this.cart.reduce((sum, item) => sum + item.qty, 0);
            },

            clearCart() {
                this.cart = [];
            },

            formatPrice(amount) {
                return '$' + amount.toFixed(2);
            }
        }"
        class="flex h-[calc(100vh-7rem)] flex-col gap-6 lg:flex-row"
    >
        {{-- Product section --}}
        <div class="flex flex-1 flex-col min-w-0">
            {{-- Category tabs & search --}}
            <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <button
                        @click="activeCategory = 'All'"
                        :class="activeCategory === 'All' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'"
                        class="shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                    >
                        All
                    </button>
                    @foreach($categories as $category)
                        <button
                            @click="activeCategory = '{{ $category }}'"
                            :class="activeCategory === '{{ $category }}' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'"
                            class="shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                        >
                            {{ $category }}
                        </button>
                    @endforeach
                </div>

                <div class="relative w-full sm:max-w-xs">
                    <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input
                        x-model="searchQuery"
                        type="text"
                        placeholder="Search products..."
                        class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-10 pe-4 text-sm placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                </div>
            </div>

            {{-- Product grid --}}
            <div class="flex-1 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
                    @foreach($products as $product)
                        <button
                            x-show="(activeCategory === 'All' || activeCategory === '{{ $product['category'] }}') && (searchQuery === '' || '{{ strtolower($product['name']) }}'.includes(searchQuery.toLowerCase()))"
                            @click="addToCart({{ json_encode($product) }})"
                            class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all hover:border-emerald-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                        >
                            {{-- Product image placeholder --}}
                            <div class="flex h-24 items-center justify-center bg-gradient-to-br from-gray-100 to-gray-50 sm:h-28">
                                <svg class="h-10 w-10 text-gray-300 transition-colors group-hover:text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                            </div>
                            <div class="flex flex-1 flex-col p-3">
                                <span class="text-xs text-gray-400">{{ $product['category'] }}</span>
                                <span class="mt-0.5 text-sm font-medium text-gray-900 group-hover:text-emerald-600">{{ $product['name'] }}</span>
                                <span class="mt-auto pt-2 text-sm font-bold text-emerald-600">${{ number_format($product['price'], 2) }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Cart section --}}
        <div class="flex w-full flex-col rounded-xl border border-gray-200 bg-white shadow-sm lg:w-96">
            {{-- Cart header --}}
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900">Current Order</h3>
                    <span
                        x-show="cartCount > 0"
                        x-text="cartCount"
                        class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-emerald-100 px-1.5 text-xs font-medium text-emerald-700"
                    ></span>
                </div>
                <button
                    x-show="cart.length > 0"
                    @click="clearCart()"
                    class="text-xs font-medium text-red-500 hover:text-red-700"
                >
                    Clear All
                </button>
            </div>

            {{-- Cart items --}}
            <div class="flex-1 overflow-y-auto p-4">
                {{-- Empty cart state --}}
                <div x-show="cart.length === 0" class="flex h-full flex-col items-center justify-center py-8 text-center">
                    <svg class="h-16 w-16 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="0.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                    <p class="mt-3 text-sm font-medium text-gray-500">Cart is empty</p>
                    <p class="mt-1 text-xs text-gray-400">Click on products to add them</p>
                </div>

                {{-- Cart item list --}}
                <div x-show="cart.length > 0" class="space-y-3">
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100">
                                <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900" x-text="item.name"></p>
                                <p class="text-xs text-gray-500" x-text="formatPrice(item.price)"></p>
                            </div>
                            {{-- Qty controls --}}
                            <div class="flex items-center gap-1.5">
                                <button
                                    @click="updateQty(index, -1)"
                                    class="flex h-7 w-7 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-50"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                    </svg>
                                </button>
                                <span class="w-6 text-center text-sm font-medium text-gray-900" x-text="item.qty"></span>
                                <button
                                    @click="updateQty(index, 1)"
                                    class="flex h-7 w-7 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-50"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>
                            </div>
                            {{-- Line total --}}
                            <span class="w-16 text-end text-sm font-semibold text-gray-900" x-text="formatPrice(item.price * item.qty)"></span>
                            {{-- Remove --}}
                            <button @click="removeFromCart(index)" class="shrink-0 p-1 text-gray-300 hover:text-red-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Cart summary & checkout --}}
            <div class="border-t border-gray-200 bg-gray-50 p-5">
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between text-gray-500">
                        <span>Subtotal</span>
                        <span x-text="formatPrice(subtotal)"></span>
                    </div>
                    <div class="flex items-center justify-between text-gray-500">
                        <span>Tax (10%)</span>
                        <span x-text="formatPrice(tax)"></span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-900">
                        <span>Total</span>
                        <span x-text="formatPrice(total)"></span>
                    </div>
                </div>

                {{-- Payment buttons --}}
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <x-button variant="secondary" class="w-full" :disabled="false" x-bind:disabled="cart.length === 0">
                        <svg class="-ms-0.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
                        </svg>
                        Hold
                    </x-button>
                    <x-button variant="primary" class="w-full" x-bind:disabled="cart.length === 0">
                        <svg class="-ms-0.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        Pay
                    </x-button>
                </div>

                {{-- Quick payment methods --}}
                <div class="mt-3 flex gap-2">
                    <button x-bind:disabled="cart.length === 0" class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white py-2 text-xs font-medium text-gray-600 transition-colors hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        Card
                    </button>
                    <button x-bind:disabled="cart.length === 0" class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white py-2 text-xs font-medium text-gray-600 transition-colors hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                        Mobile
                    </button>
                    <button x-bind:disabled="cart.length === 0" class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white py-2 text-xs font-medium text-gray-600 transition-colors hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                        </svg>
                        QR
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
