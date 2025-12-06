<!DOCTYPE html>

<html lang="es"> 
    <head> 
        <meta charset="UTF-8"> 
        <title>Condiciones de Entrega - {{ $client->name }}</title> 
        <style> 
        @page { margin: 0; padding: 0; }
        body { 
                font-family: 'Helvetica', 'Arial', sans-serif; 
                margin: 0; 
                padding: 0; 
                background-color: #ffffff; 
                color: #333333; 
            }

            .header-container {
                background-color: #ffffff;
                padding: 40px 50px 20px 50px;
                border-bottom: 4px solid #2c3856;
            }

            .header-table { width: 100%; }
            
            .logo { 
                max-height: 70px; 
                width: auto; 
            }

            .doc-info {
                text-align: right;
            }

            .doc-title {
                color: #2c3856;
                font-size: 22px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin: 0;
            }

            .doc-meta {
                color: #888;
                font-size: 10px;
                margin-top: 5px;
                text-transform: uppercase;
            }

            .client-card {
                background-color: #f8f9fa;
                margin: 30px 50px 10px 50px;
                padding: 20px;
                border-left: 5px solid #ff9c00;
                border-radius: 4px;
            }

            .client-label {
                font-size: 9px;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 1px;
                font-weight: bold;
                margin-bottom: 4px;
            }

            .client-name {
                font-size: 20px;
                color: #2c3856;
                font-weight: bold;
            }

            .content {
                padding: 10px 50px 50px 50px;
            }

            .section-heading {
                margin-top: 35px;
                margin-bottom: 20px;
                border-bottom: 1px solid #e0e0e0;
                padding-bottom: 10px;
            }

            .section-badge {
                background-color: #2c3856;
                color: #ffffff;
                padding: 6px 12px;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
                border-radius: 4px;
                display: inline-block;
            }

            .section-title-text {
                color: #2c3856;
                font-size: 14px;
                font-weight: bold;
                text-transform: uppercase;
                margin-left: 10px;
                vertical-align: middle;
            }

            .grid-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 10px;
                margin: 0 -10px;
            }

            .grid-cell {
                width: 33.33%;
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 12px;
                vertical-align: top;
            }

            .item-label {
                font-size: 10px;
                color: #555;
                font-weight: bold;
                margin-bottom: 8px;
                display: block;
                height: 25px; 
                overflow: hidden;
            }

            .status-pill {
                display: block;
                text-align: center;
                padding: 4px 0;
                border-radius: 4px;
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
            }

            .status-yes {
                background-color: #e3f9e5;
                color: #1f7a24;
                border: 1px solid #ccebd0;
            }

            .status-no {
                background-color: #f1f3f5;
                color: #adb5bd;
                border: 1px solid #e9ecef;
            }

            .gallery-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 15px 0;
                margin: 15px -15px 0 -15px;
            }

            .gallery-cell {
                width: 33.33%;
                vertical-align: top;
            }

            .photo-card {
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                overflow: hidden;
                background-color: #fff;
                box-shadow: 0 2px 5px rgba(0,0,0,0.03);
            }

            .photo-frame {
                width: 100%;
                height: 250px; 
                background-color: #f8f9fa;
                text-align: center;
                line-height: 248px; 
                vertical-align: middle;
                position: relative;
                overflow: hidden;
            }

            .photo-img {
                max-width: 100%;
                max-height: 100%;
                width: auto;
                height: auto;
                vertical-align: middle;
                display: inline-block;
            }

            .photo-placeholder {
                width: 100%;
                height: 250px;
                line-height: 250px;
                text-align: center;
                color: #cbd5e0;
                font-size: 9px;
                font-weight: bold;
                text-transform: uppercase;
            }

            .photo-caption {
                padding: 10px;
                text-align: center;
                background-color: #ffffff;
                border-top: 1px solid #f0f0f0;
                font-size: 9px;
                color: #2c3856;
                font-weight: bold;
            }

            .footer {
                position: fixed;
                bottom: 30px;
                left: 50px;
                right: 50px;
                padding-top: 15px;
                border-top: 1px solid #eeeeee;
                font-size: 8px;
                color: #999;
                text-align: justify;
            }

            .page-break { page-break-inside: avoid; }
        </style>
        </head> <body>

        <div class="header-container">
            <table class="header-table">
                <tr>
                    <td align="left" style="vertical-align: bottom;">
                        <img src="{{ $logoUrl }}" class="logo" alt="Logo Minmer">
                    </td>
                    <td align="right" style="vertical-align: bottom;">
                        <h1 class="doc-title">Condiciones de Entrega</h1>
                        <div class="doc-meta">Documento de Referencia Operativa</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="client-card">
            <div class="client-label">Cliente Seleccionado</div>
            <div class="client-name">{{ $client->name }}</div>
            
            @if(isset($specific_address) || isset($specific_observations))
                <div style="margin-top: 15px; border-top: 1px dashed #ccc; padding-top: 10px;">
                    <table width="100%">
                        <tr>
                            <td valign="top" width="60%">
                                <div class="client-label">Dirección de Entrega (Pedido Actual)</div>
                                <div style="font-size: 11px; color: #444;">{{ $specific_address ?? 'N/A' }}</div>
                            </td>
                            <td valign="top" width="40%">
                                <div class="client-label">Observaciones Específicas</div>
                                <div style="font-size: 11px; color: #444; font-style: italic;">{{ $specific_observations ?? 'Ninguna' }}</div>
                            </td>
                        </tr>
                    </table>
                </div>
            @endif
        </div>

        <div class="content">
            
            <div class="page-break">
                <div class="section-heading">
                    <span class="section-badge">01</span>
                    <span class="section-title-text">Requisitos de Preparación</span>
                </div>

                @php $prepChunks = array_chunk($prepFields, 3, true); @endphp
                <table class="grid-table">
                    @foreach($prepChunks as $chunk)
                        <tr>
                            @foreach($chunk as $label => $key)
                                <td class="grid-cell">
                                    <span class="item-label">{{ $label }}</span>
                                    <span class="status-pill {{ $conditions->$key ? 'status-yes' : 'status-no' }}">
                                        {{ $conditions->$key ? 'REQUERIDO' : 'NO APLICA' }}
                                    </span>
                                </td>
                            @endforeach
                            @for($k = 0; $k < (3 - count($chunk)); $k++) <td width="33.33%"></td> @endfor
                        </tr>
                    @endforeach
                </table>

                <table class="gallery-table">
                    <tr>
                        @for($i=1; $i<=3; $i++)
                            <td class="gallery-cell">
                                <div class="photo-card">
                                    <div class="photo-frame">
                                        @if($url = $conditions->getImageUrl('prep_img_'.$i))
                                            <img src="{{ $url }}" class="photo-img">
                                        @else
                                            <div class="photo-placeholder">Espacio Disponible</div>
                                        @endif
                                    </div>
                                    <div class="photo-caption">Referencia Visual {{ $i }}</div>
                                </div>
                            </td>
                        @endfor
                    </tr>
                </table>
            </div>

            <div class="page-break">
                <div class="section-heading">
                    <span class="section-badge">02</span>
                    <span class="section-title-text">Documentación</span>
                </div>

                @php $docChunks = array_chunk($docFields, 3, true); @endphp
                <table class="grid-table">
                    @foreach($docChunks as $chunk)
                        <tr>
                            @foreach($chunk as $label => $key)
                                <td class="grid-cell">
                                    <span class="item-label">{{ $label }}</span>
                                    <span class="status-pill {{ $conditions->$key ? 'status-yes' : 'status-no' }}">
                                        {{ $conditions->$key ? 'REQUERIDO' : 'NO APLICA' }}
                                    </span>
                                </td>
                            @endforeach
                            @for($k = 0; $k < (3 - count($chunk)); $k++) <td width="33.33%"></td> @endfor
                        </tr>
                    @endforeach
                </table>

                <table class="gallery-table">
                    <tr>
                        @for($i=1; $i<=3; $i++)
                            <td class="gallery-cell">
                                <div class="photo-card">
                                    <div class="photo-frame">
                                        @if($url = $conditions->getImageUrl('doc_img_'.$i))
                                            <img src="{{ $url }}" class="photo-img">
                                        @else
                                            <div class="photo-placeholder">Espacio Disponible</div>
                                        @endif
                                    </div>
                                    <div class="photo-caption">Ejemplo Documento {{ $i }}</div>
                                </div>
                            </td>
                        @endfor
                    </tr>
                </table>
            </div>

            <div class="page-break">
                <div class="section-heading">
                    <span class="section-badge">03</span>
                    <span class="section-title-text">Evidencia de Entrega</span>
                </div>

                @php $evidChunks = array_chunk($evidFields, 3, true); @endphp
                <table class="grid-table">
                    @foreach($evidChunks as $chunk)
                        <tr>
                            @foreach($chunk as $label => $key)
                                <td class="grid-cell">
                                    <span class="item-label">{{ $label }}</span>
                                    <span class="status-pill {{ $conditions->$key ? 'status-yes' : 'status-no' }}">
                                        {{ $conditions->$key ? 'REQUERIDO' : 'NO APLICA' }}
                                    </span>
                                </td>
                            @endforeach
                            @for($k = 0; $k < (3 - count($chunk)); $k++) <td width="33.33%"></td> @endfor
                        </tr>
                    @endforeach
                </table>

                <table class="gallery-table">
                    <tr>
                        @for($i=1; $i<=3; $i++)
                            <td class="gallery-cell">
                                <div class="photo-card">
                                    <div class="photo-frame">
                                        @if($url = $conditions->getImageUrl('evid_img_'.$i))
                                            <img src="{{ $url }}" class="photo-img">
                                        @else
                                            <div class="photo-placeholder">Espacio Disponible</div>
                                        @endif
                                    </div>
                                    <div class="photo-caption">Ejemplo Evidencia {{ $i }}</div>
                                </div>
                            </td>
                        @endfor
                    </tr>
                </table>
            </div>

        </div>

        <div class="footer">
            <table width="100%">
                <tr>
                    <td width="70%">
                        <strong>Control Tower - Minmer Global</strong><br>
                        Este documento detalla los requisitos específicos para la correcta ejecución logística con el cliente mencionado.
                    </td>
                    <td width="30%" align="right" style="vertical-align: bottom;">
                        Generado el: {{ date('d/m/Y') }}
                    </td>
                </tr>
            </table>
        </div>
    </body> 
</html>