<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Físico - Minmer Global</title>
    <style>
        @page {
            margin: 1cm;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        body {
            margin-top: 3cm;
            margin-bottom: 3cm;
            background-color: #ffffff;
            color: #2b2b2b;
            font-size: 11px;
        }

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2.5cm;
            border-bottom: 4px solid #ff9c00;
            background: white;
            z-index: 1000;
        }

        .header-tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-container img {
            height: 1.2cm;
            object-fit: contain;
        }

        .doc-title {
            color: #2c3856;
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            text-align: right;
            letter-spacing: -0.5px;
            margin-bottom: 10px;
        }

        .input-group {
            text-align: right;
            font-size: 10px;
            font-weight: bold;
            color: #2c3856;
            line-height: 1.8;
        }

        .manual-line {
            display: inline-block;
            border-bottom: 1px solid #2c3856;
            width: 150px;
            margin-left: 5px;
        }
        
        .manual-line-long {
            display: inline-block;
            border-bottom: 1px solid #2c3856;
            width: 250px;
            margin-left: 5px;
        }

        .signatures-section {
            position: fixed;
            bottom: 0.8cm;
            left: 0cm;
            right: 0cm;
            height: 1.8cm;
        }

        .sig-box {
            border-top: 1px solid #2b2b2b;
            padding-top: 5px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            color: #2c3856;
            text-transform: uppercase;
        }

        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 0.5cm;
            text-align: center;
            font-size: 8px;
            color: #999999;
            border-top: 1px solid #eeeeee;
            padding-top: 2px;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inventory-table th {
            background-color: #2c3856;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            padding: 10px;
            text-align: left;
            border-bottom: 3px solid #ff9c00;
        }

        .inventory-table td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }

        .inventory-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .cell-image {
            width: 140px;
            text-align: center;
            background-color: #ffffff;
            border-right: 1px solid #f0f0f0;
        }

        .product-img {
            max-width: 130px;
            max-height: 130px;
            object-fit: contain;
            border-radius: 6px;
            display: block;
            margin: 0 auto;
        }

        .cell-info { padding-left: 15px; }

        .sku-badge {
            display: inline-block;
            background-color: #ff9c00;
            color: white;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .product-name {
            font-size: 13px;
            font-weight: 800;
            color: #2c3856;
            margin-bottom: 4px;
            display: block;
            line-height: 1.3;
        }

        .product-details {
            font-size: 10px;
            color: #666666;
            line-height: 1.4;
        }

        .cell-count {
            width: 120px;
            text-align: center;
        }

        .write-box {
            height: 40px;
            width: 100%;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            background-color: #ffffff;
        }

        .cell-notes { width: 140px; }

        .notes-line {
            border-bottom: 1px solid #d1d5db;
            height: 20px;
            width: 100%;
            margin-bottom: 8px;
        }

        .status-row { text-align: center; }

        .status-pill {
            display: inline-block;
            border: 1px solid #9ca3af;
            color: #9ca3af;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 8px;
            margin: 0 2px;
            text-transform: uppercase;
        }

    </style>
</head>
<body>

    <header>
        <table class="header-tbl">
            <tr>
                <td width="30%" valign="top" class="logo-container" style="padding-top: 10px;">
                    <img src="{{ $logo_url }}" alt="Minmer Logo">
                </td>
                <td width="70%" valign="top">
                    <div class="doc-title">Toma de Inventario Físico</div>
                    
                    <div class="input-group">
                        <div>
                            UBICACIÓN: <span class="manual-line-long"></span>
                        </div>
                        <div style="margin-top: 5px;">
                            FECHA: <span class="manual-line"></span> &nbsp;&nbsp; HORA: <span class="manual-line" style="width: 80px;"></span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <div class="signatures-section">
        <table width="100%">
            <tr>
                <td width="10%"></td>
                <td width="35%">
                    <div class="sig-box">
                        <br>NOMBRE Y FIRMA DEL CONTADOR
                    </div>
                </td>
                <td width="10%"></td>
                <td width="35%">
                    <div class="sig-box">
                        <br>NOMBRE Y FIRMA DE QUIEN VERIFICÓ
                    </div>
                </td>
                <td width="10%"></td>
            </tr>
        </table>
    </div>

    <footer>
        Minmer Global Control Tower • Formato de Control Interno • Pág. <span class="pagenum"></span>
    </footer>

    <table class="inventory-table">
        <thead>
            <tr>
                <th width="20%" align="center">Fotografía</th>
                <th width="40%">Detalle del Producto</th>
                <th width="20%">Conteo Físico</th>
                <th width="20%">Estado / Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td class="cell-image">
                    @if($product->photo_url)
                        <img src="{{ $product->photo_url }}" class="product-img" alt="Prod">
                    @else
                        <div style="height: 80px; width: 80px; background: #f3f4f6; border-radius: 6px; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #ccc;">
                            SIN FOTO
                        </div>
                    @endif
                </td>
                
                <td class="cell-info">
                    <span class="sku-badge">{{ $product->sku }}</span>
                    <span class="product-name">{{ $product->description }}</span>
                    
                    <div class="product-details">
                        <strong>Marca:</strong> {{ $product->brand ?? 'N/A' }}<br>
                        <strong>Tipo:</strong> {{ $product->type ?? 'Gral' }}<br>
                        @if($product->upc)
                            <strong>UPC:</strong> {{ $product->upc }}
                        @endif
                    </div>

                    <div style="margin-top: 8px; font-size: 9px; color: #999;">
                        Stock Sistema: {{ $product->movements_sum_quantity ?? 0 }} unids.
                    </div>
                </td>

                <td class="cell-count">
                    <div style="font-size: 9px; text-transform: uppercase; color: #64748b; margin-bottom: 4px; text-align: left;">Cantidad:</div>
                    <div class="write-box"></div>
                </td>

                <td class="cell-notes">
                    <div style="font-size: 9px; text-transform: uppercase; color: #64748b; margin-bottom: 2px;">Observaciones:</div>
                    <div class="notes-line"></div>
                    <div class="status-row">
                        <span class="status-pill">OK</span>
                        <span class="status-pill">DAÑADO</span>
                        <span class="status-pill">CADUCADO</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "{PAGE_NUM} / {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("Helvetica");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $pdf->page_text(540, 825, $text, $font, $size, array(0.6, 0.6, 0.6));
        }
    </script>

</body>
</html>