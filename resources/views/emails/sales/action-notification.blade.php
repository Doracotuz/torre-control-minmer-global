<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Notificación de Pedido</title>
    <style type="text/css">
        /* Reset de estilos básicos */
        body { margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #f4f4f4; }
        table, td { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        
        /* Estilos generales */
        body, #bodyTable { font-family: 'Helvetica', Arial, sans-serif; color: #333333; line-height: 1.6; }
        
        /* Media Queries para móviles */
        @media screen and (max-width: 600px) {
            .mobile-width { width: 100% !important; max-width: 100% !important; }
            .mobile-padding { padding-left: 15px !important; padding-right: 15px !important; }
            .mobile-stack { display: block !important; width: 100% !important; }
            .mobile-center { text-align: center !important; }
            .hide-mobile { display: none !important; }
        }
    </style>
    </head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4;">
    
    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="bodyTable" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 680px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" class="mobile-width">
                    
                    <tr>
                        <td align="center" style="padding: 30px 30px 20px 30px; border-bottom: 2px solid #eeeeee;" class="mobile-padding">
                            @if(isset($data['logo_url']))
                                <img src="{{ $data['logo_url'] }}" alt="Logo" width="150" style="display: block; max-height: 50px; width: auto; margin-bottom: 15px;">
                            @endif
                            
                            <h2 style="margin: 0; color: #2c3856; font-size: 24px; font-weight: bold;">Notificación de Pedido</h2>
                            
                            <table border="0" cellpadding="0" cellspacing="0" style="margin-top: 15px;">
                                <tr>
                                    @php
                                        $badgeColor = '#2c3856';
                                        $badgeText = 'NOTIFICACIÓN';
                                        
                                        if($type == 'new') { $badgeColor = '#28a745'; $badgeText = 'NUEVA VENTA'; }
                                        elseif($type == 'update') { $badgeColor = '#ff9c00'; $badgeText = 'ACTUALIZACIÓN'; }
                                        elseif($type == 'cancel') { $badgeColor = '#dc3545'; $badgeText = 'CANCELADO'; }
                                        elseif($type == 'admin_alert') { $badgeColor = '#2c3856'; $badgeText = 'REQUIERE APROBACIÓN'; }
                                    @endphp
                                    
                                    <td align="center" bgcolor="{{ $badgeColor }}" style="border-radius: 50px; padding: 6px 18px;">
                                        <span style="color: #ffffff; font-size: 12px; font-weight: bold; font-family: Helvetica, Arial, sans-serif;">{{ $badgeText }}</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 15px 0 0 0; font-size: 18px; font-weight: bold; color: #333333;">Folio #{{ $data['folio'] }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px 30px 10px 30px;" class="mobile-padding">
                            <h3 style="margin: 0 0 15px 0; color: #333333; font-size: 18px;">Datos Generales</h3>
                            
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="35%" style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #777777; font-size: 13px; font-weight: bold;">Cliente:</td>
                                    <td width="65%" style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #333333; font-size: 13px;">{{ $data['client_name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #777777; font-size: 13px; font-weight: bold;">Empresa:</td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #333333; font-size: 13px;">{{ $data['company_name'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #777777; font-size: 13px; font-weight: bold;">Entrega:</td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #333333; font-size: 13px;">{{ $data['delivery_date'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #777777; font-size: 13px; font-weight: bold;">Surtidor:</td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #333333; font-size: 13px;">{{ $data['surtidor_name'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #777777; font-size: 13px; font-weight: bold;">Tipo:</td>
                                    <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #333333; font-size: 13px; text-transform: capitalize;">{{ $data['order_type'] ?? 'Normal' }}</td>
                                </tr>
                                @if($type == 'cancel')
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #dc3545; font-size: 13px; font-weight: bold;">Motivo Cancelación:</td>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #dc3545; font-size: 13px; font-weight: bold;">{{ $data['cancel_reason'] ?? 'Solicitud de usuario' }}</td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    @if($type != 'cancel')
                    <tr>
                        <td style="padding: 20px 30px;" class="mobile-padding">
                            <h3 style="margin: 0 0 15px 0; color: #333333; font-size: 18px;">Detalle</h3>
                            
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <thead>
                                    <tr bgcolor="#2c3856">
                                        <th align="left" style="padding: 10px; color: #ffffff; font-size: 11px; font-family: Helvetica, Arial, sans-serif;">SKU</th>
                                        <th align="left" style="padding: 10px; color: #ffffff; font-size: 11px; font-family: Helvetica, Arial, sans-serif;">Descripción</th>
                                        <th align="center" style="padding: 10px; color: #ffffff; font-size: 11px; font-family: Helvetica, Arial, sans-serif;">Cant.</th>
                                        @if(($data['order_type'] ?? 'normal') === 'normal')
                                            <th align="right" style="padding: 10px; color: #ffffff; font-size: 11px; font-family: Helvetica, Arial, sans-serif;">P. Lista</th>
                                            <th align="center" style="padding: 10px; color: #ffffff; font-size: 11px; font-family: Helvetica, Arial, sans-serif;">Desc.</th>
                                            <th align="right" style="padding: 10px; color: #ffffff; font-size: 11px; font-family: Helvetica, Arial, sans-serif;">Total</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data['items'] as $item)
                                    <tr>
                                        <td align="left" style="padding: 10px; border-bottom: 1px solid #eeeeee; font-size: 11px;">{{ $item['sku'] }}</td>
                                        <td align="left" style="padding: 10px; border-bottom: 1px solid #eeeeee; font-size: 11px;">{{ $item['description'] }}</td>
                                        <td align="center" style="padding: 10px; border-bottom: 1px solid #eeeeee; font-size: 11px; font-weight: bold;">{{ $item['quantity'] }}</td>
                                        
                                        @if(($data['order_type'] ?? 'normal') === 'normal')
                                            <td align="right" style="padding: 10px; border-bottom: 1px solid #eeeeee; font-size: 11px;">
                                                ${{ number_format($item['base_price'] ?? $item['unit_price'], 2) }}
                                            </td>
                                            <td align="center" style="padding: 10px; border-bottom: 1px solid #eeeeee; font-size: 11px;">
                                                @if(isset($item['discount_percentage']) && $item['discount_percentage'] > 0)
                                                    <span style="color: #e65100; font-weight: bold; display: block;">-{{ $item['discount_percentage'] }}%</span>
                                                    <span style="color: #e65100; font-size: 9px; white-space: nowrap;">(-${{ number_format($item['discount_amount'], 2) }})</span>
                                                @else
                                                    <span style="color: #cccccc;">-</span>
                                                @endif
                                            </td>
                                            <td align="right" style="padding: 10px; border-bottom: 1px solid #eeeeee; font-size: 11px; font-weight: bold; color: #2c3856;">
                                                ${{ number_format($item['total_price'], 2) }}
                                            </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            @if(($data['order_type'] ?? 'normal') === 'normal')
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="right" style="padding-top: 15px; font-size: 18px; font-weight: bold; color: #2c3856;">
                                            Gran Total: ${{ number_format($data['grandTotal'] ?? $data['total'] ?? 0, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                    </tr>
                    @endif

                    @if($type === 'admin_alert' && isset($approveUrl) && isset($rejectUrl))
                    <tr>
                        <td align="center" style="padding: 20px 30px 40px 30px; border-top: 1px solid #eeeeee;" class="mobile-padding">
                            <p style="margin: 0 0 20px 0; font-weight: bold; font-size: 14px;">Acciones Rápidas:</p>
                            
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-right: 10px;" class="mobile-stack">
                                        <table border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center" bgcolor="#28a745" style="border-radius: 5px;">
                                                    <a href="{{ $approveUrl }}" target="_blank" style="display: inline-block; padding: 12px 24px; font-family: Helvetica, Arial, sans-serif; font-size: 14px; color: #ffffff; text-decoration: none; font-weight: bold; border: 1px solid #28a745; border-radius: 5px;">
                                                        ✔ Aprobar
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    
                                    <td class="mobile-stack" height="10" style="font-size: 0; line-height: 0;">&nbsp;</td>

                                    <td align="center" style="padding-left: 10px;" class="mobile-stack">
                                        <table border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center" bgcolor="#dc3545" style="border-radius: 5px;">
                                                    <a href="{{ $rejectUrl }}" target="_blank" style="display: inline-block; padding: 12px 24px; font-family: Helvetica, Arial, sans-serif; font-size: 14px; color: #ffffff; text-decoration: none; font-weight: bold; border: 1px solid #dc3545; border-radius: 5px;">
                                                        ✖ Rechazar
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 10px; color: #999999; margin-top: 15px;">Enlaces válidos por 48 horas.</p>
                        </td>
                    </tr>
                    @endif

                    <tr>
                        <td align="center" style="padding: 20px; background-color: #f8f9fa; color: #999999; font-size: 11px; border-top: 1px solid #eeeeee;">
                            Generado automáticamente por el sistema Control Tower de Minmer Global.<br>
                            Fecha de movimiento: {{ date('d/m/Y H:i') }}
                        </td>
                    </tr>

                </table>
                
                </td>
        </tr>
    </table>
</body>
</html>