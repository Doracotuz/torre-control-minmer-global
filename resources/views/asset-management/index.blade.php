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
        --color-border: #d1d5db;
    }
    body { 
        background-color: var(--color-background); 
    }
    .form-label { font-weight: 600; color: var(--color-text-primary); margin-bottom: 0.5rem; display: block; }
    .form-input, .form-select, .form-textarea { 
        border-radius: 0.5rem; 
        border: 1px solid var(--color-border); 
        transition: all 150ms ease-in-out; 
        width: 100%; 
        padding: 0.75rem 2rem;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus { 
        --tw-ring-color: var(--color-primary); 
        border-color: var(--color-primary); 
        box-shadow: 0 0 0 2px var(--tw-ring-color); 
    }
    .btn { padding: 0.65rem 1.25rem; border-radius: 0.5rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); transition: all 200ms ease-in-out; transform: translateY(0); border: 1px solid transparent; }
    .btn:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .btn-sm { padding: 0.5rem 1rem; font-size: 0.8rem; }
    .btn-primary { background-color: var(--color-primary); color: white; }
    .btn-primary:hover { background-color: #212a41; }
    .btn-secondary { background-color: var(--color-surface); color: var(--color-text-secondary); border-color: var(--color-border); }
    .btn-secondary:hover { background-color: #f9fafb; }

    .status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-en-almacén { background-color: #10B981; color: white; }
    .status-asignado { background-color: #3B82F6; color: white; }
    .status-en-reparación { background-color: var(--color-accent); color: white; }
    .status-prestado { background-color: #8B5CF6; color: white; }
    .status-de-baja { background-color: var(--color-text-secondary); color: white; }
    .status-en-mantenimiento { background-color: var(--color-text-secondary); color: white; }
    
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .kpi-card { animation: fadeIn 0.5s ease-out forwards; }

    @media (max-width: 767px) {
        .responsive-table-header {
            display: none;
        }
        .asset-card {
            display: block;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: var(--color-surface);
            box-shadow: var(--shadow-sm);
        }
        .asset-card-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .asset-card-row:last-child {
            border-bottom: none;
        }
        .asset-card-label {
            font-weight: 600;
            color: var(--color-text-secondary);
        }
        .asset-card-value {
            text-align: right;
            color: var(--color-text-primary);
        }
    }
</style>

<div x-data="{ modalOpen: false }" class="w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10">
        <div>
            <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Dashboard de Activos</h1>
            <p class="text-[var(--color-text-secondary)] mt-2">Vista general y filtrado del inventario de hardware.</p>
        </div>
        <div class="flex items-center space-x-3 mt-4 sm:mt-0">
            <button @click="modalOpen = true" class="btn btn-secondary">
                <i class="fas fa-chart-pie mr-2"></i> Ver Balance
            </button>

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
                     class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl z-20 overflow-hidden border border-gray-200" style="display: none;" x-cloak>
                    <a href="{{ route('asset-management.sites.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Sitios</a>
                    <a href="{{ route('asset-management.manufacturers.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Fabricantes</a>
                    <a href="{{ route('asset-management.categories.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Categorías</a>
                    <a href="{{ route('asset-management.models.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Modelos</a>
                    <a href="{{ route('asset-management.software-licenses.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all border-t border-gray-100">Gestionar Software</a>    
                    <a href="{{ route('asset-management.maintenances.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">
                        Mantenimientos
                    </a>
                </div>
            </div>
            
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

    <div class="bg-white p-6 rounded-xl shadow-lg">
        <form action="{{ route('asset-management.dashboard') }}" method="GET" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

                <div class="lg:col-span-2">
                    <label for="search" class="form-label text-xs">Buscar Activo</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fas fa-search text-gray-400"></i></span>
                        <input type="text" id="search" name="search" placeholder="Etiqueta, serie, modelo..." value="{{ $filters['search'] ?? '' }}" class="form-input w-full pl-10">
                    </div>
                </div>
                <div>
                    <label for="status" class="form-label text-xs">Estatus</label>
                    <select name="status" id="status" class="form-select w-full">
                        <option value="">Todos</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') == $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="site_id" class="form-label text-xs">Ubicación</label>
                    <select name="site_id" id="site_id" class="form-select w-full">
                        <option value="">Todas</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}" @selected(request('site_id') == $site->id)>{{ $site->name }}</option>
                        @endforeach
                    </select>
                </div>
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
            <div class="mt-4 flex items-center space-x-3 border-t pt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter mr-2"></i>Aplicar Filtros
                </button>
                <a href="{{ route('asset-management.dashboard') }}" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>

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

    <div x-show="modalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60" x-cloak>

        <div @click.away="modalOpen = false" 
             x-show="modalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             class="bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4 max-h-[90vh] flex flex-col">

            <div class="p-6 border-b">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-[var(--color-primary)]">Balance de Activos por Categoría</h2>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                </div>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto">
                @forelse($assetBalance as $categoryName => $data)
                    <div x-data="{ expanded: false }" class="bg-gray-50 rounded-lg border">
                        {{-- Fila Principal de la Categoría --}}
                        <div class="grid grid-cols-5 gap-4 items-center p-4">
                            <div class="col-span-2 md:col-span-1 font-bold text-gray-800">{{ $categoryName }}</div>
                            <div class="text-center">
                                <span class="block text-xs text-gray-500">Total</span>
                                <span class="text-xl font-bold text-[var(--color-primary)]">{{ $data['total'] }}</span>
                            </div>
                            <div class="text-center">
                                <span class="block text-xs text-gray-500">Utilizados</span>
                                <span class="text-xl font-bold text-orange-500">{{ $data['utilizados'] }}</span>
                            </div>
                            <div class="text-center">
                                <span class="block text-xs text-gray-500">Restantes</span>
                                <span class="text-xl font-bold text-green-600">{{ $data['restantes'] }}</span>
                            </div>
                            <div class="text-right">
                                <button @click="expanded = !expanded" class="btn btn-secondary btn-sm py-1 px-2 text-xs">
                                    <span x-show="!expanded">Ver desglose</span>
                                    <span x-show="expanded">Ocultar</span>
                                    <i class="fas fa-chevron-down ml-2 text-xs transition-transform" :class="{'rotate-180': expanded}"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div x-show="expanded" x-transition class="border-t bg-white p-4">
                            <h4 class="font-semibold text-sm mb-2">Desglose por Estatus:</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                @foreach($data['breakdown'] as $status => $count)
                                    <div class="flex justify-between items-center p-2 rounded">
                                        <span>{{ $status }}</span>
                                        <span class="font-bold">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">No hay activos para mostrar en el balance.</p>
                @endforelse
            </div>

            <div class="bg-gray-100 px-6 py-4 text-right rounded-b-xl border-t">
                <button @click="modalOpen = false" class="btn btn-secondary">Cerrar</button>
            </div>
        </div>
    </div>

</div>
@endsection