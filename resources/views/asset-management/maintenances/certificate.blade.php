<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documento de Mantenimiento</title>
    <style>
        @page { margin: 3.5cm 1.5cm 3cm 1.5cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #2b2b2b; line-height: 1.6; }
        :root { --color-primary: #2c3856; --color-accent: #ff9c00; }
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
        .signatures { margin-top: 60px; width: 100%; page-break-inside: avoid; }
        .signature-line { margin-top: 50px; border-top: 1px solid #333; }
        strong { color: #000; }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;
        Carbon::setLocale('es');

        // Determinamos el estado del documento
        $isCompleted = !is_null($maintenance->end_date);
        $isDamaged = $maintenance->asset->status === 'De Baja';
        
        if (!$isCompleted) {
            $docTitle = "Orden de Servicio y Diagnóstico";
            $costLabel = "Costo Estimado / Presupuesto";
            $partsLabel = "Refacciones Requeridas";
        } elseif ($isDamaged) {
            $docTitle = "Dictamen Técnico de Baja";
            $costLabel = "Costo del Diagnóstico/Intento";
            $partsLabel = "Insumos Utilizados";
        } else {
            $docTitle = "Certificado de Mantenimiento";
            $costLabel = "Costo Total del Servicio";
            $partsLabel = "Insumos y Partes Utilizadas";
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
            {{ $maintenance->asset->site->address ?? 'Dirección no especificada' }} | 
            Tel: +52 33 3022 1806
        </div>
        <div>Ref: MANT-{{ $maintenance->id }} | {{ $isCompleted ? 'Finalizado' : 'En Proceso' }}</div>
    </footer>

    <main>
        <h1>{{ $docTitle }}</h1>

        <p class="text-justify">
            @if(!$isCompleted)
                El presente documento detalla el diagnóstico inicial y los requerimientos para el servicio de mantenimiento del activo descrito a continuación.
            @elseif($isDamaged)
                Este documento certifica que el activo ha sido sometido a revisión técnica y se dictamina como <strong>IRREPARABLE</strong> o <strong>DAÑADO</strong>, procediendo a su baja definitiva del inventario activo.
            @else
                El presente documento certifica que se ha realizado el servicio de mantenimiento y el equipo ha sido verificado para su <strong>correcto funcionamiento</strong>, quedando disponible en almacén.
            @endif
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

        <h2>{{ $isCompleted ? 'Detalles del Servicio' : 'Presupuesto y Tiempos' }}</h2>
        <table>
            <tr>
                <th>Tipo de Servicio</th>
                <td>{{ $maintenance->type }}</td>
            </tr>
            <tr>
                <th>Proveedor / Técnico</th>
                <td>{{ $maintenance->supplier ?? 'Interno' }}</td>
            </tr>
            <tr>
                <th>Fechas</th>
                <td>
                    Inicio: <strong>{{ Carbon::parse($maintenance->start_date)->isoFormat('LL') }}</strong>
                    @if($isCompleted)
                        <br>Cierre: <strong>{{ Carbon::parse($maintenance->end_date)->isoFormat('LL') }}</strong>
                    @else
                        <br>Estatus: <span style="color: #d97706; font-weight: bold;">EN CURSO</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ $costLabel }}</th>
                <td>$ {{ number_format($maintenance->cost, 2) }} MXN</td>
            </tr>
        </table>

        <div class="notes-section">
            <h3 style="color: var(--color-primary); margin-top: 0;">Diagnóstico / Motivo</h3>
            <p style="font-size: 10px;">{{ $maintenance->diagnosis }}</p>
        </div>

        @if($maintenance->actions_taken)
        <div class="notes-section">
            <h3 style="color: var(--color-primary); margin-top: 0;">Acciones {{ $isCompleted ? 'Realizadas' : 'Propuestas' }}</h3>
            <p style="font-size: 10px;">{{ $maintenance->actions_taken }}</p>
        </div>
        @endif

        @if($maintenance->parts_used)
        <div class="notes-section">
            <h3 style="color: var(--color-primary); margin-top: 0;">{{ $partsLabel }}</h3>
            <p style="font-size: 10px;">{{ $maintenance->parts_used }}</p>
        </div>
        @endif

        <div class="signatures">
            <p class="text-justify" style="margin-bottom: 40px;">
                @if(!$isCompleted)
                    Se autoriza la realización del servicio descrito y la compra de las refacciones necesarias según el diagnóstico presentado.
                @elseif($isDamaged)
                    Se confirma el diagnóstico de daño irreversible y se autoriza la baja administrativa del activo.
                @else
                    Se valida la correcta ejecución del mantenimiento y la funcionalidad del equipo.
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
                        <p style="margin-top: 5px;"><strong>Administración</strong><br>
                            {{ $isCompleted ? 'Vo.Bo. Final' : 'Autorización de Servicio' }}
                        </p>
                    </td>
                </tr>
            </table>
        </div>

    </main>
</body>
</html>