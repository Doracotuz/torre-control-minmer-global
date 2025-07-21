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
            border-radius: 10px;
            overflow: hidden;
            border: 4px solid #e5e7eb;
        }
        .btn-start-scan {
            background-color: #2c3856;
            color: white;
            transition: background-color 0.3s ease;
        }
        .btn-start-scan:hover {
            background-color: #1a2233;
        }
        /* Oculta el footer de la librería del escaner */
        #qr-reader__dashboard_section_csr { display: none; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="w-full max-w-2xl mx-auto p-4">
        <div class="text-center mb-6">
            <img src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Logotipo Minmer Global" class="mx-auto h-16 w-auto mb-4">
            <h1 class="text-3xl font-bold text-[#2b2b2b]">Escaner de Acceso</h1>
            <p class="text-[#666666]">Presiona el botón para iniciar la cámara.</p>
        </div>

        {{-- Alerta de conexión no segura (solo se muestra si no es HTTPS) --}}
        <div id="https-warning" class="hidden bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded-md" role="alert">
            <p class="font-bold">Aviso de Seguridad</p>
            <p>Para usar la cámara en un dispositivo móvil, esta página debe cargarse a través de una conexión segura (HTTPS).</p>
        </div>

        <div id="scanner-container" class="bg-white p-6 rounded-lg shadow-md text-center">
            <div id="qr-reader" class="w-full"></div>
            <div id="scanner-status" class="mt-4 text-gray-600 font-semibold"></div>
            <button id="start-scan-btn" class="mt-4 px-6 py-3 rounded-lg font-semibold btn-start-scan">
                <i class="fas fa-camera mr-2"></i> Iniciar Escáner
            </button>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startScanBtn = document.getElementById('start-scan-btn');
            const scannerStatus = document.getElementById('scanner-status');
            const httpsWarning = document.getElementById('https-warning');
            let lastScannedUrl = null;

            // Muestra la alerta si la conexión no es segura (y no es localhost)
            if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                httpsWarning.classList.remove('hidden');
            }

            const html5QrCode = new Html5Qrcode("qr-reader");

            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                if (decodedText === lastScannedUrl) return;
                lastScannedUrl = decodedText;

                scannerStatus.innerHTML = `<i class="fas fa-check-circle text-green-500"></i> QR Detectado. Redirigiendo...`;
                
                // Detener el escáner
                html5QrCode.stop().then(() => {
                    window.location.href = decodedText;
                }).catch(err => {
                    console.error("Fallo al detener el escáner.", err);
                    window.location.href = decodedText; // Redirigir de todas formas
                });
            };

            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                facingMode: "environment" // Usar la cámara trasera
            };

            startScanBtn.addEventListener('click', () => {
                startScanBtn.style.display = 'none';
                scannerStatus.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Solicitando permiso de cámara...`;

                // Iniciar el escáner
                html5QrCode.start(
                    { facingMode: "environment" }, // Pedir la cámara trasera
                    config,
                    qrCodeSuccessCallback,
                    (errorMessage) => {
                        // Se ignora el error de "QR no encontrado"
                    })
                .catch((err) => {
                    // Mostrar un error si el permiso es denegado
                    scannerStatus.innerHTML = `<i class="fas fa-times-circle text-red-500"></i> Error: No se pudo acceder a la cámara. Por favor, concede los permisos.`;
                    console.error(`No se pudo iniciar el escáner: ${err}`);
                    startScanBtn.style.display = 'block'; // Mostrar el botón de nuevo
                });
            });
        });
    </script>
</body>
</html>
