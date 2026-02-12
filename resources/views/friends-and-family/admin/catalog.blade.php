<x-app-layout>
    <x-slot name="header"></x-slot>    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(20px); }
        @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }
        .floating-card { background: white; border-radius: 24px; box-shadow: 0 10px 40px -10px rgba(44, 56, 86, 0.08); border: 1px solid rgba(255, 255, 255, 0.5); }
        .table-row-modern {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f3f4f6;
        }
        .table-row-modern:last-child { border-bottom: none; }

        .table-row-modern:hover {
            background-color: #f8fafc;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            z-index: 10;
            position: relative;
        }
        .input-modern { background-color: #f9fafb; border: 2px solid transparent; transition: all 0.3s ease; }
        .input-modern:focus { background-color: white; border-color: #ff9c00; box-shadow: 0 0 0 4px rgba(255, 156, 0, 0.1); }
        .btn-animated { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-animated:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(255, 156, 0, 0.4); }
    </style>

    <div class="min-h-screen font-sans text-[#2c3856] p-6 lg:p-10"
         x-data="{ 
            showModal: false, 
            editMode: false, 
            itemId: null, 
            itemName: '',
            itemCode: '',
            itemAddress: '',
            itemPhone: '',
            itemAreaId: '', 
            modalTitle: '',
            search: '',
            selectedArea: '',
            currentType: '{{ $type }}',
            isSuperAdmin: {{ Auth::user()->isSuperAdmin() ? 'true' : 'false' }},
            
            get filteredItems() {
                let items = {{ $items }};

                if (this.selectedArea !== '') {
                    items = items.filter(item => item.area_id == this.selectedArea);
                }

                if (this.search !== '') {
                    const searchLower = this.search.toLowerCase();
                    items = items.filter(item => {
                        const nameMatch = item.name ? item.name.toLowerCase().includes(searchLower) : false;
                        const descMatch = item.description ? item.description.toLowerCase().includes(searchLower) : false;
                        const codeMatch = item.code ? item.code.toLowerCase().includes(searchLower) : false;
                        const areaMatch = (item.area && item.area.name.toLowerCase().includes(searchLower));

                        return nameMatch || descMatch || codeMatch || areaMatch;
                    });
                }
                
                return items;
            },
            openCreate() {
                this.editMode = false; 
                this.itemId = null; 
                this.itemName = '';
                this.itemCode = '';
                this.itemAddress = '';
                this.itemPhone = '';
                this.itemAreaId = ''; 
                this.modalTitle = 'Crear Nuevo Registro'; 
                this.showModal = true;
            },
            openEdit(item) {
                this.editMode = true; 
                this.itemId = item.id; 
                this.itemName = item.name || item.description || '';
                this.itemCode = item.code || '';
                this.itemAddress = item.address || '';
                this.itemPhone = item.phone || '';
                this.itemAreaId = item.area_id; 
                this.modalTitle = 'Editar Registro'; 
                this.showModal = true;
            }
        }">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-6 animate-slide-up">
            <div class="relative">
                <div class="absolute -left-4 top-0 h-full w-1 bg-[#ff9c00] rounded-full"></div>
                <h2 class="font-montserrat font-extrabold text-3xl md:text-4xl text-[#2c3856] flex items-center gap-3">
                    <span class="bg-white p-2 rounded-xl shadow-sm border border-gray-100 text-[#ff9c00]">
                        <i class="fas {{ $config['icon'] }}"></i>
                    </span>
                    {{ $config['title'] }}
                </h2>
                <p class="text-gray-500 text-sm mt-2 font-medium ml-1">Administración y mantenimiento del catálogo</p>
            </div>
            
            <div class="flex flex-wrap gap-3 items-center">
                <a href="{{ route('ff.admin.index') }}" class="group px-5 py-2.5 bg-white text-gray-600 border border-gray-200 rounded-xl font-bold shadow-sm hover:shadow-md hover:text-[#2c3856] transition-all flex items-center gap-2">
                    <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> <span>Volver</span>
                </a>
                <button @click="openCreate()" class="btn-animated px-6 py-2.5 bg-[#2c3856] text-white rounded-xl font-bold flex items-center gap-2 group">
                    <div class="bg-white/20 rounded-lg p-1 group-hover:rotate-90 transition-transform duration-300"><i class="fas fa-plus text-xs"></i></div>
                    <span>Agregar Nuevo</span>
                </button>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-6 mb-8 animate-slide-up" style="animation-delay: 0.1s;">
            
            @if(Auth::user()->isSuperAdmin())
                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-filter"></i>
                    </div>
                    <select x-model="selectedArea" class="block w-full pl-10 pr-8 py-3 bg-white border-none rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] text-gray-700 font-medium focus:ring-2 focus:ring-[#ff9c00] transition-all appearance-none cursor-pointer">
                        <option value="">Todas las Áreas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-search text-gray-400"></i></div>
                <input type="text" x-model="search" class="block w-full pl-11 pr-4 py-3 bg-white border-none rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-[#ff9c00] transition-all" placeholder="Buscar...">
            </div>
            
            <div class="bg-white px-6 py-3 rounded-2xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] flex items-center gap-3 border border-gray-100">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Registros</div>
                <div class="text-2xl font-black text-[#2c3856]" x-text="filteredItems.length"></div>
            </div>
        </div>

        <div class="floating-card overflow-hidden animate-slide-up" style="animation-delay: 0.2s;">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-5 text-xs font-extrabold text-gray-400 uppercase tracking-wider font-montserrat">ID</th>
                            @if(Auth::user()->isSuperAdmin())
                                <th class="px-8 py-5 text-xs font-extrabold text-gray-400 uppercase tracking-wider font-montserrat">Área</th>
                            @endif
                            @if($type === 'warehouses')
                                <th class="px-8 py-5 text-xs font-extrabold text-gray-400 uppercase tracking-wider font-montserrat">Código</th>
                                <th class="px-8 py-5 text-xs font-extrabold text-gray-400 uppercase tracking-wider font-montserrat">Descripción</th>
                                <th class="px-8 py-5 text-xs font-extrabold text-gray-400 uppercase tracking-wider font-montserrat">Teléfono</th>
                            @else
                                <th class="px-8 py-5 text-xs font-extrabold text-gray-400 uppercase tracking-wider font-montserrat w-full">Nombre / Descripción</th>
                            @endif
                            <th class="px-8 py-5 text-xs font-extrabold text-gray-400 uppercase tracking-wider font-montserrat text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <template x-for="item in filteredItems" :key="item.id">
                            <tr class="table-row-modern group">
                                <td class="px-8 py-5">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md bg-gray-100 text-gray-500 font-mono text-xs font-bold group-hover:bg-[#2c3856] group-hover:text-white transition-colors"><span x-text="'#' + item.id"></span></span>
                                </td>
                                
                                @if(Auth::user()->isSuperAdmin())
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border"
                                              :class="item.area ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-gray-50 text-gray-500 border-gray-100'">
                                            <i class="fas fa-building text-[10px]"></i>
                                            <span x-text="item.area ? item.area.name : 'Global/N/A'"></span>
                                        </span>
                                    </td>
                                @endif

                                @if($type === 'warehouses')
                                    <td class="px-8 py-5 font-bold text-[#2c3856]" x-text="item.code"></td>
                                    <td class="px-8 py-5 font-medium text-gray-600" x-text="item.description"></td>
                                    <td class="px-8 py-5 text-sm text-gray-500" x-text="item.phone"></td>
                                @else
                                    <td class="px-8 py-5">
                                        <div class="flex items-center">
                                            <div class="h-2 w-2 rounded-full bg-[#ff9c00] mr-3 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                            <span class="font-semibold text-[#2c3856] text-lg" x-text="item.name"></span>
                                        </div>
                                    </td>
                                @endif

                                <td class="px-8 py-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        
                                        @if($type === 'clients')
                                            <a :href="'/ff/admin/clients/' + item.id + '/branches'" 
                                               class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all text-xs font-bold flex items-center gap-2 mr-2 border border-indigo-100 hover:border-indigo-600">
                                                <i class="fas fa-store-alt"></i> Sucursales
                                            </a>
                                            @if(Auth::user()->hasFfPermission('admin.delivery_conditions'))
                                            <a :href="'/ff/admin/clients/' + item.id + '/conditions'" 
                                               class="px-3 py-1.5 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-600 hover:text-white transition-all text-xs font-bold flex items-center gap-2 mr-2 border border-teal-100 hover:border-teal-600">
                                                <i class="fas fa-clipboard-check"></i> Condiciones
                                            </a>
                                            @endif                                            
                                        @endif

                                        <button @click="openEdit(item)" class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Editar"><i class="fas fa-pencil-alt text-sm"></i></button>
                                        <form :action="'/ff/admin/catalog/{{ $type }}/' + item.id" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-9 h-9 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center shadow-sm" title="Eliminar"><i class="fas fa-trash-alt text-sm"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="filteredItems.length === 0">
                            <tr>
                                <td colspan="{{ Auth::user()->isSuperAdmin() ? ($type === 'warehouses' ? 6 : 4) : ($type === 'warehouses' ? 5 : 3) }}" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center mb-4 animate-bounce"><i class="fas fa-search text-3xl text-gray-300"></i></div>
                                        <h3 class="text-lg font-bold text-[#2c3856]">No se encontraron resultados</h3>
                                        <p class="text-gray-400 text-sm mt-1">Intenta ajustar tus filtros de búsqueda.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-[#2c3856]/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all relative z-10" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95">
                <div class="relative bg-[#2c3856] p-6 overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-[#ff9c00] rounded-full opacity-20 blur-xl"></div>
                    <h3 class="text-white font-montserrat font-bold text-xl relative z-10 flex items-center gap-2"><i class="fas" :class="editMode ? 'fa-edit' : 'fa-plus-circle'"></i> <span x-text="modalTitle"></span></h3>
                    <button @click="showModal = false" class="absolute top-6 right-6 text-white/60 hover:text-white transition-colors z-10"><i class="fas fa-times text-lg"></i></button>
                </div>
                <div class="p-8">
                    <form method="POST" :action="editMode ? '/ff/admin/catalog/{{ $type }}/' + itemId : '/ff/admin/catalog/{{ $type }}'">
                        @csrf
                        <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                        
                        <div class="space-y-6">
                            
                            @if(Auth::user()->isSuperAdmin())
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Asignar al Área</label>
                                <div class="relative">
                                    <select name="area_id" x-model="itemAreaId" class="w-full rounded-xl px-4 py-3 input-modern text-[#2c3856] font-semibold appearance-none cursor-pointer">
                                        <option value="" class="font-bold text-blue-600">🌐 Global / Sin Área (Compartido)</option>
                                        @if(isset($areas))
                                            @foreach($areas as $area)
                                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <template x-if="currentType === 'warehouses'">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Código</label>
                                        <input type="text" name="code" x-model="itemCode" required class="w-full rounded-xl px-4 py-3 input-modern text-[#2c3856] font-semibold" placeholder="Ej. ALM-01">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Descripción</label>
                                        <input type="text" name="description" x-model="itemName" required class="w-full rounded-xl px-4 py-3 input-modern text-[#2c3856] font-semibold" placeholder="Ej. Almacén Central">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Teléfono</label>
                                        <input type="text" name="phone" x-model="itemPhone" required class="w-full rounded-xl px-4 py-3 input-modern text-[#2c3856] font-semibold" placeholder="Ej. 55-1234-5678">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Dirección Completa</label>
                                        <textarea name="address" x-model="itemAddress" required class="w-full rounded-xl px-4 py-3 input-modern text-[#2c3856] font-semibold h-24 resize-none" placeholder="Calle, Número, Colonia, CP..."></textarea>
                                    </div>
                                </div>
                            </template>

                            <template x-if="currentType !== 'warehouses'">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Nombre / Descripción</label>
                                    <input type="text" name="name" x-model="itemName" required autofocus class="w-full rounded-xl px-4 py-3 input-modern text-[#2c3856] font-semibold text-lg placeholder-gray-300" placeholder="Ej. Nombre del registro...">
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-end gap-3 pt-8">
                            <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl text-gray-500 hover:bg-gray-50 font-bold transition-colors text-sm">Cancelar</button>
                            <button type="submit" class="btn-animated px-8 py-2.5 bg-[#ff9c00] hover:bg-[#e08b00] text-white font-bold rounded-xl shadow-lg shadow-orange-500/20 text-sm"><span x-text="editMode ? 'Actualizar' : 'Guardar Registro'"></span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>