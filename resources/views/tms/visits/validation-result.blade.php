<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de Validación - Minmer Global</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body { background-color: #f3f4f6; }
        .status-success { background-color: #10B981; /* green-500 */ }
        .status-error { background-color: #EF4444; /* red-500 */ }
        .status-warning { background-color: #F59E0B; /* amber-500 */ }
        .btn-scan-again { background-color: #2c3856; color: white; }
        .btn-scan-again:hover { background-color: #1a2233; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md mx-auto">
        <div class="text-center mb-6">
            <img src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Logotipo Minmer Global" class="mx-auto h-16 w-auto mb-4">
        </div>

        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
            <div class="p-8 text-center status-{{ $status }} text-white">
                @if($status === 'success')
                    <i class="fas fa-check-circle fa-4x"></i>
                @elseif($status === 'error')
                    <i class="fas fa-times-circle fa-4x"></i>
                @else
                    <i class="fas fa-exclamation-triangle fa-4x"></i>
                @endif
                <h1 class="text-2xl font-bold mt-4">{{ $message }}</h1>
            </div>

            <div class="p-6 bg-gray-50">
                <h2 class="text-lg font-semibold text-[#2b2b2b] mb-4">Detalles de la Visita</h2>
                <div class="space-y-3 text-sm text-[#666666]">
                    <p><strong>Visitante:</strong> {{ $visit->visitor_name }} {{ $visit->visitor_last_name }}</p>
                    <p><strong>Empresa:</strong> {{ $visit->company ?? 'N/A' }}</p>
                    <p><strong>Fecha y Hora Programada:</strong> {{ \Carbon\Carbon::parse($visit->visit_datetime)->format('d/m/Y h:i A') }}</p>
                    <p><strong>Estatus Final:</strong> <span class="font-bold text-black">{{ $visit->status }}</span></p>
                </div>

                {{-- ✅ INICIO: SECCIÓN DE ACOMPAÑANTES --}}
                @if($visit->companions && count($visit->companions) > 0)
                <div class="mt-6 pt-4 border-t">
                    <h2 class="text-lg font-semibold text-[#2b2b2b] mb-3">Acompañantes Autorizados</h2>
                    <ul class="space-y-2 text-sm text-[#666666] list-disc list-inside">
                        @foreach($visit->companions as $companion)
                            <li>{{ $companion }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                {{-- ✅ FIN: SECCIÓN DE ACOMPAÑANTES --}}

            </div>
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('visits.scan.page') }}" class="inline-block px-8 py-3 rounded-lg font-semibold btn-scan-again transition">
                <i class="fas fa-qrcode mr-2"></i>Escanear Otro QR
            </a>
        </div>
    </div>
</body>
</html>