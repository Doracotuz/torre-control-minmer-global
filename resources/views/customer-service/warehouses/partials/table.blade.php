<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Almacén</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre del Almacén</th><th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th></tr></thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($warehouses as $warehouse)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $warehouse->warehouse_id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $warehouse->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('customer-service.warehouses.edit', $warehouse) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                        <form action="{{ route('customer-service.warehouses.destroy', $warehouse) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este almacén?');">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-900 ml-4">Eliminar</button></form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No se encontraron almacenes.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $warehouses->withQueryString()->links() }}</div>
