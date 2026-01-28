<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Raleway:wght@700;800;900&display=swap');
        :root { --c-navy: #2c3856; --c-orange: #ff9c00; --c-teal: #0d9488; --c-rose: #e11d48; }
        body { font-family: 'Montserrat', sans-serif; background-color: #f0f2f5; overflow-x: hidden; }
        .complex-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2; background: linear-gradient(135deg, #eef2f3 0%, #e6e9ef 100%); }
        .complex-bg::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: radial-gradient(rgba(44, 56, 86, 0.05) 1px, transparent 1px); background-size: 24px 24px; }
        .card-glass {
            background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.6); box-shadow: 0 8px 32px rgba(44, 56, 86, 0.08);
            border-radius: 20px; transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-glass:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(44, 56, 86, 0.12); }
        .font-impact { font-family: 'Raleway', sans-serif; letter-spacing: -0.02em; }
        .tech-input { background: rgba(255,255,255,0.9); border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.75rem; font-weight: 700; color: var(--c-navy); padding: 8px 12px; transition: all 0.2s; }
        .tech-input:focus { border-color: var(--c-orange); outline: none; box-shadow: 0 0 0 3px rgba(255, 156, 0, 0.15); }
        .analytics-table th { background: rgba(44, 56, 86, 0.04); color: var(--c-navy); font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 12px 16px; cursor: pointer; }
        .analytics-table td { padding: 10px 16px; border-bottom: 1px solid rgba(0,0,0,0.03); font-size: 0.8rem; }
        .analytics-row:hover { background-color: rgba(255, 255, 255, 0.95); }
        .badge-risk-low { background: #d1fae5; color: #047857; }
        .badge-risk-med { background: #ffedd5; color: #c2410c; }
        .badge-risk-high { background: #ffe4e6; color: #be123c; }
        .animate-enter { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(20px); }
        @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    <script>
        window.serverData = {
            clients: @json($clientSuggestions),
            initialSearch: '{{ $search }}',
            chartHistory: @json($chartHistory),
            chartDays: @json($chartDays),
            chartChannels: @json($chartChannels)
        };
    </script>

    <div class="complex-bg">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-gradient-to-b from-blue-100/40 to-transparent rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-gradient-to-t from-orange-100/40 to-transparent rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <div class="relative min-h-screen py-8 px-4 sm:px-6 lg:px-8 max-w-[1920px] mx-auto"
         x-data="{
            search: '{{ $search }}',
            sortCol: 'valor',
            sortAsc: false,
            pageSize: 20,
            currentPage: 1,
            clients: {{ Js::from($clientsData->values()) }},
            get filteredClients() {
                const term = this.search.toLowerCase();
                return this.clients.filter(c => c.name.toLowerCase().includes(term));
            },
            get sortedClients() {
                return this.filteredClients.sort((a, b) => {
                    let valA = a[this.sortCol], valB = b[this.sortCol];
                    if(typeof valA === 'string') valA = valA.toLowerCase();
                    if(typeof valB === 'string') valB = valB.toLowerCase();
                    return this.sortAsc ? (valA > valB ? 1 : -1) : (valA < valB ? 1 : -1);
                });
            },
            get pagedClients() {
                const start = (this.currentPage - 1) * this.pageSize;
                return this.sortedClients.slice(start, start + this.pageSize);
            },
            get totalPages() { return Math.ceil(this.sortedClients.length / this.pageSize); },
            sortBy(col) { this.sortAsc = (this.sortCol === col) ? !this.sortAsc : false; this.sortCol = col; },
            formatMoney(val) { return '$' + Number(val).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2}); },
            get totalFilteredSales() { return this.filteredClients.reduce((sum, c) => sum + c.valor, 0); }
         }">

        <div class="flex flex-col lg:flex-row justify-between items-end mb-8 animate-enter gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-2 h-2 rounded-full bg-[#ff9c00] animate-pulse"></span>
                    <span class="text-[10px] font-black tracking-[0.2em] text-[#2c3856] uppercase">Inteligencia de Negocio</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-impact font-black text-[#2c3856] leading-none">
                    ANÁLISIS DE <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">CLIENTES 360°</span>
                </h2>
                <p class="text-xs text-slate-500 font-bold mt-2 uppercase tracking-wide">
                    Segmentación, rentabilidad y patrones de consumo.
                </p>
            </div>

            <div class="w-full lg:w-auto bg-white/60 p-3 rounded-xl border border-white shadow-sm">
                <form method="GET" action="{{ route('ff.reports.clientAnalysis') }}" class="flex flex-col md:flex-row gap-3 items-end">
                    @if(Auth::user()->isSuperAdmin())
                        <div class="w-full md:w-auto">
                            <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Área</label>
                            <select name="area_id" onchange="this.form.submit()" class="tech-input cursor-pointer w-full">
                                <option value="">-- GLOBAL --</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ strtoupper($area->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="w-full md:w-auto">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Almacén</label>
                        <select name="warehouse_id" onchange="this.form.submit()" class="tech-input cursor-pointer w-full">
                            <option value="">-- TODOS --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Desde</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="tech-input w-full">
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Hasta</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="tech-input w-full">
                    </div>
                    
                    <div class="w-full md:w-auto relative group" 
                         x-data="clientSearch(window.serverData.clients, window.serverData.initialSearch)">
                        
                        <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Buscar Cliente</label>
                        
                        <div class="relative">
                            <input type="text" 
                                   id="search-input"
                                   name="search" 
                                   x-model="term"
                                   @focus="open = true"
                                   @click.away="open = false"
                                   @keydown.escape="open = false"
                                   @keydown.enter="open = false"
                                   autocomplete="off" 
                                   placeholder="Escriba para buscar..." 
                                   class="tech-input w-48 pl-8 focus:w-64 transition-all duration-300 relative z-20">
                                   

                            <button type="button" 
                                    x-show="term.length > 0" 
                                    @click="term = ''; open = true; $nextTick(() => $el.previousElementSibling.previousElementSibling.focus())"
                                    class="absolute right-2 top-2 text-slate-300 hover:text-rose-500 z-30 transition-colors">
                                <i class="fas fa-times text-xs"></i>
                            </button>

                            <div x-show="open && matches.length > 0" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="absolute left-0 mt-1 w-64 max-h-60 overflow-y-auto bg-white/95 backdrop-blur-md border border-slate-200 rounded-lg shadow-xl z-50 custom-scroll">
                                
                                <ul class="py-1 text-xs">
                                    <template x-for="(match, index) in matches" :key="index">
                                        <li @click="select(match)"
                                            class="px-4 py-2 cursor-pointer hover:bg-[#2c3856] hover:text-white text-slate-600 font-bold uppercase transition-colors border-b border-slate-50 last:border-0 flex justify-between items-center group">
                                            <span x-text="match"></span>
                                            <i class="fas fa-chevron-right opacity-0 group-hover:opacity-100 text-[10px] text-[#ff9c00]"></i>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            
                            <div x-show="open && matches.length === 0 && term !== ''" 
                                 class="absolute left-0 mt-1 w-48 bg-white border border-slate-200 rounded-lg shadow-xl z-50 p-3 text-center">
                                <span class="text-[10px] text-slate-400 font-bold uppercase">Sin coincidencias</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-auto">
                        <button type="submit" class="bg-[#2c3856] hover:bg-[#ff9c00] text-white px-4 py-2 rounded-lg transition-colors text-xs font-bold w-full h-[34px] flex items-center justify-center">
                            <i class="fas fa-filter mr-1"></i> FILTRAR
                        </button>
                    </div>
<div class="w-full md:w-auto ml-2">
    <button type="submit" 
            formaction="{{ route('ff.reports.clientAnalysis.pdf') }}" 
            formtarget="_blank"
            class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg transition-colors text-xs font-bold w-full h-[34px] flex items-center justify-center shadow-lg shadow-rose-600/30">
        <i class="fas fa-file-pdf mr-2"></i> REPORTE 360°
    </button>
</div>                    
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 animate-enter" style="animation-delay: 0.1s;">
            <div class="card-glass p-5 relative overflow-hidden group">
                <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity"><i class="fas fa-wallet text-5xl text-[#2c3856]"></i></div>
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Valor Filtrado</h3>
                <div class="text-2xl font-impact font-black text-[#2c3856]" x-text="formatMoney(totalFilteredSales)"></div>
                <div class="text-[10px] font-bold text-emerald-600 mt-1 flex items-center gap-1">
                    <i class="fas fa-users"></i> <span x-text="filteredClients.length + ' Clientes'"></span>
                </div>
            </div>
            
            <div class="card-glass p-5 relative overflow-hidden group">
                <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity"><i class="fas fa-receipt text-5xl text-[#ff9c00]"></i></div>
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Ticket Promedio</h3>
                <div class="text-2xl font-impact font-black text-[#2c3856]">{{ '$' . number_format($kpis['ticket_promedio_global'], 2) }}</div>
                <div class="text-[10px] font-bold text-slate-400 mt-1">Global del periodo</div>
            </div>

            <div class="card-glass p-5 relative overflow-hidden group">
                <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity"><i class="fas fa-crown text-5xl text-yellow-500"></i></div>
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Cliente Top</h3>
                <div class="text-lg font-impact font-black text-[#2c3856] truncate leading-tight">
                    {{ Str::limit($kpis['mejor_cliente']['name'] ?? 'N/A', 18) }}
                </div>
                <div class="text-[10px] font-bold text-[#ff9c00] mt-1">
                    {{ '$' . number_format($kpis['mejor_cliente']['valor'] ?? 0, 2) }}
                </div>
            </div>

            <div class="card-glass p-5 relative overflow-hidden group border-l-4 border-rose-400">
                <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity"><i class="fas fa-user-clock text-5xl text-rose-500"></i></div>
                <h3 class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Riesgo de Fuga</h3>
                <div class="text-lg font-impact font-black text-[#2c3856] truncate leading-tight">
                    {{ Str::limit($kpis['cliente_riesgo']['name'] ?? 'N/A', 18) }}
                </div>
                <div class="text-[10px] font-bold text-rose-500 mt-1">
                    {{ $kpis['cliente_riesgo']['dias_inactivo'] ?? 0 }} días inactivo
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8 animate-enter" style="animation-delay: 0.2s;">
            <div class="lg:col-span-2 card-glass p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-sm font-impact font-black text-[#2c3856] uppercase"><i class="fas fa-chart-line text-[#ff9c00] mr-2"></i>Tendencia (Valor)</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase">Evolución de compras</p>
                    </div>
                </div>
                <div id="chart-history" class="w-full h-[250px]"></div>
            </div>

            <div class="lg:col-span-1 card-glass p-6">
                <div class="mb-4">
                    <h3 class="text-sm font-impact font-black text-[#2c3856] uppercase"><i class="fas fa-calendar-day text-[#ff9c00] mr-2"></i>Días Pico</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase">Frecuencia por día</p>
                </div>
                <div id="chart-days" class="w-full h-[250px]"></div>
            </div>

            <div class="lg:col-span-1 card-glass p-6">
                 <div class="mb-4">
                    <h3 class="text-sm font-impact font-black text-[#2c3856] uppercase"><i class="fas fa-network-wired text-[#ff9c00] mr-2"></i>Canales</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase">Origen de Venta</p>
                </div>
                <div id="chart-channels" class="w-full h-[250px]"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 animate-enter" style="animation-delay: 0.3s;">
            <div class="card-glass p-6">
                <h3 class="text-sm font-impact font-black text-[#2c3856] uppercase mb-4"><i class="fas fa-box-open text-[#ff9c00] mr-2"></i>Productos Top (Volumen)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="border-b border-slate-200">
                            <tr>
                                <th class="py-2 text-[10px] font-bold text-slate-400 uppercase">SKU</th>
                                <th class="py-2 text-[10px] font-bold text-slate-400 uppercase">Descripción</th>
                                <th class="py-2 text-[10px] font-bold text-slate-400 uppercase text-right">Piezas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($topProducts as $prod)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-2 text-xs font-mono font-bold text-[#2c3856]">{{ $prod->sku }}</td>
                                <td class="py-2 text-[10px] font-bold text-slate-600 uppercase truncate max-w-[200px]">{{ $prod->description }}</td>
                                <td class="py-2 text-xs font-bold text-emerald-600 text-right">{{ number_format($prod->qty) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-glass p-6">
                <h3 class="text-sm font-impact font-black text-[#2c3856] uppercase mb-4"><i class="fas fa-store-alt text-[#ff9c00] mr-2"></i>Sucursales Top</h3>
                <div class="space-y-4">
                    @foreach($topBranches as $index => $branch)
                    <div class="relative">
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-xs font-bold text-[#2c3856] uppercase">{{ $index + 1 }}. {{ $branch->name ?: 'Sucursal General' }}</span>
                            <span class="text-xs font-mono font-bold text-[#ff9c00]">{{ '$' . number_format($branch->total) }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            @php $percent = ($topBranches->first()->total > 0) ? ($branch->total / $topBranches->first()->total) * 100 : 0; @endphp
                            <div class="bg-[#2c3856] h-2 rounded-full" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                    @endforeach
                    @if($topBranches->isEmpty())
                        <div class="text-center py-8 text-xs text-slate-400">No hay datos de sucursales disponibles.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-glass overflow-hidden flex flex-col animate-enter" style="animation-delay: 0.4s;">
            <div class="p-6 border-b border-slate-100 bg-white/40 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="text-lg font-impact font-black text-[#2c3856] uppercase">Listado de Clientes</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">Análisis detallado de comportamiento</p>
                </div>
                <div class="relative w-full sm:w-72">
                    <input type="text" x-model="search" placeholder="Filtrar en tabla..." class="tech-input pl-9 w-full bg-white shadow-sm border-transparent focus:border-[#ff9c00]">
                </div>
            </div>

            <div class="flex-grow overflow-x-auto custom-scroll">
                <table class="w-full analytics-table">
                    <thead>
                        <tr>
                            <th class="text-left pl-6" @click="sortBy('name')">Cliente <i class="fas fa-sort ml-1 opacity-30"></i></th>
                            <th class="text-right" @click="sortBy('valor')">Monto Total <i class="fas fa-sort ml-1 opacity-30"></i></th>
                            <th class="text-right" @click="sortBy('frecuencia')">Transacciones <i class="fas fa-sort ml-1 opacity-30"></i></th>
                            <th class="text-right" @click="sortBy('ticket_promedio')">Ticket Prom. <i class="fas fa-sort ml-1 opacity-30"></i></th>
                            <th class="text-center" @click="sortBy('ultima_compra')">Última Compra <i class="fas fa-sort ml-1 opacity-30"></i></th>
                            <th class="text-center" @click="sortBy('dias_inactivo')">Estado <i class="fas fa-sort ml-1 opacity-30"></i></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white/30">
                        <template x-for="client in pagedClients" :key="client.name">
                            <tr class="analytics-row transition-colors">
                                <td class="pl-6 font-bold text-xs text-[#2c3856] uppercase">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded bg-[#2c3856] text-white flex items-center justify-center text-[10px] font-black" x-text="client.name.substring(0,1)"></div>
                                        <span x-text="client.name"></span>
                                    </div>
                                </td>
                                <td class="text-right font-mono text-xs font-black text-[#ff9c00]" x-text="formatMoney(client.valor)"></td>
                                <td class="text-right font-mono text-xs text-slate-600">
                                    <span class="px-2 py-0.5 rounded bg-slate-100" x-text="client.frecuencia"></span>
                                </td>
                                <td class="text-right font-mono text-xs font-bold text-emerald-600" x-text="formatMoney(client.ticket_promedio)"></td>
                                <td class="text-center text-[10px] font-bold text-slate-500 uppercase" x-text="client.ultima_compra"></td>
                                <td class="text-center">
                                    <span class="px-2 py-1 rounded text-[9px] font-black uppercase tracking-wide border"
                                          :class="{
                                            'badge-risk-low border-emerald-200': client.status_riesgo === 'Bajo',
                                            'badge-risk-med border-orange-200': client.status_riesgo === 'Medio',
                                            'badge-risk-high border-rose-200': client.status_riesgo === 'Alto'
                                          }">
                                        <span x-text="client.status_riesgo === 'Alto' ? 'RIESGO' : (client.status_riesgo === 'Medio' ? 'INACTIVO' : 'ACTIVO')"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="p-4 bg-white/60 border-t border-slate-100 flex justify-between items-center" x-show="sortedClients.length > 0">
                <div class="text-[10px] font-bold text-slate-500 uppercase">
                    Mostrando <span x-text="((currentPage-1)*pageSize)+1"></span> - <span x-text="Math.min(currentPage*pageSize, sortedClients.length)"></span> de <span x-text="sortedClients.length"></span>
                </div>
                <div class="flex gap-2">
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-300 bg-white hover:bg-[#ff9c00] hover:text-white transition-colors disabled:opacity-50" 
                            :disabled="currentPage === 1" @click="currentPage--"><i class="fas fa-chevron-left text-xs"></i></button>
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-300 bg-white hover:bg-[#ff9c00] hover:text-white transition-colors disabled:opacity-50" 
                            :disabled="currentPage === totalPages" @click="currentPage++"><i class="fas fa-chevron-right text-xs"></i></button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('clientSearch', (suggestionsData, initialSearch) => ({
                open: false,
                term: initialSearch,
                suggestions: suggestionsData || [],
                
                get matches() {
                    if (this.term === '') return []; 
                    
                    return this.suggestions.filter(s => 
                        s.toLowerCase().includes(this.term.toLowerCase())
                    );
                },
                
                select(val) {
                    this.term = val;
                    this.open = false;
                    setTimeout(() => {
                        document.getElementById('search-input').form.submit();
                    }, 100);
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function () {
            
            const histData = window.serverData.chartHistory;
            const histSeriesData = Array.isArray(histData.data) ? histData.data.map(Number) : [];
            const histCats = Array.isArray(histData.categories) ? histData.categories : [];

            new ApexCharts(document.querySelector("#chart-history"), {
                series: [{ name: 'Ventas ($)', data: histSeriesData }],
                chart: { type: 'area', height: 250, toolbar: { show: false }, fontFamily: 'Montserrat' },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1 } },
                dataLabels: { enabled: false },
                xaxis: { 
                    categories: histCats, 
                    labels: { style: { colors: '#64748b', fontSize: '10px' } }, 
                    axisBorder: { show: false }, 
                    axisTicks: { show: false } 
                },
                yaxis: { 
                    labels: { style: { colors: '#64748b', fontSize: '10px' }, formatter: (val) => '$' + (val/1000).toFixed(0) + 'k' } 
                },
                grid: { borderColor: '#f1f5f9' },
                colors: ['#ff9c00'],
            }).render();

            const daysData = window.serverData.chartDays;
            new ApexCharts(document.querySelector("#chart-days"), {
                series: [{ name: 'Transacciones', data: daysData.transactions || [] }],
                chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'Montserrat' },
                plotOptions: { bar: { borderRadius: 4, distributed: true } },
                dataLabels: { enabled: false },
                xaxis: { 
                    categories: daysData.labels, 
                    labels: { style: { colors: '#64748b', fontSize: '10px' } }, 
                    axisBorder: { show: false }, 
                    axisTicks: { show: false } 
                },
                yaxis: { show: false },
                grid: { show: false },
                colors: ['#2c3856', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#e2e8f0'],
                legend: { show: false }
            }).render();

            const channelsData = window.serverData.chartChannels;
            
            let donutSeries = [];
            if (Array.isArray(channelsData.series)) {
                donutSeries = channelsData.series.map(val => parseFloat(val) || 0);
            } else if (typeof channelsData.series === 'object' && channelsData.series !== null) {
                donutSeries = Object.values(channelsData.series).map(val => parseFloat(val) || 0);
            }

            if (donutSeries.length === 0 || donutSeries.every(val => val === 0)) {
                document.querySelector("#chart-channels").innerHTML = 
                    '<div class="flex items-center justify-center h-full text-xs text-slate-400 font-bold uppercase">Sin datos de canales</div>';
            } else {
                new ApexCharts(document.querySelector("#chart-channels"), {
                    series: donutSeries,
                    chart: { type: 'donut', height: 250, fontFamily: 'Montserrat' },
                    labels: Object.values(channelsData.labels || []),
                    colors: ['#0d9488', '#2c3856', '#ff9c00', '#f43f5e', '#64748b'],
                    legend: { position: 'bottom', fontSize: '10px' },
                    dataLabels: { enabled: false },
                    plotOptions: {
                        pie: { donut: { size: '70%' } }
                    }
                }).render();
            }
        });
    </script>
</x-app-layout>