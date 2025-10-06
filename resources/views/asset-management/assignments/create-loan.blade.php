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

    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); transition: all 200ms ease-in-out; transform: translateY(0); border: 1px solid transparent; }
    .btn:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .btn-primary { background-color: var(--color-primary); color: white; }
    .btn-primary:hover { background-color: #212a41; }
    .btn-secondary { background-color: var(--color-surface); color: var(--color-text-secondary); border-color: var(--color-border); }
    .btn-secondary:hover { background-color: #f9fafb; }
</style>

<div class="w-full max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-8">
        <a href="{{ route('asset-management.assets.show', $asset) }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Volver a los detalles del activo
        </a>
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Registrar Préstamo de Activo</h1>
        <div class="mt-2 p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm">
            <p class="text-[var(--color-text-secondary)]">Estás prestando el activo:</p>
            <p class="font-semibold text-lg text-[var(--color-text-primary)] mt-1">{{ $asset->model->name }}</p>
            <div class="flex space-x-4 mt-2 font-mono text-xs">
                <span>Etiqueta: <strong class="text-[var(--color-primary)]">{{ $asset->asset_tag }}</strong></span>
                <span>Serie: <strong class="text-[var(--color-primary)]">{{ $asset->serial_number }}</strong></span>
            </div>
        </div>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-lg mt-8">
        <form action="{{ route('asset-management.assignments.storeLoan', $asset) }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="organigram_member_id" class="form-label">Prestar a:</label>
                    <select id="organigram_member_id" name="organigram_member_id" class="form-select w-full" required>
                        <option value="">-- Selecciona un miembro del equipo --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }} - ({{ $member->position->name ?? 'Sin Puesto' }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
                    <div>
                        <label for="assignment_date" class="form-label">Fecha de Préstamo:</label>
                        <input type="date" id="assignment_date" name="assignment_date" value="{{ date('Y-m-d') }}" class="form-input w-full" required>
                    </div>
                    <div>
                        <label for="expected_return_date" class="form-label">Fecha de Devolución Esperada:</label>
                        <input type="date" id="expected_return_date" name="expected_return_date" class="form-input w-full" required>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end items-center space-x-4">
                <a href="{{ route('asset-management.assets.show', $asset) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Confirmar Préstamo</button>
            </div>
        </form>
    </div>
</div>
@endsection