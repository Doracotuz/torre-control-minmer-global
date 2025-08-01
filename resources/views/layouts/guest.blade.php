<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control Tower - Minmer Global</title>

    {{-- Se recomienda usar Tailwind CSS para estos estilos --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&family=Raleway:wght@800&display=swap');
        body {
            font-family: 'Montserrat', sans-serif;
            background-image: url('{{ Storage::disk('s3')->url('fondDeLogin.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-6xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
            
            {{-- Columna Izquierda: Logo e Información --}}
            <div class="hidden lg:flex flex-col items-start justify-center p-12">
                <h1 class="text-4xl font-extrabold text-[#FF9C00] mb-4" style="font-family: 'Montserrat', sans-serif;">CONTROL TOWER</h1>
                <img src="{{ Storage::disk('s3')->url('LogoBlanco1.PNG') }}" alt="Minmer Global Logo" class="h-24 mb-4">
                <p class="text-3xl text-white mt-2">Toda su operación</p>
                <p class="text-3xl text-white mt-2">en un solo sitio</p>
            </div>

            {{-- Columna Derecha: Contenido Dinámico (Formulario) --}}
            <div class="w-full max-w-md mx-auto bg-[#f0f3fa] rounded-md shadow-lg p-6 sm:p-8">
                {{-- Logo para vista móvil --}}
                <div class="lg:hidden flex flex-col items-center justify-center mb-6 text-center">
                    <h1 class="text-2xl font-extrabold text-[#2c3856] mb-2" style="font-family: 'Raleway', sans-serif;">CONTROL TOWER</h1>
                    <img src="{{ Storage::disk('s3')->url('LogoAzulm.PNG') }}" alt="Minmer Global Logo" class="h-20">
                </div>
                {{ $slot }}
            </div>

        </div>
    </main>

    <footer class="w-full p-6">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center text-center text-sm text-white">
            <a href="{{ route('terms.conditions') }}" class="text-white hover:underline mb-2 md:mb-0">Términos y condiciones</a>
            <a href="{{ route('privacy.policy') }}" class="text-white hover:underline mb-2 md:mb-0">Política de privacidad</a>
            <span>© 2025 Minmer Global. Todos los derechos reservados.</span>
        </div>
    </footer>

</body>
</html>