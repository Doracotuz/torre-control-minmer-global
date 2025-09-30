@extends('layouts.app')

@section('content')
<style>
    :root {
        /* Tu paleta de colores */
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        
        /* Colores de apoyo */
        --color-primary-dark: #212a41; /* Versión oscurecida para hover */
        --color-background: #f3f4f6;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }

    body {
        background-color: var(--color-background);
    }

    /* Estilos para formularios (Inputs, Selects, Textareas) */
    .form-input, .form-select, .form-textarea {
        border-radius: 0.5rem;
        border-color: #d1d5db;
        transition: all 150ms ease-in-out;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        --tw-ring-color: var(--color-primary);
        border-color: var(--color-primary);
        box-shadow: 0 0 0 2px var(--tw-ring-color);
    }
    label.form-label {
        font-weight: 600;
        color: var(--color-text-primary);
        margin-bottom: 0.5rem;
        display: block;
    }
    
    /* Botones */
    .btn {
        padding: 0.65rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-sm);
        transition: all 200ms ease-in-out;
        transform: translateY(0);
    }
    .btn:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    .btn-primary {
        background-color: var(--color-primary);
        color: white;
    }
    .btn-primary:hover {
        background-color: var(--color-primary-dark);
    }
    .btn-secondary {
        background-color: var(--color-surface);
        color: var(--color-text-secondary);
        border: 1px solid #d1d5db;
    }
    .btn-secondary:hover {
        background-color: #f9fafb;
    }

    /* Badges de Estado */
    .status-badge { 
        padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; 
        font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .status-asignado { background-color: #3B82F6; color: white; }
    .status-en-almacen { background-color: #10B981; color: white; }
    .status-en-reparacion { background-color: var(--color-accent); color: white; }
    .status-prestado { background-color: #8B5CF6; color: white; }
    .status-de-baja { background-color: var(--color-text-secondary); color: white; }
</style>
<div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Encabezado --}}
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <a href="{{ route('asset-management.dashboard') }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
            </a>
            <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Gestionar Licencias de Software</h1>
            <p class="text-[var(--color-text-secondary)] mt-1">Administra todas las licencias de software de la organización.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('asset-management.software-licenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Añadir Licencia
            </a>
        </div>
    </header>

    {{-- Contenedor de la Tabla --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="table-header" style="background-color: #f8f9fa;">
                    <tr>
                        <th class="p-4 font-semibold text-left text-gray-600 uppercase">Nombre del Software</th>
                        <th class="p-4 font-semibold text-left text-gray-600 uppercase">Licencias Usadas</th>
                        <th class="p-4 font-semibold text-left text-gray-600 uppercase">Fecha de Compra</th>
                        <th class="p-4 font-semibold text-left text-gray-600 uppercase">Fecha de Vencimiento</th>
                        <th class="p-4 font-semibold text-right text-gray-600 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($licenses as $license)
                        <tr class="table-row transition-colors hover:bg-gray-50">
                            <td class="p-4 font-semibold text-[var(--color-text-primary)]">{{ $license->name }}</td>
                            <td class="p-4">
                                <div class="flex items-center">
                                    <span class="mr-3 font-mono text-sm">{{ $license->assignments_count }} / {{ $license->total_seats }}</span>
                                    <div class="w-24 bg-gray-200 rounded-full h-2.5">
                                        @php
                                            $percentage = ($license->total_seats > 0) ? ($license->assignments_count / $license->total_seats) * 100 : 0;
                                        @endphp
                                        <div class="bg-[var(--color-primary)] h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-gray-600">{{ $license->purchase_date ? $license->purchase_date->format('d/m/Y') : 'N/A' }}</td>
                            <td class="p-4 text-gray-600">{{ $license->expiry_date ? $license->expiry_date->format('d/m/Y') : 'No vence' }}</td>
                            <td class="p-4 text-right">
                                <a href="{{ route('asset-management.software-licenses.edit', $license) }}" class="text-gray-500 hover:text-[var(--color-primary)] transition-colors" title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-12">
                                <i class="fas fa-id-card text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500">No hay licencias de software registradas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($licenses->hasPages())
        <div class="p-4 bg-gray-50 border-t">
            {!! $licenses->links() !!}
        </div>
        @endif
    </div>
</div>
@endsection