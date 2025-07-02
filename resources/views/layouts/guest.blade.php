<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Google Fonts: Raleway Extrabold and Montserrat Regular -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Montserrat', sans-serif;
                background-color: #0b101c; /* Fondo azul marino muy oscuro, casi negro */
                overflow: hidden;
                cursor: default;
            }

            .login-container {
                position: relative;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1;
            }

            canvas {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 0; /* Detrás del contenido del login */
            }

            /* Estilos para el contenedor del formulario */
            .form-card {
                background-color: rgb(255, 255, 255); /* Fondo muy transparente */
                backdrop-filter: blur(15px); /* Desenfoque de cristal esmerilado */
                border-radius: 20px;
                padding: 50px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
                border: 1px solid rgba(255, 255, 255, 0.05);
                animation: slide-in 1s ease-out forwards;
            }

            @keyframes slide-in {
                0% { transform: translateY(50px); opacity: 0; }
                100% { transform: translateY(0); opacity: 1; }
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <!-- Canvas para el fondo Matrix -->
            <canvas id="matrixCanvas"></canvas>

            <!-- Contenido principal (formulario de login) -->
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 form-card overflow-hidden">
                {{ $slot }}
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const canvas = document.getElementById('matrixCanvas');
                const ctx = canvas.getContext('2d');

                let matrix = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789@#$%^&*()_+{}[]|:;"<>,./?~`';
                matrix = matrix.split('');

                const fontSize = 16;
                let columns = canvas.width / fontSize;
                let drops = [];

                // Inicializar drops para cada columna
                for (let x = 0; x < columns; x++) {
                    drops[x] = 1;
                }

                // Función para redimensionar el canvas
                const resizeCanvas = () => {
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                    columns = canvas.width / fontSize;
                    // Re-inicializar drops para las nuevas columnas si es necesario
                    for (let x = 0; x < columns; x++) {
                        if (typeof drops[x] === 'undefined') {
                            drops[x] = 1;
                        }
                    }
                };

                window.addEventListener('resize', resizeCanvas);
                resizeCanvas(); // Establecer tamaño inicial

                let frameCount = 0;
                const animationSpeed = 2; // Dibuja cada 2 frames para un movimiento más rápido

                function draw() {
                    // Fondo semitransparente para el efecto de "rastro"
                    ctx.fillStyle = 'rgba(11, 16, 28, 0.05)'; /* Color de fondo del body con transparencia */
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    for (let i = 0; i < drops.length; i++) {
                        const text = matrix[Math.floor(Math.random() * matrix.length)];

                        // Color de los caracteres que caen (azul de la guía de estilo)
                        ctx.fillStyle = '#2c3856'; 
                        ctx.font = `${fontSize}px monospace`;
                        ctx.fillText(text, i * fontSize, drops[i] * fontSize);

                        // Reiniciar la gota cuando llega al final o aleatoriamente para el efecto de aparición/desaparición
                        if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                            drops[i] = 0; // Reinicia arriba
                            ctx.fillStyle = 'rgba(255, 255, 255, 0.04)'; // Color de la figura original para que aparezca más "blanca"
                        } else if (Math.random() > 0.99) { // Aleatoriamente hacer que desaparezca y reaparezca
                            drops[i] = 0;
                            ctx.fillStyle = 'rgba(255, 255, 255, 0.04)';
                        } else {
                            drops[i]++;
                        }
                    }
                }

                // Bucle de animación con requestAnimationFrame y control de velocidad
                function animate() {
                    requestAnimationFrame(animate);
                    frameCount++;
                    if (frameCount % animationSpeed === 0) {
                        draw();
                    }
                }
                animate(); // Iniciar la animación
            });
        </script>
    </body>
</html>