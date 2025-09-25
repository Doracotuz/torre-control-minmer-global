<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Plantillas de Rutas') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="plantillasManager()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <div class="space-y-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <a href="{{ route('rutas.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-[#2c3856] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1e2638] transition-colors">
                            &larr; Volver
                        </a>
                        <div class="flex items-center gap-2 sm:gap-4">
                            <a href="{{ route('rutas.plantillas.export') }}" class="inline-flex items-center px-4 py-2 bg-[#666666] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#2b2b2b] transition-colors">
                                Exportar Todo
                            </a>
                            <a href="{{ route('rutas.plantillas.create') }}" class="inline-flex items-center px-4 py-2 bg-[#ff9c00] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#e68c00] transition-colors">
                                Crear Nueva Ruta
                            </a>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow-md">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <input type="text" x-model.debounce.300ms="filters.search" placeholder="Buscar por nombre..." class="rounded-md border-gray-300 shadow-sm text-sm">
                            <select x-model="filters.region" class="rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Todas las regiones</option>
                                <option value="MEX">MEX</option>
                                <option value="MIN">MIN</option>
                                <option value="GDL">GDL</option>
                                <option value="MTY">MTY</option>
                                <option value="SJD">SJD</option>
                                <option value="CUN">CUN</option>
                                <option value="MZN">MZN</option>
                                <option value="VER">VER</option>
                            </select>
                            <select x-model="filters.tipo_ruta" class="rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">Todos los tipos</option>
                                <option value="Entrega">Entrega</option>
                                <option value="Traslado">Traslado</option>
                                <option value="Importacion">Importación</option>
                            </select>
                            <div>
                                <button @click="clearFilters()" class="w-full justify-center inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                                    Quitar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <button @click="selectVisible()" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Seleccionar Visibles</button>
                        <button @click="deselectVisible()" class="text-sm font-semibold text-gray-600 hover:text-gray-800">Deseleccionar Visibles</button>
                    </div>

                    <div id="table-container" x-html="tableView">
                        @include('rutas.plantillas.partials.table', ['rutas' => $rutas])
                    </div>
                </div>

                <div class="bg-[#E8ECF7] rounded-lg sticky top-8">
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

<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,drawing&callback=initIndexMap" async defer></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('plantillasManager', () => ({
                filters: {
                    search: '{{ request('search', '') }}',
                    region: '{{ request('region', '') }}',
                    tipo_ruta: '{{ request('tipo_ruta', '') }}',
                    page: '{{ request('page', 1) }}'
                },
                tableView: '',
                isLoading: false,
                selectedRutas: [],
                visibleRutaIds: [],
                debounce: null,
                
                init() {
                    this.tableView = document.getElementById('table-container').innerHTML;
                    this.updateVisibleRutaIds();

                    this.$watch('filters', (newValue, oldValue) => {
                        if (newValue.search !== oldValue.search || newValue.region !== oldValue.region || newValue.tipo_ruta !== oldValue.tipo_ruta) {
                            this.filters.page = 1;
                        }
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
                                this.filters.page = url.searchParams.get('page');
                            }
                        });
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
                                const currentSelection = this.selectedRutas;
                                this.selectedRutas = currentSelection.filter(id => this.visibleRutaIds.includes(id));
                                history.pushState({}, '', `?${params}`);
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
                    const paradasParaRuta = window.rutasJson[rutaId];
                    if (!paradasParaRuta || paradasParaRuta.length < 2) return;

                    activeRenderers[rutaId] = { renderer: null, markers: [] };

                    const waypoints = paradasParaRuta.slice(1, -1).map(p => ({ location: { lat: p.lat, lng: p.lng }, stopover: true }));
                    const request = {
                        origin: { lat: paradasParaRuta[0].lat, lng: paradasParaRuta[0].lng },
                        destination: { lat: paradasParaRuta[paradasParaRuta.length - 1].lat, lng: paradasParaRuta[paradasParaRuta.length - 1].lng },
                        waypoints: waypoints,
                        travelMode: 'DRIVING'
                    };
                    
                    directionsService.route(request, (result, status) => {
                        if (status == 'OK') {
                            const color = routeColors[rutaId % routeColors.length];
                            const renderer = new google.maps.DirectionsRenderer({ map: indexMap, directions: result, suppressMarkers: true, polylineOptions: { strokeColor: color, strokeWeight: 5, strokeOpacity: 0.8 } });
                            activeRenderers[rutaId].renderer = renderer;

                            paradasParaRuta.forEach((parada, index) => {
                                const stopMarker = new google.maps.Marker({
                                    position: { lat: parada.lat, lng: parada.lng },
                                    map: indexMap,
                                    label: { text: `${index + 1}`, color: "white", fontSize: "11px", fontWeight: "bold" },
                                    icon: {
                                        path: google.maps.SymbolPath.CIRCLE,
                                        scale: 10,
                                        fillColor: color,
                                        fillOpacity: 1,
                                        strokeWeight: 1.5,
                                        strokeColor: "white"
                                    },
                                    title: `Parada ${index + 1}`
                                });
                                activeRenderers[rutaId].markers.push(stopMarker);
                            });
                        }
                    });
                },

                removeRouteFromMap(rutaId) {
                    if (activeRenderers[rutaId]) {
                        if (activeRenderers[rutaId].renderer) {
                            activeRenderers[rutaId].renderer.setMap(null);
                        }
                        if (activeRenderers[rutaId].markers) {
                            activeRenderers[rutaId].markers.forEach(marker => marker.setMap(null));
                        }
                        delete activeRenderers[rutaId];
                    }
                }
            }));
        });
    </script>
</x-app-layout>