@extends('layouts.app')

@section('content')

{{-- Estilos --}}
<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        --color-background: #f3f4f6;
        --color-success: #10B981;
        --color-danger: #EF4444;
        --color-warning: #F59E0B;
    }
    body { 
        background-color: var(--color-background); 
    }
    .btn { padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); }
    .btn-sm { padding: 0.5rem 1rem; font-size: 0.7rem; }
    .btn-primary { background-color: var(--color-primary); color: var(--color-surface); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1); }
    .badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
    .badge-baja { background-color: var(--color-success); color: white; }
    .badge-media { background-color: var(--color-warning); color: white; }
    .badge-alta { background-color: var(--color-danger); color: white; }
    .form-input { border-radius: 0.5rem; border-color: #e5e7eb; transition: all 0.3s ease; width: 100%; padding: 0.75rem 1rem; }
    .form-input-sm { font-size: 0.875rem; padding: 0.5rem 0.75rem; border-radius: 0.375rem; }
    .form-input:focus { --tw-ring-color: var(--color-accent); border-color: var(--color-accent); outline: none; box-shadow: 0 0 0 2px var(--tw-ring-color); }
    .timeline { border-left: 2px solid #e5e7eb; }
    .timeline-item { position: relative; padding-left: 2rem; padding-bottom: 1.5rem; }
    .timeline-item:last-child { padding-bottom: 0; }
    .timeline-dot { position: absolute; left: -0.5rem; top: 0.25rem; height: 1rem; width: 1rem; background-color: white; border: 2px solid var(--color-primary); border-radius: 9999px; }
    .timeline-item.is-active .timeline-dot { background-color: var(--color-primary); }
    .btn-file-upload { background-color: #f3f4f6; color: #4b5563; padding: 0.5rem 1rem; border-radius: 0.375rem; border: 1px solid #d1d5db; font-weight: 600; font-size: 0.75rem; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; }
    .btn-file-upload:hover { background-color: #e5e7eb; border-color: #9ca3af; }
</style>

@php
    $user = Auth::user();
    $isCreator = $user->id === $ticket->user_id;
    $isAgent = $ticket->agent_id && $user->id === $ticket->agent_id;
    $isSuperAdmin = $user->isSuperAdmin();

    $nextStatus = null;
    if ($ticket->status === 'Abierto') $nextStatus = 'En Proceso';
    if ($ticket->status === 'En Proceso') $nextStatus = 'Cerrado';
    
    $timelineItems = $ticket->statusHistories->map(function ($item) {
        $item->type = 'status';
        return $item;
    })->concat($ticket->replies->where('is_internal', false)->map(function ($item) {
        $item->type = 'reply';
        return $item;
    }))->sortBy('created_at');
@endphp

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="bg-white border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-lg shadow-md mb-6 flex items-center justify-between">
            <div class="flex items-center"><svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><div><strong class="font-bold">{{ __('¡Éxito!') }}</strong><span class="block sm:inline ml-1">{{ session('success') }}</span></div></div><button @click="show = false" class="text-gray-400 hover:text-gray-700">&times;</button>
        </div>
    @endif
     @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="bg-white border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg shadow-md mb-6 flex items-center justify-between">
            <div class="flex items-center"><svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><div><strong class="font-bold">{{ __('Error') }}</strong><span class="block sm:inline ml-1">{{ session('error') }}</span></div></div><button @click="show = false" class="text-gray-400 hover:text-gray-700">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h1 class="text-3xl font-bold text-[var(--color-text-primary)] mb-2">{{ $ticket->title }}</h1>
                <p class="text-sm text-[var(--color-text-secondary)]">Abierto por {{ $ticket->user->name }}</p>
                <div class="prose max-w-none text-gray-600 mt-4 whitespace-pre-wrap"><p>{{ $ticket->description }}</p></div>
                @if($ticket->attachment_path)
                    <div class="mt-6">
                        <h4 class="font-bold text-sm text-[var(--color-primary)] mb-2">Archivo Adjunto</h4>
                        <a href="{{ Storage::disk('s3')->url($ticket->attachment_path) }}" target="_blank"><img src="{{ Storage::disk('s3')->url($ticket->attachment_path) }}" alt="Archivo adjunto" class="max-w-xs rounded-lg shadow-md hover:opacity-80 transition-opacity"></a>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                @foreach($ticket->replies as $reply)
                    @if(!$reply->is_internal || $isSuperAdmin || $isAgent)
                        <div class="flex items-start gap-4 {{ $reply->user_id === $ticket->user_id ? '' : 'flex-row-reverse' }}">
                            @if ($reply->user->profile_photo_path)
                                <img src="{{ Storage::disk('s3')->url($reply->user->profile_photo_path) }}" alt="{{ $reply->user->name }}" class="h-10 w-10 rounded-full object-cover">
                            @else
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($reply->user->name) }}&color=2c3856&background=e8ecf7" alt="{{ $reply->user->name }}" class="h-10 w-10 rounded-full">
                            @endif
                            <div class="w-full bg-white rounded-xl shadow-md p-4 {{ $reply->is_internal ? 'bg-blue-50 border-l-4 border-blue-400' : '' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-bold text-[var(--color-primary)]">{{ $reply->user->name }}</p>
                                    @if($reply->is_internal)
                                        <span class="text-xs font-bold text-blue-600">NOTA INTERNA</span>
                                    @endif
                                    <p class="text-xs text-[var(--color-text-secondary)]">{{ $reply->created_at->diffForHumans() }}</p>
                                </div>
                                <p class="text-[var(--color-text-secondary)] whitespace-pre-wrap">{{ $reply->body }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if($ticket->status !== 'Cerrado' && !in_array($ticket->status, ['Pendiente de Aprobación']))
                @if($isCreator || $isSuperAdmin || $isAgent)
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <form action="{{ route('tickets.reply.store', $ticket) }}" method="POST">
                        @csrf
                        <h3 class="font-bold text-lg text-[var(--color-primary)] mb-2">Añadir una Respuesta</h3>
                        <textarea name="body" rows="4" class="form-input" placeholder="Escribe tu respuesta aquí..." required></textarea>
                        
                        @if($isSuperAdmin || $isAgent)
                        <div class="mt-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_internal" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">Marcar como Nota Interna</span>
                            </label>
                        </div>
                        @endif

                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-primary">Enviar Respuesta</button>
                        </div>
                    </form>
                </div>
                @endif
            @elseif($ticket->status === 'Cerrado')
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="text-center">
                        <i class="fas fa-lock text-gray-400 text-2xl mb-2"></i>
                        <h3 class="font-bold text-lg text-gray-700">Este ticket ha sido cerrado.</h3>
                        <p class="text-sm text-gray-500">Ya no se pueden añadir más respuestas.</p>
                    </div>

                    @if($isCreator && is_null($ticket->rating))
                        <div x-data="{ rating: 0, hoverRating: 0 }" class="border-t mt-6 pt-4">
                            <h4 class="font-bold text-center text-gray-800 mb-2">Califica el servicio recibido</h4>
                            <form action="{{ route('tickets.rating.store', $ticket) }}" method="POST">
                                @csrf
                                <div class="flex justify-center items-center space-x-2 mb-4">
                                    <template x-for="star in 5">
                                        <svg @click="rating = star" @mouseenter="hoverRating = star" @mouseleave="hoverRating = 0"
                                             class="w-8 h-8 cursor-pointer" 
                                             :class="(hoverRating >= star || rating >= star) ? 'text-yellow-400' : 'text-gray-300'"
                                             fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                        </svg>
                                    </template>
                                    <input type="hidden" name="rating" x-model="rating">
                                </div>
                                
                                <textarea name="rating_comment" rows="3" class="form-input w-full text-sm" placeholder="Añade un comentario (opcional)..."></textarea>
                                
                                <button type="submit" class="btn btn-primary w-full mt-4">Enviar Calificación</button>
                            </form>
                        </div>
                    @elseif($isCreator && !is_null($ticket->rating))
                        <div class="text-center border-t mt-6 pt-4">
                            <p class="font-semibold text-gray-700">Tu calificación:</p>
                            <div class="flex justify-center text-yellow-400 mt-1">
                                @for ($i = 0; $i < $ticket->rating; $i++) <i class="fas fa-star"></i> @endfor
                                @for ($i = $ticket->rating; $i < 5; $i++) <i class="far fa-star"></i> @endfor
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-[var(--color-primary)] mb-4">Detalles del Ticket</h3>
                <div class="space-y-3 text-sm">
                    <p><strong>ID:</strong> <span class="font-mono">#{{ $ticket->id }}</span></p>
                    <p><strong>Categoría:</strong> {{ $ticket->subCategory->category->name ?? 'N/A' }} > {{ $ticket->subCategory->name ?? 'N/A' }}</p>
                    <p><strong>Creado por:</strong> {{ $ticket->user->name }}</p>
                    <p><strong>Asignado a:</strong> {{ $ticket->agent->name ?? 'Sin asignar' }}</p>
                    <p><strong>Fecha:</strong> {{ $ticket->created_at->format('d/m/Y') }}</p>
                    <p><strong>Prioridad:</strong> <span class="badge badge-{{ strtolower($ticket->priority) }}">{{ $ticket->priority }}</span></p>
                    @if($ticket->asset)
                    <div class="border-t pt-3 mt-3">
                        <p class="font-bold">Activo Vinculado:</p>
                        <p>
                            <a href="{{ route('asset-management.assets.show', $ticket->asset) }}" class="text-indigo-600 hover:underline">
                                {{ $ticket->asset->model->name }} ({{ $ticket->asset->asset_tag }})
                            </a>
                        </p>
                    </div>
                    @endif                    
                </div>

                @if(($isAgent || $isSuperAdmin) && !in_array($ticket->status, ['Cerrado', 'Pendiente de Aprobación']))
                    <div class="border-t border-gray-200 mt-4 pt-4">
                        <h4 class="font-bold text-sm text-[var(--color-primary)] mb-2">Panel de Agente</h4>
                        
                        @if($nextStatus)
                        <form action="{{ route('tickets.status.update', $ticket) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="status" value="{{ $nextStatus }}">
                            <div class="mb-2">
                                <label class="text-xs font-semibold text-gray-600">Siguiente Acción</label>
                                <p class="font-bold text-gray-800">{{ $nextStatus === 'Cerrado' ? 'Solicitar Cierre' : 'Marcar como ' . $nextStatus }}</p>
                            </div>
                            
                            @if($nextStatus === 'Cerrado')
                            <div class="space-y-2" x-data="{}">
                                <div>
                                    <label for="work_summary" class="text-xs font-semibold text-gray-600">Trabajo Realizado (requerido):</label>
                                    <textarea name="work_summary" id="work_summary" rows="3" class="form-input form-input-sm mt-1"></textarea>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600">Adjuntar evidencia (opcional):</label>
                                    <div class="mt-1">
                                        <label for="closure_evidence_input" class="btn-file-upload">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            <span id="closure_evidence_name">Seleccionar archivo</span>
                                        </label>
                                        <input type="file" name="closure_evidence" id="closure_evidence_input" class="hidden" onchange="document.getElementById('closure_evidence_name').textContent = this.files[0] ? this.files[0].name : 'Seleccionar archivo'">
                                    </div>
                                </div>
                            </div>
                            @endif
                            <button type="submit" class="btn btn-sm btn-primary w-full mt-2">Confirmar Acción</button>
                        </form>
                        @endif

                        @if($isSuperAdmin)
                        <div class="mt-4 border-t pt-4">
                            <h5 class="font-semibold text-gray-700 text-xs mb-1">Reasignar Agente</h5>
                            <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <select name="agent_id" class="form-input form-input-sm w-full">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" @selected($ticket->agent_id == $agent->id)>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Asignar</button>
                            </form>
                        </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-[var(--color-primary)] mb-4">Línea de Tiempo</h3>
                <div class="timeline mt-4">
                    @forelse($timelineItems as $item)
                        <div class="timeline-item {{ $loop->last ? 'is-active' : '' }}">
                            <div class="timeline-dot"></div>
                            @if($item->type === 'status')
                                <p class="font-bold text-sm text-[var(--color-text-primary)]">{{ $item->status }}</p>
                            @else
                                <p class="font-bold text-sm text-[var(--color-text-primary)]">{{ $item->user_id === $ticket->user_id ? 'Mensaje de Usuario' : 'Respuesta de Agente' }}</p>
                                <p class="text-sm text-gray-600 mt-1 italic">"{{ Str::limit($item->body, 50) }}"</p>
                            @endif
                            <p class="text-xs text-[var(--color-text-secondary)]">{{ $item->created_at->diffForHumans() }} por {{ $item->user->name ?? 'Sistema' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No hay actividad en la línea de tiempo.</p>
                    @endforelse
                </div>
            </div>

            @if($ticket->status === 'Pendiente de Aprobación' && $isCreator)
                <div x-data="{ showRejectionForm: false }" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg">
                    <h4 class="font-bold">Acción Requerida</h4>
                    <p>Este ticket está esperando tu aprobación para cerrarse.</p>
                    
                    @if($ticket->work_summary)
                    <div class="mt-4 bg-white p-3 rounded-md">
                        <p class="font-semibold text-gray-800">Resumen del Trabajo Realizado:</p>
                        <p class="text-gray-700 mt-1 whitespace-pre-wrap">{{ $ticket->work_summary }}</p>
                    </div>
                    @endif
                    @if($ticket->closure_evidence_path)
                        <div class="mt-4">
                            <p class="font-semibold">Evidencia de Cierre:</p>
                            <a href="{{ Storage::disk('s3')->url($ticket->closure_evidence_path) }}" target="_blank"><img src="{{ Storage::disk('s3')->url($ticket->closure_evidence_path) }}" alt="Evidencia" class="max-w-xs rounded-lg shadow-md mt-2"></a>
                        </div>
                    @endif

                    <div class="mt-4 flex items-center space-x-2">
                        <form action="{{ route('tickets.approve-closure', $ticket) }}" method="POST"> @csrf <button type="submit" class="btn btn-sm bg-green-600 text-white hover:bg-green-700">Aprobar y Cerrar</button></form>
                        <button @click="showRejectionForm = !showRejectionForm" class="btn btn-sm bg-red-600 text-white hover:bg-red-700">Rechazar</button>
                    </div>

                    <div x-show="showRejectionForm" x-transition class="mt-4 border-t border-yellow-400 pt-4">
                        <form action="{{ route('tickets.reject-closure', $ticket) }}" method="POST">
                            @csrf
                            <label for="rejection_reason" class="block text-sm font-bold text-gray-700 mb-1">Motivo del Rechazo (requerido)</label>
                            <textarea name="rejection_reason" rows="3" class="form-input w-full text-sm" placeholder="Explica por qué la solución no fue satisfactoria..."></textarea>
                            <button type="submit" class="btn btn-sm btn-primary w-full mt-2">Enviar Rechazo</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection