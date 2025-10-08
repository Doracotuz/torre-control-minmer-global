<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Responsiva Consolidada</title>
    <style>
        @page { margin: 3.5cm 1.5cm 3cm 1.5cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #2b2b2b; line-height: 1.6; }
        :root { --color-primary: #2c3856; --color-accent: #ff9c00; }
        header { position: fixed; top: -3cm; left: 0cm; right: 0cm; height: 2.5cm; text-align: center; }
        header img { width: 180px; height: auto; }
        footer { position: fixed; bottom: -2.5cm; left: 0cm; right: 0cm; height: 2cm; font-size: 9px; color: #666666; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        h1 { color: var(--color-primary); font-size: 20px; text-align: center; margin-bottom: 25px; border-bottom: 2px solid var(--color-accent); padding-bottom: 10px; }
        h2 { color: var(--color-primary); font-size: 14px; margin-top: 25px; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: avoid; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f3f4f6; font-weight: bold; color: var(--color-primary); width: 25%; }
        .text-justify { text-align: justify; }
        .legal { font-size: 9px; text-align: justify; color: #666666; }
        .signatures { margin-top: 80px; width: 100%; }
        .signature-box { display: inline-block; width: 48%; text-align: center; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; }
        strong { color: #000; }
    </style>
</head>
<body>
    @php use Carbon\Carbon; Carbon::setLocale('es'); @endphp    
    <header>
        @if($logoBase64) <img src="{{ $logoBase64 }}" alt="Logo"> @endif
    </header>
    <footer>
        <div><strong>Estrategias y Soluciones Minmer Global</strong></div>
        <div>ID del Documento: RESP-CONSOLIDADA-{{ $member->id }}-{{ date('Ymd') }}</div>
    </footer>
    <main>
        <h1>Carta Responsiva Consolidada de Activos</h1>
        <p class="text-justify">
            En nuestras oficinas, a {{ Carbon::now()->isoFormat('D [de] MMMM [de] YYYY') }}, la empresa
            <strong>Estrategias y Soluciones Minmer Global</strong> (en adelante "LA EMPRESA"), hace constar que el colaborador(a)
            <strong>{{ $member->name }}</strong>, con puesto de <strong>{{ $member->position->name ?? 'No especificado' }}</strong> (en adelante "EL COLABORADOR"), tiene bajo su resguardo los equipos de cómputo que se detallan a continuación.
        </p>

        <h2>Detalle de Activos Asignados</h2>
        @foreach($assignments as $index => $assignment)
            <h3 style="color: var(--color-primary); font-size: 12px; margin-top: 20px;">Activo #{{ $index + 1 }}: {{ $assignment->asset->model->category->name }} {{ $assignment->asset->model->name }}</h3>
            <table>
                <tr><th>Etiqueta de Activo</th><td><strong>{{ $assignment->asset->asset_tag }}</strong></td></tr>
                <tr><th>Número de Serie</th><td>{{ $assignment->asset->serial_number }}</td></tr>
                <tr><th>Fecha de Asignación</th><td>{{ Carbon::parse($assignment->assignment_date)->isoFormat('L') }}</td></tr>
            </table>
        @endforeach

        <h2>Cláusulas y Condiciones de Uso</h2>
        <div class="legal">
            <p><strong>PRIMERA:</strong> "EL COLABORADOR" reconoce tener bajo su resguardo los equipos y/o software descritos, los cuales son propiedad exclusiva de "LA EMPRESA", y se destinarán única y exclusivamente para el desempeño de las funciones laborales.</p>
            <p><strong>SEGUNDA:</strong> "EL COLABORADOR" se compromete a custodiar y conservar los equipos en excelentes condiciones, y a seguir en todo momento las políticas de seguridad de la información de "LA EMPRESA".</p>
            <p><strong>TERCERA:</strong> En caso de daño por negligencia, mal uso, robo o extravío de cualquiera de los equipos, "EL COLABORADOR" asumirá la total responsabilidad y cubrirá los costos de reparación o reposición.</p>
            <p><strong>CUARTA:</strong> Al término de la relación laboral, o por solicitud expresa, "EL COLABORADOR" se compromete a devolver todos los equipos listados, completos y con todos sus accesorios.</p>
        </div>
        
        <p class="text-justify" style="margin-top: 20px;">
            Habiendo leído y entendido las cláusulas, y reconociendo tener bajo mi resguardo todos los activos listados, firmo de conformidad.
        </p>
        <div class="signatures">
            <div class="signature-box"><div class="signature-line"></div><p><strong>Recibe de Conformidad</strong><br>{{ $member->name }}</p></div>
            <div class="signature-box" style="float: right;"><div class="signature-line"></div><p><strong>Entrega</strong><br>Departamento de TI</p></div>
        </div>
    </main>
</body>
</html>