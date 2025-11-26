<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Técnica - {{ $product->sku }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            /* Fondo verde principal */
            background-color: #00683f;
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px; /* Un poco de padding para que se vea el fondo verde */
            color: #333;
            text-align: center;
        }

        /* El contenedor blanco que simula la forma */
        .main-card {
            background-color: #ffffff;
            /* Bordes muy redondeados para aproximar la forma de la imagen de manera segura para DomPDF */
            border-radius: 60px;
            width: 100%;
            height: 95%; /* Altura casi completa */
            box-sizing: border-box;
            padding: 30px;
            position: relative;
        }

        /* Título principal */
        h1.main-title {
            font-weight: normal;
            font-size: 32px;
            margin-top: 10px;
            margin-bottom: 50px;
            color: #000;
        }

        /* Estructura de layout principal de 3 columnas */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .col-side {
            width: 28%;
            vertical-align: middle;
        }
        .col-center {
            width: 44%;
            vertical-align: middle;
            text-align: center;
            padding: 0 10px;
        }

        /* Estilos para los items individuales (filas de datos) */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px; /* Espacio entre items */
        }
        .icon-cell {
            width: 50px;
            vertical-align: middle;
        }
        .text-cell {
            vertical-align: middle;
        }

        /* Alineación específica para columna izquierda */
        .left-col-text {
            text-align: right;
            padding-right: 15px;
        }
        .left-col-icon img {
            float: left;
        }

        /* Alineación específica para columna derecha */
        .right-col-text {
            text-align: left;
            padding-left: 15px;
        }
        .right-col-icon img {
            float: right;
        }

        /* Tipografía de etiquetas y valores */
        .label {
            font-weight: bold;
            font-size: 14px;
            color: #000;
            margin-bottom: 5px;
        }
        .value {
            font-size: 16px;
            color: #555;
            line-height: 1.2;
        }

        /* Estilos de iconos SVG */
        .icon-svg {
            width: 45px;
            height: auto;
            /* El color verde de los iconos debe venir definido dentro del propio SVG en S3
               Si los SVGs son negros, se necesitaría un filtro CSS, pero DomPDF tiene soporte limitado para filtros.
               Asumo que los SVGs en S3 ya tienen el color #8fc742 o similar. */
        }

        /* Sección central de producto */
        .product-image {
            max-width: 100%;
            max-height: 450px;
            object-fit: contain;
            /* Sombra suave para levantar la botella */
            filter: drop-shadow(0px 5px 10px rgba(0,0,0,0.2));
        }
        .product-brand {
            font-size: 24px;
            font-weight: bold;
            color: #00683f;
            margin-top: 20px;
            text-transform: uppercase;
            line-height: 1;
        }
        .product-type {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
        }
        .logo-img {
            height: 70px;
            width: auto;
        }
    </style>
