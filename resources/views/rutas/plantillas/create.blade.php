<x-app-layout>
    <x-slot name="header"></x-slot>

    <div class="relative w-full h-[calc(100vh-64px)] bg-[#2c3856] overflow-hidden font-['Montserrat',sans-serif]">
        <div class="absolute top-0 left-0 w-full h-full z-0 opacity-10 pointer-events-none" 
             style="background-image: url('data:image/svg+xml,%3Csvg width=%22100%25%22 height=%22100%25%22 viewBox=%220 0 1440 800%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath fill=%22%23ffffff%22 fill-opacity=%221%22 d=%22M0,256L80,245.3C160,235,320,213,480,224C640,235,800,277,960,288C1120,299,1280,277,1360,266.7L1440,256L1440,0L1360,0C1280,0,1120,0,960,0C800,0,640,0,480,0C320,0,160,0,80,0L0,0Z%22%3E%3C/path%3E%3C/svg%3E'); background-size: cover; background-position: top;">
        </div>

        <div class="absolute top-4 left-4 md:left-auto md:right-[500px] z-20 flex flex-col space-y-3">
            <div class="bg-white/80 backdrop-blur-xl border border-white/40 p-2 rounded-xl shadow-lg flex flex-col space-y-2">
                <button type="button" onclick="window.toggleMapStyle()" id="btn-style" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-orange-50 transition-colors duration-300 group">
                    <div class="p-1.5 rounded-md bg-[#2c3856] text-white group-hover:bg-[#ff9c00] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-[#2c3856] hidden sm:inline"><span id="style-label">Minmer</span></span>
                </button>

                <button type="button" onclick="window.toggleTraffic()" id="btn-traffic" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-orange-50 transition-colors duration-300 group">
                    <div class="p-1.5 rounded-md bg-gray-200 text-gray-500 group-hover:text-white group-hover:bg-[#ff9c00] transition-colors" id="icon-traffic-bg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-[#2c3856] hidden sm:inline">Tráfico</span>
                </button>
            </div>
        </div>

        <div id="map" class="absolute inset-0 z-0 w-full h-full"></div>

        <div class="absolute bottom-0 left-0 w-full h-[50vh] md:top-0 md:right-0 md:w-[480px] md:h-full md:left-auto bg-white/80 backdrop-blur-xl border-t md:border-t-0 md:border-l border-white/30 shadow-[-10px_0_30px_rgba(0,0,0,0.1)] overflow-y-auto transition-all duration-500 ease-out p-6 md:p-8 rounded-t-[2rem] md:rounded-none z-30">
            
            <div class="md:hidden w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-6"></div>

            <form action="{{ route('rutas.plantillas.store') }}" method="POST" id="rutaForm" class="space-y-8 pb-20 md:pb-0">
                @csrf

                <div class="space-y-4 animate-fade-in-down">
                    <a href="{{ route('rutas.plantillas.index') }}" class="group inline-flex items-center text-sm font-medium text-[#666666] hover:text-[#2c3856] transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </a>
                    <h2 class="font-['Raleway',sans-serif] font-extrabold text-2xl md:text-3xl text-[#2c3856] leading-tight">
                        Nueva Plantilla
                    </h2>
                    <div class="w-20 h-1 bg-[#ff9c00] rounded-full"></div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50/90 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg backdrop-blur-sm animate-shake" role="alert">
                        <p class="font-bold flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            Error
                        </p>
                        <ul class="mt-2 ml-7 list-disc text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-6 animate-fade-in-up delay-100">
                    <div class="relative group">
                        <input type="text" name="nombre" id="nombre" required placeholder=" " 
                               class="block px-4 py-3 w-full text-[#2b2b2b] bg-white/50 backdrop-blur-sm rounded-lg border-2 border-gray-200/50 appearance-none focus:outline-none focus:ring-0 focus:border-[#ff9c00] focus:bg-white/80 transition-all duration-300 peer" />
                        <label for="nombre" class="absolute text-sm text-[#666666] duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-transparent px-2 peer-focus:px-2 peer-focus:text-[#ff9c00] peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 left-2 pointer-events-none">
                            Nombre Ruta
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <select name="region" id="region" required
                                    class="block px-4 py-3 w-full text-[#2b2b2b] bg-white/50 backdrop-blur-sm rounded-lg border-2 border-gray-200/50 appearance-none focus:outline-none focus:ring-0 focus:border-[#ff9c00] focus:bg-white/80 transition-all duration-300">
                                <option value="" disabled selected>Región</option>
                                @foreach(['MEX', 'SJD', 'GDL', 'MTY', 'CUN', 'MIN', 'MZN', 'VER'] as $regionOption)
                                    <option value="{{ $regionOption }}" {{ old('region') == $regionOption ? 'selected' : '' }}>{{ $regionOption }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-[#666666]">
                                <svg class="fill-current h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>

                        <div class="relative">
                            <select name="tipo_ruta" id="tipo_ruta" required
                                    class="block px-4 py-3 w-full text-[#2b2b2b] bg-white/50 backdrop-blur-sm rounded-lg border-2 border-gray-200/50 appearance-none focus:outline-none focus:ring-0 focus:border-[#ff9c00] focus:bg-white/80 transition-all duration-300">
                                <option value="Entrega">Entrega</option>
                                <option value="Traslado">Traslado</option>
                                <option value="Importacion">Importación</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-[#6666666]">
                                <svg class="fill-current h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-t border-gray-200/50 my-8 animate-fade-in delay-200">

                <div class="space-y-6 animate-fade-in-up delay-300">
                    <div>
                        <h3 class="font-['Raleway',sans-serif] font-extrabold text-xl text-[#2c3856] mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-[#ff9c00]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Paradas
                        </h3>
                        <div class="relative group z-50">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-[#666666] group-focus-within:text-[#ff9c00] transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" id="autocomplete" placeholder="Buscar lugar..." 
                                   class="block w-full pl-12 pr-4 py-3 text-[#2b2b2b] bg-white/70 backdrop-blur-md rounded-full border-2 border-gray-200/50 focus:outline-none focus:ring-0 focus:border-[#ff9c00] focus:bg-white shadow-sm hover:shadow-md transition-all duration-300" />
                        </div>
                        <p class="text-xs text-[#666666] mt-3 ml-4 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Tip: Clic derecho en mapa.
                        </p>
                    </div>

                    <div id="paradas-container" class="space-y-3 min-h-[50px]"></div>
                    
                    <p id="paradas-error" class="text-red-500 text-sm font-medium mt-2 hidden animate-pulse flex items-center">
                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Min. 2 paradas.
                    </p>

                    <div id="resumen-ruta" class="mt-6 p-5 bg-gradient-to-br from-[#2c3856]/10 to-white/50 rounded-xl border border-white/40 shadow-sm hidden animate-fade-in">
                        <h4 class="font-['Raleway',sans-serif] font-bold text-[#2c3856] mb-1">Resumen</h4>
                        <div class="flex items-baseline">
                            <span class="text-sm text-[#666666] mr-2">Distancia:</span>
                            <span id="distancia-total" class="text-2xl font-extrabold text-[#ff9c00] tracking-tight">0 km</span>
                        </div>
                    </div>

                    <div id="paradas-hidden-inputs"></div>
                    <input type="hidden" name="distancia_total_km" id="distancia-total-input" value="0">

                    <div class="pt-4">
                        <button type="button" onclick="validarYEnviarFormulario()" 
                                class="group relative w-full flex justify-center items-center py-4 px-6 border border-transparent font-['Raleway',sans-serif] font-extrabold rounded-xl text-white bg-gradient-to-r from-[#ff9c00] to-orange-500 hover:from-orange-500 hover:to-[#ff9c00] shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] transition-all duration-300 overflow-hidden">
                            <span class="absolute inset-0 w-full h-full transition-all duration-300 ease-out transform translate-x-full bg-white/20 group-hover:translate-x-0"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 transform group-hover:scale-110 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            <span class="relative">Guardar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes fadeInDown {
            from { opacity: 0; transform: translate3d(0, -20px, 0); }
            to { opacity: 1; transform: translate3d(0, 0, 0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translate3d(0, 20px, 0); }
            to { opacity: 1; transform: translate3d(0, 0, 0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .animate-fade-in-down { animation: fadeInDown 0.6s ease-out both; }
        .animate-fade-in-up { animation: fadeInUp 0.6s ease-out both; }
        .animate-fade-in { animation: fadeIn 0.8s ease-out both; }
        .animate-shake { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
        
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }

        .grab-cursor { cursor: grab; }
        .grab-cursor:active { cursor: grabbing; }
        
        .sortable-ghost { opacity: 0.4; background-color: #eef2ff; border: 2px dashed #ff9c00; }
        .sortable-chosen { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
    </style>

    <script type="module">
        import autoAnimate from 'https://cdn.jsdelivr.net/npm/@formkit/auto-animate/index.min.js';
        window.autoAnimate = autoAnimate;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        (function() { 
            var mapaRuta, directionsService, directionsRenderer;
            var trafficLayer;
            var markers = [];
            var paradas = [];
            var defaultLocation = { lat: 23.6345, lng: -102.5528 };
            var isMinmerStyle = true;
            var isTrafficOn = false;

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

            window.initMap = function() {
                mapaRuta = new google.maps.Map(document.getElementById("map"), {
                    zoom: 5,
                    center: defaultLocation,
                    mapTypeId: 'roadmap',
                    gestureHandling: 'greedy', 
                    disableDefaultUI: false,
                    zoomControl: true,
                    zoomControlOptions: { position: google.maps.ControlPosition.LEFT_CENTER },
                    mapTypeControl: true,
                    mapTypeControlOptions: {
                        style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                        position: google.maps.ControlPosition.TOP_LEFT
                    },
                    streetViewControl: true,
                    streetViewControlOptions: { position: google.maps.ControlPosition.LEFT_CENTER },
                    fullscreenControl: false, 
                    styles: minmerStyles
                });

                directionsService = new google.maps.DirectionsService();
                directionsRenderer = new google.maps.DirectionsRenderer({ 
                    map: mapaRuta,
                    suppressMarkers: true, 
                    polylineOptions: { strokeColor: "#ff9c00", strokeWeight: 5, strokeOpacity: 0.8 }
                });

                trafficLayer = new google.maps.TrafficLayer();

                setupAutocomplete();

                mapaRuta.addListener('rightclick', (event) => {
                    geocodePosition(event.latLng);
                });

                setupSortable();
            };

            window.toggleMapStyle = function() {
                isMinmerStyle = !isMinmerStyle;
                const label = document.getElementById('style-label');
                
                if (isMinmerStyle) {
                    mapaRuta.setOptions({ styles: minmerStyles });
                    label.innerText = "Minmer";
                } else {
                    mapaRuta.setOptions({ styles: null }); 
                    label.innerText = "Natural";
                }
            };

            window.toggleTraffic = function() {
                isTrafficOn = !isTrafficOn;
                const bgIcon = document.getElementById('icon-traffic-bg');

                if (isTrafficOn) {
                    trafficLayer.setMap(mapaRuta);
                    bgIcon.classList.remove('bg-gray-200', 'text-gray-500');
                    bgIcon.classList.add('bg-[#ff9c00]', 'text-white');
                } else {
                    trafficLayer.setMap(null);
                    bgIcon.classList.remove('bg-[#ff9c00]', 'text-white');
                    bgIcon.classList.add('bg-gray-200', 'text-gray-500');
                }
            };

            function setupAutocomplete() {
                const input = document.getElementById('autocomplete');
                if (!google.maps.places || !input) return;
                const autocomplete = new google.maps.places.Autocomplete(input);
                autocomplete.bindTo('bounds', mapaRuta);
                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    if (!place.geometry || !place.geometry.location) return;
                    
                    const lugarNombre = place.name || place.formatted_address || "Parada sin nombre";
                    
                    window.agregarParada(place.geometry.location, place.formatted_address || place.name, lugarNombre);
                    input.value = ''; 
                    mapaRuta.panTo(place.geometry.location);
                    mapaRuta.setZoom(15);
                });
            }

            function geocodePosition(latLng) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: latLng }, (results, status) => {
                    if (status === "OK" && results[0]) {
                        window.agregarParada(latLng, results[0].formatted_address, results[0].formatted_address);
                    } else {
                        const coords = `Coordenadas: ${latLng.toUrlValue(4)}`;
                        window.agregarParada(latLng, coords, coords);
                    }
                });
            }

            function setupSortable() {
                const el = document.getElementById('paradas-container');
                if(el) {
                    Sortable.create(el, {
                        animation: 300,
                        handle: '.drag-handle',
                        ghostClass: 'sortable-ghost',
                        onEnd: function () { actualizarOrdenParadas(); }
                    });
                    if (typeof window.autoAnimate === 'function') window.autoAnimate(el);
                }
            }

            window.agregarParada = function(location, address, nombrePersonalizado) {
                document.getElementById('paradas-error').classList.add('hidden');
                
                const index = paradas.length;
                const marker = new google.maps.Marker({
                    position: location,
                    map: mapaRuta,
                    icon: 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' + (index + 1) + '|ff9c00|ffffff',
                    animation: google.maps.Animation.DROP
                });
                markers.push(marker);

                const nombreFinal = nombrePersonalizado || `Parada ${index + 1}`;

                const paradaData = { lat: location.lat(), lng: location.lng(), address: address, nombre: nombreFinal };
                paradas.push(paradaData);

                const paradaElement = document.createElement('div');
                paradaElement.className = `group flex items-start p-3 bg-white/80 backdrop-blur-sm rounded-xl border border-white/50 shadow-sm hover:shadow-md transition-all duration-300 parada-item`;
                paradaElement.dataset.index = index;
                
                paradaElement.innerHTML = `
                    <div class="drag-handle grab-cursor mt-1 p-2 mr-2 text-[#666666] hover:text-[#ff9c00] transition-colors rounded-full hover:bg-orange-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                         <div class="flex items-center justify-between mb-1">
                             <span class="text-[10px] font-bold text-[#ff9c00] uppercase tracking-wider bg-orange-50 px-2 py-0.5 rounded-full">Parada <span class="numero-parada">${index + 1}</span></span>
                        </div>
                        <input type="text" 
                               value="${nombreFinal}" 
                               oninput="window.actualizarNombreParada(${index}, this.value)"
                               class="parada-nombre-input block w-full bg-transparent border-0 border-b border-transparent hover:border-gray-300 focus:border-[#ff9c00] focus:ring-0 p-0 text-sm font-bold text-[#2c3856] placeholder-gray-400 transition-colors duration-200" 
                               placeholder="Nombre de la parada" />
                        <p class="text-[11px] text-[#666666] truncate mt-1" title="${address}">${address}</p>
                    </div>
                    <button type="button" onclick="eliminarParada(${index})" class="mt-1 p-2 ml-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-all duration-300 opacity-60 group-hover:opacity-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-3a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                `;
                document.getElementById('paradas-container').appendChild(paradaElement);
                actualizarRuta();
            };

            window.actualizarNombreParada = function(index, nuevoNombre) {
                if (paradas[index]) {
                    paradas[index].nombre = nuevoNombre;
                }
            };

            window.eliminarParada = function(indexToRemove) {
                if(markers[indexToRemove]) markers[indexToRemove].setMap(null); 
                
                markers.splice(indexToRemove, 1);
                paradas.splice(indexToRemove, 1);

                const container = document.getElementById('paradas-container');
                const itemToRemove = container.querySelector(`[data-index='${indexToRemove}']`);
                
                if (itemToRemove) {
                    itemToRemove.remove();
                }
                
                Array.from(container.children).forEach((child, newIndex) => {
                    child.dataset.index = newIndex;
                    child.querySelector('.numero-parada').textContent = newIndex + 1;
                    child.querySelector('button').setAttribute('onclick', `eliminarParada(${newIndex})`);
                    child.querySelector('.parada-nombre-input').setAttribute('oninput', `window.actualizarNombreParada(${newIndex}, this.value)`);
                });

                markers.forEach((marker, idx) => {
                    marker.setIcon('https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' + (idx + 1) + '|ff9c00|ffffff');
                });

                actualizarRuta();
            };

            window.validarYEnviarFormulario = function() {
                const errorElement = document.getElementById('paradas-error');
                if (paradas.length < 2) {
                    errorElement.classList.remove('hidden');
                    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                errorElement.classList.add('hidden');
                
                const btn = document.querySelector('button[onclick="validarYEnviarFormulario()"]');
                if(btn) {
                    btn.disabled = true;
                    btn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Guardando...
                    `;
                }
                document.getElementById('rutaForm').submit();
            };

            function actualizarOrdenParadas() {
                const container = document.getElementById('paradas-container');
                const newParadas = [];
                const newMarkers = [];

                Array.from(container.children).forEach((child, newIndex) => {
                    const oldIndex = parseInt(child.dataset.index);
                    
                    if (paradas[oldIndex]) {
                        const inputVal = child.querySelector('.parada-nombre-input').value;
                        paradas[oldIndex].nombre = inputVal;
                        
                        newParadas.push(paradas[oldIndex]);
                        newMarkers.push(markers[oldIndex]);
                    }
                    
                    child.dataset.index = newIndex;
                    child.querySelector('.numero-parada').textContent = newIndex + 1;
                    child.querySelector('button').setAttribute('onclick', `eliminarParada(${newIndex})`);
                    child.querySelector('.parada-nombre-input').setAttribute('oninput', `window.actualizarNombreParada(${newIndex}, this.value)`);
                });

                paradas = newParadas;
                markers = newMarkers;

                markers.forEach((marker, idx) => {
                    marker.setIcon('https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' + (idx + 1) + '|ff9c00|ffffff');
                    marker.setAnimation(google.maps.Animation.DROP);
                });
                actualizarRuta();
            }

            function actualizarRuta() {
                const hiddenInputsContainer = document.getElementById('paradas-hidden-inputs');
                hiddenInputsContainer.innerHTML = '';
                const resumenContainer = document.getElementById('resumen-ruta');

                if (paradas.length < 2) {
                    directionsRenderer.setDirections({routes: []}); 
                    document.getElementById('distancia-total').textContent = '0 km';
                    document.getElementById('distancia-total-input').value = 0;
                    resumenContainer.classList.add('hidden');
                    return;
                }

                resumenContainer.classList.remove('hidden');

                const waypoints = paradas.slice(1, -1).map(p => ({ location: { lat: p.lat, lng: p.lng }, stopover: true }));
                const origin = { lat: paradas[0].lat, lng: paradas[0].lng };
                const destination = { lat: paradas[paradas.length - 1].lat, lng: paradas[paradas.length - 1].lng };

                directionsService.route({
                    origin: origin,
                    destination: destination,
                    waypoints: waypoints,
                    optimizeWaypoints: false, 
                    travelMode: google.maps.TravelMode.DRIVING,
                }, (response, status) => {
                    if (status === "OK" && response.routes.length > 0) {
                        directionsRenderer.setDirections(response);
                        
                        let totalDistance = 0;
                        response.routes[0].legs.forEach(leg => {
                            totalDistance += leg.distance.value;
                        });
                        const totalKm = (totalDistance / 1000).toFixed(2);
                        document.getElementById('distancia-total').textContent = `${totalKm} km`;
                        document.getElementById('distancia-total-input').value = totalKm;

                        paradas.forEach((parada, index) => {
                            hiddenInputsContainer.innerHTML += `
                                <input type="hidden" name="paradas[${index}][nombre]" value="${parada.nombre}">
                                <input type="hidden" name="paradas[${index}][direccion]" value="${parada.address}">
                                <input type="hidden" name="paradas[${index}][latitud]" value="${parada.lat}">
                                <input type="hidden" name="paradas[${index}][longitud]" value="${parada.lng}">
                                <input type="hidden" name="paradas[${index}][orden]" value="${index + 1}">
                            `;
                        });
                    }
                });
            }
        })(); 
    </script>
    
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places,drawing,geometry&callback=initMap&loading=async" async defer></script>

</x-app-layout>