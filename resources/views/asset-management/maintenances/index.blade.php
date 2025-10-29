@extends('layouts.app')

@section('content')
<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        --color-background: #f3f4f6;
        --color-border: #d1d5db;
    }
    body { 
        background-color: var(--color-background); 
    }
    .form-label { font-weight: 600; color: var(--color-text-primary); margin-bottom: 0.5rem; display: block; }
    .form-input, .form-select, .form-textarea { 
        border-radius: 0.5rem; 
        border: 1px solid var(--color-border); 
        transition: all 150ms ease-in-out; 
        width: 100%; 
        padding: 0.75rem 1rem; 
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus { 
        --tw-ring-color: var(--color-primary); 
        border-color: var(--color-primary); 
        box-shadow: 0 0 0 2px var(--tw-ring-color); 
    }
    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; transition: all 200ms ease-in-out; border: 1px solid transparent; }
    .btn:hover { transform: translateY(-2px); }
    .btn-primary { background-color: var(--color-primary); color: white; }
    .btn-primary:hover { background-color: #212a41; }
    .btn-secondary { background-color: var(--color-surface); color: var(--color-text-secondary); border-color: var(--color-border); }
    .btn-secondary:hover { background-color: #f9fafb; }
</style>
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <header class="mb-8">
        <a href="{{ route('asset-management.dashboard') }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
        </a>        
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Dashboard de Mantenimientos</h1>
        <p class="text-[var(--color-text-secondary)] mt-2">Seguimiento de todos los mantenimientos preventivos y reparaciones.</p>
    </header>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th class="p-4 font-semibold text-left text-gray-600 uppercase">Activo</th>
                        <th class="p-4 font-semibold text-left text-gray-600 uppercase">Tipo</th>
                        <th class="p-4 font-semibold text-left text-gray-600 uppercase">Estado</th>
                        <th class="p-4 font-semibold text-left text-gray-600 uppercase">Fecha Inicio</th>
                        <th class="p-4 font-semibold text-left text-gray-600 uppercase">Fecha Fin</th>
                        <th class="p-4 font-semibold text-right text-gray-600 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($maintenances as $maintenance)
                        <tr class="hover:bg-gray-50">
                            <td class="p-4 font-semibold">
                                <a href="{{ route('asset-management.assets.show', $maintenance->asset) }}" class="text-[var(--color-primary)] hover:underline">
                                    {{ $maintenance->asset->model->name }} ({{ $maintenance->asset->asset_tag }})
                                </a>
                            </td>
                            <td class="p-4 text-gray-600">{{ $maintenance->type }}</td>
                            <td class="p-4">
                                @if($maintenance->end_date)
                                    <span class="px-2 py-1 text-xs font-bold text-green-800 bg-green-100 rounded-full">Completado</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-bold text-yellow-800 bg-yellow-100 rounded-full">En Proceso</span>
                                @endif
                            </td>
                            <td class="p-4 text-gray-600">{{ $maintenance->start_date->format('d/m/Y') }}</td>
                            <td class="p-4 text-gray-600">{{ $maintenance->end_date ? $maintenance->end_date->format('d/m/Y') : '---' }}</td>
                            <td class="p-4">
                                <div class="flex items-center justify-end space-x-4">
                                    @if($maintenance->end_date)
                                        <a href="{{ route('asset-management.maintenances.pdf', $maintenance) }}" target="_blank" class="text-gray-500 hover:text-red-600" title="Ver Certificado PDF"><i class="fas fa-file-pdf"></i></a>
                                    @else
                                        <a href="{{ route('asset-management.maintenances.edit', $maintenance) }}" class="btn btn-sm btn-primary py-1 px-3 text-xs">Completar</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center p-12 text-gray-500">No hay registros de mantenimiento.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t">
            {!! $maintenances->links() !!}
        </div>
    </div>
</div>
@endsection