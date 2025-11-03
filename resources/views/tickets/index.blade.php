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
                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ticket</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Asignado a</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estatus</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Prioridad</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Últ. Actividad</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 whitespace-nowrap">
                                <a href="{{ route('tickets.show', $ticket) }}" class="font-semibold text-[var(--color-primary)] hover:underline">
                                    {{ $ticket->title }}
                                </a>
                                <div class="text-xs text-[var(--color-text-secondary)] font-mono">#{{ $ticket->id }}</div>
                            </td>

                            <td class="p-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->user->name) }}&color=2c3856&background=e8ecf7&size=32" alt="{{ $ticket->user->name }}" class="h-8 w-8 rounded-full">
                                    <span class="font-medium text-gray-800">{{ $ticket->user->name }}</span>
                                </div>
                            </td>

                            <td class.="p-4 whitespace-nowrap">
                                @if($ticket->agent)
                                    <div class="flex items-center space-x-2">
                                        @if ($ticket->agent->profile_photo_path)
                                            <img src="{{ Storage::disk('s3')->url($ticket->agent->profile_photo_path) }}" alt="{{ $ticket->agent->name }}" class="h-8 w-8 rounded-full object-cover">
                                        @else
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->agent->name) }}&color=e8ecf7&background=2c3856&size=32" alt="{{ $ticket->agent->name }}" class="h-8 w-8 rounded-full">
                                        @endif
                                        <span class="font-medium text-gray-800">{{ $ticket->agent->name }}</span>
                                    </div>
                                @else
                                    <span class="italic text-gray-500">Sin asignar</span>
                                @endif
                            </td>
                            
                            <td class="p-4 whitespace-nowrap">
                                <span class="badge-pill status-{{ strtolower(str_replace(' ', '-', $ticket->status)) }}">
                                    <span class="dot"></span>
                                    {{ $ticket->status }}
                                </span>
                            </td>
                            
                            <td class="p-4 whitespace-nowrap">
                                <span class="badge-pill priority-{{ strtolower($ticket->priority) }}">
                                    @if(strtolower($ticket->priority) == 'alta')
                                        <i class="fas fa-fire-alt opacity-70"></i>
                                    @elseif(strtolower($ticket->priority) == 'media')
                                        <i class="fas fa-exclamation-triangle opacity-70"></i>
                                    @else
                                        <i class="fas fa-check-circle opacity-70"></i>
                                    @endif
                                    {{ $ticket->priority }}
                                </span>
                            </td>
                            
                            <td class="p-4 whitespace-nowrap text-sm text-[var(--color-text-secondary)]">
                                {{ $ticket->updated_at->diffForHumans() }}
                            </td>
                            
                            <td class="p-4 whitespace-nowrap text-sm font-medium">
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600 rounded-full p-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-primary)]">
                                        <i class="fas fa-ellipsis-v"></i>
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
                                            <a href="#" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                                <i class="fas fa-user-plus w-5 mr-2 text-gray-400"></i> Reasignar
                                            </a>
                                            <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este ticket permanentemente?');" class="w-full">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50" role="menuitem">
                                                    <i class...="fas fa-trash-alt w-5 mr-2 text-red-400"></i> Eliminar
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
                            <td colspan="8" class="text-center p-12">
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
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t">
            {!! $tickets->links() !!}
        </div>
    </div>
</div>
@endsection