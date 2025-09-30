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
    }
    .status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
    .status-asignado { background-color: #3B82F6; color: white; }
    .status-en-almacén { background-color: #10B981; color: white; }
    .status-en-reparación { background-color: #F59E0B; color: white; }
    .status-prestado { background-color: #8B5CF6; color: white; }
    .status-de-baja { background-color: #6B7280; color: white; }
</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <header class="flex flex-col sm:flex-row items-center justify-between mb-8">
        <div>
            <h1 class="text-4xl font-bold text-[var(--color-text-primary)]">Dashboard de Activos de TI</h1>
            <p class="text-gray-600 mt-2">Vista general del inventario de hardware de la organización.</p>
        </div>
        <div class="flex items-center space-x-2 mt-4 sm:mt-0">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="btn bg-gray-600 text-white">
                    <i class="fas fa-cog mr-2"></i> Configuración
                </button>
                <div x-show="open" @click.away="open = false" 
                    x-transition
                    class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl z-10">
                    <a href="{{ route('asset-management.sites.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Gestionar Sitios</a>
                    <a href="{{ route('asset-management.manufacturers.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Gestionar Fabricantes</a>
                    <a href="{{ route('asset-management.categories.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Gestionar Categorías</a>
                    <a href="{{ route('asset-management.models.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Gestionar Modelos</a>
                    <a href="{{ route('asset-management.software-licenses.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Gestionar Software</a>    
                </div>
            </div>

            <a href="{{ route('asset-management.assets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Registrar Nuevo Activo
            </a>
        </div>
    </header>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="bg-blue-100 p-3 rounded-full"><i class="fas fa-desktop text-2xl text-blue-500"></i></div>
            <div>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                <p class="text-gray-500">Activos Totales</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="bg-green-100 p-3 rounded-full"><i class="fas fa-check-circle text-2xl text-green-500"></i></div>
            <div>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['assigned'] }}</p>
                <p class="text-gray-500">Asignados</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="bg-indigo-100 p-3 rounded-full"><i class="fas fa-warehouse text-2xl text-indigo-500"></i></div>
            <div>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['in_stock'] }}</p>
                <p class="text-gray-500">En Almacén</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="bg-yellow-100 p-3 rounded-full"><i class="fas fa-tools text-2xl text-yellow-500"></i></div>
            <div>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['in_repair'] }}</p>
                <p class="text-gray-500">En Reparación</p>
            </div>
        </div>
    </div>

    {{-- Filtros y Tabla --}}
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <form action="{{ route('asset-management.dashboard') }}" method="GET" class="mb-4">
            <div class="flex items-center">
                <input type="text" name="search" placeholder="Buscar por etiqueta, serie, modelo o usuario..." value="{{ $filters['search'] ?? '' }}" class="form-input w-full md:w-1/3">
                <button type="submit" class="btn btn-primary ml-2">Buscar</button>
                 <a href="{{ route('asset-management.dashboard') }}" class="btn bg-gray-200 text-gray-700 hover:bg-gray-300 ml-2">Limpiar</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[var(--color-primary)] text-white">
                    <tr>
                        <th class="p-4 font-semibold text-left">Etiqueta</th>
                        <th class="p-4 font-semibold text-left">Categoría</th>
                        <th class="p-4 font-semibold text-left">Modelo</th>
                        <th class="p-4 font-semibold text-left">Estatus</th>
                        <th class="p-4 font-semibold text-left">Asignado a</th>
                        <th class="p-4 font-semibold text-left">Ubicación</th>
                        <th class="p-4 font-semibold text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($assets as $asset)
                        <tr class="hover:bg-gray-50">
                            <td class="p-4 font-mono text-blue-600">{{ $asset->asset_tag }}</td>
                            <td class="p-4">{{ $asset->model->category->name ?? 'N/A' }}</td>
                            <td class="p-4 font-semibold">{{ $asset->model->name ?? 'N/A' }}</td>
                            <td class="p-4"><span class="status-badge status-{{ Str::slug($asset->status) }}">{{ $asset->status }}</span></td>
                            <td class="p-4">{{ $asset->currentAssignment->member->name ?? '---' }}</td>
                            <td class="p-4">{{ $asset->site->name ?? 'N/A' }}</td>
                            <td class="p-4">
                                <a href="{{ route('asset-management.assets.show', $asset) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Ver</a>
                                <a href="{{ route('asset-management.assets.edit', $asset) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold ml-4">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center p-8 text-gray-500">No se encontraron activos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t">
            {!! $assets->links() !!}
        </div>
    </div>
</div>
@endsection