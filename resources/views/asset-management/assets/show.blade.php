@extends('layouts.app')

@section('content')
<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        
        --color-primary-dark: #212a41;
        --color-background: #f3f4f6;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }

    body {
        background-color: var(--color-background);
    }

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
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div>
            <a href="{{ route('asset-management.dashboard') }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
            </a>
            <h1 class="text-3xl font-bold text-[var(--color-text-primary)] tracking-tight">{{ $asset->model->name }}</h1>
            <p class="font-mono text-[var(--color-primary)] text-sm mt-1">{{ $asset->asset_tag }}</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('asset-management.maintenances.create', $asset) }}" class="btn btn-secondary">
                <i class="fas fa-tools mr-2"></i> Enviar a Mantenimiento
            </a>            
            <a href="{{ route('asset-management.assets.edit', $asset) }}" class="btn btn-primary">
                <i class="fas fa-pencil-alt mr-2"></i> Editar Activo
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
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
                @if($asset->notes)
                <div class="mt-6 border-t pt-4">
                    <h4 class="font-semibold text-[var(--color-text-secondary)] text-sm">Notas Adicionales:</h4>
                    <p class="text-sm text-[var(--color-text-primary)] mt-2 whitespace-pre-wrap">{{ $asset->notes }}</p>
                </div>
                @endif                

                @php
                    $categoryName = $asset->model->category->name;
                    $techSpecCategories = ['Laptop', 'Desktop', 'Celular', 'Impresora', 'Ipad', 'Pantalla', 'Monitor'];
                @endphp

                @if(in_array($categoryName, $techSpecCategories))
                    <h3 class="font-bold text-lg text-[var(--color-text-primary)] border-b pb-3 my-6">Especificaciones Técnicas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                        <div class="flex justify-between py-2 border-b"><span class="font-semibold text-[var(--color-text-secondary)]">Procesador:</span><span class="text-[var(--color-text-primary)] font-medium">{{ $asset->cpu ?? 'N/A' }}</span></div>
                        <div class="flex justify-between py-2 border-b"><span class="font-semibold text-[var(--color-text-secondary)]">RAM:</span><span class="text-[var(--color-text-primary)] font-medium">{{ $asset->ram ?? 'N/A' }}</span></div>
                        <div class="flex justify-between py-2 border-b"><span class="font-semibold text-[var(--color-text-secondary)]">Almacenamiento:</span><span class="text-[var(--color-text-primary)] font-medium">{{ $asset->storage ?? 'N/A' }}</span></div>
                        <div class="flex justify-between py-2 border-b"><span class="font-semibold text-[var(--color-text-secondary)]">MAC Address:</span><span class="text-[var(--color-text-primary)] font-medium">{{ $asset->mac_address ?? 'N/A' }}</span></div>
                    </div>
                @endif
                
                @if($categoryName === 'Celular')
                    <h3 class="font-bold text-lg text-[var(--color-text-primary)] border-b pb-3 my-6">Detalles de Telefonía</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                        <div class="flex justify-between py-2 border-b"><span class="font-semibold text-[var(--color-text-secondary)]">Número Telefónico:</span><span class="text-[var(--color-text-primary)] font-medium">{{ $asset->phone_number ?? 'N/A' }}</span></div>
                        <div class="flex justify-between py-2 border-b"><span class="font-semibold text-[var(--color-text-secondary)]">Tipo de Plan:</span><span class="text-[var(--color-text-primary)] font-medium">{{ $asset->phone_plan_type ?? 'N/A' }}</span></div>
                    </div>
                @endif
            </div>

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

        <div class="space-y-6">
            <div x-data="{ returnModalOpen: false }" class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="font-bold text-lg mb-3 text-[var(--color-primary)]">Estado Actual</h3>
                <p class="status-badge status-{{ Str::kebab($asset->status) }} inline-block">{{ $asset->status }}</p>

                @if($asset->currentAssignment)
                    <div class="mt-4 border-t pt-4">
                        <p class="text-sm font-semibold text-gray-600">{{ $asset->currentAssignment->type === 'Préstamo' ? 'Prestado a:' : 'Asignado a:' }}</p>
                        <p class="text-lg font-bold text-[var(--color-primary)]">{{ $asset->currentAssignment->member->name }}</p>
                        <p class="text-sm text-gray-500">{{ $asset->currentAssignment->member->position->name ?? 'Sin Puesto' }}</p>
                        <p class="text-sm text-gray-500 mt-1">Desde: {{ date('d/m/Y', strtotime($asset->currentAssignment->assignment_date)) }}</p>
                    @if($userResponsivas->isNotEmpty())
                    <div class="mt-4 border-t pt-4">
                        <h4 class="text-sm font-semibold text-gray-600 mb-2">Responsivas Consolidadas del Usuario:</h4>
                        <ul class="space-y-2 text-sm">
                            @foreach($userResponsivas as $responsiva)
                            <li class="flex justify-between items-center">
                                <div>
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                    <span>{{ \Carbon\Carbon::parse($responsiva->generated_date)->format('d/m/Y') }}</span>
                                </div>
                                <a href="{{ Storage::disk('s3')->url($responsiva->file_path) }}" target="_blank" class="font-semibold text-[var(--color-primary)] hover:underline">
                                    Ver Documento
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif                        
                        
                        <button @click="returnModalOpen = true" class="btn bg-[var(--color-accent)] text-white w-full mt-4">
                            Registrar Devolución
                        </button>
                    </div>

                    <div x-show="returnModalOpen" 
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60" x-cloak>

                        <div @click.away="returnModalOpen = false" 
                            x-show="returnModalOpen"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-90"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-90"
                            class="bg-white rounded-xl shadow-lg w-full max-w-lg mx-4">
                            
                            <form action="{{ route('asset-management.assignments.return', $asset->currentAssignment) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="p-6 border-b">
                                    <h3 class="text-lg font-bold text-[var(--color-primary)] flex items-center">
                                        <i class="fas fa-undo mr-3"></i>
                                        Confirmar Devolución de Activo
                                    </h3>
                                </div>
                                
                                <div class="p-6">
                                    <p class="text-sm text-gray-600 mb-6">
                                        Estás a punto de registrar la devolución del activo <strong>{{ $asset->asset_tag }}</strong> por parte de <strong>{{ $asset->currentAssignment->member->name }}</strong>. El estatus del activo cambiará a "En Almacén".
                                    </p>

                                    <div x-data="{ fileName: '' }">
                                        <label class="form-label">Adjuntar Responsiva de Devolución Firmada (PDF, Opcional)</label>
                                        <div class="mt-1 flex items-center justify-center w-full px-6 py-4 border-2 border-gray-300 border-dashed rounded-md">
                                            <div class="text-center">
                                                <i class="fas fa-file-pdf text-3xl text-gray-400"></i>
                                                <div class="flex text-sm text-gray-600 mt-2">
                                                    <label for="return_receipt" class="relative cursor-pointer bg-white rounded-md font-medium text-[var(--color-primary)] hover:text-[var(--color-accent)] focus-within:outline-none">
                                                        <span>Haz clic para seleccionar el archivo</span>
                                                        <input id="return_receipt" name="return_receipt" type="file" class="sr-only" accept=".pdf"
                                                            @change="fileName = $event.target.files.length > 0 ? $event.target.files[0].name : ''">
                                                    </label>
                                                </div>
                                                <p x-show="fileName" class="text-xs text-gray-500 mt-2" x-cloak>
                                                    Archivo seleccionado: <span x-text="fileName" class="font-semibold"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-100 px-6 py-4 flex justify-end items-center space-x-3 rounded-b-xl">
                                    <button type="button" @click="returnModalOpen = false" class="btn btn-secondary">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Confirmar Devolución</button>
                                </div>
                            </form>
                        </div>
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

            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-[var(--color-primary)] mb-4">Línea de Vida del Activo</h3>
                <ul class="space-y-4 text-sm">
                @forelse($asset->logs as $log)
                    <li class="flex items-start space-x-3 border-b pb-3 last:border-b-0">
                        <div class="flex-shrink-0 pt-1">
                            {{-- Icono dinámico --}}
                            @if($log->action_type == 'Creación') <i class="fas fa-plus-circle text-blue-500"></i>
                            @elseif(in_array($log->action_type, ['Asignación', 'Préstamo'])) <i class="fas fa-user-check text-green-500"></i>
                            @elseif($log->action_type == 'Devolución') <i class="fas fa-undo text-gray-500"></i>
                            @elseif(in_array($log->action_type, ['En Mantenimiento', 'En Reparación', 'Mantenimiento Completado'])) <i class="fas fa-tools text-orange-500"></i>
                            @else <i class="fas fa-info-circle text-yellow-500"></i>
                            @endif
                        </div>
                        <div class="flex-grow">
                            <p class="font-semibold text-gray-800">{{ $log->action_type }}</p>
                            <p class="text-gray-600">{{ $log->notes }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $log->created_at->diffForHumans() }}
                                @if($log->user) por {{ $log->user->name }} @endif
                            </p>

                            @if($log->loggable_type === 'App\Models\Assignment' && $log->loggable)
                                @php $assignment = $log->loggable; @endphp
                                
                                @if(in_array($log->action_type, ['Asignación', 'Préstamo']))
                                <div class="mt-2 pl-4 border-l-2 space-y-1">
                                    @if(!$assignment->signed_receipt_path)
                                        <div><a href="{{ route('asset-management.assignments.pdf', $assignment) }}" target="_blank" class="text-xs text-indigo-600 hover:underline">Ver Responsiva Original</a></div>
                                    @endif
                                    <div>
                                        @if($assignment->signed_receipt_path)
                                            <a href="{{ Storage::disk('s3')->url($assignment->signed_receipt_path) }}" target="_blank" class="text-xs text-green-600 hover:underline">Ver Responsiva</a>
                                        @else
                                            <form action="{{ route('asset-management.assignments.uploadReceipt', $assignment) }}" method="POST" enctype="multipart/form-data" class="inline-block">
                                                @csrf
                                                <input type="file" name="signed_receipt" class="hidden" id="receipt-{{ $assignment->id }}" onchange="this.form.submit()">
                                                <label for="receipt-{{ $assignment->id }}" class="cursor-pointer text-xs text-blue-600 hover:underline">Cargar responsiva</label>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                @if($log->action_type === 'Devolución')
                                <div class="mt-2 pl-4 border-l-2 space-y-1">
                                    @if($assignment->return_receipt_path)
                                        <div><a href="{{ Storage::disk('s3')->url($assignment->return_receipt_path) }}" target="_blank" class="text-xs text-green-600 hover:underline">Ver responsiva</a></div>
                                    @else
                                    <form action="{{ route('asset-management.assignments.uploadReturnReceipt', $assignment) }}" method="POST" enctype="multipart/form-data" class="inline-block">
                                            @csrf
                                            <input type="file" name="return_receipt" class="hidden" id="return-receipt-{{ $assignment->id }}" onchange="this.form.submit()">
                                            <label for="return-receipt-{{ $assignment->id }}" class="cursor-pointer text-xs text-blue-600 hover:underline">Cargar responsiva</label>
                                        </form>
                                    @endif
                                </div>
                                @endif
                            @endif
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