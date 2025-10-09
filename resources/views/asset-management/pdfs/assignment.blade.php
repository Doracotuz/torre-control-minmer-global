<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Responsiva de Activo</title>
    <style>
        @page {
            margin: 3cm 1.5cm 2.5cm 1.5cm;
        }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; line-height: 1.4; margin-top: 2cm; }
        :root { --color-primary: #2c3856; --color-accent: #ff9c00; --color-light-gray: #f7f8fa; }
        
        .header-line {
            position: fixed;
            top: -3cm;
            left: -1.5cm;
            right: -1.5cm;
            height: 10px;
            background-color: var(--color-primary);
        }

        .watermark {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1000; opacity: 0.05; width: 60%;
            background-image: url("{{ $logoBase64 }}");
            background-repeat: no-repeat; background-position: center; background-size: contain;
            height: 400px;
        }
        
        #header {
            position: fixed;
            top: -2.7cm;
            left: 0cm;
            right: 0cm;
            height: 2.5cm;
        }
        #footer { position: fixed; bottom: -2cm; left: 0; right: 0; height: 1.5cm; text-align: center; font-size: 8px; color: #888; border-top: 1px solid #ddd; padding-top: 5px; }

        .logo { width: 150px; }
        .info-table { width: 100%; border-spacing: 0; }
        .info-table td { vertical-align: bottom; padding: 0; }
        .doc-title { font-size: 22px; font-weight: bold; color: var(--color-primary); margin: 0; }
        .doc-subtitle { font-size: 11px; color: #555; margin: 0; }
        .details-box { border: 1px solid #eee; background-color: var(--color-light-gray); padding: 8px; font-size: 9px; }
        
        .recipient-box { background-color: var(--color-light-gray); border-left: 4px solid var(--color-accent); padding: 12px; }
        h2 { font-size: 14px; color: var(--color-primary); border-bottom: 2px solid var(--color-light-gray); padding-bottom: 5px; margin-top: 25px; }
        
        .details-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 9px; }
        .details-table th, .details-table td { border: 1px solid #e0e0e0; padding: 8px; text-align: left; vertical-align: top; }
        .details-table th { background-color: var(--color-light-gray); font-weight: bold; color: var(--color-primary); width: 25%; }
        
        .legal { font-size: 8px; text-align: justify; color: #555; column-count: 2; column-gap: 20px; margin-top: 15px;}
        .signatures { margin-top: 50px; width: 100%; text-align: center; }
        .signature-box { display: inline-block; width: 32%; text-align: center; font-size: 9px; vertical-align: top; }
        .signature-line { margin-top: 40px; border-top: 1px solid #333; padding-top: 5px; }
        
        .devolution-section { margin-top: 40px; border-top: 2px dashed #ccc; padding-top: 20px; page-break-inside: avoid; }
        .legal-signatures-block { page-break-inside: avoid; }
    </style>
</head>
<body>
    @php use Carbon\Carbon; Carbon::setLocale('es'); @endphp
    <div class="header-line"></div>
    <div class="watermark"></div>

    <header id="header">
        <table class="info-table">
            <tr>
                <td style="padding-bottom: 15px;">
                    @if($logoBase64) <img src="{{ $logoBase64 }}" alt="Logo" class="logo"> @endif
                </td>
            </tr>
            <tr>
                <td>
                    <h1 class="doc-title">Carta Responsiva de Activo</h1>
                    <p class="doc-subtitle">Asignación de Activo de Tecnologías de la Información</p>
                </td>
                <td style="width: 35%; text-align: right;">
                    <div class="details-box">
                        <strong>ID de Documento:</strong> RESP-{{ $assignment->asset->asset_tag }}<br>
                        <strong>Fecha de Emisión:</strong> {{ \Carbon\Carbon::now()->isoFormat('D MMMM, YYYY') }}
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <footer id="footer">
        Estrategias y Soluciones Minmer Global | Este documento es confidencial y para uso exclusivo interno.
    </footer>

    <main>
        <div class="recipient-box">
            <strong>COLABORADOR:</strong> {{ $assignment->member->name }} <br>
            <strong>PUESTO:</strong> {{ $assignment->member->position->name ?? 'No especificado' }}
        </div>

        <p style="text-align: justify; margin-top: 20px;">
             En {{ $assignment->asset->site->name ?? 'nuestras oficinas' }}, a {{ \Carbon\Carbon::parse($assignment->assignment_date)->isoFormat('D [de] MMMM [de] YYYY') }}, la empresa
            <strong>Estrategias y Soluciones Minmer Global</strong> (en adelante "LA EMPRESA"), hace entrega del equipo de cómputo que se detalla a continuación al colaborador(a)
            <strong>{{ $assignment->member->name }}</strong>, quien se identifica con el puesto de
            <strong>{{ $assignment->member->position->name ?? 'No especificado' }}</strong> (en adelante "EL COLABORADOR").
        </p>

        <h2>Detalles del Activo Asignado</h2>
        <table class="details-table">
            <tr><th>Etiqueta de Activo</th><td><strong>{{ $assignment->asset->asset_tag }}</strong></td></tr>
            <tr><th>Categoría</th><td>{{ $assignment->asset->model->category->name }}</td></tr>
            <tr><th>Modelo</th><td>{{ $assignment->asset->model->manufacturer->name }} {{ $assignment->asset->model->name }}</td></tr>
            <tr><th>Número de Serie</th><td>{{ $assignment->asset->serial_number }}</td></tr>
            
            {{-- --- CAMBIO: Se reinserta la Ubicación Asignada --- --}}
            <tr>
                <th>Ubicación Asignada</th>
                <td>
                    <strong>{{ $assignment->asset->site->name ?? 'N/A' }}</strong><br>
                    <span style="font-size: 8px; color: #666;">{{ $assignment->asset->site->address ?? '' }}</span>
                </td>
            </tr>

            @if($assignment->asset->cpu || $assignment->asset->ram || $assignment->asset->storage || $assignment->asset->mac_address || $assignment->asset->phone_number || $assignment->asset->phone_plan_type)
            <tr>
                <th>Especificaciones</th>
                <td>
                    @if($assignment->asset->cpu) <strong>CPU:</strong> {{ $assignment->asset->cpu }} <br> @endif
                    @if($assignment->asset->ram) <strong>RAM:</strong> {{ $assignment->asset->ram }} <br> @endif
                    @if($assignment->asset->storage) <strong>Almacenamiento:</strong> {{ $assignment->asset->storage }} <br> @endif
                    @if($assignment->asset->mac_address) <strong>MAC Address:</strong> {{ $assignment->asset->mac_address }} <br> @endif
                    @if($assignment->asset->phone_number) <strong>No. Telefónico:</strong> {{ $assignment->asset->phone_number }} @endif
                    @if($assignment->asset->phone_plan_type) ({{ $assignment->asset->phone_plan_type }}) @endif
                </td>
            </tr>
            @endif

            {{-- --- CAMBIO: Se reinsertan las Fechas Clave --- --}}
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
            <table class="details-table" style="font-size: 9px;">
                <thead style="background-color: var(--color-light-gray);">
                    <tr>
                        <th style="width: 70%; padding: 5px; color: var(--color-primary)">Nombre del Software</th>
                        <th style="padding: 5px; color: var(--color-primary)">Fecha de Instalación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignment->asset->softwareAssignments as $software)
                        <tr>
                            <td style="padding: 5px;">{{ $software->license->name }}</td>
                            <td style="padding: 5px;">{{ Carbon::parse($software->install_date)->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="legal-signatures-block">
            <h2>Cláusulas y Condiciones de Uso</h2>
            <div class="legal">
                <p><strong>PRIMERA:</strong> "EL COLABORADOR" recibe de "LA EMPRESA" el equipo y/o software descrito, el cual es propiedad exclusiva de "LA EMPRESA", y se destinará única y exclusivamente para el desempeño de las funciones laborales para las cuales fue contratado.</p>
                <p><strong>SEGUNDA:</strong> "EL COLABORADOR" se compromete a custodiar y conservar el equipo en excelentes condiciones, salvo el deterioro normal derivado del uso cotidiano y adecuado. Se obliga a no instalar software no autorizado, no alterar la configuración de seguridad y a seguir en todo momento las políticas de seguridad de la información de "LA EMPRESA".</p>
                <p><strong>TERCERA:</strong> En caso de daño por negligencia, dolo, mal uso, robo o extravío, "EL COLABORADOR" asumirá la total responsabilidad y cubrirá los costos de reparación o de reposición del equipo a valor de mercado.</p>
                <p><strong>CUARTA:</strong> "EL COLABORADOR" se obliga a reportar de manera inmediata al Departamento de TI cualquier falla, daño o mal funcionamiento del equipo.</p>
                <p><strong>QUINTA:</strong> Al término de la relación laboral, o por solicitud expresa de "LA EMPRESA", "EL COLABORADOR" se compromete a devolver el equipo completo, con todos sus accesorios, en las mismas condiciones físicas y operativas en las que lo recibió.</p>
            </div>
            
            <p style="text-align: justify; font-size: 10px; margin-top: 20px;">
                Habiendo leído y entendido las cláusulas anteriores, ambas partes firman de conformidad.
            </p>

            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line">
                        <strong>Recibe de Conformidad</strong><br>
                        {{ $assignment->member->name }}
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <strong>Entrega</strong><br>
                        Departamento de TI
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <strong>Vo. Bo. (Visto Bueno)</strong><br>
                        Recursos Humanos
                    </div>
                </div>
            </div>
        </div>

        <div class="devolution-section">
            <h2>Acta de Devolución de Activo</h2>
            <p style="text-align: justify;">
                "EL COLABORADOR" hace entrega a "LA EMPRESA" del equipo descrito en la presente acta, dando por finalizada la responsiva sobre el mismo. El equipo se recibe para su revisión y validación de estado.
                <br><br>
                Fecha de Devolución: ______ / _______________ / ______
            </p>
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line">
                        <strong>Entrega de Conformidad</strong><br>
                        {{ $assignment->member->name }}
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <strong>Recibe</strong><br>
                        Departamento de TI
                    </div>
                </div>
                 <div class="signature-box">
                    <div class="signature-line">
                        <strong>Vo. Bo.</strong><br>
                        Recursos Humanos
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>