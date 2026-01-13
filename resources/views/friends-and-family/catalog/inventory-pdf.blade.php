<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Conteo Físico - Minmer</title>
    <style>
        @page {
            margin: 1cm;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        body {
            margin-top: 2.5cm;
            margin-bottom: 1.5cm;
            background-color: #ffffff;
            color: #2b2b2b;
            font-size: 11px;
        }

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            border-bottom: 4px solid #ff9c00;
        }

        .header-table { width: 100%; border-collapse: collapse; }
        
        .header-logo img {
            height: 1.2cm;
            object-fit: contain;
        }

        .header-title {
            color: #2c3856;
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            text-align: right;
        }

        .header-meta {
            color: #666666;
            font-size: 10px;
            text-align: right;
            margin-top: 4px;
        }

        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1cm;
            border-top: 1px solid #eeeeee;
            text-align: center;
            line-height: 1cm;
            font-size: 9px;
            color: #666666;
        }

        .inv-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .inv-table th {
            background-color: #2c3856;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px;
            text-align: left;
            border-bottom: 3px solid #ff9c00;
        }

        .inv-table td {
            padding: 8px;
            vertical-align: middle;
            border-bottom: 1px solid #e0e0e0;
        }

        .inv-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .col-img img {
            width: 45px;
            height: 45px;
            object-fit: contain;
            border-radius: 4px;
            border: 1px solid #eee;
            background: white;
        }

        .sku {
            color: #2c3856;
            font-weight: 800;
            font-size: 12px;
            display: block;
        }

        .desc {
            font-size: 10px;
            color: #2b2b2b;
            font-weight: bold;
            display: block;
            margin-top: 2px;
        }

        .meta {
            font-size: 9px;
            color: #666666;
            margin-top: 2px;
        }

        .system-stock {
            color: #bbb;
            font-size: 10px;
            text-align: center;
        }

        .write-box {
            border: 2px solid #666666;
            border-radius: 4px;
            height: 30px;
            width: 100%;
            background: #ffffff;
        }

        .status-options {
            display: inline-block;
            margin-top: 4px;
        }
        .status-circle {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #666666;
            border-radius: 50%;
            margin-right: 4px;
            font-size: 8px;
            text-align: center;
            line-height: 11px;
            color: #666666;
        }

    </style>
</head>
<body>

    <header>
        <table class="header-table">
            <tr>
                <td width="30%" valign="bottom">
                    <div class="header-logo">
                        <img src="{{ $logo_url }}" alt="Minmer Global">
                    </div>
                </td>
                
                <td width="70%" valign="bottom">
                    <div class="header-title">Toma de Inventario Físico</div>
                    <div class="header-meta">
                        {{ Auth::user()->area ? Auth::user()->area->name : 'Global' }} | 
                        Fecha: {{ date('d/m/Y H:i') }} | 
                        Resp: ___________________
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Minmer Global Control Tower • Documento Interno • Pág. <span class="page-number"></span>
    </footer>

    <table class="inv-table">
        <thead>
            <tr>
                <th width="10%" align="center">FOTO</th>
                <th width="40%">DESCRIPCIÓN / SKU</th>
                <th width="10%" align="center">SISTEMA</th>
                <th width="15%" align="center">CONTEO</th>
                <th width="25%">NOTAS / ESTADO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td class="col-img" align="center">
                    @if($product->photo_url)
                        <img src="{{ $product->photo_url }}" alt="Foto">
                    @else
                        <div style="width:45px; height:45px; background:#f0f0f0; border-radius:4px; line-height:45px; text-align:center; color:#ccc; font-size:9px;">N/A</div>
                    @endif
                </td>

                <td>
                    <span class="sku">{{ $product->sku }}</span>
                    <span class="desc">{{ $product->description }}</span>
                    <div class="meta">
                        {{ $product->brand ?? '' }} • {{ $product->type ?? '' }}
                        @if($product->upc) • UPC: {{ $product->upc }} @endif
                    </div>
                </td>

                <td class="system-stock">
                    {{ $product->movements_sum_quantity ?? 0 }}
                </td>

                <td>
                    <div class="write-box"></div>
                </td>

                <td>
                    <div style="border-bottom: 1px solid #ccc; height: 15px; width: 100%; margin-bottom: 5px;"></div>
                    <div style="color: #666666; font-size: 8px;">
                        <span class="status-circle">B</span> Bien
                        <span class="status-circle">D</span> Dañado
                        <span class="status-circle">C</span> Caducado
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "{PAGE_NUM} / {PAGE_COUNT}";
            $size = 9;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $pdf->page_text(540, 810, $text, $font, $size, array(0.4,0.4,0.4));
        }
    </script>
</body>
</html>