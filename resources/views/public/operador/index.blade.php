<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso Operador - Control Tower</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-sm p-8 space-y-8 bg-white shadow-2xl rounded-2xl">
        <div>
            <img class="mx-auto h-24 w-auto" src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Minmer Global Logo">
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-[#2c3856]">
                Acceso de Operador
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingresa tu número de guía para comenzar.
            </p>
        </div>

        <form class="mt-8 space-y-6" action="{{ route('operador.check') }}" method="POST">
            @csrf

            {{-- Mensajes de error --}}
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="guia" class="sr-only">Número de Guía</label>
                    <input id="guia" name="guia" type="text" required class="relative block w-full appearance-none rounded-lg border border-gray-300 px-3 py-4 text-gray-900 placeholder-gray-500 focus:z-10 focus:border-[#ff9c00] focus:outline-none focus:ring-[#ff9c00] sm:text-sm" placeholder="Número de Guía">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative flex w-full justify-center rounded-lg border border-transparent bg-[#ff9c00] py-3 px-4 text-sm font-semibold text-white hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                    Continuar
                </button>
            </div>
        </form>
    </div>

</body>
</html>