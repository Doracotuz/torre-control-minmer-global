<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Ruta - TMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Usamos la fuente Inter como predeterminada y definimos el color de fondo */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #e5e7eb; /* Un gris claro de fondo para resaltar la tarjeta */
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="operatorView()" x-cloak>

    <div class="container mx-auto max-w-lg p-4 flex items-center justify-center min-h-screen">
        
        <div class="w-full">
            <div class="text-center mb-8">
                <img src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Logotipo" class="mx-auto h-20 w-auto mb-4">
                <h1 class="text-3xl font-bold text-gray-800">Seguimiento de Ruta</h1>
                <p class="text-gray-500">Introduce el número de guía para ver los detalles.</p>
            </div>

            <div class="bg-white p-6 sm:p-8 rounded-xl shadow-xl">

                @if (session('success'))
                    <div class="flex items-center bg-green-50 border-l-4 border-green-500 text-green-800 p-4 mb-6 rounded-r-lg" role="alert">
                        <i class="fas fa-check-circle mr-3"></i>
                        <p class="font-semibold">{{ session('success') }}</p>
                    </div>
                @endif
                @if (session('error'))
                    <div class="flex items-center bg-red-50 border-l-4 border-red-500 text-red-800 p-4 mb-6 rounded-r-lg" role="alert">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        <p class="font-semibold">{{ session('error') }}</p>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 mb-6 rounded-r-lg" role="alert">
                        <p class="font-bold mb-2"><i class="fas fa-times-circle mr-2"></i>Se encontraron errores:</p>
                        <ul class="list-disc list-inside ml-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (!isset($shipments))
                <div>
                    <form action="{{ route('operator.findRoute') }}" method="POST">
                        @csrf
                        <label for="guide_number" class="block text-sm font-medium text-gray-600 mb-1">Número de Guía</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="guide_number" id="guide_number" class="block w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 shadow-sm focus:border-[#2c3856] focus:ring focus:ring-[#2c3856] focus:ring-opacity-50 transition" placeholder="Ej: GUIA-12345" required>
                        </div>
                        <button type="submit" class="mt-4 w-full flex items-center justify-center bg-[#2c3856] hover:bg-[#1e293b] text-white font-bold py-2.5 px-4 rounded-lg shadow-md transition-transform transform hover:scale-105">
                            Buscar Ruta
                        </button>
                    </form>
                </div>
                @endif

                @if (isset($shipments))
                <div>
                    <div class="border-b pb-4 mb-4">
                        <h2 class="text-xl font-bold text-[#2b2b2b]">Guía: <span class="text-[#2c3856]">{{ $guide_number }}</span></h2>
                        <p class="text-gray-600">Estatus de la Ruta: 
                            <span class="font-semibold px-3 py-1 text-sm rounded-full
                                @switch($routeStatus)
                                    @case('Asignada') bg-blue-100 text-blue-800 @break
                                    @case('En Tránsito') bg-yellow-100 text-yellow-800 @break
                                    @case('Completada') bg-green-100 text-green-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch
                            ">
                                {{ $routeStatus }}
                            </span>
                        </p>
                    </div>

                    @if($routeStatus == 'Asignada')
                    <form action="{{ route('operator.startRoute') }}" method="POST" @submit.prevent="submitWithLocation($el)">
                        @csrf
                        <input type="hidden" name="guide_number" value="{{ $guide_number }}">
                        <input type="hidden" name="latitude" x-model="latitude">
                        <input type="hidden" name="longitude" x-model="longitude">
                        <button type="submit" class="w-full flex items-center justify-center bg-[#ff9c00] hover:bg-[#f59e0b] text-white font-bold py-3 px-4 rounded-lg shadow-lg transition-transform transform hover:scale-105">
                            <i class="fas fa-play mr-2"></i>Iniciar Ruta
                        </button>
                    </form>
                    @endif

                    <div class="mt-6">
                        @include('tms.partials.operator-details')
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @include('tms.partials.operator-modals')

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('operatorView', () => ({
                latitude: null,
                longitude: null,
                modalType: null,
                modalData: {},
                
                getLocation(callback) {
                    if (!navigator.geolocation) {
                        alert('La geolocalización no es soportada por tu navegador.');
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            this.latitude = position.coords.latitude;
                            this.longitude = position.coords.longitude;
                            if (callback) callback();
                        }, 
                        () => {
                            alert('No se pudo obtener tu ubicación. Asegúrate de conceder los permisos.');
                        }
                    );
                },

                submitWithLocation(form) {
                    this.getLocation(() => {
                        // Espera un momento para asegurar que los modelos de Alpine se actualicen
                        this.$nextTick(() => {
                            form.submit();
                        });
                    });
                },

                openModal(type, id = null, status = null) {
                    this.modalType = type;
                    this.modalData = { id, status };
                },

                closeModal() {
                    this.modalType = null;
                    this.modalData = {};
                }
            }));
        });
    </script>
</body>
</html>