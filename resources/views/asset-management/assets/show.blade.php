@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary: #2c3856;
        --accent: #ff9c00;
        --success: #10b981;
        --bg-color: #f3f4f6;
    }
    [x-cloak] { display: none !important; }

    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    
    .timeline-line {
        position: absolute; left: 1.25rem; top: 2rem; bottom: -2rem; width: 2px; background-color: #e5e7eb; z-index: 0;
    }
    .timeline-item:last-child .timeline-line { display: none; }

    .modern-input {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.75rem;
        transition: all 0.2s;
        background-color: #f9fafb;
    }
    .modern-input:focus {
        background-color: #fff;
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(44, 56, 86, 0.1);
    }
</style>

<div class="min-h-screen bg-[#f3f4f6] pb-20" x-data="{ photoModalOpen: false, currentPhoto: '', returnModalOpen: false, assignmentToReturn: null, assignmentMember: '' }">
    
    <div class="bg-[var(--primary)] pt-12 pb-24 px-4 sm:px-6 lg:px-8 rounded-b-[3rem] shadow-2xl relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>
        
        <div class="max-w-7xl mx-auto relative z-10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 text-white">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/20 border border-white/20 backdrop-blur-md">
                            {{ $asset->model->category->name }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider 
                            {{ $asset->status == 'En Almacén' ? 'bg-green-500' : ($asset->status == 'Asignado' ? 'bg-blue-500' : 'bg-orange-500') }}">
                            {{ $asset->status }}
                        </span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">{{ $asset->model->name }}</h1>
                    <p class="mt-2 text-blue-200 font-mono text-lg">{{ $asset->asset_tag }}</p>
                </div>
                <div class="mt-6 md:mt-0 flex flex-wrap gap-3">
                    <a href="{{ route('asset-management.dashboard') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg backdrop-blur-md transition-all">
                        <i class="fas fa-arrow-left mr-2"></i> Volver
                    </a>
                    <a href="{{ route('asset-management.assets.edit', $asset) }}" class="px-4 py-2 bg-[var(--accent)] hover:bg-orange-600 rounded-lg shadow-lg transition-all text-white font-semibold">
                        <i class="fas fa-pencil-alt mr-2"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-20">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-8">
                
                @if($asset->photo_1_path || $asset->photo_2_path || $asset->photo_3_path)
                <div class="glass-panel rounded-2xl p-6 shadow-lg bg-white overflow-hidden">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-camera text-[var(--primary)] mr-2"></i> Galería</h3>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach([$asset->photo_1_path, $asset->photo_2_path, $asset->photo_3_path] as $photo)
                            @if($photo)
                                <div class="aspect-video rounded-xl overflow-hidden cursor-pointer shadow-sm hover:shadow-md transition-all group"
                                     @click="photoModalOpen = true; currentPhoto = '{{ Storage::disk('s3')->url($photo) }}'">
                                    <img src="{{ Storage::disk('s3')->url($photo) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <h3 class="font-bold text-lg text-gray-800 border-b pb-3 mb-4">Especificaciones y Detalles</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 text-sm">
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <span class="text-gray-500">Serie</span>
                            <span class="font-mono font-bold text-gray-800">{{ $asset->serial_number }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <span class="text-gray-500">Fabricante</span>
                            <span class="font-bold text-gray-800">{{ $asset->model->manufacturer->name }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <span class="text-gray-500">Ubicación</span>
                            <span class="font-bold text-gray-800">{{ $asset->site->name }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-50 pb-2">
                            <span class="text-gray-500">Compra</span>
                            <span class="font-bold text-gray-800">{{ $asset->purchase_date ? date('d/m/Y', strtotime($asset->purchase_date)) : 'N/A' }}</span>
                        </div>
                        @if($asset->cpu)
                            <div class="flex justify-between border-b border-gray-50 pb-2"><span class="text-gray-500">CPU</span><span class="font-bold text-gray-800">{{ $asset->cpu }}</span></div>
                        @endif
                        @if($asset->ram)
                            <div class="flex justify-between border-b border-gray-50 pb-2"><span class="text-gray-500">RAM</span><span class="font-bold text-gray-800">{{ $asset->ram }}</span></div>
                        @endif
                        @if($asset->storage)
                            <div class="flex justify-between border-b border-gray-50 pb-2"><span class="text-gray-500">Almacenamiento</span><span class="font-bold text-gray-800">{{ $asset->storage }}</span></div>
                        @endif
                        @if($asset->phone_number)
                            <div class="flex justify-between border-b border-gray-50 pb-2"><span class="text-gray-500">Teléfono</span><span class="font-bold text-gray-800">{{ $asset->phone_number }}</span></div>
                        @endif
                    </div>
                    @if($asset->notes)
                        <div class="mt-6 bg-yellow-50 p-4 rounded-xl text-sm text-yellow-800 border border-yellow-100">
                            <strong><i class="fas fa-sticky-note mr-1"></i> Notas:</strong> {{ $asset->notes }}
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800"><i class="fas fa-compact-disc text-[var(--primary)] mr-2"></i> Software Instalado</h3>
                        <a href="{{ route('asset-management.software-assignments.create', $asset) }}" class="text-xs bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full hover:bg-indigo-100 font-bold">
                            + Asignar
                        </a>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-100">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="p-3 text-left font-semibold text-gray-600">Licencia</th>
                                    <th class="p-3 text-left font-semibold text-gray-600">Instalación</th>
                                    <th class="p-3 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($asset->softwareAssignments as $soft)
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 font-medium">{{ $soft->license->name }}</td>
                                        <td class="p-3 text-gray-500">{{ $soft->install_date ? date('d/m/Y', strtotime($soft->install_date)) : '-' }}</td>
                                        <td class="p-3 text-right">
                                            <form action="{{ route('asset-management.software-assignments.destroy', $soft) }}" method="POST" onsubmit="return confirm('¿Eliminar?');">
                                                @csrf @method('DELETE')
                                                <button class="text-red-400 hover:text-red-600"><i class="fas fa-trash-alt"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="p-4 text-center text-gray-400 text-xs">Sin software registrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                
                <div class="bg-white rounded-2xl shadow-xl p-6 border-t-4 border-[var(--primary)]">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Asignación Actual</h4>
                        @if(!in_array($asset->status, ['En Reparación', 'De Baja', 'En Mantenimiento']))
                            <a href="{{ route('asset-management.assignments.create', $asset) }}" class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded hover:bg-blue-100 font-bold">
                                Nueva Asignación
                            </a>
                        @endif
                    </div>

                    @if($asset->currentAssignments->isNotEmpty())
                        @foreach($asset->currentAssignments as $assignment)
                            <div class="text-center py-4 bg-gray-50 rounded-xl mb-4">
                                <div class="w-16 h-16 bg-[var(--primary)] text-white rounded-full flex items-center justify-center mx-auto text-2xl shadow-lg mb-3">
                                    <span class="font-bold">{{ substr($assignment->member->name, 0, 1) }}</span>
                                </div>
                                <h3 class="font-bold text-gray-800 text-lg">{{ $assignment->member->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $assignment->member->position->name ?? 'Sin Puesto' }}</p>
                                <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                                    <i class="fas fa-calendar-alt mr-2"></i> {{ date('d M Y', strtotime($assignment->assignment_date)) }}
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('asset-management.assignments.edit', $assignment) }}" class="flex items-center justify-center bg-gray-100 text-gray-600 hover:bg-gray-200 w-full text-sm py-2 rounded-lg font-semibold transition-colors">Editar</a>
                                <button class="bg-[var(--accent)] text-white hover:bg-orange-600 w-full text-sm py-2 rounded-lg font-semibold shadow transition-colors"
                                        @click="assignmentToReturn = {{ $assignment->id }}; assignmentMember = '{{ $assignment->member->name }}'; returnModalOpen = true;">
                                    Devolver
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8 bg-green-50 rounded-xl border border-green-100">
                            <i class="fas fa-check-circle text-4xl text-green-400 mb-2"></i>
                            <p class="text-green-800 font-bold">Disponible en Almacén</p>
                        </div>
                    @endif

                    <div class="mt-4 pt-4 border-t border-gray-100">
                         <a href="{{ route('asset-management.maintenances.create', $asset) }}" class="flex items-center justify-center w-full py-3 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors font-semibold text-sm">
                            <i class="fas fa-tools mr-2"></i> Reportar Falla / Mantenimiento
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-6">Historial de Eventos</h3>
                    <div class="space-y-0 relative">
                        @forelse($asset->logs as $log)
                            <div class="timeline-item relative pl-8 pb-6">
                                <div class="timeline-line"></div>
                                <div class="absolute left-0 top-1 w-8 h-8 rounded-full flex items-center justify-center border-2 border-white shadow-sm z-10
                                    {{ $log->action_type == 'Creación' ? 'bg-blue-100 text-blue-600' : 
                                      ($log->action_type == 'Devolución' ? 'bg-gray-100 text-gray-600' : 
                                      ($log->action_type == 'Mantenimiento Completado' ? 'bg-green-100 text-green-600' : 'bg-[var(--primary)] text-white')) }}">
                                    <i class="fas {{ $log->action_type == 'Creación' ? 'fa-star' : 'fa-circle' }} text-xs"></i>
                                </div>
                                
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                    <div class="flex justify-between items-start">
                                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $log->action_type }}</span>
                                        <span class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($log->event_date)->format('d/m/y') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-800 mt-1 font-medium">{{ $log->notes }}</p>
                                    @if($log->user)
                                        <p class="text-xs text-gray-400 mt-1">Por: {{ $log->user->name }}</p>
                                    @endif
                                    
                                    @if($log->loggable_type === 'App\Models\Assignment' && $log->loggable)
                                        <div class="mt-2 flex gap-2 flex-wrap">
                                             @if($log->loggable->signed_receipt_path && in_array($log->action_type, ['Asignación', 'Préstamo']))
                                                <a href="{{ Storage::disk('s3')->url($log->loggable->signed_receipt_path) }}" target="_blank" class="text-[10px] bg-white border border-gray-200 px-2 py-1 rounded hover:bg-gray-100 text-blue-600 font-semibold"><i class="fas fa-file-pdf mr-1"></i> Entrega</a>
                                             @endif
                                             @if($log->loggable->return_receipt_path && $log->action_type == 'Devolución')
                                                <a href="{{ Storage::disk('s3')->url($log->loggable->return_receipt_path) }}" target="_blank" class="text-[10px] bg-white border border-gray-200 px-2 py-1 rounded hover:bg-gray-100 text-green-600 font-semibold"><i class="fas fa-file-pdf mr-1"></i> Devolución</a>
                                             @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400 text-sm text-center">Sin eventos registrados.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div x-show="photoModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm" x-cloak x-transition>
        <div @click.away="photoModalOpen = false" class="relative max-w-4xl w-full p-4">
            <button @click="photoModalOpen = false" class="absolute -top-10 right-0 text-white hover:text-gray-300 text-3xl">&times;</button>
            <img :src="currentPhoto" class="w-full h-auto rounded shadow-2xl">
        </div>
    </div>

    <div x-show="returnModalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" x-cloak>

        <div @click.away="returnModalOpen = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden transform transition-all">
            <form :action="'{{ route('asset-management.assignments.return', ['assignment' => 'ASSIGNMENT_ID']) }}'.replace('ASSIGNMENT_ID', assignmentToReturn)" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-gradient-to-r from-[var(--primary)] to-gray-800 p-6 text-white">
                    <h3 class="text-lg font-bold flex items-center">
                        <i class="fas fa-undo mr-3"></i> Registrar Devolución
                    </h3>
                </div>
                
                <div class="p-6 space-y-6">
                    <p class="text-sm text-gray-600 bg-blue-50 p-4 rounded-xl border border-blue-100">
                        Confirmas la recepción del activo <strong>{{ $asset->asset_tag }}</strong> devuelto por <strong x-text="assignmentMember" class="text-blue-700"></strong>.
                    </p>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Fecha Real de Devolución</label>
                        <input type="date" name="actual_return_date" value="{{ date('Y-m-d') }}" class="modern-input" required>
                    </div>

                    <div x-data="{ fileName: '' }">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Evidencia / Responsiva (PDF)</label>
                        <div class="relative group cursor-pointer">
                            <div class="flex items-center justify-center w-full px-6 py-6 border-2 border-dashed border-gray-300 rounded-xl group-hover:border-[var(--primary)] group-hover:bg-blue-50 transition-all">
                                <div class="text-center">
                                    <i class="fas fa-file-upload text-3xl text-gray-400 group-hover:text-[var(--primary)] mb-2 transition-colors"></i>
                                    <p class="text-sm text-gray-500 group-hover:text-[var(--primary)]">Click para subir archivo</p>
                                    <p x-show="fileName" x-text="fileName" class="text-xs font-bold text-green-600 mt-2"></p>
                                </div>
                            </div>
                            <input type="file" name="return_receipt" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf"
                                   @change="fileName = $event.target.files.length > 0 ? $event.target.files[0].name : ''">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end items-center space-x-3 border-t border-gray-100">
                    <button type="button" @click="returnModalOpen = false" class="px-4 py-2 text-gray-600 font-semibold hover:bg-gray-200 rounded-lg transition-colors">Cancelar</button>
                    <button type="submit" class="px-6 py-2 bg-[var(--primary)] hover:bg-gray-800 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection