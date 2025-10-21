<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Sesiones de Conteo Físico</h2>
            <a href="{{ route('wms.physical-counts.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Nueva Sesión</a>
        </div>
        </x-slot>
    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">Nombre</th><th class="px-6 py-3 text-left">Tipo</th>
                        <th class="px-6 py-3 text-left">Estatus</th><th class="px-6 py-3 text-left">Creado por</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($sessions as $session)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $session->name }}</td>
                            <td class="px-6 py-4">{{ $session->type }}</td>
                            <td class="px-6 py-4">{{ $session->status }}</td>
                            <td class="px-6 py-4">{{ $session->user->name }}</td>
                            <td class="px-6 py-4 text-right"><a href="{{ route('wms.physical-counts.show', $session) }}" class="text-indigo-600">Ver Dashboard</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</x-app-layout>