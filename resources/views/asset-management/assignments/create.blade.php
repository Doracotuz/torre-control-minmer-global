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
    body { background-color: var(--color-background); }
    .form-input, .form-select, .form-textarea { border-radius: 0.5rem; border-color: #d1d5db; transition: all 150ms ease-in-out; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { --tw-ring-color: var(--color-primary); border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--tw-ring-color); }
    label.form-label { font-weight: 600; color: var(--color-text-primary); margin-bottom: 0.5rem; display: block; }
    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); transition: all 200ms ease-in-out; transform: translateY(0); border: 1px solid transparent; }
    .btn:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .btn-primary { background-color: var(--color-primary); color: white; }
    .btn-primary:hover { background-color: var(--color-primary-dark); }
    .btn-secondary { background-color: var(--color-surface); color: var(--color-text-secondary); border-color: #d1d5db; }
    .btn-secondary:hover { background-color: #f9fafb; }
    .status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-en-almacen { background-color: #10B981; color: white; }
    .status-asignado { background-color: #3B82F6; color: white; }
    .status-en-reparacion { background-color: var(--color-accent); color: white; }
    .status-prestado { background-color: #8B5CF6; color: white; }
    .status-de-baja { background-color: var(--color-text-secondary); color: white; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .kpi-card { animation: fadeIn 0.5s ease-out forwards; }
    .table-container { overflow: hidden; border-radius: 0.75rem; box-shadow: var(--shadow-md); }
    .table-header th { background-color: #f9fafb; color: var(--color-text-secondary); text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; }
    .table-row:hover { background-color: #eff6ff; }
</style>

<div class="w-full max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- Encabezado --}}
    <div class="mb-8">
        <a href="{{ route('asset-management.assets.show', $asset) }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Volver a los detalles del activo
        </a>
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Asignar Activo</h1>
        <div class="mt-2 p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm">
            <p class="text-[var(--color-text-secondary)]">Estás asignando el activo:</p>
            <p class="font-semibold text-lg text-[var(--color-text-primary)] mt-1">{{ $asset->model->name }}</p>
            <div class="flex space-x-4 mt-2 font-mono text-xs">
                <span>Etiqueta: <strong class="text-[var(--color-primary)]">{{ $asset->asset_tag }}</strong></span>
                <span>Serie: <strong class="text-[var(--color-primary)]">{{ $asset->serial_number }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <div class="bg-white p-8 rounded-xl shadow-lg mt-8">
        <form action="{{ route('asset-management.assignments.store', $asset) }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="organigram_member_id" class="form-label">Asignar a:</label>
                    <select id="organigram_member_id" name="organigram_member_id" class="form-select w-full" required>
                        <option value="">-- Selecciona un miembro del equipo --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }} - ({{ $member->position->name ?? 'Sin Puesto' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="assignment_date" class="form-label">Fecha de Asignación:</label>
                    <input type="date" id="assignment_date" name="assignment_date" value="{{ date('Y-m-d') }}" class="form-input w-full" required>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end items-center space-x-4">
                <a href="{{ route('asset-management.assets.show', $asset) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Confirmar Asignación</button>
            </div>
        </form>
    </div>
</div>
@endsection