<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Marcas') }}
            </h2>
            <a href="{{ route('customer-service.products.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                &larr; Volver a Productos
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ isModalOpen: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                <button @click="isModalOpen = true" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-green-700">
                    Añadir Nueva Marca
                </button>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre de la Marca</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($brands as $brand)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $brand->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('customer-service.brands.destroy', $brand) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro? Solo se puede eliminar si no tiene productos asociados.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center p-4">No hay marcas creadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $brands->links() }}</div>
        </div>

        <!-- Modal para Crear Marca -->
        <div x-show="isModalOpen" @keydown.escape.window="isModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
            <div @click.outside="isModalOpen = false" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
                <h3 class="text-xl font-bold text-[#2c3856] mb-4">Crear Nueva Marca</h3>
                <form action="{{ route('customer-service.brands.store') }}" method="POST">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="mt-6 flex justify-end gap-4">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Guardar Marca</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
