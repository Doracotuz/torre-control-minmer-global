<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formato de Toma de Inventario</title>
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
        .header-title h1 { font-size: 24px; text-transform: uppercase; margin: 0; }
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

        .products-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px 15px;
        }

        .product-card {
            background-color: #fff;
            border: 1px solid #9ca3af;
            border-radius: 6px;
            overflow: hidden;
            height: 260px; 
            width: 100%;
            position: relative;
        }

        .card-content {
            display: table;
            width: 100%;
            height: 100%;
        }

        .img-cell {
            display: table-cell;
            width: 35%;
            vertical-align: middle;
            text-align: center;
            background-color: #fff;
            border-right: 1px solid #e5e7eb;
            padding: 5px;
        }

        .img-cell img {
            max-height: 120px;
            max-width: 90%;
            object-fit: contain;
        }

        .info-cell {
            display: table-cell;
            width: 65%;
            vertical-align: top;
            padding: 10px;
        }

        .sku-row {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .sku {
            font-size: 16px;
            font-weight: 900;
            color: #2c3856;
            font-family: monospace;
        }

        .brand {
            font-size: 10px;
            color: #6b7280;
            font-weight: bold;
            text-transform: uppercase;
            float: right;
            margin-top: 4px;
        }

        .description {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            line-height: 1.2;
            height: 28px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .inventory-input-area {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 8px;
            border-radius: 4px;
            margin-top: 5px;
        }

        .input-row {
            margin-bottom: 8px;
        }

        .input-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #4b5563;
            display: block;
            margin-bottom: 2px;
        }

        .write-box {
            border: 1px solid #64748b;
            background: white;
            height: 25px;
            width: 100%;
        }

        .write-line {
            border-bottom: 1px solid #64748b;
            height: 15px;
            width: 100%;
            margin-top: 10px;
        }

        .meta-info {
            font-size: 9px;
            color: #666;
            margin-top: 4px;
        }

    </style>
</head>
<body>

    <header>
        <table class="header-table">
            <tr>
                <td class="header-title">
                    <h1>Toma de Inventario Físico</h1>
                    <h2>{{ Auth::user()->area ? Auth::user()->area->name : 'General' }} - Fecha: {{ date('d/m/Y') }}</h2>
                </td>
                <td class="header-logo" align="right">
                    <img src="{{ $logo_url }}" alt="Logo">
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Responsable de Conteo: __________________________ • Auditor: __________________________ • Pág. <span class="page-number"></span>
    </footer>

    <table class="products-table">
        @foreach($products->chunk(2) as $row)
            <tr>
                @foreach($row as $product)
                    <td width="50%" valign="top">
                        <div class="product-card">
                            <div class="card-content">
                                
                                <div class="img-cell">
                                    <img src="{{ $product->photo_url }}" alt="Img">
                                    <div style="margin-top: 5px; font-size: 8px; color: #888;">
                                        {{ $product->upc ?? 'Sin UPC' }}
                                    </div>
                                </div>

                                <div class="info-cell">
                                    <div class="sku-row">
                                        <span class="sku">{{ $product->sku }}</span>
                                        <span class="brand">{{ Str::limit($product->brand ?? 'GENÉRICO', 10) }}</span>
                                    </div>

                                    <div class="description">
                                        {{ Str::limit($product->description, 75) }}
                                    </div>

                                    <div class="inventory-input-area">
                                        <table width="100%">
                                            <tr>
                                                <td width="50%" style="padding-right: 5px;">
                                                    <span class="input-label">Conteo Físico</span>
                                                    <div class="write-box"></div>
                                                </td>
                                                <td width="50%" style="padding-left: 5px;">
                                                    <span class="input-label">Ubicación</span>
                                                    <div class="write-box"></div>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <div style="margin-top: 8px;">
                                            <span class="input-label">Comentarios / Estado del producto</span>
                                            <div class="write-line"></div>
                                        </div>
                                    </div>

                                    <div class="meta-info">
                                        Sistema: {{ $product->movements_sum_quantity ?? 0 }} | Caja: {{ $product->pieces_per_box ?? 1 }} pz
                                    </div>
                                </div>

                            </div>
                        </div>
                    </td>

                    @if($loop->count == 1 && $loop->parent->count == 1) 
                         <td width="50%"></td>
                    @endif
                @endforeach
            </tr>
        @endforeach
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "{PAGE_NUM} / {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $pdf->page_text(540, 800, $text, $font, $size, array(0.4,0.4,0.4));
        }
    </script>

</body>
</html>