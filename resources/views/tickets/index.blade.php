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
        --color-border: #e5e7eb;
        --color-priority-baja: #10B981;
        --color-priority-media: #F59E0B;
        --color-priority-alta: #EF4444;
        --color-status-abierto: #3B82F6;
        --color-status-proceso: #A855F7;
        --color-status-pendiente: #F59E0B;
        --color-status-cerrado: #6B7280;
    }
    body { background-color: var(--color-background); }
    
    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; transition: all 0.2s ease; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); }
    .btn-sm { padding: 0.5rem 1rem; font-size: 0.7rem; }
    .btn-accent { background-color: var(--color-accent); color: var(--color-primary); }
    .btn-accent:hover { background-color: #ffb03a; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transform: translateY(-1px); }
    .btn-primary { background-color: var(--color-primary); color: var(--color-surface); }
    .btn-primary:hover { background-color: #1e263b; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transform: translateY(-1px); }
    .form-input-sm { font-size: 0.875rem; padding: 0.5rem 0.75rem; border-radius: 0.375rem; border: 1px solid var(--color-border); }
    .form-input-sm:focus { border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--color-primary); outline: none; }
    .form-checkbox { border-radius: 0.25rem; border-color: var(--color-border); color: var(--color-primary); }
    .form-checkbox:focus { border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--color-primary); }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
    }
    .modern-table th {
        padding: 0.75rem 1.5rem;
        text-align: left;
        font-size: 0.75rem;
        color: var(--color-text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .modern-table th:hover { color: var(--color-text-primary); cursor: pointer; }
    .modern-table tr.table-row {
        background-color: var(--color-surface);
        box-shadow: 0 1px 3px 0 rgba(0,0,0,0.07), 0 1px 2px -1px rgba(0,0,0,0.07);
        transition: all 0.2s ease-in-out;
    }
    .modern-table tr.table-row:hover {
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .modern-table td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        font-size: 0.875rem;
        color: var(--color-text-primary);
    }
    .modern-table td:first-child { border-top-left-radius: 0.5rem; border-bottom-left-radius: 0.5rem; }
    .modern-table td:last-child { border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }

    .badge-pill {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.7rem;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }
    
    .status-abierto { background-color: #DBEAFE; color: #1D4ED8; }
    .status-abierto .dot { background-color: var(--color-status-abierto); }
    .status-en-proceso { background-color: #F3E8FF; color: #7E22CE; }
    .status-en-proceso .dot { background-color: var(--color-status-proceso); }
    .status-pendiente-de-aprobación { background-color: #FEF3C7; color: #B45309; }
    .status-pendiente-de-aprobación .dot { background-color: var(--color-status-pendiente); }
    .status-cerrado { background-color: #F3F4F6; color: #4B5563; }
    .status-cerrado .dot { background-color: var(--color-status-cerrado); }
    .dot { width: 6px; height: 6px; border-radius: 9999px; }

    .priority-baja { background-color: #D1FAE5; color: #065F46; }
    .priority-media { background-color: #FEF3C7; color: #B45309; }
    .priority-alta { background-color: #FEE2E2; color: #B91C1C; }
</style>

<div class.="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{ 
         selectedTickets: [], 
         selectAll: false, 
         allTicketIds: {{ $tickets->pluck('id')->toJson() }},
         toggleSelectAll() {
             this.selectedTickets = this.selectAll ? [ ...this.allTicketIds ] : [];
         }
     }">

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
                <a href="{{ route('asset-management.dashboard') }}" class="btn bg-white text-[var(--color-primary)] border border-gray-300 hover:bg-gray-50">
                    <i class="fas fa-desktop mr-2"></i> Activos
                </a>
            @endif
            <a href="{{ route('tickets.create') }}" class="btn btn-accent">
                <i class="fas fa-plus mr-2"></i> Crear Ticket
            </a>
        </div>
    </header>

    <div class="bg-white p-4 rounded-xl shadow-lg mb-4" x-data="{ filtersOpen: {{ request()->hasAny(['search', 'status', 'priority', 'category_id', 'agent_id']) ? 'true' : 'false' }} }">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-[var(--color-primary)]">Filtros</h3>
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
                <button type..." class="btn btn-sm btn-primary">Filtrar</button>
            </div>
        </form>
    </div>

    <div x-show="selectedTickets.length > 0" x-transition
         class="bg-blue-900 text-white p-4 rounded-xl shadow-lg mb-4 flex items-center justify-between" x-cloak>
        <span class="font-semibold text-lg">
            <span x-text="selectedTickets.length"></span> ticket(s) seleccionado(s)
        </span>
        <div class="flex items-center space-x-2">
            <button class="btn btn-sm bg-blue-500 hover:bg-blue-400 text-white"><i class="fas fa-user-plus mr-2"></i> Asignar</button>
            <button class="btn btn-sm bg-yellow-500 hover:bg-yellow-400 text-black"><i class="fas fa-exchange-alt mr-2"></i> Cambiar Estatus</button>
            <button class="btn btn-sm bg-red-600 hover:bg-red-500 text-white"><i class="fas fa-trash-alt mr-2"></i> Eliminar</button>
        </div>
    </div>


    <div class="overflow-x-auto">
        <table class="modern-table">
            <thead>
                <tr>
                    <th class="w-12">
                        <input type="checkbox" class="form-checkbox"
                               x-model="selectAll"
                               @click="toggleSelectAll()">
                    </th>
                    <th>Ticket <i class="fas fa-sort-down ml-1 text-gray-300"></i></th>
                    <th>Usuario</th>
                    <th>Asignado a</th>
                    <th>Estatus</th>
                    <th>Prioridad</th>
                    <th>Últ. Actividad</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr class="table-row">
                        <td>
                            <input type="checkbox" class="form-checkbox"
                                   x-model="selectedTickets"
                                   value="{{ $ticket->id }}">
                        </td>
                        
                        <td>
                            <a href="{{ route('tickets.show', $ticket) }}" class="font-semibold text-[var(--color-primary)] hover:underline">
                                {{ $ticket->title }}
                            </a>
                            <div class="text-xs text-[var(--color-text-secondary)] font-mono">#{{ $ticket->id }}</div>
                        </td>

                        <td class="w-48">
                            <div class="flex items-center space-x-2">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->user->name) }}&color=2c3856&background=e8ecf7&size=32" alt="{{ $ticket->user->name }}" class="h-8 w-8 rounded-full">
                                <span>{{ $ticket->user->name }}</span>
                            </div>
                        </td>

                        <td class="w-48">
                            @if($ticket->agent)
                                <div class="flex items-center space-x-2">
                                    @if ($ticket->agent->profile_photo_path)
                                        <img src="{{ Storage::disk('s3')->url($ticket->agent->profile_photo_path) }}" alt="{{ $ticket->agent->name }}" class="h-8 w-8 rounded-full object-cover">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->agent->name) }}&color=e8ecf7&background=2c3856&size=32" alt="{{ $ticket->agent->name }}" class="h-8 w-8 rounded-full">
                                    @endif
                                    <span>{{ $ticket->agent->name }}</span>
                                </div>
                            @else
                                <span class="italic text-gray-500">Sin asignar</span>
                            @endif
                        </td>
                        
                        <td class="w-48">
                            <span class="badge-pill status-{{ strtolower(str_replace(' ', '-', $ticket->status)) }}">
                                <span class..."dot"></span>
                                {{ $ticket->status }}
                            </span>
                        </td>
                        
                        <td class="w-32">
                            <span class="badge-pill priority-{{ strtolower($ticket->priority) }}">
                                @if(strtolower($ticket->priority) == 'alta')
                                    <i class="fas fa-fire-alt"></i>
                                @elseif(strtolower($ticket->priority) == 'media')
                                    <i class="fas fa-exclamation-triangle"></i>
                                @else
                                    <i class="fas fa-check-circle"></i>
                                @endif
                                {{ $ticket->priority }}
                            </span>
                        </td>
                        
                        <td class="w-40 text-sm text-[var(--color-text-secondary)]">
                            {{ $ticket->updated_at->diffForHumans() }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class.="bg-white rounded-xl shadow-lg p-12 text-center">
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
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">
        {!! $tickets->links() !!}
    </div>
</div>
@endsection