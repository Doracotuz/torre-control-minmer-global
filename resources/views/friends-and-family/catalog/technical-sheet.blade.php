<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Técnica - {{ $product->sku }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #00683f;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .card {
            background-color: #ffffff;
            margin: 20px;
            height: 94%; 
            border-radius: 25px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }

        .header-block {
            background-color: #f4f4f4;
            border-bottom: 2px solid #8fc742;
            padding: 25px 0;
            text-align: center;
        }
        
        .header-title {
            font-weight: 900;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #00683f;
            margin: 0;
        }
        
        .header-sku {
            color: #666;
            font-size: 10px;
            letter-spacing: 1px;
            margin-top: 5px;
            font-weight: bold;
        }

        .content-wrapper {
            width: 100%;
            height: 75%;
            display: table;
        }
        
        .content-cell {
            display: table-cell;
            vertical-align: middle;
            padding: 0 20px;
        }

        .layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        .col-side { width: 30%; vertical-align: middle; }
        .col-center { width: 40%; vertical-align: middle; text-align: center; }

        .item-row {
            margin-bottom: 35px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        .item-row:last-child { border-bottom: none; }

        .label {
            display: block;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            color: #00683f;
            margin-bottom: 4px;
        }
        
        .value {
            display: block;
            font-size: 12px;
            color: #333;
            font-weight: bold;
            line-height: 1.3;
        }

        .icon-container {
            width: 32px;
            height: 32px;
            background: #f9f9f9;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-bottom: 5px;
            display: inline-block;
        }
        
        .data-cell-icon { width: 40px; vertical-align: top; }
        .data-cell-text { vertical-align: top; }
        
        .align-right { text-align: right; }
        .align-left { text-align: left; }

        .icon-svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: #8fc742;
            stroke-width: 2;
        }

        .product-image {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            filter: drop-shadow(0px 10px 20px rgba(0,0,0,0.15));
        }

        .no-image {
            width: 100%;
            height: 300px;
            background: #f8f8f8;
            border: 2px dashed #ddd;
            border-radius: 20px;
            line-height: 300px;
            color: #aaa;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            position: absolute;
            bottom: 25px;
            width: 100%;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .logo-img {
            height: 60px;
            width: auto;
        }

    </style>
</head>
<body>

    <div class="card">
        
        <div class="header-block">
            <h1 class="header-title">FICHA TÉCNICA</h1>
            <div class="header-sku">REF: {{ $product->sku }}</div>
        </div>

        <div class="content-wrapper">
            <div class="content-cell">
                
                <table class="layout-table">
                    <tr>
                        <td class="col-side">
                            
                            <div class="item-row">
                                <table width="100%"><tr>
                                    <td class="data-cell-icon align-left">
                                        <div class="icon-container">
                                            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                                        </div>
                                    </td>
                                    <td class="data-cell-text align-right">
                                        <span class="label">DESCRIPCIÓN</span>
                                        <span class="value">{{ Str::limit($product->description, 60) }}</span>
                                    </td>
                                </tr></table>
                            </div>

                            <div class="item-row">
                                <table width="100%"><tr>
                                    <td class="data-cell-icon align-left">
                                        <div class="icon-container">
                                            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M12 2l9 19H3l9-19zm0 3.8L5.6 19h12.8L12 5.8z"/></svg>
                                        </div>
                                    </td>
                                    <td class="data-cell-text align-right">
                                        <span class="label">MARCA</span>
                                        <span class="value">{{ $product->brand ?? 'Genérico' }}</span>
                                    </td>
                                </tr></table>
                            </div>

                            <div class="item-row">
                                <table width="100%"><tr>
                                    <td class="data-cell-icon align-left">
                                        <div class="icon-container">
                                            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M3 5h2v14H3zm4 0h1v14H7zm3 0h3v14h-3zm5 0h2v14h-2zm4 0h1v14h-1z"/></svg>
                                        </div>
                                    </td>
                                    <td class="data-cell-text align-right">
                                        <span class="label">UPC CAJA</span>
                                        <span class="value">{{ $product->upc ?? 'No Aplica' }}</span>
                                    </td>
                                </tr></table>
                            </div>

                            <div class="item-row">
                                <table width="100%"><tr>
                                    <td class="data-cell-icon align-left">
                                        <div class="icon-container">
                                            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z"/></svg>
                                        </div>
                                    </td>
                                    <td class="data-cell-text align-right">
                                        <span class="label">VOL. ALCOHOL</span>
                                        <span class="value">{{ $extra['alcohol_vol'] ?? 'N/A' }}</span>
                                    </td>
                                </tr></table>
                            </div>

                        </td>

                        <td class="col-center">
                            @if($product->photo_path)
                                <img src="{{ $product->photo_url }}" class="product-image">
                            @else
                                <div class="no-image">SIN IMAGEN</div>
                            @endif
                            
                            <div style="margin-top: 20px;">
                                <span style="background:#eee; padding:5px 15px; border-radius:15px; font-size:10px; color:#888; font-weight:bold;">
                                    {{ $product->type ?? 'PRODUCTO' }}
                                </span>
                            </div>
                        </td>

                        <td class="col-side">
                            
                            <div class="item-row">
                                <table width="100%"><tr>
                                    <td class="data-cell-text align-left">
                                        <span class="label">CANTIDAD / CAJA</span>
                                        <span class="value">{{ $product->pieces_per_box ?? '1' }} Pzas</span>
                                    </td>
                                    <td class="data-cell-icon align-right">
                                        <div class="icon-container">
                                            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M21 16.5c0 .38-.21.71-.53.88l-7.9 4.44c-.16.12-.36.18-.57.18-.21 0-.41-.06-.57-.18l-7.9-4.44A.991.991 0 0 1 3 16.5V7.5c0-.38.21-.71.53-.88l7.9-4.44c.16-.12.36-.18.57-.18.21 0 .41.06.57.18l7.9 4.44c.32.17.53.5.53.88v9zM12 4.15L6.04 7.5 12 10.85l5.96-3.35L12 4.15z"/></svg>
                                        </div>
                                    </td>
                                </tr></table>
                            </div>

                            <div class="item-row">
                                <table width="100%"><tr>
                                    <td class="data-cell-text align-left">
                                        <span class="label">PESO CAJA MASTER</span>
                                        <span class="value">{{ $extra['master_box_weight'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="data-cell-icon align-right">
                                        <div class="icon-container">
                                            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                                        </div>
                                    </td>
                                </tr></table>
                            </div>

                            <div class="item-row">
                                <table width="100%"><tr>
                                    <td class="data-cell-text align-left">
                                        <span class="label">CAJAS POR CAMA</span>
                                        <span class="value">{{ $extra['boxes_per_layer'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="data-cell-icon align-right">
                                        <div class="icon-container">
                                            <svg class="icon-svg" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                                        </div>
                                    </td>
                                </tr></table>
                            </div>

                            <div class="item-row">
                                <table width="100%"><tr>
                                    <td class="data-cell-text align-left">
                                        <span class="label">CAMAS POR PALLET</span>
                                        <span class="value">{{ $extra['layers_per_pallet'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="data-cell-icon align-right">
                                        <div class="icon-container">
                                            <svg class="icon-svg" viewBox="0 0 24 24"><path d="M2 17h20v2H2zm2-10h16v8H4zm0-5h16v3H4z"/></svg>
                                        </div>
                                    </td>
                                </tr></table>
                            </div>

                        </td>
                    </tr>
                </table>

            </div>
        </div>

        <div class="footer">
            <img src="{{ $logo_url }}" class="logo-img" alt="Consorcio Monter">
        </div>

    </div>

</body>
</html>