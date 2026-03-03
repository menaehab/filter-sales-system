{{-- Language / Direction Toggle --}}
{{-- Place this component anywhere in your layout (e.g., navbar) to allow switching between LTR and RTL --}}

<div x-data="{ isRtl: document.documentElement.dir === 'rtl' }">
    <button
        @click="
            isRtl = !isRtl;
            document.documentElement.dir = isRtl ? 'rtl' : 'ltr';
            document.documentElement.lang = isRtl ? 'ar' : 'en';
        "
        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
        :title="isRtl ? 'Switch to LTR' : 'Switch to RTL'"
    >
        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.148 15.08 2 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
        </svg>
        <span x-text="isRtl ? 'EN' : 'عربي'"></span>
    </button>
</div>
