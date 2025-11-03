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
        --color-success: #10B981;      /* Baja */
        --color-danger: #EF4444;       /* Alta */
        --color-warning: #F59E0B;     /* Media */
        --color-info: #3B82F6;        /* En Proceso */
        --color-pending: #A855F7;     /* Pendiente de Aprobación */
        --color-closed: #6B7280;      /* Cerrado */
        --color-border: #e5e7eb;
    }
    body { background-color: var(--color-background); }

    /* Estilos de Insignia de Estado */
    .badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; display: inline-flex; align-items: center; }
    .badge-dot { width: 6px; height: 6px; border-radius: 9999px; margin-right: 6px; }
    
    .badge-baja { background-color: var(--color-success); color: white; }
    .badge-media { background-color: var(--color-warning); color: white; }
    .badge-alta { background-color: var(--color-danger); color: white; }

    .badge-abierto { background-color: #e0e7ff; color: #3730a3; }
    .badge-abierto .badge-dot { background-color: #4f46e5; }

    .badge-en-proceso { background-color: #fef3c7; color: #b45309; }
    .badge-en-proceso .badge-dot { background-color: var(--color-warning); }
    
    .badge-pendiente-de-aprobación { background-color: #f3e8ff; color: #7e22ce; }
    .badge-pendiente-de-aprobación .badge-dot { background-color: var(--color-pending); }

    .badge-cerrado { background-color: #f3f4f6; color: #4b5563; }
    .badge-cerrado .badge-dot { background-color: var(--color-closed); }

    /* Colores de Borde de Prioridad para las Tarjetas */
    .priority-baja { border-left-color: var(--color-success); }
    .priority-media { border-left-color: var(--color-warning); }
    .priority-alta { border-left-color: var(--color-danger); }

    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; transition: all 0.2s ease; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); }
    .btn-sm { padding: 0.5rem 1rem; font-size: 0.7rem; }
    .btn-accent { background-color: var(--color-accent); color: var(--color-primary); }
    .btn-accent:hover { background-color: #ffb03a; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transform: translateY(-1px); }
    .btn-primary { background-color: var(--color-primary); color: var(--color-surface); }
    .btn-primary:hover { background-color: #1e263b; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transform: translateY(-1px); }
    
    .form-input-sm { font-size: 0.875rem; padding: 0.5rem 0.75rem; border-radius: 0.375rem; border: 1px solid var(--color-border); }
    .form-input-sm:focus { border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--color-primary); outline: none; }

    /* Estilos de Tarjeta */
    .ticket-card {
        background-color: var(--color-surface);
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--color-border);
        border-left-width: 4px;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .ticket-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -4px rgba(0, 0, 0, 0.07);
        transform: translateY(-2px);
    }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8">
        <div>
            <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Centro de Soporte</h1>
            <p class="text-lg text-[var(--color-text-secondary)] mt-1">Gestiona todas las solicitudes de TI en un solo lugar.</p>
        </div>
        <div class="flex items-center space-x-2 mt-4 sm:mt-0 flex-shrink-0">
            @if(in_array(Auth::user()->area?->name, ['Administración', 'Innovación y Desarrollo']))
                <a href="{{ route('tickets.dashboard') }}" class="btn bg-white text-[var(--color-primary)] border border-gray-300 hover:bg-gray-50">
                    <i class="fas fa-chart-bar mr-2"></i> Indicadores
                </a>
            @endif
            <a href="{{ route('tickets.create') }}" class="btn btn-accent">
                <i class="fas fa-plus mr-2"></i> Crear Ticket
            </a>
        </div>
    </header>

    <div class="bg-white p-4 rounded-xl shadow-lg mb-8" x-data="{ filtersOpen: {{ request()->hasAny(['search', 'status', 'priority', 'category_id', 'agent_id']) ? 'true' : 'false' }} }">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-[var(--color-primary)]">Filtrar Tickets</h3>
            <button @click="filtersOpen = !filtersOpen" class="btn btn-sm bg-gray-100 text-gray-700 hover:bg-gray-200">
                <span x-show="!filtersOpen"><i class="fas fa-filter mr-2"></i> Mostrar Filtros</span>
                <span x-show="filtersOpen"><i class="fas fa-times mr-2"></i> Ocultar Filtros</span>
            </button>
        </div>

        <form action="{{ route('tickets.index') }}" method="GET" x-show="filtersOpen" x-transition class="mt-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                <input type="text" name="search" placeholder="Buscar por título o usuario..." value="{{ $filters['search'] ?? '' }}" class="form-input-sm col-span-1 sm:col-span-2 lg:col-span-1">
                <select name="status" class="form-input-sm">
                    <option value="">-- Estatus --</option>
                    <option value="Abierto" @selected(($filters['status'] ?? '') == 'Abierto')>Abierto</option>
                    <option value="En Proceso" @selected(($filters['status'] ?? '') == 'En Proceso')>En Proceso</option>
                    <option value="Pendiente de Aprobación" @selected(($filters['status'] ?? '') == 'Pendiente de Aprobación')>Pendiente de Aprobación</option>
                    <option value="Cerrado" @selected(($filters['status'] ?? '') == 'Cerrado')>Cerrado</option>
                </select>
                <select name="priority" class="form-input-sm">
                    <option value="">-- Prioridad --</option>
                    <option value="Baja" @selected(($filters['priority'] ?? '') == 'Baja')>Baja</option>
                    <option value="Media" @selected(($filters['priority'] ?? '') == 'Media')>Media</option>
                    <option value="Alta" @selected(($filters['priority'] ?? '') == 'Alta')>Alta</option>
                </select>
                <select name="category_id" class="form-input-sm">
                    <option value="">-- Categoría --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @if(Auth::user()->isSuperAdmin())
                <select name="agent_id" class="form-input-sm">
                    <option value="">-- Agente Asignado --</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" @selected(($filters['agent_id'] ?? '') == $agent->id)>{{ $agent->name }}</option>
                    @endforeach
                </select>
                @endif
            </div>
            <div class="flex items-center justify-end mt-4 space-x-2">
                <a href="{{ route('tickets.index') }}" class="btn btn-sm bg-gray-200 text-gray-700 hover:bg-gray-300">Limpiar</a>
                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($tickets as $ticket)
            <div class="ticket-card priority-{{ strtolower($ticket->priority) }}" onclick="window.location='{{ route('tickets.show', $ticket) }}';">
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center">
                        <h2 class="text-lg font-semibold text-[var(--color-primary)] hover:underline">{{ $ticket->title }}</h2>
                        <span class="font-mono text-sm text-[var(--color-text-secondary)] mt-1 sm:mt-0">#{{ $ticket->id }}</span>
                    </div>
                    
                    <div class="flex items-center space-x-4 text-sm text-[var(--color-text-secondary)] mt-2">
                        <span>
                            <i class="fas fa-user mr-1 opacity-60"></i>
                            {{ $ticket->user->name }}
                        </span>
                        <span>
                            <i class="fas fa-calendar-alt mr-1 opacity-60"></i>
                            {{ $ticket->created_at->format('d/m/Y h:i A') }}
                        </span>
                        <span>
                            <i class="fas fa-tag mr-1 opacity-60"></i>
                            {{ $ticket->subCategory->category->name ?? 'Sin categoría' }}
                        </span>
                    </div>

                    <div class="border-t border-gray-200 my-4"></div>

                    <div class="flex flex-col sm:flex-row justify-between sm:items-center">
                        <div class="flex items-center space-x-2">
                            <span class_badge_status = "badge-{{ strtolower(str_replace(' ', '-', $ticket->status)) }}">
                                <span class="badge-dot"></span>
                                {{ $ticket->status }}
                            </span>
                            <span class="badge badge-{{ strtolower($ticket->priority) }}">
                                {{ $ticket->priority }}
                            </span>
                        </div>
                        
                        <div class="flex items-center mt-3 sm:mt-0">
                            <span class="text-sm text-gray-500 mr-2">Asignado a:</span>
                            @if($ticket->agent)
                                @if ($ticket->agent->profile_photo_path)
                                    <img src="{{ Storage::disk('s3')->url($ticket->agent->profile_photo_path) }}" alt="{{ $ticket->agent->name }}" class="h-8 w-8 rounded-full object-cover">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->agent->name) }}&color=2c3856&background=e8ecf7" alt="{{ $ticket->agent->name }}" class="h-8 w-8 rounded-full">
                                @endif
                                <span class="ml-2 font-medium text-[var(--color-text-primary)]">{{ $ticket->agent->name }}</span>
                            @else
                                <span class="ml-2 italic text-gray-500">Sin asignar</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <i class="fas fa-ticket-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700">No se encontraron tickets</h3>
                <p class="text-gray-500 mt-2">
                    @if(request()->hasAny(['search', 'status', 'priority', 'category_id', 'agent_id']))
                        Intenta ajustar tus filtros o
                        <a href="{{ route('tickets.index') }}" class="text-blue-600 hover:underline">limpiarlos</a>.
                    @else
                        ¡Parece que todo está en orden! O puedes
                        <a href="{{ route('tickets.create') }}" class="text-blue-600 hover:underline">crear un nuevo ticket</a>.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {!! $tickets->links() !!}
    </div>
</div>
@endsection