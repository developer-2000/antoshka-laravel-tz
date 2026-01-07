<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Заказы') }}
            </h2>
            <a href="{{ route('orders.create') }}"
               class="btn-create-order inline-flex items-center text-white font-bold py-2 px-4 rounded transition-colors">
                {!! icon('plus', 'w-5 h-5 mr-2') !!}
                Создать заказ
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('orders.index') }}" class="mb-6 flex gap-4">
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Поиск по SKU"
                                   class="w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <select name="status" class="rounded-md border-gray-300 shadow-sm">
                                <option value="">Все статусы</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Ожидает</option>
                                <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Зарезервирован</option>
                                <option value="awaiting_restock" {{ request('status') == 'awaiting_restock' ? 'selected' : '' }}>Ожидает пополнения</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Ошибка</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Поиск
                        </button>
                        @if(request('search') || request('status'))
                            <a href="{{ route('orders.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Сбросить
                            </a>
                        @endif
                    </form>

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">ID</th>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">SKU</th>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">Количество</th>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">Статус</th>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">Дата создания</th>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($orders as $order)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            #{{ $order->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ $order->sku }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $order->qty }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @include('orders.partials.status-badge', ['status' => $order->status])
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <button onclick="window.location.href='{{ route('orders.show', $order->id) }}'"
                                                    class="btn-view inline-flex items-center px-2 py-1 text-white text-xs rounded transition-colors"
                                                    title="Просмотр деталей">
                                                {!! icon('eye', 'w-4 h-4') !!}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Заказы не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

