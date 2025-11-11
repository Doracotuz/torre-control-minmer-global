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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
         
        <style>
            :root {
                --bg-sidebar: #2c3856;
                --bg-nav: #2c3856;
                --bg-nav-mobile: #344266;
                --bg-content: #E8ECF7;
                --bg-base: #f3f4f6;
                --text-sidebar: #e5e7eb;
                --text-sidebar-logo: #ffffff;
                --text-nav: #ffffff;
                --accent: #BECEF5;
                --accent-text: #b2c1fcff;
                --accent-hover: rgba(255, 255, 255, 0.05);
                --highlight: #ff9c00;
            }

            body.theme-gold {
                --bg-sidebar: rgba(0, 0, 0, 1);
                --bg-nav: rgba(0, 0, 0, 1);
                --bg-nav-mobile: rgba(0, 0, 0, 1);
                --bg-content: hsl(223, 10%, 90%);
                --bg-base: hsl(223, 10%, 95%);
                --text-sidebar: hsla(0, 0%, 100%, 1.00);
                --text-sidebar-logo: hsla(0, 0%, 100%, 1.00);
                --text-nav: hsla(0, 0%, 100%, 1.00);
                --accent: rgb(235, 205, 134);
                --accent-text: hsla(0, 0%, 100%, 1.00);
                --accent-hover: rgba(235, 205, 134, 0.80);
                --highlight: rgb(189, 159, 87);
            }

            .sidebar-bg { background-color: var(--bg-sidebar); }
            .base-bg { background-color: var(--bg-base); }
            .content-bg { background-color: var(--bg-content); }

            .logo-container .logo-text, .logo-container .logo-subtitle {
                color: var(--text-sidebar-logo);
            }
            body.theme-gold .logo-container:hover {
                background-color: rgba(0, 0, 0, 0.05);
            }

            .nav-link-custom {
                position: relative;
                display: flex;
                align-items: center;
                padding: 12px 16px;
                border-radius: 5px;
                font-weight: 500;
                color: var(--text-sidebar);
                transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
            }
            a.nav-link-custom:visited { color: var(--text-sidebar); }

            .nav-link-custom::before {
                content: '';
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                height: 0;
                width: 4px;
                background-color: var(--accent);
                border-radius: 2px;
                transition: height 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }

            .nav-link-custom:hover:not(.active-link) {
                background-color: var(--accent-hover);
                color: var(--text-sidebar-logo);
            }
            .nav-link-custom:hover:not(.active-link)::before {
                height: 60%;
            }

            .nav-link-custom.active-link {
                background-color: var(--accent-hover);
                color: var(--accent-text);
                font-weight: 600;
            }
            a.nav-link-custom.active-link:visited { color: var(--accent-text); }
            
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
            
            .nav-bg { background-color: var(--bg-nav); }
            .mobile-menu-bg { background-color: var(--bg-nav-mobile); }
            
            .nav-toggle-btn { color: var(--text-nav); }
            .nav-toggle-btn:hover { background-color: rgba(255, 255, 255, 0.1); }
            body.theme-gold .nav-toggle-btn:hover { background-color: rgba(0, 0, 0, 0.1); }
            
            .nav-area-name span { color: var(--text-nav); }
            .nav-user-btn { color: var(--text-nav); }
            .nav-user-btn:hover { color: #e5e7eb; }
            body.theme-gold .nav-user-btn:hover { color: var(--accent); }
            
            .search-bar:focus {
                border-color: var(--highlight) !important;
                --tw-ring-color: var(--highlight) !important;
            }
            .search-bar-icon:hover { color: var(--highlight); }
            
            .mobile-menu-link { 
                display: block;
                padding-left: 1rem;
                padding-right: 1rem;
                padding-top: 0.5rem;
                padding-bottom: 0.5rem;
                font-size: 1rem;
                line-height: 1.5rem;
                font-weight: 500;
                color: var(--text-nav); 
                transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, color 0.15s ease-in-out;
            }
            .mobile-menu-link:hover, .mobile-menu-link:focus {
                background-color: rgba(255, 255, 255, 0.1);
                color: var(--highlight);
                outline: none;
            }
            body.theme-gold .mobile-menu-link:hover, body.theme-gold .mobile-menu-link:focus {
                background-color: rgba(0, 0, 0, 0.1);
            }
            .mobile-menu-divider { border-color: rgba(255, 255, 255, 0.3); }
            body.theme-gold .mobile-menu-divider { border-color: rgba(0, 0, 0, 0.2); }
            .mobile-menu-user-email { color: #d1d5db; }
            body.theme-gold .mobile-menu-user-email { color: var(--accent) }

            /* body {
                zoom: 90%;
            }             */

            .logo-container {
                transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.4s ease;
                border-radius: 8px;
            }
            .logo-container:hover {
                transform: scale(1.03);
                background-color: rgba(255, 255, 255, 0.03);
            }
            .logo-container .logo-text {
                font-family: 'Montserrat', sans-serif;
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
                background-color: rgba(255, 255, 255, 0.07);
                color: #cbd5e0;
            }
            .dropdown-toggle .chevron-icon {
                transition: transform 0.3s ease-in-out;
            }

            .sticky-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
            }


            [x-cloak] { display: none !important; }
            
            .glowing-button {
            position: relative;
            overflow: hidden;
            }

            .glowing-button::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 40px;
                height: 40px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                opacity: 0;
                pointer-events: none;
                animation: glow-burst 1s ease-out forwards infinite alternate;
                transform: translate(-50%, -50%) scale(0);
            }

            .glowing-button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0px;
            height: 0px;
            background-color: white;
            border-radius: 50%;
            opacity: 0.8;
            pointer-events: none;
            animation: twinkle 1.5s ease-in-out infinite;
            }
            .sub-dropdown-toggle { 
                display: flex; 
                justify-content: space-between; 
                align-items: center; width: 100%; 
                padding: 8px 12px; 
                font-family: 'Montserrat', 
                sans-serif; 
                font-weight: 600; 
                text-transform: none; 
                letter-spacing: normal; 
                color: #cbd5e0;
                border-radius: 6px; 
                transition: background-color 0.3s ease; 
                margin-left: -12px;
            }
            .sub-dropdown-toggle:hover { 
                background-color: rgba(255, 255, 255, 0.05); 
                color: #ffffff; 
            }
            .sub-dropdown-toggle .chevron-icon { 
                transition: transform 0.3s 
                ease-in-out; width: 0.8rem; 
                height: 0.8rem;
            }       

            @keyframes glow-burst {
                0% {
                    transform: translate(-50%, -50%) scale(0);
                    opacity: 0.8;
                }
                100% {
                    transform: translate(-50%, -50%) scale(5);
                    opacity: 0;
                }
            }

            @keyframes twinkle {
            0%, 100% {
            opacity: 0.8;
            transform: translate(-50%, -50%) scale(1);
            }
            50% {
            opacity: 0.3;
            transform: translate(-50%, -50%) scale(1.2);
            }
            }

            .glowing-button span {
            position: relative;
            }
        </style>
        <script>
        let map, directionsService, directionsRenderer, autocomplete;
        let paradas = [];
        let indexMap, monitoreoMap, activeRenderers = {};
        const routeColors = ['#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#A133FF', '#33FFA1'];

        document.addEventListener("turbo:before-visit", () => {
            paradas = [];
            activeRenderers = {};
        });

        // CREAR/EDITAR
        window.initMap = function() {
            if (!document.getElementById('map')) return;
            if (window.initialParadas) { paradas = window.initialParadas; delete window.initialParadas; } else { paradas = []; }
            map = new google.maps.Map(document.getElementById("map"), { center: { lat: 19.4326, lng: -99.1332 }, zoom: 12, mapTypeControl: false, streetViewControl: false, gestureHandling: 'greedy',});
            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({ map: map, draggable: true, markerOptions: { draggable: true } });
            google.maps.event.addListener(directionsRenderer, 'directions_changed', () => {
                const result = directionsRenderer.getDirections();
                if (result && result.routes.length > 0) {
                    const newRoute = result.routes[0];
                    actualizarDistancia(newRoute);

                    newRoute.legs.forEach((leg, index) => {
                        if (paradas[index]) {
                            paradas[index].lat = leg.start_location.lat();
                            paradas[index].lng = leg.start_location.lng();
                        }
                    });
                    
                    const lastLeg = newRoute.legs[newRoute.legs.length - 1];
                    if (paradas[newRoute.legs.length]) {
                        paradas[newRoute.legs.length].lat = lastLeg.end_location.lat();
                        paradas[newRoute.legs.length].lng = lastLeg.end_location.lng();
                    }
                    
                    actualizarVistaParadas();
                }
            });
            const input = document.getElementById("autocomplete");
            autocomplete = new google.maps.places.Autocomplete(input, { fields: ["name", "geometry.location"] });
            autocomplete.addListener("place_changed", () => { const place = autocomplete.getPlace(); if (!place.geometry || !place.geometry.location) return; agregarParada(place.geometry.location, place.name); input.value = ''; });
            map.addListener('rightclick', (event) => agregarParada(event.latLng, "Punto Personalizado"));
            actualizarVistaParadas();
            trazarRuta();
        };

        // MAPA DEL INDEX
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

        // MAPA DE MONITOREO 
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

        // DIBUJAR EN MONITOREO 
        function drawMonitoreoRoute(guiaId) {
            const guiaData = window.guiasData[guiaId];
            if (!guiaData) return;

            activeRenderers[guiaId] = { renderer: null, markers: [], infoWindow: new google.maps.InfoWindow() };
            
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
                        text: `${index + 1}`,
                        color: "white",
                        fontSize: "12px",
                        fontWeight: "bold"
                    },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 12,
                        fillColor: "#2c3856",
                        fillOpacity: 1,
                        strokeWeight: 2,
                        strokeColor: "white"
                    },
                    title: `Parada ${index + 1}: ${parada.nombre_lugar}`
                });

                stopMarker.addListener("click", () => {
                    const contentString = `<div style="font-family: Montserrat, sans-serif; padding: 5px;">
                                            <p style="font-weight: 600; color: #2c3856;">Parada ${index + 1}</p>
                                            <p>${parada.nombre_lugar}</p>
                                        </div>`;
                    activeRenderers[guiaId].infoWindow.setContent(contentString);
                    activeRenderers[guiaId].infoWindow.open({ anchor: stopMarker, map: monitoreoMap });
                });

                activeRenderers[guiaId].markers.push(stopMarker);
            });

            guiaData.eventos.forEach(evento => {
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
                    ${evento.url_evidencia && evento.url_evidencia.length > 0 ? `
                        <div>
                        ${evento.url_evidencia.map(url => `
                            <a href="${url}" target="_blank" style="color: #007bff; font-weight: 600; display: block; margin-bottom: 4px;">
                            Ver Evidencia
                            </a>
                        `).join('')}
                        </div>
                    ` : ''}
                    </div>`;
                
                marker.addListener("click", () => {
                    activeRenderers[guiaId].infoWindow.setContent(contentString);
                    activeRenderers[guiaId].infoWindow.open({ anchor: marker, map: monitoreoMap });
                });
                activeRenderers[guiaId].markers.push(marker);
            });
        }

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
        
        // ALPINE.JS PARA MONITOREO
        document.addEventListener('alpine:init', () => {
        Alpine.data('monitoringManager', () => ({
        selectedGuias: JSON.parse(sessionStorage.getItem('selectedGuias')) || [],

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
                },
                showAccessDeniedModal() {
                    this.isAccessDeniedModalOpen = true;
                }

            }));

            Alpine.data('globalState', () => ({
                isAccessDeniedModalOpen: false,
                closeAccessDeniedModal() {
                    this.isAccessDeniedModalOpen = false;
                }
            }));
        });

            function agregarParada(location, nombre) { paradas.push({ lat: location.lat(), lng: location.lng(), nombre: nombre }); actualizarVistaParadas(); trazarRuta(); }
            function eliminarParada(index) { paradas.splice(index, 1); actualizarVistaParadas(); trazarRuta(); }
            function actualizarVistaParadas() { const container = document.getElementById('paradas-container'); if (!container) return; container.innerHTML = paradas.length === 0 ? '<p class="text-sm text-gray-500">Aún no hay paradas.</p>' : ''; paradas.forEach((parada, index) => { const el = document.createElement('div'); el.className = 'flex items-center justify-between p-2 bg-gray-100 rounded-md border'; el.setAttribute('data-id', index); el.innerHTML = `<div class="flex items-center"><svg class="w-5 h-5 text-gray-400 mr-2 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg><input type="text" value="${parada.nombre}" class="text-sm font-medium text-gray-800 bg-transparent border-0 p-1 focus:ring-1 focus:ring-indigo-500 focus:bg-white rounded" onchange="actualizarNombreParada(${index}, this.value)"></div><button type="button" onclick="eliminarParada(${index})" class="text-red-500 hover:text-red-700">&times;</button>`; container.appendChild(el); }); if (document.getElementById('paradas-container') && paradas.length > 0) { new Sortable(document.getElementById('paradas-container'), { animation: 150, onEnd: (evt) => { const [item] = paradas.splice(evt.oldIndex, 1); paradas.splice(evt.newIndex, 0, item); actualizarVistaParadas(); trazarRuta(); }, }); } }
            function actualizarNombreParada(index, nuevoNombre) {
                if (paradas[index]) {
                    paradas[index].nombre = nuevoNombre;
                }
            }            
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
            isSidebarOpen: JSON.parse(localStorage.getItem('isSidebarOpen') ?? 'true'),
            toggleSidebar() {
                this.isSidebarOpen = !this.isSidebarOpen;
                localStorage.setItem('isSidebarOpen', this.isSidebarOpen);
            },

            theme: localStorage.getItem('theme') ?? 'default',
            toggleTheme() {
                this.theme = (this.theme === 'default') ? 'gold' : 'default';
                localStorage.setItem('theme', this.theme);
            },
            
            isSuperAdminMenuOpen: {{ request()->routeIs('admin.*') ? 'true' : 'false' }},
            isAreaAdminMenuOpen: {{ request()->routeIs('area_admin.*') ? 'true' : 'false' }},
            isAccessDeniedModalOpen: false,
            
            showAccessDeniedModal() {
                this.isAccessDeniedModalOpen = true;
            },
            
            checkAccess(event) {
                const restrictedUsers = ['24', '25', '26', '27', '4'];
                if (restrictedUsers.includes(String({{ Auth::id() }}))) {
                    this.showAccessDeniedModal();
                    event.preventDefault();
                }
            }
        }"
        :class="{ 'theme-gold': theme === 'gold' }" 
    >
        <div class="min-h-screen base-bg flex">
            <div class="sidebar-bg text-white flex-col min-h-screen shadow-2xl relative z-10 hidden lg:flex sticky-sidebar transition-all duration-300 ease-in-out"
                :class="isSidebarOpen ? 'w-64' : 'w-0'"
            >
                <div class="overflow-hidden transition-opacity duration-200" :class="isSidebarOpen ? 'opacity-100' : 'opacity-0'">
                    <div class="p-6 text-center">
                            <div class="logo-container py-4">
                                <img src="{{ Storage::disk('s3')->url('escudoMinmerGlobal.png') }}" alt="Minmer Global Logo" class="h-20 mx-auto mb-3">
                                <span class="text-xl font-extrabold text-white tracking-wide logo-text">CONTROL TOWER</span>
                                <span class="text-xs text-gray-300 mt-1 block logo-subtitle">MINMER GLOBAL</span>
                            </div>
                        </div>

                        <nav class="flex-1 px-4 py-6 space-y-2">

                            @if(Auth::user()->area?->name === 'VentasFF')
                                
                                <x-nav-link :href="route('ff.dashboard.index')" :active="request()->routeIs('ff.*')" class="nav-link-custom {{ request()->routeIs('ff.*') ? 'active-link' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 00-3.741-7.11 9.094 9.094 0 00-7.11-3.741 9.094 9.094 0 00-7.11 3.741 9.094 9.094 0 003.741 7.11 9.094 9.094 0 007.11 3.741 9.094 9.094 0 007.11-3.741zM12 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="nav-text">{{ __('Friends & Family') }}</span>
                                </x-nav-link>

                            @else

                            @if(!Auth::user()->is_client)
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active-link' : '' }}">
                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.2099 15.89C20.5737 17.3945 19.5787 18.7202 18.3118 19.7513C17.0449 20.7824 15.5447 21.4874 13.9424 21.8048C12.34 22.1221 10.6843 22.0421 9.12006 21.5718C7.55578 21.1014 6.13054 20.2551 4.96893 19.1067C3.80733 17.9582 2.94473 16.5428 2.45655 14.9839C1.96837 13.4251 1.86948 11.7705 2.16851 10.1646C2.46755 8.55878 3.15541 7.05063 4.17196 5.77203C5.18851 4.49343 6.5028 3.48332 7.99992 2.83M21.9999 12C21.9999 10.6868 21.7413 9.38642 21.2387 8.17317C20.7362 6.95991 19.9996 5.85752 19.071 4.92893C18.1424 4.00035 17.04 3.26375 15.8267 2.7612C14.6135 2.25866 13.3131 2 11.9999 2V12H21.9999Z" /></svg>
                                <span class="nav-text">{{ __('Dashboard ') }}</span>
                            </x-nav-link>
                            @endif

                            @if (Auth::user()->is_client)
                                <x-nav-link :href="route('tablero.index')" :active="request()->routeIs('tablero.index')" class="nav-link-custom {{ request()->routeIs('tablero.index') ? 'active-link' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.2099 15.89C20.5737 17.3945 19.5787 18.7202 18.3118 19.7513C17.0449 20.7824 15.5447 21.4874 13.9424 21.8048C12.34 22.1221 10.6843 22.0421 9.12006 21.5718C7.55578 21.1014 6.13054 20.2551 4.96893 19.1067C3.80733 17.9582 2.94473 16.5428 2.45655 14.9839C1.96837 13.4251 1.86948 11.7705 2.16851 10.1646C2.46755 8.55878 3.15541 7.05063 4.17196 5.77203C5.18851 4.49343 6.5028 3.48332 7.99992 2.83M21.9999 12C21.9999 10.6868 21.7413 9.38642 21.2387 8.17317C20.7362 6.95991 19.9996 5.85752 19.071 4.92893C18.1424 4.00035 17.04 3.26375 15.8267 2.7612C14.6135 2.25866 13.3131 2 11.9999 2V12H21.9999Z" /></svg>
                                    <span class="nav-text">{{ __('Dashboard ') }}</span>
                                </x-nav-link>
                            @endif                    

                            @if(in_array(Auth::id(), ['24', '25', '26', '27', '4', '5', '6']))
                                <x-nav-link href="#" class="nav-link-custom" @click.prevent="checkAccess($event)">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8V21H3V8M10 12H14M1 3H23V8H1V3Z" /></svg>
                                    <span class="nav-text">{{ __('Archivos') }}</span>
                                </x-nav-link>
                            @else
                                <x-nav-link :href="route('folders.index')" :active="request()->routeIs('folders.index')" class="nav-link-custom {{ request()->routeIs('folders.index') ? 'active-link' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8V21H3V8M10 12H14M1 3H23V8H1V3Z" /></svg>
                                    <span class="nav-text">
                                        @if (Auth::user()->is_client)
                                            {{ __('Archivos') }}
                                        @else
                                            {{ __('Gestión de Archivos') }}
                                        @endif
                                    </span>
                                </x-nav-link>
                            @endif

                            @if (Auth::user()->is_client)
                                @if(in_array(Auth::id(), ['24', '25', '26', '27', '4', '5', '6']))
                                    <x-nav-link href="#" class="nav-link-custom" @click.prevent="checkAccess($event)">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                                        <span class="nav-text">{{ __('Organigrama') }}</span>
                                    </x-nav-link>

                                    <x-nav-link href="#" class="nav-link-custom" @click.prevent="checkAccess($event)">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 16V3H1V16H16ZM16 16H23V11L20 8H16V16ZM8 18.5C8 19.8807 6.88071 21 5.5 21C4.11929 21 3 19.8807 3 18.5C3 17.1193 4.11929 16 5.5 16C6.88071 16 8 17.1193 8 18.5ZM21 18.5C21 19.8807 19.8807 21 18.5 21C17.1193 21 16 19.8807 16 18.5C16 17.1193 17.1193 16 18.5 16C19.8807 16 21 17.1193 21 18.5Z" />
                                        </svg>
                                        <span class="nav-text">{{ __('Tracking') }}</span>
                                    </x-nav-link>
                                @else
                                    <x-nav-link :href="route('client.organigram.interactive')" :active="request()->routeIs('client.organigram.interactive')" class="nav-link-custom {{ request()->routeIs('client.organigram.interactive') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                                        <span class="nav-text">{{ __('Organigrama') }}</span>
                                    </x-nav-link>

                                    <x-nav-link :href="route('tracking.index')" :active="request()->routeIs('tracking.index')" class="nav-link-custom {{ request()->routeIs('tracking.index') ? 'active-link' : '' }}" target="_blank" rel="noopener noreferrer">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 16V3H1V16H16ZM16 16H23V11L20 8H16V16ZM8 18.5C8 19.8807 6.88071 21 5.5 21C4.11929 21 3 19.8807 3 18.5C3 17.1193 4.11929 16 5.5 16C6.88071 16 8 17.1193 8 18.5ZM21 18.5C21 19.8807 19.8807 21 18.5 21C17.1193 21 16 19.8807 16 18.5C16 17.1193 17.1193 16 18.5 16C19.8807 16 21 17.1193 21 18.5Z" />
                                        </svg>
                                        <span class="nav-text">{{ __('Tracking') }}</span>
                                    </x-nav-link>
                                @endif

                                <x-nav-link :href="route('rfq.index')" class="nav-link-custom glowing-button">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#FF9C00">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                    </svg>
                                    <span class="nav-text text-lg leading-none text-[#FF9C00]">
                                        RFQ
                                        <br>
                                        <span class="text-sm">Moët Hennessy</span>
                                    </span>
                                </x-nav-link>

                                <div class="pt-4 mt-4 border-t border-white/10 space-y-2">
                                    @if(in_array(Auth::id(), ['24', '25', '26', '27', '4', '5', '6']))
                                        <x-nav-link href="#" class="nav-link-custom" @click.prevent="checkAccess($event)">
                                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" d="M12 4c3.5 0 6.5 4 6.5 8s-4.5 8.5-6.5 10.5c-2-2-6.5-6.5-6.5-10.5S8.5 4 12 4z"/>
                                                <path stroke-linecap="round" d="M12 14.5l-3-6m3 6l3-6m-3 6l-1.5-3m1.5 3l1.5-3"/>
                                            </svg>
                                            <span class="nav-text">{{ __('Huella de Carbono') }}</span>
                                        </x-nav-link>

                                        <x-nav-link href="#" class="nav-link-custom" @click.prevent="checkAccess($event)">
                                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M 9 12.75 L 11.25 15 L 15 9.75 M 12 3 A 11.959 11.959 0 0 1 3.598 6 A 11.99 11.99 0 0 0 3 9.749 C 3 15.341 6.824 20.039 12 21.371 C 17.176 20.039 21 15.341 21 9.749 C 21 8.439 20.79 7.178 20.398 5.998 C 18 6 17 6 12 3 L 12 3" />
                                            </svg>
                                            <span class="nav-text">{{ __('Certificaciones') }}</span>
                                        </x-nav-link>

                                        @php
                                            $whatsappNumber = "5215536583392";
                                            $whatsappMessage = urlencode("Hola, me gustaría recibir asistencia para la plataforma \"Control Tower - Minmer Global\"");
                                            $whatsappLink = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";
                                        @endphp
                                        <x-nav-link :href="$whatsappLink" target="_blank" class="nav-link-custom" @click.prevent="checkAccess($event)">
                                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.423 7.016h-0.303C16.798 3.091 13.695 0 9.921 0 6.146 0 3.043 3.091 2.72 7.016H2.576c-0.776 0 -1.409 0.631 -1.409 1.408v5.53c0 0.778 0.633 1.409 1.409 1.409h1.531c0.778 0 1.409 -0.631 1.409 -1.409v-5.53c0 -0.777 -0.631 -1.408 -1.409 -1.408H3.494c0.318 -3.499 3.079 -6.242 6.427 -6.242 3.348 0 6.109 2.743 6.426 6.242h-0.454c-0.778 0 -1.409 0.631 -1.409 1.408v5.53c0 0.668 0.475 1.248 1.128 1.381l0.687 0.019c0.021 0.459 -0.028 1.353 -0.621 2.065 -0.494 0.593 -1.276 0.951 -2.321 1.077 -0.173 -0.269 -0.475 -0.447 -0.817 -0.447h-1.478c-0.538 0 -0.975 0.436 -0.975 0.975 0 0.539 0.437 0.975 0.975 0.975h1.479c0.457 0 0.838 -0.316 0.944 -0.741 1.235 -0.154 2.176 -0.603 2.796 -1.351 0.734 -0.888 0.819 -1.951 0.796 -2.544h0.349c0.777 0 1.409 -0.631 1.409 -1.41V8.424c0 -0.777 -0.632 -1.408 -1.41 -1.408" />
                                            </svg>
                                            <span class="nav-text">{{ __('Asistencia') }}</span>
                                        </x-nav-link>
                                    @else
                                        <x-nav-link href="#" class="nav-link-custom">
                                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" d="M12 4c3.5 0 6.5 4 6.5 8s-4.5 8.5-6.5 10.5c-2-2-6.5-6.5-6.5-10.5S8.5 4 12 4z"/>
                                                <path stroke-linecap="round" d="M12 14.5l-3-6m3 6l3-6m-3 6l-1.5-3m1.5 3l1.5-3"/>
                                            </svg>
                                            <span class="nav-text">{{ __('Huella de Carbono') }}</span>
                                        </x-nav-link>

                                        <x-nav-link href="#" class="nav-link-custom">
                                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M 9 12.75 L 11.25 15 L 15 9.75 M 12 3 A 11.959 11.959 0 0 1 3.598 6 A 11.99 11.99 0 0 0 3 9.749 C 3 15.341 6.824 20.039 12 21.371 C 17.176 20.039 21 15.341 21 9.749 C 21 8.439 20.79 7.178 20.398 5.998 C 18 6 17 6 12 3 L 12 3" />
                                            </svg>
                                            <span class="nav-text">{{ __('Certificaciones') }}</span>
                                        </x-nav-link>

                                        @php
                                            $whatsappNumber = "5215536583392";
                                            $whatsappMessage = urlencode("Hola, me gustaría recibir asistencia para la plataforma \"Control Tower - Minmer Global\"");
                                            $whatsappLink = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";
                                        @endphp
                                        <x-nav-link :href="$whatsappLink" target="_blank" class="nav-link-custom">
                                            <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.423 7.016h-0.303C16.798 3.091 13.695 0 9.921 0 6.146 0 3.043 3.091 2.72 7.016H2.576c-0.776 0 -1.409 0.631 -1.409 1.408v5.53c0 0.778 0.633 1.409 1.409 1.409h1.531c0.778 0 1.409 -0.631 1.409 -1.409v-5.53c0 -0.777 -0.631 -1.408 -1.409 -1.408H3.494c0.318 -3.499 3.079 -6.242 6.427 -6.242 3.348 0 6.109 2.743 6.426 6.242h-0.454c-0.778 0 -1.409 0.631 -1.409 1.408v5.53c0 0.668 0.475 1.248 1.128 1.381l0.687 0.019c0.021 0.459 -0.028 1.353 -0.621 2.065 -0.494 0.593 -1.276 0.951 -2.321 1.077 -0.173 -0.269 -0.475 -0.447 -0.817 -0.447h-1.478c-0.538 0 -0.975 0.436 -0.975 0.975 0 0.539 0.437 0.975 0.975 0.975h1.479c0.457 0 0.838 -0.316 0.944 -0.741 1.235 -0.154 2.176 -0.603 2.796 -1.351 0.734 -0.888 0.819 -1.951 0.796 -2.544h0.349c0.777 0 1.409 -0.631 1.409 -1.41V8.424c0 -0.777 -0.632 -1.408 -1.41 -1.408" />
                                            </svg>
                                            <span class="nav-text">{{ __('Asistencia') }}</span>
                                        </x-nav-link>
                                    @endif
                                </div>
                            @endif

                            @if (!Auth::user()->is_client)
                                <x-nav-link :href="route('area_admin.visits.index')" :active="request()->routeIs('area_admin.visits.*')" class="nav-link-custom {{ request()->routeIs('area_admin.visits.*') ? 'active-link' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z" />
                                    </svg>
                                    <span class="nav-text">{{ __('Gestión de Visitas') }}</span>
                                </x-nav-link>
                                @if(in_array(Auth::user()->area?->name, ['Tráfico', 'Tráfico Importaciones', 'Administración']))
                                <x-nav-link :href="route('rutas.dashboard')" :active="request()->routeIs('rutas.*')" class="nav-link-custom {{ request()->routeIs('rutas.*') ? 'active-link' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m0 0v2.25m0-2.25h1.5m-1.5 0H5.25m11.25-8.25v2.25m0-2.25h-1.5m1.5 0H12m0 0v2.25m0-2.25V6.75m0 0H9m12 6.75h-1.5m1.5 0v-2.25m0 2.25H12m0 0H9m12 6.75h-1.5m1.5 0v-2.25m0 2.25H12m0 0H9m-3.75 0H5.25m0 0V9.75M5.25 12h1.5m0 0V9.75m0 0H5.25m3.75 0H9m-3.75 0H5.25m0 0h1.5m3 0h1.5m-1.5 0H9m-3.75 0H9m9 3.75h1.5m-1.5 0H9m3.75 0H9m-3.75 0H9" />
                                    </svg>
                                    <span class="nav-text">{{ __('Gestión de Rutas') }}</span>
                                </x-nav-link>
                                @endif

                                <x-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')" class="nav-link-custom {{ request()->routeIs('tickets.*') ? 'active-link' : '' }}">
                                    <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                    </svg>
                                    <span class="nav-text">{{ __('Tickets de Soporte') }}</span>
                                </x-nav-link>
                                @can('viewAny', App\Models\Project::class)
                                    <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" class="nav-link-custom {{ request()->routeIs('projects.*') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="nav-text">{{ __('Proyectos') }}</span>
                                    </x-nav-link>  
                                @endcan
                                @if (Auth::check() && !Auth::user()->is_client && in_array(Auth::user()->area?->name, ['Administración', 'Almacén']))
                                    <x-nav-link :href="route('wms.dashboard')" :active="request()->routeIs('wms.*')" class="nav-link-custom {{ request()->routeIs('wms.*') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h6M9 11.25h6M9 15.75h6" />
                                        </svg>
                                        <span class="nav-text">WMS</span>
                                    </x-nav-link>
                                @endif    
                                @if(Auth::user()->is_area_admin && in_array(Auth::user()->area?->name, ['Recursos Humanos', 'Innovación y Desarrollo']))
                                    <x-nav-link :href="route('admin.organigram.index')" :active="request()->routeIs('admin.organigram.*')" class="nav-link-custom {{ request()->routeIs('admin.organigram.*') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                                        <span class="nav-text">{{ __('Organigrama') }}</span>
                                    </x-nav-link>
                                @endif
                                @if(in_array(Auth::user()->area?->name, ['Customer Service', 'Administración', 'Tráfico']))
                                    <x-nav-link :href="route('customer-service.index')" :active="request()->routeIs('customer-service.*')" class="nav-link-custom {{ request()->routeIs('customer-service.*') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 25 25" stroke-width="0.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.169 15.023c-0.012 0.006 -0.026 0.008 -0.037 0.017 -0.402 0.336 -0.804 0.283 -1.12 0.008l0.153 -1.576c0.142 -0.035 0.256 -0.071 0.335 -0.11 0.059 -0.033 0.123 -0.078 0.154 -0.14l0.033 -0.04 -0.009 -0.072c-0.003 -0.02 -0.065 -0.48 -0.138 -0.715l-0.001 -0.004c0.001 -0.006 0.003 -0.011 0.003 -0.017 0 -0.17 -0.002 -0.455 -2.845 -0.616 -0.028 -0.149 -0.054 -0.298 -0.089 -0.447 -0.18 -0.78 -0.348 -1.543 -0.435 -2.314 -0.158 -1.393 0.146 -5.046 0.36 -6.699 0.041 -0.011 0.081 -0.029 0.116 -0.054a0.353 0.353 0 0 0 0.14 -0.232l0.008 -0.055c0.025 -0.166 -0.074 -0.317 -0.224 -0.377l0.102 -0.678c0.056 -0.374 -0.212 -0.727 -0.599 -0.785L14.346 0.008c-0.384 -0.055 -0.746 0.199 -0.803 0.574l-0.102 0.678c-0.159 0.014 -0.298 0.128 -0.323 0.294l-0.008 0.054c-0.021 0.142 0.047 0.277 0.16 0.35 -0.282 1.642 -1.067 5.221 -1.628 6.508 -0.31 0.711 -0.696 1.392 -1.097 2.084a11.65 11.65 0 0 0 -0.592 1.172c-3.606 0.14 -3.609 0.485 -3.609 0.652 0 0.006 0.003 0.012 0.004 0.019l-0.008 0.027c-0.068 0.226 -0.128 0.67 -0.13 0.689l-0.011 0.084 0.159 0.147 0.038 0.027c0.069 0.034 0.166 0.066 0.286 0.097L6.85 15.13a0.968 0.968 0 0 1 -0.351 0.183c-0.656 0.023 -0.945 -0.791 -1.66 -0.567 -0.404 0.126 -0.66 0.514 -0.716 0.92 -0.136 0.986 0.896 1.717 1.657 0.995 0.012 -0.006 0.026 -0.008 0.036 -0.017 0.438 -0.378 0.878 -0.27 1.197 0.096l0.674 6.667c0 0.107 0.064 0.201 0.176 0.285 -0.016 0.205 0.006 0.443 0.009 0.464 0 0.07 0.025 0.095 0.12 0.192l0.035 0.028c0.422 0.241 2.002 0.624 4.443 0.624 2.445 0 4.026 -0.383 4.458 -0.63 0.065 -0.043 0.107 -0.087 0.127 -0.134l0.019 -0.067c0.003 -0.035 0.022 -0.295 0 -0.509 0.079 -0.071 0.125 -0.15 0.125 -0.237l0.645 -6.641c0.154 -0.201 0.323 -0.339 0.584 -0.412 0.676 -0.023 0.974 0.79 1.711 0.566 0.416 -0.126 0.68 -0.513 0.738 -0.92 0.14 -0.986 -0.923 -1.716 -1.707 -0.994m-1.064 -2.647c-0.301 0.099 -1.059 0.207 -2.27 0.281a11.322 11.322 0 0 0 -0.076 -0.568c1.39 0.082 2.077 0.201 2.346 0.287M13.773 0.616c0.037 -0.247 0.277 -0.418 0.539 -0.378l0.732 0.11c0.259 0.039 0.44 0.273 0.403 0.52l-0.1 0.662 -1.674 -0.252zm-0.433 1.026 0.008 -0.053a0.123 0.123 0 0 1 0.14 -0.103l0.079 0.012 1.899 0.286c0.067 0.01 0.113 0.073 0.103 0.139l-0.008 0.055c-0.005 0.032 -0.022 0.061 -0.048 0.08s-0.059 0.028 -0.091 0.023l-1.978 -0.298a0.123 0.123 0 0 1 -0.103 -0.14m0.246 0.397 1.622 0.244c-0.108 0.85 -0.275 2.56 -0.356 4.124l-2.14 -0.322c0.383 -1.519 0.727 -3.203 0.874 -4.047m-0.92 4.228 2.176 0.328c-0.044 0.918 -0.053 1.763 -0.001 2.332l-1.092 0.651c-0.118 -0.065 -0.248 -0.112 -0.387 -0.133 -0.141 -0.021 -0.279 -0.013 -0.41 0.015l-0.938 -1.048c0.208 -0.529 0.435 -1.301 0.651 -2.145m1.296 3.735c0.139 0.19 0.196 0.421 0.159 0.65 -0.078 0.49 -0.556 0.826 -1.073 0.751 -0.256 -0.038 -0.478 -0.17 -0.626 -0.372 -0.139 -0.19 -0.196 -0.421 -0.159 -0.65 0.071 -0.443 0.472 -0.761 0.928 -0.761q0.071 0 0.144 0.011c0.256 0.037 0.478 0.17 0.627 0.372m-3.788 2.038c0.003 0 0.005 0 0.008 0l-0.001 -0.016c0.185 -0.444 0.398 -0.881 0.646 -1.31 0.404 -0.698 0.793 -1.385 1.11 -2.109l0.82 0.917c-0.348 0.141 -0.617 0.445 -0.678 0.83 -0.045 0.279 0.024 0.56 0.193 0.79 0.178 0.243 0.444 0.402 0.75 0.447 0.057 0.009 0.115 0.013 0.171 0.013 0.545 0 1.027 -0.384 1.112 -0.918 0.045 -0.279 -0.024 -0.56 -0.193 -0.79 -0.055 -0.076 -0.122 -0.14 -0.193 -0.198l0.944 -0.563c0.092 0.755 0.254 1.498 0.428 2.255 0.099 0.432 0.169 0.862 0.217 1.291 -0.555 0.029 -1.194 0.051 -1.918 0.063l-0.694 -0.608 -1.365 0.611a58.085 58.085 0 0 1 -1.609 -0.041c0.076 -0.222 0.161 -0.442 0.252 -0.661m3.139 0.702c-0.279 0.003 -0.566 0.006 -0.869 0.006 -0.16 0 -0.313 -0.001 -0.467 -0.002l0.887 -0.397zm-3.497 -0.688a10.829 10.829 0 0 0 -0.233 0.632c-1.522 -0.073 -2.458 -0.197 -2.801 -0.31 0.314 -0.101 1.186 -0.245 3.033 -0.323m-4.152 4.305c-0.028 0.006 -0.055 0.017 -0.077 0.04 -0.528 0.539 -1.15 0.07 -1.15 -0.557 -0.001 -0.241 0.081 -0.452 0.247 -0.632q0.484 -0.41 0.921 0.101c0.319 0.201 0.519 0.356 0.923 0.328 0.005 0 0.009 -0.003 0.014 -0.004 0.01 -0.001 0.019 0.001 0.029 -0.002 0.119 -0.032 0.222 -0.082 0.318 -0.138l0.078 0.777c-0.392 -0.261 -0.857 -0.256 -1.303 0.086m11.09 7.738c-0.341 0.191 -1.824 0.578 -4.282 0.578 -2.423 0 -3.897 -0.378 -4.263 -0.571l-0.016 -0.016a3.118 3.118 0 0 1 -0.008 -0.23c1.035 0.392 3.398 0.431 4.259 0.431 0.877 0 3.31 -0.039 4.314 -0.444 0.002 0.091 0 0.184 -0.004 0.253m0.123 -0.699c-0.08 0.177 -1.308 0.565 -4.433 0.565s-4.352 -0.388 -4.432 -0.571L7.146 14.828c0.72 0.447 3.413 0.617 5.312 0.617 1.844 0 4.436 -0.159 5.244 -0.578zm0.848 -8.746c-0.286 0.242 -2.079 0.607 -5.265 0.607 -3.487 0 -5.311 -0.438 -5.311 -0.672h-0.026l-0.106 -1.052c1.02 0.183 2.998 0.294 5.428 0.294 2.395 0 4.351 -0.108 5.387 -0.289zm-5.28 -1.149c-3.405 0 -5.423 -0.216 -5.884 -0.419l-0.016 -0.014c0.015 -0.101 0.042 -0.274 0.073 -0.418 0.023 0.004 0.049 0.007 0.073 0.011l-0.008 -0.009c0.981 0.358 3.891 0.42 5.762 0.42 1.909 0 4.902 -0.064 5.82 -0.442h0.001c0.035 0.158 0.065 0.352 0.08 0.445 -0.426 0.206 -2.451 0.427 -5.901 0.427m7.856 2.971q-0.499 0.41 -0.949 -0.101c-0.329 -0.201 -0.535 -0.356 -0.951 -0.328 -0.006 0 -0.009 0.003 -0.014 0.004 -0.01 0.001 -0.019 -0.001 -0.03 0.002 -0.182 0.048 -0.329 0.13 -0.463 0.229l0.083 -0.854c0.397 0.243 0.865 0.233 1.314 -0.101 0.029 -0.006 0.056 -0.017 0.08 -0.04 0.544 -0.539 1.185 -0.07 1.185 0.557q0.002 0.361 -0.255 0.632" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12.34 10.389c-0.034 0.213 0.018 0.427 0.147 0.603 0.136 0.186 0.34 0.307 0.574 0.342 0.044 0.007 0.088 0.01 0.131 0.01 0.417 0 0.786 -0.293 0.852 -0.702 0.034 -0.213 -0.018 -0.427 -0.147 -0.603 -0.136 -0.186 -0.34 -0.307 -0.574 -0.342 -0.471 -0.071 -0.91 0.241 -0.983 0.692m1.482 -0.296a0.706 0.706 0 0 1 0.13 0.534c-0.064 0.401 -0.456 0.676 -0.878 0.615 -0.209 -0.031 -0.391 -0.139 -0.513 -0.305a0.706 0.706 0 0 1 -0.13 -0.534c0.058 -0.363 0.387 -0.624 0.759 -0.624q0.059 0 0.118 0.009c0.209 0.031 0.392 0.139 0.513 0.305" />
                                        </svg>
                                        <span class="nav-text">{{ __('Customer Service') }}</span>
                                    </x-nav-link>
                                @endif
                                
                                <!-- @if (Auth::user()->isSuperAdmin())
                                    <x-nav-link :href="route('ff.dashboard.index')" :active="request()->routeIs('ff.*')" class="nav-link-custom {{ request()->routeIs('ff.*') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 00-3.741-7.11 9.094 9.094 0 00-7.11-3.741 9.094 9.094 0 00-7.11 3.741 9.094 9.094 0 003.741 7.11 9.094 9.094 0 007.11 3.741 9.094 9.094 0 007.11-3.741zM12 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="nav-text">{{ __('Friends & Family') }}</span>
                                    </x-nav-link>
                                @endif                                                        -->
                            @endif


                            @if (Auth::user()->isSuperAdmin())
                                <div class="pt-4 mt-2 border-t border-white/10">
                                    <button @click="isSuperAdminMenuOpen = !isSuperAdminMenuOpen" class="dropdown-toggle text-xs">
                                        <span>Super Admin</span>
                                        <svg class="chevron-icon w-4 h-4" :class="{'rotate-180': isSuperAdminMenuOpen}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                    </button>

                                    <div x-show="isSuperAdminMenuOpen" x-transition:enter="transition ease-out duration-200" ...>
                                        <div class="pl-4 mt-2 space-y-2">
                                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="nav-link-custom {{ request()->routeIs('admin.dashboard') ? 'active-link' : '' }}">
                                                <svg class="nav-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.56-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22l-1.92 3.32c-.12.2-.06.47.12.61l2.03 1.58c-.03.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.03-1.58zm-5.14 2.56c-1.4 0-2.5-1.1-2.5-2.5s1.1-2.5 2.5-2.5 2.5 1.1 2.5 2.5-1.1 2.5-2.5 2.5z" fill="currentColor"/>
                                                    </svg>
                                                <span class="nav-text">{{ __('Panel General') }}</span>
                                            </x-nav-link>
                                            <x-nav-link :href="route('admin.statistics.index')" :active="request()->routeIs('admin.statistics.*')" class="nav-link-custom {{ request()->routeIs('admin.statistics.*') ? 'active-link' : '' }}">
                                                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5a9 9 0 019-9.75V15h6.75a9 9 0 01-6.75 6.75V13.5z" /></svg>
                                                <span class="nav-text">{{ __('Estadísticas') }}</span>
                                            </x-nav-link>                                    
                                            
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

                        @endif
                        </nav>
                    </div>
                </div>
            <div class="flex-1 flex flex-col content-bg w-full lg:w-auto transition-all duration-300 ease-in-out"
                :class="isSidebarOpen ? 'lg:ml-64' : 'lg:ml-0'"
            >
                @include('layouts.navigation', ['currentFolder' => $currentFolder ?? null])
                @if (isset($header))
                <div class="nav-bg">
                    <header class="content-bg rounded-tl-3xl">
                        <div class="w-[95%] py-6 pl-6 pr-4 sm:pl-8 lg:pl-10">{{ $header }}</div>
                    </header>
                </div>
                @endif
                <main class="content-bg flex-1 p-8">
                    @if (isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </main>

                <div x-show="isAccessDeniedModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-[#2b2b2b] bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        
                        <div x-show="isAccessDeniedModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-[#ffffff] rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                            <div>
                                    <div class="mx-auto flex items-center justify-center h-16">
                                        <img 
                                            src="{{ Storage::disk('s3')->url('LogoAzulm.PNG') }}" 
                                            alt="Logo" 
                                            class="h-12 w-auto max-w-[200px]">
                                    </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <h3 class="text-xl leading-6 font-bold text-[#2c3856]" id="modal-title">Acceso Denegado</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-[#666666]">No tienes acceso a esta función, consulta con tu asesor.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-6">
                                <button type="button" @click="isAccessDeniedModalOpen = false" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#ff9c00] text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] sm:text-sm">
                                    Entendido
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <x-chat-assistant />
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    </body>
</html>