@extends('layouts.app')

@section('content')
<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-primary-dark: #212a41;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        --color-background: #f3f4f6;
        --color-border: #d1d5db;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }
    body { background-color: var(--color-background); }

    /* --- ESTILOS DEL DASHBOARD --- */

    /* Tarjetas de Estadísticas */
    .stats-card {
        background-color: var(--color-surface);
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .stats-card-icon {
        flex-shrink: 0;
        width: 3rem;
        height: 3rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .stats-card-icon.primary { background-color: #e0e7ff; color: #4338ca; }
    .stats-card-icon.secondary { background-color: #e5e7eb; color: #4b5563; }
    .stats-card-icon.tertiary { background-color: #fee2e2; color: #b91c1c; }
    .stats-card-content {
        line-height: 1.2;
    }
    .stats-card-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--color-text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .stats-card-value {
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--color-text-primary);
    }

    /* Pestañas (Tabs) */
    .tab-nav {
        display: flex;
        border-bottom: 2px solid var(--color-border);
        margin-bottom: 1.5rem;
    }
    .tab-button {
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        color: var(--color-text-secondary);
        border-bottom: 3px solid transparent;
        transform: translateY(2px);
        transition: all 150ms ease-in-out;
    }
    .tab-button:hover {
        color: var(--color-text-primary);
    }
    .tab-button.active {
        color: var(--color-primary);
        border-color: var(--color-primary);
    }

    /* Tarjetas de Activos (Grid) */
    .asset-card {
        background-color: var(--color-surface);
        border-radius: 0.75rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--color-border);
        overflow: hidden;
        transition: all 200ms ease-in-out;
        display: flex;
        flex-direction: column;
    }
    .asset-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-4px);
    }
    .asset-card-header {
        background-color: #f9fafb;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .asset-card-icon {
        font-size: 1.5rem;
        color: var(--color-primary);
    }
    .asset-card-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--color-text-primary);
    }
    .asset-card-content {
        padding: 1.5rem;
        flex-grow: 1;
    }
    .asset-card-footer {
        padding: 1rem 1.5rem;
        background-color: #f9fafb;
        border-top: 1px solid var(--color-border);
    }

    /* Línea de Tiempo (Historial) */
    .timeline {
        position: relative;
        padding-left: 2.5rem;
        border-left: 3px solid var(--color-border);
    }
    .timeline-item {
        position: relative;
        margin-bottom: 2rem;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-icon {
        position: absolute;
        left: -2.5rem;
        top: 0;
        transform: translateX(-50%);
        width: 2.5rem;
        height: 2.5rem;
        background-color: var(--color-surface);
        border: 3px solid var(--color-border);
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-text-secondary);
    }
    .timeline-content {
        background-color: var(--color-surface);
        border-radius: 0.5rem;
        padding: 1.5rem;
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-sm);
    }
    .timeline-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--color-text-primary);
    }
    .timeline-dates {
        font-size: 0.875rem;
        color: var(--color-text-secondary);
        font-weight: 500;
    }
    
    /* Lista de Documentos */
    .document-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-radius: 0.5rem;
        transition: background-color 150ms ease;
    }
    .document-list-item:hover {
        background-color: #f9fafb;
    }
    .document-icon {
        color: #ef4444; /* Rojo PDF */
        font-size: 1.5rem;
        margin-right: 1rem;
    }

    /* Botones */
    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); transition: all 200ms ease-in-out; transform: translateY(0); border: 1px solid transparent; }
    .btn:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .btn-primary { background-color: var(--color-primary); color: white; }
    .btn-primary:hover { background-color: var(--color-primary-dark); }
    .btn-secondary { background-color: var(--color-surface); color: var(--color-text-secondary); border-color: var(--color-border); }
    .btn-secondary:hover { background-color: #f9fafb; }
    .btn-accent { background-color: var(--color-accent); color: white; }
    .btn-accent:hover { background-color: #ffb433; }
</style>

<div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <header class="mb-8">
        <a href="{{ route('asset-management.user-dashboard.index') }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Usuarios
        </a>
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">{{ $member->name }}</h1>
        <p class="text-xl text-[var(--color-text-secondary)] mt-1">{{ $member->position->name ?? 'Sin Puesto' }}</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="stats-card">
            <div class="stats-card-icon primary"><i class="fas fa-desktop"></i></div>
            <div class="stats-card-content">
                <div class="stats-card-title">Activos Actuales</div>
                <div class="stats-card-value">{{ $currentAssignments->count() }}</div>
            </div>
        </div>
        <div class="stats-card">
            <div class="stats-card-icon secondary"><i class="fas fa-history"></i></div>
            <div class="stats-card-content">
                <div class="stats-card-title">Histórico</div>
                <div class="stats-card-value">{{ $assignmentHistory->count() }}</div>
            </div>
        </div>
        <div class="stats-card">
            <div class="stats-card-icon tertiary"><i class="fas fa-file-contract"></i></div>
            <div class="stats-card-content">
                <div class="stats-card-title">Documentos</div>
                <div class="stats-card-value">{{ $responsivas->count() }}</div>
            </div>
        </div>
    </div>

    <div x-data="{ activeTab: 'current' }" class="w-full">
        <nav class="tab-nav">
            <button type="button" class="tab-button" :class="{ 'active': activeTab === 'current' }" @click="activeTab = 'current'">
                <i class="fas fa-laptop-house mr-2"></i> Activos Actuales
            </button>
            <button type="button" class="tab-button" :class="{ 'active': activeTab === 'history' }" @click="activeTab = 'history'">
                <i class="fas fa-archive mr-2"></i> Historial de Devoluciones
            </button>
            <button type="button" class="tab-button" :class="{ 'active': activeTab === 'documents' }" @click="activeTab = 'documents'">
                <i class="fas fa-file-pdf mr-2"></i> Documentos (Responsivas)
            </button>
        </nav>

        <div class="py-6">

            <div x-show="activeTab === 'current'" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($currentAssignments as $assignment)
                        @php
                            $category = $assignment->asset->model->category->name ?? 'Default';
                            $icon = 'fa-laptop'; // Icono por defecto
                            if (Str::contains($category, 'Celular')) $icon = 'fa-mobile-alt';
                            if (Str::contains($category, 'Monitor')) $icon = 'fa-tv';
                            if (Str::contains($category, 'Impresora')) $icon = 'fa-print';
                        @endphp
                        <div class="asset-card">
                            <div class="asset-card-header">
                                <i class="fas {{ $icon }} asset-card-icon"></i>
                                <div>
                                    <h3 class="asset-card-title">{{ $assignment->asset->model->name }}</h3>
                                    <p class="text-sm font-mono text-[var(--color-primary)]">{{ $assignment->asset->asset_tag }}</p>
                                </div>
                            </div>
                            <div class="asset-card-content space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Categoría:</span>
                                    <span class="font-semibold text-gray-700">{{ $category }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Fabricante:</span>
                                    <span class="font-semibold text-gray-700">{{ $assignment->asset->model->manufacturer->name }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">No. Serie:</span>
                                    <span class="font-semibold text-gray-700 font-mono">{{ $assignment->asset->serial_number }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Asignado:</span>
                                    <span class="font-semibold text-gray-700">{{ $assignment->assignment_date->format('d/m/Y') }}</span>
                                </div>
                            </div>
                            <div class="asset-card-footer">
                                <a href="{{ route('asset-management.assets.show', $assignment->asset) }}" class="btn btn-secondary w-full">
                                    Ver Detalles del Activo <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-2 lg:col-span-3 text-center py-16 bg-white rounded-lg shadow-sm border">
                            <i class="fas fa-inbox text-5xl text-gray-300"></i>
                            <h3 class="mt-4 text-xl font-bold text-gray-700">Sin Activos</h3>
                            <p class="text-gray-500 mt-1">Este usuario no tiene ningún activo asignado actualmente.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div x-show="activeTab === 'history'" x-transition>
                <div class="timeline">
                    @forelse($assignmentHistory as $assignment)
                        <div class="timeline-item">
                            <div class="timeline-icon"><i class="fas fa-archive"></i></div>
                            <div class="timeline-content">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="timeline-title">{{ $assignment->asset->model->name }}</h3>
                                        <p class="font-mono text-sm text-[var(--color-primary)]">{{ $assignment->asset->asset_tag }}</p>
                                    </div>
                                    <a href="{{ route('asset-management.assignments.edit', $assignment) }}" class="btn btn-secondary text-sm py-2 px-4">
                                        <i class="fas fa-pen mr-2"></i> Editar
                                    </a>
                                </div>
                                <div class="timeline-dates mt-3 pt-3 border-t">
                                    <span class="font-bold text-green-600">Asignado:</span> {{ $assignment->assignment_date->format('d/m/Y') }}
                                    <span class="mx-2">|</span>
                                    <span class="font-bold text-red-600">Devuelto:</span> {{ $assignment->actual_return_date->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="timeline-item">
                            <div class="timeline-icon"><i class="fas fa-inbox"></i></div>
                            <div class="timeline-content text-center">
                                <h3 class="timeline-title text-gray-700">Historial Vacío</h3>
                                <p class="text-gray-500 mt-1">Este usuario no tiene un historial de devoluciones.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div x-show="activeTab === 'documents'" x-transition>
                <div class="bg-white rounded-xl shadow-lg border">
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-b">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Generar Responsiva</h3>
                            <p class="text-sm text-gray-600 mt-1 mb-4">Genera un nuevo PDF consolidado con todos los activos *actuales* del usuario.</p>
                            <a href="{{ route('asset-management.user-dashboard.pdf', $member) }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-file-invoice mr-2"></i> Generar PDF Consolidado
                            </a>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Adjuntar Responsiva Firmada</h3>
                            <p class="text-sm text-gray-600 mt-1 mb-4">Sube el PDF de la responsiva consolidada firmada por el usuario.</p>
                            
                            <form x-data="{ fileName: '' }" action="{{ route('asset-management.user-dashboard.uploadReceipt', $member) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                @csrf
                                <div>
                                    <input type="file" name="signed_receipt" id="signed_receipt_upload" class="hidden" 
                                           @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''" 
                                           required accept=".pdf">
                                    
                                    <label for="signed_receipt_upload" 
                                           class="btn btn-secondary w-full cursor-pointer overflow-hidden">
                                        <i class="fas fa-paperclip mr-2"></i>
                                        <span x-show="!fileName" class="truncate">Seleccionar archivo PDF...</span>
                                        <span x-show="fileName" x-text="fileName" class="truncate max-w-xs" x-cloak></span>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-accent w-full" :disabled="!fileName" :class="{ 'opacity-50 cursor-not-allowed': !fileName }">
                                    <i class="fas fa-upload mr-2"></i>
                                    Subir Archivo
                                </button>
                            </form>
                        </div>
                        </div>
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-[var(--color-primary)] mb-4">Historial de Responsivas</h3>
                        <div class="divide-y divide-gray-200">
                            @forelse($responsivas as $responsiva)
                                <div class="document-list-item">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-pdf document-icon"></i>
                                        <div>
                                            <p class="font-semibold text-gray-800">Responsiva Consolidada</p>
                                            <p class="text-sm text-gray-600">Subido el: {{ $responsiva->generated_date->format('d/m/Y h:i A') }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ Storage::disk('s3')->url($responsiva->file_path) }}" target="_blank" class="btn btn-secondary">
                                        <i class="fas fa-eye mr-2"></i> Ver Documento
                                    </a>
                                </div>
                            @empty
                                <div class="text-center py-10">
                                    <i class="fas fa-file-excel text-5xl text-gray-300"></i>
                                    <p class="mt-4 text-gray-500">No se han subido responsivas consolidadas para este usuario.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection