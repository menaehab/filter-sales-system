<x-layouts.app title="Dashboard">
    {{-- Stats grid --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Sales" value="$48,250" trend="12.5%" :trendUp="true" color="emerald">
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Orders Today" value="142" trend="8.2%" :trendUp="true" color="emerald">
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Products" value="386" trend="3.1%" :trendUp="false" color="amber">
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card label="Customers" value="1,024" trend="5.7%" :trendUp="true" color="sky">
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    {{-- Charts / Content area --}}
    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Sales chart placeholder --}}
        <div class="lg:col-span-2">
            <x-card title="Sales Overview">
                <div class="flex h-72 items-center justify-center p-6 text-gray-400">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        <p class="mt-2 text-sm">Chart placeholder — integrate Chart.js or ApexCharts here</p>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Recent activity --}}
        <div>
            <x-card title="Recent Activity">
                <div class="divide-y divide-gray-100">
                    @foreach([
                        ['name' => 'Order #1042', 'desc' => 'Completed by John', 'time' => '2 min ago', 'color' => 'emerald'],
                        ['name' => 'Order #1041', 'desc' => 'New order placed', 'time' => '15 min ago', 'color' => 'blue'],
                        ['name' => 'Product Update', 'desc' => 'Stock adjusted: Widget Pro', 'time' => '1 hour ago', 'color' => 'amber'],
                        ['name' => 'New Customer', 'desc' => 'Sarah Johnson registered', 'time' => '2 hours ago', 'color' => 'emerald'],
                        ['name' => 'Order #1040', 'desc' => 'Refund processed', 'time' => '3 hours ago', 'color' => 'red'],
                    ] as $activity)
                        <div class="flex items-start gap-3 p-4">
                            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-{{ $activity['color'] }}-100">
                                <div class="h-2 w-2 rounded-full bg-{{ $activity['color'] }}-500"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $activity['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $activity['desc'] }}</p>
                            </div>
                            <span class="shrink-0 text-xs text-gray-400">{{ $activity['time'] }}</span>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>

    {{-- Recent orders table --}}
    <div class="mt-8">
        <x-data-table
            :headers="[
                ['key' => 'order', 'label' => 'Order', 'sortable' => true],
                ['key' => 'customer', 'label' => 'Customer', 'sortable' => true],
                ['key' => 'items', 'label' => 'Items', 'sortable' => false],
                ['key' => 'total', 'label' => 'Total', 'sortable' => true],
                ['key' => 'status', 'label' => 'Status', 'sortable' => true],
                ['key' => 'date', 'label' => 'Date', 'sortable' => true],
            ]"
        >
            <x-slot:actions>
                <x-button variant="primary" size="sm">
                    <svg class="-ms-0.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Order
                </x-button>
            </x-slot:actions>

            @foreach([
                ['id' => '#1042', 'customer' => 'John Smith', 'items' => 3, 'total' => '$245.00', 'status' => 'Completed', 'status_color' => 'green', 'date' => 'Mar 3, 2026'],
                ['id' => '#1041', 'customer' => 'Emma Wilson', 'items' => 1, 'total' => '$89.99', 'status' => 'Processing', 'status_color' => 'blue', 'date' => 'Mar 3, 2026'],
                ['id' => '#1040', 'customer' => 'Sarah Johnson', 'items' => 5, 'total' => '$512.50', 'status' => 'Refunded', 'status_color' => 'red', 'date' => 'Mar 2, 2026'],
                ['id' => '#1039', 'customer' => 'Michael Brown', 'items' => 2, 'total' => '$178.00', 'status' => 'Completed', 'status_color' => 'green', 'date' => 'Mar 2, 2026'],
                ['id' => '#1038', 'customer' => 'Lisa Anderson', 'items' => 4, 'total' => '$340.00', 'status' => 'Pending', 'status_color' => 'yellow', 'date' => 'Mar 1, 2026'],
            ] as $order)
                <tr class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-emerald-600">{{ $order['id'] }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $order['customer'] }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $order['items'] }} items</td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">{{ $order['total'] }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                        <x-badge :label="$order['status']" :color="$order['status_color']" />
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $order['date'] }}</td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
</x-layouts.app>
