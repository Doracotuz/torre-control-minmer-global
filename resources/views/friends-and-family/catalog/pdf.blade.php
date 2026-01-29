<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de productos</title>
    <style>
        @page {
            margin: 0cm;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        body {
            margin-top: 3cm;
            margin-bottom: 2cm;
            margin-left: 1cm;
            margin-right: 1cm;
            background-color: #ffffff;
            color: #111;
        }

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            background-color: #2c3856;
            color: white;
            padding: 0.5cm 1cm;
            border-bottom: 6px solid #ff9c00;
        }

        .header-table { width: 100%; }
        .header-title h1 { font-size: 28px; text-transform: uppercase; margin: 0; }
        .header-title h2 { font-size: 12px; color: #ff9c00; margin-top: 5px; text-transform: uppercase; }
        .header-logo img { height: 1.8cm; background: white; padding: 5px; border-radius: 4px; }

        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1cm;
            background-color: #f3f4f6;
            text-align: center;
            line-height: 1cm;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
        }

        .products-container {
            width: 100%;
            overflow: hidden;
        }

        .product-wrapper {
            width: 48%;
            float: left;
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .product-wrapper:nth-child(even) {
            float: right;
        }

        .product-card {
            background-color: #fff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            overflow: hidden;
            height: 425px; 
            width: 100%;
            position: relative;
        }

        .img-container {
            height: 220px;
            width: 100%;
            text-align: center;
            background-color: white;
            border-bottom: 1px solid #eee;
            padding: 5px;
            line-height: 210px; 
        }

        .img-container img {
            max-height: 200px;
            max-width: 90%;
            vertical-align: middle;
            display: inline-block;
        }

        .card-body {
            padding: 10px 15px 0 15px;
            height: 150px; 
            overflow: hidden;
        }

        .brand-row {
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .brand {
            font-size: 14px;
            color: #6b7280;
            font-weight: bold;
            text-transform: uppercase;
        }

        .sku {
            font-size: 14px;
            color: #374151;
            float: right;
            font-weight: bold;
            font-family: monospace;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
            color: #000;
            line-height: 1.3;
            margin-top: 5px;
            margin-bottom: 2px;
            height: 38px;
            overflow: hidden;
        }

        .specs {
            font-size: 11px;
            color: #333;
            line-height: 1.5;
            margin-top: 5px;
        }

        .spec-label {
            font-weight: bold;
            color: #555;
            margin-right: 5px;
        }

        .spec-label-1 {
            font-weight: bold;
            color: #ff9c00;
            margin-right: 5px;
        }        

        .price-container {
            width: 100%;
            background-color: #f1f5f9;
            padding: 8px 15px;
            text-align: right;
            border-top: 2px solid #e5e7eb;
            height: 55px; 
            box-sizing: border-box;
            margin-top: 0;
        }

        .price-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: 0px;
        }

        .price {
            font-size: 24px;
            font-weight: 900;
            color: #2c3856;
            display: block;
        }

        .badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            color: white;
            z-index: 10;
        }
        .bg-green { background-color: #10b981; }
        .bg-red { background-color: #ef4444; }

    </style>
</head>
<body>

    <header>
        <table class="header-table">
            <tr>
                <td class="header-title">
                    <h1>Catálogo de productos</h1>
                    <h2>Colección Exclusiva {{ Auth::user()->area ? ' - ' . Auth::user()->area->name : '' }}{{ $percentage_text ?? '' }}</h2>
                </td>
                <td class="header-logo" align="right">
                    <img src="{{ $logo_url }}" alt="Logo">
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Minmer Global Control Tower • {{ $date }} • Página <span class="page-number"></span>
    </footer>

    <div class="products-container">
        @foreach($products as $product)
            <div class="product-wrapper">
                <div class="product-card">
                    
                    @if($product->is_active)
                        <span class="badge bg-green">DISPONIBLE</span>
                    @else
                        <span class="badge bg-red">AGOTADO / INACTIVO</span>
                    @endif

                    <div class="img-container">
                        <img src="{{ $product->photo_url }}" alt="Prod">
                    </div>

                    <div class="card-body">
                        <div class="brand-row">
                            <span class="brand">{{ Str::limit($product->brand ?? 'S/M', 15) }}</span>
                            <span class="sku">{{ $product->sku }}</span>
                        </div>

                        <div class="title">
                            {{ Str::limit($product->description, 55) }}
                        </div>

                        <div class="specs">
                            @if($product->pieces_per_box)
                                <div><span class="spec-label">CAJA:</span> {{ $product->pieces_per_box }} Pzas</div>
                            @endif
                            
                            @if($product->upc)
                                <div style="margin-top: 2px;"><span class="spec-label">UPC:</span> {{ $product->upc }}
                                @if($product->length)
                                        <br>
                                        <span class="spec-label-1"> MEDIDAS:</span> 
                                        {{ floatval($product->length) }}x{{ floatval($product->width) }}x{{ floatval($product->height) }}
                                @endif                                        
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="price-container">
                        <span class="price-label">Precio Unitario</span>
                        <span class="price">${{ number_format($product->unit_price, 2) }}</span>
                    </div>

                </div>
            </div>

            @if($loop->iteration % 2 == 0)
                <div style="clear: both;"></div>
            @endif
        @endforeach
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Pág. {PAGE_NUM} / {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $pdf->page_text(520, 800, $text, $font, $size, array(0.4,0.4,0.4));
        }
    </script>

</body>
</html>