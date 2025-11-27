<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documento de Mantenimiento</title>
    <style>
        @page { margin: 3.5cm 1.5cm 3cm 1.5cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #2b2b2b; line-height: 1.6; }
        :root { --color-primary: #2c3856; --color-accent: #ff9c00; --color-danger: #dc2626; }
        
        header { position: fixed; top: -3cm; left: 0cm; right: 0cm; height: 2.5cm; text-align: center; }
        header img { width: 180px; height: auto; }
        
        footer { position: fixed; bottom: -2.5cm; left: 0cm; right: 0cm; height: 2cm; font-size: 9px; color: #666666; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        
        h1 { color: var(--color-primary); font-size: 20px; text-align: center; margin-bottom: 25px; border-bottom: 2px solid var(--color-accent); padding-bottom: 10px; }
        h2 { color: var(--color-primary); font-size: 14px; margin-top: 25px; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #f3f4f6; font-weight: bold; color: var(--color-primary); width: 30%; }
        
        .text-justify { text-align: justify; }
        .notes-section { margin-top: 20px; padding: 15px; background-color: #f9fafb; border-left: 3px solid var(--color-accent); }
        
        .status-danger { color: var(--color-danger); font-weight: bold; border-left-color: var(--color-danger); }
        
        .signatures { margin-top: 60px; width: 100%; page-break-inside: avoid; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; }
        strong { color: #000; }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;
        Carbon::setLocale('es');

        $isCompleted = !is_null($maintenance->end_date);
        $isDamaged = $maintenance->asset->status === 'De Baja';
        
        if (!$isCompleted) {
            $docTitle = "Orden de Servicio y Diagnóstico";
            $statusText = "EN PROCESO / COTIZACIÓN";
            $statusColor = "#d97706"; // Ámbar
            $introText = "El presente documento detalla el diagnóstico inicial y los recursos estimados para el servicio de mantenimiento.";
            $costLabel = "Costo Estimado / Presupuesto";
            $partsLabel = "Refacciones Requeridas / A Cotizar";
            $actionLabel = "Acciones Propuestas";
            
        } elseif ($isDamaged) {
            $docTitle = "Dictamen Técnico de Baja";
            $statusText = "IRREPARABLE / DAÑADO";
            $statusColor = "#dc2626"; // Rojo
            $introText = "Este documento certifica que el activo ha sido sometido a revisión técnica y se dictamina como <strong>IRREPARABLE</strong>. Se detallan los recursos consumidos en el intento de reparación o diagnóstico.";
            $costLabel = "Costo Incurrido (Diagnóstico/Intento)";
            $partsLabel = "Insumos y Refacciones Consumidas en Diagnóstico";
            $actionLabel = "Acciones Realizadas y Resultados";

        } else {
            $docTitle = "Certificado de Mantenimiento";
            $statusText = "REPARADO / FUNCIONAL";
            $statusColor = "#059669"; // Verde
            $introText = "El presente documento certifica que se ha realizado el servicio de mantenimiento y el equipo ha sido verificado para su <strong>correcto funcionamiento</strong>.";
            $costLabel = "Costo Total del Servicio";
            $partsLabel = "Refacciones e Insumos Instalados";
            $actionLabel = "Acciones Realizadas";
        }
    @endphp

    <header>
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo">
        @endif
    </header>

    <footer>
        <div>
            <strong>Estrategias y Soluciones Minmer Global</strong> | 
            {{ $maintenance->asset->site->address ?? 'Dirección no especificada' }}
        </div>
        <div>Ref: MANT-{{ $maintenance->id }} | {{ $isCompleted ? 'Cerrado' : 'Abierto' }}</div>
    </footer>

    <main>
        <h1>{{ $docTitle }}</h1>

        <p class="text-justify">{!! $introText !!}</p>

        <h2>Detalles del Activo</h2>
        <table>
            <tr>
                <th>Etiqueta</th>
                <td><strong>{{ $maintenance->asset->asset_tag }}</strong></td>
            </tr>
            <tr>
                <th>Modelo y Serie</th>
                <td>{{ $maintenance->asset->model->name }} (SN: {{ $maintenance->asset->serial_number }})</td>
            </tr>
        </table>

        <h2>{{ $isCompleted ? 'Resumen del Servicio' : 'Detalles de la Orden' }}</h2>
        <table>
            <tr>
                <th>Tipo y Proveedor</th>
                <td>{{ $maintenance->type }} | {{ $maintenance->supplier ?? 'Interno' }}</td>
            </tr>
            <tr>
                <th>Dictamen / Estatus</th>
                <td style="color: {{ $statusColor }}; font-weight: bold; font-size: 12px;">
                    {{ $statusText }}
                </td>
            </tr>
            <tr>
                <th>Fechas</th>
                <td>
                    Inicio: {{ Carbon::parse($maintenance->start_date)->isoFormat('LL') }}
                    @if($isCompleted)
                        <br>Cierre: {{ Carbon::parse($maintenance->end_date)->isoFormat('LL') }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ $costLabel }}</th>
                <td>
                    <strong>$ {{ number_format($maintenance->cost, 2) }} MXN</strong>
                    @if(!$isCompleted) <span style="font-size:9px; color:#666;">(Aprox)</span> @endif
                </td>
            </tr>
        </table>

        <div class="notes-section {{ $isDamaged ? 'status-danger' : '' }}">
            <h3 style="color: var(--color-primary); margin-top: 0;">Diagnóstico / Motivo</h3>
            <p style="font-size: 10px;">{{ $maintenance->diagnosis }}</p>
        </div>

        @if($maintenance->actions_taken)
        <div class="notes-section">
            <h3 style="color: var(--color-primary); margin-top: 0;">{{ $actionLabel }}</h3>
            <p style="font-size: 10px;">{{ $maintenance->actions_taken }}</p>
        </div>
        @endif

        @if($maintenance->parts_used)
        <div class="notes-section" style="{{ $isDamaged ? 'border-left-color: #dc2626; background-color: #fef2f2;' : '' }}">
            <h3 style="color: {{ $isDamaged ? '#dc2626' : 'var(--color-primary)' }}; margin-top: 0;">
                {{ $partsLabel }}
            </h3>
            <p style="font-size: 10px;">{{ $maintenance->parts_used }}</p>
        </div>
        @endif

        @if(count($evidencePhotos) > 0)
        <div style="margin-top: 20px;">
            <h3 style="color: var(--color-primary); border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Evidencia Fotográfica</h3>
            <table style="width: 100%; border: none; margin-top:10px;">
                <tr>
                    @foreach($evidencePhotos as $photoBase64)
                        <td style="width: 33%; padding: 5px; border: none; text-align: center;">
                            <div style="border: 1px solid #ccc; padding: 3px; background: #fff;">
                                <img src="{{ $photoBase64 }}" style="width: 100%; max-height: 120px; object-fit: contain;">
                            </div>
                        </td>
                    @endforeach
                    @for($i = count($evidencePhotos); $i < 3; $i++) <td style="width: 33%; border: none;"></td> @endfor
                </tr>
            </table>
        </div>
        @endif
        
        <div class="signatures">
            <p class="text-justify" style="margin-bottom: 40px; font-size: 10px; font-style: italic;">
                @if(!$isCompleted)
                    Nota: Se autoriza la compra de las refacciones arriba descritas (si aplica) para proceder con la reparación.
                @elseif($isDamaged)
                    Nota: Se valida que los recursos descritos fueron utilizados en el proceso de revisión y se autoriza la baja del activo por inviabilidad técnica/económica.
                @else
                    Nota: El servicio ha concluido satisfactoriamente y el activo retorna a inventario funcional.
                @endif
            </p>
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 45%; border: none; text-align: center;">
                        <div class="signature-line" style="width: 80%; margin: 0 auto;"></div>
                        <p style="margin-top: 5px;"><strong>Responsable de TI</strong><br>Realizó / Supervisó</p>
                    </td>
                    <td style="width: 10%; border: none;"></td>
                    <td style="width: 45%; border: none; text-align: center;">
                        <div class="signature-line" style="width: 80%; margin: 0 auto;"></div>
                        <p style="margin-top: 5px;"><strong>Administración</strong><br>Autorización / Vo.Bo.</p>
                    </td>
                </tr>
            </table>
        </div>

    </main>
</body>
</html>