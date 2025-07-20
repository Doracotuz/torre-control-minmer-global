@extends('layouts.app')

@section('content')
{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
{{-- Font Awesome (para los iconos de los marcadores) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<style>
    #map { height: 80vh; border-radius: 0.5rem; }
    .route-list-item { cursor: pointer; transition: background-color 0.2s ease-in-out; }
    .route-list-item:hover { background-color: #f1f5f9; }
    .status-badge { padding: 0.35em 0.65em; font-size: .75em; font-weight: 700; line-height: 1; color: #fff; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: .25rem; }
    .status-planeada { background-color: #6c757d; }
    .status-asignada { background-color: #0d6efd; }
    .status-en-transito { background-color: #ff9c00; }
    .status-completada { background-color: #198754; }
    .status-cancelada { background-color: #dc3545; }
    /* Estilos para los iconos personalizados en el mapa */
    .custom-icon i { font-size: 24px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
    /* Estilo para el botón de filtro activo */
    .filter-btn-active {
        background-color: #2c3856;
        color: white;
        border-color: #2c3856;
    }
</style>

<div class="container mx-auto px-4" x-data="routeViewer()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Visualización de Rutas</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white p-4 shadow-xl rounded-lg">
            <h2 class="text-lg font-bold text-gray-800 mb-2">Rutas Activas</h2>
            <div class="flex flex-wrap gap-2 mb-4">
                <button @click="filterStatus = 'All'" :class="{'filter-btn-active': filterStatus === 'All'}" class="px-3 py-1 text-sm font-semibold border rounded-full">Todas</button>
                <button @click="filterStatus = 'En transito'" :class="{'filter-btn-active': filterStatus === 'En transito'}" class="px-3 py-1 text-sm font-semibold border rounded-full">En Tránsito</button>
                <button @click="filterStatus = 'Asignada'" :class="{'filter-btn-active': filterStatus === 'Asignada'}" class="px-3 py-1 text-sm font-semibold border rounded-full">Asignadas</button>
                <button @click="filterStatus = 'Planeada'" :class="{'filter-btn-active': filterStatus === 'Planeada'}" class="px-3 py-1 text-sm font-semibold border rounded-full">Planeadas</button>
            </div>

            <div class="max-h-[65vh] overflow-y-auto">
                <template x-for="route in filteredRoutes" :key="route.id">
                    <div class="route-list-item p-3 border-b">
                        <div class="flex justify-between items-start">
                            <div @click="focusOnRoute(route)" class="flex-grow">
                                <span class="font-semibold text-gray-700" x-text="route.name"></span>
                                <p class="text-sm text-gray-500" x-text="`${route.total_distance_km} km - ${route.total_duration_min} min`"></p>
                            </div>
                            <div class="flex flex-col items-end flex-shrink-0 ml-2">
                                <span class="status-badge" :class="`status-${route.status.toLowerCase().replace(' ', '-')}`" x-text="route.status"></span>
                                <form :action="`/tms/routes/${route.id}`" method="POST" @submit.prevent="confirmDelete($event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs mt-2" title="Eliminar Ruta">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div id="map" class="shadow-xl"></div>
        </div>
    </div>

    <div x-show="isModalOpen" @keydown.escape.window="isModalOpen = false" class="fixed inset-0 z-[1000] overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isModalOpen" @click="isModalOpen = false" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="isModalOpen" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-start">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" x-text="selectedRoute.name"></h3>
                        <span class="status-badge" :class="`status-${selectedRoute.status.toLowerCase().replace(' ', '-')}`" x-text="selectedRoute.status"></span>
                    </div>
                    <div class="mt-4 border-t pt-4 space-y-4">
                        <div>
                            <p><strong>Distancia:</strong> <span x-text="selectedRoute.total_distance_km"></span> km</p>
                            <p><strong>Duración:</strong> <span x-text="selectedRoute.total_duration_min"></span> minutos</p>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2">Embarques Asignados:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-600">
                                <template x-for="shipment in selectedRoute.shipments" :key="shipment.guide_number">
                                    <li x-text="`${shipment.guide_number} (${shipment.operator || 'N/A'})`"></li>
                                </template>
                                <li x-show="selectedRoute.shipments.length === 0">No hay embarques asignados.</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2">Eventos Registrados:</h4>
                            <ul class="list-disc list-inside text-sm text-gray-600 max-h-40 overflow-y-auto">
                                <template x-for="event in selectedRoute.events" :key="event.timestamp">
                                    <li x-text="`${event.type} - ${event.timestamp}`"></li>
                                </template>
                                <li x-show="selectedRoute.events.length === 0">No hay eventos registrados.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="isModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    function routeViewer() {
        return {
            map: null,
            allRoutes: [],
            routeLayers: new L.FeatureGroup(),
            eventMarkers: new L.FeatureGroup(),
            isModalOpen: false,
            selectedRoute: { name: '', status: '', shipments: [], events: [] },
            filterStatus: 'All',
            mapboxApiKey: 'pk.eyJ1IjoiZG9yYWNvdHV6IiwiYSI6ImNtZDhrMWVhdTAxMGsycHE3ODVjZTF5MjEifQ.jWhPKN41kfzZLMUqkdVobA',
            
            statusColors: {
                'Planeada': '#6c757d', 'Asignada': '#0d6efd', 'En transito': '#ff9c00',
                'Completada': '#198754', 'Cancelada': '#dc3545',
            },
            eventIcons: {
                'Inicio de Ruta': L.divIcon({ className: 'custom-icon', html: '<i class="fas fa-play-circle text-green-500"></i>', iconAnchor: [12, 12], popupAnchor: [0, -12] }),
                'Entrega': L.divIcon({ className: 'custom-icon', html: '<i class="fas fa-check-circle text-blue-500"></i>', iconAnchor: [12, 12], popupAnchor: [0, -12] }),
                'No Entregado': L.divIcon({ className: 'custom-icon', html: '<i class="fas fa-times-circle text-yellow-500"></i>', iconAnchor: [12, 12], popupAnchor: [0, -12] }),
                'En pension': L.divIcon({ className: 'custom-icon', html: '<i class="fas fa-bed text-purple-500"></i>', iconAnchor: [12, 12], popupAnchor: [0, -12] }),
                'Alimentos': L.divIcon({ className: 'custom-icon', html: '<i class="fas fa-utensils text-orange-500"></i>', iconAnchor: [12, 12], popupAnchor: [0, -12] }),
                'Altercado': L.divIcon({ className: 'custom-icon', html: '<i class="fas fa-exclamation-triangle text-red-500"></i>', iconAnchor: [12, 12], popupAnchor: [0, -12] }),
            },

            get filteredRoutes() {
                if (this.filterStatus === 'All') {
                    return this.allRoutes;
                }
                return this.allRoutes.filter(route => route.status === this.filterStatus);
            },

            init() {
                this.allRoutes = JSON.parse(@json($routesData));
                this.initMap();
                this.$watch('filterStatus', () => this.redrawMap(true)); // Al filtrar, sí ajustamos el mapa
                this.redrawMap(false); // En la carga inicial, NO ajustamos el mapa
            },

            initMap() {
                this.map = L.map('map').setView([19.6465, -99.1711], 13);
                L.tileLayer(`https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=${this.mapboxApiKey}`, {
                    attribution: '© Mapbox © OpenStreetMap', tileSize: 512, zoomOffset: -1
                }).addTo(this.map);
                this.routeLayers.addTo(this.map);
                this.eventMarkers.addTo(this.map);
                setTimeout(() => this.map.invalidateSize(), 100);
            },

            redrawMap(shouldFitBounds) {
                this.routeLayers.clearLayers();
                this.eventMarkers.clearLayers();
                
                if(this.filteredRoutes.length === 0) return;

                let allBounds = [];
                this.filteredRoutes.forEach(route => {
                    if (route.polyline && route.polyline.length > 0) {
                        const routeColor = this.statusColors[route.status] || '#2c3856';
                        const polyline = L.polyline(route.polyline, { color: routeColor, weight: 6, opacity: 0.8 });
                        
                        polyline.on('click', () => this.showRouteDetails(route));
                        this.routeLayers.addLayer(polyline);
                        allBounds.push(polyline.getBounds());
                        
                        this.drawRouteEvents(route);
                    }
                });
                
                // Solo ajustamos los límites si se indica y si hay rutas para mostrar
                if(shouldFitBounds && allBounds.length > 0) {
                    this.map.fitBounds(L.latLngBounds(allBounds).pad(0.1));
                }
            },

            drawRouteEvents(route) {
                route.events.forEach(event => {
                    const marker = L.marker([event.latitude, event.longitude], {
                        icon: this.eventIcons[event.type] || L.divIcon({ className: 'custom-icon', html: '<i class="fas fa-map-marker-alt"></i>' })
                    });

                    let popupContent = `<b>${event.type}</b><br>${event.timestamp}`;
                    if (event.notes) popupContent += `<br><i>${event.notes}</i>`;
                    if (event.photos && event.photos.length > 0) {
                        event.photos.forEach(photoUrl => {
                            popupContent += `<br><a href="${photoUrl}" target="_blank"><img src="${photoUrl}" width="150" class="mt-2 rounded"></a>`;
                        });
                    }
                    marker.bindPopup(popupContent);
                    this.eventMarkers.addLayer(marker);
                });
            },

            showRouteDetails(route) {
                this.selectedRoute = route;
                this.isModalOpen = true;
            },

            focusOnRoute(route) {
                // Para centrarse en una ruta específica, sí ajustamos los límites
                if(route.polyline) {
                    this.map.fitBounds(L.polyline(route.polyline).getBounds().pad(0.1));
                }
            },
            
            confirmDelete(event) {
                if (confirm('¿Estás seguro de que quieres eliminar esta ruta? Los embarques asignados volverán al estado "Por asignar".')) {
                    event.target.submit();
                }
            },
        }
    }
</script>
@endsection