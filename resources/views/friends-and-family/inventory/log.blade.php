<x-app-layout>
    <div x-data="logManager()" x-init="init(@js($movements->items()))" class="font-sans text-gray-800">
        
        <x-slot name="header">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-[#2c3856] leading-tight font-[Montserrat]">
                        <i class="fas fa-history mr-2 text-[#ff9c00]"></i> Historial de Movimientos
                    </h2>
                    <p class="text-sm text-gray-500 font-[Montserrat] mt-1">Bitácora completa de cambios en inventario</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('ff.inventory.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white text-[#2c3856] border border-gray-200 rounded-full text-sm font-semibold shadow-sm hover:bg-gray-50 hover:text-[#ff9c00] transition-all duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Volver a Inventario
                    </a>

                    <a href="{{ route('ff.inventory.log.exportCsv') }}" 
                       class="inline-flex items-center px-5 py-2 bg-[#2c3856] text-white rounded-full text-sm font-bold shadow-md hover:bg-[#1e273d] hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                        <i class="fas fa-file-csv mr-2"></i> Exportar Registro
                    </a>
                </div>
            </div>
        </x-slot>

        <div class="py-6">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-3xl p-6 shadow-[0_3px_10px_rgb(0,0,0,0.05)] border border-white/50 flex items-center justify-between relative overflow-hidden group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Movimientos</p>
                        <h3 class="text-3xl font-extrabold text-[#2c3856]" x-text="filteredMovements.length"></h3>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-2xl text-[#2c3856] group-hover:scale-110 group-hover:bg-[#2c3856] group-hover:text-white transition-all duration-300">
                        <i class="fas fa-list-ul fa-xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-[0_3px_10px_rgb(0,0,0,0.05)] border border-white/50 flex items-center justify-between relative overflow-hidden group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Entradas (Vista)</p>
                        <h3 class="text-3xl font-extrabold text-emerald-600" x-text="totalEntries"></h3>
                    </div>
                    <div class="bg-emerald-50 p-4 rounded-2xl text-emerald-600 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                        <i class="fas fa-arrow-down fa-xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-[0_3px_10px_rgb(0,0,0,0.05)] border border-white/50 flex items-center justify-between relative overflow-hidden group hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Salidas (Vista)</p>
                        <h3 class="text-3xl font-extrabold text-red-600" x-text="totalExits"></h3>
                    </div>
                    <div class="bg-red-50 p-4 rounded-2xl text-red-600 group-hover:scale-110 group-hover:bg-red-600 group-hover:text-white transition-all duration-300">
                        <i class="fas fa-arrow-up fa-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                
                <div class="p-6 border-b border-gray-100 flex flex-col lg:flex-row gap-4 justify-between items-center bg-white">
                    
                    <div class="relative w-full lg:w-1/3 group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-300 group-focus-within:text-[#ff9c00] transition-colors duration-300"></i>
                        </div>
                        <input type="text" x-model="search" 
                               class="block w-full pl-11 pr-4 py-3 bg-[#F3F4F6] border-none text-gray-700 rounded-xl focus:ring-2 focus:ring-[#ff9c00] focus:bg-white transition-all duration-200 placeholder-gray-400 font-[Montserrat] text-sm" 
                               placeholder="Buscar por SKU, usuario o motivo...">
                    </div>

                    <div class="flex flex-wrap gap-3 w-full lg:w-auto justify-end items-center">
                        
                        <div class="flex bg-[#F3F4F6] p-1 rounded-xl">
                            <button @click="filterType = 'all'" 
                                    :class="filterType === 'all' ? 'bg-white text-[#2c3856] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-300">
                                Todos
                            </button>
                            <button @click="filterType = 'in'" 
                                    :class="filterType === 'in' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-300 flex items-center gap-1">
                                <i class="fas fa-arrow-down"></i> Entradas
                            </button>
                            <button @click="filterType = 'out'" 
                                    :class="filterType === 'out' ? 'bg-white text-red-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-4 py-2 rounded-lg text-xs font-bold transition-all duration-300 flex items-center gap-1">
                                <i class="fas fa-arrow-up"></i> Salidas
                            </button>
                        </div>

                        <button @click="resetFilters()" x-show="search || filterType !== 'all'" x-transition 
                                class="px-4 py-2.5 text-red-500 bg-red-50 hover:bg-red-100 rounded-xl text-sm font-bold transition-all" 
                                title="Limpiar Filtros">
                            <i class="fas fa-times mr-1"></i> Limpiar
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap text-left">
                        <thead>
                            <tr class="bg-[#F9FAFB] border-b border-gray-100">
                                <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase tracking-wider font-[Montserrat]">Producto</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase tracking-wider font-[Montserrat]">Usuario</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase tracking-wider text-center font-[Montserrat]">Movimiento</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase tracking-wider font-[Montserrat]">Motivo</th>
                                <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase tracking-wider text-right font-[Montserrat]">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            <template x-for="mov in filteredMovements" :key="mov.id">
                                <tr class="hover:bg-blue-50/90 transition-colors duration-200 group">
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-50 border border-gray-200 overflow-hidden flex items-center justify-center">
                                                <template x-if="mov.product && mov.product.photo_url">
                                                    <img :src="mov.product.photo_url" :alt="mov.product.sku" class="h-full w-full object-contain bg-white">
                                                </template>
                                                
                                                <template x-if="!mov.product || !mov.product.photo_url">
                                                    <div class="text-gray-400 group-hover:text-[#2c3856] transition-colors">
                                                        <i class="fas fa-box"></i>
                                                    </div>
                                                </template>
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-[#2c3856]" x-text="mov.product ? mov.product.description : 'Producto Eliminado'"></div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-xs text-gray-400 font-mono bg-gray-100 inline-block px-1.5 py-0.5 rounded" x-text="mov.product ? mov.product.sku : '---'"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-[#e8ecf7] text-[#2c3856] flex items-center justify-center text-xs font-bold border border-blue-100">
                                                <span x-text="getInitials(mov.user ? mov.user.name : '?')"></span>
                                            </div>
                                            <span class="text-sm font-medium text-gray-600" x-text="mov.user ? mov.user.name : 'N/A'"></span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-bold border transition-all duration-300"
                                             :class="mov.quantity > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100'">
                                            <i class="fas mr-1.5" :class="mov.quantity > 0 ? 'fa-arrow-down' : 'fa-arrow-up'"></i>
                                            <span x-text="(mov.quantity > 0 ? '+' : '') + mov.quantity"></span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600 max-w-[250px] truncate" :title="mov.reason" x-text="mov.reason"></div>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-col items-end">
                                            <span class="text-sm font-bold text-gray-700" x-text="formatDate(mov.created_at)"></span>
                                            <span class="text-xs text-gray-400 font-medium" x-text="formatTime(mov.created_at)"></span>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="filteredMovements.length === 0">
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="bg-gray-50 rounded-full p-6 mb-4 shadow-inner">
                                                <i class="fas fa-clipboard-list text-gray-300 text-3xl"></i>
                                            </div>
                                            <h3 class="text-lg font-bold text-[#2c3856]">No se encontraron movimientos</h3>
                                            <p class="text-gray-500 text-sm mt-1">Intenta ajustar los términos de búsqueda o filtros.</p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                @if($movements->hasPages())
                <div class="bg-gray-50/50 px-6 py-4 border-t border-gray-100">
                    {{ $movements->links() }}
                </div>
                @endif
                
                <div class="bg-gray-50/50 px-6 py-2 border-t border-gray-100 flex items-center justify-between text-xs font-medium text-gray-400">
                    <span x-text="`Visualizando ${filteredMovements.length} registros en esta página`"></span>
                    <span>Mostrando últimos 50 registros</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function logManager() {
            return {
                movements: [],
                search: '',
                filterType: 'all', // all, in, out

                init(data) {
                    this.movements = data;
                },

                get filteredMovements() {
                    const searchLower = this.search.toLowerCase();
                    
                    return this.movements.filter(mov => {
                        // Filter by Type
                        if (this.filterType === 'in' && mov.quantity <= 0) return false;
                        if (this.filterType === 'out' && mov.quantity >= 0) return false;

                        // Search
                        if (this.search) {
                            const productName = mov.product ? mov.product.description.toLowerCase() : '';
                            const productSku = mov.product ? mov.product.sku.toLowerCase() : '';
                            const userName = mov.user ? mov.user.name.toLowerCase() : '';
                            const reason = mov.reason.toLowerCase();
                            
                            return productName.includes(searchLower) || 
                                   productSku.includes(searchLower) || 
                                   userName.includes(searchLower) || 
                                   reason.includes(searchLower);
                        }
                        return true;
                    });
                },

                get totalEntries() {
                    return this.filteredMovements
                        .filter(m => m.quantity > 0)
                        .reduce((acc, m) => acc + parseInt(m.quantity), 0);
                },

                get totalExits() {
                    return Math.abs(this.filteredMovements
                        .filter(m => m.quantity < 0)
                        .reduce((acc, m) => acc + parseInt(m.quantity), 0));
                },

                getInitials(name) {
                    if (!name) return '?';
                    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
                },

                formatTime(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', hour12: true });
                },

                resetFilters() {
                    this.search = '';
                    this.filterType = 'all';
                }
            }
        }
    </script>
</x-app-layout>