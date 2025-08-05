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
</style>

@php
    $user = Auth::user();
    $isCreator = $user->id === $ticket->user_id;
    $isAgent = $ticket->agent_id && $user->id === $ticket->agent_id;
    $isSuperAdmin = $user->isSuperAdmin();
@endphp

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="bg-white border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-lg shadow-md mb-6 flex items-center justify-between">
            <div class="flex items-center"><svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><div><strong class="font-bold">{{ __('¡Éxito!') }}</strong><span class="block sm:inline ml-1">{{ session('success') }}</span></div></div><button @click="show = false" class="text-gray-400 hover:text-gray-700">&times;</button>
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
                            <img src="{{ $reply->user->profile_photo_path ?? 'https://ui-avatars.com/api/?name='.urlencode($reply->user->name).'&color=7F9CF5&background=EBF4FF' }}" alt="{{ $reply->user->name }}" class="h-10 w-10 rounded-full">
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

            @if($ticket->status !== 'Cerrado')
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
            @else
                <div class="bg-gray-100 p-4 rounded-lg text-center text-gray-600">
                    <i class="fas fa-lock mr-2"></i> Este ticket ha sido cerrado y ya no se pueden añadir respuestas.
                </div>
            @endif
        </div>

        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-[var(--color-primary)] mb-4">Detalles del Ticket</h3>
                <div class="space-y-3 text-sm">
                    <p><strong>ID:</strong> <span class="font-mono">#{{ $ticket->id }}</span></p>
                    <p><strong>Categoría:</strong> {{ $ticket->category->name ?? 'Sin categoría' }}</p>
                    <p><strong>Creado por:</strong> {{ $ticket->user->name }}</p>
                    <p><strong>Asignado a:</strong> {{ $ticket->agent->name ?? 'Sin asignar' }}</p>
                    <p><strong>Fecha:</strong> {{ $ticket->created_at->format('d/m/Y') }}</p>
                    <p><strong>Prioridad:</strong> <span class="badge badge-{{ strtolower($ticket->priority) }}">{{ $ticket->priority }}</span></p>
                </div>

                @if($isAgent || $isSuperAdmin)
                    <div class="border-t border-gray-200 mt-4 pt-4">
                        <h4 class="font-bold text-sm text-[var(--color-primary)] mb-2">Panel de Agente</h4>
                        
                        <div>
                            <form action="{{ route('tickets.status.update', $ticket) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label for="status" class="text-xs font-semibold text-gray-600">Cambiar Estado</label>
                                    <select name="status" id="status" class="form-input form-input-sm w-full mt-1">
                                        <option value="Abierto" @selected($ticket->status == 'Abierto')>Abierto</option>
                                        <option value="En Proceso" @selected($ticket->status == 'En Proceso')>En Proceso</option>
                                        <option value="Cerrado" @selected($ticket->status == 'Cerrado')>Cerrado (Solicitar Aprobación)</option>
                                    </select>
                                </div>
                                <div id="closure-fields-container" class="hidden space-y-2">
                                    <div>
                                        <label for="work_summary" class="text-xs font-semibold text-gray-600">Trabajo Realizado (requerido):</label>
                                        <textarea name="work_summary" id="work_summary" rows="3" class="form-input form-input-sm mt-1"></textarea>
                                    </div>
                                    <div>
                                        <label for="closure_evidence" class="text-xs font-semibold text-gray-600">Adjuntar evidencia (opcional):</label>
                                        <input type="file" name="closure_evidence" class="form-input form-input-sm mt-1">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary w-full mt-2">Actualizar</button>
                            </form>
                        </div>

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
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const statusSelect = document.getElementById('status');
                            const closureFields = document.getElementById('closure-fields-container');
                            function toggleClosureFields() {
                                closureFields.classList.toggle('hidden', statusSelect.value !== 'Cerrado');
                            }
                            statusSelect.addEventListener('change', toggleClosureFields);
                            toggleClosureFields();
                        });
                    </script>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-bold text-[var(--color-primary)] mb-4">Historial de Estado</h3>
                <div class="timeline">
                    @foreach($ticket->statusHistories as $history)
                        <div class="timeline-item {{ $loop->last ? 'is-active' : '' }}">
                            <div class="timeline-dot"></div>
                            <p class="font-bold text-sm text-[var(--color-text-primary)]">{{ $history->status }}</p>
                            <p class="text-xs text-[var(--color-text-secondary)]">{{ $history->created_at->diffForHumans() }} @if($history->user) por {{ $history->user->name }} @endif</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($ticket->status === 'Pendiente de Aprobación' && $isCreator)
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg">
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
                    <form action="{{ route('tickets.approve-closure', $ticket) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-sm bg-green-600 text-white hover:bg-green-700">Aprobar y Cerrar Ticket</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection