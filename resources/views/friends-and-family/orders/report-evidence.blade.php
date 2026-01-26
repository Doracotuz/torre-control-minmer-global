<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entregable Ejecutivo</title>
    <style>
        @page {
            margin: 0;
            size: 29.7cm 21cm;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #ffffff;
            color: #333333;
        }

        .slide {
            width: 29.7cm;
            height: 21cm;
            position: relative;
            overflow: hidden;
            page-break-after: always;
            background-color: #ffffff;
        }

        .slide:last-child {
            page-break-after: avoid;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            opacity: 0.08;
            z-index: 0;
            pointer-events: none;
        }

        .sidebar {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 70px;
            background-color: #2c3856;
            z-index: 10;
            text-align: center;
            padding-top: 30px;
        }

        .minmer-logo-sidebar {
            width: 50px;
            height: auto;
            opacity: 0.9;
        }

        .client-logo-header {
            position: absolute;
            top: 40px;
            right: 50px;
            height: 60px;
            max-width: 200px;
            object-fit: contain;
            z-index: 20;
        }

        h1 { font-size: 42px; font-weight: 900; color: #2c3856; margin: 0; text-transform: uppercase; line-height: 0.9; }
        h2 { font-size: 16px; font-weight: bold; color: #ff9c00; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 3px; }
        h3 { font-size: 14px; font-weight: bold; color: #2c3856; border-bottom: 2px solid #ff9c00; display: inline-block; margin-bottom: 20px; text-transform: uppercase; padding-bottom: 5px; }

        .content {
            position: absolute;
            top: 110px;
            left: 110px;
            right: 50px;
            bottom: 50px;
            z-index: 5;
        }

        .folio-badge {
            background-color: #f4f6f8;
            border-left: 6px solid #ff9c00;
            padding: 15px 25px;
            margin-top: 40px;
            display: inline-block;
        }
        .folio-number { font-size: 32px; font-weight: 900; color: #2c3856; }
        .folio-label { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 5px; }

        .client-info-grid {
            display: table;
            width: 100%;
            margin-top: 60px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        .info-col {
            display: table-cell;
            width: 33%;
            vertical-align: top;
            padding-right: 20px;
        }
        .label { font-size: 9px; color: #a0aec0; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 4px; }
        .value { font-size: 11px; font-weight: bold; color: #2d3748; line-height: 1.4; }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .styled-table thead th {
            background-color: #2c3856;
            color: #ffffff;
            padding: 10px 12px;
            text-align: left;
            text-transform: uppercase;
        }
        .styled-table tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }
        .styled-table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .styled-table tbody tr:last-child td {
            border-bottom: 2px solid #2c3856;
        }

        .evidence-grid {
            margin-top: 10px;
            width: 100%;
        }
        .evidence-row {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            table-layout: fixed;
        }
        .evidence-cell {
            display: table-cell;
            width: 32%;
            padding-right: 2%;
            vertical-align: top;
        }
        .evidence-cell:last-child {
            padding-right: 0;
        }
        
        .ev-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 5px;
            border-radius: 6px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.06);
            height: 280px;
        }
        
        .ev-img-container {
            height: 220px;
            width: 100%;
            background-color: #f8fafc;
            margin-bottom: 8px;
            display: table;
            border-bottom: 1px solid #eee;
            border-radius: 4px;
        }
        
        .ev-img-inner {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            height: 220px;
        }

        .ev-img {
            max-width: 100%;
            max-height: 220px;
            width: auto;
            height: auto;
            margin: 0 auto;
            display: block;
        }

        .pdf-icon {
            width: 100px;
            height: auto;
            opacity: 1;
            display: inline-block;
        }

        .ev-name {
            font-size: 9px;
            font-weight: bold;
            color: #2c3856;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 0 5px;
        }
        .btn-link {
            display: inline-block;
            background-color: #2c3856;
            color: #ffffff;
            text-decoration: none;
            font-size: 9px;
            font-weight: bold;
            padding: 5px 12px;
            border-radius: 4px;
            margin-top: 5px;
            text-transform: uppercase;
        }

        .thank-you-slide {
            background-color: #2c3856;
            width: 29.7cm;
            height: 21cm;
            position: relative;
            color: #ffffff;
            overflow: hidden;
        }
        .ty-circle {
            position: absolute;
            top: -150px;
            right: -150px;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.05);
        }
        .ty-content {
            position: absolute;
            top: 30%;
            left: 100px;
        }
        .ty-title {
            font-size: 70px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 20px;
        }
        .ty-text {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.5;
            margin-bottom: 40px;
            max-width: 500px;
        }
        .contact-box {
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 20px;
        }
        .contact-row {
            font-size: 12px;
            margin-bottom: 10px;
            display: block;
        }
        .icon-text {
            color: #ff9c00;
            font-weight: bold;
            margin-right: 10px;
        }

        .powered-by {
            position: absolute;
            bottom: 30px;
            right: 50px;
            text-align: right;
        }
        .powered-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 5px;
            display: block;
        }
        .powered-logo {
            height: 35px;
            opacity: 0.9;
        }

        .footer {
            position: absolute;
            bottom: 15px;
            right: 50px;
            font-size: 9px;
            color: #cbd5e0;
        }
    </style>
</head>
<body>

    @php 
        $pageNum = 1; 
        $itemsPerPage = 13;
        $evidencesPerPage = 6;
    @endphp

    <div class="slide">
        <div class="sidebar">
            @if(isset($system_logo) && $system_logo)
                <img src="{{ $system_logo }}" class="minmer-logo-sidebar">
            @endif
        </div>
        
        @if(isset($watermark_logo) && $watermark_logo)
            <img src="{{ $watermark_logo }}" class="watermark">
        @endif

        @if($logo_path)
            <img src="{{ $logo_path }}" class="client-logo-header">
        @endif

        <div class="content" style="top: 140px;">
            <h2>Reporte de Servicio</h2>
            <h1>Entregable<br>Ejecutivo</h1>
            <div style="font-size: 22px; color: #4a5568; margin-top: 15px; font-weight: 300;">{{ $header->company_name }}</div>

            <div class="folio-badge">
                <span class="folio-label">Folio del Proyecto</span>
                <span class="folio-number">#{{ str_pad($header->folio, 5, '0', STR_PAD_LEFT) }}</span>
            </div>

            <div class="client-info-grid">
                <div class="info-col">
                    <span class="label">Cliente</span>
                    <span class="value">{{ $header->client_name }}</span>
                    <div style="font-size: 10px; color: #718096; margin-top: 3px;">{{ $header->surtidor_name }}</div>
                </div>
                <div class="info-col">
                    <span class="label">Ubicación de Entrega</span>
                    <span class="value">{{ $header->locality }}</span>
                    <div style="font-size: 10px; color: #718096; margin-top: 3px;">
                        {{ Str::limit($header->address, 60) }}
                    </div>
                </div>
                <div class="info-col">
                    <span class="label">Fecha de Emisión</span>
                    <span class="value">{{ $date->format('d F, Y') }}</span>
                    <div style="font-size: 10px; color: #718096; margin-top: 3px;">
                        Entrega: {{ $header->delivery_date ? $header->delivery_date->format('d/m/Y') : '--' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="footer">Minmer Global | Control Tower | Página {{ $pageNum++ }}</div>
    </div>

    @foreach($items->chunk($itemsPerPage) as $chunk)
    <div class="slide">
        <div class="sidebar">
            @if(isset($system_logo) && $system_logo)
                <img src="{{ $system_logo }}" class="minmer-logo-sidebar">
            @endif
        </div>

        @if(isset($watermark_logo) && $watermark_logo)
            <img src="{{ $watermark_logo }}" class="watermark">
        @endif

        @if($logo_path) <img src="{{ $logo_path }}" class="client-logo-header" style="height: 35px; top: 30px;"> @endif

        <div class="content" style="top: 80px;">
            <h3>Resumen de Artículos</h3>
            <p style="font-size: 11px; color: #718096; margin-bottom: 20px;">
                Desglose detallado de los productos y materiales incluidos (Página {{ $loop->iteration }}).
            </p>

            <table class="styled-table">
                <thead>
                    <tr>
                        <th width="15%">SKU</th>
                        <th width="50%">Descripción</th>
                        <th width="15%">Almacén</th>
                        <th width="10%" style="text-align: center;">Cant.</th>
                        <th width="10%" style="text-align: center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chunk as $item)
                    <tr>
                        <td style="font-weight: bold; font-family: monospace;">{{ $item->product->sku }}</td>
                        <td>{{ $item->product->description }}</td>
                        <td>{{ $item->warehouse ? $item->warehouse->code : 'General' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ abs($item->quantity) }}</td>
                        <td style="text-align: center;">
                            <span style="color: #2c3856; font-weight: bold; font-size: 9px;">ENTREGADO</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="footer">Minmer Global | Control Tower | Página {{ $pageNum++ }}</div>
    </div>
    @endforeach

    @foreach($evidences->chunk($evidencesPerPage) as $evidenceChunk)
    <div class="slide">
        <div class="sidebar">
            @if(isset($system_logo) && $system_logo)
                <img src="{{ $system_logo }}" class="minmer-logo-sidebar">
            @endif
        </div>

        @if(isset($watermark_logo) && $watermark_logo)
            <img src="{{ $watermark_logo }}" class="watermark">
        @endif

        @if($logo_path) <img src="{{ $logo_path }}" class="client-logo-header" style="height: 35px; top: 30px;"> @endif

        <div class="content" style="top: 80px;">
            <h3>Registro de Evidencias</h3>
            <p style="font-size: 11px; color: #718096; margin-bottom: 20px;">
                Documentación visual y archivos adjuntos del servicio (Página {{ $loop->iteration }}).
            </p>

            <div class="evidence-grid">
                @foreach($evidenceChunk->chunk(3) as $row)
                    <div class="evidence-row">
                        @foreach($row as $ev)
                            <div class="evidence-cell">
                                <div class="ev-card">
                                    <div class="ev-img-container">
                                        <div class="ev-img-inner">
                                            @if($ev['is_image'])
                                                <img src="{{ $ev['local_path'] }}" class="ev-img">
                                            @else
                                                @if(isset($pdf_logo) && $pdf_logo)
                                                    <img src="{{ $pdf_logo }}" class="pdf-icon">
                                                @else
                                                    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAAclBMVEUAAAD///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////9e30x8AAAAJXRSTlMAAQIDBAUGBwgJCgsMDQ4PEBESExQVFhcYGRobHB0eHyAhIiMk6u3kwwAAAXxJREFUeNrtltdOw0AQRa/tXccuTgyY3ntv/v9fI2tGlnK8E44QZzzx7szO7I52jUaO/4iU00rK5Q80S+X08q+JclE7P19V/62o61Xz81C7qJTTyr/Fm00mnd4gn02+W/w+Y/H7jM03i/cZiw823yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nw23yzeZ+J85t9n4nzm/w9+Aet5F9w3EAAAAABJRU5ErkJggg==" class="pdf-icon">
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="ev-name" title="{{ $ev['filename'] }}">{{ $ev['filename'] }}</div>
                                    
                                    @if(!$ev['is_image'])
                                        <a href="{{ $ev['remote_url'] }}" target="_blank" class="btn-link">Ver Documento</a>
                                    @else
                                        <div style="font-size: 8px; color: #cbd5e0; margin-top: 5px;">IMAGEN ADJUNTA</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
        <div class="footer">Minmer Global | Control Tower | Página {{ $pageNum++ }}</div>
    </div>
    @endforeach

    <div class="slide thank-you-slide">
        <div class="ty-circle"></div>
        
        <div class="ty-content">
            <div class="ty-title">GRACIAS</div>
            <div class="ty-text">
                Agradecemos su confianza.<br>
                Estamos comprometidos con la excelencia operativa en cada entrega.
            </div>

            <div class="contact-box">
                <div style="font-size: 10px; letter-spacing: 2px; color: #ff9c00; margin-bottom: 15px; font-weight: bold; text-transform: uppercase;">Contacto</div>
                
                @if($company['emitter_phone'] != 'Pendiente de definir')
                    <span class="contact-row"><span class="icon-text">T:</span> {{ $company['emitter_phone'] }}</span>
                @endif
                <span class="contact-row"><span class="icon-text">E:</span> contacto@minmerglobal.com</span>
                <span class="contact-row"><span class="icon-text">W:</span> www.minmerglobal.com</span>
            </div>
        </div>

        <div class="powered-by">
            <span class="powered-label">Powered By</span>
            @if(isset($system_logo) && $system_logo)
                <img src="{{ $system_logo }}" class="powered-logo">
            @else
                <div style="font-weight: bold; font-size: 18px;">MINMER GLOBAL</div>
            @endif
        </div>
    </div>

</body>
</html>