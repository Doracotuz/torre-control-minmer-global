
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Cookies - Estrategias y Soluciones Minmer Global</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Animación de entrada para secciones */
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.8s ease-out forwards;
        }
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        /* Transiciones suaves para enlaces y botones */
        .hover-effect:hover {
            transform: translateY(-2px);
            transition: transform 0.3s ease, color 0.3s ease;
        }
        /* Estilo para la tabla */
        .elegant-table th, .elegant-table td {
            border: 1px solid #e5e7eb;
            padding: 1rem;
            transition: background-color 0.3s ease;
        }
        .elegant-table th {
            background-color:rgb(40, 56, 86);
            color: white;
        }
        .elegant-table tr:hover {
            background-color: #f3f4f6;
        }
        /* Fondo degradado */
        body {
            background: linear-gradient(to bottom,rgb(40, 56, 86), #e5e7eb);
        }
        /* Estilo para el logotipo */
        .logo {
            opacity: 0;
            animation: logoFadeIn 1s ease-out 0.5s forwards;
        }
        @keyframes logoFadeIn {
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <!-- Logotipo -->
        <div class="flex justify-center mb-8">
            <img src="{{ asset('images/logoBlanco.png') }}" alt="Logo Estrategias y Soluciones Minmer Global" class="h-24 logo">
        </div>

        <!-- Título principal -->
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8 fade-in" style="color: white; animation-delay: 0.5s;">Política de Cookies</h1>

        <div class="bg-white shadow-xl rounded-lg p-8 fade-in" style="animation-delay: 0.2s;">
            <!-- Introducción -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Introducción</h2>
                <p class="text-gray-600 leading-relaxed">
                    En Estrategias y Soluciones Minmer Global, utilizamos cookies y tecnologías similares para mejorar su experiencia en nuestro sitio web, analizar el rendimiento de nuestros servicios y personalizar el contenido que ofrecemos. Esta Política de Cookies explica qué son las cookies, cómo las usamos, y cómo puede gestionarlas.
                </p>
            </section>

            <!-- ¿Qué son las cookies? -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">¿Qué son las cookies?</h2>
                <p class="text-gray-600 leading-relaxed">
                    Las cookies son pequeños archivos de texto que se almacenan en su dispositivo (ordenador, teléfono, tableta) cuando visita nuestro sitio web. Estas cookies nos ayudan a:
                </p>
                <ul class="list-disc pl-5 text-gray-600 mt-2">
                    <li>Hacer que nuestro sitio web funcione correctamente.</li>
                    <li>Recordar sus preferencias y configuraciones.</li>
                    <li>Analizar cómo los usuarios interactúan con nuestro sitio.</li>
                    <li>Mostrar contenido personalizado, como anuncios relevantes.</li>
                </ul>
            </section>

            <!-- Tipos de Cookies que Utilizamos -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Tipos de Cookies que Utilizamos</h2>
                <table class="w-full elegant-table">
                    <thead>
                        <tr>
                            <th class="text-left">Tipo de Cookie</th>
                            <th class="text-left">Finalidad</th>
                            <th class="text-left">Duración</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Cookies Esenciales</td>
                            <td>Necesarias para el funcionamiento básico del sitio, como la navegación y el acceso a áreas seguras.</td>
                            <td>Durante la sesión o hasta 1 año.</td>
                        </tr>
                        <tr>
                            <td>Cookies de Rendimiento</td>
                            <td>Recopilan información anónima sobre cómo los usuarios utilizan nuestro sitio, ayudándonos a mejorar su funcionamiento.</td>
                            <td>Hasta 2 años.</td>
                        </tr>
                        <tr>
                            <td>Cookies de Funcionalidad</td>
                            <td>Permiten recordar sus preferencias (como idioma o región) para una experiencia personalizada.</td>
                            <td>Hasta 1 año.</td>
                        </tr>
                        <tr>
                            <td>Cookies de Publicidad</td>
                            <td>Nos permiten mostrar anuncios relevantes según sus intereses, tanto en nuestro sitio como en sitios de terceros.</td>
                            <td>Hasta 2 años, dependiendo del proveedor.</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Base Jurídica -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Base Jurídica para el Uso de Cookies</h2>
                <ul class="list-disc pl-5 text-gray-600 mt-2">
                    <li><strong>Cookies esenciales:</strong> Necesarias para el funcionamiento del sitio y basadas en nuestro interés legítimo en ofrecer un servicio funcional.</li>
                    <li><strong>Cookies no esenciales (rendimiento, funcionalidad, publicidad):</strong> Se utilizan solo con su consentimiento, el cual puede retirar en cualquier momento.</li>
                </ul>
            </section>

            <!-- Gestión de Cookies -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">¿Cómo Gestionamos las Cookies?</h2>
                <p class="text-gray-600 leading-relaxed">
                    Al visitar nuestro sitio web, le mostramos un banner de cookies que le permite aceptar o rechazar las cookies no esenciales. Puede gestionar sus preferencias en cualquier momento a través de la configuración de cookies en nuestro sitio.
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    También puede configurar su navegador para bloquear o eliminar cookies, aunque esto puede afectar la funcionalidad del sitio. Consulte las instrucciones de su navegador para más detalles:
                </p>
                <ul class="list-disc pl-5 text-gray-600 mt-2">
                    <li><a href="https://support.google.com/chrome/answer/95647" class="text-blue-600 hover:text-blue-800 hover-effect" target="_blank">Google Chrome</a></li>
                    <li><a href="https://support.mozilla.org/es/kb/cookies-informacion-que-los-sitios-web-guardan-en-" class="text-blue-600 hover:text-blue-800 hover-effect" target="_blank">Mozilla Firefox</a></li>
                    <li><a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" class="text-blue-600 hover:text-blue-800 hover-effect" target="_blank">Safari</a></li>
                    <li><a href="https://support.microsoft.com/es-es/microsoft-edge/eliminar-cookies-en-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" class="text-blue-600 hover:text-blue-800 hover-effect" target="_blank">Microsoft Edge</a></li>
                </ul>
            </section>

            <!-- Cookies de Terceros -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Cookies de Terceros</h2>
                <p class="text-gray-600 leading-relaxed">
                    Algunas cookies son gestionadas por terceros (por ejemplo, servicios de análisis como Google Analytics o plataformas publicitarias). Estos terceros están sujetos a sus propias políticas de privacidad, y le recomendamos revisarlas.
                </p>
            </section>

            <!-- Contacto -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">¿Cómo Contactarnos?</h2>
                <p class="text-gray-600 leading-relaxed">
                    Si tiene preguntas sobre esta Política de Cookies, puede contactarnos en:
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Estrategias y Soluciones Minmer Global<br>
                    Blvd. de las Ciencias No. Ext. 3015 No. Int. 3015<br>
                    Juriquilla 76230, Querétaro, México<br>
                    Correo: <a href="mailto:contacto@minmerglobal.com" class="text-blue-600 hover:text-blue-800 hover-effect">contacto@minmerglobal.com</a>
                </p>
            </section>

            <!-- Actualizaciones -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Actualizaciones de Esta Política</h2>
                <p class="text-gray-600 leading-relaxed">
                    Podemos actualizar esta Política de Cookies periódicamente para reflejar cambios en nuestras prácticas o en la legislación aplicable. Le notificaremos de cualquier cambio significativo a través de nuestro sitio web.
                </p>
            </section>
        </div>
    </div>
</body>
</html>
