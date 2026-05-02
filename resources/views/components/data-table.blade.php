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
            ['key' => 'name',    'label' => 'Name',    'sortable' => true],
            ['key' => 'email',   'label' => 'Email',   'sortable' => true],
            ['key' => 'role',    'label' => 'Role',    'sortable' => false],
            ['key' => 'actions', 'label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ]"
    >
        @foreach ($users as $user)
            <tr wire:key="user-{{ $user->id }}" class="group transition-colors duration-150 hover:bg-slate-50/80">
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900" data-label="Name">{{ $user->name }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500" data-label="Email">{{ $user->email }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500" data-label="Role">{{ $user->role }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm" data-label="Actions">...</td>
            </tr>
        @endforeach
    </x-data-table>
--}}

<div x-data="{
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
}" {{ $attributes->merge(['class' => 'w-full']) }}>
    {{-- Toolbar --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        {{-- Search --}}
        @if ($searchable)
            <div class="relative w-full sm:max-w-xs" x-data="{ focused: false }">
                <svg class="pointer-events-none absolute inset-s-3 top-1/2 h-4 w-4 -translate-y-1/2 transition-colors duration-200"
                    :class="focused ? 'text-emerald-500' : 'text-gray-400'" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input x-model="search" @focus="focused = true" @blur="focused = false" type="search"
                    placeholder="Search..."
                    class="block w-full rounded-xl border border-gray-300 bg-white py-2.5 ps-10 pe-4 text-sm
                           text-gray-900 placeholder-gray-400 transition-all duration-200
                           hover:border-gray-400
                           focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25">
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            {{-- Filters slot --}}
            @isset($filters)
                {{ $filters }}
            @endisset

            {{-- Per page --}}
            @if ($paginated)
                <select x-model="perPage"
                    class="rounded-xl border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-700
                           transition-all duration-200 hover:border-gray-400
                           focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25">
                    @foreach ($perPageOptions as $option)
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

    {{-- Table wrapper --}}
    <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

        {{-- Livewire loading overlay --}}
        <div wire:loading.flex
            class="pointer-events-none absolute inset-0 z-20 hidden items-center justify-center
                   bg-white/70 backdrop-blur-[2px] transition-opacity duration-200">
            <div class="flex items-center gap-2.5 rounded-xl bg-white px-4 py-2.5 shadow-lg ring-1 ring-gray-200">
                <svg class="h-4 w-4 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <span class="text-sm font-medium text-gray-600">{{ __('keywords.loading') ?? 'Loading…' }}</span>
            </div>
        </div>

        <div class="overflow-x-auto table-card-mobile">
            <table class="min-w-full divide-y divide-gray-100">
                <thead
                    class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm
                              shadow-[0_1px_0_0_var(--color-gray-200)]">
                    <tr>
                        @foreach ($headers as $header)
                            @php
                                $align = $header['align'] ?? 'left';
                                $alignClass = match ($align) {
                                    'center' => 'text-center',
                                    'right' => 'text-end',
                                    default => 'text-start',
                                };
                            @endphp
                            <th scope="col"
                                class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500
                                       {{ $alignClass }}
                                       {{ $header['sortable'] ?? false ? 'cursor-pointer select-none hover:text-gray-700 hover:bg-gray-100 transition-colors duration-150' : '' }}"
                                @if ($header['sortable'] ?? false) @click="toggleSort('{{ $header['key'] }}')"
                                    role="button"
                                    aria-label="Sort by {{ $header['label'] }}" @endif>
                                <div
                                    class="flex items-center gap-1.5
                                            {{ $align === 'right' ? 'justify-end' : ($align === 'center' ? 'justify-center' : '') }}">
                                    <span>{!! is_string($header['label']) && strpos($header['label'], '<') === 0 ? $header['label'] : e($header['label']) !!}</span>
                                    @if ($header['sortable'] ?? false)
                                        <span
                                            class="inline-flex flex-col opacity-50 transition-opacity group-hover:opacity-100"
                                            aria-hidden="true">
                                            <svg class="h-3 w-3 transition-colors duration-150"
                                                :class="sortKey === '{{ $header['key'] }}' && sortDir === 'asc' ?
                                                    'text-emerald-600' : 'text-gray-300'"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M14.77 12.79a.75.75 0 0 1-1.06-.02L10 8.832 6.29 12.77a.75.75 0 1 1-1.08-1.04l4.25-4.5a.75.75 0 0 1 1.08 0l4.25 4.5a.75.75 0 0 1-.02 1.06Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <svg class="-mt-1 h-3 w-3 transition-colors duration-150"
                                                :class="sortKey === '{{ $header['key'] }}' && sortDir === 'desc' ?
                                                    'text-emerald-600' : 'text-gray-300'"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        {{-- Empty state --}}
        @if (empty(trim((string) $slot)))
            <div class="px-6 py-16 text-center">
                <div
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl
                            bg-gray-100 text-gray-400 ring-1 ring-gray-200/80">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-semibold text-gray-700">{{ $emptyMessage }}</h3>
                <p class="mt-1.5 text-sm text-gray-400">{{ $emptyDescription }}</p>
            </div>
        @endif
    </div>

    {{-- Pagination --}}
    @if ($paginated)
        <div class="mt-4 flex flex-col items-center justify-between gap-4 sm:flex-row">
            <p class="text-sm text-gray-500">
                Showing <span class="font-semibold text-gray-700">1</span>
                to <span class="font-semibold text-gray-700">10</span>
                of <span class="font-semibold text-gray-700">100</span> results
            </p>
            <nav class="flex items-center gap-1" aria-label="Pagination">
                {{-- Previous --}}
                <button
                    class="inline-flex items-center gap-1 rounded-xl border border-gray-300 bg-white
                           px-3 py-2 text-sm font-medium text-gray-500 transition-colors
                           hover:bg-gray-50 hover:text-gray-700
                           disabled:cursor-not-allowed disabled:opacity-40"
                    disabled aria-label="Previous page">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Prev
                </button>

                {{-- Page numbers --}}
                <button
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl
                           bg-emerald-600 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20"
                    aria-current="page">1</button>
                <button
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-300
                           bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">2</button>
                <button
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-300
                           bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">3</button>
                <span class="inline-flex h-9 w-9 items-center justify-center text-sm text-gray-400">…</span>
                <button
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-300
                           bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">10</button>

                {{-- Next --}}
                <button
                    class="inline-flex items-center gap-1 rounded-xl border border-gray-300 bg-white
                           px-3 py-2 text-sm font-medium text-gray-700 transition-colors
                           hover:bg-gray-50 hover:text-gray-900"
                    aria-label="Next page">
                    Next
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </nav>
        </div>
    @endif
</div>
