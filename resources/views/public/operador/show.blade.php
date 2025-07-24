<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Operador - Guía {{ $guia->guia }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .tab-button.active {
            border-bottom-width: 2px;
            border-color: #ff9c00;
            color: #2c3856;
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen pb-12">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <div class="bg-white shadow-2xl rounded-2xl p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-center border-b pb-4 mb-4">
                <div class="text-center sm:text-left mb-4 sm:mb-0">
                    <img class="mx-auto sm:mx-0 h-16 w-auto mb-2" src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Minmer Global Logo">
                    <h2 class="text-2xl font-bold tracking-tight text-[#2c3856]">
                        Guía de Viaje: <span class="text-[#ff9c00]">{{ $guia->guia }}</span>
                    </h2>
                    <p class="text-sm text-gray-600">Operador: {{ $guia->operador }} | Placas: {{ $guia->placas }}</p>
                    <p class="text-sm text-gray-600">Estatus: <span class="font-semibold @if($guia->estatus == 'En Espera') text-yellow-600 @elseif($guia->estatus == 'Planeada') text-blue-600 @elseif($guia->estatus == 'En Transito') text-purple-600 @elseif($guia->estatus == 'Completada') text-green-600 @endif">{{ $guia->estatus }}</span></p>
                </div>
                
                <a href="{{ route('operador.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm font-semibold">
                    &larr; Volver
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Botón de Iniciar Ruta --}}
            @if($showStartButton)
                <div class="text-center mb-6" x-data="{
                    isSubmitting: false,
                    startTrip() {
                        this.isSubmitting = true;
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition((position) => {
                                // Llenar los campos ocultos con la ubicación
                                document.getElementById('start_lat').value = position.coords.latitude;
                                document.getElementById('start_lng').value = position.coords.longitude;
                                // Enviar el formulario programáticamente
                                document.getElementById('startTripForm').submit();
                            }, (error) => {
                                console.error('Error getting location for start trip: ', error);
                                alert('No se pudo obtener tu ubicación actual para iniciar la ruta. Por favor, habilita los servicios de ubicación.');
                                this.isSubmitting = false; // Re-habilitar el botón
                            });
                        } else {
                            alert('Tu navegador no soporta geolocalización.');
                            this.isSubmitting = false; // Re-habilitar el botón
                        }
                    }
                }">
                    <form action="{{ route('operador.start-trip', $guia) }}" method="POST" id="startTripForm">
                        @csrf
                        <input type="hidden" name="latitud" id="start_lat">
                        <input type="hidden" name="longitud" id="start_lng">
                        <button type="button" @click="if(confirm('¿Estás seguro de que quieres iniciar esta ruta?')) startTrip()"
                                :disabled="isSubmitting"
                                class="w-full sm:w-auto px-6 py-3 bg-[#ff9c00] text-white rounded-lg font-bold text-lg hover:bg-orange-600 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">Iniciar Ruta</span>
                            <span x-show="isSubmitting">Iniciando Ruta...</span>
                        </button>
                    </form>
                </div>
            @endif

            {{-- Tabs de navegación --}}
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button type="button" @click="currentTab = 'facturas'" :class="{'tab-button active': currentTab === 'facturas', 'tab-button text-gray-500 hover:text-gray-700': currentTab !== 'facturas'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Facturas
                    </button>
                </nav>
            </div>

            <div x-data="{ currentTab: 'facturas', 
                            showFacturaModal: false, 
                            showNotificationModal: false,
                            selectedFactura: null,
                            notificationSubtype: 'Sanitario',
                            eventSubtypes: {
                                'Notificacion': ['Sanitario', 'Alimentos', 'Combustible', 'Pernocta', 'Percance', 'Otro'],
                            }
                        }" 
                 x-init="
                    // Initialize current location for map and event forms
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition((position) => {
                            document.getElementById('start_lat').value = position.coords.latitude;
                            document.getElementById('start_lng').value = position.coords.longitude;
                            document.querySelectorAll('[name=\'latitud\']').forEach(el => el.value = position.coords.latitude);
                            document.querySelectorAll('[name=\'longitud\']').forEach(el => el.value = position.coords.longitude);
                        }, (error) => {
                            console.error('Error getting location: ', error);
                            alert('No se pudo obtener tu ubicación actual. Por favor, habilita los servicios de ubicación.');
                        });
                    } else {
                        alert('Tu navegador no soporta geolocalización.');
                    }
                ">
                
                {{-- Contenido de la Tab de Facturas --}}
                <div x-show="currentTab === 'facturas'" class="mt-6">
                    <h3 class="text-xl font-semibold text-[#2c3856] mb-4">Facturas de la Guía</h3>
                    
                    <div class="space-y-4">
                        @forelse ($facturas as $factura)
                            <div class="bg-gray-50 p-4 rounded-lg shadow flex flex-col sm:flex-row justify-between items-center">
                                <div>
                                    <p class="font-bold text-gray-800">Factura: <span class="text-[#ff9c00]">{{ $factura->numero_factura }}</span></p>
                                    <p class="text-sm text-gray-700">Destino: {{ $factura->destino }}</p>
                                    <p class="text-xs text-gray-500">Cajas: {{ $factura->cajas }} | Botellas: {{ $factura->botellas }}</p>
                                    <p class="text-sm font-semibold @if($factura->estatus_entrega == 'Pendiente') text-yellow-600 @elseif($factura->estatus_entrega == 'Entregada') text-green-600 @else text-red-600 @endif">
                                        Estatus: {{ $factura->estatus_entrega }}
                                    </p>
                                </div>
                                @if($showEventButtons && $factura->estatus_entrega == 'Pendiente')
                                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-4 sm:mt-0">
                                        <button @click="selectedFactura = {{ $factura->id }}; showFacturaModal = true; $nextTick(() => { document.getElementById('factura_subtipo').value = 'Factura Entregada'; })" 
                                                class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                            Factura Entregada
                                        </button>
                                        <button @click="selectedFactura = {{ $factura->id }}; showFacturaModal = true; $nextTick(() => { document.getElementById('factura_subtipo').value = 'Factura no entregada'; })" 
                                                class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                            Factura no entregada
                                        </button>
                                    </div>
                                @elseif($factura->estatus_entrega != 'Pendiente')
                                    <span class="text-sm text-gray-500 mt-4 sm:mt-0">Acción registrada</span>
                                @else
                                    <span class="text-sm text-gray-500 mt-4 sm:mt-0">Ruta no iniciada</span>
                                @endif
                            </div>
                        @empty
                            <p class="text-center text-gray-500">No hay facturas asociadas a esta guía.</p>
                        @endforelse
                    </div>
                </div>
                <br>
                    @if($showEventButtons)
                        <div class="flex justify-center">
                            <button type="button" @click="openNotificationModal()" class="px-4 py-2 bg-[#2c3856] text-white text-sm rounded-md hover:bg-[#1e263d] disabled:opacity-50 disabled:cursor-not-allowed">
                                + Agregar Evento
                            </button>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm mb-4">Inicia la ruta para poder registrar eventos.</p>
                    @endif                    

                {{-- Modal para Evento de Factura --}}
                <div x-show="showFacturaModal" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div @click.outside="showFacturaModal = false" class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                        <h3 class="text-lg font-bold text-[#2c3856] mb-4">Registrar Evento de Factura</h3>
                        <form :action="`/operador/${guia.guia}/facturas/${selectedFactura}/event`" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="subtipo" id="factura_subtipo">
                            <input type="hidden" name="latitud" id="fact_lat">
                            <input type="hidden" name="longitud" id="fact_lng">

                            <div class="mb-4">
                                <label for="factura_nota" class="block text-sm font-medium text-gray-700">Nota (Opcional)</label>
                                <textarea name="nota" id="factura_nota" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="factura_evidencia" class="block text-sm font-medium text-gray-700">Evidencia (Foto/Video, máx. 50MB)</label>
                                <input type="file" name="evidencia" id="factura_evidencia" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#ff9c00]/20 file:text-[#ff9c00] hover:file:bg-[#ff9c00]/30">
                            </div>
                            <div class="flex justify-end space-x-4">
                                <button type="button" @click="showFacturaModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Modal para Evento de Notificación --}}
                <div x-show="showNotificationModal" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div @click.outside="showNotificationModal = false" class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                        <h3 class="text-lg font-bold text-[#2c3856] mb-4">Registrar Notificación</h3>
                        <form :action="`/operador/${guia.guia}/notifications/event`" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="latitud" id="notif_lat">
                            <input type="hidden" name="longitud" id="notif_lng">
                            
                            <div class="mb-4">
                                <label for="notification_subtipo" class="block text-sm font-medium text-gray-700">Tipo de Notificación</label>
                                <select name="subtipo" x-model="notificationSubtype" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <template x-for="type in eventSubtypes.Notificacion" :key="type">
                                        <option :value="type" x-text="type"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="notification_nota" class="block text-sm font-medium text-gray-700">Nota (Opcional)</label>
                                <textarea name="nota" id="notification_nota" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="notification_evidencia" class="block text-sm font-medium text-gray-700">Evidencia (Foto/Video, Opcional, máx. 50MB)</label>
                                <input type="file" name="evidencia" id="notification_evidencia" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#ff9c00]/20 file:text-[#ff9c00] hover:file:bg-[#ff9c00]/30">
                            </div>
                            <div class="flex justify-end space-x-4">
                                <button type="button" @click="showNotificationModal = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div> {{-- Fin del x-data principal --}}

        </div>
    </div>
    
    {{-- Script para el mapa del operador --}}
    <script>
        function initOperatorMap() {
            const mapElement = document.getElementById('operator-map');
            if (!mapElement) {
                console.warn('Map element not found for operator view.');
                return;
            }

            // Obtener datos de la guía desde el backend para Alpine.js y JavaScript
            const guia = @json($guia); // Pasa todo el objeto guía para usar sus propiedades en JS

            let initialCenter = { lat: 19.4326, lng: -99.1332 }; // Default center (Mexico City)
            let hasRoute = false;

            if (guia.ruta && guia.ruta.paradas && guia.ruta.paradas.length > 0) {
                initialCenter = { lat: parseFloat(guia.ruta.paradas[0].latitud), lng: parseFloat(guia.ruta.paradas[0].longitud) };
                hasRoute = true;
            }

            const map = new google.maps.Map(mapElement, {
                center: initialCenter,
                zoom: 10,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                fullscreenControl: false,
                mapTypeControl: false,
                streetViewControl: false
            });

            if (hasRoute) {
                const directionsService = new google.maps.DirectionsService();
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    map: map,
                    suppressMarkers: false, // Show default markers for start/end/waypoints
                    polylineOptions: {
                        strokeColor: '#2c3856', // Azul oscuro de tu paleta
                        strokeOpacity: 0.8,
                        strokeWeight: 6
                    }
                });

                const waypoints = [];
                // Recorrer paradas para construir waypoints, omitiendo la primera y la última
                for (let i = 1; i < guia.ruta.paradas.length - 1; i++) {
                    waypoints.push({
                        location: { lat: parseFloat(guia.ruta.paradas[i].latitud), lng: parseFloat(guia.ruta.paradas[i].longitud) },
                        stopover: true
                    });
                }

                directionsService.route(
                    {
                        origin: { lat: parseFloat(guia.ruta.paradas[0].latitud), lng: parseFloat(guia.ruta.paradas[0].longitud) },
                        destination: { lat: parseFloat(guia.ruta.paradas[guia.ruta.paradas.length - 1].latitud), lng: parseFloat(guia.ruta.paradas[guia.ruta.paradas.length - 1].longitud) },
                        waypoints: waypoints,
                        optimizeWaypoints: false,
                        travelMode: google.maps.TravelMode.DRIVING,
                    },
                    (response, status) => {
                        if (status === 'OK') {
                            directionsRenderer.setDirections(response);
                            // Ajustar los límites del mapa para que se ajuste la ruta
                            if (response.routes[0]) {
                                map.fitBounds(response.routes[0].bounds);
                            }
                        } else {
                            console.error('Directions request failed due to ' + status);
                            // Mostrar un mensaje al usuario o un marcador por defecto si la ruta falla
                            alert('No se pudo cargar la ruta en el mapa: ' + status);
                        }
                    }
                );
            } else {
                console.warn('No hay ruta asignada o paradas para esta guía. Mostrando ubicación inicial.');
                // Puedes agregar un marcador para la ubicación inicial si no hay ruta
                new google.maps.Marker({
                    position: initialCenter,
                    map: map,
                    title: 'Ubicación de Referencia'
                });
            }

            // Add markers for existing events
            guia.eventos.forEach(evento => {
                new google.maps.Marker({
                    position: { lat: parseFloat(evento.latitud), lng: parseFloat(evento.longitud) },
                    map: map,
                    icon: { // Custom icon based on event type
                        url: getEventIcon(evento.subtipo), // Function to determine icon
                        scaledSize: new google.maps.Size(30, 30)
                    },
                    title: `Evento: ${evento.subtipo} - ${evento.nota}`
                });
            });
        }

        function getEventIcon(subtipo) {
            // Mapping subtipos to FontAwesome icons (using a simple logic, you might enhance this)
            // Note: For real FA icons, you'd need SVG or a dedicated library to render them as map icons.
            // For simplicity, here we'll use generic colors or simple shapes if not a real icon font is used.
            // For actual FA icons on Google Maps, you'd usually use a custom SVG from FA.
            const icons = {
                'Factura Entregada': 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
                'Factura no entregada': 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
                'Sanitario': 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                'Alimentos': 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png',
                'Combustible': 'http://maps.google.com/mapfiles/ms/icons/purple-dot.png',
                'Pernocta': 'http://maps.google.com/mapfiles/ms/icons/ltblue-dot.png',
                'Percance': 'http://maps.google.com/mapfiles/ms/icons/orange-dot.png',
                'Inicio de Ruta': 'http://maps.google.com/mapfiles/ms/icons/blue.png'
            };
            return icons[subtipo] || 'http://maps.google.com/mapfiles/ms/icons/info.png'; // Default icon
        }

        // Initialize Google Maps after DOM is loaded and API script is ready
        window.initOperatorMap = initOperatorMap; // Renaming for clarity if multiple maps

        // Global function for getting current location for modals
        function getCurrentLocationAndFill(latId, lngId) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    document.getElementById(latId).value = position.coords.latitude;
                    document.getElementById(lngId).value = position.coords.longitude;
                }, (error) => {
                    console.error('Error getting location: ', error);
                    alert('No se pudo obtener tu ubicación actual. Por favor, habilita los servicios de ubicación.');
                });
            } else {
                alert('Tu navegador no soporta geolocalización.');
            }
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('operatorView', () => ({
                currentTab: 'facturas',
                showFacturaModal: false,
                showNotificationModal: false,
                selectedFactura: null,
                notificationSubtype: 'Sanitario',
                eventSubtypes: {
                    'Notificacion': ['Sanitario', 'Alimentos', 'Combustible', 'Pernocta', 'Percance', 'Otro'],
                },
                // Expose getCurrentLocationAndFill to Alpine.js scope
                guia: @json($guia),

                // Method to open factura event modal and set initial subtype
                openFacturaModal(facturaId, subtype) {
                    this.selectedFactura = facturaId;
                    this.showFacturaModal = true;
                    this.$nextTick(() => {
                        document.getElementById('factura_subtipo').value = subtype;
                        getCurrentLocationAndFill('fact_lat', 'fact_lng'); // Get location when modal opens
                    });
                },
                // Method to open notification event modal
                openNotificationModal() {
                    this.showNotificationModal = true;
                    this.$nextTick(() => {
                        getCurrentLocationAndFill('notif_lat', 'notif_lng'); // Get location when modal opens
                    });
                },
                // Filter facturas that are still pending
                getPendingFacturas: function() {
                    return this.guia.facturas.filter(factura => factura.estatus_entrega === 'Pendiente');
                },
            }));
        });
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,geometry&callback=initOperatorMap" async defer></script>
</body>
</html>