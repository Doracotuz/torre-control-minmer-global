<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Bienvenido(a)!</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            word-wrap: break-word;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        .header {
            background-color: #2c3856;
            padding: 24px;
            text-align: center;
        }
        .header img {
            max-width: 150px;
            height: auto;
        }
        .content-body {
            padding: 30px;
        }
        .title {
            font-size: 26px;
            font-weight: 700;
            color: #2b2b2b;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 16px;
            color: #666666;
            text-align: center;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .info-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            margin-bottom: 25px;
        }
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 15px;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }
        .info-item-text {
            color: #2c3856;
        }
        .info-label {
            font-weight: 600;
            margin-right: 8px;
        }
        .info-value {
            font-weight: 400;
            background-color: #eef1f8;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
        }
        .alert {
            margin-top: 25px;
            padding: 15px;
            background-color: #fff9e6;
            border-left: 4px solid #ff9c00;
            color: #594a26;
            font-size: 14px;
            border-radius: 0 4px 4px 0;
        }
        .button-link {
            display: block;
            width: fit-content;
            margin: 30px auto 0;
            padding: 12px 28px;
            background-color: #ff9c00;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            border-radius: 8px;
        }
        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #999999;
            margin-top: 30px;
            padding: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .feature-section {
            margin-bottom: 25px;
            text-align: center;
        }
        .feature-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3856;
            margin-bottom: 10px;
        }
        .feature-text {
            font-size: 15px;
            color: #666666;
            line-height: 1.6;
            margin-bottom: 10px;
        }        
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <img src="{{ Storage::disk('s3')->url('LogoBlanco.png') }}" 
                 alt="Logotipo Minmer Global" 
                 style="height: auto; max-height: 60px; display: block; border: 0;" 
                 width="150">
        </div>
        
        <div class="content-body">
            @if ($isReWelcome)
                <h1 class="title">¡Bienvenido(a) a bordo!</h1>
                <p class="subtitle">
                    Hola, <strong>{{ $user->name }}</strong>. Se ha creado una cuenta para ti en nuestro sistema. 
                    A continuación, encontrarás tus credenciales para acceder.
                </p>
            @else
                <h1 class="title">¡Bienvenido(a) a bordo!</h1>
                <p class="subtitle">
                    Hola, <strong>{{ $user->name }}</strong>. Se ha creado una cuenta para ti en nuestro sistema. 
                    A continuación, encontrarás tus credenciales para acceder.
                </p>
            @endif
            
            <div class="info-card">
                <div class="info-item">
                    <span class="info-item-text">
                        <span class="info-label">Usuario:</span>
                        <span class="info-value">{{ $user->email }}</span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-item-text">
                        <span class="info-label">Contraseña Temporal:</span>
                        <span class="info-value">{{ $password }}</span>
                    </span>
                </div>
            </div>

            <div class="feature-section">
                <h2 class="feature-title">Tu Herramienta para el Éxito Diario</h2>
                <p class="feature-text">
                    Tu nueva cuenta te da acceso a <strong>Control Tower</strong>, el software de gestión integral creado por <strong>Minmer Global</strong>. Esta plataforma está diseñada para optimizar y facilitar tu operación diaria.
                </p>
                <p class="feature-text">
                    Estamos en constante desarrollo para asegurarnos de que Control Tower sea siempre una herramienta moderna, funcional y perfectamente adaptada a tus necesidades.
                </p>
            </div>
            <div class="alert">
                <strong>Nota de seguridad:</strong> Por tu seguridad, te recomendamos que cambies esta contraseña temporal la primera vez que inicies sesión.
            </div>

            <a href="{{ route('login') }}" class="button-link">Iniciar Sesión Ahora</a>
        </div>

        <div class="footer-text">
            Este es un correo generado automáticamente. Por favor, no respondas a este mensaje.
        </div>
    </div>
</body>
</html>