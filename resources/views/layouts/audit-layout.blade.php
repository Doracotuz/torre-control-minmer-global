<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Módulo de Auditoría - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>

</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Header Fijo para Móvil -->
        <header class="bg-[#2c3856] text-white shadow-lg sticky top-0 z-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <img src="{{ Storage::disk('s3')->url('escudoMinmer.png') }}" alt="Logo" class="h-10 w-auto">
                        <span class="ml-3 font-bold text-lg">Auditoría</span>
                    </div>
                    <div>
                        <!-- INICIA CÓDIGO CORREGIDO -->
                        <!-- Se usa un enlace <a> que dispara el envío del formulario con JS -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); this.closest('form').submit();"
                               class="text-gray-300 hover:text-white">
                                <i class="fas fa-sign-out-alt fa-lg"></i>
                            </a>
                        </form>
                        <!-- TERMINA CÓDIGO CORREGIDO -->
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenido Principal -->
        <main class="p-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
