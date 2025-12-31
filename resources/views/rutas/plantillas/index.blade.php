<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="relative w-full h-[calc(100vh-64px)] bg-[#2c3856] overflow-hidden font-['Montserrat',sans-serif]" 
         x-data="plantillasManager()">

        <div class="absolute top-16 right-14 md:right-4 z-20 flex flex-col space-y-3">
            <div class="bg-white/90 backdrop-blur-xl border border-white/40 p-2 rounded-xl shadow-lg flex flex-col space-y-2">
                
                <button type="button" @click="toggleMapStyle()" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-orange-50 transition-colors duration-300 group">
                    <div class="p-1.5 rounded-md bg-[#2c3856] text-white group-hover:bg-[#ff9c00] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-[#2c3856] hidden sm:inline" x-text="isMinmerStyle ? 'Minmer' : 'Natural'"></span>
                </button>

                <button type="button" @click="toggleTraffic()" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-orange-50 transition-colors duration-300 group">
                    <div :class="isTrafficOn ? 'bg-[#ff9c00] text-white' : 'bg-gray-200 text-gray-500'" class="p-1.5 rounded-md transition-colors group-hover:bg-[#ff9c00] group-hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-[#2c3856] hidden sm:inline">Tráfico</span>
                </button>

                <button type="button" @click="toggleSatellite()" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-orange-50 transition-colors duration-300 group">
                    <div :class="mapType === 'hybrid' ? 'bg-[#ff9c00] text-white' : 'bg-gray-200 text-gray-500'" class="p-1.5 rounded-md transition-colors group-hover:bg-[#ff9c00] group-hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-[#2c3856] hidden sm:inline" x-text="mapType === 'hybrid' ? 'Mapa' : 'Satélite'"></span>
                </button>

            </div>
        </div>

        <div id="map" class="absolute inset-0 z-0 w-full h-full"></div>

        <div class="absolute top-0 left-0 w-full h-full md:w-[600px] bg-white/85 backdrop-blur-xl border-r border-white/40 shadow-[10px_0_30px_rgba(0,0,0,0.1)] z-30 flex flex-col transition-all duration-500">
            
            <div class="p-6 md:p-8 pb-4 border-b border-gray-200/50">
                <div class="space-y-4 animate-fade-in-down">
                    <div class="flex justify-between items-center">
                        <a href="{{ route('rutas.dashboard') }}" class="group inline-flex items-center text-sm font-medium text-[#666666] hover:text-[#2c3856] transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Volver al Dashboard
                        </a>
                        <a href="{{ route('rutas.plantillas.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#ff9c00] to-orange-500 text-white rounded-lg font-bold text-xs uppercase tracking-widest hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                            + Nueva Ruta
                        </a>
                    </div>
                    
                    <div class="flex justify-between items-end">
                        <div>
                            <h2 class="font-['Raleway',sans-serif] font-extrabold text-2xl text-[#2c3856] leading-tight">
                                Plantillas de Rutas
                            </h2>
                            <div class="w-20 h-1.5 bg-[#ff9c00] rounded-full mt-2"></div>
                        </div>
                        <a href="{{ route('rutas.plantillas.export') }}" class="text-xs font-bold text-[#666666] hover:text-[#2c3856] underline decoration-dotted">
                            Exportar Todo
                        </a>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-12 gap-3 animate-fade-in-up delay-100">
                    <div class="col-span-12 md:col-span-5 relative group">
                        <input type="text" x-model.debounce.300ms="filters.search" placeholder=" " 
                               class="block w-full px-3 py-2 text-sm text-[#2b2b2b] bg-white/50 rounded-lg border border-gray-300 focus:outline-none focus:border-[#ff9c00] focus:ring-1 focus:ring-[#ff9c00] peer" />
                        <label class="absolute text-xs text-[#666666] duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-transparent px-1 peer-focus:px-1 peer-focus:text-[#ff9c00] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 left-2 pointer-events-none">
                            Buscar...
                        </label>
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <select x-model="filters.region" class="block w-full px-2 py-2 text-xs text-[#2b2b2b] bg-white/50 rounded-lg border border-gray-300 focus:border-[#ff9c00] focus:ring-0">
                            <option value="">Región</option>
                            @foreach(['MEX', 'MIN', 'GDL', 'MTY', 'SJD', 'CUN', 'MZN', 'VER'] as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 md:col-span-3">
                        <select x-model="filters.tipo_ruta" class="block w-full px-2 py-2 text-xs text-[#2b2b2b] bg-white/50 rounded-lg border border-gray-300 focus:border-[#ff9c00] focus:ring-0">
                            <option value="">Tipo</option>
                            <option value="Entrega">Entrega</option>
                            <option value="Traslado">Traslado</option>
                            <option value="Importacion">Importación</option>
                        </select>
                    </div>
                    <div class="col-span-12 md:col-span-1 flex justify-center">
                        <button @click="clearFilters()" title="Limpiar Filtros" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex gap-4 text-xs font-bold border-t border-gray-200/50 pt-3">
                    <button @click="selectVisible()" class="text-blue-600 hover:text-blue-800 transition-colors">Seleccionar Visibles</button>
                    <span class="text-gray-300">|</span>
                    <button @click="deselectVisible()" class="text-gray-500 hover:text-gray-800 transition-colors">Deseleccionar Visibles</button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 md:p-6 custom-scrollbar" id="table-container">
                <div x-show="isLoading" class="flex justify-center py-8">
                    <svg class="animate-spin h-8 w-8 text-[#ff9c00]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div x-show="!isLoading && tableView === null" class="animate-fade-in-up delay-200">
                    @include('rutas.plantillas.partials.table', ['rutas' => $rutas])
                </div>

                <div x-show="!isLoading && tableView !== null" x-html="tableView" class="animate-fade-in-up"></div>
            </div>
        </div>
    </div>

    <style>
        .animate-fade-in-down { animation: fadeInDown 0.6s ease-out both; }
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out both; }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        @keyframes fadeInDown { from { opacity: 0; transform: translate3d(0, -20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translate3d(0, 20px, 0); } to { opacity: 1; transform: translate3d(0, 0, 0); } }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(44, 56, 86, 0.2); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 156, 0, 0.5); }
    </style>

    <script>
        window.rutasJson = {!! $rutasJson !!};
        if (typeof window.routeColors === 'undefined') {
            window.routeColors = ['#ff9c00', '#2c3856', '#e74c3c', '#27ae60', '#8e44ad', '#3498db', '#f39c12', '#16a085'];
        }
    </script>

    <script>
        function duplicarRuta(form, nombreOriginal) {
            const nuevoNombre = prompt("Introduce el nuevo nombre:", nombreOriginal + " - Copia");
            if (nuevoNombre && nuevoNombre.trim() !== "") {
                form.querySelector('input[name="new_name"]').value = nuevoNombre;
                form.submit();
            }
        }
    </script>

    <script>
        window.indexMap = window.indexMap || null;
        window.directionsService = window.directionsService || null;
        window.activeRenderers = window.activeRenderers || {};
        window.trafficLayer = window.trafficLayer || null;

        window.initIndexMap = function() {
            const minmerStyles = [ 
                { "featureType": "all", "elementType": "labels.text.fill", "stylers": [{ "color": "#ffffff" }] },
                { "featureType": "all", "elementType": "labels.text.stroke", "stylers": [{ "color": "#2c3856" }, { "weight": 4 }] },
                { "featureType": "administrative", "elementType": "labels.text.fill", "stylers": [{ "color": "#ff9c00" }] },
                { "featureType": "landscape", "elementType": "all", "stylers": [{ "color": "#2c3856" }] },
                { "featureType": "poi", "elementType": "all", "stylers": [{ "visibility": "off" }] },
                { "featureType": "road", "elementType": "all", "stylers": [{ "saturation": -100 }, { "lightness": 45 }] },
                { "featureType": "road.highway", "elementType": "all", "stylers": [{ "visibility": "simplified" }] },
                { "featureType": "road.arterial", "elementType": "labels.icon", "stylers": [{ "visibility": "off" }] },
                { "featureType": "transit", "elementType": "all", "stylers": [{ "visibility": "off" }] },
                { "featureType": "water", "elementType": "all", "stylers": [{ "color": "#1a233a" }, { "visibility": "on" }] }
            ];

            const defaultLocation = { lat: 23.6345, lng: -102.5528 };
            
            window.indexMap = new google.maps.Map(document.getElementById("map"), {
                zoom: 5,
                center: defaultLocation,
                mapTypeId: 'roadmap',
                gestureHandling: 'greedy', 
                disableDefaultUI: false, // UI base
                zoomControl: true,
                zoomControlOptions: { position: google.maps.ControlPosition.RIGHT_CENTER },
                
                // OCULTAMOS CONTROL NATIVO PARA USAR EL BOTÓN PERSONALIZADO
                mapTypeControl: false, 
                
                streetViewControl: false,
                fullscreenControl: false,
                styles: minmerStyles
            });

            window.directionsService = new google.maps.DirectionsService();
            window.trafficLayer = new google.maps.TrafficLayer();

            document.dispatchEvent(new Event('map-ready'));
        };
    </script>
    
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,drawing&loading=async&callback=initIndexMap" async defer></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('plantillasManager', () => ({
                filters: {
                    search: @json(request('search', '')),
                    region: @json(request('region', '')),
                    tipo_ruta: @json(request('tipo_ruta', '')),
                    page: @json(request('page', 1))
                },
                tableView: null,
                isLoading: false,
                selectedRutas: [],
                visibleRutaIds: [],
                debounce: null,
                isMinmerStyle: true,
                isTrafficOn: false,
                mapType: 'roadmap', // Nuevo estado para el tipo de mapa
                
                init() {    
                    this.updateVisibleRutaIds();
                    this.$watch('filters', (newValue, oldValue) => {
                        if (newValue.search !== oldValue.search || newValue.region !== oldValue.region || newValue.tipo_ruta !== oldValue.tipo_ruta) { this.filters.page = 1; }
                        this.applyFilters();
                    });

                    this.$watch('selectedRutas', (newSelection, oldSelection) => {
                        const toAdd = newSelection.filter(id => !oldSelection.includes(id));
                        const toRemove = oldSelection.filter(id => !newSelection.includes(id));
                        toAdd.forEach(id => this.drawRouteOnMap(id));
                        toRemove.forEach(id => this.removeRouteFromMap(id));
                    });

                    this.$nextTick(() => {
                        document.getElementById('table-container').addEventListener('click', (e) => {
                            if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
                                e.preventDefault();
                                const url = new URL(e.target.href);
                                const page = url.searchParams.get('page');
                                if(page) { this.filters.page = page; }
                            }
                        });
                    });
                },

                // Lógica Estilo
                toggleMapStyle() {
                    this.isMinmerStyle = !this.isMinmerStyle;
                    this.updateMapOptions();
                },

                // Lógica Tráfico
                toggleTraffic() {
                    this.isTrafficOn = !this.isTrafficOn;
                    if(window.trafficLayer && window.indexMap) {
                        this.isTrafficOn ? window.trafficLayer.setMap(window.indexMap) : window.trafficLayer.setMap(null);
                    }
                },

                // NUEVA Lógica Satélite
                toggleSatellite() {
                    // Si estamos en 'roadmap', pasamos a 'hybrid' (satélite con etiquetas)
                    // Si estamos en 'hybrid', volvemos a 'roadmap'
                    this.mapType = this.mapType === 'roadmap' ? 'hybrid' : 'roadmap';
                    this.updateMapOptions();
                },

                // Función centralizada para actualizar el mapa
                updateMapOptions() {
                    if(!window.indexMap) return;

                    const minmerStyles = [ 
                        { "featureType": "all", "elementType": "labels.text.fill", "stylers": [{ "color": "#ffffff" }] },
                        { "featureType": "all", "elementType": "labels.text.stroke", "stylers": [{ "color": "#2c3856" }, { "weight": 4 }] },
                        { "featureType": "administrative", "elementType": "labels.text.fill", "stylers": [{ "color": "#ff9c00" }] },
                        { "featureType": "landscape", "elementType": "all", "stylers": [{ "color": "#2c3856" }] },
                        { "featureType": "poi", "elementType": "all", "stylers": [{ "visibility": "off" }] },
                        { "featureType": "road", "elementType": "all", "stylers": [{ "saturation": -100 }, { "lightness": 45 }] },
                        { "featureType": "road.highway", "elementType": "all", "stylers": [{ "visibility": "simplified" }] },
                        { "featureType": "road.arterial", "elementType": "labels.icon", "stylers": [{ "visibility": "off" }] },
                        { "featureType": "transit", "elementType": "all", "stylers": [{ "visibility": "off" }] },
                        { "featureType": "water", "elementType": "all", "stylers": [{ "color": "#1a233a" }, { "visibility": "on" }] }
                    ];

                    window.indexMap.setOptions({
                        mapTypeId: this.mapType, // Aplica 'roadmap' o 'hybrid'
                        // Si estamos en modo 'roadmap', aplicamos el estilo Minmer si está activo. Si es 'hybrid', el estilo JSON no aplica.
                        styles: (this.mapType === 'roadmap' && this.isMinmerStyle) ? minmerStyles : null
                    });
                },

                applyFilters() {
                    this.isLoading = true;
                    clearTimeout(this.debounce);
                    this.debounce = setTimeout(() => {
                        const params = new URLSearchParams(this.filters).toString();
                        fetch(`{{ route('rutas.plantillas.filter') }}?${params}`)
                            .then(response => response.json())
                            .then(data => {
                                this.tableView = data.tableView;
                                window.rutasJson = JSON.parse(data.rutasJson);
                                this.updateVisibleRutaIds();
                                const newUrl = `${window.location.pathname}?${params}`;
                                window.history.pushState({path: newUrl}, '', newUrl);
                                this.isLoading = false;
                            });
                    }, 300);
                },

                clearFilters() {
                    this.filters.search = '';
                    this.filters.region = '';
                    this.filters.tipo_ruta = '';
                    this.filters.page = 1;
                },

                updateVisibleRutaIds() {
                    this.$nextTick(() => {
                        const checkboxes = document.querySelectorAll('#table-container .route-checkbox');
                        this.visibleRutaIds = Array.from(checkboxes).map(cb => cb.value);
                    });
                },

                selectVisible() {
                    this.selectedRutas = [...new Set([...this.selectedRutas, ...this.visibleRutaIds])];
                },

                deselectVisible() {
                    this.selectedRutas = this.selectedRutas.filter(id => !this.visibleRutaIds.includes(id));
                },

                drawRouteOnMap(rutaId) {
                    if(!window.indexMap) return; 
                    const paradasParaRuta = window.rutasJson[rutaId];
                    if (!paradasParaRuta || paradasParaRuta.length < 2) return;
                    window.activeRenderers[rutaId] = { renderer: null, markers: [] };
                    const waypoints = paradasParaRuta.slice(1, -1).map(p => ({ location: { lat: p.lat, lng: p.lng }, stopover: true }));
                    const request = {
                        origin: { lat: paradasParaRuta[0].lat, lng: paradasParaRuta[0].lng },
                        destination: { lat: paradasParaRuta[paradasParaRuta.length - 1].lat, lng: paradasParaRuta[paradasParaRuta.length - 1].lng },
                        waypoints: waypoints,
                        travelMode: 'DRIVING'
                    };
                    window.directionsService.route(request, (result, status) => {
                        if (status == 'OK') {
                            const color = window.routeColors[rutaId % window.routeColors.length];
                            const renderer = new google.maps.DirectionsRenderer({ 
                                map: window.indexMap, 
                                directions: result, 
                                suppressMarkers: true, 
                                polylineOptions: { strokeColor: color, strokeWeight: 6, strokeOpacity: 0.9 } 
                            });
                            window.activeRenderers[rutaId].renderer = renderer;
                            paradasParaRuta.forEach((parada, index) => {
                                const stopMarker = new google.maps.Marker({
                                    position: { lat: parada.lat, lng: parada.lng },
                                    map: window.indexMap,
                                    label: { text: `${index + 1}`, color: "white", fontSize: "10px", fontWeight: "bold" },
                                    icon: { path: google.maps.SymbolPath.CIRCLE, scale: 12, fillColor: color, fillOpacity: 1, strokeWeight: 2, strokeColor: "white" },
                                    title: parada.nombre || `Parada ${index + 1}`,
                                    zIndex: 100 + index
                                });
                                window.activeRenderers[rutaId].markers.push(stopMarker);
                            });
                        }
                    });
                },

                removeRouteFromMap(rutaId) {
                    if (window.activeRenderers[rutaId]) {
                        if (window.activeRenderers[rutaId].renderer) window.activeRenderers[rutaId].renderer.setMap(null);
                        if (window.activeRenderers[rutaId].markers) window.activeRenderers[rutaId].markers.forEach(marker => marker.setMap(null));
                        delete window.activeRenderers[rutaId];
                    }
                }
            }));
        });
    </script>
</x-app-layout>