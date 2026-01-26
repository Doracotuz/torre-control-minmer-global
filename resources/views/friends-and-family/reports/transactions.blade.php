<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@700;800;900&display=swap');

        :root {
            --c-navy: #2c3856;
            --c-navy-light: #3b4b72;
            --c-orange: #ff9c00;
            --c-dark: #1a1f2e;
        }

        body { font-family: 'Montserrat', sans-serif; background-color: #f0f2f5; overflow-x: hidden; }
        
        .complex-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;
            background: linear-gradient(125deg, #eef2f3 0%, #eef2f3 40%, #e2e6ea 100%);
        }
        .complex-bg::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(var(--c-navy) 1px, transparent 1px);
            background-size: 40px 40px; opacity: 0.05;
        }
        .orb-float {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4;
            animation: floatOrb 20s infinite ease-in-out;
        }

        .card-complex {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 40px -10px rgba(44, 56, 86, 0.1), inset 0 0 20px rgba(255, 255, 255, 0.5);
            border-radius: 16px; transition: all 0.3s ease;
        }
        .card-complex:hover { transform: translateY(-2px); box-shadow: 0 20px 50px -10px rgba(44, 56, 86, 0.15); }

        .font-impact { font-family: 'Raleway', sans-serif; letter-spacing: -0.02em; }
        .input-cockpit {
            background: rgba(255,255,255,0.6); border: 1px solid rgba(44, 56, 86, 0.15);
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
            color: var(--c-navy); padding: 8px 12px; border-radius: 6px; transition: all 0.3s;
            width: 100%;
        }
        .input-cockpit:focus {
            background: #fff; border-color: var(--c-orange); outline: none;
            box-shadow: 0 0 0 3px rgba(255, 156, 0, 0.15);
        }

        .tech-table th {
            font-family: 'Raleway', sans-serif; font-weight: 800; text-transform: uppercase;
            letter-spacing: 0.05em; color: var(--c-navy); font-size: 0.65rem;
            background: rgba(44, 56, 86, 0.03); border-bottom: 2px solid rgba(44, 56, 86, 0.1);
        }
        .tech-table td {
            font-size: 0.8rem; border-bottom: 1px solid rgba(0,0,0,0.03);
            font-variant-numeric: tabular-nums;
        }
        .tech-row:hover { background-color: rgba(255, 156, 0, 0.05) !important; }

        @keyframes floatOrb { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(30px, -50px); } }
        .animate-enter { animation: slideUpFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <div class="complex-bg">
        <div class="orb-float bg-[#ff9c00] w-64 h-64 top-10 right-20"></div>
        <div class="orb-float bg-[#2c3856] w-96 h-96 bottom-0 left-10"></div>
    </div>

    <div x-data="{ 
        showDetailModal: false, 
        currentFolio: null, 
        saleDetails: [],
        isLoading: false,
        
        fetchDetails(folio) {
            this.isLoading = true;
            this.currentFolio = folio;
            this.saleDetails = [];
            this.showDetailModal = true;
            
            const url = '{{ route('ff.reports.api.saleDetails', ['folio' => '__FOLIO__']) }}'.replace('__FOLIO__', folio);
            
            fetch(url)
            .then(response => response.json())
            .then(data => {
                this.saleDetails = data.items;
            })
            .catch(error => {
                console.error('Error al cargar los detalles:', error);
                alert('Error al cargar los detalles de la venta.');
            })
            .finally(() => {
                this.isLoading = false;
            });
        },
        
        formatCurrency(value) {
            if (typeof value === 'string') {
                value = parseFloat(value.replace(/[^0-9.-]+/g,''));
            }
            if (isNaN(value)) return '$0.00';
            return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
        },
        
        clearFilter() {
            window.location = '{{ route('ff.reports.transactions') }}';
        },
        
        clearVendedor() {
            document.getElementById('vendedor_id').value = '';
            document.getElementById('filter-form').submit();
        }
    }" class="relative min-h-screen py-8 px-4 sm:px-6 lg:px-8 max-w-[1920px] mx-auto">

        <x-slot name="header"></x-slot>

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 animate-enter gap-4"> 
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-2 h-2 bg-[#ff9c00] rounded-full animate-pulse"></div>
                    <span class="text-[9px] font-bold tracking-[0.2em] text-[#2c3856] uppercase">Base de Datos</span>
                </div>
                <h2 class="text-4xl font-impact font-black text-[#2c3856] leading-none">
                    REPORTE DE<span class="text-[#ff9c00]"> TRANSACCIONES</span>
                </h2>
            </div>
            
            <a href="{{ route('ff.reports.index') }}"
               class="group flex items-center gap-3 px-6 py-2 bg-white/80 border border-[#2c3856]/10 rounded-lg hover:bg-[#2c3856] hover:text-white transition-all duration-300 shadow-sm">
                <i class="fas fa-undo-alt text-[#ff9c00] group-hover:text-white transition-colors"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">Regresar al Panel</span>
            </a>
        </div>

        <div class="card-complex p-6 mb-8 animate-enter" style="animation-delay: 0.1s;">
            <div class="flex items-center gap-2 mb-4 border-b border-slate-200 pb-2">
                <i class="fas fa-search text-[#ff9c00]"></i>
                <h3 class="text-xs font-black text-[#2c3856] uppercase tracking-wider">Parámetros de Búsqueda</h3>
            </div>

            <form method="GET" action="{{ route('ff.reports.transactions') }}" id="filter-form">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-12 gap-4 items-end">
                    
                    <div class="lg:col-span-2">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Query (Folio/Cliente)</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Input Data..." class="input-cockpit pl-8">
                        </div>
                    </div>

                    @if(Auth::user()->isSuperAdmin())
                        <div class="lg:col-span-2">
                            <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">ÁREA</label>
                            <div class="relative">
                                <select name="area_id" onchange="this.form.submit()" class="input-cockpit cursor-pointer appearance-none">
                                    <option value="">GLOBAL</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                            {{ strtoupper($area->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="lg:col-span-2">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Almacén</label>
                        <div class="relative">
                            <select name="warehouse_id" onchange="this.form.submit()" class="input-cockpit cursor-pointer appearance-none">
                                <option value="">-- Todos los Almacenes --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->code }} - {{ $wh->description }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>                    

                    <div class="lg:col-span-2">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Agente de Venta</label>
                        <div class="relative">
                            <select name="vendedor_id" id="vendedor_id" class="input-cockpit cursor-pointer appearance-none">
                                <option value="">TODOS LOS AGENTES</option>
                                @foreach ($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}" @if ($userIdFilter == $vendedor->id) selected @endif>
                                        {{ strtoupper($vendedor->name) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                            @if ($userIdFilter)
                                <button type="button" @click="clearVendedor()" class="absolute -right-6 top-2 text-rose-500 hover:text-rose-700 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-3 grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Rango Inicial</label>
                            <input type="datetime-local" name="start_date" id="start_date" value="{{ $startDate }}" class="input-cockpit">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Rango Final</label>
                            <input type="datetime-local" name="end_date" id="end_date" value="{{ $endDate }}" class="input-cockpit">
                        </div>
                    </div>

                    <div class="lg:col-span-1 flex justify-end gap-2">
                        @if ($userIdFilter || $search || $startDate || $endDate)
                            <button type="button" @click="clearFilter()" class="h-[34px] w-[34px] flex items-center justify-center border border-slate-300 rounded text-slate-500 hover:bg-slate-100 transition-colors" title="Reset">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        @endif
                        <button type="submit" class="h-[34px] px-4 bg-[#2c3856] text-white text-[10px] font-black uppercase tracking-widest rounded hover:bg-[#ff9c00] transition-colors shadow-lg flex-grow">
                            Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-complex overflow-hidden animate-enter" style="animation-delay: 0.2s;">
            @if ($sales->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-slate-400 text-xl"></i>
                    </div>
                    <p class="text-sm font-bold text-[#2c3856]">SIN RESULTADOS</p>
                    <p class="text-xs text-slate-500">Ajuste los filtros de búsqueda.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 tech-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left">Ref. Folio</th>
                                <th class="px-6 py-4 text-left">Timestamp</th>
                                <th class="px-6 py-4 text-left">Agente</th>
                                <th class="px-6 py-4 text-left">Cliente / Destino</th>
                                <th class="px-6 py-4 text-right">SKUs</th>
                                <th class="px-6 py-4 text-right">Volumen</th>
                                <th class="px-6 py-4 text-right">Valor Neto</th>
                                <th class="px-6 py-4 text-right">Control</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/50 divide-y divide-gray-100">
                            @foreach ($sales as $sale)
                                <tr class="tech-row transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono font-bold text-[#ff9c00]">#{{ $sale->folio }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-[#2c3856] font-medium">
                                        {{ $sale->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold uppercase text-slate-500">
                                        {{ $sale->user->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-[#2c3856]">
                                        {{ $sale->client_name ?? 'CLIENTE GENERAL' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-slate-500">
                                        {{ $sale->total_items }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-xs">{{ number_format($sale->total_units) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="font-mono font-bold text-emerald-600">{{ '$' . number_format($sale->total_value, 2) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <button @click="fetchDetails({{ $sale->folio }})" class="text-[#2c3856] hover:text-[#ff9c00] transition-colors" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @php
                                            $firstMovement = \App\Models\ffInventoryMovement::where('folio', $sale->folio)->where('quantity', '<', 0)->first();
                                            $movementId = $firstMovement->id ?? null;
                                        @endphp

                                        @if ($movementId)
                                            <form action="{{ route('ff.reports.reprintReceipt', $movementId) }}" method="POST" target="_blank" class="inline">
                                                @csrf
                                                <button type="submit" class="text-slate-400 hover:text-[#2c3856] transition-colors" title="Imprimir Ticket">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
        
        <div x-show="showDetailModal" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
            <div x-show="showDetailModal" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
                 class="absolute inset-0 bg-[#2c3856]/80 backdrop-blur-md transition-opacity" @click="showDetailModal = false"></div>

            <div class="relative w-full h-full flex items-center justify-center p-4 pointer-events-none">
                <div x-show="showDetailModal" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-10" x-transition:enter-end="opacity-100 scale-100 translate-y-0" 
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-10" 
                     class="pointer-events-auto w-full max-w-4xl bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] border border-white/50">
                    
                    <div class="bg-[#2c3856] px-6 py-6 relative overflow-hidden flex-shrink-0">
                        <div class="absolute -right-10 -top-10 w-40 h-40 bg-[#ff9c00] rounded-full opacity-20 blur-2xl"></div>
                        <div class="relative z-10 flex justify-between items-start">
                            <div>
                                <p class="text-[9px] font-black text-[#ff9c00] uppercase tracking-widest mb-1">INSPECCIÓN DE TRANSACCIÓN</p>
                                <h3 class="text-2xl font-impact font-black text-white">
                                    FOLIO #<span x-text="currentFolio"></span>
                                </h3>
                            </div>
                            <button @click="showDetailModal = false" class="text-white/50 hover:text-white transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex-grow overflow-y-auto p-0 bg-slate-50">
                        <template x-if="isLoading">
                            <div class="flex flex-col items-center justify-center py-20">
                                <div class="w-10 h-10 border-4 border-[#2c3856] border-t-[#ff9c00] rounded-full animate-spin"></div>
                                <p class="text-[10px] font-bold text-slate-400 mt-4 uppercase tracking-widest">Recuperando Data...</p>
                            </div>
                        </template>
                        
                        <template x-if="!isLoading && saleDetails.length > 0">
                            <div>
                                <table class="min-w-full divide-y divide-slate-200 tech-table">
                                    <thead class="bg-slate-100">
                                        <tr>
                                            <th class="px-6 py-3 text-left">Código SKU</th>
                                            <th class="px-6 py-3 text-left">Descripción</th>
                                            <th class="px-6 py-3 text-right">Cantidad</th>
                                            <th class="px-6 py-3 text-right">P. Unitario</th>
                                            <th class="px-6 py-3 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-100">
                                        <template x-for="item in saleDetails" :key="item.sku">
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-6 py-3 whitespace-nowrap text-xs font-mono font-bold text-[#2c3856]" x-text="item.sku"></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-xs text-slate-600 font-bold uppercase" x-text="item.description"></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-right text-xs font-bold text-[#ff9c00]" x-text="item.quantity"></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-right text-xs font-mono text-slate-500" x-text="item.unit_price"></td>
                                                <td class="px-6 py-3 whitespace-nowrap text-right text-xs font-mono font-bold text-emerald-600" x-text="item.total_price"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="bg-[#2c3856] text-white">
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-right text-xs font-black uppercase tracking-widest">Valor Total de Venta:</td>
                                            <td class="px-6 py-4 text-right text-base font-mono font-bold text-[#ff9c00]" x-text="formatCurrency(saleDetails.reduce((sum, item) => sum + parseFloat(item.total_price.replace(/[^0-9.-]+/g,'')), 0))"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </template>

                        <template x-if="!isLoading && saleDetails.length === 0">
                            <div class="text-center py-12 text-slate-400 text-xs font-bold uppercase">
                                No hay datos de ítems asociados.
                            </div>
                        </template>
                    </div>

                    <div class="bg-white px-6 py-4 border-t border-slate-100 flex justify-end">
                        <button @click="showDetailModal = false" type="button" class="px-6 py-2 border border-slate-200 rounded text-xs font-bold uppercase text-slate-600 hover:bg-slate-50 transition-colors">
                            Cerrar Panel
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>