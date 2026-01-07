<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                История движений: {{ $sku }}
            </h2>
            <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                {!! icon('arrowLeft', 'w-4 h-4 mr-2') !!}
                Назад к складу
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if($inventory)
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-4">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            {!! icon('cube', 'w-6 h-6 mr-2 text-white') !!}
                            Текущее состояние
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex gap-4">
                            <div class="flex-1 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-3 bg-green-500 rounded-md">
                                            {!! icon('cube', 'w-6 h-6 text-white') !!}
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Доступно</p>
                                            <p class="text-2xl font-semibold text-gray-900">{{ $inventory->available_qty }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-3 bg-yellow-500 rounded-md">
                                            {!! icon('check-circle', 'w-6 h-6 text-white') !!}
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Зарезервировано</p>
                                            <p class="text-2xl font-semibold text-gray-900">{{ $inventory->reserved_qty }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 p-3 bg-blue-500 rounded-md">
                                            {!! icon('chart', 'w-6 h-6 text-white') !!}
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-500">Всего</p>
                                            <p class="text-2xl font-semibold text-gray-900">{{ $inventory->getTotalQty() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-200">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        {!! icon('chart', 'w-6 h-6 mr-2 text-white') !!}
                        История движений
                    </h3>
                </div>
                <div class="p-6">
                    <form method="GET" action="{{ route('inventory.movements', $sku) }}" class="mb-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Тип движения</label>
                                <select name="movement_type" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Все типы</option>
                                    <option value="reserve" {{ request('movement_type') == 'reserve' ? 'selected' : '' }}>Резервирование</option>
                                    <option value="release" {{ request('movement_type') == 'release' ? 'selected' : '' }}>Освобождение</option>
                                    <option value="restock" {{ request('movement_type') == 'restock' ? 'selected' : '' }}>Пополнение</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Дата от</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                       class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Дата до</label>
                                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                       class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <div class="flex items-end gap-2">
                                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Применить
                                </button>
                                @if(request('movement_type') || request('date_from') || request('date_to'))
                                    <a href="{{ route('inventory.movements', $sku) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                        Сбросить
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($movements->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">Тип</th>
                                        <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">Количество</th>
                                        <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">До</th>
                                        <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">После</th>
                                        <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">Заказ</th>
                                        <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">Дата</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($movements as $movement)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($movement->movement_type == 'reserve')
                                                    <span class="movement-badge movement-badge-reserve">Резервирование</span>
                                                @elseif($movement->movement_type == 'release')
                                                    <span class="movement-badge movement-badge-release">Освобождение</span>
                                                @else
                                                    <span class="movement-badge movement-badge-restock">Пополнение</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $movement->qty }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $movement->qty_before }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $movement->qty_after }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                @if($movement->order_id)
                                                    <a href="{{ route('orders.show', $movement->order_id) }}" class="text-blue-600 hover:text-blue-900">
                                                        #{{ $movement->order_id }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $movement->created_at->format('d.m.Y H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            {!! icon('document', 'svg-icon-small mx-auto text-gray-400') !!}
                            <p class="mt-4 text-sm text-gray-500">Движений пока нет</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        {{ $movements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
