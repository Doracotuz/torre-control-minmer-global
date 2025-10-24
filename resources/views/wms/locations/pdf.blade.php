<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Etiquetas de Ubicación</title>
    <style>
        @page {
            size:100mm 50mm;
            margin: 0;
        }

        body, html { 
            margin: 0; 
            padding: 0; 
            font-family: 'Helvetica', sans-serif; 
        }

        .label-page {
            width: 100%;
            height: 50mm;
            page-break-after: always;
            
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            
            min-height: 100%;
            overflow: hidden;
        }
        
        .label-page:last-child {
            page-break-after: auto;
        }

        .barcode-wrapper {
            width: 98%;
            height: 45%;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 4mm;
            padding-left: 4mm;
            box-sizing: border-box;
        }

        .barcode-container {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .barcode-container svg {
            max-width: 95%;
            max-height: 80%;
            display: block;
            margin: 0 auto;
        }

        .code-text {
            font-size: 40px;
            font-weight: bold;
            margin: 0;
            padding: 1mm 0;
            line-height: 1.2;
            text-align: center;
            width: 100%;
            flex-shrink: 0;
        }
        .location-type {
            font-size: 15px;
            font-style: italic;
            color: #000000ff;
            margin-top: 0.5mm;
            line-height: 1;
        }        
    </style>
</head>
<body>
    @foreach ($locations as $location)
        <div class="label-page">
            <div class="barcode-wrapper">
                <div class="barcode-container">
                    {!! DNS1D::getBarcodeHTML($location->code, 'C128', 4.3, 100, 'black', true) !!}
                </div>
            </div>
            <div class="code-text">
                {{ $location->aisle }}-{{ $location->rack }}-{{ $location->shelf }}-{{$location->bin}}
            </div>
            <div class="location-type">
                Tipo: {{ $location->translated_type }}
            </div>
        </div>
    @endforeach
</body>
</html>