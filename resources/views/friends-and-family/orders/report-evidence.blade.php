<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Entrega - Folio {{ $header->folio }}</title>
    <style>
        @page { margin: 0px; }
        body { margin: 30px; font-family: 'Helvetica', 'Arial', sans-serif; color: #2b2b2b; line-height: 1.4; }
        
        .text-navy { color: #2c3856; }
        .text-orange { color: #ff9c00; }
        .bg-navy { background-color: #2c3856; }
        .bg-orange { background-color: #ff9c00; }
        .bg-gray { background-color: #f3f4f6; }

        .header-bg {
            position: absolute; top: 0; left: 0; right: 0; height: 120px;
            background-color: #f8fafc; border-bottom: 3px solid #ff9c00; z-index: -1;
        }
        .header-content { padding-top: 20px; width: 100%; }
        .logo { max-height: 60px; }
        .folio-box {
            float: right; text-align: right;
        }
        .folio-number {
            font-size: 24px; font-weight: 900; color: #2c3856;
        }

        h1, h2, h3 { font-weight: 700; margin: 0; }
        .section-title {
            font-size: 14px; text-transform: uppercase; letter-spacing: 1px;
            color: #ff9c00; border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px; margin-bottom: 10px; margin-top: 20px;
        }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 11px; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .label { font-weight: bold; color: #666666; width: 110px; }

        .items-table { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 10px; }
        .items-table th { 
            background-color: #2c3856; color: white; padding: 8px; text-align: left; font-weight: bold;
        }
        .items-table td { 
            border-bottom: 1px solid #eee; padding: 8px; color: #444;
        }
        .row-even { background-color: #f9fafb; }

        .evidence-container { margin-top: 20px; text-align: center; }
        .evidence-item {
            display: inline-block; width: 48%; margin: 1%; vertical-align: top;
            border: 1px solid #ddd; padding: 5px; border-radius: 4px; page-break-inside: avoid;
        }
        .evidence-img {
            width: 100%; height: 250px; object-fit: contain; background-color: #f3f3f3;
        }
        .evidence-label {
            font-size: 9px; color: #666; margin-top: 5px; text-align: left;
        }
        
        .pdf-file {
            background-color: #f8fafc; border: 1px dashed #2c3856; padding: 15px;
            border-radius: 5px; text-align: center; margin-bottom: 10px;
        }

        .footer {
            position: fixed; bottom: 0; left: 0; right: 0; height: 30px;
            background-color: #2c3856; color: white; text-align: center;
            font-size: 9px; line-height: 30px;
        }
    </style>
</head>
<body>
    <div class="header-bg"></div>

    <table class="header-content">
        <tr>
            <td style="width: 50%;">
                @if($logo_path)
                    <img src="{{ $logo_path }}" class="logo">
                @else
                    <h1 class="text-navy">MINMER</h1>
                @endif
                <div style="font-size: 9px; color: #666; margin-top: 5px;">
                    {{ $company['emitter_address'] }}<br>
                    Tel: {{ $company['emitter_phone'] }}
                </div>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: top;">
                <div class="folio-box">
                    <div style="font-size: 10px; color: #ff9c00; font-weight: bold; text-transform: uppercase;">Reporte de Entrega</div>
                    <div class="folio-number">#{{ $header->folio }}</div>
                    <div style="font-size: 11px; color: #666;">
                        {{ \Carbon\Carbon::parse($header->created_at)->format('d/m/Y') }}
                    </div>
                    <div style="margin-top: 5px;">
                        <span style="background-color: #eee; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; color: #2c3856; text-transform: uppercase;">
                            {{ $header->status }}
                        </span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-top: 30px;">
        <div class="section-title">Información del Cliente</div>
        <table class="info-table">
            <tr>
                <td class="label">Cliente:</td>
                <td><strong class="text-navy" style="font-size: 12px;">{{ $header->client_name }}</strong></td>
                <td class="label">Razón Social:</td>
                <td>{{ $header->company_name }}</td>
            </tr>
            <tr>
                <td class="label">Dirección:</td>
                <td colspan="3">{{ $header->address }}, {{ $header->locality }}</td>
            </tr>
            <tr>
                <td class="label">Surtidor:</td>
                <td>{{ $header->surtidor_name ?? 'N/A' }}</td>
                <td class="label">Fecha Entrega:</td>
                <td>{{ $header->delivery_date ? $header->delivery_date->format('d/m/Y H:i') : 'N/A' }}</td>
            </tr>
            @if($header->observations)
            <tr>
                <td class="label">Observaciones:</td>
                <td colspan="3" style="font-style: italic; color: #555;">{{ $header->observations }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="section-title">Detalle de Artículos Entregados</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Descripción</th>
                <th style="text-align: center;">Cant.</th>
                <th>Almacén</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr class="{{ $index % 2 == 0 ? 'row-even' : '' }}">
                    <td>{{ $item->product->sku }}</td>
                    <td>{{ $item->product->description }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ abs($item->quantity) }}</td>
                    <td>{{ $item->warehouse ? $item->warehouse->code : 'General' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="page-break-before: always;">
        <div class="section-title" style="margin-top: 0;">Evidencia Fotográfica y Documental</div>
        
        <p style="font-size: 10px; color: #666; margin-bottom: 20px;">
            A continuación se presentan los archivos adjuntos como prueba de entrega o documentación relacionada al pedido #{{ $header->folio }}.
        </p>

        <div class="evidence-container">
            @foreach($evidences as $ev)
                @if($ev['is_image'])
                    <div class="evidence-item">
                        <img src="{{ $ev['local_path'] }}" class="evidence-img">
                        <div class="evidence-label">
                            <strong>Archivo:</strong> {{ $ev['filename'] }}<br>
                            <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($ev['uploaded_at'])->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @else
                    {{-- Manejo de PDFs --}}
                    <div class="pdf-file">
                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAbUlEQVR4nO3MwQ2AIBBE0W9ohbZgS9qCrdgSjEcwEOMucyL53yR2XvMREZFhJq95NfM918z3/4w555p5r6/5t5n5+R4RERERERERERERERERERERERERERERERERERERERERERERERE50gC80wl1h9D7OAAAAABJRU5ErkJggg==" style="width: 24px; opacity: 0.5;">
                        <div style="font-size: 11px; font-weight: bold; color: #2c3856; margin-top: 5px;">Documento PDF Adjunto</div>
                        <div style="font-size: 10px; color: #555;">{{ $ev['filename'] }}</div>
                        <div style="font-size: 9px; color: #999; margin-top: 2px;">(Este archivo no se puede previsualizar, consulte el anexo original)</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="footer">
        Generado el {{ $date }} | Minmer Global - Control Tower
    </div>
</body>
</html>