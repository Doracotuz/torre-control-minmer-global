<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>¡Bienvenido(a)!</title>
    <style>
        /* ESTILOS PARA CLIENTES MODERNOS (GMAIL, APPLE MAIL) - OUTLOOK LOS IGNORARÁ */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            -webkit-text-size-adjust: none;
            word-wrap: break-word;
        }

        /* Estilos responsivos */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                max-width: 100% !important;
            }
            .content-body {
                padding: 25px !important;
            }
            .title {
                font-size: 22px !important;
            }
            .subtitle {
                font-size: 15px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f0f2f5;">
    
    <table class="email-container" width="600" border="0" cellpadding="0" cellspacing="0" align="center" style="width: 600px; max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); border: 1px solid #e0e0e0; overflow: hidden;">
        
        <tr>
            <td class="header" style="background-color: #2c3856; padding: 24px; text-align: center;">
                <img src="{{ Storage::disk('s3')->url('LogoBlanco.png') }}" 
                     alt="Logotipo Minmer Global" 
                     width="150" 
                     style="width: 150px; max-width: 150px; height: auto; display: block; border: 0; margin: 0 auto;">
            </td>
        </tr>

        <tr>
            <td class="content-body" style="padding: 40px; font-family: 'Inter', Arial, sans-serif;">
                
                @if ($isReWelcome)
                    <h1 class="title" style="font-family: 'Inter', Arial, sans-serif; font-size: 26px; font-weight: 700; color: #2b2b2b; text-align: center; margin: 0 0 10px 0;">
                        ¡Bienvenido(a) a bordo!
                    </h1>
                @else
                    <h1 class="title" style="font-family: 'Inter', Arial, sans-serif; font-size: 26px; font-weight: 700; color: #2b2b2b; text-align: center; margin: 0 0 10px 0;">
                        ¡Bienvenido(a) a bordo!
                    </h1>
                @endif
                
                <p class="subtitle" style="font-family: 'Inter', Arial, sans-serif; font-size: 16px; color: #666666; text-align: center; margin: 0 0 30px 0; line-height: 1.6;">
                    Hola, <strong>{{ $user->name }}</strong>. Se ha creado una cuenta para ti en nuestro sistema. 
                    Aquí tienes tus credenciales para acceder.
                </p>

                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 30px; background-color: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px;">
                    <tr>
                        <td style="padding: 25px; font-family: 'Inter', Arial, sans-serif; font-size: 15px;">
                            <div style="margin-bottom: 20px;">
                                <span style="font-weight: 600; margin-bottom: 8px; display: block; color: #2c3856;">Usuario:</span>
                                <span style="font-weight: 400; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; font-family: monospace; color: #2c3856; display: block; border: 1px solid #d0d0d0;">{{ $user->email }}</span>
                            </div>
                            <div>
                                <span style="font-weight: 600; margin-bottom: 8px; display: block; color: #2c3856;">Contraseña Temporal:</span>
                                <span style="font-weight: 400; background-color: #ffffff; padding: 8px 12px; border-radius: 4px; font-family: monospace; color: #2c3856; display: block; border: 1px solid #d0d0d0;">{{ $password }}</span>
                            </div>
                        </td>
                    </tr>
                </table>

                <table border="0" cellpadding="0" cellspacing="0" align="center" style="margin: 0 auto 30px auto;">
                    <tr>
                        <a href="{{ route('login') }}" class="button-link">Iniciar Sesión Ahora</a>
                    </tr>
                </table>
                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                    <tr>
                        <td style="border-bottom: 1px solid #e0e0e0; height: 1px; line-height: 1px; font-size: 1px;">&nbsp;</td>
                    </tr>
                </table>

                <div style="margin-bottom: 25px; text-align: left;">
                    <h2 style="font-family: 'Inter', Arial, sans-serif; font-size: 18px; font-weight: 600; color: #2c3856; margin: 0 0 10px 0;">
                        Tu Herramienta para el Éxito Diario
                    </h2>
                    <p style="font-family: 'Inter', Arial, sans-serif; font-size: 15px; color: #666666; line-height: 1.6; margin: 0 0 10px 0;">
                        Tu nueva cuenta te da acceso a <strong>Control Tower</strong>, el software de gestión integral creado por <strong>Minmer Global</strong>. Esta plataforma está diseñada para optimizar y facilitar tu operación diaria.
                    </p>
                    <p style="font-family: 'Inter', Arial,serif; font-size: 15px; color: #666666; line-height: 1.6; margin: 0 0 10px 0;">
                        Estamos en constante desarrollo para asegurarnos de que Control Tower sea siempre una herramienta moderna, funcional y perfectamente adaptada a tus necesidades.
                    </D>
                </div>

                <div style="margin-top: 25px; padding: 15px; background-color: #fff9e6; border-left: 4px solid #ff9c00; color: #594a26; font-size: 14px; border-radius: 0 4px 4px 0; font-family: 'Inter', Arial, sans-serif;">
                    <strong>Nota de seguridad:</strong> Por tu seguridad, te recomendamos que cambies esta contraseña temporal la primera vez que inicies sesión.
                </div>

            </td>
        </tr>

        <tr>
            <td class="footer-text" style="text-align: center; font-size: 12px; color: #999999; padding: 20px 30px; border-top: 1px solid #e0e0e0; font-family: 'Inter', Arial, sans-serif; background-color: #f9f9f9;">
                Este es un correo generado automáticamente. Por favor, no respondas a este mensaje.
            </td>
        </tr>

    </table>
    
</body>
</html>