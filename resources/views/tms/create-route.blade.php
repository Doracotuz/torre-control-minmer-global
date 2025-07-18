@extends('layouts.app')

@section('content')
{{-- Leaflet CSS y JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
{{-- Bootstrap 5.3.3 CSS (respetando tu versión) --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<style>
    #map {
        height: 60vh;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        z-index: 1;
    }
    .control-panel {
        background-color: #fff;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .stop-list-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 10px;
        background-color: #f9f9f9;
    }
    .stop-list-item .drag-handle { cursor: move; margin-right: 15px; color: #666; }
    
    .stop-list-item .stop-name-input {
        flex-grow: 1;
        border: none;
        background-color: transparent;
        padding: 4px 8px;
        border-radius: 5px;
        transition: background-color 0.2s ease-in-out;
        font-weight: 500;
        color: #333;
    }
    .stop-list-item .stop-name-input:focus {
        background-color: #e9ecef;
        outline: none;
        box-shadow: 0 0 0 2px rgba(44, 56, 86, 0.2);
    }

    .stop-list-item .remove-stop-btn { color: #dc3545; cursor: pointer; margin-left: 10px; }
    
    #saveRouteBtn {
        background-color: #2c3856; color: #ffffff; font-weight: bold;
        padding: 12px 20px; border-radius: 8px; border: none;
        transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(44, 56, 86, 0.3);
        cursor: pointer; width: 100%; display: flex;
        justify-content: center; align-items: center;
    }
    #saveRouteBtn:hover {
        background-color: #ff9c00; transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(255, 156, 0, 0.4);
    }
    #saveRouteBtn:active {
        transform: translateY(0);
        box-shadow: 0 4px 10px rgba(44, 56, 86, 0.3);
    }
    
    .modal-header-custom { background-color: #2c3856; color: #fff; }
    .btn-custom-secondary { background-color: #ff9c00; color: #fff; border: none; }
    .btn-custom-secondary:hover { background-color: #e68a00; color: #fff; }
    .btn-custom-primary { background-color: #2c3856; color: #fff; border: none; }
    .btn-custom-primary:hover { background-color: #1a2233; color: #fff; }

</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Crear Nueva Ruta</h1>
        </div>
    </div>

    <div class="row">
        <!-- Panel de Control (Izquierda) -->
        <div class="col-lg-4 mb-4">
            <div class="control-panel">
                <h4>Detalles de la Ruta</h4>
                <div class="form-group mb-3">
                    <label for="routeName" class="form-label">Nombre de la Ruta</label>
                    <input type="text" class="form-control" id="routeName" placeholder="Ej: Entrega Zona Norte">
                </div>
                <hr>
                <h4>Paradas</h4>
                <p class="text-muted small">Haz clic en el mapa para añadir paradas. Arrástralas para reordenar y edita sus nombres.</p>
                <div id="stopsList"></div>
                <hr>
                <h4>Resumen</h4>
                <p><strong>Distancia Total:</strong> <span id="totalDistance">0.00</span> km</p>
                <p><strong>Duración Estimada:</strong> <span id="totalDuration">0</span> minutos</p>
                
                <button id="saveRouteBtn" class="btn">
                    <i class="fas fa-save me-2"></i>Guardar Ruta
                </button>
            </div>
        </div>

        <!-- Mapa (Derecha) -->
        <div class="col-lg-8">
            <div id="map"></div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="confirmationModalLabel">Ruta Creada Exitosamente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>La ruta ha sido guardada. ¿Qué deseas hacer ahora?</p>
            </div>
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
{{-- Bootstrap 5.3.3 JS (respetando tu versión) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapboxApiKey = 'pk.eyJ1IjoiZG9yYWNvdHV6IiwiYSI6ImNtZDhrMWVhdTAxMGsycHE3ODVjZTF5MjEifQ.jWhPKN41kfzZLMUqkdVobA'; // Reemplaza con tu API Key de Mapbox

    const initialCoords = [19.4326, -99.1332];
    const map = L.map('map').setView(initialCoords, 12);

    L.tileLayer(`https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=${mapboxApiKey}`, {
        attribution: '© <a href="https://www.mapbox.com/about/maps/">Mapbox</a> © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    const routingControl = L.Routing.control({
        waypoints: [],
        routeWhileDragging: true,
        show: false,
        addWaypoints: false,
        router: L.Routing.mapbox(mapboxApiKey, { profile: 'mapbox/driving-traffic' }),
        // El geocodificador de la librería ya no se usa para el clic, solo para el control de búsqueda si se quisiera añadir
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

    // ==================================================================
    // INICIO DE LA CORRECCIÓN: Lógica de clic usando Fetch a Mapbox
    // ==================================================================
    map.on('click', function(e) {
        const latlng = e.latlng;
        const stopId = Date.now();

        // 1. Añade la parada inmediatamente
        stops.push({ 
            id: stopId,
            lat: latlng.lat,
            lng: latlng.lng,
            name: 'Buscando dirección...' 
        });
        updateRoute();

        // 2. Busca la dirección usando la API de Mapbox
        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${latlng.lng},${latlng.lat}.json?access_token=${mapboxApiKey}&types=address,poi&limit=1`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const stopToUpdate = stops.find(s => s.id === stopId);
                if (stopToUpdate) {
                    let finalName = `Parada ${stops.indexOf(stopToUpdate) + 1}`; // Nombre de respaldo
                    if (data.features && data.features.length > 0) {
                        finalName = data.features[0].place_name; // Usa la dirección de Mapbox
                    }
                    stopToUpdate.name = finalName;
                    renderStopsList(); // Vuelve a renderizar la lista con el nombre correcto
                }
            })
            .catch(err => {
                console.error('Error de Geocodificación de Mapbox:', err);
                const stopToUpdate = stops.find(s => s.id === stopId);
                if (stopToUpdate) {
                    // Si falla, se queda con el nombre de respaldo
                    stopToUpdate.name = `Parada ${stops.indexOf(stopToUpdate) + 1}`;
                    renderStopsList();
                }
            });
    });
    // ==================================================================
    // FIN DE LA CORRECCIÓN
    // ==================================================================
    
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
            const newName = e.target.value;
            if (stops[index]) {
                stops[index].name = newName;
            }
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
        handle: '.drag-handle',
        animation: 150,
        onEnd: function (evt) {
            const [reorderedItem] = stops.splice(evt.oldIndex, 1);
            stops.splice(evt.newIndex, 0, reorderedItem);
            updateRoute();
        }
    });

    document.getElementById('saveRouteBtn').addEventListener('click', function() {
        const routeName = document.getElementById('routeName').value;
        
        if (!routeName) { alert('Por favor, asigna un nombre a la ruta.'); return; }
        if (stops.length < 2) { alert('Una ruta debe tener al menos 2 paradas (origen y destino).'); return; }
        if (!lastFoundRoute) { alert('No se ha podido calcular la ruta. Asegúrate de que los puntos son válidos.'); return; }

        const routeData = {
            name: routeName,
            polyline: JSON.stringify(lastFoundRoute.coordinates),
            distance: (lastFoundRoute.summary.totalDistance / 1000).toFixed(2),
            duration: Math.round(lastFoundRoute.summary.totalTime / 60),
            stops: stops.map(stop => ({ name: stop.name, lat: stop.lat, lng: stop.lng }))
        };

        fetch('{{ route("tms.storeRoute") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(routeData)
        })
        .then(response => {
            if (!response.ok) { return response.json().then(err => { throw new Error(err.message || 'Error del servidor') }); }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const assignBtn = document.getElementById('assignNowBtn');
                assignBtn.href = `{{ url('/tms/asignar-rutas') }}?route_id=${data.route_id}`;
                var myModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                myModal.show();
            } else {
                alert(data.message || 'Ocurrió un error al guardar.');
            }
        })
        .catch(error => {
            console.error('Error al guardar la ruta:', error);
            alert('Ocurrió un error de conexión o del servidor: ' + error.message);
        });
    });
});
</script>
@endsection
