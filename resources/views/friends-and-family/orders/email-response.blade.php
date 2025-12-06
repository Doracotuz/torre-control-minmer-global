<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de la Solicitud</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 h-screen flex items-center justify-center font-sans">
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full text-center border border-gray-100">
        
        @if($status === 'success')
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 mb-3">¡Acción Exitosa!</h1>
            <p class="text-gray-600 mb-8 leading-relaxed">{{ $message }}</p>
        @else
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100">
                    <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 mb-3">No se pudo procesar</h1>
            <p class="text-gray-600 mb-8 leading-relaxed">{{ $message }}</p>
        @endif

        <div class="pt-6 border-t border-gray-100">
            <p class="text-xs text-gray-400">Ya puedes cerrar esta ventana.</p>
        </div>
    </div>
</body>
</html>