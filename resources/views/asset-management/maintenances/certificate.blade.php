<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Mantenimiento de Activo</title>
    <style>
        /* --- ESTILOS GENERALES Y PALETA DE COLORES --- */
        @page {
            margin: 3.5cm 1.5cm 3cm 1.5cm;
        }
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 11px; 
            color: #2b2b2b;
            line-height: 1.6;
        }
        :root {
            --color-primary: #2c3856;
            --color-accent: #ff9c00;
        }
        
        /* --- ENCABEZADO Y PIE DE PÁGINA --- */
        header {
            position: fixed;
            top: -3cm;
            left: 0cm;
            right: 0cm;
            height: 2.5cm;
            text-align: center;
        }
        header img {
            width: 180px;
            height: auto;
        }
        footer {
            position: fixed; 
            bottom: -2.5cm; 
            left: 0cm; 
            right: 0cm;
            height: 2cm;
            font-size: 9px;
            color: #666666;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        
        /* --- TÍTULOS Y SECCIONES --- */
        h1 {
            color: var(--color-primary);
            font-size: 20px;
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid var(--color-accent);
            padding-bottom: 10px;
        }
        h2 {
            color: var(--color-primary);
            font-size: 14px;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        
        /* --- TABLAS DE DETALLES --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: var(--color-primary);
            width: 30%;
        }
        
        /* --- TEXTOS Y FIRMAS --- */
        .text-justify { text-align: justify; }
        .notes-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 3px solid var(--color-accent);
        }
        .signatures { margin-top: 60px; width: 100%; page-break-inside: avoid; }
        .signature-box { display: inline-block; width: 48%; text-align: center; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; }
        strong { color: #000; }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;
        Carbon::setLocale('es');
    @endphp

    <header>
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo de la Empresa">
        @endif
    </header>

    <footer>
        <div>
            <strong>Estrategias y Soluciones Minmer Global</strong> | 
            {{ $maintenance->asset->site->address ?? 'Dirección no especificada' }} | 
            Tel: +52 33 3022 1806
        </div>
        <div>ID del Mantenimiento: MANT-{{ $maintenance->id }}-{{ $maintenance->asset->asset_tag }}</div>
    </footer>

    <main>
        <h1>Certificado de Mantenimiento</h1>

        <p class="text-justify">
            El presente documento certifica que se ha realizado un servicio de mantenimiento al activo de TI propiedad de <strong>Estrategias y Soluciones Minmer Global</strong>, cuyos detalles se describen a continuación.
        </p>

        <h2>Detalles del Activo</h2>
        <table>
            <tr>
                <th>Etiqueta de Activo</th>
                <td><strong>{{ $maintenance->asset->asset_tag }}</strong></td>
            </tr>
            <tr>
                <th>Modelo</th>
                <td>{{ $maintenance->asset->model->manufacturer->name }} {{ $maintenance->asset->model->name }}</td>
            </tr>
            <tr>
                <th>Número de Serie</th>
                <td>{{ $maintenance->asset->serial_number }}</td>
            </tr>
        </table>

        <h2>Detalles del Servicio de Mantenimiento</h2>
        <table>
            <tr>
                <th>Tipo de Mantenimiento</th>
                <td>{{ $maintenance->type }}</td>
            </tr>
            <tr>
                <th>Proveedor / Técnico</th>
                <td>{{ $maintenance->supplier ?? 'Interno' }}</td>
            </tr>
            <tr>
                <th>Fechas del Servicio</th>
                <td>Del <strong>{{ Carbon::parse($maintenance->start_date)->isoFormat('LL') }}</strong> al <strong>{{ Carbon::parse($maintenance->end_date)->isoFormat('LL') }}</strong></td>
            </tr>
            <tr>
                <th>Costo Total del Servicio</th>
                <td>$ {{ number_format($maintenance->cost, 2) }} MXN</td>
            </tr>
        </table>

        <div class="notes-section">
            <h3 style="color: var(--color-primary); margin-top: 0;">Diagnóstico / Motivo de Entrada</h3>
            <p style="font-size: 10px;">{{ $maintenance->diagnosis }}</p>
        </div>

        <div class="notes-section">
            <h3 style="color: var(--color-primary); margin-top: 0;">Acciones Realizadas</h3>
            <p style="font-size: 10px;">{{ $maintenance->actions_taken }}</p>
        </div>

        @if($maintenance->parts_used)
        <div class="notes-section">
            <h3 style="color: var(--color-primary); margin-top: 0;">Insumos y Partes Utilizadas</h3>
            <p style="font-size: 10px;">{{ $maintenance->parts_used }}</p>
        </div>
        @endif
        
        {{-- SECCIÓN DE FIRMA DEL PERSONAL DE TI --}}
        <div class="signatures">
            <p class="text-justify">
                El personal de TI certifica que el servicio de mantenimiento descrito ha sido completado y el equipo ha sido verificado para su correcto funcionamiento, dejándolo en estatus de "En Almacén".
            </p>
            <div style="margin: 0 auto; width: 48%; text-align: center;">
                <div class="signature-line"></div>
                <p><strong>Responsable de TI</strong><br>Nombre y Firma</p>
            </div>
        </div>

    </main>
</body>
</html>