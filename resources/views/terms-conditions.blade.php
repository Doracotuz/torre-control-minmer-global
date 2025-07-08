
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Condiciones - Estrategias y Soluciones Minmer Global</title>
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
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8 fade-in" style="color: white; animation-delay: 0.5s;">Términos y Condiciones</h1>

        <div class="bg-white shadow-xl rounded-lg p-8 fade-in" style="animation-delay: 0.2s;">
            <!-- Introducción -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Introducción</h2>
                <p class="text-gray-600 leading-relaxed">
                    Bienvenido a los servicios proporcionados por Estrategias y Soluciones Minmer Global. Al acceder o utilizar nuestro sitio web y servicios, usted acepta cumplir con los presentes Términos y Condiciones, que rigen su relación con nosotros.
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Estos Términos se aplican a todos los usuarios, incluidos, entre otros, los visitantes, clientes y socios comerciales. Si no está de acuerdo con alguna parte de estos Términos, le recomendamos que no utilice nuestros servicios.
                </p>
            </section>

            <!-- Uso de los Servicios -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Uso de los Servicios</h2>
                <p class="text-gray-600 leading-relaxed">
                    Usted se compromete a utilizar nuestros servicios de manera lícita y de acuerdo con todas las leyes aplicables. Está prohibido:
                </p>
                <ul class="list-disc pl-5 text-gray-600 mt-2">
                    <li>Utilizar los servicios para fines ilegales o no autorizados.</li>
                    <li>Intentar acceder sin autorización a nuestros sistemas o redes.</li>
                    <li>Distribuir contenido que sea difamatorio, ofensivo o que infrinja los derechos de terceros.</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Nos reservamos el derecho de suspender o cancelar su acceso a los servicios si incumple estas condiciones.
                </p>
            </section>

            <!-- Cuentas de Usuario -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Cuentas de Usuario</h2>
                <p class="text-gray-600 leading-relaxed">
                    Para acceder a ciertas funciones de nuestros servicios, puede ser necesario crear una cuenta. Usted es responsable de:
                </p>
                <ul class="list-disc pl-5 text-gray-600 mt-2">
                    <li>Proporcionar información precisa y actualizada durante el registro.</li>
                    <li>Mantener la confidencialidad de sus credenciales de acceso.</li>
                    <li>Notificarnos inmediatamente sobre cualquier uso no autorizado de su cuenta.</li>
                </ul>
            </section>

            <!-- Propiedad Intelectual -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Propiedad Intelectual</h2>
                <p class="text-gray-600 leading-relaxed">
                    Todo el contenido disponible en nuestro sitio web, incluidos textos, imágenes, logotipos y software, es propiedad de Estrategias y Soluciones Minmer Global o de sus licenciantes y está protegido por leyes de propiedad intelectual.
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    No está permitido copiar, distribuir o modificar dicho contenido sin nuestro consentimiento expreso por escrito.
                </p>
            </section>

            <!-- Limitación de Responsabilidad -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Limitación de Responsabilidad</h2>
                <p class="text-gray-600 leading-relaxed">
                    Estrategias y Soluciones Minmer Global no será responsable de daños indirectos, incidentales o consecuentes derivados del uso de nuestros servicios, salvo que se disponga lo contrario por la ley aplicable.
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    No garantizamos que nuestros servicios estén libres de errores o interrupciones, pero haremos esfuerzos razonables para mantener su disponibilidad.
                </p>
            </section>

            <!-- Modificaciones de los Términos -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Modificaciones de los Términos</h2>
                <p class="text-gray-600 leading-relaxed">
                    Podemos actualizar estos Términos y Condiciones periódicamente. Le notificaremos sobre cambios significativos a través de nuestro sitio web o por correo electrónico. El uso continuado de nuestros servicios tras dichas modificaciones implica su aceptación de los nuevos términos.
                </p>
            </section>

            <!-- Contacto -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Contacto</h2>
                <p class="text-gray-600 leading-relaxed">
                    Si tiene preguntas sobre estos Términos y Condiciones, contáctenos en:
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Estrategias y Soluciones Minmer Global<br>
                    Blvd. de las Ciencias No. Ext. 3015 No. Int. 3015<br>
                    Juriquilla 76230, Querétaro, México<br>
                    Correo: <a href="mailto:contacto@minmerglobal.com" class="text-blue-600 hover:text-blue-800 hover-effect">contacto@minmerglobal.com</a>
                </p>
            </section>

            <!-- Ley Aplicable -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Ley Aplicable</h2>
                <p class="text-gray-600 leading-relaxed">
                    Estos Términos y Condiciones se rigen por la legislación del país donde opera Estrategias y Soluciones Minmer Global. Cualquier disputa será resuelta en los tribunales competentes de la jurisdicción correspondiente.
                </p>
            </section>
        </div>
    </div>
</body>
</html>
