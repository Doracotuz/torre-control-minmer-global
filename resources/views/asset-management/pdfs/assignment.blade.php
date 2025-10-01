<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Responsiva de Activos de TI</title>
    <style>
        /* --- ESTILOS GENERALES Y PALETA DE COLORES --- */
        @page {
            margin: 3.5cm 1.5cm 3cm 1.5cm; /* Aumentado el margen superior para el logo más grande */
        }
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 11px; 
            color: #2b2b2b; /* --color-text-primary */
            line-height: 1.6;
        }
        :root {
            --color-primary: #2c3856;
            --color-accent: #ff9c00;
        }
        
        /* --- ENCABEZADO Y PIE DE PÁGINA --- */
        header {
            position: fixed;
            top: -3cm; /* Ajustado para subir el logo */
            left: 0cm;
            right: 0cm;
            height: 2.5cm;
            text-align: center; /* <-- CAMBIO: Centrado */
        }
        header img {
            width: 180px; /* <-- CAMBIO: Logo más grande */
            height: auto;
        }
        footer {
            position: fixed; 
            bottom: -2.5cm; 
            left: 0cm; 
            right: 0cm;
            height: 2cm;
            font-size: 9px;
            color: #666666; /* --color-text-secondary */
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
            width: 25%;
        }
        
        /* --- TEXTOS Y FIRMAS --- */
        .text-justify { text-align: justify; }
        .legal { font-size: 9px; text-align: justify; color: #666666; }
        .signatures { margin-top: 80px; width: 100%; }
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
            {{ $assignment->asset->site->address ?? 'Dirección no especificada' }} | 
            Tel: +52 33 3022 1806
        </div>
        <div>ID del Documento: RESP-{{ $assignment->asset->asset_tag }}-{{ $assignment->id }}</div>
    </footer>

    <main>
        <h1>Carta Responsiva de Activos de TI</h1>

        <p class="text-justify">
            En {{ $assignment->asset->site->name ?? 'nuestras oficinas' }}, a {{ \Carbon\Carbon::parse($assignment->assignment_date)->isoFormat('D [de] MMMM [de] YYYY') }}, la empresa
            <strong>Estrategias y Soluciones Minmer Global</strong> (en adelante "LA EMPRESA"), hace entrega del equipo de cómputo que se detalla a continuación al colaborador(a)
            <strong>{{ $assignment->member->name }}</strong>, quien se identifica con el puesto de
            <strong>{{ $assignment->member->position->name ?? 'No especificado' }}</strong> (en adelante "EL COLABORADOR").
        </p>

        <h2>Detalles del Activo Asignado</h2>
        <table>
            <tr>
                <th>Etiqueta de Activo</th>
                <td><strong>{{ $assignment->asset->asset_tag }}</strong></td>
            </tr>
            <tr>
                <th>Categoría</th>
                <td>{{ $assignment->asset->model->category->name }}</td>
            </tr>
            <tr>
                <th>Modelo</th>
                <td>{{ $assignment->asset->model->manufacturer->name }} {{ $assignment->asset->model->name }}</td>
            </tr>
            <tr>
                <th>Número de Serie</th>
                <td>{{ $assignment->asset->serial_number }}</td>
            </tr>
            <tr>
                <th>Ubicación Asignada</th>
                <td>
                    <strong>{{ $assignment->asset->site->name ?? 'N/A' }}</strong><br>
                    <span style="font-size: 9px; color: #666;">{{ $assignment->asset->site->address ?? '' }}</span>
                </td>
            </tr>
             @if($assignment->asset->model->category->name === 'Laptop' || $assignment->asset->model->category->name === 'Desktop' || $assignment->asset->model->category->name === 'Celular')
            <tr>
                <th>Especificaciones</th>
                <td>
                    @if($assignment->asset->cpu) <strong>CPU:</strong> {{ $assignment->asset->cpu }} <br> @endif
                    @if($assignment->asset->ram) <strong>RAM:</strong> {{ $assignment->asset->ram }} <br> @endif
                    @if($assignment->asset->storage) <strong>Almacenamiento:</strong> {{ $assignment->asset->storage }} <br> @endif
                    @if($assignment->asset->mac_address) <strong>MAC Address:</strong> {{ $assignment->asset->mac_address }} <br> @endif
                    @if($assignment->asset->phone_number) <strong>No. Telefónico:</strong> {{ $assignment->asset->phone_number }} ({{ $assignment->asset->phone_plan_type ?? 'N/A' }}) @endif
                </td>
            </tr>
            @endif
             <tr>
                <th>Fechas Clave</th>
                <td>
                    <strong>Fecha de Compra:</strong> {{ $assignment->asset->purchase_date ? Carbon::parse($assignment->asset->purchase_date)->isoFormat('L') : 'N/A' }} <br>
                    <strong>Fin de Garantía:</strong> {{ $assignment->asset->warranty_end_date ? Carbon::parse($assignment->asset->warranty_end_date)->isoFormat('L') : 'N/A' }}
                </td>
            </tr>
        </table>

        @if($assignment->asset->softwareAssignments->isNotEmpty())
            <h2>Software y Licencias Instaladas</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 70%;">Nombre del Software</th>
                        <th>Fecha de Instalación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignment->asset->softwareAssignments as $software)
                        <tr>
                            <td>{{ $software->license->name }}</td>
                            <td>{{ date('d/m/Y', strtotime($software->install_date)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <h2>Cláusulas y Condiciones de Uso</h2>
        <div class="legal">
            <p><strong>PRIMERA:</strong> "EL COLABORADOR" recibe de "LA EMPRESA" el equipo y/o software descrito, el cual es propiedad exclusiva de "LA EMPRESA", y se destinará única y exclusivamente para el desempeño de las funciones laborales para las cuales fue contratado.</p>
            <p><strong>SEGUNDA:</strong> "EL COLABORADOR" se compromete a custodiar y conservar el equipo en excelentes condiciones, salvo el deterioro normal derivado del uso cotidiano y adecuado. Se obliga a no instalar software no autorizado, no alterar la configuración de seguridad y a seguir en todo momento las políticas de seguridad de la información de "LA EMPRESA".</p>
            <p><strong>TERCERA:</strong> En caso de daño por negligencia, dolo, mal uso, robo o extravío, "EL COLABORADOR" asumirá la total responsabilidad y cubrirá los costos de reparación o de reposición del equipo a valor de mercado.</p>
            <p><strong>CUARTA:</strong> "EL COLABORADOR" se obliga a reportar de manera inmediata al Departamento de TI cualquier falla, daño o mal funcionamiento del equipo.</p>
            <p><strong>QUINTA:</strong> Al término de la relación laboral, o por solicitud expresa de "LA EMPRESA", "EL COLABORADOR" se compromete a devolver el equipo completo, con todos sus accesorios, en las mismas condiciones físicas y operativas en las que lo recibió.</p>
        </div>
        
        <p class="text-justify" style="margin-top: 20px;">
            Habiendo leído y entendido las cláusulas anteriores, ambas partes firman de conformidad.
        </p>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line"></div>
                <p><strong>Recibe de Conformidad</strong><br>{{ $assignment->member->name }}</p>
            </div>
            <div class="signature-box" style="float: right;">
                <div class="signature-line"></div>
                <p><strong>Entrega</strong><br>Departamento de TI</p>
            </div>
        </div>

        <div style="margin-top: 60px; border-top: 2px dashed #ccc; padding-top: 20px; page-break-inside: avoid;">
            <h2>Acta de Devolución de Activo</h2>
            <p class="text-justify">
                "EL COLABORADOR" hace entrega a "LA EMPRESA" del equipo descrito en la presente acta, dando por finalizada la responsiva sobre el mismo. El equipo se recibe para su revisión y validación de estado.
                <br><br>
                Fecha de Devolución: ______ / _______________ / ______
            </p>
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p><strong>Entrega de Conformidad</strong><br>{{ $assignment->member->name }}</p>
                </div>
                <div class="signature-box" style="float: right;">
                    <div class="signature-line"></div>
                    <p><strong>Recibe</strong><br>Departamento de TI</p>
                </div>
            </div>
        </div>        
    </main>
</body>
</html>