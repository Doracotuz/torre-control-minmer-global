<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Solicitud de Servicio {{ $serviceRequest->folio }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        header { width: 100%; border-bottom: 2px solid #ddd; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #2c3856; }
        .meta { width: 100%; margin-bottom: 20px; }
        .meta td { vertical-align: top; padding: 5px; }
        .label { font-weight: bold; color: #666; font-size: 10px; text-transform: uppercase; }
        .value { font-size: 14px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background-color: #f3f4f6; padding: 8px; text-align: left; border-bottom: 1px solid #ddd; font-size: 10px; text-transform: uppercase; }
        table.items td { padding: 8px; border-bottom: 1px solid #eee; }
        .total-row td { font-weight: bold; border-top: 2px solid #ddd; font-size: 14px; }
        .signatures { margin-top: 50px; width: 100%; }
        .signature-box { width: 45%; float: left; border-top: 1px solid #333; padding-top: 10px; text-align: center; margin-right: 5%; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <header>
        <div class="logo">Solistica - WMS</div> <!-- Adjust specific company name if known, using generic for now based on context -->
    </header>

    <table class="meta">
        <tr>
            <td width="50%">
                <div class="label">Folio</div>
                <div class="value">{{ $serviceRequest->folio }}</div>
                <br>
                <div class="label">Fecha Solicitud</div>
                <div class="value">{{ $serviceRequest->requested_at->format('d/m/Y H:i') }}</div>
            </td>
            <td width="50%">
                <div class="label">Estatus</div>
                <div class="value">{{ ucfirst(__($serviceRequest->status)) }}</div>
                <br>
                <div class="label">Generado Por</div>
                <div class="value">{{ $serviceRequest->user->name }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Cliente / Área</div>
                <div class="value">{{ $serviceRequest->area->name }}</div>
            </td>
            <td>
                <div class="label">Almacén</div>
                <div class="value">{{ $serviceRequest->warehouse->name }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Tipo</th>
                <th style="text-align: center;">Cant.</th>
                <th style="text-align: right;">Costo Unit.</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($serviceRequest->valueAddedServices as $assignment)
            <tr>
                <td>{{ $assignment->service->code }}</td>
                <td>{{ $assignment->service->description }}</td>
                <td>{{ ucfirst($assignment->service->type) }}</td>
                <td style="text-align: center;">{{ $assignment->quantity }}</td>
                <td style="text-align: right;">${{ number_format($assignment->cost_snapshot, 2) }}</td>
                <td style="text-align: right;">${{ number_format($assignment->quantity * $assignment->cost_snapshot, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">TOTAL:</td>
                <td style="text-align: right;">${{ number_format($serviceRequest->valueAddedServices->sum(fn($a) => $a->quantity * $a->cost_snapshot), 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signatures clearfix">
        <div class="signature-box">
            Solicitado Por<br>
            <span style="font-size: 10px; color: #666;">{{ $serviceRequest->area->name }}</span>
        </div>
        <div class="signature-box" style="float: right; margin-right: 0;">
            Autorizado Por<br>
            <span style="font-size: 10px; color: #666;">Almacén</span>
        </div>
    </div>
</body>
</html>
