<x-app-layout>
    <x-slot name="header"></x-slot>
    <div x-data='productManager(@json($products), @json($channels), @json($areas ?? []))' class="min-h-screen relative">
        
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4">
            <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-[#2c3856] tracking-tight flex items-center gap-3">
                        <span class="bg-white text-[#2c3856] border border-gray-200 p-2 rounded-xl shadow-sm"><i class="fas fa-boxes"></i></span>
                        Catálogo de productos
                    </h2>
                    <p class="text-gray-500 mt-1 text-sm font-medium ml-1">
                        Gestión de inventario para: <span class="font-bold text-[#2c3856]">{{ Auth::user()->area ? Auth::user()->area->name : 'N/A' }}</span>
                    </p>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <div class="bg-white p-1 rounded-xl border border-gray-200 shadow-sm flex items-center">
                        <button @click="toggleView('grid')" :class="viewMode === 'grid' ? 'bg-[#2c3856] text-white shadow-md' : 'text-gray-400 hover:text-gray-600'" class="p-2 rounded-lg transition-all duration-200">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button @click="toggleView('list')" :class="viewMode === 'list' ? 'bg-[#2c3856] text-white shadow-md' : 'text-gray-400 hover:text-gray-600'" class="p-2 rounded-lg transition-all duration-200">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>

                    <a href="{{ route('ff.dashboard.index') }}" 
                       class="inline-flex items-center px-5 py-2.5 rounded-xl bg-white text-gray-700 border border-gray-300 shadow-sm hover:bg-gray-50 transition-all font-medium">
                        <i class="fas fa-arrow-left mr-2 text-gray-400"></i>
                        <span class="hidden sm:inline">Panel</span>
                    </a>
                    
                    <button @click="openUploadModal()" 
                        class="inline-flex items-center px-5 py-2.5 rounded-xl bg-white text-gray-700 border border-gray-300 shadow-sm hover:bg-gray-50 transition-all font-medium">
                        <i class="fas fa-cloud-upload-alt mr-2 text-gray-500"></i> <span class="hidden sm:inline">Importar</span>
                    </button>

                    <button @click="selectNewProduct()" 
                        class="inline-flex items-center px-5 py-2.5 rounded-xl bg-[#2c3856] text-white shadow-md hover:bg-[#1a233a] transition-all hover:-translate-y-0.5 font-bold">
                        <i class="fas fa-plus mr-2"></i> Nuevo
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
                <template x-for="(stat, index) in stats" :key="index">
                    <div class="bg-white border border-gray-200 p-4 rounded-2xl shadow-sm flex items-center gap-4 transition-transform hover:scale-[1.01]">
                        <div :class="`p-3 rounded-xl ${stat.color} shadow-sm`">
                            <i :class="stat.icon"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider" x-text="stat.label"></p>
                            <p class="text-2xl font-bold text-gray-800" x-text="stat.value"></p>
                        </div>
                    </div>
                </template>
            </div>

            <div class="sticky top-4 z-30 bg-white/90 backdrop-blur-md border border-gray-200 shadow-lg shadow-gray-200/50 rounded-2xl p-3 mb-8 transition-all">
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="relative w-full md:w-96 group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 group-focus-within:text-[#2c3856]"></i>
                        </div>
                        <input type="text" x-model="filters.search" @input="currentPage = 1"
                            class="block w-full pl-10 pr-3 py-2.5 border-gray-200 bg-gray-50 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:bg-white focus:border-transparent transition-all" 
                            placeholder="Buscar SKU, nombre, UPC...">
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
                        <button @click="showFilters = !showFilters" 
                                :class="{'bg-gray-100 text-gray-900': showFilters, 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50': !showFilters}"
                                class="px-4 py-2.5 rounded-xl text-sm font-bold transition-colors whitespace-nowrap flex items-center gap-2 shadow-sm">
                            <i class="fas fa-filter"></i> Filtros
                            <span x-show="activeFilterCount > 0" class="flex h-5 w-5 items-center justify-center rounded-full bg-[#2c3856] text-[10px] text-white" x-text="activeFilterCount"></span>
                        </button>
                        <div class="h-8 w-px bg-gray-300 mx-2"></div>
                        <a :href="generateUrl('{{ route('ff.catalog.exportInventoryPdf') }}')" target="_blank" class="px-4 py-2.5 rounded-xl bg-white text-gray-600 border border-gray-200 hover:text-gray-900 hover:bg-gray-50 transition-colors text-sm font-bold whitespace-nowrap shadow-sm">
                            <i class="fas fa-clipboard-list mr-2 text-gray-400"></i> Inventario
                        </a>                      
                        <button @click="openPdfModal()" class="px-4 py-2.5 rounded-xl bg-white text-gray-600 border border-gray-200 hover:text-gray-900 hover:bg-gray-50 transition-colors text-sm font-bold whitespace-nowrap shadow-sm">
                            <i class="fas fa-file-pdf mr-2 text-gray-400"></i> PDF
                        </button>
                        <a :href="generateUrl('{{ route('ff.catalog.exportCsv') }}')" target="_blank" class="px-4 py-2.5 rounded-xl bg-white text-gray-600 border border-gray-200 hover:text-gray-900 hover:bg-gray-50 transition-colors text-sm font-bold whitespace-nowrap shadow-sm">
                            <i class="fas fa-file-csv mr-2 text-gray-400"></i> CSV
                        </a>
                    </div>
                </div>

                <div x-show="showFilters" class="mt-4 pt-4 border-t border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Marca</label>
                            <select x-model="filters.brand" @change="currentPage = 1" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#2c3856] focus:border-[#2c3856]">
                                <option value="">Todas las marcas</option>
                                <template x-for="brand in uniqueBrands"><option :value="brand" x-text="brand"></option></template>
                            </select>
                        </div>
                        @if(Auth::user()->isSuperAdmin())
                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Área</label>
                                <select x-model="filters.area" @change="currentPage = 1" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#2c3856] focus:border-[#2c3856]">
                                    <option value="">Todas las áreas</option>
                                    <template x-for="area in areas" :key="area.id">
                                        <option :value="area.id" x-text="area.name"></option>
                                    </template>
                                </select>
                            </div>
                        @endif                        
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Tipo</label>
                            <select x-model="filters.type" @change="currentPage = 1" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#2c3856] focus:border-[#2c3856]">
                                <option value="">Todos los tipos</option>
                                <template x-for="type in uniqueTypes"><option :value="type" x-text="type"></option></template>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Canal de Venta</label>
                            <select x-model="filters.channel" @change="currentPage = 1" class="w-full rounded-lg border-gray-300 text-sm focus:ring-[#2c3856] focus:border-[#2c3856]">
                                <option value="">Todos los canales</option>
                                <template x-for="channel in channels" :key="channel.id"><option :value="channel.id" x-text="channel.name"></option></template>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Estado</label>
                            <div class="flex gap-2">
                                <button @click="filters.status = 'all'; currentPage = 1" :class="filters.status === 'all' ? 'bg-[#2c3856] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="flex-1 py-2 rounded-lg text-xs font-bold transition-colors">Todos</button>
                                <button @click="filters.status = 'active'; currentPage = 1" :class="filters.status === 'active' ? 'bg-[#2c3856] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="flex-1 py-2 rounded-lg text-xs font-bold transition-colors">Activos</button>
                                <button @click="filters.status = 'inactive'; currentPage = 1" :class="filters.status === 'inactive' ? 'bg-[#2c3856] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="flex-1 py-2 rounded-lg text-xs font-bold transition-colors">Inactivos</button>
                            </div>
                        </div>
                        <div class="flex items-end md:col-span-4 justify-end">
                            <button @click="resetFilters()" class="px-6 py-2.5 text-sm text-gray-500 font-bold hover:bg-gray-100 hover:text-gray-900 rounded-lg transition-colors"><i class="fas fa-times mr-1"></i> Limpiar Filtros</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
                <div x-show="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <template x-for="i in 8">
                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 h-80 animate-pulse flex flex-col gap-4">
                            <div class="h-48 bg-gray-200 rounded-xl w-full"></div><div class="h-4 bg-gray-200 rounded w-3/4"></div><div class="h-4 bg-gray-200 rounded w-1/2"></div><div class="mt-auto h-8 bg-gray-200 rounded w-full"></div>
                        </div>
                    </template>
                </div>

                <div x-show="!loading && filteredProducts.length === 0" class="flex flex-col items-center justify-center py-20 text-center" style="display: none;">
                    <div class="bg-white p-6 rounded-full shadow-lg mb-6"><i class="fas fa-search text-4xl text-gray-300"></i></div>
                    <h3 class="text-xl font-bold text-gray-900">No se encontraron productos</h3>
                    <p class="text-gray-500 mt-2">Intenta ajustar los filtros o tu búsqueda.</p>
                    <button @click="resetFilters()" class="mt-6 text-[#2c3856] font-bold hover:underline">Limpiar todo</button>
                </div>

                <div x-show="!loading && viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <template x-for="product in paginatedProducts" :key="product.id">
                        <div class="group relative bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-xl hover:shadow-gray-200/50 hover:-translate-y-1 transition-all duration-300 overflow-hidden cursor-pointer">
                            <div class="absolute top-3 right-3 z-10 flex flex-col gap-2 items-end">
                                <span :class="product.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200'" class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md border shadow-sm"><span x-text="product.is_active ? 'Activo' : 'Inactivo'"></span></span>
                                <template x-if="product.channels && product.channels.length > 0">
                                    <div class="flex flex-wrap justify-end gap-1 max-w-[140px]">
                                        <template x-for="channel in product.channels.slice(0, 2)">
                                            <span :class="getChannelStyle(channel.name)" class="px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded-md border shadow-sm truncate max-w-full" x-text="channel.name"></span>
                                        </template>
                                        <span x-show="product.channels.length > 2" class="px-2 py-0.5 text-[9px] font-bold bg-gray-100 text-gray-600 rounded-md border" x-text="'+' + (product.channels.length - 2)"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="h-56 w-full p-6 bg-white flex items-center justify-center relative border-b border-gray-100">
                                <img :src="product.photo_url" class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105" :class="{'grayscale opacity-40': !product.is_active}">
                                <div x-show="!product.is_active" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <span class="bg-gray-800/80 text-white px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest backdrop-blur-sm transform -rotate-12">Inactivo</span>
                                </div>
                                <button @click.stop="openDetailModal(product)" class="absolute top-0 left-0 bg-white/90 backdrop-blur text-gray-500 p-2 rounded-br-xl shadow-sm hover:bg-[#2c3856] hover:text-white transition-colors border-r border-b border-gray-100 z-20" title="Ver Detalle"><i class="fas fa-eye"></i></button>
                                <div class="absolute inset-0 bg-[#2c3856]/80 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-[1px]" @click="editProduct(product)">
                                    <span class="bg-white text-[#2c3856] px-5 py-2 rounded-full font-bold text-sm shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all"><i class="fas fa-pen mr-2"></i> Editar</span>
                                </div>
                            </div>
                            <div class="p-5" @click="editProduct(product)">
                                <div class="flex justify-between items-start mb-2">
                                    <p class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded border border-gray-200" x-text="product.sku"></p>
                                    <p class="text-xs font-bold text-[#2c3856] uppercase tracking-wider" x-text="product.brand || 'Genérico'"></p>
                                </div>
                                <h4 class="font-bold text-gray-900 leading-snug line-clamp-2 h-10 mb-3 text-sm" x-text="product.description"></h4>
                                <div class="flex items-end justify-between border-t border-gray-100 pt-3 mt-auto">
                                    <div><p class="text-[10px] text-gray-400 font-bold uppercase mb-0.5">Precio Unitario</p><p class="text-lg font-extrabold text-[#2c3856]" x-text="formatMoney(product.unit_price)"></p></div>
                                    <div class="text-right"><p class="text-[10px] text-gray-400 font-bold uppercase mb-0.5">Presentación</p><p class="text-sm font-semibold text-gray-600" x-text="(product.pieces_per_box || 0) + ' pzas/caja'"></p></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="!loading && viewMode === 'list'" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">Foto</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Detalles</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Canales</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Precio</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Estado</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="product in paginatedProducts" :key="product.id">
                                    <tr class="hover:bg-gray-50 transition-colors cursor-pointer group" @click="editProduct(product)">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="h-12 w-12 rounded-lg border border-gray-200 p-1 bg-white relative">
                                                <img :src="product.photo_url" class="h-full w-full object-contain" :class="{'grayscale opacity-40': !product.is_active}">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900 text-sm line-clamp-1" x-text="product.description"></span>
                                                <span class="text-xs text-gray-500 font-mono bg-gray-100 px-1.5 py-0.5 rounded w-fit mt-1 border border-gray-200" x-text="product.sku"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 hidden md:table-cell">
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs font-bold text-[#2c3856]" x-text="product.brand || '-'"></span>
                                                <span class="text-xs text-gray-500" x-text="product.type || '-'"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 hidden lg:table-cell">
                                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                                <template x-if="product.channels && product.channels.length > 0">
                                                    <template x-for="channel in product.channels">
                                                        <span :class="getChannelStyle(channel.name)" class="px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider" x-text="channel.name"></span>
                                                    </template>
                                                </template>
                                                <template x-if="!product.channels || product.channels.length === 0">
                                                    <span class="text-xs text-gray-400 italic">-</span>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <span class="text-sm font-extrabold text-[#2c3856]" x-text="formatMoney(product.unit_price)"></span>
                                            <p class="text-[10px] text-gray-400 mt-0.5" x-text="(product.pieces_per_box || 0) + ' pzas/caja'"></p>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <span :class="product.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-gray-100 text-gray-500 border-gray-200'" class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md border shadow-sm">
                                                <span x-text="product.is_active ? 'Activo' : 'Inactivo'"></span>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click.stop="openDetailModal(product)" class="p-2 text-gray-400 hover:text-[#2c3856] hover:bg-gray-100 rounded-lg transition-colors" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button @click.stop="editProduct(product)" class="p-2 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors" title="Editar">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="!loading && filteredProducts.length > 0" class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4 border-t border-gray-200 pt-6">
                    <div class="text-sm text-gray-500">
                        Mostrando <span class="font-bold text-gray-900" x-text="((currentPage - 1) * itemsPerPage) + 1"></span> a 
                        <span class="font-bold text-gray-900" x-text="Math.min(currentPage * itemsPerPage, filteredProducts.length)"></span> de 
                        <span class="font-bold text-gray-900" x-text="filteredProducts.length"></span> resultados
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"><i class="fas fa-chevron-left mr-1"></i> Anterior</button>
                        <div class="hidden md:flex gap-1">
                            <template x-for="page in totalPages" :key="page">
                                <button x-show="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
                                        @click="changePage(page)"
                                        x-text="page"
                                        :class="currentPage === page ? 'bg-[#2c3856] text-white border-[#2c3856]' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg border text-sm font-bold transition-colors">
                                </button>
                            </template>
                        </div>
                        <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">Siguiente <i class="fas fa-chevron-right ml-1"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" x-show="isEditorOpen" style="display: none;">
            <div x-show="isEditorOpen" x-transition:enter="ease-in-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-500" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeEditor()"></div>
            <div class="fixed inset-0 overflow-hidden pointer-events-none">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <div x-show="isEditorOpen" x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="pointer-events-auto w-screen max-w-md bg-white shadow-2xl h-full flex flex-col">
                            <form @submit.prevent="saveProduct()" class="flex h-full flex-col divide-y divide-gray-200">
                                <div class="flex min-h-0 flex-1 flex-col overflow-y-scroll py-6">
                                    <div class="px-4 sm:px-6">
                                        <div class="flex items-start justify-between">
                                            <h2 class="text-xl font-bold text-[#2c3856]" x-text="form.id ? 'Editar Producto' : 'Crear Nuevo'"></h2>
                                            <button type="button" @click="closeEditor()" class="rounded-full bg-gray-100 text-gray-400 hover:text-gray-500 p-1"><i class="fas fa-times fa-lg"></i></button>
                                        </div>
                                    </div>
                                    <div class="relative mt-6 flex-1 px-4 sm:px-6 space-y-6">
                                        <div class="flex justify-center mb-6">
                                            <div class="relative group cursor-pointer" @click="$refs.photoInput.click()">
                                                <div class="w-48 h-48 rounded-xl overflow-hidden bg-gray-50 border-2 border-dashed border-gray-300 group-hover:border-[#2c3856] transition-colors flex items-center justify-center relative">
                                                    <img x-show="photoPreview" :src="photoPreview" class="w-full h-full object-contain">
                                                    <img x-show="!photoPreview && form.photo_url" :src="form.photo_url" class="w-full h-full object-contain">
                                                    <div x-show="!photoPreview && !form.photo_url" class="text-center p-4"><i class="fas fa-camera text-gray-400 text-3xl mb-2"></i><p class="text-xs text-gray-500 font-medium">Subir Imagen</p></div>
                                                </div>
                                                <input type="file" @change="previewPhoto($event)" class="hidden" x-ref="photoInput" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="space-y-6">
                                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                                                <span class="text-sm font-bold text-gray-900">Activado / Desactivado</span>
                                                <button type="button" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" :class="form.is_active ? 'bg-[#2c3856]' : 'bg-gray-300'" @click="form.is_active = !form.is_active">
                                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"></span>
                                                </button>
                                            </div>
                                            <div><label class="block text-sm font-bold text-gray-700">SKU</label><input type="text" x-model="form.sku" :disabled="form.id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#2c3856] focus:ring-[#2c3856] disabled:bg-gray-100 disabled:text-gray-500 font-mono text-sm"></div>
                                            <div><label class="block text-sm font-bold text-gray-700">Descripción</label><textarea x-model="form.description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#2c3856] focus:ring-[#2c3856] text-sm"></textarea></div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div><label class="block text-sm font-bold text-gray-700">Precio</label><input type="number" step="0.01" x-model="form.unit_price" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-[#2c3856] focus:ring-[#2c3856] text-sm font-bold"></div>
                                                <div><label class="block text-sm font-bold text-gray-700">Marca</label><input type="text" x-model="form.brand" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#2c3856] focus:ring-[#2c3856] text-sm"></div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div><label class="block text-sm font-bold text-gray-700">UPC</label><input type="text" x-model="form.upc" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#2c3856] focus:ring-[#2c3856] text-sm"></div>
                                                <div><label class="block text-sm font-bold text-gray-700">Tipo</label><input type="text" x-model="form.type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#2c3856] focus:ring-[#2c3856] text-sm"></div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 mb-2">Canales de Venta</label>
                                                <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-2 space-y-2 bg-gray-50">
                                                    <template x-for="channel in channels" :key="channel.id">
                                                        <label class="flex items-center space-x-3 p-2 rounded hover:bg-white cursor-pointer transition-colors">
                                                            <input type="checkbox" :value="channel.id" x-model="form.channels" class="h-4 w-4 text-[#2c3856] border-gray-300 rounded focus:ring-[#2c3856]">
                                                            <span class="text-sm text-gray-700 font-medium" x-text="channel.name"></span>
                                                        </label>
                                                    </template>
                                                    <div x-show="channels.length === 0" class="text-xs text-gray-500 italic p-2">No hay canales activos.</div>
                                                </div>
                                                <p class="text-[10px] text-gray-500 mt-1">Selecciona uno o más canales donde este producto está disponible.</p>
                                            </div>
                                            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                                                <h4 class="text-xs font-bold text-[#2c3856] uppercase tracking-wide mb-3">Datos Logísticos</h4>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div><label class="block text-xs font-bold text-gray-600">Pzas/Caja</label><input type="number" x-model="form.pieces_per_box" class="mt-1 block w-full rounded-md border-blue-200 shadow-sm focus:border-[#2c3856] focus:ring-[#2c3856] text-sm"></div>
                                                    <div class="col-span-2 grid grid-cols-3 gap-2">
                                                        <div><label class="block text-[10px] font-bold text-gray-500">Largo</label><input type="number" step="0.01" x-model="form.length" class="block w-full rounded-md border-blue-200 shadow-sm text-xs"></div>
                                                        <div><label class="block text-[10px] font-bold text-gray-500">Ancho</label><input type="number" step="0.01" x-model="form.width" class="block w-full rounded-md border-blue-200 shadow-sm text-xs"></div>
                                                        <div><label class="block text-[10px] font-bold text-gray-500">Alto</label><input type="number" step="0.01" x-model="form.height" class="block w-full rounded-md border-blue-200 shadow-sm text-xs"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-start gap-3 px-4 py-4 bg-gray-50 border-t border-gray-200">
                                    <button type="button" @click="deleteProduct(form)" x-show="form.id" class="rounded-lg bg-white py-2 px-4 text-sm font-bold text-red-600 shadow-sm border border-red-200 hover:bg-red-50">Eliminar</button>
                                        <button type="button" @click="closeEditor()" class="rounded-lg bg-white py-2 px-4 text-sm font-bold text-gray-700 shadow-sm border border-gray-300 hover:bg-gray-50">Cancelar</button>
                                        <button type="submit" :disabled="isSaving" class="rounded-lg bg-[#2c3856] py-2 px-6 text-sm font-bold text-white shadow-md hover:bg-[#1a233a] disabled:opacity-50"><span x-text="isSaving ? 'Guardando...' : 'Guardar'"></span></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="isDetailModalOpen" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" @click="isDetailModalOpen = false"></div>
                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full p-0">
                    <div class="flex justify-between items-center p-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="text-lg font-bold text-[#2c3856]">Detalle del Producto</h3>
                        <button @click="isDetailModalOpen = false" class="text-gray-400 hover:text-gray-600 focus:outline-none"><i class="fas fa-times fa-lg"></i></button>
                    </div>
                    <div class="p-8 flex flex-col md:flex-row gap-8">
                        <div class="w-full md:w-1/2 flex items-center justify-center bg-gray-50 rounded-xl border border-gray-100 p-6"><img :src="detailProduct.photo_url" class="max-h-[400px] w-auto object-contain drop-shadow-lg"></div>
                        <div class="w-full md:w-1/2 flex flex-col justify-between">
                            <div class="space-y-6">
                                <div><span class="inline-block px-3 py-1 bg-gray-200 text-gray-700 text-sm font-mono font-bold rounded-lg mb-2" x-text="detailProduct.sku"></span><h2 class="text-3xl font-extrabold text-gray-900 leading-tight" x-text="detailProduct.description"></h2><p class="text-gray-500 font-bold text-lg mt-2" x-text="detailProduct.brand"></p></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100"><p class="text-xs text-gray-500 font-bold uppercase">Precio Unitario</p><p class="text-2xl font-bold text-[#2c3856]" x-text="formatMoney(detailProduct.unit_price)"></p></div>
                                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100"><p class="text-xs text-gray-500 font-bold uppercase">Presentación</p><p class="text-xl font-bold text-gray-700" x-text="(detailProduct.pieces_per_box || 0) + ' pzas/caja'"></p></div>
                                </div>
                                <div class="space-y-3 pt-4 border-t border-gray-100 text-sm">
                                    <div class="flex justify-between"><span class="text-gray-500 font-medium">UPC:</span><span class="font-bold text-gray-900" x-text="detailProduct.upc || 'N/A'"></span></div>
                                    <div class="flex justify-between"><span class="text-gray-500 font-medium">Tipo:</span><span class="font-bold text-gray-900" x-text="detailProduct.type || 'N/A'"></span></div>
                                    <div class="flex justify-between items-start"><span class="text-gray-500 font-medium">Canales:</span>
                                        <div class="flex flex-wrap justify-end gap-1 max-w-[200px]">
                                            <template x-if="detailProduct.channels && detailProduct.channels.length > 0">
                                                <template x-for="channel in detailProduct.channels">
                                                    <span class="px-2 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-100 text-xs font-bold" x-text="channel.name"></span>
                                                </template>
                                            </template>
                                            <template x-if="!detailProduct.channels || detailProduct.channels.length === 0">
                                                <span class="text-gray-400 italic">Ninguno</span>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="flex justify-between"><span class="text-gray-500 font-medium">Dimensiones:</span><span class="font-bold text-gray-900" x-text="`${detailProduct.length || 0} x ${detailProduct.width || 0} x ${detailProduct.height || 0} cm`"></span></div>
                                </div>
                            </div>
                            <div class="mt-8 flex gap-3"><button @click="openSheetModal(detailProduct)" class="flex-1 py-3 bg-[#2c3856] text-white rounded-xl font-bold hover:bg-[#1a233a] shadow-lg"><i class="fas fa-file-invoice mr-2"></i> Ficha Técnica</button><button @click="closeDetailAndEdit(detailProduct)" class="flex-1 py-3 bg-white text-[#2c3856] border border-gray-300 rounded-xl font-bold hover:bg-gray-50">Editar</button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div x-show="isSheetModalOpen" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" @click="isSheetModalOpen = false"></div>
                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                    <form :action="`{{ url('ff/catalog') }}/${sheetProduct.id}/technical-sheet`" method="POST" target="_blank" class="flex flex-col h-full">
                        @csrf
                        <div class="px-6 py-4 bg-[#2c3856] flex justify-between items-center">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="fas fa-file-invoice"></i> Generar Ficha Técnica</h3>
                            <button type="button" @click="isSheetModalOpen = false" class="text-white/70 hover:text-white"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 mb-4">
                                <p class="text-xs text-blue-800 font-medium">Completa los datos adicionales para generar el PDF.</p>
                                <p class="font-bold text-[#2c3856] text-sm mt-1 truncate" x-text="sheetProduct.description"></p>
                            </div>
                            <div><label class="block text-sm font-bold text-gray-700 mb-1">Vol. Alcohol</label><input type="text" name="alcohol_vol" class="w-full rounded-lg border-gray-300 focus:ring-[#2c3856] focus:border-[#2c3856] text-sm"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="block text-sm font-bold text-gray-700 mb-1">Cajas/Cama</label><input type="number" name="boxes_per_layer" class="w-full rounded-lg border-gray-300 focus:ring-[#2c3856] focus:border-[#2c3856] text-sm"></div>
                                <div><label class="block text-sm font-bold text-gray-700 mb-1">Camas/Tarima</label><input type="number" name="layers_per_pallet" class="w-full rounded-lg border-gray-300 focus:ring-[#2c3856] focus:border-[#2c3856] text-sm"></div>
                            </div>
                            <div><label class="block text-sm font-bold text-gray-700 mb-1">Peso Caja Master</label><input type="text" name="master_box_weight" class="w-full rounded-lg border-gray-300 focus:ring-[#2c3856] focus:border-[#2c3856] text-sm"></div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="isSheetModalOpen = false" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg font-bold hover:bg-gray-50">Cancelar</button>
                            <button type="submit" @click="setTimeout(() => isSheetModalOpen = false, 1000)" class="px-4 py-2 bg-[#2c3856] text-white rounded-lg font-bold hover:bg-[#1a233a] shadow-md">Generar PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="isUploadModalOpen" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" @click="closeUploadModal()"></div>
                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                     <form @submit.prevent="submitImport($event)" class="p-6">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-xl font-bold text-[#2c3856] flex items-center"><i class="fas fa-file-excel mr-3 text-[#2c3856]"></i> Importación Masiva</h3>
                            <button type="button" @click="closeUploadModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 mb-6">
                            <h4 class="text-sm font-bold text-[#2c3856] mb-2 flex items-center"><i class="fas fa-info-circle mr-2 text-blue-600"></i> ¿Cómo editar masivamente?</h4>
                            <ol class="list-decimal list-inside text-xs text-blue-800 space-y-1 font-medium ml-1">
                                <li>Descarga la plantilla (incluye productos actuales).</li>
                                <li>Edita precios, nombres o añade productos nuevos en Excel.</li>
                                <li>Sube el archivo CSV editado en el <strong>Paso 2</strong>.</li>
                                <li>El archivo ZIP es opcional (solo si subes fotos nuevas).</li>
                            </ol>
                        </div>
                        <div class="space-y-6">
                             <div><label class="block text-sm font-bold text-gray-700 mb-2">Paso 1: Descargar Catálogo Actual</label><a href="{{ route('ff.catalog.downloadTemplate') }}" class="w-full flex items-center justify-center p-3 border border-gray-300 rounded-xl hover:border-[#2c3856] hover:bg-gray-50 transition-colors group cursor-pointer text-sm font-bold text-gray-600 shadow-sm bg-white"><i class="fas fa-download mr-2 text-blue-600 group-hover:scale-110 transition-transform"></i> Descargar plantilla.csv</a></div>
                             <div><label class="block text-sm font-bold text-gray-700 mb-2">Paso 2: Subir CSV Editado <span class="text-red-500">*</span></label><input type="file" name="product_file" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-[#2c3856] file:text-white hover:file:bg-[#1a233a] bg-gray-50 rounded-lg border border-gray-200 cursor-pointer"/></div>
                             <div><label class="block text-sm font-bold text-gray-700 mb-1">Paso 3: Imágenes Nuevas (Opcional)</label><input type="file" name="image_zip" accept=".zip" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-gray-600 file:text-white hover:file:bg-gray-700 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer"/></div>
                             <div x-show="uploadMessage" :class="uploadSuccess ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200'" class="p-4 rounded-xl text-sm border font-medium"><p x-text="uploadMessage"></p></div>
                        </div>
                        <div class="mt-8 flex gap-3 border-t border-gray-100 pt-4">
                             <button type="button" @click="closeUploadModal()" class="flex-1 py-2.5 px-4 border border-gray-300 rounded-xl text-gray-700 font-bold hover:bg-gray-50 transition-colors">Cancelar</button>
                             <button type="submit" :disabled="isSaving" class="flex-1 py-2.5 px-4 bg-[#2c3856] text-white rounded-xl font-bold hover:bg-[#1a233a] shadow-lg transition-colors"><span x-text="isSaving ? 'Procesando...' : 'Iniciar Importación'"></span></button>
                        </div>
                     </form>
                </div>
            </div>
        </div>

        <div x-show="isPdfModalOpen" style="display: none;" class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" @click="isPdfModalOpen = false"></div>
                <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full p-6">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-50 mb-4"><i class="fas fa-file-pdf text-[#2c3856] text-2xl"></i></div>
                        <h3 class="text-lg font-bold text-gray-900">Generar Catálogo PDF</h3>
                        <div class="mt-6"><label class="block text-sm font-bold text-gray-700 text-left mb-2">Aumento (%)</label><input type="number" x-model="pdfPercentage" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-[#2c3856] focus:ring-[#2c3856] text-center text-xl font-bold py-3" placeholder="0"></div>
                        <div class="mt-6 flex gap-3"><button @click="isPdfModalOpen = false" class="flex-1 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-bold hover:bg-gray-50">Cancelar</button><button @click="generatePdf()" class="flex-1 py-2.5 bg-[#2c3856] text-white rounded-xl font-bold hover:bg-[#1a233a] shadow-lg">Descargar</button></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function productManager(initialProducts, initialChannels, initialAreas) {
            return {
                products: initialProducts || [],
                channels: initialChannels || [],
                areas: initialAreas || [],
                loading: true,
                currentPage: 1, itemsPerPage: 12,
                isEditorOpen: false, showFilters: false,
                filters: { search: '', brand: '', type: '', status: 'all', channel: '', area: '' },
                isSaving: false, photoPreview: null,
                form: { id: null, sku: '', description: '', type: '', brand: '', unit_price: 0.00, pieces_per_box: null, length: null, width: null, height: null, upc: '', photo: null, photo_url: null, is_active: true, channels: [] },
                isUploadModalOpen: false, uploadMessage: '', uploadSuccess: false,
                isPdfModalOpen: false, pdfPercentage: 0,
                isDetailModalOpen: false, detailProduct: {}, isSheetModalOpen: false, sheetProduct: {},
                viewMode: localStorage.getItem('ff_catalog_view_mode') || 'grid',

                init() { setTimeout(() => { this.loading = false; }, 300); },

                toggleView(mode) {
                    this.viewMode = mode;
                    localStorage.setItem('ff_catalog_view_mode', mode);
                },

                get filteredProducts() {    
                    if (!this.products) return [];
                    let result = this.products;
                    const search = this.filters.search.toLowerCase();
                    
                    if (search) { result = result.filter(p => (p.sku && p.sku.toLowerCase().includes(search)) || (p.description && p.description.toLowerCase().includes(search)) || (p.brand && p.brand.toLowerCase().includes(search)) || (p.upc && p.upc.toLowerCase().includes(search))); }
                    if (this.filters.brand) result = result.filter(p => p.brand === this.filters.brand);
                    if (this.filters.type) result = result.filter(p => p.type === this.filters.type);
                    if (this.filters.status !== 'all') { const isActive = this.filters.status === 'active'; result = result.filter(p => Boolean(p.is_active) === isActive); }
                    if (this.filters.channel) { result = result.filter(p => p.channels && p.channels.some(c => c.id == this.filters.channel)); }
                    if (this.filters.area) { 
                        result = result.filter(p => p.area_id == this.filters.area); 
                    }

                    return result;
                },

                get paginatedProducts() { const start = (this.currentPage - 1) * this.itemsPerPage; return this.filteredProducts.slice(start, start + this.itemsPerPage); },
                get totalPages() { return Math.ceil(this.filteredProducts.length / this.itemsPerPage); },
                changePage(page) { if (page >= 1 && page <= this.totalPages) { this.currentPage = page; window.scrollTo({ top: 0, behavior: 'smooth' }); } },
                get uniqueBrands() { if (!this.products) return []; const brands = this.products.map(p => p.brand).filter(b => b); return [...new Set(brands)].sort(); },
                get uniqueTypes() { if (!this.products) return []; const types = this.products.map(p => p.type).filter(t => t); return [...new Set(types)].sort(); },
                get activeFilterCount() { let count = 0; if(this.filters.brand) count++; if(this.filters.type) count++; if(this.filters.status !== 'all') count++; if(this.filters.channel) count++; if(this.filters.area) count++; return count; },
                get stats() {
                    const total = this.filteredProducts.length;
                    const active = this.filteredProducts.filter(p => p.is_active).length;
                    const inactive = total - active;

                    return [
                        { 
                            label: 'Total Listado', 
                            value: total, 
                            icon: 'fas fa-boxes', 
                            color: 'bg-blue-50 text-blue-700' 
                        },
                        { 
                            label: 'Activos', 
                            value: active, 
                            icon: 'fas fa-check-circle', 
                            color: 'bg-emerald-50 text-emerald-700' 
                        },
                        { 
                            label: 'Inactivos', 
                            value: inactive, 
                            icon: 'fas fa-times-circle', 
                            color: 'bg-red-50 text-red-700' 
                        }
                    ];
                },                
                formatMoney(amount) { return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount); },
                resetFilters() { this.filters.brand = ''; this.filters.type = ''; this.filters.status = 'all'; this.filters.search = ''; this.filters.channel = ''; this.filters.area = ''; this.currentPage = 1; },
                selectNewProduct() { this.resetForm(); this.isEditorOpen = true; },
                editProduct(product) { const channelIds = product.channels ? product.channels.map(c => c.id) : []; this.form = { ...product, photo: null, channels: channelIds }; this.photoPreview = null; this.isEditorOpen = true; },
                closeEditor() { this.isEditorOpen = false; setTimeout(() => this.resetForm(), 300); },
                resetForm() { this.form = { id: null, sku: '', description: '', type: '', brand: '', unit_price: 0.00, pieces_per_box: null, length: null, width: null, height: null, upc: '', photo: null, photo_url: null, is_active: true, channels: [] }; this.photoPreview = null; },
                previewPhoto(event) { const file = event.target.files[0]; if (file) { this.form.photo = file; const reader = new FileReader(); reader.onload = (e) => { this.photoPreview = e.target.result; }; reader.readAsDataURL(file); } },
                async saveProduct() {
                    if (this.isSaving) return; this.isSaving = true;
                    const formData = new FormData();
                    Object.keys(this.form).forEach(key => {
                        if (key === 'photo_url' || key === 'channels') return;
                        let value = this.form[key];
                        if (typeof value === 'boolean') value = value ? 1 : 0;
                        if (key === 'photo' && !value) return;
                        if (value === null) value = '';
                        formData.append(key, value);
                    });
                    if (this.form.channels && this.form.channels.length > 0) { this.form.channels.forEach(id => formData.append('channels[]', id)); }
                    const isUpdate = !!this.form.id;
                    if (isUpdate) formData.append('_method', 'PUT');
                    const url = isUpdate ? `{{ url('ff/catalog') }}/${this.form.id}` : `{{ route('ff.catalog.store') }}`;
                    try {
                        const response = await fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', }, });
                        if (!response.ok) { const errorData = await response.json(); alert('Error: ' + (errorData.message || 'Verifica los datos')); throw new Error('Error en validación'); }
                        const savedProduct = await response.json();
                        if (isUpdate) { const index = this.products.findIndex(p => p.id === savedProduct.id); if (index > -1) this.products.splice(index, 1, savedProduct); } else { this.products.unshift(savedProduct); }
                        this.closeEditor();
                    } catch (error) { console.error(error); } finally { this.isSaving = false; }
                },
                async deleteProduct(product) { if (!confirm(`¿Eliminar definitivamente "${product.description}"?`)) return; try { const response = await fetch(`{{ url('ff/catalog') }}/${product.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', }, }); if (!response.ok) throw new Error('Error al eliminar'); this.products = this.products.filter(p => p.id !== product.id); this.closeEditor(); } catch (error) { alert('No se pudo eliminar.'); } },
                openUploadModal() { this.isUploadModalOpen = true; this.uploadMessage = ''; },
                closeUploadModal() { this.isUploadModalOpen = false; if(this.uploadSuccess) location.reload(); },
                async submitImport(event) { this.isSaving = true; this.uploadMessage = ''; const formData = new FormData(event.target); try { const response = await fetch("{{ route('ff.catalog.import') }}", { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json'} }); const data = await response.json(); this.uploadSuccess = response.ok; this.uploadMessage = data.message || (data.errors ? Object.values(data.errors).join(' ') : 'Error'); if(this.uploadSuccess) event.target.reset(); } catch (e) { this.uploadMessage = 'Error de conexión'; } finally { this.isSaving = false; } },
                openPdfModal() { this.pdfPercentage = 0; this.isPdfModalOpen = true; },
                generatePdf() { 
                    let url = this.generateUrl("{{ route('ff.catalog.exportPdf') }}");
                    url += '&percentage=' + this.pdfPercentage;
                    
                    window.open(url, '_blank'); 
                    this.isPdfModalOpen = false; 
                },
                openDetailModal(product) { this.detailProduct = product; this.isDetailModalOpen = true; },
                openSheetModal(product) { this.sheetProduct = product; this.isSheetModalOpen = true; },
                closeDetailAndEdit(product) { this.isDetailModalOpen = false; setTimeout(() => { this.editProduct(product); }, 300); },
                generateUrl(baseUrl) {
                    const params = new URLSearchParams();
                    if(this.filters.search) params.append('search', this.filters.search);
                    if(this.filters.brand) params.append('brand', this.filters.brand);
                    if(this.filters.type) params.append('type', this.filters.type);
                    if(this.filters.status !== 'all') params.append('status', this.filters.status);
                    if(this.filters.channel) params.append('channel', this.filters.channel);
                    if(this.filters.area) params.append('area_id', this.filters.area);
                    
                    return baseUrl + '?' + params.toString();
                },                
                getChannelStyle(name) {
                    if (!name) return 'bg-gray-100 text-gray-600 border-gray-200';
                    const styles = [
                        'bg-blue-100 text-blue-700 border-blue-200',
                        'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'bg-purple-100 text-purple-700 border-purple-200',
                        'bg-amber-100 text-amber-700 border-amber-200',
                        'bg-rose-100 text-rose-700 border-rose-200',
                        'bg-cyan-100 text-cyan-700 border-cyan-200',
                        'bg-indigo-100 text-indigo-700 border-indigo-200',
                        'bg-orange-100 text-orange-700 border-orange-200',
                        'bg-teal-100 text-teal-700 border-teal-200',
                        'bg-fuchsia-100 text-fuchsia-700 border-fuchsia-200',
                    ];
                    let hash = 0;
                    for (let i = 0; i < name.length; i++) {
                        hash = name.charCodeAt(i) + ((hash << 5) - hash);
                    }
                    const index = Math.abs(hash) % styles.length;
                    return styles[index];
                }
            }
        }
    </script>
</x-app-layout>