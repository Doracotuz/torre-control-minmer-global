<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Responsiva de Activo</title>
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
        #header .logo { width: 160px; }
        #header .doc-info { text-align: right; vertical-align: bottom; }
        .doc-title { font-size: 20px; font-weight: bold; color: var(--color-primary); margin: 0; letter-spacing: 1px; text-transform: uppercase; }
        .doc-subtitle { font-size: 10px; margin: 2px 0 8px O; color: var(--color-secondary); }
        
        h2 { font-size: 11px; color: var(--color-primary); font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; border-bottom: 1px solid var(--color-border); padding-bottom: 5px; margin: 25px 0 10px 0; }
        
        .details-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .details-table th, .details-table td { padding: 6px 0; border-bottom: 1px solid var(--color-border); text-align: left; }
        .details-table th { color: var(--color-primary); width: 25%; font-weight: bold; }
        .details-table td { color: var(--color-secondary); }
        .details-table strong { color: var(--color-primary); }
        
        .legal-summary { font-size: 8px; text-align: justify; column-count: 2; column-gap: 20px; color: var(--color-secondary); }
        .legal-summary ul { list-style-position: inside; padding-left: 0; margin: 0; }
        .legal-summary li::marker { content: '• '; color: var(--color-accent); }
        .signatures-area { margin-top: 15px; page-break-inside: avoid; }
        .signatures-area h3 { font-size: 10px; font-weight: bold; color: var(--color-primary); margin: 15px 0 10px 0; border-top: 1px solid var(--color-border); padding-top: 10px; }
        .signature-table { width: 100%; text-align: center; }
        .signature-table td { width: 33.33%; padding: 0 15px; }
        .signature-line { margin-top: 40px; border-top: 1px solid var(--color-border); padding: 5px 0; font-size: 8px; color: var(--color-secondary); }
        .signature-line strong { color: var(--color-primary); }
    </style>
</head>
<body>
    @php use Carbon\Carbon; Carbon::setLocale('es'); @endphp
    <div class="watermark"></div>

    <footer id="footer">
        <div class="footer-line"></div>
        <table>
            <tr>
                <td>Estrategias y Soluciones Minmer Global | Documento Confidencial</td>
                <td style-="text-align: right;" class="page-number"></td>
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
                    @if(isset($logoBase64)) <img src="{{ $logoBase64 }}" alt="Logo" class="logo"> @endif
                </td>
                <td style="width: 50%;" class="doc-info">
                    <h1 class="doc-title">Responsiva de Activo</h1>
                    <p class="doc-subtitle">ID de Documento: RESP-{{ $assignment->asset->asset_tag }} | {{ \Carbon\Carbon::now()->isoFormat('D MMMM, YYYY') }}</p>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <p style="text-align: justify; font-size: 10px;">
            En {{ $assignment->asset->site->name ?? 'nuestras oficinas' }}, a {{ \Carbon\Carbon::parse($assignment->assignment_date)->isoFormat('D [de] MMMM [de] YYYY') }}, <strong>Estrategias y Soluciones Minmer PC33</strong> ("LA EMPRESA"), formaliza la entrega del activo detallado a continuación al colaborador(a) <strong>{{ $assignment->member->name }}</strong> ("EL COLABORADOR"), con puesto de <strong>{{ $assignment->member->position->name ?? 'No especificado' }}</strong>, quien se compromete a cumplir las políticas de uso y cuidado estipuladas.
        </p>

        <h2>Detalles del Activo Asignado</h2>
        <table class="details-table">
            <tr>
                <th>Categoría</th>
                <td><strong>{{ $assignment->asset->model->category->name }}</strong></td>
            </tr>
            <tr>
                <th>Activo</th>
                <td>{{ $assignment->asset->model->manufacturer->name }} {{ $assignment->asset->model->name }}</td>
            </tr>
            <tr>
                <th>Etiqueta / Serie</th>
                <td><strong>Tag:</strong> {{ $assignment->asset->asset_tag }} | <strong>Serie:</strong> {{ $assignment->asset->serial_number ?? 'N/A' }}</td>
            </tr>
             @if($assignment->asset->cpu || $assignment->asset->ram || $assignment->asset->storage)
            <tr>
                <th>Especificaciones</th>
                <td>{{ $assignment->asset->cpu }} / {{ $assignment->asset->ram }} / {{ $assignment->asset->storage }}</td>
            </tr>
            @endif
            @if($assignment->asset->mac_address)
            <tr>
                <th>MAC Address</th>
                <td>{{ $assignment->asset->mac_address }}</td>
            </tr>
            @endif
             @if($assignment->asset->phone_number)
            <tr>
                <th>No. Telefónico</th>
                <td>{{ $assignment->asset->phone_number }} ({{ $assignment->asset->phone_plan_type ?? 'N/A' }})</td>
            </tr>
            @endif
             @if($assignment->asset->notes)
            <tr>
                <th>Notas Adicionales</th>
                <td>{{ $assignment->asset->notes }}</td>
            </tr>
            @endif
        </table>

        @if($assignment->asset->softwareAssignments->isNotEmpty())
            <table class="details-table" style="margin-top: 10px;">
                <tr>
                    <th style="width: 25%;">Software Instalado</th>
                    <td>
                        @foreach($assignment->asset->softwareAssignments as $software)
                            {{ $software->license->name }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                </tr>
            </table>
        @endif

        <div class="signatures-area">
            <h2>Términos, Condiciones y Firmas</h2>
            <div class="legal-summary">
                <ul>
                    <li><strong>Propiedad y Uso:</strong> Los activos son propiedad de la empresa y para uso exclusivamente laboral.</li>
                    <li><strong>Deber de Cuidado:</strong> Es mi responsabilidad custodiar el equipo y reportar fallas a TI.</li>
                    <li><strong>Responsabilidad Financiera:</strong> Asumiré el costo por negligencia, mal uso o pérdida.</li>
                    <li><strong>En caso de robo:</strong> El colaborador está obligado a presentar la denuncia formal a las autoridades correspondientes, notificar y presentar la documentación entregada por la autoridad a la empresa para la gestión de un nuevo equipo.</li>
                    <li><strong>Devolución:</strong> Al finalizar mi relación laboral, devolveré todos los activos en un plazo de 48 horas.</li>
                </ul>
            </div>

            <h3>Conformidad de Entrega</h3>
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-line"><strong>{{ $assignment->member->name }}</strong><br>Recibe de Conformidad</div>
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
                        <div class="signature-line"><strong>{{ $assignment->member->name }}</strong><br>Entrega</div>
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