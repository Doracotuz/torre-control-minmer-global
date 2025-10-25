<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido(a) a Control Tower</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        /* --- Animación de "Impresión" --- */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes subtleShine {
            0% { background-position: 200% 50%; }
            100% { background-position: -100% 50%; }
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .email-wrapper {
            background-color: #f0f2f5;
            padding: 40px 20px;
        }
        /* --- El Contenedor Principal (El "Salón de Control") --- */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            /* --- El fondo oscuro sofisticado --- */
            background-color: #2c3856; 
            /* --- Un gradiente sutil para dar "luz" --- */
            background: radial-gradient(circle at 50% 0%, #3a4a6d 0%, #2c3856 70%);
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeIn 1.2s ease-out;
            border: 1px solid #4a5a7d; /* Borde sutil para el modo oscuro */
        }
        
        /* --- El Header (Hero Section) --- */
        .header {
            padding: 40px 40px 30px 40px;
            text-align: center;
        }
        .header img {
            max-width: 160px; 
            height: auto; 
            display: block; 
            border: 0; 
            margin: 0 auto 25px auto;
        }
        .title {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff; /* Texto "brilla" sobre el fondo */
            margin-bottom: 12px;
            letter-spacing: -0.5px; /* Detalle de alta gama */
        }
        .subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.8); /* Blanco menos intenso */
            margin-bottom: 0;
            line-height: 1.6;
        }
        .subtitle strong {
            color: #ffffff;
            font-weight: 600;
        }

        /* --- Cuerpo del Contenido --- */
        .content-body {
            padding: 0 40px 40px 40px;
        }

        /* --- Tarjeta de Credenciales (Innovación: Glassmorphism) --- */
        .info-card {
            /* --- El efecto "Vidrio Esmerilado" --- */
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            /* --- FIN del efecto --- */
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        .info-card:hover {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .info-item {
            display: flex;
            justify-content: space-between; 
            align-items: center;
            margin-bottom: 16px;
            font-size: 15px;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8); /* Texto sobre el vidrio */
            margin-right: 15px;
        }
        /* --- El valor resalta para máxima legibilidad (UX) --- */
        .info-value {
            font-weight: 600;
            background-color: #ffffff;
            color: #2c3856;
            padding: 6px 12px;
            border-radius: 6px;
            font-family: 'Menlo', 'Courier New', monospace;
            border: 1px solid #ffffff;
        }
        
        /* --- Texto de Valor (Marketing) --- */
        .feature-text {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.7;
            margin-bottom: 25px;
            text-align: center;
        }
        .feature-text strong {
            color: #ff9c00; /* Naranja corporativo resalta */
            font-weight: 600;
        }

        /* --- Alerta de Seguridad (UX Crítico) --- */
        .alert {
            /* Mantenemos el contraste alto para esta acción crítica */
            padding: 16px;
            background-color: #fff9e6;
            border-left: 5px solid #ff9c00;
            color: #594a26;
            font-size: 14px;
            border-radius: 0 8px 8px 0;
            line-height: 1.6;
        }

        /* --- El Botón (Call To Action) --- */
        .button-link {
            display: block;
            width: fit-content;
            margin: 35px auto 0;
            padding: 14px 32px;
            /* --- Gradiente que "brilla" --- */
            background: linear-gradient(135deg, #ffb340 0%, #ff9c00 50%, #e68a00 100%);
            background-size: 200% 200%; /* Para la animación de brillo */
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(255, 156, 0, 0.3);
            transition: all 0.3s ease-in-out;
            animation: subtleShine 3s ease-in-out infinite;
        }
        .button-link:hover {
            color: #ffffff;
            transform: scale(1.05) translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 156, 0, 0.4);
            animation-play-state: paused; /* Detiene el brillo al pasar el mouse */
        }
        .button-arrow {
            margin-left: 8px;
            transition: transform 0.2s ease;
        }
        .button-link:hover .button-arrow {
            transform: translateX(5px); /* Micro-interacción */
        }

        /* --- Footer Elegante --- */
        .footer-text {
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5); /* Texto sutil */
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            
            <div class="header">
                <img src="{{ Storage::disk('s3')->url('LogoBlanco.png') }}" 
                     alt="Logotipo Minmer Global" 
                     style="max-width: 160px; height: auto; display: block; border: 0; margin: 0 auto 25px auto;">
                
                <h1 class="title">Bienvenido(a) a Control Tower</h1>
                <p class="subtitle">
                    Hola, <strong>{{ $user->name }}</strong>. Tu cuenta de acceso ha sido creada.
                </p>
            </div>
            
            <div class="content-body">
                
                <div class="info-card">
                    <div class="info-item">
                        <span class="info-label">Usuario:</span>
                        <span class="info-value">{{ $user->email }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Contraseña Temporal:</span>
                        <span class="info-value">{{ $password }}</span>
                    </div>
                </div>

                <p class="feature-text">
                    Esta es tu llave de acceso a <strong>Control Tower</strong>, la plataforma de gestión integral de <strong>Minmer Global</strong> diseñada para la excelencia operativa.
                </p>
                
                <div class="alert">
                    <strong>Acción Requerida:</strong> Por tu seguridad, deberás cambiar esta contraseña temporal en tu primer inicio de sesión.
                </div>

                <a href="{{ route('login') }}" class="button-link">
                    Iniciar Sesión
                    <span class="button-arrow">&rsaquo;</span>
                </a>
            </div>

            <div class="footer-text">
                &copy; {{ date('Y') }} Minmer Global. Todos los derechos reservados.
                <br>Este es un correo transaccional.
            </div>
        </div>
    </div>
</body>
</html>