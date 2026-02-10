<x-app-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .minmer-navy { color: #2c3856; }
        .bg-minmer-navy { background-color: #2c3856; }
        .minmer-orange { color: #ff9c00; }
        .bg-minmer-orange { background-color: #ff9c00; }
        .shadow-soft { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.1); }
        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen text-[#2b2b2b] font-montserrat pb-20 relative" 
         x-data="{ 
            showImportModal: false, 
            showDetailModal: false, 
            detailProduct: null 
         }">
        
        <div class="fixed inset-0 -z-10 pointer-events-none overflow-hidden">
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-[#2c3856] rounded-full blur-[150px] opacity-5"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-[#ff9c00] rounded-full blur-[150px] opacity-5"></div>
        </div>

        <div class="max-w-[1800px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 border-b border-gray-200 pb-6">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="h-1 w-8 bg-[#ff9c00]"></div>
                        <p class="text-xs font-bold text-[#666666] uppercase tracking-[0.2em]">Catálogos Maestros</p>
                    </div>
                    <h1 class="text-5xl font-raleway font-black text-[#2c3856] mb-1 leading-none">
                        Productos <span class="text-[#ff9c00]">WMS</span>
                    </h1>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0">
                    <a href="{{ route('wms.dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all">
                        <i class="fas fa-arrow-left"></i> <span>Dashboard</span>
                    </a>
                    @if(Auth::user()->hasFfPermission('wms.products.create'))
                    <a href="{{ route('wms.products.template') }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:text-[#ff9c00] transition-all">
                        <i class="fas fa-download"></i> <span>Plantilla</span>
                    </a>
                    <button @click="showImportModal = true" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:text-green-600 transition-all">
                        <i class="fas fa-upload"></i> <span>Importar</span>
                    </button>
                    @endif
                    @if(Auth::user()->hasFfPermission('wms.products.view'))
                    <a href="{{ route('wms.products.export-csv', request()->query()) }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-[#666666] font-bold rounded-full shadow-sm hover:shadow-md hover:text-blue-600 transition-all">
                        <i class="fas fa-file-csv"></i> <span>Exportar</span>
                    </a>
                    @endif
                    @if(Auth::user()->hasFfPermission('wms.products.create'))
                    <a href="{{ route('wms.products.create') }}" class="flex items-center gap-2 px-6 py-2.5 bg-[#2c3856] text-white font-bold rounded-full shadow-lg shadow-[#2c3856]/20 hover:bg-[#1a253a] hover:-translate-y-0.5 transition-all">
                        <i class="fas fa-plus"></i> <span>Nuevo</span>
                    </a>
                    @endif
                </div>
            </div>

            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-xl shadow-sm font-medium flex items-center gap-3">
                    <i class="fas fa-check-circle text-xl"></i>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                 <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl shadow-sm font-medium flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    <p>{{ session('error') }}</p>
                 </div>
            @endif

            <div class="bg-white rounded-[2rem] shadow-soft border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                    <form method="GET" action="{{ route('wms.products.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Cliente</label>
                            <div class="relative">
                                <select name="area_id" class="w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 bg-white text-[#2c3856] font-bold focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all appearance-none cursor-pointer">
                                    <option value="">-- Todos --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Búsqueda</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
                                <input type="text" name="search" placeholder="SKU, Nombre o UPC..." value="{{ request('search') }}" class="w-full pl-10 pr-4 py-3 rounded-xl border-gray-200 bg-white focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all">
                            </div>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-[#666666] uppercase tracking-wider mb-2 ml-2">Marca</label>
                            <div class="relative">
                                <select name="brand_id" class="w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 bg-white text-gray-700 font-medium focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all appearance-none cursor-pointer">
                                    <option value="">-- Todas --</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="md:col-span-2 flex gap-2 mt-6">
                            <button type="submit" class="w-full py-3 bg-[#2c3856] text-white font-bold rounded-xl hover:bg-[#1a253a] transition-all shadow-md">
                                Filtrar
                            </button>
                            <a href="{{ route('wms.products.index') }}" class="w-12 flex items-center justify-center py-3 bg-white border border-gray-200 text-[#666666] rounded-xl hover:bg-gray-50 transition-all" title="Limpiar">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Marca</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-[#666666] uppercase tracking-wider">U.M.</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-[#666666] uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($products as $product)
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#fff8e6] text-[#b36b00] border border-[#ff9c00]/20">
                                            {{ $product->area->name ?? 'Global' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-bold text-[#2c3856]">{{ $product->sku }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-700">{{ $product->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->brand->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->productType->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->unit_of_measure }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click='detailProduct = {!! json_encode($product) !!}; showDetailModal = true' class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-blue-600 hover:border-blue-600 transition-all shadow-sm" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if(Auth::user()->hasFfPermission('wms.products.edit'))
                                            <a href="{{ route('wms.products.edit', $product) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-[#ff9c00] hover:border-[#ff9c00] transition-all shadow-sm" title="Editar">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            @endif
                                            @if(Auth::user()->isSuperAdmin())
                                            <form action="{{ route('wms.products.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-400 hover:text-red-600 hover:border-red-600 transition-all shadow-sm" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-box-open text-4xl mb-3 opacity-50"></i>
                                            <p class="text-sm font-medium">No se encontraron productos.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                    {{ $products->links() }}
                </div>
            </div>
        </div>

        <div x-show="showImportModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-[#2c3856] bg-opacity-75 transition-opacity" aria-hidden="true" @click="showImportModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 relative z-50">
                    <form action="{{ route('wms.products.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-8 pt-8 pb-6">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-2xl leading-6 font-raleway font-black text-[#2c3856] mb-6" id="modal-title">Importar Masivo</h3>
                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-sm font-bold text-[#666666] mb-2 uppercase tracking-wide">1. Cliente (Dueño)</label>
                                            <div class="relative">
                                                <select name="area_id" required class="block w-full pl-4 pr-10 py-3 rounded-xl border-gray-200 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00] text-[#2c3856] font-bold appearance-none">
                                                    <option value="">-- Seleccionar --</option>
                                                    @foreach($areas as $area)
                                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-2 ml-1">Los productos se vincularán a este cliente.</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-[#666666] mb-2 uppercase tracking-wide">2. Archivo CSV</label>
                                            <div class="flex items-center justify-center w-full">
                                                <label class="flex flex-col w-full h-32 border-2 border-dashed border-gray-200 hover:border-[#ff9c00] hover:bg-[#fff8e6] transition-colors rounded-xl cursor-pointer group">
                                                    <div class="flex flex-col items-center justify-center pt-7">
                                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2 group-hover:text-[#ff9c00] transition-colors"></i>
                                                        <p class="text-sm text-gray-500 font-medium group-hover:text-[#2c3856]">Click para seleccionar archivo</p>
                                                    </div>
                                                    <input type="file" name="file" accept=".csv" required class="opacity-0" />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-8 py-4 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg shadow-green-500/20 px-6 py-3 bg-green-600 text-base font-bold text-white hover:bg-green-700 focus:outline-none sm:w-auto sm:text-sm transition-all">
                                Importar Datos
                            </button>
                            <button type="button" @click="showImportModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-200 shadow-sm px-6 py-3 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-all">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="showDetailModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="detail-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDetailModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-[#2c3856] bg-opacity-75 transition-opacity" aria-hidden="true" @click="showDetailModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showDetailModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100 relative z-50">
                    
                    <div class="bg-white">
                        <div class="bg-[#2c3856] px-8 py-6 flex justify-between items-start relative overflow-hidden">
                            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-[#ff9c00] rounded-full opacity-20"></div>
                            <div class="relative z-10">
                                <p class="text-[#ff9c00] font-bold text-xs uppercase tracking-[0.2em] mb-1">Detalles del Producto</p>
                                <h3 class="text-2xl font-raleway font-black text-white" x-text="detailProduct?.name"></h3>
                                <p class="text-white/60 font-mono text-sm mt-1" x-text="detailProduct?.sku"></p>
                            </div>
                            <button @click="showDetailModal = false" class="relative z-10 text-white/50 hover:text-white transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <div class="px-8 py-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                                <div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">Información General</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-[10px] text-gray-400 uppercase font-bold">Cliente</p>
                                            <p class="font-bold text-[#2c3856]" x-text="detailProduct?.area?.name || 'N/A'"></p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 uppercase font-bold">Marca</p>
                                            <p class="font-bold text-[#2c3856]" x-text="detailProduct?.brand?.name || 'N/A'"></p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 uppercase font-bold">Tipo</p>
                                            <p class="font-bold text-[#2c3856]" x-text="detailProduct?.product_type?.name || 'N/A'"></p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 uppercase font-bold">UPC / Código Barras</p>
                                            <p class="font-mono text-gray-600" x-text="detailProduct?.upc || '-'"></p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">Logística</h4>
                                    <div class="space-y-4">
                                        <div class="flex justify-between">
                                            <div>
                                                <p class="text-[10px] text-gray-400 uppercase font-bold">Unidad Base</p>
                                                <p class="font-bold text-[#2c3856]" x-text="detailProduct?.unit_of_measure"></p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-gray-400 uppercase font-bold">Piezas / Caja</p>
                                                <p class="font-bold text-[#2c3856]" x-text="detailProduct?.pieces_per_case"></p>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-2 text-center">Dimensiones y Peso</p>
                                            <div class="grid grid-cols-2 gap-4 text-center">
                                                <div>
                                                    <span class="block text-lg font-black text-[#2c3856]" x-text="detailProduct?.weight || 0"></span>
                                                    <span class="text-[9px] text-gray-400 font-bold uppercase">KG</span>
                                                </div>
                                                <div>
                                                    <span class="block text-sm font-bold text-[#2c3856]">
                                                        <span x-text="detailProduct?.length || 0"></span> x 
                                                        <span x-text="detailProduct?.width || 0"></span> x 
                                                        <span x-text="detailProduct?.height || 0"></span>
                                                    </span>
                                                    <span class="text-[9px] text-gray-400 font-bold uppercase">CM (L x A x A)</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 pt-6 border-t border-gray-100">
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-2">Descripción Detallada</p>
                                <p class="text-sm text-gray-600 leading-relaxed" x-text="detailProduct?.description || 'Sin descripción disponible.'"></p>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-8 py-4 flex justify-end">
                            <button type="button" @click="showDetailModal = false" class="px-6 py-2 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl shadow-sm hover:bg-gray-50 transition-all text-sm">
                                Cerrar
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-app-layout>