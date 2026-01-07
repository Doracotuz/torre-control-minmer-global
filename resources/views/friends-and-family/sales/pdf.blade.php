<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Remisión</title>
    <style>
        @page { margin: 20px 30px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #000; }
        
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }

        .rounded-box {
            border: 2px solid #000;
            border-radius: 12px;
            padding: 5px;
            overflow: hidden;
            background-color: #fff;
        }

        .header-box { height: 110px; }
        .logo-img { max-height: 85px; max-width: 200px; margin-bottom: 0px; }
        
        .company-info-table td { padding: 1px 2px; font-size: 9px; border: none; }
        .label { font-weight: bold; width: 65px; display: inline-block; }

        .remision-content { padding: 5px 10px; }
        .remision-title { font-size: 14px; font-weight: bold; margin-bottom: 15px; }

        .client-box { margin-bottom: 10px; padding: 5px 8px; }
        .client-table td { padding: 3px 2px; border: none; font-size: 10px; }
        .client-label { font-weight: bold; font-style: italic; width: 75px; }

        .items-box {
            border: 2px solid #000;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 10px;
            min-height: 300px; 
        }
        .items-table th { 
            background-color: #d7e4bd; 
            border-bottom: 2px solid #000; 
            border-right: 2px solid #000; 
            padding: 4px; 
            text-align: center; 
            font-weight: bold; 
            font-style: italic;
            font-size: 10px;
        }
        .items-table th:last-child { border-right: none; }
        .items-table td { 
            border-right: 2px solid #000; 
            border-bottom: 1px solid #000;
            padding: 4px; 
            font-size: 10px;
        }
        .items-table td:last-child { border-right: none; }
        .items-table tr.empty-row td { height: 14px; color: transparent; }

        .footer-conforme { height: 60px; margin-bottom: 10px; position: relative; }
        .conforme-label { font-weight: bold; font-size: 9px; margin: 5px; }
        .conforme-line { width: 40%; border-bottom: 2px solid #000; position: absolute; bottom: 20px; left: 30%; }
        .conforme-text { position: absolute; bottom: 5px; width: 100%; text-align: center; font-weight: bold; font-size: 8px; }

        .transportista-box { height: 145px; position: relative; font-size: 9px; }
        .transport-label { font-weight: bold; margin: 5px; font-size: 10px; }
        .transport-table td { padding: 3px 2px; border: none; }

        .date-right { position: absolute; right: 20px; bottom: 55px; text-align: right; font-weight: bold; }
        .obs-container { position: absolute; bottom: 5px; left: 5px; right: 5px; }
        .obs-label { font-weight: bold; font-style: italic; color: #aaa; font-size: 9px; margin-bottom: 1px; }
        .obs-box { border: 1px solid #aaa; height: 25px; width: 100%; font-size: 8px; overflow: hidden; }
        .obs-text { padding: 2px; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .page-break { page-break-after: always; }
        .spacer-col { width: 10px; }
    </style>
</head>
<body>
    <div style="font-size: 9px; text-align: right; margin-bottom: 2px; color: #666;">COPIA: Original</div>

    <table class="main-table" cellspacing="0" cellpadding="0">
        <tr>
            <td width="73%">
                <div class="rounded-box header-box">
                    <table width="100%" height="100%">
                        <tr>
                            <td width="180" align="center" valign="middle">
                                <img src="{{ $logo_url }}" class="logo-img">
                            </td>
                            <td valign="top" style="padding-top: 5px;">
                                <table class="company-info-table">
                                    <tr>
                                        <td class="label">Razón Social</td>
                                        <td>{{ $emitter_name ?? 'Consorcio Monter S.A. de C.V.' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Teléfono</td>
                                        <td>{{ $emitter_phone ?? '5533347203' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Dirección</td>
                                        <td>{{ $emitter_address ?? 'Jose de Teresa 65 A' }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>{{ $emitter_colonia ?? 'San Angel, Alvaro Obregon, CDMX, Mexico' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Cód. Postal</td>
                                        <td>{{ $emitter_cp ?? '01000' }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            
            <td class="spacer-col"></td>

            <td width="26%">
                <div class="rounded-box header-box">
                    <div class="remision-content">
                        <div class="remision-title">REMISION</div>
                        <div style="margin-bottom: 15px;">Nro {{ $folio }}</div>
                        <table width="100%">
                            <tr>
                                <td style="font-weight: bold; font-size: 10px;">FECHA</td>
                                <td align="right" style="font-size: 10px;">{{ $date }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="rounded-box client-box">
        <table class="client-table">
            <tr>
                <td class="client-label">Señor/es:</td>
                <td width="45%">{{ $client_name }}</td>
                <td width="5%"></td>
                <td></td>
            </tr>
            <tr>
                <td class="client-label">Nombre</td>
                <td>{{ $company_name }}</td>
                <td class="client-label" align="right" style="padding-right: 5px;">Teléfono</td>
                <td>{{ $client_phone }}</td>
            </tr>
            <tr>
                <td class="client-label">Domicilio</td>
                <td colspan="3">{{ $address }}</td>
            </tr>
            <tr>
                <td class="client-label">Localidad</td>
                <td>{{ $locality }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="client-label">Otros Datos</td>
                <td colspan="3">{{ $delivery_date }}</td>
            </tr>
        </table>
    </div>

    <div class="items-box">
        <table class="items-table" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th width="10%">CANTIDAD</th>
                    <th width="50%">DESCRIPCION</th>
                    <th width="20%">CLAVE</th>
                    <th width="20%">PRECIO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td class="text-center">{{ $item['quantity'] }}</td>
                    <td style="font-style: italic; padding-left: 5px;">{{ $item['description'] }}</td>
                    <td class="text-center" style="font-style: italic;">{{ $item['sku'] }}</td>
                    <td class="text-center">${{ number_format($item['unit_price'], 2) }} / ${{ number_format($item['total_price'], 2) }}</td>
                </tr>
                @endforeach
                
                @for($i = count($items); $i < 15; $i++)
                <tr class="empty-row">
                    <td>.</td>
                    <td>.</td>
                    <td>.</td>
                    <td>.</td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="rounded-box footer-conforme">
        <div class="conforme-label">RECIBI CONFORME:</div>
        <div class="conforme-line"></div>
        <div class="conforme-text">FIRMA Y SELLO</div>
    </div>

    <div class="rounded-box transportista-box">
        <div class="transport-label">DATOS DEL TRANSPORTISTA</div>
        <table class="transport-table" style="width: 60%; margin-left: 5px;">
            <tr><td>Nombre</td></tr>
            <tr><td>Datos del Vehículo</td></tr>
            <tr><td>Chofer</td></tr>
            <tr><td>Lugar de Entrega</td></tr>
        </table>
        <div class="date-right">
            <div>Fecha</div>
            <div style="margin-top: 5px;">Hora</div>
        </div>
        <div class="obs-container">
            <div class="obs-label">Observaciones</div>
            <div class="obs-box">
                <div class="obs-text">{{ $observations }}</div>
            </div>
        </div>
    </div>
</body>
</html>