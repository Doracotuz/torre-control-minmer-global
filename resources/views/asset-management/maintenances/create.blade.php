@extends('layouts.app')

@section('content')

<style>
    :root {
        /* Paleta de colores completa y refinada */
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

<div class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Encabezado --}}
    <div class="mb-8">
        <a href="{{ route('asset-management.assets.show', $asset) }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Volver a los detalles del activo
        </a>
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Enviar Activo a Mantenimiento</h1>
        <div class="mt-2 p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm">
            <p class="text-[var(--color-text-secondary)]">Activo a registrar:</p>
            <p class="font-semibold text-lg text-[var(--color-text-primary)] mt-1">{{ $asset->model->name }}</p>
            <div class="flex space-x-4 mt-2 font-mono text-xs">
                <span>Etiqueta: <strong class="text-[var(--color-primary)]">{{ $asset->asset_tag }}</strong></span>
                <span>Estatus Actual: <strong class="text-[var(--color-accent)]">{{ $asset->status }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="bg-white p-8 rounded-xl shadow-lg mt-8">
        {{-- Usamos Alpine.js para la lógica condicional del campo 'sustituto' --}}
        <form action="{{ route('asset-management.maintenances.store', $asset) }}" method="POST"
              x-data="{ isAssigned: {{ $asset->status === 'Asignado' ? 'true' : 'false' }} }">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                
                <div>
                    <label for="type" class="form-label">Tipo de Mantenimiento</label>
                    <select id="type" name="type" class="form-select w-full" required>
                        <option value="Preventivo">Preventivo</option>
                        <option value="Reparación">Reparación</option>
                    </select>
                </div>
                
                <div>
                    <label for="start_date" class="form-label">Fecha de Inicio</label>
                    <input type="date" id="start_date" name="start_date" value="{{ date('Y-m-d') }}" class="form-input w-full" required>
                </div>

                <div class="md:col-span-2">
                    <label for="supplier" class="form-label">Proveedor o Técnico (Opcional)</label>
                    <input type="text" id="supplier" name="supplier" class="form-input w-full" placeholder="Ej: Soporte Técnico Interno, Proveedor Externo">
                </div>

                <div class="md:col-span-2">
                    <label for="diagnosis" class="form-label">Diagnóstico / Motivo de Envío</label>
                    <textarea id="diagnosis" name="diagnosis" rows="4" class="form-textarea w-full" required placeholder="Describe el problema o el motivo del mantenimiento preventivo..."></textarea>
                </div>

                {{-- CAMPO CONDICIONAL: Aparece solo si el activo está asignado --}}
                <div x-show="isAssigned" x-transition class="md:col-span-2 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <label for="substitute_asset_id" class="form-label">Activo Sustituto (Opcional)</label>
                    <p class="text-xs text-gray-600 mb-2">
                        El activo actual está asignado a <strong>{{ $asset->currentAssignment->member->name ?? '' }}</strong>.
                        Selecciona un equipo de almacén para asignárselo temporalmente.
                    </p>
                    <select id="substitute_asset_id" name="substitute_asset_id" class="form-select w-full">
                        <option value="">-- No asignar sustituto --</option>
                        @forelse($substituteAssets as $substitute)
                            <option value="{{ $substitute->id }}">
                                {{ $substitute->model->name }} ({{ $substitute->asset_tag }})
                            </option>
                        @empty
                             <option value="" disabled>No hay activos disponibles en almacén</option>
                        @endforelse
                    </select>
                </div>

            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end items-center space-x-4">
                <a href="{{ route('asset-management.assets.show', $asset) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Confirmar Envío a Mantenimiento</button>
            </div>
        </form>
    </div>
</div>
@endsection