</head>
<body>
    {{-- Helper function para generar URLs de S3 rápidamente --}}
    @php
        function s3Icon($filename) {
            return Illuminate\Support\Facades\Storage::disk('s3')->url('IconosFichaTecnica/' . $filename);
        }
    @endphp

    <div class="main-card">
        
        <h1 class="main-title">Ficha técnica</h1>

        <table class="layout-table">
            <tr>
                <td class="col-side">
                    
                    <table class="item-table">
                        <tr>
                            <td class="icon-cell left-col-icon">
                                <img src="{{ s3Icon('SKU.svg') }}" class="icon-svg" alt="SKU">
                            </td>
                            <td class="text-cell left-col-text">
                                <div class="label">SKU</div>
                                <div class="value">{{ $product->sku }}</div>
                            </td>
                        </tr>
                    </table>

                    <table class="item-table">
                        <tr>
                            <td class="icon-cell left-col-icon">
                                <img src="{{ s3Icon('Descripción.svg') }}" class="icon-svg" style="width: 35px;" alt="Descripción">
                            </td>
                            <td class="text-cell left-col-text">
                                <div class="label">Descripción</div>
                                <div class="value" style="font-size: 14px;">{{ Str::limit($product->description, 80) }}</div>
                            </td>
                        </tr>
                    </table>

                    <table class="item-table">
                        <tr>
                            <td class="icon-cell left-col-icon">
                                <img src="{{ s3Icon('UPC Caja.svg') }}" class="icon-svg" alt="UPC Caja">
                            </td>
                            <td class="text-cell left-col-text">
                                <div class="label">UPC Caja</div>
                                <div class="value">{{ $product->upc ?? 'No Aplica' }}</div>
                            </td>
                        </tr>
                    </table>

                    <table class="item-table">
                        <tr>
                            <td class="icon-cell left-col-icon">
                                <img src="{{ s3Icon('UPC Botella.svg') }}" class="icon-svg" style="width: 25px; margin-left:10px;" alt="UPC Botella">
                            </td>
                            <td class="text-cell left-col-text">
                                <div class="label">UPC Botella</div>
                                {{-- NOTA: El campo 'upc_bottle' no existe en tu controlador actualmente. Se usa un placeholder. --}}
                                <div class="value">{{ $extra['upc_bottle'] ?? 'No Aplica' }}</div>
                            </td>
                        </tr>
                    </table>

                     <table class="item-table">
                        <tr>
                            <td class="icon-cell left-col-icon">
                                <img src="{{ s3Icon('Graduación Alcoholica.svg') }}" class="icon-svg" alt="Graduación Alcohólica">
                            </td>
                            <td class="text-cell left-col-text">
                                <div class="label">Graduación Alcohólica</div>
                                <div class="value">{{ $extra['alcohol_vol'] ?? 'N/A' }}</div>
                            </td>
                        </tr>
                    </table>

                </td>

                <td class="col-center">
                    @if($product->photo_path)
                        <img src="{{ $product->photo_url }}" class="product-image" alt="Producto">
                    @else
                        <div style="height: 400px; display:flex; align-items:center; justify-content:center; background:#f0f0f0; border-radius:20px; color:#999; font-weight:bold;">
                            SIN IMAGEN
                        </div>
                    @endif

                    <div class="product-brand">
                        {{ $product->brand ?? 'MARCA NO DEFINIDA' }}
                    </div>
                    <div class="product-type">
                        {{ $product->type ?? 'TIPO NO DEFINIDO' }}
                    </div>
                </td>

                <td class="col-side">
                    
                    <table class="item-table">
                        <tr>
                            <td class="text-cell right-col-text">
                                <div class="label">Cantidad por caja</div>
                                <div class="value">{{ $product->pieces_per_box ?? '1' }}</div>
                            </td>
                            <td class="icon-cell right-col-icon">
                                <img src="{{ s3Icon('Cantidad Caja.svg') }}" class="icon-svg" alt="Cantidad por caja">
                            </td>
                        </tr>
                    </table>

                     <table class="item-table">
                        <tr>
                            <td class="text-cell right-col-text">
                                <div class="label">Peso de caja Master</div>
                                <div class="value">{{ $extra['master_box_weight'] ?? 'N/A' }}</div>
                            </td>
                            <td class="icon-cell right-col-icon">
                                <img src="{{ s3Icon('Peso caja master.svg') }}" class="icon-svg" alt="Peso caja Master">
                            </td>
                        </tr>
                    </table>

                    <table class="item-table">
                        <tr>
                            <td class="text-cell right-col-text">
                                <div class="label">Cajas por Cama</div>
                                <div class="value">{{ $extra['boxes_per_layer'] ?? 'N/A' }}</div>
                            </td>
                            <td class="icon-cell right-col-icon">
                                <img src="{{ s3Icon('Cajas por cama.svg') }}" class="icon-svg" alt="Cajas por Cama">
                            </td>
                        </tr>
                    </table>

                     <table class="item-table">
                        <tr>
                            <td class="text-cell right-col-text">
                                <div class="label">Camas por pallet</div>
                                <div class="value">{{ $extra['layers_per_pallet'] ?? 'N/A' }}</div>
                            </td>
                            <td class="icon-cell right-col-icon">
                                <img src="{{ s3Icon('Camas por tarima.svg') }}" class="icon-svg" alt="Camas por pallet">
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>

        <div class="footer">
            <img src="{{ $logo_url }}" class="logo-img" alt="Consorcio Monter">
        </div>

    </div>

</body>
</html>