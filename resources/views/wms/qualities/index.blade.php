<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Catálogo de Calidades</h2>
        <a href="{{ route('wms.qualities.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Añadir Calidad</a>
    </x-slot>
    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left">Nombre</th><th class="px-6 py-3 text-left">Descripción</th><th class="px-6 py-3 text-right">Acciones</th></tr></thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($qualities as $quality)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $quality->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $quality->description }}</td>
                            <td class="px-6 py-4 text-right text-sm"><a href="{{ route('wms.qualities.edit', $quality) }}" class="text-indigo-600">Editar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</x-app-layout>