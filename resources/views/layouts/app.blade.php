<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script> -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
         
        <style>
            .nav-link-custom {
                position: relative;
                display: flex;
                align-items: center;
                padding: 12px 16px;
                border-radius: 8px;
                font-weight: 500;
                color: #e5e7eb;
                transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
            }

            .nav-link-custom::before {
                content: '';
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                height: 0;
                width: 4px;
                background-color: #ff9c00;
                border-radius: 2px;
                transition: height 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }

            .nav-link-custom:hover:not(.active-link) {
                background-color: rgba(255, 255, 255, 0.05);
                color: #ffffff;
            }
            .nav-link-custom:hover:not(.active-link)::before {
                height: 60%;
            }

            .nav-link-custom.active-link {
                background-color: #ff9c00;
                color: #ffffff;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(255, 156, 0, 0.2);
            }
            .nav-link-custom.active-link::before {
                height: 100%;
            }


            .nav-link-custom .nav-icon {
                flex-shrink: 0;
                width: 1.25rem;
                height: 1.25rem;
                margin-right: 12px;
                transition: transform 0.3s ease;
            }

            .nav-link-custom:hover .nav-text {
                transform: translateX(4px);
            }


            .logo-container {
                transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.4s ease;
                border-radius: 8px;
            }
            .logo-container:hover {
                transform: scale(1.03);
                background-color: rgba(255, 255, 255, 0.03);
            }
            .logo-container .logo-text {
                font-family: 'Raleway', sans-serif;
            }
            .logo-container .logo-subtitle {
                font-family: 'Montserrat', sans-serif;
            }


            .dropdown-toggle {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
                padding: 10px 16px;
                font-family: 'Raleway', sans-serif;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .05em;
                color: #a0aec0;
                border-radius: 8px;
                transition: background-color 0.3s ease;
            }
            .dropdown-toggle:hover {
                background-color: rgba(255, 255, 255, 0.05);
                color: #cbd5e0;
            }
            .dropdown-toggle .chevron-icon {
                transition: transform 0.3s ease-in-out;
            }

            .sticky-sidebar {
                position: sticky;
                top: 0;
                min-height: 100vh;
                align-self: flex-start;
            }


            [x-cloak] { display: none !important; }
        </style>
            <script>
        // Variables globales
        let map, directionsService, directionsRenderer, autocomplete;
        let paradas = [];
        let indexMap, monitoreoMap, activeRenderers = {};
        const routeColors = ['#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#A133FF', '#33FFA1'];

        document.addEventListener("turbo:before-visit", () => {
            paradas = [];
            activeRenderers = {};
        });

        // ========= CREAR/EDITAR =========
        window.initMap = function() {
            if (!document.getElementById('map')) return;
            if (window.initialParadas) { paradas = window.initialParadas; delete window.initialParadas; } else { paradas = []; }
            map = new google.maps.Map(document.getElementById("map"), { center: { lat: 19.4326, lng: -99.1332 }, zoom: 12, mapTypeControl: false, streetViewControl: false, gestureHandling: 'greedy',});
            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({ map: map, draggable: true, markerOptions: { draggable: true } });
            google.maps.event.addListener(directionsRenderer, 'directions_changed', () => { const result = directionsRenderer.getDirections(); if (result && result.routes.length > 0) { const newRoute = result.routes[0]; actualizarDistancia(newRoute); const newParadas = newRoute.legs.map((leg, index) => ({ lat: leg.start_location.lat(), lng: leg.start_location.lng(), nombre: (paradas[index] && paradas[index].nombre.includes("Punto")) ? `Punto ${index + 1}` : leg.start_address.split(',')[0] })); const lastLeg = newRoute.legs[newRoute.legs.length - 1]; newParadas.push({ lat: lastLeg.end_location.lat(), lng: lastLeg.end_location.lng(), nombre: (paradas[newParadas.length] && paradas[newParadas.length].nombre.includes("Punto")) ? `Punto ${newParadas.length + 1}` : lastLeg.end_address.split(',')[0] }); paradas = newParadas; actualizarVistaParadas(); } });
            const input = document.getElementById("autocomplete");
            autocomplete = new google.maps.places.Autocomplete(input, { fields: ["name", "geometry.location"] });
            autocomplete.addListener("place_changed", () => { const place = autocomplete.getPlace(); if (!place.geometry || !place.geometry.location) return; agregarParada(place.geometry.location, place.name); input.value = ''; });
            map.addListener('rightclick', (event) => agregarParada(event.latLng, "Punto Personalizado"));
            actualizarVistaParadas();
            trazarRuta();
        };

        // ========= MAPA DEL INDEX =========
        window.initIndexMap = function() {
            if (!document.getElementById('map-panel')) return;
            indexMap = new google.maps.Map(document.getElementById("map-panel"), { center: { lat: 19.4326, lng: -99.1332 }, zoom: 10, mapTypeControl: false, gestureHandling: 'greedy', });
            directionsService = new google.maps.DirectionsService();
            document.querySelectorAll('.route-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', (event) => {
                    const rutaId = event.target.dataset.rutaId;
                    if (event.target.checked) { drawRoute(rutaId); } else { removeRoute(rutaId); }
                });
            });
        };

        // ========= MAPA DE MONITOREO =========
        window.initMonitoreoMap = function() {
            if (!document.getElementById('monitoreo-map')) return;
            monitoreoMap = new google.maps.Map(document.getElementById("monitoreo-map"), {
                center: { lat: 23.6345, lng: -102.5528 },
                zoom: 5,
                mapTypeControl: false,
                gestureHandling: 'greedy',
            });
            directionsService = new google.maps.DirectionsService();
        };

        // ========= DIBUJAR EN MONITOREO =========
        function drawMonitoreoRoute(guiaId) {
            const guiaData = window.guiasData[guiaId];
            if (!guiaData) return;

            activeRenderers[guiaId] = { renderer: null, markers: [], infoWindow: new google.maps.InfoWindow() };
            
            // Se dibuja el trazo de la ruta
            const paradas = guiaData.paradas;
            if (paradas.length >= 2) {
                const request = {
                    origin: {lat: paradas[0].lat, lng: paradas[0].lng},
                    destination: {lat: paradas[paradas.length - 1].lat, lng: paradas[paradas.length - 1].lng},
                    waypoints: paradas.slice(1, -1).map(p => ({ location: {lat: p.lat, lng: p.lng}, stopover: true })),
                    travelMode: 'DRIVING'
                };
                directionsService.route(request, (result, status) => {
                    if (status == 'OK') {
                        const color = routeColors[guiaId % routeColors.length];
                        const renderer = new google.maps.DirectionsRenderer({ 
                            map: monitoreoMap,
                            directions: result,
                            suppressMarkers: true,
                            polylineOptions: { strokeColor: color, strokeWeight: 5, strokeOpacity: 0.7 } 
                        });
                        activeRenderers[guiaId].renderer = renderer;
                    }
                });
            }

            paradas.forEach((parada, index) => {
                const stopMarker = new google.maps.Marker({
                    position: { lat: parada.lat, lng: parada.lng },
                    map: monitoreoMap,
                    label: {
                        text: `${index + 1}`, // Etiqueta con el número de la parada (1, 2, 3...)
                        color: "white",
                        fontSize: "12px",
                        fontWeight: "bold"
                    },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 12,
                        fillColor: "#2c3856", // Azul oscuro de tu paleta
                        fillOpacity: 1,
                        strokeWeight: 2,
                        strokeColor: "white"
                    },
                    title: `Parada ${index + 1}: ${parada.nombre_lugar}`
                });

                // Añadimos un InfoWindow para mostrar el nombre al hacer clic
                stopMarker.addListener("click", () => {
                    const contentString = `<div style="font-family: Montserrat, sans-serif; padding: 5px;">
                                            <p style="font-weight: 600; color: #2c3856;">Parada ${index + 1}</p>
                                            <p>${parada.nombre_lugar}</p>
                                        </div>`;
                    activeRenderers[guiaId].infoWindow.setContent(contentString);
                    activeRenderers[guiaId].infoWindow.open({ anchor: stopMarker, map: monitoreoMap });
                });

                // Guardamos el marcador para poder borrarlo después
                activeRenderers[guiaId].markers.push(stopMarker);
            });

            // Se dibujan marcadores de eventos con iconos SVG
            guiaData.eventos.forEach(evento => {
                // SVG Paths
                const icons = {
                    'Factura Entregada': { path: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z', fillColor: '#28a745', fillOpacity: 1, strokeWeight: 0, scale: 1.2 },
                    'Factura no entregada': { path: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z', fillColor: '#dc3545', fillOpacity: 1, strokeWeight: 0, scale: 1.2 },
                    'Sanitario': { path: 'M14 11v1h-4v-1c0-1.1.9-2 2-2s2 .9 2 2zM9.5 11c0-1.38 1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5V12h-5v-.5C9.5 11.22 9.5 11 9.5 11zM20 12v-1.5c0-1.38-1.12-2.5-2.5-2.5S15 9.12 15 10.5V12h5zM4 12v-1.5C4 9.12 5.12 8 6.5 8S9 9.12 9 10.5V12H4zm15 2H5c-1.1 0-2 .9-2 2v2h18v-2c0-1.1-.9-2-2-2z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Alimentos': { path: 'M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 5z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.8 },
                    'Combustible': { path: 'M18 6.09C17.83 6.03 17.66 6 17.5 6h-11C6.22 6 6 6.22 6 6.5v9C6 15.78 6.22 16 6.5 16h11c.28 0 .5-.22.5-.5V7l-4-1h3.5zM12 4c0-1.1-.9-2-2-2s-2 .9-2 2v1h4V4z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Pernocta': { path: 'M21.5 10.5c-1.1 0-2 .9-2 2v3H4.5v-3c0-1.1-.9-2-2-2s-2 .9-2 2v5h2c0 1.66 1.34 3 3 3s3-1.34 3-3h8c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5c0-1.1-.9-2-2-2zM7.5 10c.83 0 1.5-.67 1.5-1.5S8.33 7 7.5 7 6 7.67 6 8.5 6.67 10 7.5 10zm9 0c.83 0 1.5-.67 1.5-1.5S17.33 7 16.5 7 15 7.67 15 8.5s.67 1.5 1.5 1.5z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Llegada a carga': { path: 'M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zM18 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z', fillColor: '#ffc107', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Fin de carga': { path: 'M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zM18 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z', fillColor: '#28a745', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'En ruta': { path: 'M22.43 10.59l-9.01-9.01c-.75-.75-2.07-.75-2.82 0l-9.01 9.01c-.75.75-.75 2.07 0 2.82l9.01 9.01c.75.75 2.07.75 2.82 0l9.01-9.01c.76-.75.76-2.07.01-2.82zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Llegada a cliente': { path: 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z', fillColor: '#28a745', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Proceso de entrega': { path: 'M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Rechazo': { path: 'M19.83 9.17l-1-1-1.42 1.42-1.41-1.41-1-1-1.42 1.42-1.41-1.41-1-1L10 8.17 8.59 6.76l-1 1 1.42 1.42-1.42 1.41 1 1 1.41-1.41 1.42 1.42 1-1 1.41 1.41 1.42-1.42 1-1-1.42-1.41 1.42-1.42zM3 21.5h12V20H3v1.5zM4.44 19l.83-2H14v-1H5.83l-.83-2H14v-1H6.83l-.83-2H14v-1H7.83l-.83-2H14V9H8.83l-.83-2H14V6H3l1.44 3z', fillColor: '#dc3545', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Percance': { path: 'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z', fillColor: '#dc3545', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'default': { path: google.maps.SymbolPath.CIRCLE, fillColor: '#6c757d', fillOpacity: 1, strokeColor: '#fff', scale: 6, strokeWeight: 1 }
                };

                const marker = new google.maps.Marker({
                    position: {lat: evento.lat, lng: evento.lng},
                    map: monitoreoMap,
                    title: evento.subtipo,
                    icon: icons[evento.subtipo] || icons['default']
                });

                let facturaAfectadaHtml = '';
                if (evento.tipo === 'Entrega' && evento.factura_id) {
                    // factura en los datos de la guía
                    const factura = guiaData.facturas.find(f => f.id === evento.factura_id);
                    if (factura) {
                        facturaAfectadaHtml = `<p style="font-size: 12px; color: #666; margin: 0 0 8px 0;"><strong>Factura:</strong> ${factura.numero_factura}</p>`;
                    }
                }

                const contentString = `
                    <div style="font-family: Montserrat, sans-serif; max-width: 250px; padding: 5px;">
                        <h4 style="font-weight: 700; color: #2c3856; margin-bottom: 5px;">${evento.subtipo}</h4>
                        ${facturaAfectadaHtml}
                        <p style="font-size: 13px; margin: 0 0 8px 0;">${evento.nota || 'Sin notas.'}</p>
                        <p style="font-size: 12px; color: #666; margin: 0 0 8px 0;"><strong>Fecha:</strong> ${evento.fecha_evento}</p>
                        ${evento.url_evidencia ? `<div><a href="${evento.url_evidencia}" target="_blank" style="color: #007bff; font-weight: 600;">Ver Evidencia</a></div>` : ''}
                    </div>`;
                
                marker.addListener("click", () => {
                    activeRenderers[guiaId].infoWindow.setContent(contentString);
                    activeRenderers[guiaId].infoWindow.open({ anchor: marker, map: monitoreoMap });
                });
                activeRenderers[guiaId].markers.push(marker);
            });
        }

            // Lógica para remover rutas del mapa
            function removeMonitoreoRoute(guiaId) {
                if (activeRenderers[guiaId]) {
                    if (activeRenderers[guiaId].renderer) {
                        activeRenderers[guiaId].renderer.setMap(null);
                    }
                    activeRenderers[guiaId].markers.forEach(marker => marker.setMap(null));
                    if(activeRenderers[guiaId].infoWindow) {
                        activeRenderers[guiaId].infoWindow.close();
                    }
                    delete activeRenderers[guiaId];
                }
            }
        
        // ========= ALPINE.JS PARA MONITOREO =========
document.addEventListener('alpine:init', () => {
    Alpine.data('monitoringManager', () => ({
        // El array con los IDs de las guías seleccionadas. Es la "única fuente de verdad".
        selectedGuias: JSON.parse(sessionStorage.getItem('selectedGuias')) || [],

        
        // Modal de eventos
        isEventModalOpen: false,
        isDetailsModalOpen: false,
        selectedGuia: null,
        
        evento: { tipo: 'Entrega', subtipo: 'Factura Entregada', lat: '', lng: '' },
        eventSubtypes: {
            'Entrega': ['Factura Entregada', 'Factura no entregada'],
            'Notificacion': ['Sanitario', 'Alimentos', 'Combustible', 'Pernocta', 'Percance'],
            'Incidencias': [
                'Rechazo', 'Percance', 'Cambios de datos de unidad', 'Datos Incorrectos', 
                'Datos Incompletos', 'Cambio de dirección de entrega', 'Capacidad de unidad errónea', 
                'Carga Tardía', 'Daños al cliente', 'Datos incompletos en planeación', 
                'Desvío de ruta', 'Entrega en dirección errónea', 'Extravío del producto', 
                'Falla mecánica', 'Falta de maniobristas', 'Falta de evidencia', 
                'Incidencia con transito', 'Ingreso de unidades a resguardo', 'Llegada tardía de custodia', 
                'Mercancía robada', 'No comparten datos de unidad', 'No cuenta con herramientas de embarque', 
                'No cuenta con herramientas de entrega', 'No cumple con capacidad requerida', 
                'No cumple con solicitud de unidad', 'No envía estatus', 'No envía evidencias de entrega', 
                'No llega a tiempo a embarque', 'No llega a tiempo de entrega', 'No lleva gastos', 
                'No lleva combustible', 'No presenta checklist', 'No regresa producto', 
                'No reporta incidencias en tiempo', 'No respeta especificaciones del cliente', 
                'No respeta instrucciones de custodia', 'No valido carga', 'No valido documentos de entrega', 
                'Salió sin custodia', 'Solicitud de unidades sin antelación', 'Transporte accidentado'
            ]
            
        },
        
        // Cuando carga en la página.
            init() {
                this.selectedGuias = JSON.parse(sessionStorage.getItem('selectedGuias')) || [];
                

                if (monitoreoMap) {
                    monitoreoMap.addListener('rightclick', (event) => {
                        this.handleMapRightClick(event.latLng);
                    });
                }

                this.$watch('selectedGuias', (newSelection, oldSelection) => {
                    sessionStorage.setItem('selectedGuias', JSON.stringify(newSelection));
                    let toAdd = newSelection.filter(id => !oldSelection.includes(id));
                    let toRemove = oldSelection.filter(id => !newSelection.includes(id));
                    toAdd.forEach(id => drawMonitoreoRoute(id));
                    toRemove.forEach(id => removeMonitoreoRoute(id));
                });
                this.selectedGuias.forEach(id => drawMonitoreoRoute(id));
            },
                updateSelection(checkbox, guiaId) {
                    guiaId = String(guiaId);
                    if (checkbox.checked) {
                      this.selectedGuias = [...new Set([...this.selectedGuias, guiaId])];
                    } else {

                        this.selectedGuias = this.selectedGuias.filter(id => id !== guiaId);
                    }
                },

                deselectAll() {
                    this.selectedGuias = [];
                },
                openEventModal() {
                    if (this.selectedGuias.length !== 1) {
                        alert("Por favor, selecciona solo una guía para registrar un evento.");
                        return;
                    }
                    
                    const guiaId = this.selectedGuias[0];
                    
                    this.selectedGuia = window.guiasData[guiaId];
                    
                    this.isEventModalOpen = true;
                },

                openDetailsModal(guiaId) {
                    this.selectedGuia = window.guiasData[guiaId];
                    this.isDetailsModalOpen = true;
                },
                closeAllModals() {
                    this.isEventModalOpen = false;
                    this.isDetailsModalOpen = false;
                },

                getSelectedGuiaFacturas() {
                    if (this.selectedGuias.length !== 1) return [];
                    const guiaId = this.selectedGuias[0];
                    const guiaData = window.guiasData && window.guiasData[guiaId] ? window.guiasData[guiaId] : null;
                    
                    if (!guiaData) return [];

                    // Filtros de facturas de la guía seleccionada
                    return guiaData.facturas.filter(factura => factura.estatus_entrega === 'Pendiente');
                },
                setEventLocationFromMapClick(event) {
                    if(this.selectedGuias.length !== 1) {
                        alert("Por favor, selecciona solo una guía para añadir un evento.");
                        return;
                    }
                    this.evento.lat = event.detail.latLng.lat().toFixed(6);
                    this.evento.lng = event.detail.latLng.lng().toFixed(6);
                    this.isEventModalOpen = true;
                },

                handleMapRightClick(latLng) {
                    if(this.selectedGuias.length !== 1) {
                        alert("Por favor, selecciona solo una guía para añadir un evento desde el mapa.");
                        return;
                    }
                    this.evento.lat = latLng.lat().toFixed(6);
                    this.evento.lng = latLng.lng().toFixed(6);
                    this.isEventModalOpen = true;
                }

            }));
        });

            // --- Mapa del INDEX ---
            function drawRoute(rutaId) {
                const paradasParaRuta = window.rutasJson[rutaId];
                if (!paradasParaRuta || paradasParaRuta.length < 2) return;
                const waypoints = paradasParaRuta.slice(1, -1).map(p => ({ location: {lat: p.lat, lng: p.lng}, stopover: true }));
                const request = {
                    origin: {lat: paradasParaRuta[0].lat, lng: paradasParaRuta[0].lng},
                    destination: {lat: paradasParaRuta[paradasParaRuta.length - 1].lat, lng: paradasParaRuta[paradasParaRuta.length - 1].lng},
                    waypoints: waypoints,
                    travelMode: 'DRIVING'
                }; 
                directionsService.route(request, (result, status) => {
                    if (status == 'OK') {
                        const color = routeColors[rutaId % routeColors.length];
                        const renderer = new google.maps.DirectionsRenderer({
                            map: indexMap,
                            directions: result,
                            suppressMarkers: true,
                            polylineOptions: { strokeColor: color, strokeWeight: 5, strokeOpacity: 0.8 }
                        });
                        
                        activeRenderers[rutaId] = { renderer: renderer, markers: [] };

                        // AÑADIDO: Bucle para crear un marcador para cada parada
                        paradasParaRuta.forEach((parada, index) => {
                            const stopMarker = new google.maps.Marker({
                                position: { lat: parada.lat, lng: parada.lng },
                                map: indexMap,
                                label: {
                                    text: `${index + 1}`, // Etiqueta con el número de parada
                                    color: "white",
                                    fontSize: "11px",
                                    fontWeight: "bold"
                                },
                                icon: {
                                    path: google.maps.SymbolPath.CIRCLE,
                                    scale: 10,
                                    fillColor: color, // Usa el mismo color que la línea de la ruta
                                    fillOpacity: 1,
                                    strokeWeight: 1.5,
                                    strokeColor: "white"
                                },
                                title: `Parada ${index + 1}`
                            });
                            // Guardamos el marcador para poder borrarlo después
                            activeRenderers[rutaId].markers.push(stopMarker);
                        });
                    }
                });
            }

            function removeRoute(rutaId) {
                if (activeRenderers[rutaId]) {
                    // Elimina la línea de la ruta
                    if (activeRenderers[rutaId].renderer) {
                        activeRenderers[rutaId].renderer.setMap(null);
                    }
                    // Elimina cada marcador de parada asociado
                    if (activeRenderers[rutaId].markers) {
                        activeRenderers[rutaId].markers.forEach(marker => marker.setMap(null));
                    }
                    // Limpia el registro
                    delete activeRenderers[rutaId];
                }
            }

            // --- Funciones para el mapa de CREAR/EDITAR ---
            function agregarParada(location, nombre) { paradas.push({ lat: location.lat(), lng: location.lng(), nombre: nombre }); actualizarVistaParadas(); trazarRuta(); }
            function eliminarParada(index) { paradas.splice(index, 1); actualizarVistaParadas(); trazarRuta(); }
            function actualizarVistaParadas() { const container = document.getElementById('paradas-container'); if (!container) return; container.innerHTML = paradas.length === 0 ? '<p class="text-sm text-gray-500">Aún no hay paradas.</p>' : ''; paradas.forEach((parada, index) => { const el = document.createElement('div'); el.className = 'flex items-center justify-between p-2 bg-gray-100 rounded-md border'; el.setAttribute('data-id', index); el.innerHTML = `<div class="flex items-center"><svg class="w-5 h-5 text-gray-400 mr-2 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg><input type="text" value="${parada.nombre}" class="text-sm font-medium text-gray-800 bg-transparent border-0 p-1 focus:ring-1 focus:ring-indigo-500 focus:bg-white rounded" onchange="actualizarNombreParada(${index}, this.value)"></div><button type="button" onclick="eliminarParada(${index})" class="text-red-500 hover:text-red-700">&times;</button>`; container.appendChild(el); }); if (document.getElementById('paradas-container') && paradas.length > 0) { new Sortable(document.getElementById('paradas-container'), { animation: 150, onEnd: (evt) => { const [item] = paradas.splice(evt.oldIndex, 1); paradas.splice(evt.newIndex, 0, item); actualizarVistaParadas(); trazarRuta(); }, }); } }
            function actualizarNombreParada(index, nuevoNombre) { if (paradas[index]) paradas[index].nombre = nuevoNombre; }
            function trazarRuta() { if (paradas.length < 2) { if (directionsRenderer) directionsRenderer.setDirections({ routes: [] }); actualizarDistancia(null); return; } const waypoints = paradas.slice(1, -1).map(p => ({ location: new google.maps.LatLng(p.lat, p.lng), stopover: true })); const request = { origin: new google.maps.LatLng(paradas[0].lat, paradas[0].lng), destination: new google.maps.LatLng(paradas[paradas.length - 1].lat, paradas[paradas.length - 1].lng), waypoints: waypoints, travelMode: 'DRIVING' }; if (directionsService) { directionsService.route(request, (result, status) => { if (status == 'OK') { directionsRenderer.setDirections(result); directionsRenderer.setOptions({ draggable: true }); } }); } }
            function validarYEnviarFormulario() { const form = document.getElementById('rutaForm'); const paradasContainer = document.getElementById('paradas-hidden-inputs'); const errorContainer = document.getElementById('paradas-error'); if (!form || !paradasContainer || !errorContainer) { alert("Error: No se encontró el formulario."); return; } if (paradas.length < 2) { errorContainer.classList.remove('hidden'); return; } errorContainer.classList.add('hidden'); paradasContainer.innerHTML = ''; paradas.forEach((parada, index) => { paradasContainer.innerHTML += `<input type="hidden" name="paradas[${index}][nombre_lugar]" value="${parada.nombre.replace(/"/g, "'")}"><input type="hidden" name="paradas[${index}][latitud]" value="${parada.lat}"><input type="hidden" name="paradas[${index}][longitud]" value="${parada.lng}">`; }); form.submit(); }
            
            function actualizarDistancia(route) { 
                const distEl = document.getElementById('distancia-total'); 
                const inputEl = document.getElementById('distancia-total-input'); 
                if (!distEl || !inputEl) return; 
                let total = 0; 
                if (route) { route.legs.forEach(leg => total += leg.distance.value); } 
                const km = (total / 1000).toFixed(2); 
                distEl.textContent = `${km} km`; 
                inputEl.value = km; 
            }
        </script>
    </head>
    <body class="font-sans antialiased" style="font-family: 'Montserrat', sans-serif;" x-cloak
        x-data="{
            /* State for collapsible menus - automatically opens if the current route matches */
            isSuperAdminMenuOpen: {{ request()->routeIs('admin.*') ? 'true' : 'false' }},
            isAreaAdminMenuOpen: {{ request()->routeIs('area_admin.*') ? 'true' : 'false' }}
        }"
    >
        <div class="min-h-screen bg-gray-100 flex">
            <div class="w-64 bg-[#2c3856] text-white flex-col min-h-screen shadow-2xl relative z-10 hidden lg:flex sticky-sidebar">
            <!-- <div class="w-64 bg-[#2c3856] text-white flex-col min-h-screen shadow-2xl relative z-10 hidden lg:flex"> -->
                <div class="p-6 text-center">
                    <div class="logo-container py-4">
                        <img src="{{ Storage::disk('s3')->url('LogoBlanco.png') }}" alt="Minmer Global Logo" class="h-20 mx-auto mb-3">
                        <span class="text-xl font-extrabold text-white tracking-wide logo-text">CONTROL TOWER</span>
                        <span class="text-xs text-gray-300 mt-1 block logo-subtitle">MINMER GLOBAL</span>
                    </div>
                </div>

                <div class="px-6"><div class="border-t border-white/10"></div></div>

                <nav class="flex-1 px-4 py-6 space-y-2">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active-link' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        <span class="nav-text">{{ __('Dashboard') }}</span>
                    </x-nav-link>

                    <x-nav-link :href="route('folders.index')" :active="request()->routeIs('folders.index')" class="nav-link-custom {{ request()->routeIs('folders.index') ? 'active-link' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                        <span class="nav-text">{{ __('Gestión de Archivos') }}</span>
                    </x-nav-link>

                    <x-nav-link :href="route('area_admin.visits.index')" :active="request()->routeIs('area_admin.visits.*')" class="nav-link-custom {{ request()->routeIs('area_admin.visits.*') ? 'active-link' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z" />
                        </svg>
                        <span class="nav-text">{{ __('Gestión de Visitas') }}</span>
                    </x-nav-link>

                    <x-nav-link :href="route('rutas.dashboard')" :active="request()->routeIs('rutas.*')" class="nav-link-custom {{ request()->routeIs('rutas.*') ? 'active-link' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m0 0v2.25m0-2.25h1.5m-1.5 0H5.25m11.25-8.25v2.25m0-2.25h-1.5m1.5 0H12m0 0v2.25m0-2.25V6.75m0 0H9m12 6.75h-1.5m1.5 0v-2.25m0 2.25H12m0 0H9m12 6.75h-1.5m1.5 0v-2.25m0 2.25H12m0 0H9m-3.75 0H5.25m0 0V9.75M5.25 12h1.5m0 0V9.75m0 0H5.25m3.75 0H9m-3.75 0H5.25m0 0h1.5m3 0h1.5m-1.5 0H9m-3.75 0H9m9 3.75h1.5m-1.5 0H9m3.75 0H9m-3.75 0H9" />
                        </svg>
                        <span class="nav-text">{{ __('Gestión de Rutas') }}</span>
                    </x-nav-link>


                    {{-- Menu Super Admin --}}
                    @if (Auth::user()->is_area_admin && Auth::user()->area?->name === 'Administración')
                        <div class="pt-4 mt-2 border-t border-white/10">
                            <button @click="isSuperAdminMenuOpen = !isSuperAdminMenuOpen" class="dropdown-toggle text-xs">
                                <span>Super Admin</span>
                                <svg class="chevron-icon w-4 h-4" :class="{'rotate-180': isSuperAdminMenuOpen}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </button>

                            <div x-show="isSuperAdminMenuOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="overflow-hidden">
                                <div class="pl-4 mt-2 space-y-2">
                                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="nav-link-custom {{ request()->routeIs('admin.dashboard') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-1.621-1.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span class="nav-text">{{ __('Panel General') }}</span>
                                    </x-nav-link>
                                    {{-- Agregar mas links de admin... --}}
                                </div>
                            </div>
                        </div>
                    @elseif (Auth::user()->is_area_admin)
                        <div class="pt-4 mt-2 border-t border-white/10">
                            <button @click="isAreaAdminMenuOpen = !isAreaAdminMenuOpen" class="dropdown-toggle text-xs">
                                <span>Admin de Área</span>
                                <svg class="chevron-icon w-4 h-4" :class="{'rotate-180': isAreaAdminMenuOpen}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <div x-show="isAreaAdminMenuOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="overflow-hidden">
                                <div class="pl-4 mt-2 space-y-2">
                                    {{-- Links para Admin de Área --}}
                                    <x-nav-link :href="route('area_admin.dashboard')" :active="request()->routeIs('area_admin.dashboard')" class="nav-link-custom {{ request()->routeIs('area_admin.dashboard') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-1.621-1.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span class="nav-text">{{ __('Panel de Área') }}</span>
                                    </x-nav-link>

                                    <x-nav-link :href="route('area_admin.users.index')" :active="request()->routeIs('area_admin.users.*')" class="nav-link-custom {{ request()->routeIs('area_admin.users.*') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a8.967 8.967 0 0015 0H4.501z" /></svg> {{-- Icono de ejemplo, puedes usar otro --}}
                                        <span class="nav-text">{{ __('Gestión de Usuarios') }}</span>
                                    </x-nav-link>

                                    <x-nav-link :href="route('area_admin.folder_permissions.index')" :active="request()->routeIs('area_admin.folder_permissions.*')" class="nav-link-custom {{ request()->routeIs('area_admin.folder_permissions.*') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25H9.75m4.006-7.03a3.375 3.375 0 00-3.375-3.375H9.75M19.5 19.5h-1.5a3.375 3.375 0 00-3.375-3.375M12 2.253A8.962 8.962 0 0121 12c0 1.133-.213 2.21-.613 3.223M12 2.253A8.962 8.962 0 003 12c0 1.133.213 2.21.613 3.223" /></svg> {{-- Icono de ejemplo, puedes usar otro --}}
                                        <span class="nav-text">{{ __('Permisos de Carpetas') }}</span>
                                    </x-nav-link>

                                </div>
                            </div>
                        </div>
                    @endif
                </nav>
            </div>

            <div class="flex-1 flex flex-col bg-gray-100 w-full lg:w-auto">
                @include('layouts.navigation', ['currentFolder' => $currentFolder ?? null])
                @if (isset($header))
                    <header class="bg-white shadow-sm">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{{ $header }}</div>
                    </header>
                @endif
                <main class="flex-1 p-8">
                    @if (isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </main>

            </div>
        </div>
    </body>
</html>