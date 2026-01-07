<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Панель управления') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-blue-500 rounded-md">
                                {!! icon('document', 'w-6 h-6 text-white') !!}
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Всего заказов</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalOrders }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-yellow-500 rounded-md">
                                {!! icon('clock', 'w-6 h-6 text-white') !!}
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Ожидают</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $pendingOrders }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-green-500 rounded-md">
                                {!! icon('check-circle', 'w-6 h-6 text-white') !!}
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Зарезервировано</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $reservedOrders }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 bg-gray-500 rounded-md">
                                {!! icon('cube', 'w-6 h-6 text-white') !!}
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Товаров на складе</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $totalInventory }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Быстрые действия</h3>
                        <div class="quick-actions">
                            <a href="{{ route('orders.create') }}" class="text-white">
                                Создать заказ
                            </a>
                            <a href="{{ route('orders.index') }}" class="text-white">
                                Просмотр заказов
                            </a>
                            <a href="{{ route('inventory.index') }}" class="text-white">
                                Просмотр склада
                            </a>
                        </div>
                    </div>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Последние заказы</h3>
                        <div class="space-y-3">
                            @forelse($recentOrders as $order)
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <div>
                                        <a href="{{ route('orders.show', $order->id) }}" class="font-medium text-blue-600 hover:text-blue-900">
                                        Заказ #{{ $order->id }}
                                        </a>
                                        <p class="text-sm text-gray-500">{{ $order->sku }} - {{ $order->qty }} шт.</p>
                                    </div>
                                    <div>
                                        @include('orders.partials.status-badge', ['status' => $order->status])
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">Заказов пока нет</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
