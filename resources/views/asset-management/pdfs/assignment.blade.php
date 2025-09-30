<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta Responsiva de Activos</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header img { max-width: 150px; }
        .header h1 { margin: 0; font-size: 18px; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .legal { font-size: 10px; text-align: justify; }
        .signatures { margin-top: 60px; }
        .signature-box { display: inline-block; width: 45%; text-align: center; }
        .signature-line { border-top: 1px solid #333; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Logo">
        @endif
        <h1>Carta Responsiva de Activos de TI</h1>
    </div>

    <div class="section">
        <p style="text-align: right;">Fecha: {{ date('d/m/Y') }}</p>
        <p>
            Por medio de la presente, yo, <strong>{{ $assignment->member->name }}</strong>,
            con puesto de <strong>{{ $assignment->member->position->name ?? 'No especificado' }}</strong>,
            confirmo la recepción del siguiente equipo y software propiedad de la empresa, los cuales se encuentran en óptimas
            condiciones y de los cuales seré responsable a partir de la fecha de firma.
        </p>
    </div>

    <div class="section">
        <h2>Detalles del Activo Asignado</h2>
        <table>
            <tr>
                <th style="width: 30%;">Etiqueta de Activo</th>
                <td>{{ $assignment->asset->asset_tag }}</td>
            </tr>
            <tr>
                <th>Categoría</th>
                <td>{{ $assignment->asset->model->category->name }}</td>
            </tr>
            <tr>
                <th>Modelo</th>
                <td>{{ $assignment->asset->model->manufacturer->name }} {{ $assignment->asset->model->name }}</td>
            </tr>
            <tr>
                <th>Número de Serie</th>
                <td>{{ $assignment->asset->serial_number }}</td>
            </tr>
            
            {{-- SECCIÓN ACTUALIZADA PARA ESPECIFICACIONES --}}
            @if($assignment->asset->model->category->name === 'Laptop' || $assignment->asset->model->category->name === 'Desktop')
            <tr>
                <th>Especificaciones</th>
                <td>
                    CPU: {{ $assignment->asset->cpu ?? 'N/A' }}, 
                    RAM: {{ $assignment->asset->ram ?? 'N/A' }}, 
                    Almacenamiento: {{ $assignment->asset->storage ?? 'N/A' }}
                </td>
            </tr>
            @endif

            {{-- NUEVA SECCIÓN PARA DETALLES DE CELULAR --}}
            @if($assignment->asset->model->category->name === 'Celular')
            <tr>
                <th>Plan de Telefonía</th>
                <td>{{ $assignment->asset->phone_plan_type ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Número Telefónico</th>
                <td>{{ $assignment->asset->phone_number ?? 'N/A' }}</td>
            </tr>
            @endif
        </table>

        {{-- NUEVA SECCIÓN PARA SOFTWARE --}}
        @if($assignment->asset->softwareAssignments->isNotEmpty())
            <h2>Software y Licencias Asignadas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre del Software</th>
                        <th>Fecha de Instalación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignment->asset->softwareAssignments as $software)
                        <tr>
                            <td>{{ $software->license->name }}</td>
                            <td>{{ date('d/m/Y', strtotime($software->install_date)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="section">
        <h2>Cláusulas de Responsabilidad</h2>
        <p class="legal">
            1. El equipo y el software son propiedad exclusiva de la empresa y se me entregan únicamente para el desempeño de mis funciones laborales.
            2. Me comprometo a cuidar el equipo, mantenerlo en buen estado y utilizarlo de acuerdo con las políticas de seguridad de la información de la empresa.
            3. En caso de daño por negligencia, mal uso, robo o extravío, me haré responsable de los costos de reparación o reposición correspondientes.
            4. Me comprometo a devolver el equipo en las mismas condiciones en que lo recibí (considerando el desgaste normal por uso) al término de mi relación laboral con la empresa o cuando esta me lo solicite.
        </p>
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line"></div>
            <p>Firma de Recibido<br>{{ $assignment->member->name }}</p>
        </div>
        <div class="signature-box" style="float: right;">
            <div class="signature-line"></div>
            <p>Firma de Entregado<br>Departamento de TI</p>
        </div>
    </div>
</body>
</html>