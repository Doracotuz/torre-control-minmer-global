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
                    'Sanitario': { path: 'M21.58,24.97c0-1.01,0.82-1.82,1.82-1.82c1.01,0,1.82,0.82,1.82,1.82v33.33c0,1.01-0.82,1.82-1.82,1.82H1.82 C0.82,60.13,0,59.31,0,58.31V3.92c0-1.07,0.44-2.05,1.15-2.77l0.01-0.01C1.88,0.44,2.85,0,3.93,0H21.3c1.07,0,2.06,0.44,2.77,1.16 l0,0c0.71,0.71,1.15,1.69,1.15,2.77v4.62c0,1.01-0.82,1.82-1.82,1.82c-1.01,0-1.82-0.82-1.82-1.82V3.92c0-0.07-0.03-0.14-0.08-0.2 l0,0l0,0c-0.05-0.05-0.12-0.08-0.2-0.08H3.93c-0.08,0-0.15,0.03-0.2,0.08L3.72,3.73c-0.05,0.05-0.08,0.12-0.08,0.2v52.56h17.94 V24.97L21.58,24.97z M21.57,99.88L0.21,59.15c-0.46-0.89-0.12-1.98,0.77-2.45c0.27-0.14,0.56-0.21,0.84-0.21v-0.01h94.53 c1.01,0,1.82,0.82,1.82,1.82c0,0.07,0,0.14-0.01,0.21c-0.51,21.74-11.17,27.86-20.14,33c-5.24,3.01-9.83,5.64-10.69,11.21 l-0.01,0.05c-0.33,2.18-0.15,4.68,0.54,7.51c0.72,2.95,1.99,6.27,3.84,9.96c0.45,0.9,0.08,1.99-0.82,2.44 c-0.26,0.13-0.54,0.19-0.81,0.19l-57.06,0c-1.01,0-1.82-0.82-1.82-1.82c0-0.35,0.1-0.68,0.28-0.96L21.57,99.88L21.57,99.88z M4.83,60.13l20.39,38.89c0.26,0.5,0.28,1.11,0.01,1.65l-9.28,18.57h51.24c-1.3-2.89-2.25-5.59-2.86-8.09 c-0.81-3.32-1.01-6.29-0.61-8.91l0.01-0.06c1.13-7.3,6.43-10.34,12.48-13.81c7.92-4.54,17.29-9.92,18.26-28.23H4.83L4.83,60.13z M23.61,101.68c-1.01,0-1.82-0.82-1.82-1.82c0-1.01,0.82-1.82,1.82-1.82H43.5c1.01,0,1.82,0.82,1.82,1.82 c0,1.01-0.82,1.82-1.82,1.82H23.61L23.61,101.68z M25.21,58.58c-0.15,0.99-1.08,1.68-2.07,1.53c-0.99-0.15-1.68-1.08-1.53-2.07 c0.29-1.88,0.76-3.58,1.42-5.07c0.69-1.55,1.58-2.86,2.67-3.93c3.54-3.46,8.04-3.38,12.34-3.3c0.38,0.01,0.75,0.01,1.72,0.01 l38.96,0c9.24-0.06,19.48-0.13,19.43,13c0,1-0.81,1.81-1.81,1.81s-1.81-0.81-1.81-1.81c0.04-9.48-8.28-9.42-15.78-9.37 c-1.13,0.01-1.1,0.02-1.77,0.02H39.77l-1.78-0.03c-3.56-0.06-7.29-0.13-9.75,2.28c-0.77,0.75-1.39,1.68-1.89,2.79 C25.83,55.6,25.45,56.98,25.21,58.58L25.21,58.58z M15.33,11.17c2.83,0,5.12,2.29,5.12,5.12c0,2.83-2.29,5.12-5.12,5.12 c-2.83,0-5.12-2.29-5.12-5.12C10.21,13.46,12.51,11.17,15.33,11.17L15.33,11.17z M20.45,18.11c-1.01,0-1.82-0.82-1.82-1.82 c0-1.01,0.82-1.82,1.82-1.82h12.28c1.01,0,1.82,0.82,1.82,1.82c0,1.01-0.82,1.82-1.82,1.82H20.45L20.45,18.11z', fillColor: '#ff0040ff', fillOpacity: 1, strokeWeight: 0, scale: 0.3 },
                    'Alimentos': { path: 'M29.03,100.46l20.79-25.21l9.51,12.13L41,110.69C33.98,119.61,20.99,110.21,29.03,100.46L29.03,100.46z M53.31,43.05 c1.98-6.46,1.07-11.98-6.37-20.18L28.76,1c-2.58-3.03-8.66,1.42-6.12,5.09L37.18,24c2.75,3.34-2.36,7.76-5.2,4.32L16.94,9.8 c-2.8-3.21-8.59,1.03-5.66,4.7c4.24,5.1,10.8,13.43,15.04,18.53c2.94,2.99-1.53,7.42-4.43,3.69L6.96,18.32 c-2.19-2.38-5.77-0.9-6.72,1.88c-1.02,2.97,1.49,5.14,3.2,7.34L20.1,49.06c5.17,5.99,10.95,9.54,17.67,7.53 c1.03-0.31,2.29-0.94,3.64-1.77l44.76,57.78c2.41,3.11,7.06,3.44,10.08,0.93l0.69-0.57c3.4-2.83,3.95-8,1.04-11.34L50.58,47.16 C51.96,45.62,52.97,44.16,53.31,43.05L53.31,43.05z M65.98,55.65l7.37-8.94C63.87,23.21,99-8.11,116.03,6.29 C136.72,23.8,105.97,66,84.36,55.57l-8.73,11.09L65.98,55.65L65.98,55.65z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.3 },
                    'Combustible': { path: 'M99.06,20.2c0.27,0.13,0.51,0.3,0.74,0.52c0.06,0.06,0.11,0.12,0.16,0.18c2.89,2.29,5.78,4.88,7.88,8 c2.32,3.45,3.61,7.44,2.83,12.17c-0.33,1.98-1.08,3.71-2.22,5.24c-0.82,1.09-1.82,2.05-3,2.89c-0.06,1.53-0.08,3.03-0.08,4.52 c0.01,1.91,0.07,3.88,0.18,5.9c0.25,4.74,0.96,9.52,1.67,14.26c0.76,5.1,1.52,10.16,1.72,15.43c0.27,6.75-0.53,12.3-2.76,16.22 c-2.48,4.38-6.51,6.72-12.45,6.51v0c-7.09-0.13-11.45-4.11-13.42-11.46c-1.72-6.43-1.46-15.61,0.49-27.16 c-0.06-9.15-1.25-16.08-3.61-20.75c-1.54-3.05-3.63-5.07-6.27-6.03v59.91c0.86,0.41,1.64,0.97,2.3,1.64 c1.52,1.52,2.47,3.63,2.47,5.95v5.98c0,1.51-1.23,2.74-2.74,2.74H2.74c-1.51,0-2.74-1.23-2.74-2.74v-5.98 c0-2.32,0.95-4.42,2.47-5.95c0.47-0.47,1-0.89,1.57-1.24V14.52c0-4,1.63-7.63,4.26-10.26C10.93,1.63,14.56,0,18.56,0h37.78 C60.35,0,64,1.64,66.64,4.28c2.64,2.64,4.28,6.29,4.28,10.31v26.36c4.86,1.06,8.57,4.17,11.15,9.27 c2.77,5.47,4.15,13.31,4.19,23.46c0,0.16-0.01,0.32-0.04,0.47l0.01,0c-1.85,10.87-2.15,19.35-0.63,25.02 c1.27,4.77,3.95,7.35,8.24,7.41l0.05,0v0c3.66,0.12,6.09-1.22,7.52-3.75c1.69-2.98,2.28-7.55,2.05-13.31 c-0.19-4.88-0.94-9.85-1.68-14.85c-0.72-4.82-1.44-9.68-1.71-14.78c-0.11-2.01-0.17-4.06-0.18-6.18c-0.01-1.68,0.02-3.34,0.09-4.97 c-5.11-4.48-8.22-8.96-9.18-13.42c-0.91-4.23,0.05-8.29,3-12.17c-2.25-1.54-4.54-2.8-6.86-3.81c-3.17-1.38-6.43-2.31-9.75-2.85 c-1.49-0.24-2.5-1.65-2.26-3.14c0.24-1.49,1.65-2.5,3.14-2.26c3.76,0.61,7.45,1.66,11.06,3.23C92.54,15.82,95.85,17.75,99.06,20.2 L99.06,20.2z M65.44,44.23c-0.12-0.34-0.18-0.7-0.15-1.08c0.02-0.27,0.07-0.52,0.15-0.76v-27.8c0-2.5-1.03-4.78-2.68-6.43 c-1.65-1.65-3.93-2.68-6.43-2.68H18.56c-2.48,0-4.74,1.02-6.38,2.66c-1.64,1.64-2.66,3.9-2.66,6.38v91.22h55.92V44.23L65.44,44.23z M68.42,111.46c-0.08,0.01-0.15,0.01-0.23,0.01H7.26c-0.34,0.15-0.65,0.36-0.91,0.62c-0.53,0.53-0.86,1.26-0.86,2.07v3.24h64.73 v-3.24c0-0.8-0.33-1.53-0.86-2.07C69.09,111.82,68.77,111.61,68.42,111.46L68.42,111.46z M23.04,13.74h29.44 c1.53,0,2.92,0.62,3.92,1.63c0.07,0.07,0.14,0.14,0.2,0.22c0.89,0.99,1.43,2.29,1.43,3.7v18.78c0,1.53-0.62,2.92-1.63,3.92 c-1,1-2.39,1.63-3.92,1.63H23.04c-1.52,0-2.9-0.63-3.91-1.63l-0.01,0.01c-1-1-1.63-2.39-1.63-3.92V19.29 c0-1.53,0.62-2.92,1.63-3.92c0.07-0.07,0.14-0.14,0.22-0.2C20.33,14.28,21.63,13.74,23.04,13.74L23.04,13.74z M52.48,19.22H23.04 c-0.01,0-0.02,0-0.02,0L23,19.24c-0.01,0.01-0.02,0.03-0.02,0.04v18.78c0,0.01,0.01,0.03,0.02,0.04L23,38.12L23,38.12 c0.01,0.01,0.02,0.01,0.04,0.01h29.44c0.01,0,0.03-0.01,0.04-0.02c0.01-0.01,0.02-0.03,0.02-0.04V19.29c0-0.01,0-0.02,0-0.02 l-0.02-0.02C52.51,19.23,52.5,19.22,52.48,19.22L52.48,19.22z M98.15,26.5c-1.91,2.56-2.55,5.12-1.99,7.7 c0.67,3.12,3,6.44,6.88,9.95c0.39-0.35,0.74-0.72,1.03-1.11c0.61-0.81,1.02-1.76,1.19-2.84c0.52-3.16-0.37-5.87-1.97-8.25 C101.97,29.97,100.13,28.16,98.15,26.5L98.15,26.5z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.3 },
                    'Pernocta': { path: 'M3.36,0h7.3c1.85,0,3.36,1.56,3.36,3.36v43.77h37.33L61.99,9.69h41.85c10.47,0,19.04,8.59,19.04,19.04v19.04 h-0.02c0.01,0.12,0.02,0.24,0.02,0.37v30.49h-14.02V64.32H14.02v13.66H0V3.36C0,1.51,1.51,0,3.36,0L3.36,0z M35.44,10.37 c8.62,0,15.61,6.99,15.61,15.61c0,8.62-6.99,15.61-15.61,15.61c-8.62,0-15.61-6.99-15.61-15.61 C19.83,17.36,26.82,10.37,35.44,10.37L35.44,10.37z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.3 },
                    'Llegada a carga': { path: 'M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zM18 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z', fillColor: '#ffc107', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Fin de carga': { path: 'M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zM18 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z', fillColor: '#28a745', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'En ruta': { path: 'M22.43 10.59l-9.01-9.01c-.75-.75-2.07-.75-2.82 0l-9.01 9.01c-.75.75-.75 2.07 0 2.82l9.01 9.01c.75.75 2.07.75 2.82 0l9.01-9.01c.76-.75.76-2.07.01-2.82zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Llegada a cliente': { path: 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z', fillColor: '#28a745', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Proceso de entrega': { path: 'M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z', fillColor: '#007bff', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'Rechazo': { path: 'M82.89,2.55v35.68c0,21.86-13.5,42.16-35.63,43.82v33.22h17.12c1.42,0,2.55,2.4,2.55,3.8s-1.15,3.8-2.55,3.8H22.12 c-1.42,0-2.55-2.4-2.55-3.8s1.15-3.8,2.55-3.8h15.03V82.15C14.33,81.22,0,60.74,0,38.23V2.55C0,1.15,1.15,0,2.55,0h77.79 C81.75,0,82.89,1.15,82.89,2.55L82.89,2.55L82.89,2.55z M52.14,5.95l-4.57,18.28l11.04-1.98c2.04-0.36,3.99,1,4.35,3.03 c0.11,0.61,0.06,1.22-0.11,1.77L57.4,51.82c-0.44,2.02-2.44,3.29-4.46,2.84c-2.01-0.44-3.29-2.44-2.84-4.46l4.32-19.58l-11.22,2.01 c-0.5,0.09-1.04,0.07-1.57-0.06c-2.01-0.5-3.23-2.54-2.73-4.54l5.52-22.08H7.18v27.09v2.48v2.7c0,20.91,13.36,36.77,34.27,36.77 s34.27-15.87,34.27-36.77v-3.16V31.9V5.95H52.14L52.14,5.95z M75.72,31.9L75.72,31.9L75.72,31.9z', fillColor: '#dc3545', fillOpacity: 1, strokeWeight: 0, scale: 0.3 },
                    'Percance': { path: 'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z', fillColor: '#dc3545', fillOpacity: 1, strokeWeight: 0, scale: 0.9 },
                    'default': { path: 'M83.896,5.08H27.789c-12.491,0-22.709,10.219-22.709,22.71v40.079c0,12.489,10.22,22.71,22.709,22.71h17.643 c-2.524,9.986-5.581,18.959-14.92,27.241c17.857-4.567,31.642-13.8,41.759-27.241h3.051c12.488,0,31.285-10.219,31.285-22.71V27.79 C106.605,15.299,96.387,5.08,83.896,5.08L83.896,5.08z M81.129,41.069c-4.551,0-8.24,3.691-8.24,8.242s3.689,8.242,8.24,8.242 c4.553,0,8.242-3.691,8.242-8.242S85.682,41.069,81.129,41.069L81.129,41.069z M30.556,41.069c-4.552,0-8.242,3.691-8.242,8.242 s3.69,8.242,8.242,8.242c4.551,0,8.242-3.691,8.242-8.242S35.107,41.069,30.556,41.069L30.556,41.069z M55.843,41.069 c-4.551,0-8.242,3.691-8.242,8.242s3.691,8.242,8.242,8.242c4.552,0,8.241-3.691,8.241-8.242S60.395,41.069,55.843,41.069 L55.843,41.069z M27.789,0h56.108h0.006v0.02c7.658,0.002,14.604,3.119,19.623,8.139l-0.01,0.01 c5.027,5.033,8.148,11.977,8.15,19.618h0.02v0.003h-0.02v40.079h0.02v0.004h-0.02c-0.004,8.17-5.68,15.289-13.24,20.261 c-7.041,4.629-15.932,7.504-23.104,7.505v0.021H75.32v-0.021h-0.576c-5.064,6.309-10.941,11.694-17.674,16.115 c-7.443,4.888-15.864,8.571-25.31,10.987l-0.004-0.016c-1.778,0.45-3.737-0.085-5.036-1.552c-1.852-2.093-1.656-5.292,0.437-7.144 c4.118-3.651,6.849-7.451,8.826-11.434c1.101-2.219,1.986-4.534,2.755-6.938h-10.95h-0.007v-0.021 c-7.656-0.002-14.602-3.119-19.622-8.139C3.138,82.478,0.021,75.53,0.02,67.871H0v-0.003h0.02V27.79H0v-0.007h0.02 C0.021,20.282,3.023,13.46,7.878,8.464C7.967,8.36,8.059,8.258,8.157,8.16c5.021-5.021,11.968-8.14,19.628-8.141V0H27.789L27.789,0 z', fillColor: '#257e2aff', fillOpacity: 1, strokeWeight: 0, scale: 0.3  }
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