<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nueva Plantilla de Ruta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('rutas.plantillas.store') }}" method="POST" id="rutaForm">
                @csrf
                @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Hay errores en tu formulario:</p>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md space-y-6">
                        <div>
                            <a href="{{ route('rutas.plantillas.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Volver a la lista</a>
                        </div>
                        
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre de la Ruta</label>
                            <input type="text" name="nombre" id="nombre" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="region" class="block text-sm font-medium text-gray-700">Región</label>
                            <input type="text" name="region" id="region" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="tipo_ruta" class="block text-sm font-medium text-gray-700">Tipo de Ruta</label>
                            <select name="tipo_ruta" id="tipo_ruta" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="Entrega">Entrega</option>
                                <option value="Traslado">Traslado</option>
                                <option value="Importacion">Importación</option>
                            </select>
                        </div>
                        
                        <hr>

                        <div>
                            <h3 class="text-lg font-semibold text-[#2c3856] mb-2">Añadir Paradas</h3>
                            <label for="autocomplete" class="block text-sm font-medium text-gray-700">Buscar dirección</label>
                            <input type="text" id="autocomplete" placeholder="Escribe una dirección o lugar..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <p class="text-xs text-gray-500 mt-2">O haz clic derecho en el mapa para añadir una parada.</p>
                        </div>

                        <div id="paradas-container" class="space-y-3 mt-4">
                            </div>
                        <p id="paradas-error" class="text-red-500 text-sm mt-2 hidden">Debes añadir al menos 2 paradas.</p>

                        <div id="paradas-hidden-inputs"></div>
            
                        <input type="hidden" name="distancia_total_km" id="distancia-total-input" value="0">

                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-bold text-gray-800">Resumen de la Ruta</h4>
                            <p class="text-sm text-gray-600">Distancia Total: <span id="distancia-total" class="font-semibold">0 km</span></p>
                        </div>
                        
                        <div class="mt-6">
                            <button type="button" onclick="validarYEnviarFormulario()" class="w-full justify-center inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-[#ff9c00] hover:bg-orange-600">
                                Guardar Ruta
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

    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,drawing&callback=initMap" async defer></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

</x-app-layout>