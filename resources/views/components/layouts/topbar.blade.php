@props([
    'title' => 'dashboard',
    'unreadNotificationsCount' => 0,
    'latestNotifications' => null,
])

<header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-gray-200 bg-white px-4 sm:px-6">
    {{-- Mobile menu button --}}
    <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 lg:hidden">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    {{-- Page title --}}
    <h1 class="text-lg font-semibold text-gray-900">{{ __('keywords.' . $title) }}</h1>

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Notifications --}}
    <div class="relative" x-data="notificationPanel()" x-on:confirmed-confirm-action.window="executeConfirmAction()">
        <button @click="open = !open"
            class="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
            title="{{ __('keywords.notifications') }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>

            @if ($unreadNotificationsCount > 0)
                <span
                    class="absolute -top-1 -end-1 min-w-5 rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-bold text-white">
                    {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                </span>
            @endif
        </button>

        <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute end-0 mt-2 w-96 overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black/5">

            {{-- Header with actions --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                <p class="text-sm font-semibold text-gray-800">{{ __('keywords.notifications') }}</p>

                @if ($unreadNotificationsCount > 0)
                    <button @click="markAllAsRead()"
                        class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                        {{ __('keywords.mark_all_as_read') }}
                    </button>
                @endif
            </div>

            {{-- Notifications list --}}
            <div class="max-h-96 overflow-y-auto">
                @forelse($latestNotifications as $notification)
                    @php
                        $notificationMessage = data_get($notification->data, 'message', __('keywords.notification'));
                        $notificationDate = optional($notification->created_at)->diffForHumans();
                        $notificationType = data_get($notification->data, 'type', 'default');
                        $typeColors = [
                            'customer_installment' => 'text-blue-600',
                            'supplier_installment' => 'text-purple-600',
                            'low_stock' => 'text-red-600',
                            'filter_candle' => 'text-amber-600',
                        ];
                        $typeColor = $typeColors[$notificationType] ?? 'text-gray-600';
                    @endphp
                    <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false"
                        class="relative border-b border-gray-50 px-4 py-3 {{ is_null($notification->read_at) ? 'bg-emerald-50/40' : '' }} hover:bg-gray-50 transition-colors">

                        {{-- Notification content --}}
                        <div class="flex items-start gap-3">
                            {{-- Icon --}}
                            <div class="flex-shrink-0 mt-0.5">
                                @if ($notificationType === 'customer_installment' || $notificationType === 'supplier_installment')
                                    <i class="fas fa-money-bill-wave {{ $typeColor }}"></i>
                                @elseif($notificationType === 'low_stock')
                                    <i class="fas fa-exclamation-triangle {{ $typeColor }}"></i>
                                @elseif($notificationType === 'filter_candle')
                                    <i class="fas fa-filter {{ $typeColor }}"></i>
                                @else
                                    <i class="fas fa-bell {{ $typeColor }}"></i>
                                @endif
                            </div>

                            {{-- Message --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700 leading-relaxed">{{ $notificationMessage }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $notificationDate }}</p>
                            </div>

                            {{-- Actions (shown on hover) --}}
                            <div x-show="hover" x-transition class="flex gap-1 flex-shrink-0">
                                @if (is_null($notification->read_at))
                                    <button @click="markAsRead('{{ $notification->id }}')"
                                        class="p-1.5 text-gray-400 hover:text-emerald-600 rounded transition-colors"
                                        title="{{ __('keywords.mark_as_read') }}">
                                        <i class="fas fa-check text-xs"></i>
                                    </button>
                                @endif
                                <button @click="deleteNotification('{{ $notification->id }}')"
                                    class="p-1.5 text-gray-400 hover:text-red-600 rounded transition-colors"
                                    title="{{ __('keywords.delete') }}">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-gray-500">
                        <i class="fas fa-bell-slash text-2xl text-gray-300 mb-2"></i>
                        <p>{{ __('keywords.no_notifications') }}</p>
                    </div>
                @endforelse
            </div>

            {{-- Footer with clear actions --}}
            @if ($latestNotifications->isNotEmpty())
                <div class="border-t border-gray-100 px-4 py-2 bg-gray-50 flex gap-2">
                    <button @click="deleteAllRead()"
                        class="flex-1 text-xs py-2 px-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded transition-colors">
                        <i class="fas fa-trash-alt me-1"></i>
                        {{ __('keywords.delete_read_notifications') }}
                    </button>
                    <button @click="deleteAll()"
                        class="flex-1 text-xs py-2 px-3 text-red-600 hover:text-red-700 hover:bg-red-50 rounded transition-colors">
                        <i class="fas fa-trash me-1"></i> {{ __('keywords.delete_all_notifications') }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    <x-modal name="confirm-action" title="{{ __('keywords.confirm') }}" maxWidth="sm">
        <x-slot:body>
            <p class="text-sm text-gray-600">{{ __('keywords.are_you_sure') }}</p>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary" @click="$dispatch('close-modal-confirm-action')">
                {{ __('keywords.cancel') }}
            </x-button>
            <x-button variant="danger"
                @click="$dispatch('confirmed-confirm-action'); $dispatch('close-modal-confirm-action')">
                {{ __('keywords.confirm') }}
            </x-button>
        </x-slot:footer>
    </x-modal>

    <script>
        function notificationPanel() {
            return {
                open: false,
                confirmMessage: '',
                confirmAction: null,
                setConfirm(message, action) {
                    window.confirmModalMessage = message;
                    this.confirmAction = action;
                    window.dispatchEvent(new CustomEvent('open-modal-confirm-action'));
                },
                async executeConfirmAction() {
                    if (typeof this.confirmAction === 'function') {
                        try {
                            await this.confirmAction();
                        } catch (error) {
                            console.error('Error executing confirmed action:', error);
                        }
                    }
                    this.confirmAction = null;
                    window.confirmModalMessage = '';
                },
                async markAsRead(id) {
                    try {
                        const response = await fetch(`/notifications/${id}/read`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        if (response.ok) {
                            location.reload();
                        }
                    } catch (error) {
                        console.error('Error marking notification as read:', error);
                    }
                },
                async markAllAsRead() {
                    this.setConfirm(@json(__('keywords.delete_all_read_notifications_confirmation')),
                        async () => {
                            try {
                                const response = await fetch('/notifications/read-all', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content
                                    }
                                });
                                if (response.ok) {
                                    location.reload();
                                }
                            } catch (error) {
                                console.error('Error marking all as read:', error);
                            }
                        }
                    );
                },
                async deleteNotification(id) {
                    this.setConfirm(@json(__('keywords.delete_notification_confirmation')),
                        async () => {
                            try {
                                const response = await fetch(`/notifications/${id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content
                                    }
                                });
                                if (response.ok) {
                                    location.reload();
                                }
                            } catch (error) {
                                console.error('Error deleting notification:', error);
                            }
                        }
                    );
                },
                async deleteAllRead() {
                    this.setConfirm(@json(__('keywords.delete_all_read_notifications_confirmation')),
                        async () => {
                            try {
                                const response = await fetch('/notifications/delete-all-read', {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content
                                    }
                                });
                                if (response.ok) {
                                    location.reload();
                                }
                            } catch (error) {
                                console.error('Error deleting read notifications:', error);
                            }
                        }
                    );
                },
                async deleteAll() {
                    this.setConfirm(@json(__('keywords.delete_all_notifications_confirmation')),
                        async () => {
                            try {
                                const response = await fetch('/notifications/delete-all', {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content
                                    }
                                });
                                if (response.ok) {
                                    location.reload();
                                }
                            } catch (error) {
                                console.error('Error deleting all notifications:', error);
                            }
                        }
                    );
                }
            }
        }
    </script>

    {{-- User avatar dropdown --}}
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-gray-100">
            <div
                class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-sm font-medium text-white">
                {{ auth()->user()->name[0] ?? 'A' }}
            </div>
            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>
        <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute end-0 mt-2 w-48 ltr:origin-top-right rtl:origin-top-left rounded-lg bg-white py-1 shadow-lg ring-1 ring-black/5">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100">{{ __('keywords.log_out') }}</button>
            </form>
        </div>
    </div>
</header>
