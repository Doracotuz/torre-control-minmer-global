<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha Técnica - {{ $product->sku }}</title>
    <style>
        @page { margin: 0px; padding: 0px; }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #2d3748;
        }

        .main-container {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        .sidebar {
            width: 30%;
            background-color: {{ $colors['primary'] }}; 
            color: #ffffff;
            vertical-align: top;
            padding: 40px 30px;
            height: 100vh;
        }

        .content {
            width: 70%;
            vertical-align: top;
            padding: 40px;
            background-color: #ffffff;
        }

        .logo-box {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .logo-img {
            width: 100%;
            max-width: 180px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .sidebar-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.6);
            margin-bottom: 5px;
            margin-top: 30px;
        }
        
        .sidebar-value-large {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 5px;
        }

        .sidebar-value {
            font-size: 16px;
            font-weight: 500;
            color: #ffffff;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 10px;
        }

        .product-brand {
            font-size: 50px;
            font-weight: 900;
            color: #1a202c;
            line-height: 0.9;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .product-sku {
            font-size: 20px;
            color: {{ $colors['primary'] }};
            font-weight: bold;
            margin-bottom: 30px;
            display: inline-block;
            background: #e6fffa;
            padding: 5px 15px;
            border-radius: 50px;
        }

        .image-stage {
            width: 100%;
            height: 450px;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .main-bottle {
            height: 100%;
            max-height: 450px;
            object-fit: contain;
            filter: drop-shadow(0px 10px 20px rgba(0,0,0,0.15));
        }

        .metrics-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px;
        }

        .metric-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .metric-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #718096;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .metric-number {
            font-size: 24px;
            font-weight: 800;
            color: #2d3748;
        }

        .metric-sub {
            font-size: 11px;
            color: {{ $colors['primary'] }};
            margin-top: 3px;
        }

        .progress-bg {
            background: #e2e8f0;
            height: 6px;
            width: 100%;
            border-radius: 3px;
            margin-top: 8px;
            overflow: hidden;
        }
        .progress-fill {
            background: {{ $colors['accent'] }};
            height: 100%;
        }

        .tech-footer {
            margin-top: 40px;
            border-top: 2px solid #edf2f7;
            padding-top: 20px;
            font-size: 10px;
            color: #a0aec0;
            text-align: right;
        }

        .metric-card[style*="background-color: #f0fff4"] {
             border-color: {{ $colors['primary'] }}33;
        }        

    </style>
</head>
<body>
    @php
        $largo = floatval($product->length);
        $ancho = floatval($product->width);
        $alto  = floatval($product->height);
        
        $volumen_cm3 = $largo * $ancho * $alto;
        $volumen_m3  = $volumen_cm3 / 1000000;
        
        $cajas_por_cama = intval($extra['boxes_per_layer'] ?? 0);
        $camas_por_pallet = intval($extra['layers_per_pallet'] ?? 0);
        $total_cajas_pallet = $cajas_por_cama * $camas_por_pallet;
        
        $piezas_por_caja = intval($product->pieces_per_box);
        $total_unidades_pallet = $total_cajas_pallet * $piezas_por_caja;

        function getIconUrl($filename) {
            try {
                $cleanName = str_replace('.svg', '', $filename);
                if (!str_ends_with($cleanName, '.png')) $cleanName .= '.png';
                return Illuminate\Support\Facades\Storage::disk('s3')->url('IconosFichaTecnica/' . $cleanName);
            } catch (\Exception $e) { return ''; }
        }
    @endphp

    <table class="main-container">
        <tr>
            <td class="sidebar">
                
                <div class="logo-box">
                    <img src="{{ $logo_url }}" class="logo-img" alt="Logo">
                </div>

                <div class="sidebar-title">Clasificación</div>
                <div class="sidebar-value-large">{{ $product->type ?? 'General' }}</div>
                
                <div class="sidebar-title" style="margin-top: 40px;">Especificaciones Base</div>
                
                <div class="sidebar-title">SKU ID</div>
                <div class="sidebar-value">{{ $product->sku }}</div>

                <div class="sidebar-title">Código UPC</div>
                <div class="sidebar-value">{{ $product->upc ?? 'No Asignado' }}</div>

                <div class="sidebar-title">Alcohol Vol.</div>
                <div class="sidebar-value" style="border:none; padding-bottom:0;">
                    {{ $extra['alcohol_vol'] ?? 'N/A' }}
                    <div class="progress-bg">
                        <div class="progress-fill" style="width: {{ floatval($extra['alcohol_vol'] ?? 40) }}%;"></div>
                    </div>
                </div>

                <div style="margin-top: auto; padding-top: 100px;">
                    <div class="sidebar-title">Dimensión Unitaria</div>
                    <div style="font-size: 11px; color: #fff;">
                        Largo: {{ $product->length }}cm <br>
                        Ancho: {{ $product->width }}cm <br>
                        Alto: {{ $product->height }}cm
                    </div>
                </div>
            </td>

            <td class="content">
                
                <div class="product-sku">FICHA TÉCNICA #{{ $product->id }}</div>
                <h1 class="product-brand">{{ $product->brand ?? 'PRODUCTO' }}</h1>
                <p style="color: #718096; margin-bottom: 30px; line-height: 1.5;">
                    {{ $product->description }}
                </p>

                <div class="image-stage">
                    @if($product->photo_path)
                        <img src="{{ $product->photo_url }}" class="main-bottle">
                    @endif
                </div>

                <table class="metrics-grid">
                    <tr>
                        <td class="metric-card">
                            <div class="metric-label">Configuración Caja</div>
                            <div class="metric-number">{{ $product->pieces_per_box }} <span style="font-size:12px;">Pzas</span></div>
                            <div class="metric-sub">Peso Master: {{ !empty($extra['master_box_weight']) ? $extra['master_box_weight'] : ($product->master_box_weight ?? '-') }} kg</div>
                        </td>

                        <td class="metric-card">
                            <div class="metric-label">Volumetría</div>
                            <div class="metric-number">{{ number_format($volumen_cm3, 0) }} <span style="font-size:12px;">cm³</span></div>
                            <div class="metric-sub">Aprox. {{ number_format($volumen_m3, 4) }} m³</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="metric-card">
                            <div class="metric-label">Eficiencia Pallet</div>
                            <div class="metric-number">{{ $total_cajas_pallet }} <span style="font-size:12px;">Cajas Totales</span></div>
                            <div class="metric-sub">
                                {{ $cajas_por_cama }} Cajas x {{ $camas_por_pallet }} Camas
                            </div>
                        </td>

                        <td class="metric-card" style="background-color: #f0fff4; border-color: #c6f6d5;">
                            <div class="metric-label" style="color: #276749;">Total Unidades / Pallet</div>
                            <div class="metric-number" style="color: #22543d;">{{ number_format($total_unidades_pallet) }}</div>
                            <div class="metric-sub" style="color: #2f855a;">Botellas por tarima</div>
                        </td>
                    </tr>
                </table>

                <div class="tech-footer">
                    Cálculos generados automáticamente por el sistema Control Tower de Minmer Global | {{ date('Y-m-d H:i') }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>