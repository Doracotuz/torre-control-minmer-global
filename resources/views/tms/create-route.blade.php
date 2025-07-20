@extends('layouts.app')

@section('content')
{{-- Leaflet CSS y JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
{{-- Bootstrap 5.3.3 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    /* ... Tus estilos ... */
    #map { height: 60vh; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); z-index: 1; }
    .control-panel { background-color: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
    .stop-list-item { display: flex; align-items: center; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; background-color: #f9f9f9; }
    .stop-list-item .drag-handle { cursor: move; margin-right: 15px; color: #666; }
    .stop-list-item .stop-name-input { flex-grow: 1; border: none; background-color: transparent; padding: 4px 8px; border-radius: 5px; transition: background-color 0.2s ease-in-out; font-weight: 500; color: #333; }
    .stop-list-item .stop-name-input:focus { background-color: #e9ecef; outline: none; box-shadow: 0 0 0 2px rgba(44, 56, 86, 0.2); }
    .stop-list-item .remove-stop-btn { color: #dc3545; cursor: pointer; margin-left: 10px; }
    #saveRouteBtn { background-color: #2c3856; color: #ffffff; font-weight: bold; padding: 12px 20px; border-radius: 8px; border: none; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(44, 56, 86, 0.3); cursor: pointer; width: 100%; display: flex; justify-content: center; align-items: center; }
    #saveRouteBtn:hover { background-color: #ff9c00; transform: translateY(-3px); box-shadow: 0 6px 15px rgba(255, 156, 0, 0.4); }
    #saveRouteBtn:active { transform: translateY(0); box-shadow: 0 4px 10px rgba(44, 56, 86, 0.3); }
    .modal-header-custom { background-color: #2c3856; color: #fff; }
    .btn-custom-secondary { background-color: #ff9c00; color: #fff; border: none; }
    .btn-custom-secondary:hover { background-color: #e68a00; color: #fff; }
    .btn-custom-primary { background-color: #2c3856; color: #fff; border: none; }
    .btn-custom-primary:hover { background-color: #1a2233; color: #fff; }
</style>

<div class="container-fluid">
    <div class="row mb-4"><div class="col-12"><h1 class="h3 mb-0 text-gray-800">Crear Nueva Ruta</h1></div></div>
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="control-panel">
                <h4>Detalles de la Ruta</h4>
                <div class="form-group mb-3"><label for="routeName" class="form-label">Nombre de la Ruta</label><input type="text" class="form-control" id="routeName" placeholder="Ej: Entrega Zona Norte"></div>
                <hr><h4>Paradas</h4>
                <p class="text-muted small">Haz clic en el mapa o usa el buscador para añadir paradas. Arrástralas para reordenar.</p>
                <div id="stopsList"></div>
                <hr><h4>Resumen</h4>
                <p><strong>Distancia Total:</strong> <span id="totalDistance">0.00</span> km</p>
                <p><strong>Duración Estimada:</strong> <span id="totalDuration">0</span> minutos</p>
                <button id="saveRouteBtn" class="btn"><i class="fas fa-save me-2"></i>Guardar Ruta</button>
            </div>
        </div>
        <div class="col-lg-8"><div id="map"></div></div>
    </div>
</div>
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-custom"><h5 class="modal-title" id="confirmationModalLabel">Ruta Creada Exitosamente</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><p>La ruta ha sido guardada. ¿Qué deseas hacer ahora?</p></div>
            <div class="modal-footer">
                <a href="#" id="assignNowBtn" class="btn btn-custom-secondary">Asignar Ruta Ahora</a>
                <a href="{{ route('tms.createRoute') }}" class="btn btn-custom-primary">Crear Otra Ruta</a>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // -----------------------------------------------------------------
    // PASO 1: OBTENER LA API KEY (Viene del controlador)
    // -----------------------------------------------------------------
    const mapboxApiKey = '{{ $mapboxApiKey ?? '' }}';

    // Si la clave no llega, detenemos todo y mostramos un error claro.
    if (!mapboxApiKey) {
        alert('FATAL ERROR: La API Key de Mapbox no está configurada. Revisa tu archivo .env y el controlador TmsController.php');
        return;
    }
    console.log('API Key cargada correctamente en la vista.');


    // -----------------------------------------------------------------
    // PASO 2: INICIALIZAR EL MAPA
    // -----------------------------------------------------------------
    const map = L.map('map').setView([19.4326, -99.1332], 12);
    L.tileLayer(`https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=${mapboxApiKey}`, {
        attribution: '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);


    // -----------------------------------------------------------------
    // PASO 3: INICIALIZAR EL BUSCADOR (LA SOLUCIÓN DEFINITIVA)
    // -----------------------------------------------------------------
    const geocoder = L.Control.geocoder({
        placeholder: 'Buscar dirección o lugar...',
        errorMessage: 'No se encontró la ubicación.',
        defaultMarkGeocode: false, // No queremos el marcador por defecto
        
        // Esta función personalizada hace la búsqueda manualmente, evitando el error de la librería.
        geocode: function(query, cb, context) {
            const mapCenter = map.getCenter();
            const proximity = `${mapCenter.lng},${mapCenter.lat}`;
            const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${mapboxApiKey}&country=MX&limit=7&proximity=${proximity}&autocomplete=true`;
            
            console.log('Buscando en Mapbox con URL:', url); // Línea de depuración para ver la URL

            fetch(url)
            .then(response => response.json())
            .then(data => {
                const results = [];
                if (data.features) {
                    data.features.forEach(feature => {
                        results.push({
                            name: feature.place_name,
                            center: L.latLng(feature.center[1], feature.center[0]),
                            bbox: L.latLngBounds([feature.bbox[1], feature.bbox[0]], [feature.bbox[3], feature.bbox[2]])
                        });
                    });
                }
                cb.call(context, results);
            });
        }
    }).on('markgeocode', function(e) {
        const { center, name } = e.geocode;
        stops.push({ id: Date.now(), lat: center.lat, lng: center.lng, name: name });
        updateRoute();
        map.panTo(center);
    }).addTo(map);


    // -----------------------------------------------------------------
    // El resto del código (manejo de paradas, rutas, etc.)
    // -----------------------------------------------------------------
    const routingControl = L.Routing.control({
        waypoints: [],
        routeWhileDragging: true,
        show: false,
        addWaypoints: false,
        router: L.Routing.mapbox(mapboxApiKey, { profile: 'mapbox/driving-traffic' }),
    }).addTo(map);

    let stops = [];
    const stopsListElement = document.getElementById('stopsList');
    let lastFoundRoute = null;

    function updateRoute() {
        const waypoints = stops.map(stop => L.latLng(stop.lat, stop.lng));
        routingControl.setWaypoints(waypoints);
        renderStopsList();
    }

    function renderStopsList() {
        stopsListElement.innerHTML = '';
        stops.forEach((stop, index) => {
            const item = document.createElement('div');
            item.className = 'stop-list-item';
            item.dataset.id = stop.id;
            item.innerHTML = `
                <i class="fas fa-grip-vertical drag-handle"></i>
                <input type="text" class="form-control-plaintext stop-name-input" value="${stop.name}" data-index="${index}" title="Haz clic para editar el nombre">
                <i class="fas fa-times remove-stop-btn" data-index="${index}"></i>
            `;
            stopsListElement.appendChild(item);
        });
    }

    map.on('click', function(e) {
        const latlng = e.latlng;
        const stopId = Date.now();
        stops.push({ id: stopId, lat: latlng.lat, lng: latlng.lng, name: 'Buscando dirección...' });
        updateRoute();
        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${latlng.lng},${latlng.lat}.json?access_token=${mapboxApiKey}&types=address,poi&limit=1`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const stopToUpdate = stops.find(s => s.id === stopId);
                if (stopToUpdate) {
                    let finalName = `Parada ${stops.indexOf(stopToUpdate) + 1}`;
                    if (data.features && data.features.length > 0) { finalName = data.features[0].place_name; }
                    stopToUpdate.name = finalName;
                    renderStopsList();
                }
            }).catch(err => {
                console.error('Error de Geocodificación de Mapbox:', err);
            });
    });

    routingControl.on('routesfound', function(e) {
        if (e.routes.length > 0) {
            lastFoundRoute = e.routes[0];
            const summary = lastFoundRoute.summary;
            document.getElementById('totalDistance').textContent = (summary.totalDistance / 1000).toFixed(2);
            document.getElementById('totalDuration').textContent = Math.round(summary.totalTime / 60);
        }
    });

    stopsListElement.addEventListener('input', function(e) {
        if (e.target.classList.contains('stop-name-input')) {
            const index = parseInt(e.target.dataset.index, 10);
            if (stops[index]) { stops[index].name = e.target.value; }
        }
    });

    stopsListElement.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-stop-btn')) {
            const index = parseInt(e.target.dataset.index, 10);
            stops.splice(index, 1);
            updateRoute();
        }
    });

    new Sortable(stopsListElement, {
        handle: '.drag-handle', animation: 150,
        onEnd: function (evt) {
            const [reorderedItem] = stops.splice(evt.oldIndex, 1);
            stops.splice(evt.newIndex, 0, reorderedItem);
            updateRoute();
        }
    });

    document.getElementById('saveRouteBtn').addEventListener('click', function() {
        // ... (código para guardar la ruta, sin cambios)
        const routeName = document.getElementById('routeName').value;
        if (!routeName) { alert('Por favor, asigna un nombre a la ruta.'); return; }
        if (stops.length < 2) { alert('Una ruta debe tener al menos 2 paradas (origen y destino).'); return; }
        if (!lastFoundRoute) { alert('No se ha podido calcular la ruta. Asegúrate de que los puntos son válidos.'); return; }
        const routeData = { name: routeName, polyline: JSON.stringify(lastFoundRoute.coordinates), distance: (lastFoundRoute.summary.totalDistance / 1000).toFixed(2), duration: Math.round(lastFoundRoute.summary.totalTime / 60), stops: stops.map(stop => ({ name: stop.name, lat: stop.lat, lng: stop.lng })) };
        fetch('{{ route("tms.storeRoute") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(routeData)
        }).then(response => {
            if (!response.ok) { return response.json().then(err => { throw new Error(err.message || 'Error del servidor') }); }
            return response.json();
        }).then(data => {
            if (data.success) {
                const assignBtn = document.getElementById('assignNowBtn');
                assignBtn.href = `{{ url('/tms/asignar-rutas') }}?route_id=${data.route_id}`;
                var myModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                myModal.show();
            } else {
                alert(data.message || 'Ocurrió un error al guardar.');
            }
        }).catch(error => {
            console.error('Error al guardar la ruta:', error);
            alert('Ocurrió un error de conexión o del servidor: ' + error.message);
        });
    });
});
</script>
@endsection