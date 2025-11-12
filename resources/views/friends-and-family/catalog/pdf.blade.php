<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo Exclusivo</title>
    <style>
        @page { 
            margin: 0px; 
            font-family: 'Helvetica', 'Arial', sans-serif;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .header {
            background-color: #2c3856;
            color: white;
            padding: 30px 50px;
            margin-bottom: 20px;
            position: relative;
        }
        .header-content {
            border-left: 5px solid #ff9c00;
            padding-left: 20px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 24px; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 12px;
            font-weight: normal;
            color: #d1d5db;
            text-transform: uppercase;
        }
        .logo-corner {
            position: absolute;
            top: 30px;
            right: 50px;
            height: 40px;
        }

        .container {
            padding: 0 40px;
            width: 100%;
        }
        .row {
            width: 100%;
            clear: both;
            margin-bottom: 15px;
        }
        .col {
            float: left;
            width: 45%; 
        }
        .col:nth-child(even) {
            margin-left: 4%; 
        }
        .card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            page-break-inside: avoid;
            height: 520px; 
            position: relative;
        }

        .img-wrapper {
            height: 320px;
            width: 100%;
            background-color: white;
            border-bottom: 1px solid #f3f4f6;
            text-align: center;
            line-height: 310px;
            overflow: hidden;
            padding: 5px;
        }

        .img-wrapper img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            vertical-align: middle;
            margin: 0 auto;
        }

        .card-body {
            padding: 15px 20px;
        }

        .sku {
            color: #374151;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 5px;
            font-family: 'Courier New', monospace;
        }

        .title {
            font-size: 14px;
            color: #4b5563;
            font-weight: normal;
            margin: 0 0 10px 0;
            line-height: 1.3;
            height: 38px;
            overflow: hidden;
        }

        .badges { 
            margin-bottom: 15px; 
            height: 20px; 
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 5px;
        }
        .badge-brand { background-color: #2c3856; color: white; }
        .badge-type { background-color: #e5e7eb; color: #374151; }

        .price-block {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dotted #e5e7eb;
            text-align: right;
            display: block;
        }
        
        .price-label {
            display: block;
            font-size: 8px;
            color: #9ca3af;
            text-transform: uppercase;
        }
        .price-amount {
            font-size: 26px;
            font-weight: 800;
            color: #2c3856;
        }
        .currency { font-size: 14px; vertical-align: top; margin-right: 2px; }

        .inactive-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.85);
            z-index: 20;
        }
        .inactive-text {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            background: #ef4444;
            color: white;
            padding: 10px 30px;
            font-weight: bold;
            font-size: 16px;
            border: 2px solid white;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            padding: 10px;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ $logo_url }}" class="logo-corner">
        <div class="header-content">
            <h1>Colección Friends & Family</h1>
        </div>
    </div>

    <div class="container">
        @foreach($products->chunk(2) as $row)
            <div class="row">
                @foreach($row as $product)
                    <div class="col">
                        <div class="card">
                            @if(!$product->is_active)
                                <div class="inactive-overlay">
                                    <div class="inactive-text">AGOTADO</div>
                                </div>
                            @endif

                            <div class="img-wrapper">
                                <img src="{{ $product->photo_url }}" alt="{{ $product->sku }}">
                            </div>
                            
                            <div class="card-body">
                                <span class="sku">{{ $product->sku }}</span>
                                
                                <div class="title">{{ $product->description }}</div>
                                
                                <div class="badges">
                                    @if($product->brand)
                                        <span class="badge badge-brand">{{ $product->brand }}</span>
                                    @endif
                                    @if($product->type)
                                        <span class="badge badge-type">{{ $product->type }}</span>
                                    @endif
                                </div>

                                <div class="price-block">
                                    <span class="price-amount">
                                        <span class="currency">$</span>{{ number_format($product->price, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="footer">
        • Documento confidencial • <span class="page-number"></span>
    </div>
</body>
</html>