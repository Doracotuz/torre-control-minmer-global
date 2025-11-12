<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Productos</title>
    <style>
        @page { margin: 0px; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .header {
            background-color: #2c3856;
            color: white;
            padding: 30px 40px;
            height: 60px;
            display: table;
            width: 100%;
        }
        .header-text {
            display: table-cell;
            vertical-align: middle;
            width: 70%;
        }
        .header-text h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        .header-text p { margin: 5px 0 0 0; font-size: 12px; color: #aab7d1; }
        
        .logo-container {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 30%;
        }
        .logo { height: 45px; }

        .content { padding: 40px; }
        
        .product-card {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px;
            page-break-inside: avoid;
            position: relative;
        }

        .product-table { width: 100%; border-collapse: collapse; }
        .img-cell { width: 120px; vertical-align: top; }
        .info-cell { vertical-align: top; padding-left: 20px; }
        .price-cell { width: 100px; vertical-align: top; text-align: right; }

        .product-img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border-radius: 6px;
            border: 1px solid #f3f4f6;
            background-color: #fff;
        }

        .sku {
            font-family: 'Courier New', monospace;
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 4px;
            display: block;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin: 0 0 5px 0;
            line-height: 1.2;
        }

        .brand-badge {
            display: inline-block;
            background-color: #f3f4f6;
            color: #4b5563;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 5px;
        }

        .price {
            color: #2c3856;
            font-size: 18px;
            font-weight: bold;
        }

        .currency { font-size: 12px; color: #6b7280; vertical-align: top; }

        .status-inactive {
            color: #ef4444;
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
            display: block;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background-color: #f3f4f6;
            text-align: center;
            line-height: 30px;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-text">
            <h1>Catálogo Friends & Family</h1>
            <p>Generado el: {{ $date }}</p>
        </div>
        <div class="logo-container">
            <img src="{{ $logo_url }}" class="logo" alt="Logo">
        </div>
    </div>

    <div class="content">
        @foreach($products as $product)
            <div class="product-card">
                <table class="product-table">
                    <tr>
                        <td class="img-cell">
                            <img src="{{ $product->photo_url }}" class="product-img">
                        </td>
                        <td class="info-cell">
                            <span class="sku">SKU: {{ $product->sku }}</span>
                            <h3 class="title">{{ $product->description }}</h3>
                            @if($product->brand)
                                <span class="brand-badge">{{ $product->brand }}</span>
                            @endif
                            @if($product->type)
                                <span class="brand-badge" style="background-color: #eef2ff; color: #2c3856;">{{ $product->type }}</span>
                            @endif
                            
                            @if(!$product->is_active)
                                <span class="status-inactive">● PRODUCTO INACTIVO</span>
                            @endif
                        </td>
                        <td class="price-cell">
                            <div class="price">
                                <span class="currency">$</span>{{ number_format($product->price, 2) }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach
    </div>

    <div class="footer">
        Confidencial - Uso Interno - Página <span class="page-number"></span>
    </div>
</body>
</html>