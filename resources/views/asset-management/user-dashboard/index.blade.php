@extends('layouts.app')

@section('content')
<style>
    :root {
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-primary-dark: #212a41;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        --color-background: #f3f4f6;
        --color-border: #d1d5db;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }
    body { background-color: var(--color-background); }

    .view-toggle button {
        background-color: var(--color-surface);
        border: 1px solid var(--color-border);
        color: var(--color-text-secondary);
        padding: 0.5rem 1rem;
        transition: all 150ms ease;
    }
    .view-toggle button:first-child { border-radius: 0.5rem 0 0 0.5rem; }
    .view-toggle button:last-child { border-radius: 0 0.5rem 0.5rem 0; margin-left: -1px; }
    .view-toggle button:hover { background-color: #f9fafb; color: var(--color-primary); }
    .view-toggle button.active {
        background-color: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
        z-index: 10;
        box-shadow: var(--shadow-sm);
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1.25rem;
        padding-left: 3rem;
        font-size: 1rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        transition: all 150ms ease-in-out;
    }
    .search-input:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(44, 56, 86, 0.1);
        outline: none;
    }

    .user-card {
        background-color: var(--color-surface);
        border-radius: 0.75rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--color-border);
        overflow: hidden;
        transition: all 250ms ease-in-out;
        display: flex;
        flex-direction: column;
    }
    .user-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-5px);
    }
    .user-card-main {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        border-bottom: 1px solid var(--color-border);
        background-color: #fcfdff;
    }
    .user-card-avatar {
        flex-shrink: 0;
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 9999px;
        background-color: var(--color-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
    }
    .user-card-info { margin-left: 1rem; }
    .user-card-name { font-size: 1.125rem; font-weight: 700; color: var(--color-text-primary); }
    .user-card-position { font-size: 0.875rem; color: var(--color-text-secondary); }
    
    .user-card-stats {
        padding: 1.5rem;
        flex-grow: 1;
    }
    .user-card-stats-main {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--color-primary);
        line-height: 1;
    }
    .user-card-stats-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--color-text-secondary);
        text-transform: uppercase;
        margin-top: 0.25rem;
    }
    
    .user-card-assets {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--color-border);
    }
    .user-card-assets-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--color-text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }
    .asset-icon-group {
        display: flex;
        gap: 0.75rem;
    }
    .asset-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        background-color: #f3f4f6;
        color: var(--color-text-secondary);
        position: relative;
    }
    /* Tooltip para iconos */
    [data-tooltip] { position: relative; }
    [data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        background-color: var(--color-text-primary);
        color: white;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 150ms ease;
    }
    [data-tooltip]:hover::after { opacity: 1; visibility: visible; }
    
    .user-card-action {
        padding: 1rem;
        border-top: 1px solid var(--color-border);
        background-color: #f9fafb;
    }

    /* Botones */
    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); transition: all 200ms ease-in-out; transform: translateY(0); border: 1px solid transparent; }
    .btn:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .btn-primary { background-color: var(--color-primary); color: white; }
    .btn-primary:hover { background-color: var(--color-primary-dark); }
    
    .table-list {
        width: 100%;
        background-color: var(--color-surface);
        border-radius: 0.75rem;
        box-shadow: var(--shadow-md);
        overflow: hidden;
        border: 1px solid var(--color-border);
    }
</style>

