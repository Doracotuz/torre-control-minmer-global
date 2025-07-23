<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Plantillas de Rutas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            {{-- Usamos un grid para dividir la pantalla en dos paneles --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="space-y-6">
                    {{-- Botones de Navegación y Acción --}}
                    <div class="flex justify-between items-center">
                        <a href="{{ route('rutas.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                            &larr; Volver al Dashboard
                        </a>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('rutas.plantillas.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700">
                                Exportar Vista
                            </a>
                            <a href="{{ route('rutas.plantillas.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Exportar Todo
                            </a>
                            <a href="{{ route('rutas.plantillas.create') }}" class="inline-flex items-center px-4 py-2 bg-[#ff9c00] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600">
                                Crear Nueva Ruta
                            </a>
                        </div>
                    </div>

                    {{-- Filtros de Búsqueda --}}
                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <form action="{{ route('rutas.plantillas.index') }}" method="GET">
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                <input type="text" name="search" placeholder="Buscar por nombre..." value="{{ request('search') }}" class="rounded-md border-gray-300 shadow-sm">
                                <input type="text" name="region" placeholder="Filtrar por región..." value="{{ request('region') }}" class="rounded-md border-gray-300 shadow-sm">
                                <select name="tipo_ruta" class="rounded-md border-gray-300 shadow-sm">
                                    <option value="">Todos los tipos</option>
                                    <option value="Entrega" {{ request('tipo_ruta') == 'Entrega' ? 'selected' : '' }}>Entrega</option>
                                    <option value="Traslado" {{ request('tipo_ruta') == 'Traslado' ? 'selected' : '' }}>Traslado</option>
                                    <option value="Importacion" {{ request('tipo_ruta') == 'Importacion' ? 'selected' : '' }}>Importación</option>
                                </select>
                                <div>
                                    <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#2c3856] hover:bg-[#1a2b41]">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Notificaciones Flash --}}
                    <div id="flash-container">
                        <div id="flash-success" class="fixed top-5 right-5 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center" role="alert" style="display: none; min-width: 300px;">
                            <strong class="font-bold mr-1">¡Éxito!</strong>
                            <span id="flash-success-message" class="block sm:inline"></span>
                        </div>
                    </div>

                    {{-- Tabla de Rutas --}}
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="p-4"><svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paradas</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($rutas as $ruta)
                                    <tr>
                                        <td class="p-4"><input type="checkbox" class="route-checkbox rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]" data-ruta-id="{{ $ruta->id }}"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ruta->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ruta->tipo_ruta }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ruta->paradas_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('rutas.plantillas.edit', $ruta) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                            <form action="{{ route('rutas.plantillas.duplicate', $ruta) }}" method="POST" class="inline ml-4" onsubmit="event.preventDefault(); duplicarRuta(this, '{{ $ruta->nombre }}');">@csrf<input type="hidden" name="new_name"><button type="submit" class="text-green-600 hover:text-green-900">Duplicar</button></form>
                                            <form action="{{ route('rutas.plantillas.destroy', $ruta) }}" method="POST" class="inline ml-4" onsubmit="return confirm('¿Estás seguro?');">@csrf @method('DELETE')<button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button></form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No se encontraron rutas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Paginación --}}
                    <div class="mt-6">{{ $rutas->appends(request()->query())->links() }}</div>
                </div>

                <div class="bg-white rounded-lg shadow-md sticky top-8">
                    <div id="map-panel" class="w-full h-[80vh] rounded-lg bg-gray-200"></div>
                </div>
            </div>
        </div>
    </div>

<script>
    window.rutasJson = {!! $rutasJson !!};
</script>
<script>
    function duplicarRuta(form, nombreOriginal) {
        const nuevoNombre = prompt("Introduce el nuevo nombre para la ruta duplicada:", nombreOriginal + " - Copia");
        
        if (nuevoNombre && nuevoNombre.trim() !== "") {
            form.querySelector('input[name="new_name"]').value = nuevoNombre;
            form.submit();
        }
    }
</script>

{{-- SCRIPT QUE CARGA GOOGLE MAPS Y LLAMA A LA FUNCIÓN CORRECTA --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,drawing&callback=initIndexMap" async defer></script>

</x-app-layout>