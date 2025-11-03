@extends('layouts.app')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Raleway:wght@700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        --color-background: #f3f4f6;
        --color-border: #e5e7eb;
        --font-heading: 'Raleway', sans-serif;
        --font-body: 'Montserrat', sans-serif;
        --color-status-abierto-bg: #e0e7ff;
        --color-status-abierto-text: #3730a3;
        --color-status-proceso-bg: #f3e8ff;
        --color-status-proceso-text: #7e22ce;
        --color-status-pendiente-bg: #fffbeb;
        --color-status-pendiente-text: #b45309;
        --color-status-cerrado-bg: #f3f4f6;
        --color-status-cerrado-text: #4b5563;
        --color-priority-alta: #EF4444;
        --color-priority-media: #F59E0B;
        --color-priority-baja: #10B981;
    }

    body {
        background-color: var(--color-background);
        font-family: var(--font-body);
        color: var(--color-text-primary);
    }
    
    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; font-family: var(--font-body); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; transition: all 0.2s ease; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); }
    .btn-sm { padding: 0.5rem 1rem; font-size: 0.7rem; }
    
    .btn-accent { background-color: var(--color-accent); color: var(--color-primary); }
    .btn-accent:hover { background-color: #ffb03a; box-shadow: 0 4px 10px -3px rgba(255,156,0,0.4); transform: translateY(-2px); }
    
    .btn-primary { background-color: var(--color-primary); color: var(--color-surface); }
    .btn-primary:hover { background-color: #1e263b; box-shadow: 0 4px 10px -3px rgba(44,56,86,0.4); transform: translateY(-2px); }
    
    .btn-secondary { background-color: var(--color-surface); color: var(--color-text-secondary); border: 1px solid var(--color-border); }
    .btn-secondary:hover { background-color: #f9fafb; border-color: #d1d5db; }
    
    .form-input-sm { font-family: var(--font-body); font-size: 0.875rem; padding: 0.5rem 0.75rem; border-radius: 0.375rem; border: 1px solid var(--color-border); }
    .form-input-sm:focus { border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--color-primary); outline: none; }

    .badge-pill {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.7rem;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    .status-abierto { background-color: var(--color-status-abierto-bg); color: var(--color-status-abierto-text); }
    .status-en-proceso { background-color: var(--color-status-proceso-bg); color: var(--color-status-proceso-text); }
    .status-pendiente-de-aprobación { background-color: var(--color-status-pendiente-bg); color: var(--color-status-pendiente-text); }
    .status-cerrado { background-color: var(--color-status-cerrado-bg); color: var(--color-status-cerrado-text); }

    .list-view-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }
    
    .list-view-table th {
        padding: 0 1rem 0.75rem 1rem;
        text-align: left;
        font-family: var(--font-body);
        font-size: 0.7rem;
        color: var(--color-text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }
    
    .list-view-table tr.table-row {
        background-color: var(--color-surface);
        box-shadow: 0 1px 3px 0 rgba(0,0,0,0.03), 0 1px 2px -1px rgba(0,0,0,0.03);
        transition: all 0.2s ease-in-out;
        border-radius: 0.5rem;
    }
    
    .list-view-table tr.table-row:hover {
        box-shadow: 0 4px 12px -3px rgba(44,56,86,0.1);
        transform: translateY(-3px);
    }
    
    .list-view-table td {
        padding: 1rem;
        vertical-align: middle;
        font-size: 0.875rem;
        color: var(--color-text-primary);
    }
    .list-view-table td:first-child { border-top-left-radius: 0.5rem; border-bottom-left-radius: 0.5rem; }
    .list-view-table td:last-child { border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }

    .ticket-title {
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 1rem;
        color: var(--color-primary);
        transition: color 0.2s ease;
    }
    .ticket-title:hover {
        color: var(--color-accent);
    }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{ 
         filtersOpen: {{ request()->hasAny(['search', 'status', 'priority', 'category_id', 'agent_id']) ? 'true' : 'false' }},
         reassignModalOpen: false,
         reassignTicketId: null
     }">

    <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8">
        <div>
            <h1 class="text-4xl font-bold text-[var(--color-primary)] tracking-tight" style="font-family: var(--font-heading);">Centro de Soporte</h1>
            <p class="text-lg text-[var(--color-text-secondary)] mt-1">Gestiona todas las solicitudes de TI en un solo lugar.</p>
        </div>
        <div class="flex items-center space-x-2 mt-4 sm:mt-0 flex-shrink-0">
            @if(in_array(Auth::user()->area?->name, ['Administración', 'Innovación y Desarrollo']))
                <a href="{{ route('tickets.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-chart-bar mr-2"></i> Indicadores
                </a>
                <a href="{{ route('asset-management.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-desktop mr-2"></i> Activos
                </a>
            @endif
            <a href="{{ route('tickets.create') }}" class="btn btn-accent">
                <i class="fas fa-plus mr-2"></i> Crear Ticket
            </a>
        </div>
    </header>

    <div class="bg-white p-4 rounded-xl shadow-sm mb-4" x-data="{ filtersOpen: {{ request()->hasAny(['search', 'status', 'priority', 'category_id', 'agent_id']) ? 'true' : 'false' }} }">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-[var(--color-primary)]" style="font-family: var(--font-heading);">Filtros</h3>
            <button @click="filtersOpen = !filtersOpen" class="btn btn-sm btn-secondary">
                <span x-show="!filtersOpen"><i class="fas fa-filter mr-2"></i> Mostrar</span>
                <span x-show="filtersOpen"><i class="fas fa-times mr-2"></i> Ocultar</span>
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
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-secondary">Limpiar</a>
                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="list-view-table">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Usuario</th>
                    <th>Asignado a</th>
                    <th>Estatus</th>
                    <th>Prioridad</th>
                    <th>Últ. Actividad</th>
                    <th class="w-12"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr class="table-row">
                        <td class="p-4 whitespace-nowrap">
                            <a href="{{ route('tickets.show', $ticket) }}" class="ticket-title">
                                {{ $ticket->title }}
                            </a>
                            <div class="text-xs text-[var(--color-text-secondary)] font-mono mt-1">#{{ $ticket->id }} &middot; {{ $ticket->subCategory->category->name ?? 'N/A' }}</div>
                        </td>

                        <td class="p-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                @if ($ticket->user->profile_photo_path)
                                    <img src="{{ Storage::disk('s3')->url($ticket->user->profile_photo_path) }}" alt="{{ $ticket->user->name }}" class="h-8 w-8 rounded-full object-cover">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->user->name) }}&color=2c3856&background=e8ecf7&size=32" alt="{{ $ticket->user->name }}" class="h-8 w-8 rounded-full">
                                @endif
                                <span class="font-medium text-gray-800">{{ $ticket->user->name }}</span>
                            </div>
                        </td>

                        <td class="p-4 whitespace-nowrap">
                            @if($ticket->agent)
                                <div class="flex items-center space-x-2">
                                    @if ($ticket->agent->profile_photo_path)
                                        <img src="{{ Storage::disk('s3')->url($ticket->agent->profile_photo_path) }}" alt="{{ $ticket->agent->name }}" class="h-8 w-8 rounded-full object-cover">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->agent->name) }}&color=ffffff&background=2c3856&size=32" alt="{{ $ticket->agent->name }}" class="h-8 w-8 rounded-full">
                                    @endif
                                    <span class="font-medium text-gray-800">{{ $ticket->agent->name }}</span>
                                </div>
                            @else
                                <span class="italic text-gray-400">Sin asignar</span>
                            @endif
                        </td>
                        
                        <td class="p-4 whitespace-nowrap">
                            <span class="badge-pill status-{{ strtolower(str_replace(' ', '-', $ticket->status)) }}">
                                {{ $ticket->status }}
                            </span>
                        </td>
                        
                        <td class.="p-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-2 font-medium" style="color: var(--color-priority-{{ strtolower($ticket->priority) }});">
                                @if(strtolower($ticket->priority) == 'alta')
                                    <i class="fas fa-arrow-up"></i>
                                @elseif(strtolower($ticket->priority) == 'media')
                                    <i class="fas fa-arrow-right"></i>
                                @else
                                    <i class="fas fa-arrow-down"></i>
                                @endif
                                {{ $ticket->priority }}
                            </span>
                        </td>
                        
                        <td class.="p-4 whitespace-nowrap text-sm text-[var(--color-text-secondary)]">
                            {{ $ticket->updated_at->diffForHumans() }}
                        </td>
                        
                        <td class="p-4 whitespace-nowrap text-sm font-medium">
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-gray-400 hover:text-gray-600 rounded-full p-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-primary)]">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div x-show="open"
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-10 right-0 w-48 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                     x-cloak>
                                    <div class="py-1" role="menu" aria-orientation="vertical">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            <i class="fas fa-eye w-5 mr-2 text-gray-400"></i> Ver Ticket
                                        </a>
                                        @if(Auth::user()->isSuperAdmin())
                                        <button @click="reassignTicketId = {{ $ticket->id }}; reassignModalOpen = true; open = false;" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                            <i class="fas fa-user-plus w-5 mr-2 text-gray-400"></i> Reasignar
                                        </button>
                                        <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este ticket permanentemente?');" class="w-full">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class.="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50" role="menuitem">
                                                <i class="fas fa-trash-alt w-5 mr-2 text-red-400"></i> Eliminar
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="rounded-lg bg-white text-center p-12 my-4">
                                <i class="fas fa-ticket-alt text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-xl font-semibold text-gray-700" style="font-family: var(--font-heading);">No se encontraron tickets</h3>
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
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {!! $tickets->links() !!}
    </div>

    <div x-show="reassignModalOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed z-50 inset-0 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="reassignModalOpen = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="reassignModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <form x-bind:action="reassignTicketId ? '{{ url('tickets') }}/' + reassignTicketId + '/assign' : '#'" method="POST">
                    @csrf
                    <div class.="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-user-plus text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title" style="font-family: var(--font-heading);">
                                    Reasignar Ticket
                                </h3>
                                <div class="mt-4 w-full">
                                    <label for="agent_id" class="block text-sm font-medium text-gray-700">Selecciona un agente:</label>
                                    <select name="agent_id" class="form-input-sm w-full mt-1" required>
                                        <option value="">-- Seleccionar Agente --</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="btn btn-primary w-full sm:w-auto sm:ml-3">
                            Asignar
                        </button>
                        <button type="button" @click="reassignModalOpen = false" class="btn btn-secondary w-full mt-3 sm:w-auto sm:mt-0">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div> @endsection