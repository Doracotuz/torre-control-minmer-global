<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Consulta de Inventario
            </h2>
            <a href="{{ route('wms.inventory.transfer.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold">
                Realizar Transferencia
            </a>
            <form method="GET" action="{{ route('wms.inventory.index') }}" class="mt-4 md:mt-0">
                <div class="flex">
                    <input type="text" name="search" placeholder="Buscar por SKU o nombre..."
                           value="{{ request('search') }}"
                           class="rounded-l-md border-gray-300 shadow-sm">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-r-md hover:bg-indigo-700">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($stock as $inventory)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-700">
                                        {{ $inventory->product->sku }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                        {{ $inventory->product->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">
                                        {{ $inventory->location->code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-lg font-bold text-gray-900">
                                        {{ $inventory->quantity }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        No se encontró inventario o el almacén está vacío.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                {{ $stock->links() }}
            </div>
        </div>
    </div>
</x-app-layout>