<div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12" 
     x-data="{ view: localStorage.getItem('userDashboardView') || 'grid' }" 
     x-init="$watch('view', val => localStorage.setItem('userDashboardView', val))">
    
    <header class="mb-8">
        <a href="{{ route('asset-management.dashboard') }}" class="text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-primary)] transition-colors mb-2 inline-block">
            <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
        </a>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Responsivas por Usuario</h1>
                <p class="text-lg text-[var(--color-text-secondary)] mt-1">Explora los colaboradores con activos asignados.</p>
            </div>
            <div class="flex-shrink-0 bg-white border border-gray-200 shadow-md rounded-lg p-4">
                <div class="text-sm font-semibold text-gray-500 uppercase">Total de Usuarios</div>
                <div class="text-4xl font-bold text-[var(--color-primary)] text-right">{{ $members->total() }}</div>
            </div>
        </div>
    </header>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div class="w-full md:w-1/2 lg:w-1/3">
            <form action="{{ route('asset-management.user-dashboard.index') }}" method="GET">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" class="search-input" placeholder="Buscar por nombre o puesto..." value="{{ request('search') }}">
                </div>
            </form>
        </div>
        <div class="view-toggle flex-shrink-0">
            <button type="button" @click="view = 'grid'" :class="{ 'active': view === 'grid' }" data-tooltip="Vista de Cuadrícula">
                <i class="fas fa-th-large"></i>
            </button>
            <button type="button" @click="view = 'list'" :class="{ 'active': view === 'list' }" data-tooltip="Vista de Lista">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>

    <div>
        <div x-show="view === 'grid'" x-transition class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($members as $member)
            <div class="user-card">
                <div class="user-card-main">
                    
                    @if ($member->profile_photo_path)
                        <img src="{{ Storage::disk('s3')->url($member->profile_photo_path) }}" 
                            alt="{{ $member->name }}" 
                            title="{{ $member->name }}"
                            class="user-card-avatar w-[3.5rem] h-[3.5rem] object-cover">
                    @else
                        <div class="user-card-avatar" title="{{ $member->name }}">
                            <span>
                                @php
                                    $parts = explode(' ', $member->name);
                                    $initials = mb_substr($parts[0], 0, 1);
                                    if (count($parts) > 1) {
                                        $initials .= mb_substr(end($parts), 0, 1);
                                    }
                                @endphp
                                {{ $initials }}
                            </span>
                        </div>
                    @endif
                    <div class="user-card-info">
                        <h3 class="user-card-name">{{ $member->name }}</h3>
                        <p class="user-card-position">{{ $member->position->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="user-card-stats">
                    <div class="flex-grow">
                        <div class="user-card-stats-main">{{ $member->assignments_count }}</div>
                        <div class="user-card-stats-label">Activos Asignados</div>
                    </div>
                    
                    @if($member->laptop_count > 0 || $member->phone_count > 0 || $member->monitor_count > 0)
                    <div class="pl-4 border-l border-gray-200">
                        <h4 class="user-card-assets-title">Resumen</h4>
                        <div class="asset-icon-group">
                            @if($member->laptop_count > 0)
                                <span class="asset-icon" data-tooltip="Laptop ({{ $member->laptop_count }})"><i class="fas fa-laptop"></i></span>
                            @endif
                            @if($member->phone_count > 0)
                                <span class="asset-icon" data-tooltip="Celular ({{ $member->phone_count }})"><i class="fas fa-mobile-alt"></i></span>
                            @endif
                            @if($member->monitor_count > 0)
                                <span class="asset-icon" data-tooltip="Monitor/Pantalla ({{ $member->monitor_count }})"><i class="fas fa-tv"></i></span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                
                <div class="user-card-action">
                    <a href="{{ route('asset-management.user-dashboard.show', $member) }}" class="btn btn-primary w-full justify-center">
                        Ver Detalles <i class="fas fa-arrow-right ml-2 text-xs"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="md:col-span-2 lg:col-span-3 text-center py-16 bg-white rounded-lg shadow-md border">
                <i class="fas fa-inbox text-6xl text-gray-300"></i>
                <h3 class="mt-4 text-2xl font-bold text-gray-700">No se encontraron usuarios</h3>
                <p class="text-gray-500 mt-1">
                    @if(request('search'))
                        Intenta ajustar tus términos de búsqueda.
                    @else
                        No hay usuarios con activos asignados actualmente.
                    @endif
                </p>
            </div>
            @endforelse
        </div>

        <div x-show="view === 'list'" x-transition class="table-list">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre del Colaborador</th>
                        <th class="p-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Puesto</th>
                        <th class="p-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Activos Asignados</th>
                        <th class="p-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($members as $member)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="p-4">
                            <div class="font-semibold text-gray-900">{{ $member->name }}</div>
                        </td>
                        <td class="p-4 text-gray-600">{{ $member->position->name ?? 'N/A' }}</td>
                        <td class="p-4 text-center">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-bold text-sm">{{ $member->assignments_count }}</span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('asset-management.user-dashboard.show', $member) }}" class="font-semibold text-[var(--color-primary)] hover:underline">
                                Ver Detalles <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
                            <p class="font-semibold">No se encontraron usuarios</p>
                            <p>
                                @if(request('search'))
                                    Intenta ajustar tus términos de búsqueda.
                                @else
                                    No hay usuarios con activos asignados actualmente.
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($members->hasPages())
        <div class="mt-12">
            {!! $members->appends(request()->query())->links() !!}
        </div>
    @endif
</div>
@endsection