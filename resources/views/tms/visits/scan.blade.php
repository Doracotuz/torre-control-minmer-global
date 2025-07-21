<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escaner de Visitas - Minmer Global</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body { background-color: #f3f4f6; }
        #qr-reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border: 4px solid #e5e7eb;
            border-radius: 10px;
        }
        #qr-reader-results { font-family: monospace; }
        .result-card {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="w-full max-w-2xl mx-auto p-4">
        <div class="text-center mb-6">
            <img src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Logotipo Minmer Global" class="mx-auto h-16 w-auto mb-4">
            <h1 class="text-3xl font-bold text-[#2b2b2b]">Escaner de Acceso</h1>
            <p class="text-[#666666]">Apunta la cámara al código QR de la invitación.</p>
        </div>

        <div id="qr-reader"></div>

        <div id="result-container" class="mt-6 text-center">
            {{-- Los resultados de la validación se mostrarán aquí --}}
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <script>
        const resultContainer = document.getElementById('result-container');
        let lastScannedUrl = null;

        function onScanSuccess(decodedText, decodedResult) {
            // Para evitar múltiples escaneos del mismo QR
            if (decodedText === lastScannedUrl) {
                return;
            }
            lastScannedUrl = decodedText;
            
            // Pausar el escaner para procesar el resultado
            html5QrcodeScanner.pause();

            // Mostrar un estado de "Cargando..."
            resultContainer.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                    <p class="mt-2 text-gray-600 font-semibold">Validando QR...</p>
                </div>`;
            
            // Redirigir a la página de validación
            window.location.href = decodedText;
        }

        function onScanFailure(error) {
            // No hacer nada en caso de fallo, para no molestar al usuario
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", 
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                supportedScanTypes: [
                    Html5QrcodeScanType.SCAN_TYPE_CAMERA
                ]
            },
            false // verbose
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
</body>
</html>
