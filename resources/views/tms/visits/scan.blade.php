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
            overflow: hidden; /* Para que el video no se salga del borde redondeado */
        }
        #qr-reader-results { font-family: monospace; }
        .btn-start-scan {
            background-color: #2c3856;
            color: white;
            transition: background-color 0.3s ease;
        }
        .btn-start-scan:hover {
            background-color: #1a2233;
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

        <div id="scanner-container" class="bg-white p-6 rounded-lg shadow-md text-center">
            <div id="qr-reader"></div>
            <button id="start-scan-btn" class="mt-4 px-6 py-3 rounded-lg font-semibold btn-start-scan">
                <i class="fas fa-camera mr-2"></i> Iniciar Escáner
            </button>
        </div>

        <div id="result-container" class="mt-6 text-center">
            {{-- Los resultados de la validación se mostrarán aquí --}}
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const resultContainer = document.getElementById('result-container');
            const startScanBtn = document.getElementById('start-scan-btn');
            let lastScannedUrl = null;

            const html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader", 
                { 
                    fps: 10, 
                    qrbox: (viewfinderWidth, viewfinderHeight) => {
                        // Hacemos el cuadro de escaneo un 80% del ancho del contenedor
                        const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                        return {
                            width: minEdge * 0.8,
                            height: minEdge * 0.8
                        };
                    },
                    // Pedimos explícitamente la cámara trasera del móvil
                    facingMode: "environment" 
                },
                false // verbose
            );

            function onScanSuccess(decodedText, decodedResult) {
                if (decodedText === lastScannedUrl) return;
                lastScannedUrl = decodedText;
                
                // Detener el escáner por completo
                html5QrcodeScanner.clear().catch(error => {
                    console.error("Fallo al limpiar el escáner.", error);
                });

                resultContainer.innerHTML = `
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                        <p class="mt-2 text-gray-600 font-semibold">Validando QR...</p>
                    </div>`;
                
                window.location.href = decodedText;
            }

            function onScanFailure(error) {
                // Se ignora para no mostrar errores constantes si no encuentra un QR
            }

            // El escáner solo se renderiza cuando el usuario hace clic en el botón
            startScanBtn.addEventListener('click', () => {
                startScanBtn.style.display = 'none'; // Ocultar el botón
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            });
        });
    </script>
</body>
</html>
