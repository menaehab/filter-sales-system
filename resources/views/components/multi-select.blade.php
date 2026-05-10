@props([
    'label' => null,
    'name',
    'options' => [],
    'placeholder' => __('keywords.select_options'),
    'required' => false,
    'disabled' => false,
])

<div x-data="{
    open: false,
    search: '',
    selected: @entangle($attributes->wire('model')),
    options: @js($options),
    
    get filteredOptions() {
        if (!this.search) return this.options;
        return Object.fromEntries(
            Object.entries(this.options).filter(([id, name]) => 
                name.toLowerCase().includes(this.search.toLowerCase())
            )
        );
    },
    
    get selectedLabels() {
        if (!this.selected || !Array.isArray(this.selected) || this.selected.length === 0) return '';
        const labels = this.selected.map(id => this.options[String(id)]).filter(Boolean);
        if (labels.length <= 2) return labels.join(', ');
        return labels.slice(0, 2).join(', ') + ' + ' + (labels.length - 2) + ' ...';
    },

    toggle(id) {
        id = String(id);
        if (!Array.isArray(this.selected)) this.selected = [];
        
        if (this.selected.map(String).includes(id)) {
            this.selected = this.selected.map(String).filter(i => i !== id);
        } else {
            this.selected = [...this.selected.map(String), id];
        }
    },

    isSelected(id) {
        if (!Array.isArray(this.selected)) return false;
        return this.selected.map(String).includes(String(id));
    },

    clear() {
        this.selected = [];
    }
}" class="relative w-full" @click.away="open = false">
    @if ($label)
        <label class="mb-1.5 block text-sm font-medium leading-6 text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <button type="button" @click="open = !open" 
            class="relative w-full cursor-pointer rounded-xl border border-gray-300 bg-white py-2.5 ps-3.5 pe-10 text-start text-sm shadow-sm transition-all duration-200 hover:border-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400"
            :class="{ 'border-emerald-500 ring-2 ring-emerald-500/25': open }"
            {{ $disabled ? 'disabled' : '' }}>
            
            <span x-show="!selected || selected.length === 0" class="block truncate text-gray-400">
                {{ $placeholder }}
            </span>
            <span x-show="selected && selected.length > 0" class="block truncate text-gray-900 font-medium" x-text="selectedLabels">
            </span>

            <span class="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3">
                <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
            </span>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-2 w-full min-w-[240px] rounded-xl border border-gray-200 bg-white p-2 shadow-xl focus:outline-none"
            x-cloak>
            
            {{-- Search Input --}}
            <div class="relative mb-2">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <i class="fas fa-search text-xs text-gray-400"></i>
                </div>
                <input type="text" x-model="search" 
                    class="block w-full rounded-lg border-gray-200 py-2 ps-9 pe-3 text-xs focus:border-emerald-500 focus:ring-emerald-500"
                    placeholder="{{ __('keywords.search') }}..."
                    @click.stop>
            </div>

            <div class="max-h-60 overflow-y-auto custom-scrollbar">
                <template x-for="(label, id) in filteredOptions" :key="id">
                    <div @click.stop="toggle(id)" 
                        class="flex cursor-pointer items-center rounded-lg px-2 py-2 hover:bg-emerald-50 transition-colors group">
                        <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded border border-gray-300 bg-white transition-all duration-200 group-hover:border-emerald-500"
                            :class="{ 'bg-emerald-500 border-emerald-500 shadow-sm shadow-emerald-200': isSelected(id) }">
                            <i class="fas fa-check text-[10px] text-white" x-show="isSelected(id)" x-cloak></i>
                        </div>
                        <span class="ms-3 text-sm text-gray-700 transition-colors group-hover:text-emerald-700" 
                            :class="{ 'font-bold text-emerald-800': isSelected(id) }"
                            x-text="label"></span>
                    </div>
                </template>
                
                <div x-show="Object.keys(filteredOptions).length === 0" class="px-3 py-4 text-center text-xs text-gray-500">
                    {{ __('keywords.no_results_found') }}
                </div>
            </div>

            {{-- Footer / Actions --}}
            <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-2 px-1">
                <button type="button" @click="clear()" class="text-[11px] font-medium text-gray-500 hover:text-red-600">
                    {{ __('keywords.clear_all') }}
                </button>
                <button type="button" @click="open = false" class="rounded-md bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-700 hover:bg-emerald-100">
                    {{ __('keywords.done') }}
                </button>
            </div>
        </div>
    </div>
</div>
