@extends('layouts.app')

@section('content')

{{-- Estilos personalizados que usan tu paleta de colores y se aplican a las clases de Tailwind --}}
<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-secondary-text: #666666;
        --color-dark: #2b2b2b;
    }
    .btn-accent {
        background-color: var(--color-accent) !important;
        color: white !important;
        transition: background-color 0.3s ease;
    }
    .btn-accent:hover {
        background-color: #e68a00 !important; /* Un poco más oscuro para el hover */
    }
    .btn-primary-custom {
        background-color: var(--color-primary) !important;
        color: white !important;
        transition: background-color 0.3s ease;
    }
    .btn-primary-custom:hover {
        background-color: #212a40 !important;
    }
    .header-custom-primary {
        background-color: var(--color-primary);
        color: white;
    }
    .badge-status-programada { background-color: var(--color-primary); }
    .badge-status-ingresado { background-color: var(--color-accent); }
    .badge-status-no-ingresado { background-color: var(--color-secondary-text); }
    .badge-status-cancelada { background-color: var(--color-dark); }

    /* ===== ESTILOS PARA TABLA RESPONSIVA ===== */
    @media (max-width: 768px) {
        /* Esconder la cabecera de la tabla en móvil */
        .responsive-table thead {
            display: none;
        }
        /* Convertir cada fila en un bloque (tarjeta) */
        .responsive-table tbody, .responsive-table tr {
            display: block;
        }
        .responsive-table tr {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        /* Convertir cada celda en un bloque y alinear el contenido */
        .responsive-table td {
            display: block;
            text-align: right;
            padding-left: 50%; /* Espacio para la etiqueta */
            position: relative;
            border-bottom: 1px solid #eee;
        }
        .responsive-table td:last-child {
            border-bottom: none;
        }
        /* Crear la etiqueta usando el atributo data-label */
        .responsive-table td::before {
            content: attr(data-label);
            position: absolute;
            left: 1rem; /* Margen izquierdo para la etiqueta */
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            text-align: left;
            font-weight: 600;
            color: var(--color-primary);
        }
        /* Estilo especial para la celda de acciones */
        .responsive-table .actions-cell {
            padding: 1rem;
            text-align: center;
        }
        .responsive-table .actions-cell::before {
            display: none; /* No se necesita etiqueta para las acciones */
        }
    }
</style>

{{-- Comienzo del Contenido de la Página --}}
<div class="w-full">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-3xl font-bold text-[#2c3856]">Historial de Visitas</h1>
        <a href="{{ route('area_admin.visits.create') }}" class="btn-accent inline-flex items-center justify-center px-4 py-2 mt-4 sm:mt-0 rounded-md font-semibold text-xs shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2">
            <i class="fas fa-plus mr-2"></i>
            Crear Registro de Visita
        </a>
    </div>

    {{-- Filtros (sin cambios) --}}
    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="header-custom-primary p-4 rounded-t-lg">
            <h6 class="font-bold text-white">🔍 Filtros de Búsqueda</h6>
        </div>
        <div class="p-6">
            <form action="{{ route('area_admin.visits.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <input type="text" class="col-span-1 sm:col-span-2 md:col-span-1 rounded-md border-gray-300 shadow-sm" name="search" placeholder="Nombre, correo, empresa..." value="{{ $filters['search'] ?? '' }}">
                <input type="date" class="col-span-1 rounded-md border-gray-300 shadow-sm" name="start_date" title="Fecha de inicio" value="{{ $filters['start_date'] ?? '' }}">
                <input type="date" class="col-span-1 rounded-md border-gray-300 shadow-sm" name="end_date" title="Fecha de fin" value="{{ $filters['end_date'] ?? '' }}">
                <select name="status" class="col-span-1 rounded-md border-gray-300 shadow-sm">
                    <option value="">-- Todos los Estatus --</option>
                    <option value="Programada" {{ ($filters['status'] ?? '') == 'Programada' ? 'selected' : '' }}>Programada</option>
                    <option value="Ingresado" {{ ($filters['status'] ?? '') == 'Ingresado' ? 'selected' : '' }}>Ingresado</option>
                    <option value="No ingresado" {{ ($filters['status'] ?? '') == 'No ingresado' ? 'selected' : '' }}>No Ingresado</option>
                    <option value="Cancelada" {{ ($filters['status'] ?? '') == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
                <div class="col-span-1 flex items-center space-x-2">
                    <button type="submit" class="w-full btn-primary-custom px-4 py-2 rounded-md font-semibold text-xs shadow-sm">Filtrar</button>
                    <a href="{{ route('area_admin.visits.index') }}" class="w-full text-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md font-semibold text-xs">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de Visitas --}}
    <div class="bg-white rounded-lg shadow-md">
        <div class="overflow-x-auto">
            {{-- Añadida la clase "responsive-table" para aplicar los nuevos estilos --}}
            <table class="w-full text-sm text-left text-gray-700 responsive-table">
                <thead class="text-xs text-white uppercase header-custom-primary">
                    <tr>
                        <th scope="col" class="px-6 py-3">Visitante</th>
                        <th scope="col" class="px-6 py-3">Correo</th>
                        <th scope="col" class="px-6 py-3">Empresa</th>
                        <th scope="col" class="px-6 py-3">Fecha y Hora</th>
                        <th scope="col" class="px-6 py-3">Placa</th>
                        <th scope="col" class="px-6 py-3">Estatus</th>
                        <th scope="col" class="px-6 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visits as $visit)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            {{-- Atributos data-label añadidos a cada celda --}}
                            <td data-label="Visitante" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $visit->visitor_name }} {{ $visit->visitor_last_name }}</td>
                            <td data-label="Correo" class="px-6 py-4">{{ $visit->email }}</td>
                            <td data-label="Empresa" class="px-6 py-4">{{ $visit->company ?? 'N/A' }}</td>
                            <td data-label="Fecha y Hora" class="px-6 py-4">{{ $visit->visit_datetime->format('d/m/Y h:i A') }}</td>
                            <td data-label="Placa" class="px-6 py-4">{{ $visit->license_plate ?? 'N/A' }}</td>
                            <td data-label="Estatus" class="px-6 py-4">
                                @php
                                    $statusClass = '';
                                    if ($visit->status == 'Ingresado') $statusClass = 'badge-status-ingresado';
                                    elseif ($visit->status == 'Programada') $statusClass = 'badge-status-programada';
                                    elseif ($visit->status == 'No ingresado') $statusClass = 'badge-status-no-ingresado';
                                    elseif ($visit->status == 'Cancelada') $statusClass = 'badge-status-cancelada';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white {{ $statusClass }}">
                                    {{ $visit->status }}
                                </span>
                            </td>
                            {{-- Clase especial para la celda de acciones --}}
                            <td class="px-6 py-4 actions-cell">
                                <form action="{{ route('area_admin.visits.destroy', $visit) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta visita? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold text-lg" title="Eliminar Visita">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                No se encontraron visitas con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-6">
            {!! $visits->appends(request()->query())->links() !!}
        </div>
    </div>
</div>
@endsection