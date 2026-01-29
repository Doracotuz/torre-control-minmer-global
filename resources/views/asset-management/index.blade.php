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
        .responsive-table-header { display: none; }
        .asset-card { display: block; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem; background-color: var(--color-surface); box-shadow: var(--shadow-sm); }
        .asset-card-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; }
        .asset-card-row:last-child { border-bottom: none; }
        .asset-card-label { font-weight: 600; color: var(--color-text-secondary); }
        .asset-card-value { text-align: right; color: var(--color-text-primary); }
    }

    @keyframes shimmer { 0% { background-position: -1000px 0; } 100% { background-position: 1000px 0; } }
    .skeleton-shimmer { animation: shimmer 2s infinite linear; background: linear-gradient(to right, #f3f4f6 4%, #e5e7eb 25%, #f3f4f6 36%); background-size: 1000px 100%; }
    .skeleton-bar { height: 1.25rem; border-radius: 0.5rem; background-color: #e5e7eb; /* Fallback */ }
    .skeleton-avatar { height: 2.5rem; width: 2.5rem; border-radius: 9999px; background-color: #e5e7eb; /* Fallback */ }

    .form-input:focus, .form-select:focus, .form-textarea:focus { --tw-ring-color: var(--color-primary); border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(44, 56, 86, 0.15); }
    
    .kpi-card { animation: fadeIn 0.5s ease-out forwards; transition: all 0.3s ease-in-out; }
    .kpi-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.1); }
    
    .loading-overlay { transition: opacity 0.3s ease-in-out; }
    
</style>

<div x-data="assetDashboard()" 
     x-init="
        initFilters(
            '{{ request('search', '') }}',
            '{{ request('status', '') }}',
            '{{ request('site_id', '') }}',
            '{{ request('category_id', '') }}'
        );
        fetchAssetList();
     "
     class="w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10">
        <div>
            <h1 class="text-4xl font-bold text-[var(--color-text-primary)] tracking-tight">Dashboard de Activos</h1>
            <p class="text-[var(--color-text-secondary)] mt-2">Vista general y filtrado del inventario de hardware.</p>
        </div>
        <div class="flex items-center space-x-3 mt-4 sm:mt-0">
            <button @click="modalOpen = true" class="btn btn-secondary">
                <i class="fas fa-chart-pie mr-2"></i> Ver Balance
            </button>
            <a href="{{ route('asset-management.user-dashboard.index') }}" class="btn btn-secondary">
                <i class="fas fa-user-shield mr-2"></i> Responsivas por Usuario
            </a>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="btn btn-secondary">
                    <i class="fas fa-cog mr-2"></i> Acciones <i class="fas fa-chevron-down ml-2 text-xs transition-transform" :class="{'rotate-180': open}"></i>
                </button>
                <div x-show="open" @click.away="open = false" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl z-20 overflow-hidden border border-gray-200" style="display: none;" x-cloak>
                    
                    @auth
                        @if (Auth::user()->is_area_admin && Auth::user()->area?->name === 'Administración')
                            <div class="p-2">
                                <p class="text-xs font-semibold text-gray-400 uppercase px-3 pt-1 pb-2">Acciones</p>
                                <a href="{{ route('asset-management.assignments.import.create') }}" class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">
                                    <i class="fas fa-upload mr-3 w-4 text-center text-gray-500"></i> Importar Asignaciones
                                </a>
                                <a href="{{ route('asset-management.assets.export-csv') }}" class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">
                                    <i class="fas fa-download mr-3 w-4 text-center text-gray-500"></i> Exportar Inventario
                                </a>
                            </div>
                        @endif
                    @endauth

                    <div class="border-t border-gray-100 p-2">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 pt-1 pb-2">Configuración</p>
                        <a href="{{ route('asset-management.sites.index') }}" class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Sitios</a>
                        <a href="{{ route('asset-management.manufacturers.index') }}" class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Fabricantes</a>
                        <a href="{{ route('asset-management.categories.index') }}" class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Categorías</a>
                        <a href="{{ route('asset-management.models.index') }}" class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Modelos</a>
                        <a href="{{ route('asset-management.software-licenses.index') }}" class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">Gestionar Software</a>    
                        <a href="{{ route('asset-management.maintenances.index') }}" class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 hover:text-[var(--color-primary)] transition-all">
                            Mantenimientos
                        </a>
                    </div>
                </div>
            </div>
            <a href="{{ route('asset-management.assets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Registrar Activo
            </a>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="kpi-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="bg-blue-100 p-4 rounded-full"><i class="fas fa-desktop text-2xl text-blue-500"></i></div>
            <div><p class="text-3xl font-bold text-gray-800">{{ $stats['total'] }}</p><p class="text-gray-500 text-sm font-medium">Activos Totales</p></div>
        </div>
        <div class="kpi-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4" style="animation-delay: 0.1s;">
            <div class="bg-green-100 p-4 rounded-full"><i class="fas fa-check-circle text-2xl text-green-500"></i></div>
            <div><p class="text-3xl font-bold text-gray-800">{{ $stats['assigned'] }}</p><p class="text-gray-500 text-sm font-medium">Asignados</p></div>
        </div>
        <div class="kpi-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4" style="animation-delay: 0.2s;">
            <div class="bg-indigo-100 p-4 rounded-full"><i class="fas fa-warehouse text-2xl text-indigo-500"></i></div>
            <div><p class="text-3xl font-bold text-gray-800">{{ $stats['in_stock'] }}</p><p class="text-gray-500 text-sm font-medium">En Almacén</p></div>
        </div>
        <div class="kpi-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4" style="animation-delay: 0.3s;">
            <div class="bg-orange-100 p-4 rounded-full"><i class="fas fa-tools text-2xl text-[var(--color-accent)]"></i></div>
            <div><p class="text-3xl font-bold text-gray-800">{{ $stats['in_repair'] }}</p><p class="text-gray-500 text-sm font-medium">En Reparación</p></div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-lg">
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="lg:col-span-2">
                    <label for="search" class="form-label text-xs">Buscar Activo</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fas fa-search text-gray-400"></i></span>
                        <input type="text" id="search" placeholder="Etiqueta, serie, modelo, usuario..." 
                               x-model.debounce.350ms="filters.search" 
                               @change="resetPage(); fetchAssetList();"
                               class="form-input w-full pl-10">
                    </div>
                </div>
                <div>
                    <label for="status" class="form-label text-xs">Estatus</label>
                    <select id="status" class="form-select w-full"
                            x-model="filters.status"
                            @change="resetPage(); fetchAssetList();">
                        <option value="">Todos</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="site_id" class="form-label text-xs">Ubicación</label>
                    <select id="site_id" class="form-select w-full"
                            x-model="filters.site_id"
                            @change="resetPage(); fetchAssetList();">
                        <option value="">Todas</option>
                        @foreach($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category_id" class="form-label text-xs">Categoría</label>
                    <select id="category_id" class="form-select w-full"
                            x-model="filters.category_id"
                            @change="resetPage(); fetchAssetList();">
                        <option value="">Todas</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 flex items-center space-x-3 border-t pt-4">
                <button @click="clearFilters()" class="btn btn-secondary">Limpiar</button>
                <div x-show="isLoading" class="flex items-center text-sm text-[var(--color-primary)]" x-transition>
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Actualizando...
                </div>
            </div>
        </div>

        <div class="relative min-h-[300px]">
            <div x-show="isLoading" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 z-10" x-cloak>
                 
                @include('asset-management.assets._list-skeleton')
            </div>

            <div x-show="!isLoading" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                id="asset-list-container" 
                x-html="assetsHtml"
                @click="handlePaginationClick($event)" 
                class="z-0">
            </div>
        </div>
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

<script>
    function assetDashboard() {
        return {
            modalOpen: false,
            filters: {
                search: '',
                status: '',
                site_id: '',
                category_id: '',
                page: 1
            },
            assetsHtml: '', 
            isLoading: true,
            
            initFilters(search, status, siteId, categoryId) {
                this.filters.search = search;
                this.filters.status = status;
                this.filters.site_id = siteId;
                this.filters.category_id = categoryId;
            },

            clearFilters() {
                this.filters.search = '';
                this.filters.status = '';
                this.filters.site_id = '';
                this.filters.category_id = '';
                this.filters.page = 1;
                this.fetchAssetList();
            },

            resetPage() {
                this.filters.page = 1;
            },

            async fetchAssetList() {
                this.isLoading = true;
                const startTime = Date.now();
                const params = new URLSearchParams(this.filters);
                const url = `{{ route('asset-management.assets.filter') }}?${params.toString()}`;
                
                window.history.replaceState({}, '', `{{ route('asset-management.dashboard') }}?${params.toString()}`);

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    
                    if (!response.ok) throw new Error('Network response was not ok.');
                    
                    this.assetsHtml = await response.text();

                } catch (error) {
                    console.error('Error al cargar los activos:', error);
                    this.assetsHtml = '<p class="text-center p-12 text-red-600">Error al cargar la lista de activos. Intenta de nuevo.</p>';
                } finally {
                    const elapsedTime = Date.now() - startTime;
                    const remainingTime = 300 - elapsedTime; 

                    if (remainingTime > 0) {
                        await new Promise(resolve => setTimeout(resolve, remainingTime));
                    }
                    
                    this.isLoading = false;
                }
            },

            handlePaginationClick(event) {
                const link = event.target.closest('a[href]');
                
                if (link && link.href.includes('page=')) {
                    event.preventDefault(); 
                    
                    const url = new URL(link.href);
                    const page = url.searchParams.get('page');
                    if (page) {
                        this.filters.page = page;
                        this.fetchAssetList();
                    }
                }
            }
        }
    }
</script>

@endsection