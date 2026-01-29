<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo</title>
    <style>
        @page {
            margin: 0.5cm;
            margin-top: 0cm;
            font-family: sans-serif;
        }

        body {
            margin-top: 3.2cm; /* Espacio para el header fijo */
            margin-bottom: 1.5cm;
            background-color: #fff;
        }

        /* HEADER & FOOTER OPTIMIZADOS */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 2.8cm;
            background-color: #2c3856;
            color: white;
            border-bottom: 4px solid #ff9c00;
        }

        .header-content {
            padding: 0.5cm 1cm;
        }

        .header-title h1 { font-size: 24px; text-transform: uppercase; margin: 0; }
        .header-title h2 { font-size: 10px; color: #ff9c00; margin-top: 5px; text-transform: uppercase; }
        .logo-img { height: 1.5cm; background: white; padding: 2px; border-radius: 2px; float: right; }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 0.8cm;
            background-color: #f3f4f6;
            text-align: center;
            font-size: 9px;
            line-height: 0.8cm;
            color: #666;
            border-top: 1px solid #ddd;
        }

        /* GRID SYSTEM - INLINE BLOCK (Más rápido que Float) */
        .product-card {
            display: inline-block;
            width: 46%; /* Menos del 50% para evitar saltos por bordes */
            margin-right: 2%; 
            margin-bottom: 20px;
            vertical-align: top;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fff;
            height: 440px; /* Altura fija estricta para evitar recálculos */
            overflow: hidden;
            page-break-inside: avoid; /* CRÍTICO */
        }

        /* BADGE ESTÁTICO (Sin position absolute) */
        .status-bar {
            width: 100%;
            height: 20px;
            line-height: 20px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }
        .bg-green { background-color: #10b981; }
        .bg-red { background-color: #ef4444; }

        .img-container {
            height: 180px;
            width: 100%;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
            /* Centrado vertical clásico rápido */
            padding-top: 10px; 
        }

        .img-container img {
            max-height: 160px;
            max-width: 90%;
            object-fit: contain;
        }

        .card-body {
            padding: 10px;
            height: 150px; /* Altura fija para contenido */
        }

        .brand { font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; display: block; }
        .sku { font-size: 12px; color: #374151; font-weight: bold; float: right; }
        
        .title {
            font-size: 13px;
            font-weight: bold;
            color: #111;
            margin-top: 5px;
            height: 35px; /* Limite visual */
            overflow: hidden;
            line-height: 1.2;
        }

        .specs {
            margin-top: 8px;
            font-size: 10px;
            color: #444;
            line-height: 1.4;
        }
        
        .spec-label { font-weight: bold; color: #666; }
        .spec-label-hl { color: #ff9c00; font-weight: bold; }

        .price-container {
            background-color: #f8fafc;
            border-top: 1px solid #eee;
            height: 60px; /* Resto de la altura */
            padding: 5px 10px;
            text-align: right;
        }

        .price-lbl { display: block; font-size: 8px; text-transform: uppercase; color: #888; }
        .price-val { display: block; font-size: 22px; font-weight: bold; color: #2c3856; }

    </style>
</head>
<body>

    <header>
        <div class="header-content">
            <img src="{{ $logo_url }}" class="logo-img" alt="Logo">
            <div class="header-title">
                <h1>Catálogo</h1>
                <h2>{{ Auth::user()->area ? Auth::user()->area->name : 'General' }}{{ $percentage_text ?? '' }}</h2>
            </div>
        </div>
    </header>

    <footer>
        Minmer Global • {{ $date }} • Pág. <span class="page-number"></span>
    </footer>

    <div style="width: 100%;">
        @foreach($products as $product)
            <div class="product-card">
                
                @if($product->is_active)
                    <div class="status-bar bg-green">Disponible</div>
                @else
                    <div class="status-bar bg-red">Agotado</div>
                @endif

                <div class="img-container">
                    @if($product->photo_url)
                        <img src="{{ $product->photo_url }}" alt="Prod">
                    @else
                        <div style="color: #ccc; padding-top: 60px;">Sin Foto</div>
                    @endif
                </div>

                <div class="card-body">
                    <div>
                        <span class="sku">{{ $product->sku }}</span>
                        <span class="brand">{{ Str::limit($product->brand ?? 'GENÉRICO', 12) }}</span>
                    </div>

                    <div class="title">
                        {{ Str::limit($product->description, 50) }}
                    </div>

                    <div class="specs">
                        @if($product->pieces_per_box)
                            <div><span class="spec-label">Caja:</span> {{ $product->pieces_per_box }} pzas</div>
                        @endif
                        
                        @if($product->upc)
                            <div><span class="spec-label">UPC:</span> {{ $product->upc }}</div>
                        @endif

                        @if($product->length)
                            <div>
                                <span class="spec-label-hl">MEDIDAS:</span> 
                                {{ floatval($product->length) }}x{{ floatval($product->width) }}x{{ floatval($product->height) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="price-container">
                    <span class="price-lbl">Precio Unitario</span>
                    <span class="price-val">${{ number_format($product->unit_price, 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "{PAGE_NUM} / {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $pdf->page_text(540, 820, $text, $font, $size, array(0.5,0.5,0.5));
        }
    </script>

</body>
</html>