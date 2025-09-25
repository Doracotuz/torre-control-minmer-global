<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6;">
    {!! $bodyContent !!}

    @if ($signature_url)
        <div style="margin-top: 20px;">
            <img src="{{ $signature_url }}" alt="Firma" style="max-width: 400px; max-height: 100px; width: auto; height: auto;">
        </div>
    @endif
</body>
</html>