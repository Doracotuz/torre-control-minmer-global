<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control Tower - Minmer Global</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
        /* Custom responsive adjustments */
        @media (max-width: 1024px) {
            .lg\:grid-cols-2 {
                grid-template-columns: 1fr; /* Single column on smaller screens */
            }
            .lg\:flex {
                display: none; /* Hide left section on smaller screens */
            }
        }
        @media (max-width: 640px) {
            .text-3xl {
                font-size: 1.5rem; /* Adjust font size for mobile */
            }
            .text-2xl {
                font-size: 1.25rem;
            }
            .h-24 {
                height: 4rem; /* Smaller logo size for mobile */
            }
            .h-20 {
                height: 3.5rem; /* Smaller logo size for mobile */
            }
            .p-6 {
                padding: 1.5rem; /* Reduce padding on mobile */
            }
            .max-w-6xl {
                max-width: 100%; /* Full width on mobile */
            }
        }
        @media (max-width: 768px) {
            .md\:flex-row {
                flex-direction: column; /* Stack footer links vertically */
            }
            .md\:mb-0 {
                margin-bottom: 0.5rem; /* Add spacing between footer links */
            }
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <main class="flex-grow flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-6xl mx-auto grid lg:grid-cols-2 gap-6 sm:gap-8 md:gap-12 items-center">
            
            <div class="hidden lg:flex flex-col items-start justify-center p-8 md:p-12">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-[#FF9C00] mb-4" style="font-family: 'Montserrat', sans-serif;">CONTROL TOWER</h1>
                <img src="{{ Storage::disk('s3')->url('LogoBlanco1.PNG') }}" alt="Minmer Global Logo" class="h-20 sm:h-24 mb-4">
                <p class="text-xl sm:text-2xl md:text-3xl text-white mt-2">Toda su operación</p>
                <p class="text-xl sm:text-2xl md:text-3xl text-white mt-2">en un solo sitio</p>
            </div>

            <div class="w-full max-w-md mx-auto bg-[#f0f3fa] rounded-md shadow-lg p-4 sm:p-6 md:p-8">
                <div class="lg:hidden flex flex-col items-center justify-center mb-4 sm:mb-6 text-center">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-[#2c3856] mb-2" style="font-family: 'Raleway', sans-serif;">CONTROL TOWER</h1>
                    <img src="{{ Storage::disk('s3')->url('LogoAzulm.PNG') }}" alt="Minmer Global Logo" class="h-16 sm:h-20">
                </div>
                {{ $slot }}
            </div>

        </div>
    </main>

    <footer class="w-full p-4 sm:p-6">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center text-center text-xs sm:text-sm text-white">
            <a href="{{ route('terms.conditions') }}" class="text-white hover:underline mb-2 md:mb-0">Términos y condiciones</a>
            <a href="{{ route('privacy.policy') }}" class="text-white hover:underline mb-2 md:mb-0">Política de privacidad</a>
            <span>© 2025 Minmer Global. Todos los derechos reservados.</span>
        </div>
    </footer>

</body>
</html>