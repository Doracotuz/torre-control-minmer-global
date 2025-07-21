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
            let html5QrCode = new Html5Qrcode("qr-reader");

            // Muestra la alerta si la conexión no es segura (y no es localhost)
            if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                httpsWarning.classList.remove('hidden');
            }

            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                if (decodedText === lastScannedUrl) return;
                lastScannedUrl = decodedText;

                scannerStatus.innerHTML = `<i class="fas fa-check-circle text-green-500"></i> QR Detectado. Redirigiendo...`;
                
                html5QrCode.stop().then(() => {
                    window.location.href = decodedText;
                }).catch(err => {
                    console.error("Fallo al detener el escáner.", err);
                    window.location.href = decodedText;
                });
            };

            const config = { 
                fps: 10,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    const size = Math.min(minEdge * 0.8, 300); // Limitar tamaño máximo para móviles
                    return { width: size, height: size };
                }
            };

            startScanBtn.addEventListener('click', async () => {
                try {
                    startScanBtn.style.display = 'none';
                    scannerStatus.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Solicitando permiso de cámara...`;
                    
                    // 1. Verificar permisos primero
                    const stream = await navigator.mediaDevices.getUserMedia({ 
                        video: {
                            facingMode: { ideal: "environment" } // Preferir cámara trasera
                        } 
                    });
                    
                    // Detener el stream inmediatamente (solo necesitamos verificar permisos)
                    stream.getTracks().forEach(track => track.stop());
                    
                    // 2. Obtener cámaras disponibles
                    const cameras = await Html5Qrcode.getCameras();
                    scannerStatus.innerHTML = `<i class="fas fa-camera"></i> Permiso concedido. Iniciando...`;
                    
                    if (cameras && cameras.length) {
                        // Priorizar cámara trasera
                        let cameraId = cameras.find(cam => 
                            cam.label.toLowerCase().includes('back') || 
                            cam.label.toLowerCase().includes('rear') || 
                            cam.label.toLowerCase().includes('trasera')
                        )?.id || cameras[0].id;
                        
                        // 3. Iniciar escáner
                        await html5QrCode.start(
                            cameraId, 
                            config,
                            qrCodeSuccessCallback,
                            (errorMessage) => {
                                // Ignorar errores de "QR no encontrado"
                            }
                        );
                        
                    } else {
                        throw new Error('No se encontraron cámaras en este dispositivo');
                    }
                    
                } catch (err) {
                    console.error(`Error: ${err}`);
                    scannerStatus.innerHTML = `
                        <i class="fas fa-times-circle text-red-500"></i> 
                        ${err.message.includes('Permission denied') ? 
                            'Permiso denegado. Por favor habilita el acceso a la cámara.' : 
                            'Error al acceder a la cámara: ' + err.message}
                        <br>
                        <button onclick="window.location.reload()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded">
                            Reintentar
                        </button>
                    `;
                    startScanBtn.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>
