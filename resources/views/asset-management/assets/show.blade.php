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
<div x-data="{ photoModalOpen: false, currentPhoto: '' }" class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <a href="{{ route('asset-management.dashboard') }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
            </a>
            <h1 class="text-3xl font-bold text-[var(--color-text-primary)] tracking-tight">{{ $asset->model->name }}</h1>
            <p class="font-mono text-[var(--color-primary)] text-sm mt-1">{{ $asset->asset_tag }}</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('asset-management.assets.edit', $asset) }}" class="btn btn-primary">
                <i class="fas fa-pencil-alt mr-2"></i> Editar Activo
            </a>
        </div>
    </div>

    {{-- Layout Principal --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Columna Izquierda (Detalles, Fotos y Software) --}}
        <div class="lg:col-span-2 space-y-8">
            
            {{-- Tarjeta de Fotografías del Activo --}}
            @if($asset->photo_1_path || $asset->photo_2_path || $asset->photo_3_path)
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-bold text-xl text-[var(--color-text-primary)] border-b pb-3 mb-4">Fotografías del Activo</h3>
                <div class="grid grid-cols-3 gap-4">
                    @foreach([$asset->photo_1_path, $asset->photo_2_path, $asset->photo_3_path] as $photo)
                        @if($photo)
                        <div class="aspect-w-1 aspect-h-1">
                            <img src="{{ Storage::disk('s3')->url($photo) }}" 
                                 @click="photoModalOpen = true; currentPhoto = '{{ Storage::disk('s3')->url($photo) }}'"
                                 alt="Fotografía del activo"
                                 class="rounded-lg object-cover cursor-pointer w-full h-full transition transform hover:scale-105">
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Tarjeta de Detalles --}}
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-bold text-xl text-[var(--color-text-primary)] border-b pb-3 mb-4">Detalles del Activo</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-semibold text-[var(--color-text-secondary)]">No. Serie:</span>
                        <span class="text-[var(--color-text-primary)] font-medium">{{ $asset->serial_number }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-semibold text-[var(--color-text-secondary)]">Categoría:</span>
                        <span class="text-[var(--color-text-primary)] font-medium">{{ $asset->model->category->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-semibold text-[var(--color-text-secondary)]">Fabricante:</span>
                        <span class="text-[var(--color-text-primary)] font-medium">{{ $asset->model->manufacturer->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-semibold text-[var(--color-text-secondary)]">Ubicación:</span>
                        <span class="text-[var(--color-text-primary)] font-medium">{{ $asset->site->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-semibold text-[var(--color-text-secondary)]">Fecha de Compra:</span>
                        <span class="text-[var(--color-text-primary)] font-medium">{{ $asset->purchase_date ? date('d/m/Y', strtotime($asset->purchase_date)) : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b">
                        <span class="font-semibold text-[var(--color-text-secondary)]">Fin de Garantía:</span>
                        <span class="text-[var(--color-text-primary)] font-medium">{{ $asset->warranty_end_date ? date('d/m/Y', strtotime($asset->warranty_end_date)) : 'N/A' }}</span>
                    </div>
                </div>

                @if($asset->model->category->name === 'Laptop' || $asset->model->category->name === 'Desktop' || $asset->model->category->name === 'Celular')
                    <h3 class="font-bold text-lg text-[var(--color-text-primary)] border-b pb-3 my-6">Especificaciones Técnicas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                        <div class="flex justify-between py-2 border-b"><span class="font-semibold text-[var(--color-text-secondary)]">Procesador:</span><span class="text-[var(--color-text-primary)] font-medium">{{ $asset->cpu ?? 'N/A' }}</span></div>
                        <div class="flex justify-between py-2 border-b"><span class="font-semibold text-[var(--color-text-secondary)]">RAM:</span><span class="text-[var(--color-text-primary)] font-medium">{{ $asset->ram ?? 'N/A' }}</span></div>
                        <div class="flex justify-between py-2 border-b"><span class="font-semibold text-[var(--color-text-secondary)]">Almacenamiento:</span><span class="text-[var(--color-text-primary)] font-medium">{{ $asset->storage ?? 'N/A' }}</span></div>
                        <div class="flex justify-between py-2 border-b"><span class="font-semibold text-[var(--color-text-secondary)]">MAC Address:</span><span class="text-[var(--color-text-primary)] font-medium">{{ $asset->mac_address ?? 'N/A' }}</span></div>
                    </div>
                @endif
            </div>

            {{-- Tarjeta de Software Asignado --}}
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-xl text-[var(--color-text-primary)]">Software Asignado</h3>
                    <a href="{{ route('asset-management.software-assignments.create', $asset) }}" class="btn bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-sm py-2 px-4">
                        <i class="fas fa-plus mr-2"></i> Asignar Software
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 font-semibold text-left text-gray-600">Nombre</th>
                                <th class="p-3 font-semibold text-left text-gray-600">Fecha de Instalación</th>
                                <th class="p-3 font-semibold text-right text-gray-600">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($asset->softwareAssignments as $assignment)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3">{{ $assignment->license->name }}</td>
                                <td class="p-3 text-gray-600">{{ $assignment->install_date ? date('d/m/Y', strtotime($assignment->install_date)) : 'N/A' }}</td>
                                <td class="p-3 text-right">
                                    <form action="{{ route('asset-management.software-assignments.destroy', $assignment) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres desinstalar este software?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-red-500 hover:text-red-700">Desinstalar</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="p-8 text-center text-gray-500">No hay software asignado a este activo.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Columna Derecha (Estado e Historial) --}}
        <div class="space-y-6">
            {{-- Tarjeta de Estado --}}
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-bold text-lg mb-3 text-[var(--color-text-primary)]">Estado Actual</h3>
                <p class="status-badge status-{{ Str::kebab($asset->status) }} inline-block">{{ $asset->status }}</p>

                @if($asset->currentAssignment)
                    <div class="mt-4 border-t pt-4">
                        <p class="text-sm font-semibold text-gray-600">Asignado a:</p>
                        <p class="text-lg font-bold text-[var(--color-primary)]">{{ $asset->currentAssignment->member->name }}</p>
                        <p class="text-sm text-gray-500">{{ $asset->currentAssignment->member->position->name ?? 'Sin Puesto' }}</p>
                        <p class="text-sm text-gray-500 mt-1">Desde: {{ date('d/m/Y', strtotime($asset->currentAssignment->assignment_date)) }}</p>
                        
                        <a href="{{ route('asset-management.assignments.pdf', $asset->currentAssignment) }}" target="_blank" class="btn bg-gray-700 text-white w-full mt-4"><i class="fas fa-file-pdf mr-2"></i> Generar Responsiva</a>
                        
                        <form action="{{ route('asset-management.assignments.return', $asset->currentAssignment) }}" method="POST" onsubmit="return confirm('¿Registrar la devolución de este activo?');">
                            @csrf
                            <button type="submit" class="btn bg-[var(--color-accent)] text-white w-full mt-2">Registrar Devolución</button>
                        </form>
                    </div>
                @else
                    @if($asset->status === 'En Almacén')
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('asset-management.assignments.create', $asset) }}" class="btn btn-primary w-full mt-4">Asignar</a>
                            <a href="{{ route('asset-management.assignments.createLoan', $asset) }}" class="btn btn-secondary w-full mt-4">Prestar</a>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Tarjeta de Historial --}}
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-[var(--color-primary)] mb-4">Línea de Vida del Activo</h3>
                <ul class="space-y-4 text-sm">
                @forelse($asset->logs as $log)
                    <li class="flex items-start space-x-3 border-b pb-3 last:border-b-0">
                        @if($log->loggable_type === 'App\Models\Assignment' && $log->loggable)
                            @php $assignment = $log->loggable; @endphp
                            <div class="mt-2 pl-4 border-l-2">
                                <!-- <a href="{{ route('asset-management.assignments.pdf', $assignment) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Ver Responsiva Original</a> -->
                                
                                @if($assignment->signed_receipt_path)
                                    <a href="{{ Storage::disk('s3')->url($assignment->signed_receipt_path) }}" target="_blank" class="text-xs text-green-600 hover:underline ml-4">Ver Responsiva Firmada</a>
                                @else
                                    <form action="{{ route('asset-management.assignments.uploadReceipt', $assignment) }}" method="POST" enctype="multipart/form-data" class="inline-block ml-4">
                                        @csrf
                                        <input type="file" name="signed_receipt" class="hidden" id="receipt-{{ $assignment->id }}" onchange="this.form.submit()">
                                        <label for="receipt-{{ $assignment->id }}" class="cursor-pointer text-xs text-blue-600 hover:underline">Subir Firmada (PDF)</label>
                                    </form>
                                @endif
                            </div>
                        @endif                        
                        <div class="flex-shrink-0 pt-1">
                            @if($log->action_type == 'Creación') <i class="fas fa-plus-circle text-blue-500"></i>
                            @elseif($log->action_type == 'Asignación' || $log->action_type == 'Préstamo') <i class="fas fa-user-check text-green-500"></i>
                            @elseif($log->action_type == 'Devolución') <i class="fas fa-undo text-gray-500"></i>
                            @elseif($log->action_type == 'Cambio de Estatus') <i class="fas fa-info-circle text-yellow-500"></i>
                            @endif
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">{{ $log->action_type }}</p>
                            <p class="text-gray-600">{{ $log->notes }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $log->created_at->diffForHumans() }}
                                @if($log->user) por {{ $log->user->name }} @endif
                            </p>
                        </div>
                    </li>
                @empty
                    <p class="text-center text-gray-500 p-4">No hay historial para este activo.</p>
                @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div x-show="photoModalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75" x-cloak>
        <div @click.away="photoModalOpen = false" class="relative p-4">
            <button @click="photoModalOpen = false" class="absolute -top-2 -right-2 text-white bg-gray-800 bg-opacity-50 rounded-full w-8 h-8 flex items-center justify-center text-xl hover:bg-opacity-75">&times;</button>
            <img :src="currentPhoto" class="max-w-screen-lg max-h-[85vh] rounded-lg shadow-2xl">
        </div>
    </div>    
</div>
@endsection