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
    body { background-color: var(--color-background); }
    .form-label { font-weight: 600; color: var(--color-text-primary); margin-bottom: 0.5rem; display: block; }
    .form-input, .form-select, .form-textarea { border-radius: 0.5rem; border: 1px solid var(--color-border); transition: all 150ms ease-in-out; width: 100%; padding: 0.75rem 1rem; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { --tw-ring-color: var(--color-primary); border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--tw-ring-color); }
    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); transition: all 200ms ease-in-out; transform: translateY(0); border: 1px solid transparent; }
    .btn:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .btn-primary { background-color: var(--color-primary); color: white; }
    .btn-primary:hover { background-color: #212a41; }
    .btn-secondary { background-color: var(--color-surface); color: var(--color-text-secondary); border-color: var(--color-border); }
    .btn-secondary:hover { background-color: #f9fafb; }

    /* Estilos para el nuevo campo de carga de archivos */
    .file-upload-wrapper {
        border: 2px dashed var(--color-border);
        border-radius: 0.5rem;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: border-color 150ms ease-in-out, background-color 150ms ease-in-out;
    }
    .file-upload-wrapper:hover {
        border-color: var(--color-primary);
        background-color: #fcfcfd;
    }
    .file-upload-label {
        cursor: pointer;
        font-weight: 600;
        color: var(--color-primary);
    }
    .file-upload-label:hover {
        color: var(--color-accent);
    }
</style>

<div class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-8">
        <a href="{{ route('asset-management.user-dashboard.show', $assignment->member) }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Volver al usuario
        </a>
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Editar Registro de Asignación</h1>
        <div class="mt-2 p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm">
            <p class="text-[var(--color-text-secondary)]">Estás editando la asignación del activo:</p>
            <p class="font-semibold text-lg text-[var(--color-text-primary)] mt-1">{{ $assignment->asset->model->name }} ({{ $assignment->asset->asset_tag }})</p>
        </div>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-lg mt-8">
        <form action="{{ route('asset-management.assignments.update', $assignment) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="space-y-8">
                
                <fieldset>
                    <legend class="text-xl font-bold text-[var(--color-primary)] border-b border-gray-200 pb-2 mb-6 w-full">Detalles de la Asignación</legend>
                    <div class="space-y-6">
                        <div>
                            <label for="organigram_member_id" class="form-label">Asignado a:</label>
                            <select id="organigram_member_id" name="organigram_member_id" class="form-select w-full" required>
                                <option value="">-- Selecciona un miembro del equipo --</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" @selected(old('organigram_member_id', $assignment->organigram_member_id) == $member->id)>
                                        {{ $member->name }} - ({{ $member->position->name ?? 'Sin Puesto' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                            <div>
                                <label for="assignment_date" class="form-label">Fecha de Asignación:</label>
                                <input type="date" id="assignment_date" name="assignment_date" value="{{ old('assignment_date', $assignment->assignment_date->format('Y-m-d')) }}" class="form-input w-full" required>
                            </div>
                            <div>
                                <label for="actual_return_date" class="form-label">Fecha Real de Devolución:</label>
                                <input type="date" id="actual_return_date" name="actual_return_date" value="{{ old('actual_return_date', $assignment->actual_return_date ? $assignment->actual_return_date->format('Y-m-d') : null) }}" class="form-input w-full">
                            </div>
                        </div>
                         <div>
                            <label for="expected_return_date" class="form-label">Fecha Esperada Devolución (Solo Préstamo):</label>
                            <input type="date" id="expected_return_date" name="expected_return_date" value="{{ old('expected_return_date', $assignment->expected_return_date ? $assignment->expected_return_date->format('Y-m-d') : null) }}" class="form-input w-full">
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-xl font-bold text-[var(--color-primary)] border-b border-gray-200 pb-2 mb-6 w-full">Documentos (Responsivas)</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                        
                        <div x-data="{ fileName: '' }">
                            <label for="signed_receipt" class="form-label">Responsiva de Asignación</label>
                            @if($assignment->signed_receipt_path)
                            <div class="mb-2 text-sm">
                                <a href="{{ Storage::disk('s3')->url($assignment->signed_receipt_path) }}" target="_blank" class="font-medium text-green-600 hover:text-green-800 hover:underline">
                                    <i class="fas fa-file-pdf mr-1"></i> Ver responsiva actual
                                </a>
                            </div>
                            @endif
                            
                            <input type="file" id="signed_receipt" name="signed_receipt" class="hidden" accept=".pdf" @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                            <label for="signed_receipt" class="file-upload-wrapper">
                                <i class="fas fa-file-upload text-3xl text-gray-400"></i>
                                <span class="mt-2 text-sm text-gray-600">
                                    <span class="file-upload-label">Haz clic para</span> o arrastra el PDF
                                </span>
                                <p x-show="fileName" class="text-xs text-gray-500 mt-2" x-cloak>
                                    Seleccionado: <span x-text="fileName" class="font-semibold text-gray-700"></span>
                                </p>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Opcional: Sube un archivo para reemplazar el existente.</p>
                        </div>
                        
                        <div x-data="{ fileName: '' }">
                            <label for="return_receipt" class="form-label">Responsiva de Devolución</label>
                            @if($assignment->return_receipt_path)
                            <div class="mb-2 text-sm">
                                <a href="{{ Storage::disk('s3')->url($assignment->return_receipt_path) }}" target="_blank" class="font-medium text-green-600 hover:text-green-800 hover:underline">
                                    <i class="fas fa-file-pdf mr-1"></i> Ver responsiva actual
                                </a>
                            </div>
                            @endif

                            <input type="file" id="return_receipt" name="return_receipt" class="hidden" accept=".pdf" @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                             <label for="return_receipt" class="file-upload-wrapper">
                                <i class="fas fa-file-upload text-3xl text-gray-400"></i>
                                <span class="mt-2 text-sm text-gray-600">
                                    <span class="file-upload-label">Haz clic para</span> o arrastra el PDF
                                </span>
                                <p x-show="fileName" class="text-xs text-gray-500 mt-2" x-cloak>
                                    Seleccionado: <span x-text="fileName" class="font-semibold text-gray-700"></span>
                                </p>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">Opcional: Sube un archivo para reemplazar el existente.</p>
                        </div>
                    </div>
                </fieldset>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end items-center space-x-4">
                <a href="{{ route('asset-management.user-dashboard.show', $assignment->member) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> Actualizar Registro
                </button>
            </div>
        </form>
    </div>
</div>
@endsection