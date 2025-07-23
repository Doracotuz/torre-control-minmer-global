<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seguimiento de Envío - Control Tower</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-2xl p-8 space-y-8 bg-white shadow-2xl rounded-2xl">
        <div>
            <img class="mx-auto h-24 w-auto" src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Minmer Global Logo">
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-[#2c3856]">
                Seguimiento de Envío
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingresa uno o varios números de factura (separados por comas) para ver su estado.
            </p>
        </div>

        <div x-data="{ 
            facturasInput: '', 
            results: [], 
            isLoading: false,
            errorMessage: ''
        }">
            <div class="space-y-4">
                <div>
                    <label for="facturas" class="sr-only">Números de Factura</label>
                    <textarea x-model="facturasInput" id="facturas" rows="3" class="relative block w-full appearance-none rounded-lg border border-gray-300 px-3 py-4 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-[#ff9c00] focus:outline-none focus:ring-[#ff9c00] sm:text-sm" placeholder="Ej: FCT001,FCT002,FCT003"></textarea>
                </div>
                <button type="button" @click="
                    isLoading = true; 
                    errorMessage = '';
                    fetch(`{{ route('tracking.search') }}?facturas=${facturasInput}`)
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => { throw err; });
                            }
                            return response.json();
                        })
                        .then(data => { 
                            results = data; 
                            isLoading = false; 
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            errorMessage = error.message || 'Ocurrió un error al buscar las facturas. Intenta de nuevo.';
                            isLoading = false;
                            results = []; // Clear previous results on error
                        });
                " class="group relative flex w-full justify-center rounded-lg border border-transparent bg-[#ff9c00] py-3 px-4 text-sm font-semibold text-white hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                :disabled="isLoading || facturasInput.trim() === ''">
                    <span x-show="!isLoading">Consultar</span>
                    <span x-show="isLoading">Cargando...</span>
                </button>
            </div>

            <div x-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mt-6" role="alert">
                <span class="block sm:inline" x-text="errorMessage"></span>
            </div>

            <div x-show="results.length > 0 && !isLoading" class="mt-8 space-y-6">
                <template x-for="result in results" :key="result.numero_factura">
                    <div class="bg-gray-50 p-6 rounded-lg shadow-md border border-gray-200">
                        <h3 class="text-xl font-bold text-[#2c3856] mb-2">Factura: <span x-text="result.numero_factura" class="text-[#ff9c00]"></span></h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-4">
                            <div><strong class="block text-gray-600">Estado de la Guía:</strong> <span x-text="result.estatus_guia"></span></div>
                            <div><strong class="block text-gray-600">Estado de la Factura:</strong> <span x-text="result.estatus_factura"></span></div>
                            <template x-if="result.guia">
                                <div class="col-span-2"><strong class="block text-gray-600">Guía Asociada:</strong> <span x-text="result.guia"></span></div>
                            </template>
                        </div>

                        <template x-if="result.ultimo_evento_entrega">
                            <div class="bg-white p-4 rounded-md border border-gray-200 mb-4">
                                <h4 class="text-md font-semibold text-gray-800 mb-2">Última Actualización de Entrega:</h4>
                                <p class="text-sm text-gray-700"><strong class="font-medium" x-text="result.ultimo_evento_entrega.subtipo"></strong></p>
                                <p class="text-sm text-gray-600" x-text="result.ultimo_evento_entrega.nota"></p>
                                <p class="text-xs text-gray-500">Fecha y Hora: <span x-text="result.ultimo_evento_entrega.fecha_evento"></span></p>
                                <template x-if="result.ultimo_evento_entrega.url_evidencia">
                                    <div class="mt-3">
                                        <a :href="result.ultimo_evento_entrega.url_evidencia" target="_blank" class="text-blue-600 hover:underline text-sm flex items-center">
                                            <i class="fas fa-camera mr-2"></i> Ver Evidencia
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="!result.ultimo_evento_entrega && result.estatus_factura !== 'No encontrada'">
                             <div class="bg-white p-4 rounded-md border border-gray-200 mb-4 text-center text-gray-600">
                                No hay eventos de entrega registrados aún para esta factura.
                            </div>
                        </template>

                        <template x-if="result.map_url">
                            <div class="mt-4">
                                <h4 class="text-md font-semibold text-gray-800 mb-2">Ubicación de Entrega:</h4>
                                <img :src="result.map_url" alt="Ubicación de Entrega" class="w-full h-auto rounded-lg shadow-sm">
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('app.Maps_api_key') }}&libraries=places,geometry" async defer></script>
</body>
</html>