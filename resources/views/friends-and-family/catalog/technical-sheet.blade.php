<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Técnica - {{ $product->sku }}</title>
    <style>
        @page { margin: 0px; padding: 0px; }
        body {
            background-color: #00683f;
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 30px;
        }
        
        .main-card {
            background-color: #ffffff;
            border-radius: 40px;
            width: 100%;
            height: 980px;
            position: relative;
        }

        .header-title {
            text-align: center;
            font-size: 28px;
            padding-top: 40px;
            margin-bottom: 40px;
            color: #000;
            font-weight: normal;
        }

        .layout-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .col-left { width: 32%; vertical-align: top; padding-left: 30px; }
        .col-center { width: 36%; vertical-align: top; text-align: center; padding-top: 20px; }
        .col-right { width: 32%; vertical-align: top; padding-right: 30px; }

        .item-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }

        .label { font-weight: bold; font-size: 11px; color: #000; margin-bottom: 3px; display: block; }
        .value { font-size: 11px; color: #555; display: block; }

        .icon-container {
            width: 40px;
            height: 40px;
            display: block;
        }
        .icon-container svg,
        .icon-container svg path, 
        .icon-container svg rect, 
        .icon-container svg circle, 
        .icon-container svg polygon {
            fill: #00683f !important;
            width: 100% !important;
            height: auto !important;
        }
        .icon-container.narrow { width: 25px; margin-left: 8px; }


        .cell-icon-left { width: 50px; vertical-align: middle; text-align: left; }
        .cell-text-left { vertical-align: middle; text-align: left; padding-left: 10px; }
        .cell-text-right { vertical-align: middle; text-align: right; padding-right: 10px; }
        .cell-icon-right { width: 50px; vertical-align: middle; text-align: right; }

        .bottle-img { max-width: 100%; max-height: 500px; object-fit: contain; }
        .brand-name { font-size: 22px; font-weight: bold; color: #00683f; margin-top: 15px; text-transform: uppercase; }
        .product-type { font-size: 14px; color: #666; margin-top: 5px; text-transform: uppercase; }

        .footer { position: absolute; bottom: 30px; width: 100%; text-align: center; }
        .logo-footer { width: 120px; height: auto; }

    </style>
</head>
<body>
    @php
        function getInlineSvg($filename) {
            try {
                $content = Illuminate\Support\Facades\Storage::disk('s3')->get('IconosFichaTecnica/' . $filename);
                return $content;
            } catch (\Exception $e) {
                return '';
            }
        }
    @endphp

    <div class="main-card">
        
        <h1 class="header-title">Ficha técnica</h1>

        <table class="layout-table">
            <tr>
                <td class="col-left">
                    
                    <table class="item-table">
                        <tr>
                            <td class="cell-icon-left">
                                <div class="icon-container">
                                    {!! getInlineSvg('SKU.svg') !!}
                                </div>
                            </td>
                            <td class="cell-text-left">
                                <span class="label">SKU</span>
                                <span class="value">{{ $product->sku }}</span>
                            </td>
                        </tr>
                    </table>

                    <table class="item-table">
                        <tr>
                            <td class="cell-icon-left">
                                <div class="icon-container">
                                    {!! getInlineSvg('Descripción.svg') !!}
                                </div>
                            </td>
                            <td class="cell-text-left">
                                <span class="label">Descripción</span>
                                <span class="value">{{ Str::limit($product->description, 50) }}</span>
                            </td>
                        </tr>
                    </table>

                    <table class="item-table">
                        <tr>
                            <td class="cell-icon-left">
                                <div class="icon-container">
                                    {!! getInlineSvg('UPC Caja.svg') !!}
                                </div>
                            </td>
                            <td class="cell-text-left">
                                <span class="label">UPC Caja</span>
                                <span class="value">{{ $product->upc ?? 'No Aplica' }}</span>
                            </td>
                        </tr>
                    </table>

                    <table class="item-table">
                        <tr>
                            <td class="cell-icon-left">
                                <div class="icon-container">
                                    {!! getInlineSvg('Graduación Alcoholica.svg') !!}
                                </div>
                            </td>
                            <td class="cell-text-left">
                                <span class="label">Graduación Alcohólica</span>
                                <span class="value">{{ $extra['alcohol_vol'] ?? 'N/A' }}</span>
                            </td>
                        </tr>
                    </table>

                </td>

                <td class="col-center">
                    @if($product->photo_path)
                        {{-- La imagen del producto sigue siendo un <img> normal --}}
                        <img src="{{ $product->photo_url }}" class="bottle-img">
                    @else
                        <div style="height:300px; line-height:300px; background:#f0f0f0; color:#aaa; border-radius:20px;">
                            Sin Imagen
                        </div>
                    @endif

                    <div class="brand-name">{{ $product->brand ?? 'MARCA' }}</div>
                    <div class="product-type">{{ $product->type ?? 'Producto' }}</div>
                </td>

                <td class="col-right">
                    
                    <table class="item-table">
                        <tr>
                            <td class="cell-text-right">
                                <span class="label">Cantidad por caja</span>
                                <span class="value">{{ $product->pieces_per_box ?? '1' }}</span>
                            </td>
                            <td class="cell-icon-right">
                                <div class="icon-container">
                                    {!! getInlineSvg('Cantidad Caja.svg') !!}
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table class="item-table">
                        <tr>
                            <td class="cell-text-right">
                                <span class="label">Peso de caja Master</span>
                                <span class="value">{{ $extra['master_box_weight'] ?? 'N/A' }}</span>
                            </td>
                            <td class="cell-icon-right">
                                <div class="icon-container">
                                    {!! getInlineSvg('Peso caja master.svg') !!}
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table class="item-table">
                        <tr>
                            <td class="cell-text-right">
                                <span class="label">Cajas por Cama</span>
                                <span class="value">{{ $extra['boxes_per_layer'] ?? 'N/A' }}</span>
                            </td>
                            <td class="cell-icon-right">
                                <div class="icon-container">
                                    {!! getInlineSvg('Cajas por cama.svg') !!}
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table class="item-table">
                        <tr>
                            <td class="cell-text-right">
                                <span class="label">Camas por pallet</span>
                                <span class="value">{{ $extra['layers_per_pallet'] ?? 'N/A' }}</span>
                            </td>
                            <td class="cell-icon-right">
                                <div class="icon-container">
                                    {!! getInlineSvg('Camas por tarima.svg') !!}
                                </div>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>

        <div class="footer">
            {{-- El logo del footer sigue siendo una imagen normal --}}
            <img src="{{ $logo_url }}" class="logo-footer">
        </div>

    </div>
</body>
</html>