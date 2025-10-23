<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pick List - {{ $pickList->salesOrder->so_number }}</title>
    <style>
        /* --- General --- */
        @page { margin: 25mm 20mm 25mm 20mm; } /* Márgenes de la página */
        body { font-family: 'Helvetica', sans-serif; font-size: 9pt; color: #333; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        h1, h2, h3 { margin: 0 0 10px 0; padding: 0; color: #111; }
        p { margin: 3px 0; }
        .page-break { page-break-after: always; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: bold; }

        /* --- Encabezado --- */
        .header-table td { padding: 0; vertical-align: top; }
        .logo { max-height: 60px; width: auto; margin-bottom: 10px; }
        .doc-title { font-size: 18pt; font-weight: bold; margin-bottom: 5px; color: #000; }
        .header-info p { margin: 1px 0; font-size: 9pt; }
        .qr-code { text-align: right; padding-top: 5px; }
        .qr-code img { max-width: 80px; height: auto; } /* Tamaño QR ajustado */

        /* --- Sección Info Pedido --- */
        .info-table { margin-bottom: 20px; border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 10px 0; }
        .info-table td { padding: 3px 5px; font-size: 9pt; vertical-align: top; }
        .info-table strong { color: #555; }

        /* --- Tabla de Productos --- */
        .details-table { margin-top: 5px; }
        .details-table th, .details-table td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; vertical-align: middle; /* Alineación vertical */ }
        .details-table th { background-color: #e9ecef; font-weight: bold; font-size: 8pt; color: #495057; text-transform: uppercase; }
        /* Zebra striping */
        .details-table tbody tr:nth-child(even) { background-color: #f8f9fa; }
        .location-cell { font-size: 11pt; color: #D32F2F; /* Rojo oscuro */ }
        .lpn-cell { font-size: 9pt; }
        .quality-cell { font-size: 8pt; color: #17a2b8; /* Azul claro */ }
        .sku-cell { font-size: 8pt; color: #6c757d; /* Gris */ }
        .product-name { font-size: 9pt; }
        .pedimento-cell { font-size: 8pt; }
        .quantity-cell { font-size: 14pt; }
        .check-cell { font-size: 16pt; } /* Tamaño del cuadro de check */

        /* --- Sección Firma --- */
        .signature-section { margin-top: 60px; text-align: center; page-break-inside: avoid; }
        .signature-line { border-bottom: 1px solid #555; width: 250px; margin: 0 auto; padding-top: 40px; }
        .signature-label { font-size: 8pt; color: #666; margin-top: 5px; }

        /* --- Footer --- */
        #footer { position: fixed; bottom: -50px; /* Ajusta para que quede dentro del margen */ left: 0px; right: 0px; height: 50px; text-align: center; font-size: 8pt; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    {{-- Encabezado --}}
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                @if($logoBase64) <img src="{{ $logoBase64 }}" alt="Logo" class="logo"> @endif
                 <h1 class="doc-title">Pick List</h1>
            </td>
            <td style="width: 40%;" class="header-info">
                <p class="text-right"><strong>Orden de Venta:</strong> {{ $pickList->salesOrder->so_number }}</p>
                <p class="text-right"><strong>Pick List ID:</strong> #{{ $pickList->id }}</p>
                <p class="text-right"><strong>Fecha Emisión:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                {{-- Código QR --}}
                <div class="qr-code">
                    @if($qrCodeDataUri)
                        <img src="{{ $qrCodeDataUri }}" alt="QR Code">
                    @else
                        <span style="font-size: 8px; color: red;">Error al generar QR</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Info Pedido --}}
    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>Cliente:</strong><br>
                {{ $pickList->salesOrder->customer_name }}
            </td>
            <td style="width: 25%;">
                <strong>Fecha Entrega:</strong><br>
                {{ $pickList->salesOrder->order_date->format('d/m/Y') }}
            </td>
             <td style="width: 25%;">
                <strong>Factura:</strong><br>
                {{ $pickList->salesOrder->invoice_number ?? 'N/A' }}
            </td>
        </tr>
    </table>

    {{-- Título Productos --}}
    <h3>Productos a Surtir</h3>

    {{-- Tabla de Productos --}}
    <table class="details-table">
        <thead>
            <tr>
                <th style="width: 22%;">Ubicación</th>
                <th style="width: 15%;">LPN / Calidad</th>
                <th style="width: 33%;">SKU / Producto</th>
                <th style="width: 15%;">Pedimento</th>
                <th style="width: 10%;" class="text-right">Cantidad</th>
                <th style="width: 5%;" class="text-center">Check</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pickList->items as $item)
            <tr>
                {{-- Ubicación --}}
                <td class="location-cell font-mono font-bold">
                    @if($item->location)
                        {{ $item->location->aisle }}-{{ $item->location->rack }}-{{ $item->location->shelf }}-{{ $item->location->bin }}
                    @else
                        N/A
                    @endif
                </td>
                {{-- LPN / Calidad --}}
                <td>
                    <span class="lpn-cell font-mono font-bold">{{ $item->pallet->lpn ?? 'N/A' }}</span><br>
                    <span class="quality-cell font-bold">{{ $item->quality->name ?? 'N/A' }}</span>
                </td>
                {{-- SKU / Producto --}}
                <td>
                    <span class="product-name">{{ $item->product->name ?? 'N/A' }}</span><br>
                    <span class="sku-cell font-mono">{{ $item->product->sku ?? 'N/A' }}</span>
                </td>
                {{-- Pedimento --}}
                <td class="pedimento-cell font-mono">
                    {{ $item->pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}
                </td>
                {{-- Cantidad --}}
                <td class="text-right font-bold quantity-cell">
                    {{ $item->quantity_to_pick }}
                </td>
                {{-- Checkbox manual --}}
                <td class="text-center check-cell">  </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Sección de Firma --}}
    <div class="signature-section">
        <div class="signature-line"></div>
        <p class="signature-label">Nombre y Firma del Surtidor</p>

        <div class="signature-line" style="margin-top: 40px;"></div>
        <p class="signature-label">Fecha y Hora de Finalización</p>
    </div>

    {{-- Footer con Número de Página --}}
    <div id="footer">
        Documento generado el {{ now()->format('d/m/Y H:i:s') }} - Página <span class="page"></span>
    </div>

    {{-- Script para número de página (si tu generador de PDF lo soporta - dompdf lo hace) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("Helvetica", "normal"); // Especifica normal
            $width = $fontMetrics->get_text_width($text, $font, $size);
            // Centrar el texto en el footer
            $x = ($pdf->get_width() - $width) / 2;
            // Posición Y ajustada para estar dentro del margen inferior
            $y = $pdf->get_height() - $pdf->get_option('margin_bottom') + 10; // Ajusta +10 según necesites
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>