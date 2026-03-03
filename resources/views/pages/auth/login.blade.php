<x-layouts.auth title="Log In">
    <div class="flex flex-col items-center">
        {{-- Logo --}}
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 shadow-lg">
            <svg class="h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a.75.75 0 0 1 .352-.642l7.5-4.5a.75.75 0 0 1 .796 0l7.5 4.5a.75.75 0 0 1 .352.642" />
            </svg>
        </div>

        <h2 class="mt-6 text-2xl font-bold tracking-tight text-gray-900">Sign in to POS System</h2>
        <p class="mt-2 text-sm text-gray-500">Enter your credentials to access your account</p>
    </div>

    {{-- Session status --}}
    @if(session('status'))
        <div class="mt-6 rounded-lg bg-emerald-50 p-4 text-center text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
        <x-form action="{{ route('login') }}" method="POST" submitText="Sign in">
            <x-input
                name="email"
                label="Email address"
                type="email"
                placeholder="you@example.com"
                required
                autofocus
                autocomplete="email"
            />

            <div>
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-500">
                            Forgot password?
                        </a>
                    @endif
                </div>
                <div class="mt-1.5">
                    <x-input
                        name="password"
                        type="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                </div>
            </div>

            <x-checkbox name="remember" label="Remember me" />
        </x-form>
    </div>

    @if(Route::has('register'))
        <p class="mt-6 text-center text-sm text-gray-500">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-medium text-emerald-600 hover:text-emerald-500">Sign up</a>
        </p>
    @endif
</x-layouts.auth>
