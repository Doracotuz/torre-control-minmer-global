@php
    $svgIcons = [
        'Laptop' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="2" y1="20" x2="22" y2="20"></line></svg>'),
        'Celular' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>'),
        'Desktop' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>'),
        'Monitor' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>'),
        'Pantalla' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>'),
        'iPad' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>'),
        'Scanner' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 18H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h18a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2"/><path d="M5 14h14"/></svg>'),
        'Hub' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 11h-3a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h3"/><path d="M2 9h12a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2a2 2 0 0 1 2-2Z"/><path d="M7 12h2"/></svg>'),
        'default' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2c3856" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>'),
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Responsiva Consolidada de Activos</title>
    <style>
        @page { margin: 1.8cm; }
        :root {
            --color-primary: #2c3856;
            --color-accent: #ff9c00;
            --color-secondary: #6c757d;
            --color-background: #f8f9fa;
            --color-border: #e9eef5;
        }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: var(--color-secondary); line-height: 1.4; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: -1000; opacity: 0.05; width: 50%; background-image: url("{{ $logoBase64 ?? '' }}"); background-repeat: no-repeat; background-position: center; background-size: contain; height: 400px; }
        #footer { position: fixed; bottom: -1.8cm; left: -1.8cm; right: -1.8cm; height: 1.2cm; padding: 5px 1.8cm; background-color: white; z-index: 100; }
        #footer .footer-line { height: 2px; background-color: var(--color-primary); }
        #footer table { width: 100%; font-size: 8px; color: var(--color-secondary); margin-top: 5px; }
        
        #header { border-bottom: 2px solid var(--color-primary); padding-bottom: 12px; margin-bottom: 20px; }
        #header table { width: 100%; }
        #header .logo { width: 130px; }
        #header .doc-info { text-align: right; vertical-align: bottom; }
        .doc-title { font-size: 20px; font-weight: bold; color: var(--color-primary); margin: 0; letter-spacing: 1px; text-transform: uppercase; }
        .doc-subtitle { font-size: 10px; margin: 2px 0 8px 0; color: var(--color-secondary); }
        .recipient strong { color: var(--color-accent); font-weight: bold; }
        
        h2 { font-size: 11px; color: var(--color-primary); font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 1px solid var(--color-border); padding-bottom: 5px; margin: 25px 0 10px 0; }
        
        .assets-table { width: 100%; border-collapse: collapse; }
        .assets-table th { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; color: var(--color-secondary); border-bottom: 1px solid var(--color-primary); padding: 8px; }
        .assets-table td { padding: 8px; border-bottom: 1px solid var(--color-border); vertical-align: middle; }
        .assets-table tbody tr:nth-child(even) { background-color: var(--color-background); }
        .asset-icon { width: 16px; height: 16px; vertical-align: -3px; margin-right: 8px; opacity: 0.9; }
        .asset-main-line { font-weight: bold; color: var(--color-primary); }
        .asset-sub-line { font-size: 8px; }
        .details-compact { font-size: 8px; line-height: 1.5; color: var(--color-secondary); }
        .details-compact strong { color: var(--color-primary); }
        
        .legal-summary { font-size: 8px; text-align: justify; column-count: 2; column-gap: 20px; color: var(--color-secondary); }
        .legal-summary ul { list-style-position: inside; padding-left: 0; margin: 0; }
        .legal-summary li::marker { content: '• '; color: var(--color-accent); }
        .signatures-area { margin-top: 15px; }
        .signatures-area h3 { font-size: 10px; font-weight: bold; color: var(--color-primary); margin: 15px 0 10px 0; border-top: 1px solid var(--color-border); padding-top: 10px; }
        .signature-table { width: 100%; text-align: center; }
        .signature-table td { width: 33.33%; padding: 0 15px; }
        .signature-line { margin-top: 40px; border-top: 1px solid var(--color-border); padding: 5px 0; font-size: 8px; color: var(--color-secondary); }
        .signature-line strong { color: var(--color-primary); }
    </style>
