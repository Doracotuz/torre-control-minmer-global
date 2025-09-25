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
        .hidden-camera-input { display: none; }
        .preview-container {
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
            border: 2px dashed #e5e7eb;
            border-radius: 10px;
        }
        #imagePreview {
            max-height: 300px;
            object-fit: contain;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-2xl mx-auto p-4">
        <div class="text-center mb-6">
            <img src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Logotipo Minmer Global" class="mx-auto h-16 w-auto mb-4">
            <h1 class="text-3xl font-bold text-[#2b2b2b]">Escaner de Acceso</h1>
            <p class="text-[#666666]">Escanea el código QR con tu cámara.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <input type="file" id="cameraInput" accept="image/*" capture="environment" class="hidden-camera-input">
            
            <div class="preview-container hidden" id="previewContainer">
                <img id="imagePreview" class="w-full rounded-lg">
                <div class="mt-2 text-gray-600" id="scanStatus"></div>
            </div>

            <button id="startScanBtn" class="mt-4 px-6 py-3 rounded-lg font-semibold bg-[#2c3856] text-white hover:bg-[#1a2233] transition">
                <i class="fas fa-camera mr-2"></i> Iniciar Escáner
            </button>

            <button id="processBtn" class="mt-4 px-6 py-3 rounded-lg font-semibold bg-green-600 text-white hover:bg-green-700 transition hidden">
                <i class="fas fa-search mr-2"></i> Procesar QR
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cameraInput = document.getElementById('cameraInput');
            const startScanBtn = document.getElementById('startScanBtn');
            const processBtn = document.getElementById('processBtn');
            const previewContainer = document.getElementById('previewContainer');
            const imagePreview = document.getElementById('imagePreview');
            const scanStatus = document.getElementById('scanStatus');
            
            let capturedImage = null;

            startScanBtn.addEventListener('click', () => {
                cameraInput.click();
            });

            cameraInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    const file = e.target.files[0];
                    const reader = new FileReader();
                    
                    reader.onload = function(event) {
                        capturedImage = event.target.result;
                        imagePreview.src = capturedImage;
                        previewContainer.classList.remove('hidden');
                        startScanBtn.classList.add('hidden');
                        processBtn.classList.remove('hidden');
                        scanStatus.innerHTML = '<i class="fas fa-check-circle text-blue-500"></i> Imagen capturada. Haz clic en "Procesar QR"';
                    };
                    
                    reader.readAsDataURL(file);
                }
            });

            processBtn.addEventListener('click', function() {
                if (!capturedImage) return;
                
                scanStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando código QR...';
                
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    context.drawImage(img, 0, 0, canvas.width, canvas.height);
                    
                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                    
                    if (code) {
                        scanStatus.innerHTML = '<i class="fas fa-check-circle text-green-500"></i> QR detectado! Redirigiendo...';
                        setTimeout(() => {
                            window.location.href = code.data;
                        }, 1000);
                    } else {
                        scanStatus.innerHTML = '<i class="fas fa-times-circle text-red-500"></i> No se encontró QR. Intenta de nuevo.';
                        processBtn.classList.add('hidden');
                        startScanBtn.classList.remove('hidden');
                    }
                };
                
                img.src = capturedImage;
            });
        });
    </script>
</body>
</html>