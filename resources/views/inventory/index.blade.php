<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Складские запасы') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('inventory.index') }}" class="mb-6">
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Поиск по SKU" 
                                       class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>
                            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Поиск
                            </button>
                            @if(request('search'))
                                <a href="{{ route('inventory.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    Сбросить
                                </a>
                            @endif
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">
                                        <a href="{{ route('inventory.index', array_merge(request()->all(), ['sort_by' => 'sku', 'sort_dir' => $sortBy == 'sku' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-gray-700 inline-block">
                                            SKU
                                            @if($sortBy == 'sku')
                                                @if($sortDir == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">
                                        <a href="{{ route('inventory.index', array_merge(request()->all(), ['sort_by' => 'available_qty', 'sort_dir' => $sortBy == 'available_qty' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-gray-700 inline-block">
                                            Доступно
                                            @if($sortBy == 'available_qty')
                                                @if($sortDir == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">
                                        <a href="{{ route('inventory.index', array_merge(request()->all(), ['sort_by' => 'reserved_qty', 'sort_dir' => $sortBy == 'reserved_qty' && $sortDir == 'asc' ? 'desc' : 'asc'])) }}" class="hover:text-gray-700 inline-block">
                                            Зарезервировано
                                            @if($sortBy == 'reserved_qty')
                                                @if($sortDir == 'asc')
                                                    ↑
                                                @else
                                                    ↓
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">Всего</th>
                                    <th class="px-6 py-3 text-center text-gray-500 uppercase tracking-wider table-header">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($inventory as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $item->sku }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $item->available_qty }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $item->reserved_qty }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ $item->getTotalQty() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <div class="flex justify-center table-actions-gap">
                                                <button onclick="window.location.href='{{ route('inventory.movements', $item->sku) }}'"
                                                        class="btn-view inline-flex items-center px-2 py-1 text-white text-xs font-medium rounded transition-colors"
                                                        title="История движений">
                                                    {!! icon('eye', 'w-4 h-4') !!}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Товары не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $inventory->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

