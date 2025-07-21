<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Envío - Minmer Global</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style> body { background-color: #f3f4f6; font-family: sans-serif; } .map-container { height: 40vh; border-radius: 0.5rem; z-index: 1; } [x-cloak] { display: none !important; } </style>
    <script>
        tailwind.config = { theme: { extend: { colors: { 'minmer-blue': '#2c3856', 'minmer-orange': '#ff9c00', 'minmer-gray': '#666666', 'minmer-dark-gray': '#2b2b2b', } } } }
    </script>
</head>
<body>
    <div class="container mx-auto max-w-3xl p-4">
        <div class="text-center my-8">
            <img src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Logotipo Minmer Global" class="mx-auto h-20 w-auto mb-4">
            <h1 class="text-3xl font-bold text-minmer-dark-gray">Seguimiento de Envío</h1>
            <p class="text-minmer-gray">Ingresa uno o varios números de factura separados por comas.</p>
        </div>

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert"><p>{{ session('error') }}</p></div>
        @endif

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <form action="{{ route('tracking.search') }}" method="POST" class="flex items-center gap-3">
                @csrf
                <input type="text" name="invoice_number" placeholder="Ej: FAC-001, FAC-002" class="flex-grow block w-full rounded-lg border-gray-300 shadow-sm focus:border-minmer-blue focus:ring focus:ring-minmer-blue focus:ring-opacity-50 transition" required>
                <button type="submit" class="bg-minmer-blue hover:bg-minmer-dark-gray text-white font-bold py-2 px-6 rounded-lg shadow-md transition-transform transform hover:scale-105">Rastrear</button>
            </form>
        </div>

        @if (!empty($invoicesData))
        <div class="space-y-8">
            @foreach($invoicesData as $invoiceData)
            <div class="bg-white p-6 rounded-xl shadow-lg" x-data="trackingMap({{ json_encode($invoiceData ?? []) }})">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    <div>
                        <h2 class="text-2xl font-bold text-minmer-blue mb-3">Factura: {{ $invoiceData['invoice_number'] }}</h2>
                        <div class="space-y-2 text-minmer-gray">
                            <p><strong>Estado del envío:</strong> <span class="font-semibold text-minmer-blue">{{ $invoiceData['shipment_status'] }}</span></p>
                            <p><strong>Estado de la factura:</strong> {{ $invoiceData['invoice_status'] }}</p>
                            <p><strong>Origen:</strong> {{ $invoiceData['origin'] }}</p>
                            <p><strong>Destino:</strong> {{ $invoiceData['destination'] }}</p>
                            <p><strong>Contenido:</strong> {{ $invoiceData['box_quantity'] }} cajas, {{ $invoiceData['bottle_quantity'] }} botellas.</p>
                        </div>
                    </div>
                    @if($invoiceData['last_event'])
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <h3 class="font-semibold text-minmer-dark-gray">Última Actualización</h3>
                        <p class="text-sm text-minmer-gray">{{ $invoiceData['last_event']['timestamp'] }}</p>
                        <p class="text-lg font-bold text-minmer-orange mt-1">{{ $invoiceData['last_event']['type'] }}</p>
                    </div>
                    @endif
                </div>

                @if (!empty($invoiceData['evidence']))
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="font-semibold text-minmer-dark-gray mb-3">Evidencias de Entrega</h3>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($invoiceData['evidence'] as $photoUrl)
                                <a href="{{ $photoUrl }}" target="_blank" class="block">
                                    <img src="{{ $photoUrl }}" alt="Evidencia de entrega" class="w-28 h-28 object-cover rounded-lg border-2 border-gray-200 shadow-sm hover:border-minmer-orange hover:shadow-md transition-all">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                {{-- ===================== CAMBIO 2: LÓGICA DE VISUALIZACIÓN DEL MAPA ===================== --}}
                @if(!empty($invoiceData['polyline']))
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="font-semibold text-minmer-dark-gray mb-2">Ubicación de la Entrega</h3>
                    <div id="map-{{ $invoiceData['invoice_number'] }}" class="map-container" x-ref="mapContainer"></div>
                </div>
                @endif
                {{-- ===================== FIN DEL CAMBIO ===================== --}}
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('trackingMap', (initialData) => ({
            map: null,
            data: initialData,
            init() {
                if (this.data.polyline && this.data.polyline.length > 0) {
                    this.$nextTick(() => this.initializeMap());
                }
            },
            initializeMap() {
                if (!this.$refs.mapContainer || this.map) return;
                try {
                    const lastEvent = this.data.last_event || {};
                    const initialCoords = [lastEvent.latitude || 19.4326, lastEvent.longitude || -99.1332];

                    this.map = L.map(this.$refs.mapContainer).setView(initialCoords, 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(this.map);

                    if (Array.isArray(this.data.polyline) && this.data.polyline.length > 0) {
                        L.polyline(this.data.polyline, { color: '#2c3856', weight: 5 }).addTo(this.map);
                    }

                    // ===================== CAMBIO 3: LÓGICA DEL ÍCONO DINÁMICO =====================
                    let iconHtml = '<i class="fas fa-truck text-3xl text-minmer-orange"></i>'; // Ícono por defecto
                    if (lastEvent.type === 'Entrega') {
                        iconHtml = '<i class="fas fa-check-circle text-3xl text-green-500"></i>';
                    } else if (lastEvent.type === 'No Entregado') {
                         iconHtml = '<i class="fas fa-times-circle text-3xl text-red-500"></i>';
                    }

                    const customIcon = L.divIcon({ 
                        className: 'custom-icon', 
                        html: iconHtml, 
                        iconSize: [30, 30],
                        iconAnchor: [15, 30],
                    });
                    
                    L.marker(initialCoords, { icon: customIcon })
                        .addTo(this.map)
                        .bindPopup(`<b>Último evento:</b> ${lastEvent.type || 'N/A'}<br>${lastEvent.timestamp || 'N/A'}`);
                    // ===================== FIN DEL CAMBIO =====================

                } catch (error) {
                    console.error('Error al inicializar el mapa:', error);
                    this.$refs.mapContainer.innerHTML = '<p class="text-red-500">No se pudo cargar el mapa.</p>';
                }
            }
        }));
    });
    </script>
</body>
</html>