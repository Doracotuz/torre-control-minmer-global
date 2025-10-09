@php
    $svgIcons = [
        'Laptop' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="2" y1="20" x2="22" y2="20"></line></svg>'),
        'Celular' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>'),
        'Desktop' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>'),
        'Monitor' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>'),
        'default' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>'),
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Responsiva Consolidada</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 2.5cm 1.5cm;
        }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        :root { --color-primary: #2c3856; --color-accent: #ff9c00; --color-light-gray: #f7f8fa; }

        .watermark {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1000;
            opacity: 0.05;
            width: 60%;
            background-image: url("{{ $logoBase64 }}");
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            height: 400px;
        }
        
        #header {
            position: fixed;
            top: -1.5cm;
            left: -1.5cm;
            right: -1.5cm;
            padding: 15px 1.5cm 15px 1.5cm;
            background-color: white;
            border-top: 10px solid var(--color-primary);
        }

        #footer { position: fixed; bottom: -2cm; left: 0; right: 0; height: 1.5cm; text-align: center; font-size: 8px; color: #888; border-top: 1px solid #ddd; padding-top: 5px; }

        body {
            margin-top: 4cm;
        }
        
        .logo { width: 150px; }
        .info-table { width: 100%; border-spacing: 0; }
        .info-table td { vertical-align: bottom; padding: 0; }
        .doc-title { font-size: 22px; font-weight: bold; color: var(--color-primary); margin: 0; }
        .doc-subtitle { font-size: 11px; color: #555; margin: 0; }
        .details-box { border: 1px solid #eee; background-color: var(--color-light-gray); padding: 8px; font-size: 9px; }
        
        .recipient-box { background-color: var(--color-light-gray); border-left: 4px solid var(--color-accent); padding: 12px; }
        h2 { font-size: 14px; color: var(--color-primary); border-bottom: 2px solid var(--color-light-gray); padding-bottom: 5px; margin-top: 25px; }
        
        .assets-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 9px; }
        .assets-table th, .assets-table td { border: 1px solid #e0e0e0; padding: 8px; text-align: left; vertical-align: top; }
        .assets-table thead th { background-color: var(--color-primary); color: white; font-weight: bold; text-transform: uppercase; }
        .assets-table .striped { background-color: var(--color-light-gray); }
        .asset-icon { width: 16px; height: 16px; vertical-align: -3px; margin-right: 6px; }
        .assets-table .details p { margin: 0 0 4px 0; }
        .assets-table .details strong { color: #555; }
        .assets-table .details ul { margin: 4px 0 0 0; padding-left: 15px; }
        .legal { font-size: 8px; text-align: justify; color: #555; column-count: 2; column-gap: 20px; margin-top: 15px;}
        .signatures { margin-top: 50px; width: 100%; text-align: center; }
        .signature-box { display: inline-block; width: 32%; text-align: center; font-size: 9px; vertical-align: top; }
        .signature-line { margin-top: 40px; border-top: 1px solid #333; padding-top: 5px; }
        .legal-signatures-block { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="watermark"></div>

    <header id="header">
        <table class="info-table">
            <tr>
                <td>
                    @if($logoBase64) <img src="{{ $logoBase64 }}" alt="Logo" class="logo"> @endif
                </td>
            </tr>
            <tr>
                <td style="padding-top: 15px;">
                    <h1 class="doc-title">Carta Responsiva Consolidada</h1>
                    <p class="doc-subtitle">Asignación de Activos de Tecnologías de la Información</p>
                </td>
                <td style="width: 35%; text-align: right;">
                    <div class="details-box">
                        <strong>ID de Documento:</strong> {{ $documentId }} <br>
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
            <strong>COLABORADOR:</strong> {{ $member->name }} <br>
            <strong>PUESTO:</strong> {{ $member->position->name ?? 'No especificado' }}
        </div>

        <p style="text-align: justify; margin-top: 20px;">
            Por medio de la presente, <strong>Estrategias y Soluciones Minmer Global</strong> ("LA EMPRESA"), hace constar que el colaborador mencionado ("EL COLABORADOR"), recibe y acepta la custodia de los activos de TI que se detallan a continuación, comprometiéndose a cumplir con las políticas de uso y seguridad establecidas.
        </p>

        <h2>Activos Bajo Resguardo</h2>
        <table class="assets-table">
            <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 25%;">Activo</th>
                <th>Detalles y Especificaciones</th>
                <th style="width: 15%;">Asignación</th>
            </tr>
            </thead>
            <tbody>
            @foreach($assignments as $assignment)
                @php
                    $asset = $assignment->asset;
                    $categoryName = $asset->model->category->name;
                    $iconSvg = collect($svgIcons)->first(fn($svg, $key) => str_contains($categoryName, $key)) ?? $svgIcons['default'];
                @endphp
                <tr @if($loop->odd) class="striped" @endif>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>
                        <img src="{{ $iconSvg }}" class="asset-icon" alt=""><strong>{{ $categoryName }}</strong><br>
                        <span style="font-size: 8px; color: #666;">{{ $asset->model->manufacturer->name }} {{ $asset->model->name }}</span>
                    </td>
                    <td class="details">
                        <p><strong>Tag:</strong> {{ $asset->asset_tag }} / <strong>Serie:</strong> {{ $asset->serial_number }}</p>
                        @if($asset->cpu || $asset->ram || $asset->storage || $asset->mac_address || $asset->phone_number)
                            <p style="border-top: 1px dashed #ddd; padding-top: 4px; margin-top: 4px;">
                                @if($asset->cpu) <strong>CPU:</strong> {{ $asset->cpu }} | @endif
                                @if($asset->ram) <strong>RAM:</strong> {{ $asset->ram }} | @endif
                                @if($asset->storage) <strong>Almacenamiento:</strong> {{ $asset->storage }} @endif
                                @if($asset->mac_address) <br><strong>MAC:</strong> {{ $asset->mac_address }} @endif
                                @if($asset->phone_number) <br><strong>Tel:</strong> {{ $asset->phone_number }} ({{ $asset->phone_plan_type ?? 'N/A' }}) @endif
                            </p>
                        @endif
                        @if($asset->softwareAssignments->isNotEmpty())
                            <p style="border-top: 1px dashed #ddd; padding-top: 4px; margin-top: 4px;"><strong>Software:</strong></p>
                            <ul>
                                @foreach($asset->softwareAssignments as $software)
                                    <li>{{ $software->license->name }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($assignment->assignment_date)->isoFormat('L') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="legal-signatures-block">
            <h2>Cláusulas y Condiciones</h2>
            <div class="legal">
                <strong>1. Propiedad y Uso.</strong> "EL COLABORADOR" reconoce que los activos listados son propiedad exclusiva de "LA EMPRESA" y se destinan únicamente para el desempeño de sus funciones laborales. Queda prohibido su uso para fines personales, ilícitos o no autorizados.
                <br><br>
                <strong>2. Deber de Cuidado.</strong> "EL COLABORADOR" se compromete a custodiar y conservar el equipo en óptimas condiciones, reportando cualquier anomalía, daño o falla de manera inmediata al departamento de TI. Esto incluye protegerlo de extravío o derramamiento de líquidos.
                <br><br>
                <strong>3. Software y Seguridad.</strong> Queda estrictamente prohibida la instalación de software no licenciado o no autorizado por "LA EMPRESA". "EL COLABORADOR" debe cumplir con todas las políticas de seguridad de la información, incluyendo el uso de contraseñas seguras y la no divulgación de información confidencial.
                <br><br>
                <strong>4. Responsabilidad Financiera.</strong> En caso de daño por negligencia, dolo, mal uso, o por la pérdida del equipo, "EL COLABORADOR" será responsable y deberá cubrir los costos de reparación o reposición del activo a su valor de mercado actual.
                <br><br>
                <strong>5. Devolución de Activos.</strong> Al término de la relación laboral, o por solicitud expresa de "LA EMPRESA", "EL COLABORADOR" está obligado a devolver la totalidad de los equipos y accesorios asignados en un plazo no mayor a 48 horas, en las mismas condiciones en las que los recibió, salvo el desgaste normal por uso.
                <br><br>
                <strong>6. En caso de robo.</strong> En caso de robo, "EL COLABORADOR" está obligado a presentar la denuncia formal a las autoridades correspondientes, notificar y presentar la documentación entregada por la autoridad a "LA EMPRESA" para la gestión de un nuevo equipo.
            </div>
            <br>
            <br>
            <br>
            <br>
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line">
                        <strong>Recibe de Conformidad</strong><br>
                        {{ $member->name }}
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
                        <strong>Vo. Bo.</strong><br>
                        Recursos Humanos
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>