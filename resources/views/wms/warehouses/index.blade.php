<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catálogo de Almacenes</h2>
            <a href="{{ route('wms.warehouses.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Añadir Almacén</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dirección</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($warehouses as $warehouse)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $warehouse->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ $warehouse->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ Str::limit($warehouse->address, 50) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('wms.warehouses.edit', $warehouse) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                    <form action="{{ route('wms.warehouses.destroy', $warehouse) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('¿Seguro?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay almacenes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $warehouses->links() }}</div>
        </div>
    </div>
</x-app-layout>