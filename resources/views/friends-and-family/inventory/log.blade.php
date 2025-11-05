<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 md:mb-0">
                Registro de Movimientos (Friends & Family)
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('ff.inventory.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 transition ease-in-out duration-150">
                   <i class="fas fa-arrow-left mr-2"></i> Volver a Inventario
                </a>
                <a href="{{ route('ff.inventory.log.exportCsv') }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 transition ease-in-out duration-150">
                   <i class="fas fa-file-csv mr-2"></i> Exportar Registro
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha y Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto (SKU)</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Movimiento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($movements as $mov)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mov->created_at->format('d/m/Y h:i A') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{ $mov->user->name ?? 'N/A' }}</td> <td class="px-6 py-4 text-sm text-gray-700">
                                    <div class="font-medium">{{ $mov->product->description ?? 'Producto Eliminado' }}</div>
                                    <div class="text-xs text-gray-500">{{ $mov->product->sku ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($mov->quantity > 0)
                                        <span class="text-lg font-bold text-green-600">+{{ $mov->quantity }}</span>
                                    @else
                                        <span class="text-lg font-bold text-red-600">{{ $mov->quantity }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $mov->reason }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-history fa-2x mb-2"></i>
                                    <p>Aún no se han registrado movimientos.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 bg-white border-t">
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>