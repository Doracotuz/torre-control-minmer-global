@extends('layouts.app')

@section('content')

{{-- 
    Bloque de estilos completo para el dashboard y componentes.
    Incluye la paleta de colores, estilos para formularios, botones, badges y la estética general.
--}}
<style>
    :root {
        /* Tu paleta de colores */
        --color-primary: #2c3856;
        --color-accent: #ff9c00;
        --color-text-primary: #2b2b2b;
        --color-text-secondary: #666666;
        --color-surface: #ffffff;
        
        /* Colores de apoyo */
        --color-primary-dark: #212a41; /* Versión oscurecida para hover */
        --color-background: #f3f4f6;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }

    body {
        background-color: var(--color-background);
    }

    /* Estilos para formularios (Inputs, Selects, Textareas) */
    .form-input, .form-select, .form-textarea {
        border-radius: 0.5rem;
        border-color: #d1d5db;
        transition: all 150ms ease-in-out;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        --tw-ring-color: var(--color-primary);
        border-color: var(--color-primary);
        box-shadow: 0 0 0 2px var(--tw-ring-color);
    }
    label.form-label {
        font-weight: 600;
        color: var(--color-text-primary);
        margin-bottom: 0.5rem;
        display: block;
    }
    
    /* Botones */
    .btn {
        padding: 0.65rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-sm);
        transition: all 200ms ease-in-out;
        transform: translateY(0);
        border: 1px solid transparent; /* Añadido para consistencia */
    }
    .btn:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    .btn-primary {
        background-color: var(--color-primary);
        color: white;
    }
    .btn-primary:hover {
        background-color: var(--color-primary-dark);
    }
    .btn-secondary {
        background-color: var(--color-surface);
        color: var(--color-text-secondary);
        border-color: #d1d5db;
    }
    .btn-secondary:hover {
        background-color: #f9fafb;
    }

    /* Badges de Estado */
    .status-badge { 
        padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; 
        font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .status-en-almacen { background-color: #10B981; color: white; }
    .status-asignado { background-color: #3B82F6; color: white; }
    .status-en-reparacion { background-color: var(--color-accent); color: white; }
    .status-prestado { background-color: #8B5CF6; color: white; }
    .status-de-baja { background-color: var(--color-text-secondary); color: white; }

    /* Animaciones y Transiciones */
    .transition-all { transition-property: all; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 300ms; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .kpi-card { animation: fadeIn 0.5s ease-out forwards; }

    /* Estilo de la tabla */
    .table-container { overflow: hidden; border-radius: 0.75rem; box-shadow: var(--shadow-md); }
    .table-header th { background-color: #2c3856; color: #ffffff; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; }
    .table-row:hover { background-color: #eff6ff; }

    @media (max-width: 767px) {
        /* Oculta la cabecera de la tabla en móvil */
        .responsive-table-header {
            display: none;
        }
        /* Estilo de la tarjeta para cada registro */
        .asset-card {
            display: block;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: var(--color-surface);
            box-shadow: var(--shadow-sm);
        }
        /* Contenedor para cada fila de datos dentro de la tarjeta */
        .asset-card-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .asset-card-row:last-child {
            border-bottom: none;
        }
        /* Etiqueta del dato (ej. "Estatus:") */
        .asset-card-label {
            font-weight: 600;
            color: var(--color-text-secondary);
        }
        /* Valor del dato */
        .asset-card-value {
            text-align: right;
            color: var(--color-text-primary);
        }
    }

</style>

<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    {{-- Encabezado --}}
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10">
        <div>
            <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Dashboard de Activos</h1>
            <p class="text-[var(--color-text-secondary)] mt-2">Vista general y filtrado del inventario de hardware.</p>
        </div>
        <div class="flex items-center space-x-3 mt-4 sm:mt-0">
            {{-- Menú de Configuración (Dropdown) --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="btn btn-secondary">
                    <i class="fas fa-cog mr-2"></i> Configuración <i class="fas fa-chevron-down ml-2 text-xs transition-transform" :class="{'rotate-180': open}"></i>
                </button>
                <div x-show="open" 
                     @click.away="open = false" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl z-20 overflow-hidden border border-gray-200" style="display: none;">
                    <a href="{{ route('asset-management.sites.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Sitios</a>
                    <a href="{{ route('asset-management.manufacturers.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Fabricantes</a>
                    <a href="{{ route('asset-management.categories.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Categorías</a>
                    <a href="{{ route('asset-management.models.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Modelos</a>
                    <a href="{{ route('asset-management.software-licenses.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all border-t border-gray-100">Gestionar Software</a>    
                </div>
            </div>
            {{-- Botón para Añadir Activo --}}
            <a href="{{ route('asset-management.assets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Registrar Activo
            </a>
        </div>
    </header>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="kpi-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-all hover:shadow-xl hover:scale-105">
            <div class="bg-blue-100 p-4 rounded-full"><i class="fas fa-desktop text-2xl text-blue-500"></i></div>
            <div><p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p><p class="text-gray-500 text-sm font-medium">Activos Totales</p></div>
        </div>
        <div class="kpi-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-all hover:shadow-xl hover:scale-105" style="animation-delay: 0.1s;">
            <div class="bg-green-100 p-4 rounded-full"><i class="fas fa-check-circle text-2xl text-green-500"></i></div>
            <div><p class="text-3xl font-bold text-gray-800">{{ $stats['assigned'] }}</p><p class="text-gray-500 text-sm font-medium">Asignados</p></div>
        </div>
        <div class="kpi-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-all hover:shadow-xl hover:scale-105" style="animation-delay: 0.2s;">
            <div class="bg-indigo-100 p-4 rounded-full"><i class="fas fa-warehouse text-2xl text-indigo-500"></i></div>
            <div><p class="text-3xl font-bold text-gray-800">{{ $stats['in_stock'] }}</p><p class="text-gray-500 text-sm font-medium">En Almacén</p></div>
        </div>
        <div class="kpi-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4 transition-all hover:shadow-xl hover:scale-105" style="animation-delay: 0.3s;">
            <div class="bg-orange-100 p-4 rounded-full"><i class="fas fa-tools text-2xl text-[var(--color-accent)]"></i></div>
            <div><p class="text-3xl font-bold text-gray-800">{{ $stats['in_repair'] }}</p><p class="text-gray-500 text-sm font-medium">En Reparación</p></div>
        </div>
    </div>

    {{-- Filtros y Tabla --}}
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <form action="{{ route('asset-management.dashboard') }}" method="GET" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                {{-- Barra de Búsqueda --}}
                <div class="lg:col-span-2">
                    <label for="search" class="form-label text-xs">Buscar Activo</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fas fa-search text-gray-400"></i></span>
                        <input type="text" id="search" name="search" placeholder="Etiqueta, serie, modelo..." value="{{ $filters['search'] ?? '' }}" class="form-input w-full pl-10">
                    </div>
                </div>
                {{-- Filtro de Estatus --}}
                <div>
                    <label for="status" class="form-label text-xs">Estatus</label>
                    <select name="status" id="status" class="form-select w-full">
                        <option value="">Todos</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') == $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Filtro de Sitio/Ubicación --}}
                <div>
                    <label for="site_id" class="form-label text-xs">Ubicación</label>
                    <select name="site_id" id="site_id" class="form-select w-full">
                        <option value="">Todas</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" @selected(request('site_id') == $site->id)>{{ $site->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Filtro de Categoría --}}
                <div>
                    <label for="category_id" class="form-label text-xs">Categoría</label>
                    <select name="category_id" id="category_id" class="form-select w-full">
                        <option value="">Todas</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            {{-- Botones de Acción --}}
            <div class="mt-4 flex items-center space-x-3 border-t pt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter mr-2"></i>Aplicar Filtros
                </button>
                <a href="{{ route('asset-management.dashboard') }}" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>

        {{-- Tabla de Activos --}}
        <div class="table-container border rounded-lg overflow-hidden">
            {{-- Cabecera visible solo en Escritorio (md en adelante) --}}
            <div class="responsive-table-header hidden md:grid md:grid-cols-7 gap-4 bg-[#2c3856] p-4 font-bold text-xs text-[#ffffff] uppercase tracking-wider">
                <div class="col-span-1">Etiqueta</div>
                <div class="col-span-1">Categoría</div>
                <div class="col-span-1">Modelo</div>
                <div class="col-span-1">Estatus</div>
                <div class="col-span-1">Asignado a</div>
                <div class="col-span-1">Ubicación</div>
                <div class="col-span-1 text-right">Acciones</div>
            </div>

            {{-- Cuerpo de la Tabla / Contenedor de Tarjetas --}}
            <div class="divide-y md:divide-y-0">
                @forelse ($assets as $asset)
                    {{-- Vista de Fila para Escritorio --}}
                    <div class="hidden md:grid md:grid-cols-7 gap-4 p-4 items-center hover:bg-gray-50 transition-colors">
                        <div><a href="{{ route('asset-management.assets.show', $asset) }}" class="font-mono text-[var(--color-primary)] hover:underline font-semibold">{{ $asset->asset_tag }}</a></div>
                        <div class="text-gray-600">{{ $asset->model->category->name ?? 'N/A' }}</div>
                        <div class="font-semibold text-gray-800">{{ $asset->model->name ?? 'N/A' }}</div>
                        <div><span class="status-badge status-{{ Str::kebab($asset->status) }}">{{ $asset->status }}</span></div>
                        <div class="text-gray-600">{{ $asset->currentAssignment->member->name ?? '---' }}</div>
                        <div class="text-gray-600">{{ $asset->site->name ?? 'N/A' }}</div>
                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('asset-management.assets.show', $asset) }}" title="Ver Detalles"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('asset-management.assets.edit', $asset) }}" title="Editar Activo"><i class="fas fa-pencil-alt"></i></a>
                        </div>
                    </div>

                    {{-- Vista de Tarjeta para Móvil (hasta md) --}}
                    <div class="asset-card md:hidden">
                        <div class="asset-card-row">
                            <span class="asset-card-label">Etiqueta</span>
                            <span class="asset-card-value"><a href="{{ route('asset-management.assets.show', $asset) }}" class="font-mono text-[var(--color-primary)] hover:underline font-semibold">{{ $asset->asset_tag }}</a></span>
                        </div>
                        <div class="asset-card-row">
                            <span class="asset-card-label">Estatus</span>
                            <span class="asset-card-value"><span class="status-badge status-{{ Str::kebab($asset->status) }}">{{ $asset->status }}</span></span>
                        </div>
                        <div class="asset-card-row">
                            <span class="asset-card-label">Modelo</span>
                            <span class="asset-card-value font-semibold">{{ $asset->model->name ?? 'N/A' }}</span>
                        </div>
                        <div class="asset-card-row">
                            <span class="asset-card-label">Asignado a</span>
                            <span class="asset-card-value">{{ $asset->currentAssignment->member->name ?? '---' }}</span>
                        </div>
                        <div class="asset-card-row">
                            <span class="asset-card-label">Ubicación</span>
                            <span class="asset-card-value">{{ $asset->site->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-end space-x-6 pt-4">
                            <a href="{{ route('asset-management.assets.show', $asset) }}" class="btn btn-secondary py-2 px-4 text-sm">Ver Detalles</a>
                            <a href="{{ route('asset-management.assets.edit', $asset) }}" class="btn btn-primary py-2 px-4 text-sm">Editar</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center p-12 col-span-full">
                        <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No se encontraron activos que coincidan con los filtros.</p>
                    </div>
                @endforelse
            </div>
        </div>
        
        @if ($assets->hasPages())
            <div class="p-4 bg-gray-50 border-t mt-4 rounded-b-lg">
                {!! $assets->links() !!}
            </div>
        @endif
    </div>
</div>
@endsection