</head>
<body>
    <div class="watermark"></div>

    <footer id="footer">
        <div class="footer-line"></div>
        <table>
            <tr>
                <td>Estrategias y Soluciones Minmer Global | Documento Confidencial</td>
                <td style="text-align: right;" class="page-number"></td>
            </tr>
        </table>
        <script type="text/php">
            if (isset($pdf)) {
                $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
                $size = 8;
                $font = $fontMetrics->getFont("DejaVu Sans");
                $width = $fontMetrics->get_text_width($text, $font, $size);
                $x = $pdf->get_width() - $width - 51;
                $y = $pdf->get_height() - 34;
                $pdf->page_text($x, $y, $text, $font, $size);
            }
        </script>
    </footer>

    <header id="header">
        <table>
            <tr>
                <td style="width: 50%;">
                    {{-- CAMBIO: El logo se ha restaurado --}}
                    @if(isset($logoBase64)) <img src="{{ $logoBase64 }}" alt="Logo" class="logo"> @endif
                </td>
                <td style="width: 50%;" class="doc-info">
                    <h1 class="doc-title">Responsiva de Activos</h1>
                    <p class="doc-subtitle">ID de Documento: {{ $documentId ?? '' }} | {{ \Carbon\Carbon::now()->isoFormat('D MMMM, YYYY') }}</p>
                    <p class="recipient">Para: <strong>{{ $member->name ?? '' }}</strong> ({{ $member->position->name ?? 'No especificado' }})</p>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <p style="font-size: 10px; text-align: justify;">
            Por medio del presente, se formaliza la entrega de los activos de Tecnologías de la Información (TI) propiedad de <strong>Estrategias y Soluciones Minmer PC33</strong>, los cuales se detallan a continuación. El colaborador receptor se compromete a cumplir con las políticas de uso, cuidado y devolución estipuladas.
        </p>
        
        <h2>Listado de Activos Asignados</h2>
        <table class="assets-table">
            <thead>
                <tr>
                    <th>Activo</th>
                    <th>Detalles y Especificaciones</th>
                    <th style="width: 15%; text-align: center;">Fecha Asignación</th>
                </tr>
            </thead>
            <tbody>
            @foreach($assignments as $assignment)
                @php
                    $asset = $assignment->asset;
                    $categoryName = $asset->model->category->name;
                    $iconSvg = collect($svgIcons)->first(fn($svg, $key) => str_contains($categoryName, $key)) ?? $svgIcons['default'];
                @endphp
                <tr>
                    <td>
                        <div class="asset-main-line"><img src="{{ $iconSvg }}" class="asset-icon" alt="">{{ $categoryName }}</div>
                        <div class="asset-sub-line">{{ $asset->model->manufacturer->name }} {{ $asset->model->name }}</div>
                    </td>
                    <td class="details-compact">
                        <strong>Tag:</strong> {{ $asset->asset_tag }} | <strong>Serie:</strong> {{ $asset->serial_number ?? 'N/A' }}
                        @if($asset->cpu || $asset->ram || $asset->storage)
                            <br><strong>Specs:</strong> {{ $asset->cpu }} / {{ $asset->ram }} / {{ $asset->storage }}
                        @endif
                        @if($asset->mac_address)
                            <br><strong>MAC:</strong> {{ $asset->mac_address }}
                        @endif
                        @if($asset->phone_number)
                            <br><strong>Tel:</strong> {{ $asset->phone_number }} ({{ $asset->phone_plan_type ?? 'N/A' }})
                        @endif
                        @if($asset->notes)
                            <br><strong>Notas:</strong> {{ $asset->notes }}
                        @endif
                    </td>
                    <td style="text-align: center; font-size: 9px; color: var(--color-primary);">{{ \Carbon\Carbon::parse($assignment->assignment_date)->isoFormat('L') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="signatures-area">
            <h2>Términos, Condiciones y Firmas</h2>
            <div class="legal-summary">
                <ul>
                    <li><strong>Propiedad y Uso:</strong> Los activos son propiedad de la empresa y para uso exclusivamente laboral.</li>
                    <li><strong>Deber de Cuidado:</strong> Es mi responsabilidad custodiar el equipo y reportar fallas a TI.</li>
                    <li><strong>Responsabilidad Financiera:</strong> Asumiré el costo por negligencia, mal uso o pérdida.</li>
                    <li><strong>En caso de robo:</strong> EL COLABORADOR está obligado a presentar la denuncia formal a las autoridades correspondientes, notificar y presentar la documentación entregada por la autoridad a LA EMPRESA para la gestión de un nuevo equipo.</li>
                    <li><strong>Devolución:</strong> Al finalizar mi relación laboral, devolveré todos los activos en un plazo de 48 horas.</li>
                </ul>
            </div>

            <h3>Conformidad de Entrega</h3>
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-line"><strong>{{ $member->name ?? '' }}</strong><br>Recibe de Conformidad</div>
                    </td>
                    <td>
                        <div class="signature-line"><strong>Departamento de TI</strong><br>Entrega</div>
                    </td>
                    <td>
                        <div class="signature-line"><strong>Recursos Humanos</strong><br>Visto Bueno</div>
                    </td>
                </tr>
            </table>

            <h3>Acuse de Devolución</h3>
            <p style="font-size: 8px; text-align: center; color: var(--color-secondary); margin-bottom: -5px;">Aplica al término de la relación laboral. Fecha: ______ / _______________ / ______</p>
            <table class="signature-table">
                 <tr>
                    <td>
                        <div class="signature-line"><strong>{{ $member->name ?? '' }}</strong><br>Entrega</div>
                    </td>
                    <td>
                        <div class="signature-line"><strong>Departamento de TI</strong><br>Recibe</div>
                    </td>
                    <td>
                        <div class="signature-line"><strong>Recursos Humanos</strong><br>Visto Bueno</div>
                    </td>
                </tr>
            </table>
        </div>
    </main>
</body>
</html>