<!DOCTYPE html><html><head><title>Etiquetas de Ubicación</title>
<style>
    @page { size: 100mm 50mm; margin: 0; } body, html { margin: 0; padding: 0; font-family: 'Helvetica', sans-serif; }
    .label-page { width: 100%; height: 100%; page-break-after: always; box-sizing: border-box; padding: 5mm; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; }
    .label-page:last-child { page-break-after: auto; }
    .location-code { font-size: 24px; font-weight: bold; letter-spacing: 2px; margin-top: 4mm; }
</style>
</head><body>
    @foreach ($locations as $location)
        <div class="label-page">
            <div>{!! DNS1D::getBarcodeHTML($location->code, 'C128', 2.5, 50, 'black', false) !!}</div>
            <div class="location-code">{{ $location->code }}</div>
        </div>
    @endforeach
</body></html>