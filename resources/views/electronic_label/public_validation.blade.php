<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validación de Marbete | MINMER Global</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --header-bg: rgb(44, 56, 86);
            --footer-bg: rgb(44, 56, 86);
            --cert-bg: rgb(32, 41, 63);
            --btn-color: rgb(244, 153, 0);
            --page-bg: rgb(244, 242, 239);
            --text-main: #333333;
            --label-color: #544433;
            --table-header-start: #d4b98c;
            --table-header-end: #e9e0cf;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--page-bg);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .sticky-top-wrapper {
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
        }

        .main-container {
            max-width: 960px;
            margin: 0 auto;
            padding: 0 15px;
            width: 100%;
        }

        .header-section {
            background-color: var(--header-bg);
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-menu-link {
            color: #ffffff;
            font-family: 'Raleway', sans-serif;
            font-size: 11px;
            font-weight: 500;
            text-decoration: none;
            letter-spacing: 0.5px;
            transition: color 0.2s;
        }
        .nav-menu-link:hover { color: var(--btn-color); }
        
        .nav-divider {
            color: #6b7280;
            font-weight: 300;
            margin: 0 8px;
            font-size: 10px;
        }

        .header-btn {
            background-color: transparent;
            border: 1px solid var(--btn-color);
            color: var(--btn-color);
            border-radius: 20px;
            padding: 5px 20px;
            font-size: 11px;
            font-family: 'Raleway', sans-serif;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
            line-height: 1.2;
        }
        .header-btn:hover {
            background-color: transparent;
            border-color: #ffffff;
            color: #ffffff;
        }

        .cert-section {
            background-color: var(--cert-bg);
            padding: 12px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .cert-flex {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .cert-label {
            color: #e5e7eb;
            font-family: 'Montserrat', sans-serif;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-right: 20px;
        }

        .alert-outer-box {
            background-color: #f9f9f9;
            border: 1px solid #d8d0c5;
            border-radius: 8px;
            padding: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: inline-block;
            max-width: 100%;
        }

        .alert-inner-box {
            background-color: #c4a988;
            border-radius: 6px;
            padding: 10px 25px;
            display: flex;
            align-items: center;
        }

        .alert-icon-blue {
            width: 24px;
            height: 24px;
            background: linear-gradient(to bottom, #9ebcf0 0%, #5d86d1 100%);
            border: 1px solid #5277be;
            border-radius: 50%;
            color: white;
            font-family: 'Times New Roman', serif;
            font-weight: 700;
            font-style: italic;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.3);
            text-shadow: 0 1px 1px rgba(0,0,0,0.3);
        }

        .alert-text {
            color: #000000;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 0.3px;
        }

        .info-card {
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            border: 1px solid #dcdcdc;
            background: white;
        }

        .card-header {
            background: linear-gradient(to right, var(--table-header-start), var(--table-header-end));
            padding: 10px 20px;
            color: var(--label-color);
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #c8b090;
        }

        .card-body {
            background-color: #f7f5f0;
            padding: 10px 20px;
        }

        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e5e5e5;
        }
        .info-row:last-child { border-bottom: none; }

        .row-label {
            flex: 0 0 35%;
            text-align: right;
            padding-right: 25px;
            font-weight: 700;
            color: var(--label-color);
            font-size: 12px;
        }
        .row-value {
            flex: 1;
            font-size: 12px;
            color: #333;
            font-weight: 400;
        }

        .footer-section {
            background-color: var(--footer-bg);
            border-top: 1px solid #374151;
            padding: 60px 0 30px;
        }
        .branch-title {
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 14px;
            border-bottom: 1px solid #9ca3af;
            padding-bottom: 4px;
            margin-bottom: 15px;
            display: inline-block;
            letter-spacing: 1px;
        }
        .branch-list {
            color: #d1d5db;
            font-size: 12px;
            line-height: 1.8;
            font-family: 'Montserrat', sans-serif;
        }
        .footer-link { 
            color: #9ca3af; 
            font-size: 12px;
            text-decoration: none; 
            font-weight: 600; 
            margin-right: 20px; 
        }
        .footer-link:hover { color: white; }
        
        .footer-copyright {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 300;
        }

        @media (max-width: 900px) {
            .header-flex { flex-direction: column; gap: 15px; }
            .nav-group { flex-direction: column; align-items: center; gap: 10px; }
            .cert-flex { flex-direction: column; gap: 10px; text-align: center; }
            .cert-label { margin-right: 0; margin-bottom: 5px; }
            .info-row { flex-direction: column; }
            .row-label { text-align: left; padding-right: 0; padding-bottom: 2px; }
        }
    </style>
</head>
<body>

    <div class="sticky-top-wrapper">
        <header class="header-section">
            <div class="main-container header-flex">
                
                <div class="flex-shrink-0">
                    <a href="https://www.minmerglobal.com">
                        <img src="{{ Storage::disk('s3')->url('LogoBlanco1.PNG') }}" alt="MINMER GLOBAL" style="height: 48px; width: auto;">
                    </a>
                </div>

                <div class="flex flex-col md:flex-row items-center gap-6 nav-group">
                    <nav class="flex items-center flex-wrap justify-center">
                        <a href="https://www.minmerglobal.com" class="nav-menu-link">Inicio</a>
                        <span class="nav-divider">|</span>
                        <a href="https://www.minmerglobal.com/conócenos" class="nav-menu-link">Conócenos</a>
                        <span class="nav-divider">|</span>
                        <a href="#" class="nav-menu-link">Certificaciones</a>
                        <span class="nav-divider">|</span>
                        <a href="https://www.minmerglobal.com/servicios" class="nav-menu-link">Servicios</a>
                        <span class="nav-divider">|</span>
                        <a href="https://www.minmerglobal.com/contacto" class="nav-menu-link">Contacto</a>
                    </nav>
                    
                    <div class="flex gap-3">
                        <a href="https://minmer.aiko.com.mx/Clientes/login.php" target="_blank" class="header-btn">Clientes</a>
                        <a href="https://minmer.aiko.com.mx/Proveedores/login.php" target="_blank" class="header-btn">Proveedores</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="cert-section">
            <div class="main-container cert-flex">
                <span class="cert-label">Nuestras certificaciones:</span>
                <div class="flex items-center gap-4">
                    <img src="https://static.wixstatic.com/media/d3d80e_6f21c01321264597a142ff6ba44a9c60~mv2.png/v1/fill/w_49,h_50,al_c,q_85,usm_0.66_1.00_0.01,enc_avif,quality_auto/LOGO-ISO90013-blanco.png" alt="ISO 9001" style="height: 50px; width: auto; opacity: 0.9;">
                    <img src="https://static.wixstatic.com/media/d3d80e_74b69684805d4679bd911ba3d82ab317~mv2.png/v1/fill/w_57,h_55,al_c,q_85,usm_0.66_1.00_0.01,enc_avif,quality_auto/logo-iso4001-blanco.png" alt="ISO 14001" style="height: 55px; width: auto; opacity: 0.9;">
                    <img src="https://static.wixstatic.com/media/d3d80e_105065ba941a429f86292323f97f71bc~mv2.png/v1/fill/w_153,h_76,al_c,q_85,usm_0.66_1.00_0.01,enc_avif,quality_auto/LOGO-SIN-FONDO-ESR2.png" alt="ESR" style="height: 55px; width: auto; opacity: 0.9;">
                </div>
            </div>
        </div>
    </div>

    <main class="main-container py-10 min-h-screen">
        <br>
        <div class="mb-8">
            <div class="alert-outer-box">
                <div class="alert-inner-box">
                    <div class="alert-icon-blue">i</div>
                    <span class="alert-text">Si vas a manejar, no tomes.</span>
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="card-header">Datos del marbete</div>
            <div class="card-body">
                <div class="info-row">
                    <div class="row-label">Tipo:</div>
                    <div class="row-value">{{ $label->label_type }}</div>
                </div>
                <div class="info-row">
                    <div class="row-label">Folio:</div>
                    <div class="row-value font-mono text-gray-700">{{ $label->folio }}</div>
                </div>
                <div class="info-row">
                    <div class="row-label">Fecha de elaboración:</div>
                    <div class="row-value">{{ $label->elaboration_date->format('d-m-Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="row-label">Lote producción:</div>
                    <div class="row-value">{{ $label->label_batch }}</div>
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="card-header">Datos del producto</div>
            <div class="card-body">
                <div class="info-row">
                    <div class="row-label">Nombre o marca:</div>
                    <div class="row-value font-semibold">{{ $label->product_name }}</div>
                </div>
                <div class="info-row">
                    <div class="row-label">Tipo:</div>
                    <div class="row-value">{{ $label->product_type }}</div>
                </div>
                <div class="info-row">
                    <div class="row-label">Graduación alcohólica:</div>
                    <div class="row-value">{{ $label->alcohol_content }} % Alc. Vol.</div>
                </div>
                <div class="info-row">
                    <div class="row-label">Capacidad:</div>
                    <div class="row-value">{{ $label->capacity }}</div>
                </div>
                <div class="info-row">
                    <div class="row-label">Origen del producto:</div>
                    <div class="row-value">{{ $label->origin }}</div>
                </div>
                <div class="info-row">
                    <div class="row-label">Fecha envasado:</div>
                    <div class="row-value">{{ $label->packaging_date->format('d-m-Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="row-label">Lote producción:</div>
                    <div class="row-value">{{ $label->product_batch }}</div>
                </div>
            </div>
        </div>

        <div class="info-card">
            <div class="card-header">Datos del Productor, Fabricante, Envasador o Importador</div>
            <div class="card-body">
                <div class="info-row">
                    <div class="row-label">Nombre:</div>
                    <div class="row-value uppercase">{{ $label->maker_name }}</div>
                </div>
                <div class="info-row">
                    <div class="row-label">RFC:</div>
                    <div class="row-value uppercase font-mono">{{ $label->maker_rfc }}</div>
                </div>
            </div>
        </div>

    </main>

    <footer class="footer-section">
        <div class="main-container flex flex-col items-center text-center">
            
            <div class="mb-8 opacity-90">
                <a href="https://www.minmerglobal.com">
                    <img src="{{ Storage::disk('s3')->url('LogoBlanco1.PNG') }}" alt="MINMER GLOBAL" style="height: 50px; width: auto;">
                </a>
            </div>

            <div class="mb-12 w-full max-w-5xl">
                 <div class="branch-title">Sucursales</div>
                 
                 <div class="branch-list">
                    Cdmx <span class="mx-1">|</span>
                    Edo. de México <span class="mx-1">|</span>
                    Puebla <span class="mx-1">|</span>
                    Querétaro <span class="mx-1">|</span>
                    Aguascalientes <span class="mx-1">|</span>
                    Guanajuato <span class="mx-1">|</span>
                    Colima <span class="mx-1">|</span>
                    Tamaulipas <span class="mx-1">|</span>
                    Jalisco <br class="hidden sm:block">
                    Nuevo León <span class="mx-1">|</span>
                    Quintana Roo <span class="mx-1">|</span>
                    Yucatán <span class="mx-1">|</span>
                    Oaxaca <span class="mx-1">|</span>
                    Chiapas <span class="mx-1">|</span>
                    Coahuila <span class="mx-1">|</span>
                    Baja California <span class="mx-1">|</span>
                    Chihuahua <span class="mx-1">|</span>
                    Sonora <br class="hidden sm:block">
                    Sinaloa <span class="mx-1">|</span>
                    Nayarit <span class="mx-1">|</span>
                    Guerrero <span class="mx-1">|</span>
                    Michoacán <span class="mx-1">|</span>
                    Veracruz
                 </div>
            </div>

            <div class="flex flex-col md:flex-row items-center justify-between w-full border-t border-gray-600 pt-8">
                
                <div class="flex items-center mb-6 md:mb-0">
                    <a href="https://www.minmerglobal.com/política-de-privacidad" class="footer-link font-bold">Política de Privacidad</a>
                    <a href="mailto:contacto@minmerglobal.com" class="footer-link font-normal" style="font-family: 'Raleway', sans-serif;">contacto@minmerglobal.com</a>
                </div>

                <div class="flex space-x-6 mb-6 md:mb-0">
                    <a href="https://www.facebook.com/minmerglobal" target="_blank" class="text-white hover:text-[#cea364] transition text-xl"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/minmerglobal/" target="_blank" class="text-white hover:text-[#cea364] transition text-xl"><i class="fab fa-instagram"></i></a>
                    <a href="https://mx.linkedin.com/company/minmer-global" target="_blank" class="text-white hover:text-[#cea364] transition text-xl"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://x.com/Minmer_Global" target="_blank" class="text-white hover:text-[#cea364] transition text-xl"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="https://www.tiktok.com/@minmerglobal" target="_blank" class="text-white hover:text-[#cea364] transition text-xl"><i class="fab fa-tiktok"></i></a>
                </div>

                <div class="footer-copyright font-raleway">
                    © {{ date('Y') }} : Derechos reservados
                </div>
            </div>
        </div>
    </footer>
</body>
</html>