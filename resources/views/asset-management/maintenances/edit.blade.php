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
<div class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-8">
        <a href="{{ route('asset-management.maintenances.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; Volver al Dashboard de Mantenimientos</a>
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight mt-2">Completar Mantenimiento</h1>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-lg mt-8">
        <form action="{{ route('asset-management.maintenances.update', $maintenance) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div>
                    <label for="end_date" class="form-label">Fecha de Finalización</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $maintenance->end_date ? $maintenance->end_date->format('Y-m-d') : date('Y-m-d')) }}" class="form-input w-full" required>
                </div>
                <div>
                    <label for="actions_taken" class="form-label">Acciones Realizadas</label>
                    <textarea id="actions_taken" name="actions_taken" rows="4" class="form-textarea w-full" required>{{ old('actions_taken', $maintenance->actions_taken) }}</textarea>
                </div>
                <div>
                    <label for="parts_used" class="form-label">Insumos o Partes Utilizadas (Opcional)</label>
                    <textarea id="parts_used" name="parts_used" rows="3" class="form-textarea w-full">{{ old('parts_used', $maintenance->parts_used) }}</textarea>
                </div>
                <div>
                    <label for="cost" class="form-label">Costo Total (Opcional)</label>
                    <input type="number" id="cost" name="cost" step="0.01" value="{{ old('cost', $maintenance->cost) }}" class="form-input w-full">
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Evidencia Fotográfica (Opcional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @for ($i = 1; $i <= 3; $i++)
                            @php
                                $photoPath = "photo_{$i}_path";
                                $currentPhoto = $maintenance->$photoPath;
                                $photoUrl = $currentPhoto ? Storage::disk('s3')->url($currentPhoto) : null;
                            @endphp

                            <div x-data="{ 
                                    hasPhoto: {{ $currentPhoto ? 'true' : 'false' }}, 
                                    markedForDeletion: false 
                                }" 
                                class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                
                                <label class="form-label mb-2">Foto {{ $i }}</label>

                                <input type="hidden" name="remove_photo_{{ $i }}" x-model="markedForDeletion">

                                <div x-show="hasPhoto && !markedForDeletion" class="mb-3 relative">
                                    <img src="{{ $photoUrl }}" alt="Foto {{ $i }}" class="w-full h-32 object-cover rounded-lg border">
                                    <button type="button" 
                                            @click="markedForDeletion = true"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 focus:outline-none"
                                            title="Eliminar foto">
                                        <i class="fas fa-times text-xs px-1"></i>
                                    </button>
                                </div>

                                <div x-show="!hasPhoto || markedForDeletion">
                                    <input type="file" 
                                        name="photo_{{ $i }}" 
                                        accept="image/*"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors">
                                    <p x-show="markedForDeletion" class="text-xs text-red-500 mt-2 font-semibold">
                                        * La foto anterior será eliminada al guardar.
                                    </p>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            @if ($maintenance->substitute_asset_id)
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                    <strong>Atención:</strong> Al completar este mantenimiento, se registrará automáticamente la devolución del activo sustituto ({{ $maintenance->substituteAsset->asset_tag }}) que fue prestado.
                </div>
            @endif
            
            <div class="mt-8 pt-6 border-t flex justify-end">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection