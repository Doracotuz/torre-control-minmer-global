<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Etiquetas LPN</title>
    <style>
        @page {
            size: 100mm 50mm;
            margin: 0;
        }
        body, html { 
            margin: 0; 
            padding: 0; 
            font-family: 'Helvetica', sans-serif;
        }
        
        .label-page {
            width: 100%;
            height: 100%;
            page-break-after: always;
            box-sizing: border-box;
            padding: 3mm 5mm; 
            display: flex;
            flex-direction: column;
            justify-content: center; 
            align-items: center;
            text-align: center;
        }
        .label-page:last-child {
            page-break-after: auto;
        }

        .lpn-text {
            font-size: 20px; 
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 2mm;
        }
    </style>
</head>
<body>
    @foreach ($lpns as $lpn)
        <div class="label-page">
            <div class="barcode-container">
                {!! DNS1D::getBarcodeHTML($lpn->lpn, 'C128', 1.5, 120, 'black', false) !!}
            </div>

            <div class="lpn-text">
                {{ $lpn->lpn }}
            </div>
        </div>
    @endforeach
</body>
</html>