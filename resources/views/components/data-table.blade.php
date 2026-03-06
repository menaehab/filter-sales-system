@props([
    'headers' => [],
    'rows' => [],
    'searchable' => true,
    'filterable' => true,
    'paginated' => true,
    'perPageOptions' => [10, 25, 50, 100],
    'emptyMessage' => 'No records found.',
    'emptyDescription' => 'Try adjusting your search or filters.',
])

{{--
    Usage example:
    <x-data-table
        :headers="[
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'role', 'label' => 'Role', 'sortable' => false],
            ['key' => 'actions', 'label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ]"
    >
        @foreach($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $user->name }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $user->email }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $user->role }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">...</td>
            </tr>
        @endforeach
    </x-data-table>
--}}

<div
    x-data="{
        search: '',
        sortKey: '',
        sortDir: 'asc',
        perPage: {{ $perPageOptions[0] ?? 10 }},
        currentPage: 1,
        toggleSort(key) {
            if (this.sortKey === key) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortKey = key;
                this.sortDir = 'asc';
            }
        }
    }"
    {{ $attributes->merge(['class' => 'w-full']) }}
>
    {{-- Toolbar --}}
    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        {{-- Search --}}
        @if($searchable)
            <div class="relative w-full sm:max-w-xs">
                <svg class="pointer-events-none absolute inset-s-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    x-model="search"
                    type="text"
                    placeholder="Search..."
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-10 pe-4 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
            </div>
        @endif

        <div class="flex items-center gap-3">
            {{-- Filters slot --}}
            @isset($filters)
                {{ $filters }}
            @endisset

            {{-- Per page --}}
            @if($paginated)
                <select
                    x-model="perPage"
                    class="rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }} per page</option>
                    @endforeach
                </select>
            @endif

            {{-- Actions slot --}}
            @isset($actions)
                {{ $actions }}
            @endisset
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach($headers as $header)
                            @php
                                $align = $header['align'] ?? 'left';
                                $alignClass = match($align) {
                                    'center' => 'text-center',
                                    'right' => 'text-end',
                                    default => 'text-start',
                                };
                            @endphp
                            <th
                                scope="col"
                                class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 {{ $alignClass }} {{ ($header['sortable'] ?? false) ? 'cursor-pointer select-none hover:text-gray-700' : '' }}"
                                @if($header['sortable'] ?? false)
                                    @click="toggleSort('{{ $header['key'] }}')"
                                @endif
                            >
                                <div class="flex items-center gap-1 {{ $align === 'right' ? 'justify-end' : ($align === 'center' ? 'justify-center' : '') }}">
                                    <span>{{ $header['label'] }}</span>
                                    @if($header['sortable'] ?? false)
                                        <span class="inline-flex flex-col">
                                            <svg
                                                class="h-3 w-3 transition-colors"
                                                :class="sortKey === '{{ $header['key'] }}' && sortDir === 'asc' ? 'text-emerald-600' : 'text-gray-300'"
                                                viewBox="0 0 20 20" fill="currentColor"
                                            >
                                                <path fill-rule="evenodd" d="M14.77 12.79a.75.75 0 0 1-1.06-.02L10 8.832 6.29 12.77a.75.75 0 1 1-1.08-1.04l4.25-4.5a.75.75 0 0 1 1.08 0l4.25 4.5a.75.75 0 0 1-.02 1.06Z" clip-rule="evenodd" />
                                            </svg>
                                            <svg
                                                class="-mt-1 h-3 w-3 transition-colors"
                                                :class="sortKey === '{{ $header['key'] }}' && sortDir === 'desc' ? 'text-emerald-600' : 'text-gray-300'"
                                                viewBox="0 0 20 20" fill="currentColor"
                                            >
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        {{-- Empty state (shown when no rows provided) --}}
        @if(empty(trim((string) $slot)))
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <h3 class="mt-3 text-sm font-semibold text-gray-900">{{ $emptyMessage }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $emptyDescription }}</p>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if($paginated)
        <div class="mt-4 flex flex-col items-center justify-between gap-4 sm:flex-row">
            <p class="text-sm text-gray-500">
                Showing <span class="font-medium text-gray-700">1</span> to <span class="font-medium text-gray-700">10</span> of <span class="font-medium text-gray-700">100</span> results
            </p>
            <nav class="flex items-center gap-1" aria-label="Pagination">
                {{-- Previous --}}
                <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <svg class="me-1 h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Prev
                </button>

                {{-- Page numbers --}}
                <button class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-sm font-medium text-white">1</button>
                <button class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">2</button>
                <button class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">3</button>
                <span class="inline-flex h-9 w-9 items-center justify-center text-sm text-gray-500">...</span>
                <button class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">10</button>

                {{-- Next --}}
                <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Next
                    <svg class="ms-1 h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </nav>
        </div>
    @endif
</div>
