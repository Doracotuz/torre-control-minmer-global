<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editando Ruta: <span class="text-[#ff9c00]">{{ $ruta->nombre }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('rutas.plantillas.update', $ruta) }}" method="POST" id="rutaForm">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md space-y-6">
                        <div>
                            <a href="{{ route('rutas.plantillas.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Volver a la lista</a>
                        </div>
                        
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre de la Ruta</label>
                            <input type="text" name="nombre" id="nombre" required value="{{ old('nombre', $ruta->nombre) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        
                        <div>
                            <label for="region" class="block text-sm font-medium text-gray-700">Región</label>
                            <input type="text" name="region" id="region" required value="{{ old('region', $ruta->region) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label for="tipo_ruta" class="block text-sm font-medium text-gray-700">Tipo de Ruta</label>
                            <select name="tipo_ruta" id="tipo_ruta" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="Entrega" {{ $ruta->tipo_ruta == 'Entrega' ? 'selected' : '' }}>Entrega</option>
                                <option value="Traslado" {{ $ruta->tipo_ruta == 'Traslado' ? 'selected' : '' }}>Traslado</option>
                                <option value="Importacion" {{ $ruta->tipo_ruta == 'Importacion' ? 'selected' : '' }}>Importación</option>
                            </select>
                        </div>
                        
                        <hr>

                        <div>
                            <h3 class="text-lg font-semibold text-[#2c3856] mb-2">Añadir/Editar Paradas</h3>
                            <label for="autocomplete" class="block text-sm font-medium text-gray-700">Buscar dirección</label>
                            <input type="text" id="autocomplete" placeholder="Escribe una dirección o lugar..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div id="paradas-container" class="space-y-3 mt-4"></div>
                        <p id="paradas-error" class="text-red-500 text-sm mt-2 hidden">Debes añadir al menos 2 paradas.</p>

                        <div id="paradas-hidden-inputs"></div>
                        <input type="hidden" name="distancia_total_km" id="distancia-total-input" value="{{ $ruta->distancia_total_km }}">
                        
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-bold text-gray-800">Resumen de la Ruta</h4>
                            <p class="text-sm text-gray-600">Distancia Total: <span id="distancia-total" class="font-semibold">{{ $ruta->distancia_total_km ?? '0' }} km</span></p>
                        </div>
                        
                        <div class="mt-6">
                            <button type="button" onclick="validarYEnviarFormulario()" class="w-full justify-center inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#ff9c00] hover:bg-orange-600">
                                Actualizar Ruta
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div id="map" class="w-full h-[75vh] rounded-lg shadow-md bg-gray-200"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.initialParadas = @json($initialParadas);
    </script>
    
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,drawing&callback=initMap" async defer></script>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

</x-app-layout>