<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');

        :root {
            --c-navy: #2c3856;
            --c-orange: #ff9c00;
            --c-red: #ef4444;
            --c-green: #10b981;
        }

        body { font-family: 'Montserrat', sans-serif; background-color: #f0f2f5; overflow-x: hidden; }

        .complex-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;
            background: linear-gradient(135deg, #eef2f3 0%, #e6e9ef 100%);
        }
        .complex-bg::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                linear-gradient(rgba(44, 56, 86, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(44, 56, 86, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        .orb-float {
            position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.5;
            animation: floatOrb 25s infinite ease-in-out;
        }

        .card-complex {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.05), 
                0 2px 4px -1px rgba(0, 0, 0, 0.03),
                inset 0 0 20px rgba(255, 255, 255, 0.5);
            border-radius: 16px; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-complex:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
        }

        .input-tech {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(44, 56, 86, 0.1);
            color: var(--c-navy);
            font-size: 0.8rem;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s;
        }
        .input-tech:focus {
            background: #fff;
            border-color: var(--c-orange);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 156, 0, 0.15);
        }

        .pagination-btn {
            display: inline-flex; items-center; justify-center;
            width: 32px; height: 32px;
            border-radius: 8px;
            font-size: 11px; font-weight: 700;
            background: white; border: 1px solid #e2e8f0;
            color: var(--c-navy);
            cursor: pointer;
            transition: all 0.2s;
        }
        .pagination-btn.active {
            background: var(--c-navy); color: white; border-color: var(--c-navy);
        }
        .pagination-btn:hover:not(.active):not(:disabled) {
            border-color: var(--c-orange); color: var(--c-orange);
        }
        .pagination-btn:disabled {
            opacity: 0.5; cursor: not-allowed;
        }

        .tech-table th {
            font-family: 'Raleway', sans-serif; font-weight: 800; text-transform: uppercase;
            font-size: 0.7rem; letter-spacing: 0.05em; color: var(--c-navy);
            background: rgba(44, 56, 86, 0.02); border-bottom: 2px solid rgba(44, 56, 86, 0.05);
            padding: 12px 16px;
            cursor: pointer; user-select: none;
        }
        .tech-table th:hover { background: rgba(44, 56, 86, 0.05); }
        .tech-table td {
            font-size: 0.85rem; padding: 12px 16px; border-bottom: 1px solid rgba(0,0,0,0.03);
            vertical-align: middle;
        }
        .tech-row:hover { background-color: rgba(255, 255, 255, 0.8) !important; }

        .font-impact { font-family: 'Raleway', sans-serif; letter-spacing: -0.02em; }
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); }
        .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: var(--c-navy); }

        @keyframes floatOrb { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(40px, -60px); } }
        .animate-enter { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <div class="complex-bg">
        <div class="orb-float bg-indigo-400 w-96 h-96 top-0 left-1/4 mix-blend-multiply"></div>
        <div class="orb-float bg-purple-400 w-80 h-80 bottom-0 right-1/4 mix-blend-multiply" style="animation-delay: -5s;"></div>
    </div>

    <div class="relative min-h-screen py-10 px-4 sm:px-6 lg:px-8 max-w-[1920px] mx-auto"
         x-data="{
            search: '',
            sortCol: 'valor_total',
            sortAsc: false,
            currentPage: 1,
            pageSize: 15,
            sellers: {{ Js::from($sellerPerformanceData->values()) }},
            
            get sortedSellers() {
                let result = this.sellers.filter(s => 
                    s.name.toLowerCase().includes(this.search.toLowerCase())
                );
                
                return result.sort((a, b) => {
                    let valA = a[this.sortCol];
                    let valB = b[this.sortCol];
                    
                    if(typeof valA === 'string') valA = valA.toLowerCase();
                    if(typeof valB === 'string') valB = valB.toLowerCase();
                    
                    if (valA < valB) return this.sortAsc ? -1 : 1;
                    if (valA > valB) return this.sortAsc ? 1 : -1;
                    return 0;
                });
            },
            
            get pagedSellers() {
                const start = (this.currentPage - 1) * this.pageSize;
                const end = start + this.pageSize;
                return this.sortedSellers.slice(start, end);
            },
            
            get totalPages() {
                return Math.ceil(this.sortedSellers.length / this.pageSize);
            },
            
            sortBy(col) {
                if(this.sortCol === col) {
                    this.sortAsc = !this.sortAsc;
                } else {
                    this.sortCol = col;
                    this.sortAsc = false;
                }
            },
            
            formatCurrency(val) {
                return '$' + Number(val).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            },

            formatNumber(val) {
                return Number(val).toLocaleString();
            }
         }">
        
        <x-slot name="header"></x-slot>

        <div class="flex flex-col md:flex-row justify-between items-end mb-10 animate-enter gap-6"> 
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 bg-[#ff9c00] rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-bold tracking-[0.25em] text-[#2c3856] uppercase">Rendimiento Comercial</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-impact font-black text-[#2c3856] leading-none">
                    PERFORMANCE <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">DE VENTAS</span>
                </h2>
                <p class="text-sm text-slate-500 font-medium mt-2 max-w-xl">
                    Análisis detallado de KPIs individuales y ranking de facturación.
                </p>
            </div>
            
            <a href="{{ route('ff.reports.index') }}"
               class="group flex items-center gap-3 px-6 py-2.5 bg-white/60 backdrop-blur-sm border border-[#2c3856]/10 rounded-lg hover:bg-[#2c3856] hover:text-white transition-all duration-300 shadow-sm">
                <i class="fas fa-arrow-left text-[#ff9c00] group-hover:text-white transition-colors"></i>
                <span class="text-xs font-black uppercase tracking-widest">Panel Principal</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 animate-enter" style="animation-delay: 0.1s;">
            <div class="card-complex p-6 relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-[#ff9c00] to-transparent opacity-10 rounded-bl-full transition-all group-hover:scale-110"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-[#ff9c00] shadow-sm">
                            <i class="fas fa-trophy text-lg"></i>
                        </div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Líder en Ventas</h3>
                    </div>
                    <div class="text-3xl font-impact font-black text-[#2c3856]">
                        {{ $sellerPerformanceData->first()['name'] ?? 'N/A' }}
                    </div>
                    <div class="mt-2 text-sm font-bold text-emerald-600 bg-emerald-50 inline-block px-2 py-0.5 rounded">
                        {{ '$' . number_format($sellerPerformanceData->first()['valor_total'] ?? 0, 2) }}
                    </div>
                </div>
            </div>

            <div class="card-complex p-6 relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-emerald-500 to-transparent opacity-10 rounded-bl-full transition-all group-hover:scale-110"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm">
                            <i class="fas fa-receipt text-lg"></i>
                        </div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Mayor Ticket Prom.</h3>
                    </div>
                    @php $maxTicket = $sellerPerformanceData->max('ticket_promedio'); @endphp
                    <div class="text-3xl font-impact font-black text-[#2c3856]">
                        {{ '$' . number_format($maxTicket ?? 0, 2) }}
                    </div>
                    <div class="mt-2 text-xs font-semibold text-slate-500">
                        Agente: {{ $sellerPerformanceData->where('ticket_promedio', $maxTicket)->first()['name'] ?? 'N/A' }}
                    </div>
                </div>
            </div>

            <div class="card-complex p-6 relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-gradient-to-br from-indigo-500 to-transparent opacity-10 rounded-bl-full transition-all group-hover:scale-110"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-sm">
                            <i class="fas fa-shipping-fast text-lg"></i>
                        </div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Volumen Transaccional</h3>
                    </div>
                    <div class="text-3xl font-impact font-black text-[#2c3856]">
                        {{ number_format($sellerPerformanceData->sum('total_pedidos')) }}
                    </div>
                    <div class="mt-2 text-xs font-semibold text-slate-500">
                        Pedidos totales procesados
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-1 card-complex p-6 flex flex-col animate-enter" style="animation-delay: 0.2s;">
                <div class="mb-6">
                    <h3 class="text-base font-impact font-black text-[#2c3856] uppercase flex items-center gap-2">
                        <i class="fas fa-chart-pie text-[#ff9c00]"></i> Distribución de Ingresos
                    </h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">
                        Participación por facturación total
                    </p>
                </div>
                <div class="flex-grow relative w-full flex items-center justify-center" style="min-height: 400px;">
                    <div id="chart-valor-vendedor" class="w-full"></div>
                </div>
            </div>

            <div class="lg:col-span-2 card-complex overflow-hidden flex flex-col animate-enter" style="animation-delay: 0.3s;">
                <div class="p-6 border-b border-slate-100 bg-white/50 backdrop-blur-sm flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-base font-impact font-black text-[#2c3856] uppercase">Ranking de Agentes</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">
                            Listado dinámico con filtros en tiempo real
                        </p>
                    </div>
                    <form method="GET" action="{{ route('ff.reports.sellerPerformance') }}" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        
                        @if(Auth::user()->isSuperAdmin())
                            <div class="relative group w-full sm:w-auto">
                                <label class="absolute -top-2 left-2 bg-white px-1 text-[10px] font-bold text-[#ff9c00] z-10">ÁREA</label>
                                <select name="area_id" onchange="this.form.submit()" class="input-tech py-2 w-full sm:w-40 font-bold uppercase text-xs cursor-pointer">
                                    <option value="">GLOBAL</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                            {{ strtoupper($area->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="relative group w-full sm:w-auto">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[10px] font-bold text-[#ff9c00] z-10">ALMACÉN</label>
                            <select name="warehouse_id" onchange="this.form.submit()" class="input-tech py-2 w-full sm:w-48 font-bold uppercase text-xs cursor-pointer">
                                <option value="">TODOS</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->code }} - {{ Str::limit($wh->description, 15) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <div class="relative w-full sm:w-64">
                        <input type="text" x-model="search" placeholder="Buscar Agente..." class="input-tech pl-9">
                    </div>
                </div>
                
                <div class="flex-grow overflow-x-auto custom-scroll">
                    <table class="min-w-full divide-y divide-slate-100 tech-table">
                        <thead>
                            <tr>
                                <th class="text-left w-10">#</th>
                                <th class="text-left" @click="sortBy('name')">
                                    Agente <i class="fas fa-sort ml-1 opacity-30"></i>
                                </th>
                                <th class="text-right" @click="sortBy('valor_total')">
                                    Facturación <i class="fas fa-sort ml-1 opacity-30"></i>
                                </th>
                                <th class="text-right" @click="sortBy('total_pedidos')">
                                    Pedidos <i class="fas fa-sort ml-1 opacity-30"></i>
                                </th>
                                <th class="text-right" @click="sortBy('total_unidades')">
                                    Unidades <i class="fas fa-sort ml-1 opacity-30"></i>
                                </th>
                                <th class="text-right" @click="sortBy('ticket_promedio')">
                                    Ticket Prom. <i class="fas fa-sort ml-1 opacity-30"></i>
                                </th>
                                <th class="text-right" @click="sortBy('skus_unicos')">
                                    Cobertura <i class="fas fa-sort ml-1 opacity-30"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white/40">
                            <template x-for="(seller, index) in pagedSellers" :key="index">
                                <tr class="tech-row transition-colors">
                                    <td class="font-mono text-xs font-bold text-slate-400" x-text="(currentPage - 1) * pageSize + index + 1"></td>
                                    <td class="font-bold text-xs text-[#2c3856] uppercase" x-text="seller.name"></td>
                                    <td class="text-right font-mono text-xs font-bold text-[#ff9c00]" x-text="formatCurrency(seller.valor_total)"></td>
                                    <td class="text-right font-mono text-xs text-slate-600" x-text="formatNumber(seller.total_pedidos)"></td>
                                    <td class="text-right font-mono text-xs text-slate-600" x-text="formatNumber(seller.total_unidades)"></td>
                                    <td class="text-right font-mono text-xs text-emerald-600 font-bold" x-text="formatCurrency(seller.ticket_promedio)"></td>
                                    <td class="text-right font-mono text-xs text-slate-500" x-text="seller.skus_unicos + ' SKUs'"></td>
                                </tr>
                            </template>
                            <tr x-show="pagedSellers.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-xs font-bold uppercase">
                                    No se encontraron agentes con ese nombre.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100 bg-white/30 backdrop-blur-sm flex justify-between items-center" x-show="sortedSellers.length > 0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase">
                        Página <span x-text="currentPage"></span> de <span x-text="totalPages"></span>
                    </div>
                    <div class="flex gap-1">
                        <button class="pagination-btn" :disabled="currentPage === 1" @click="currentPage--">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <template x-for="page in totalPages" :key="page">
                            <button class="pagination-btn" 
                                :class="{ 'active': currentPage === page }"
                                x-show="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
                                @click="currentPage = page" x-text="page"></button>
                        </template>
                        <button class="pagination-btn" :disabled="currentPage === totalPages" @click="currentPage++">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dataValorVendedor = @json($chartValorVendedor);

            var optionsValorVendedor = {
                series: dataValorVendedor.series,
                chart: {
                    type: 'donut',
                    height: 380,
                    fontFamily: 'Montserrat, sans-serif',
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                labels: dataValorVendedor.labels,
                colors: ['#2c3856', '#ff9c00', '#64748b', '#94a3b8', '#cbd5e1'],
                stroke: { show: true, colors: ['#fff'], width: 2 },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: { show: true, fontSize: '11px', fontWeight: 600, color: '#64748b', offsetY: -5 },
                                value: { 
                                    show: true, 
                                    fontSize: '24px', 
                                    fontWeight: 900, 
                                    color: '#2c3856', 
                                    offsetY: 10,
                                    formatter: function (val) {
                                        return '$' + parseFloat(val).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'FACTURACIÓN',
                                    fontSize: '10px',
                                    fontWeight: 700,
                                    color: '#ff9c00',
                                    formatter: function (w) {
                                        const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        return '$' + total.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    fontWeight: 600,
                    itemMargin: { horizontal: 10, vertical: 5 }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function (val) {
                            return '$' + val.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart-valor-vendedor"), optionsValorVendedor);
            chart.render();
        });
    </script>
</x-app-layout>