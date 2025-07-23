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
            /* ==== REFINED STYLES FOR ELEGANCE AND INTERACTIVITY ==== */

            /* --- General Nav Link Style --- */
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

            /* --- Left Border Indicator for Hover/Active --- */
            .nav-link-custom::before {
                content: '';
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                height: 0;
                width: 4px;
                background-color: #ff9c00; /* Brand Orange */
                border-radius: 2px;
                transition: height 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }

            /* --- Hover State (Non-Active) --- */
            .nav-link-custom:hover:not(.active-link) {
                background-color: rgba(255, 255, 255, 0.05);
                color: #ffffff;
            }
            .nav-link-custom:hover:not(.active-link)::before {
                height: 60%;
            }

            /* --- Active Link State --- */
            .nav-link-custom.active-link {
                background-color: #ff9c00; /* Brand Orange */
                color: #ffffff;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(255, 156, 0, 0.2);
            }
            .nav-link-custom.active-link::before {
                height: 100%;
            }

            /* --- Icon and Text Styling --- */
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

            /* --- Elegant Logo Hover Effect --- */
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

            /* --- Dropdown Styles --- */
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
                min-height: 100vh; /* Asegura que la sidebar tenga al menos la altura de la ventana */
                align-self: flex-start; /* Ayuda a que sticky funcione correctamente dentro de un flex container */
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

        // ========= INICIALIZADOR PARA EL MAPA DE CREAR/EDITAR =========
        window.initMap = function() {
            if (!document.getElementById('map')) return;
            if (window.initialParadas) { paradas = window.initialParadas; delete window.initialParadas; } else { paradas = []; }
            map = new google.maps.Map(document.getElementById("map"), { center: { lat: 19.4326, lng: -99.1332 }, zoom: 12, mapTypeControl: false, streetViewControl: false });
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

        // ========= INICIALIZADOR PARA EL MAPA DEL INDEX =========
        window.initIndexMap = function() {
            if (!document.getElementById('map-panel')) return;
            indexMap = new google.maps.Map(document.getElementById("map-panel"), { center: { lat: 19.4326, lng: -99.1332 }, zoom: 10, mapTypeControl: false });
            directionsService = new google.maps.DirectionsService();
            document.querySelectorAll('.route-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', (event) => {
                    const rutaId = event.target.dataset.rutaId;
                    if (event.target.checked) { drawRoute(rutaId); } else { removeRoute(rutaId); }
                });
            });
        };

        // ========= INICIALIZADOR PARA EL MAPA DE MONITOREO =========
        window.initMonitoreoMap = function() {
            if (!document.getElementById('monitoreo-map')) return;
            monitoreoMap = new google.maps.Map(document.getElementById("monitoreo-map"), {
                center: { lat: 23.6345, lng: -102.5528 }, zoom: 5, mapTypeControl: false,
            });
            directionsService = new google.maps.DirectionsService();
        };

        // ========= FUNCIONES PARA DIBUJAR EN MONITOREO (MEJORADAS) =========
        function drawMonitoreoRoute(guiaId) {
            const guiaData = window.guiasData[guiaId];
            if (!guiaData) return;

            activeRenderers[guiaId] = { renderer: null, markers: [], infoWindow: new google.maps.InfoWindow() };
            
            // 1. Dibujar el trazo de la ruta (sin cambios)
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
                        const renderer = new google.maps.DirectionsRenderer({ map: monitoreoMap, directions: result, suppressMarkers: false, polylineOptions: { strokeColor: color, strokeWeight: 5, strokeOpacity: 0.7 } });
                        activeRenderers[guiaId].renderer = renderer;
                    }
                });
            }

            // 2. Dibujar marcadores de eventos con ICONOS SVG PERSONALIZADOS
            guiaData.eventos.forEach(evento => {
                // --- NUEVO OBJETO DE ICONOS ALUSIVOS ---
                // Usamos SVG Paths para tener control total
                const icons = {
                    'Factura Entregada': {
                        path: 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z', // Checkmark
                        fillColor: '#28a745', fillOpacity: 1, strokeWeight: 1, strokeColor: '#fff', scale: 1.5
                    },
                    'Factura no entregada': {
                        path: 'M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z', // Cross (X)
                        fillColor: '#dc3545', fillOpacity: 1, strokeWeight: 1, strokeColor: '#fff', scale: 1
                    },
                    'Sanitario': {
                        path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z', // Pin
                        fillColor: '#17a2b8', fillOpacity: 1, strokeWeight: 2, strokeColor: '#fff', scale: 1.8, anchor: new google.maps.Point(12, 24)
                    },
                    'Alimentos': {
                        path: 'M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 5z', // Fork and Knife
                        fillColor: '#fd7e14', fillOpacity: 1, strokeWeight: 0, scale: 0.8
                    },
                    'Combustible': {
                        path: 'M12,3c-0.55,0-1,0.45-1,1v1h-1c-0.55,0-1,0.45-1,1v10c0,1.1,0.9,2,2,2h2c0.55,0,1-0.45,1-1v-1h1c0.55,0,1-0.45,1-1V9c0-1.1-0.9-2-2-2h-3V4c0-0.55-0.45-1-1-1z M14,15h-2V9h2V15z', // Gas Pump
                        fillColor: '#ffc107', fillOpacity: 1, strokeWeight: 0, scale: 0.8
                    },
                    'Percance': {
                        path: 'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z', // Warning Triangle
                        fillColor: '#dc3545', fillOpacity: 1, strokeWeight: 0, scale: 0.9
                    },
                    'default': {
                        path: google.maps.SymbolPath.CIRCLE, fillColor: '#6c757d', fillOpacity: 1, strokeColor: '#fff', scale: 6, strokeWeight: 1
                    }
                };

                const marker = new google.maps.Marker({
                    position: {lat: evento.lat, lng: evento.lng},
                    map: monitoreoMap,
                    title: evento.subtipo,
                    icon: icons[evento.subtipo] || icons['default']
                });

                let facturaAfectadaHtml = '';
                if (evento.tipo === 'Entrega' && evento.factura_id) {
                    // Buscamos la factura en los datos de la guía
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

            // CORRECCIÓN: Lógica para remover rutas del mapa
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
        
        // ========= COMPONENTE ALPINE.JS PARA LA VISTA DE MONITOREO =========
document.addEventListener('alpine:init', () => {
    Alpine.data('monitoringManager', () => ({
        // El array con los IDs de las guías seleccionadas. Es la "única fuente de verdad".
        selectedGuias: JSON.parse(sessionStorage.getItem('selectedGuias')) || [],

        
        // Propiedades para el modal de eventos.
        isEventModalOpen: false,
        isDetailsModalOpen: false, // <-- NUEVO ESTADO
        selectedGuia: null, // <-- NUEVO ESTADO para guardar la guía completa
        
        evento: { tipo: 'Entrega', subtipo: 'Factura Entregada', lat: '', lng: '' },
        eventSubtypes: {
            'Entrega': ['Factura Entregada', 'Factura no entregada'],
            'Notificacion': ['Sanitario', 'Alimentos', 'Combustible', 'Pernocta', 'Percance']
        },
        
        // Se ejecuta cuando el componente se carga en la página.
            init() {
                this.selectedGuias = JSON.parse(sessionStorage.getItem('selectedGuias')) || [];
                
                // MEJORA: La forma más limpia de manejar el clic derecho
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
                        // Usamos un truco para evitar duplicados y forzar al observador a reaccionar.
                        this.selectedGuias = [...new Set([...this.selectedGuias, guiaId])];
                    } else {
                        // Reasignamos el array para que el observador reaccione.
                        this.selectedGuias = this.selectedGuias.filter(id => id !== guiaId);
                    }
                },

                deselectAll() {
                    this.selectedGuias = [];
                },
                openEventModal() {
                    if (this.selectedGuias.length !== 1) return;
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

                    // --- FILTRO AÑADIDO AQUÍ ---
                    // Devolvemos solo las facturas cuyo estatus de entrega sea 'Pendiente'
                    return guiaData.facturas.filter(factura => factura.estatus_entrega === 'Pendiente');
                },
                // CORRECCIÓN: Añadimos la función que faltaba
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

            // --- Funciones auxiliares para el mapa del INDEX ---
            function drawRoute(rutaId) { const paradasParaRuta = window.rutasJson[rutaId]; if (!paradasParaRuta || paradasParaRuta.length < 2) return; const waypoints = paradasParaRuta.slice(1, -1).map(p => ({ location: {lat: p.lat, lng: p.lng}, stopover: true })); const request = { origin: {lat: paradasParaRuta[0].lat, lng: paradasParaRuta[0].lng}, destination: {lat: paradasParaRuta[paradasParaRuta.length - 1].lat, lng: paradasParaRuta[paradasParaRuta.length - 1].lng}, waypoints: waypoints, travelMode: 'DRIVING' }; directionsService.route(request, (result, status) => { if (status == 'OK') { const color = routeColors[rutaId % routeColors.length]; const renderer = new google.maps.DirectionsRenderer({ map: indexMap, directions: result, suppressMarkers: true, polylineOptions: { strokeColor: color, strokeWeight: 5, strokeOpacity: 0.8 } }); activeRenderers[rutaId] = renderer; } }); }
            function removeRoute(rutaId) { if (activeRenderers[rutaId]) { activeRenderers[rutaId].setMap(null); delete activeRenderers[rutaId]; } }

            // --- Funciones auxiliares para el mapa de CREAR/EDITAR ---
            function agregarParada(location, nombre) { paradas.push({ lat: location.lat(), lng: location.lng(), nombre: nombre }); actualizarVistaParadas(); trazarRuta(); }
            function eliminarParada(index) { paradas.splice(index, 1); actualizarVistaParadas(); trazarRuta(); }
            function actualizarVistaParadas() { const container = document.getElementById('paradas-container'); if (!container) return; container.innerHTML = paradas.length === 0 ? '<p class="text-sm text-gray-500">Aún no hay paradas.</p>' : ''; paradas.forEach((parada, index) => { const el = document.createElement('div'); el.className = 'flex items-center justify-between p-2 bg-gray-100 rounded-md border'; el.setAttribute('data-id', index); el.innerHTML = `<div class="flex items-center"><svg class="w-5 h-5 text-gray-400 mr-2 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg><input type="text" value="${parada.nombre}" class="text-sm font-medium text-gray-800 bg-transparent border-0 p-1 focus:ring-1 focus:ring-indigo-500 focus:bg-white rounded" onchange="actualizarNombreParada(${index}, this.value)"></div><button type="button" onclick="eliminarParada(${index})" class="text-red-500 hover:text-red-700">&times;</button>`; container.appendChild(el); }); if (document.getElementById('paradas-container') && paradas.length > 0) { new Sortable(document.getElementById('paradas-container'), { animation: 150, onEnd: (evt) => { const [item] = paradas.splice(evt.oldIndex, 1); paradas.splice(evt.newIndex, 0, item); actualizarVistaParadas(); trazarRuta(); }, }); } }
            function actualizarNombreParada(index, nuevoNombre) { if (paradas[index]) paradas[index].nombre = nuevoNombre; }
            function trazarRuta() { if (paradas.length < 2) { if (directionsRenderer) directionsRenderer.setDirections({ routes: [] }); actualizarDistancia(null); return; } const waypoints = paradas.slice(1, -1).map(p => ({ location: new google.maps.LatLng(p.lat, p.lng), stopover: true })); const request = { origin: new google.maps.LatLng(paradas[0].lat, paradas[0].lng), destination: new google.maps.LatLng(paradas[paradas.length - 1].lat, paradas[paradas.length - 1].lng), waypoints: waypoints, travelMode: 'DRIVING' }; if (directionsService) { directionsService.route(request, (result, status) => { if (status == 'OK') { directionsRenderer.setDirections(result); directionsRenderer.setOptions({ draggable: true }); } }); } }
            function validarYEnviarFormulario() { const form = document.getElementById('rutaForm'); const paradasContainer = document.getElementById('paradas-hidden-inputs'); const errorContainer = document.getElementById('paradas-error'); if (!form || !paradasContainer || !errorContainer) { alert("Error: No se encontró el formulario."); return; } if (paradas.length < 2) { errorContainer.classList.remove('hidden'); return; } errorContainer.classList.add('hidden'); paradasContainer.innerHTML = ''; paradas.forEach((parada, index) => { paradasContainer.innerHTML += `<input type="hidden" name="paradas[${index}][nombre_lugar]" value="${parada.nombre.replace(/"/g, "'")}"><input type="hidden" name="paradas[${index}][latitud]" value="${parada.lat}"><input type="hidden" name="paradas[${index}][longitud]" value="${parada.lng}">`; }); form.submit(); }
            
            // ======== FUNCIÓN RESTAURADA ========
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


                    {{-- Super Admin Collapsible Menu --}}
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
                                    {{-- El resto de los links de admin... --}}
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
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-1.621-1.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{-- Icono de ejemplo, puedes usar otro --}}
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