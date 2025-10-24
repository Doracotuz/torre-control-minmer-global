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
    .file-upload-wrapper { border: 2px dashed var(--color-border); border-radius: 0.5rem; padding: 2.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: all 150ms ease-in-out; }
    .file-upload-wrapper.dragover { border-color: var(--color-primary); background-color: #fcfcfd; }
    .file-upload-label { cursor: pointer; font-weight: 600; color: var(--color-primary); }
    .file-upload-label:hover { color: var(--color-accent); }
</style>

<div class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-8">
        <a href="{{ route('asset-management.dashboard') }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard de Activos
        </a>
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Asignación Masiva de Activos</h1>
        <p class="mt-2 text-lg text-[var(--color-text-secondary)]">
            Sube un archivo CSV para asignar múltiples activos a miembros del equipo en un solo paso.
        </p>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 shadow" role="alert">
            <h3 class="font-bold text-lg mb-2">Error en la importación</h3>
            <div class="text-sm">{!! session('error') !!}</div>
        </div>
    @endif
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6 shadow" role="alert">
            <h3 class="font-bold text-lg">¡Éxito!</h3>
            <p class="text-sm">{!! session('success') !!}</p>
        </div>
    @endif


    <div class="bg-white p-8 rounded-xl shadow-lg mt-8">
        <form action="{{ route('asset-management.assignments.import.store') }}" method="POST" enctype="multipart/form-data" 
              x-data="{ dragging: false, fileName: '' }">
            @csrf
            
            <div class="space-y-8">
                
                <fieldset>
                    <legend class="text-xl font-bold text-[var(--color-primary)] border-b border-gray-200 pb-2 mb-6 w-full">Instrucciones</legend>
                    <div class="space-y-4 text-sm text-[var(--color-text-primary)]">
                        <p>
                            1. Descarga la plantilla CSV para asegurarte de que el formato es correcto.
                        </p>
                        <div class="pl-4">
                            <a href="{{ route('asset-management.assignments.import.template') }}" class="btn btn-secondary">
                                <i class="fas fa-download mr-2"></i> Descargar Plantilla
                            </a>
                        </div>
                        <p>2. Llena la plantilla con los siguientes datos:</p>
                        <ul class="list-disc list-inside pl-4 space-y-1">
                            <li><strong class="font-semibold">asset_tag:</strong> El identificador único del activo (Ej: `ACT-001`). El activo debe tener estatus "En Almacén".</li>
                            <li><strong class="font-semibold">organigram_member_email:</strong> El email del miembro del organigrama al que se asignará.</li>
                            <li><strong class="font-semibold">assignment_date:</strong> La fecha de asignación en formato `YYYY-MM-DD`.</li>
                        </ul>
                        <p>
                            3. Sube el archivo CSV completo usando el formulario de abajo.
                        </p>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-xl font-bold text-[var(--color-primary)] border-b border-gray-200 pb-2 mb-6 w-full">Cargar Archivo</legend>
                    
                    <div 
                        class="file-upload-wrapper" 
                        :class="{ 'dragover': dragging }"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $refs.fileInput.files[0] ? $refs.fileInput.files[0].name : '';"
                    >
                        <input 
                            type="file" 
                            id="csv_file" 
                            name="csv_file" 
                            class="hidden" 
                            accept=".csv, text/csv" 
                            x-ref="fileInput"
                            @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                            required
                        >
                        <label for="csv_file" class="text-center">
                            <i class="fas fa-file-csv text-5xl text-gray-400"></i>
                            <span class="mt-4 text-sm text-gray-600 block">
                                <span class="file-upload-label">Haz clic para</span> o arrastra el archivo CSV aquí
                            </span>
                        </label>
                        <p x-show="fileName" class="text-sm text-gray-500 mt-3" x-cloak>
                            Archivo seleccionado: <span x-text="fileName" class="font-semibold text-gray-700"></span>
                        </p>
                    </div>
                    @error('csv_file')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </fieldset>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end items-center space-x-4">
                <a href="{{ route('asset-management.dashboard') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload mr-2"></i> Procesar Importación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection