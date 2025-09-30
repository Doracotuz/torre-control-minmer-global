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
    body { background-color: var(--color-background); }
    .btn { padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); }
    .btn-sm { padding: 0.5rem 1rem; font-size: 0.7rem; }
    .btn-accent { background-color: var(--color-accent); color: var(--color-surface); }
    .btn-accent:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1); }
    .btn-primary { background-color: var(--color-primary); color: var(--color-surface); }
    .badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
    .badge-baja { background-color: var(--color-success); color: white; }
    .badge-media { background-color: var(--color-warning); color: white; }
    .badge-alta { background-color: var(--color-danger); color: white; }
    .status-abierto, .status-pendiente-de-aprobación { color: var(--color-primary); font-weight: 700; }
    .status-en-proceso { color: var(--color-accent); font-weight: 700; }
    .status-cerrado { color: var(--color-text-secondary); font-weight: 700; }
    .form-input-sm { font-size: 0.875rem; padding: 0.5rem 0.75rem; border-radius: 0.375rem; border-color: #e5e7eb; }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <header class="flex flex-col sm:flex-row items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-[var(--color-text-primary)]">Tickets de Soporte IT</h1>
        <div class="flex items-center space-x-2 mt-4 sm:mt-0">
            <a href="{{ route('tickets.dashboard') }}" class="btn bg-white text-[var(--color-primary)] border border-[var(--color-primary)] hover:bg-[var(--color-primary)] hover:text-white transition-colors duration-300">
                <i class="fas fa-chart-bar mr-2"></i> Ver Indicadores
            </a>
            
            <a href="{{ route('asset-management.dashboard') }}" class="btn bg-white text-[var(--color-primary)] border border-[var(--color-primary)] hover:bg-[var(--color-primary)] hover:text-white transition-colors duration-300">
                <i class="fas fa-desktop mr-2"></i> Gestionar Activos
            </a>
            <a href="{{ route('tickets.create') }}" class="btn btn-accent">
                <i class="fas fa-plus mr-2"></i> Crear Ticket
            </a>
        </div>
    </header>

    <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
        <form action="{{ route('tickets.index') }}" method="GET">
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
            <table class="w-full text-sm">
                <thead class="bg-[var(--color-primary)] text-white">
                    <tr>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">ID</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Título</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Usuario</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Asignado a</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Categoría</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Estatus</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Prioridad</th>
                        <th scope="col" class="p-4 font-semibold text-left uppercase tracking-wider">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location='{{ route('tickets.show', $ticket) }}';">
                            <td class="p-4 font-mono text-[var(--color-text-secondary)]">#{{ $ticket->id }}</td>
                            <td class="p-4 font-bold text-[var(--color-text-primary)]">{{ $ticket->title }}</td>
                            <td class="p-4 text-[var(--color-text-secondary)]">{{ $ticket->user->name }}</td>
                            <td class="p-4 text-[var(--color-text-secondary)]">{{ $ticket->agent->name ?? 'Sin asignar' }}</td>
                            <td class="p-4 text-[var(--color-text-secondary)]">{{ $ticket->subCategory->category->name ?? 'Sin categoría' }}</td>
                            <td class="p-4">
                                <span class="status-{{ strtolower(str_replace(' ', '-', $ticket->status)) }}">{{ $ticket->status }}</span>
                            </td>
                            <td class="p-4">
                                <span class="badge badge-{{ strtolower($ticket->priority) }}">{{ $ticket->priority }}</span>
                            </td>
                            <td class="p-4 text-[var(--color-text-secondary)]">{{ $ticket->created_at->format('d/m/Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center p-8 text-gray-500">No se encontraron tickets.</td></tr>
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