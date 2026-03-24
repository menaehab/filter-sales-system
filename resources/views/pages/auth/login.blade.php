<x-layouts.auth title="Log In">
    <div class="flex flex-col items-center">
        {{-- Logo --}}
        <div
            class="flex h-32 w-32 items-center justify-center rounded-2xl shadow-xl bg-gradient-to-br from-white to-gray-100 hover:scale-105 transition-transform duration-300">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-24 w-24 object-contain rounded-lg">
        </div>

        <h2 class="mt-6 text-2xl font-bold tracking-tight text-gray-900">{{ __('keywords.sign_in') }}</h2>
        <p class="mt-2 text-sm text-gray-500">{{ __('keywords.enter_credentials') }}</p>
    </div>

    {{-- Session status --}}
    @if (session('status'))
        <div class="mt-6 rounded-lg bg-emerald-50 p-4 text-center text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <x-form action="{{ route('login') }}" method="POST" submitText="{{ __('keywords.sign_in') }}">
            <x-input name="login" label="{{ __('keywords.email_or_phone') }}" type="text"
                placeholder="{{ __('keywords.enter_your_email_or_phone') }}" required autofocus autocomplete="email" />

            <div>
                <div class="mt-1.5">
                    <x-input name="password" type="password" label="{{ __('keywords.password') }}"
                        placeholder="••••••••" required autocomplete="current-password" />
                </div>
            </div>

            <x-checkbox name="remember" label="{{ __('keywords.remember_me') }}" />
        </x-form>
    </div>
</x-layouts.auth>
