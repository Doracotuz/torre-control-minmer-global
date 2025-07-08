
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad - Estrategias y Soluciones Minmer Global</title>
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
            background-color: rgba(40, 56, 86, 1);
            color: white;
        }
        .elegant-table tr:hover {
            background-color: #f3f4f6;
        }
        /* Fondo degradado */
        body {
            background: linear-gradient(to bottom, rgba(40, 56, 86, 1), #e5e7eb);
        }
        /* Estilo para el logotipo */
        .logo {
            opacity: 0;
            animation: logoFadeIn 3s ease-out 0.1s forwards;
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
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8 fade-in" style="color: white; animation-delay: 0.5s;">Política de Privacidad</h1>

        <div class="bg-white shadow-xl rounded-lg p-8 fade-in" style="animation-delay: 0.5s;">
            <!-- Introducción -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Introducción</h2>
                <p class="text-gray-600 leading-relaxed">
                    La protección de los Datos Personales y la privacidad de nuestros usuarios es una prioridad para Estrategias y Soluciones Minmer Global.
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Como Controlador de Datos, Estrategias y Soluciones Minmer Global se compromete a cumplir con las normativas aplicables en materia de protección de datos personales, tanto nacionales como internacionales, y a implementar medidas adecuadas para garantizar la confidencialidad y seguridad de sus Datos Personales.
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    El propósito de esta Política de Privacidad es informarle sobre por qué procesamos sus datos, cuánto tiempo los conservamos, cuáles son sus derechos y cómo puede ejercerlos.
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Si desea información sobre el uso de cookies, consulte nuestra <a href="/cookies-policy" class="text-blue-600 hover:text-blue-800 hover-effect">Política de Cookies</a>.
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Cualquier término con mayúscula inicial no definido en esta Política tiene el significado indicado en nuestras <a href="/terms-conditions" class="text-blue-600 hover:text-blue-800 hover-effect">Condiciones de Uso</a>.
                </p>
            </section>

            <!-- Finalidades del procesamiento -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">¿Por qué Estrategias y Soluciones Minmer Global procesa sus datos?</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    Estrategias y Soluciones Minmer Global procesa sus Datos Personales para los siguientes fines, cada uno respaldado por una base jurídica conforme a la legislación aplicable:
                </p>
                <table class="w-full elegant-table">
                    <thead>
                        <tr>
                            <th class="text-left">Tipo</th>
                            <th class="text-left">Finalidades</th>
                            <th class="text-left">Base jurídica</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Prestación de nuestros servicios</td>
                            <td>
                                <ul class="list-disc pl-5">
                                    <li>Permitir el acceso a nuestros servicios en línea.</li>
                                    <li>Crear y gestionar cuentas de usuario.</li>
                                    <li>Facilitar la comunicación con usted sobre nuestros servicios o en respuesta a sus solicitudes.</li>
                                    <li>Compartir datos con proveedores de servicios que realizan funciones en nuestro nombre, bajo obligaciones contractuales que respetan esta Política.</li>
                                    <li>Promocionar nuestros servicios a socios comerciales actuales o potenciales.</li>
                                </ul>
                            </td>
                            <td>
                                Estos tratamientos son necesarios para la ejecución del contrato entre usted y Estrategias y Soluciones Minmer Global, o para cumplir con intereses legítimos de Estrategias y Soluciones Minmer Global en la prestación de servicios.
                            </td>
                        </tr>
                        <tr>
                            <td>Gestión de cookies</td>
                            <td>
                                Consulte nuestra <a href="/cookies-policy" class="text-blue-600 hover:text-blue-800 hover-effect">Política de Cookies</a> para más detalles.
                            </td>
                            <td>
                                Basado en el consentimiento del usuario cuando sea requerido por la ley, o en el interés legítimo de Estrategias y Soluciones Minmer Global para mejorar nuestros servicios.
                            </td>
                        </tr>
                        <tr>
                            <td>Gestión de solicitudes de protección de datos</td>
                            <td>
                                <ul class="list-disc pl-5">
                                    <li>Recibir y procesar solicitudes relacionadas con sus derechos de protección de datos.</li>
                                    <li>Gestionar dichas solicitudes en colaboración con los departamentos pertinentes.</li>
                                </ul>
                            </td>
                            <td>
                                Necesario para cumplir con obligaciones legales definidas por la normativa aplicable en protección de datos.
                            </td>
                        </tr>
                        <tr>
                            <td>Cumplimiento de obligaciones legales</td>
                            <td>
                                <ul class="list-disc pl-5">
                                    <li>Mantener registros de las actividades relacionadas con el uso de nuestro sitio web.</li>
                                    <li>Informarle sobre cambios en esta Política de Privacidad.</li>
                                    <li>Cumplir con órdenes judiciales, requisitos legales o defender derechos legales.</li>
                                </ul>
                            </td>
                            <td>
                                Necesario para cumplir con una obligación legal definida por la normativa aplicable en materia de protección de datos, protección al consumidor o relacionada con nuestros servicios.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Tiempo de retención -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">¿Cuánto tiempo conserva Estrategias y Soluciones Minmer Global sus datos?</h2>
                <p class="text-gray-600 leading-relaxed">
                    Sus Datos Personales se conservarán únicamente durante el tiempo necesario para cumplir con los fines descritos, conforme a las leyes aplicables. En particular:
                </p>
                <ul class="list-disc pl-5 text-gray-600 mt-2">
                    <li>Conservaremos sus datos mientras mantengamos una relación activa con usted, como una cuenta activa en nuestro sitio web.</li>
                    <li>Podemos conservar sus datos por períodos más largos si así lo exige la ley o si es necesario para proteger nuestros intereses legales (por ejemplo, en relación con plazos de prescripción, litigios o investigaciones regulatorias).</li>
                    <li>Los datos relacionados con actividades de marketing se conservarán durante un máximo de 3 años desde el fin de nuestra relación con usted o desde su último contacto, según la normativa aplicable.</li>
                </ul>
            </section>

            <!-- Compartir datos -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">¿Con quién comparte Estrategias y Soluciones Minmer Global sus datos?</h2>
                <p class="text-gray-600 leading-relaxed">
                    Estrategias y Soluciones Minmer Global puede compartir sus Datos Personales con:
                </p>
                <ul class="list-disc pl-5 text-gray-600 mt-2">
                    <li>Proveedores de servicios y socios comerciales que nos asisten en la prestación de servicios, siempre bajo contratos que garantizan la protección de sus datos.</li>
                    <li>Autoridades gubernamentales, judiciales o terceros autorizados, si así lo exige o permite la ley.</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Nos aseguramos de que cualquier transferencia de datos se realice conforme a la legislación aplicable y bajo medidas técnicas y organizativas que garanticen su seguridad.
                </p>
            </section>

            <!-- Venta de datos -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">¿Se pueden vender mis Datos Personales?</h2>
                <p class="text-gray-600 leading-relaxed">
                    Estrategias y Soluciones Minmer Global no vende sus Datos Personales, salvo que se indique lo contrario en acuerdos específicos.
                </p>
            </section>

            <!-- Protección de datos -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">¿Cómo protege Estrategias y Soluciones Minmer Global sus Datos Personales?</h2>
                <p class="text-gray-600 leading-relaxed">
                    Implementamos medidas de seguridad físicas, organizativas e informáticas para proteger sus datos, incluyendo:
                </p>
                <ul class="list-disc pl-5 text-gray-600 mt-2">
                    <li>Seguridad de acceso a nuestras instalaciones.</li>
                    <li>Formación del personal y procedimientos internos.</li>
                    <li>Herramientas de seguridad informática como antivirus, gestión segura de contraseñas y copias de seguridad.</li>
                    <li>Acceso restringido a los datos solo para personal autorizado.</li>
                </ul>
            </section>

            <!-- Derechos del usuario -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">¿Cuáles son sus derechos y cómo puede ejercerlos?</h2>
                <p class="text-gray-600 leading-relaxed">
                    Como usuario, tiene derecho a:
                </p>
                <ul class="list-disc pl-5 text-gray-600 mt-2">
                    <li>Acceder, rectificar o solicitar la eliminación de sus Datos Personales.</li>
                    <li>Restringir el procesamiento de sus datos.</li>
                    <li>Solicitar la portabilidad de sus datos en un formato legible por máquina.</li>
                    <li>Retirar su consentimiento en cualquier momento, cuando el procesamiento se base en él.</li>
                    <li>Presentar una reclamación ante la autoridad de protección de datos competente.</li>
                </ul>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Para ejercer sus derechos, puede contactarnos a través de nuestro formulario en línea o enviando un correo a <a href="mailto:contacto@minmerglobal.com" class="text-blue-600 hover:text-blue-800 hover-effect">contacto@minmerglobal.com</a>, o por correo postal a:
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Estrategias y Soluciones Minmer Global<br>
                    Blvd. de las Ciencias No. Ext. 3015 No. Int. 3015<br>
                    Juriquilla 76230, Querétaro, México
                </p>
                <p class="text-gray-600 leading-relaxed mt-2">
                    Es posible que le pidamos verificar su identidad antes de procesar su solicitud. Nos reservamos el derecho a rechazar solicitudes infundadas o excesivas, conforme a la ley.
                </p>
            </section>

            <!-- Ley aplicable -->
            <section class="mb-10">
                <h2 class="text-2xl font-semibold text-blue-900 mb-4">Ley aplicable</h2>
                <p class="text-gray-600 leading-relaxed">
                    Esta Política de Privacidad se rige por la legislación del país donde opera Estrategias y Soluciones Minmer Global. En caso de disputas, intentaremos resolverlas amistosamente. Si no es posible, las disputas serán sometidas a los tribunales competentes de la jurisdicción correspondiente.
                </p>
            </section>
        </div>
    </div>
</body>
</html>
