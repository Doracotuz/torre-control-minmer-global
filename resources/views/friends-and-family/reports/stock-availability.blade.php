<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');

        :root {
            --c-navy: #2c3856;
            --c-navy-light: #3b4b72;
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

        .pagination-link {
            display: inline-flex; items-center; justify-center;
            width: 32px; height: 32px;
            border-radius: 8px;
            font-size: 11px; font-weight: 700;
            background: white; border: 1px solid #e2e8f0;
            color: var(--c-navy);
            transition: all 0.2s;
        }
        .pagination-link.active {
            background: var(--c-navy); color: white; border-color: var(--c-navy);
        }
        .pagination-link:hover:not(.active) {
            border-color: var(--c-orange); color: var(--c-orange);
        }

        .tech-table th {
            font-family: 'Raleway', sans-serif; font-weight: 800; text-transform: uppercase;
            font-size: 0.7rem; letter-spacing: 0.05em; color: var(--c-navy);
            background: rgba(44, 56, 86, 0.02); border-bottom: 2px solid rgba(44, 56, 86, 0.05);
            padding: 12px 16px;
        }
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
        <div class="orb-float bg-rose-400 w-96 h-96 top-0 left-1/4 mix-blend-multiply"></div>
        <div class="orb-float bg-blue-400 w-80 h-80 bottom-0 right-1/4 mix-blend-multiply" style="animation-delay: -5s;"></div>
    </div>

    <div class="relative min-h-screen py-10 px-4 sm:px-6 lg:px-8 max-w-[1920px] mx-auto">
        
        <x-slot name="header"></x-slot>

        <div class="flex flex-col md:flex-row justify-between items-end mb-10 animate-enter gap-6"> 
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 bg-[#ff9c00] rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-bold tracking-[0.25em] text-[#2c3856] uppercase">Auditoría de Recursos</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-impact font-black text-[#2c3856] leading-none">
                    DISPONIBILIDAD <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">STOCK</span>
                </h2>
                <p class="text-sm text-slate-500 font-medium mt-2 max-w-xl">
                    Monitoreo en tiempo real de existencias físicas vs. comprometidas.
                </p>
            </div>
            
            <a href="{{ route('ff.reports.index') }}"
               class="group flex items-center gap-3 px-6 py-2.5 bg-white/60 backdrop-blur-sm border border-[#2c3856]/10 rounded-lg hover:bg-[#2c3856] hover:text-white transition-all duration-300 shadow-sm">
                <i class="fas fa-arrow-left text-[#ff9c00] group-hover:text-white transition-colors"></i>
                <span class="text-xs font-black uppercase tracking-widest">Panel Principal</span>
            </a>
        </div>

        <div class="space-y-8">

            <div class="animate-enter" style="animation-delay: 0.1s;">
                @if ($lowStockAlerts->count() > 0)
                    <div class="card-complex border-l-4 border-rose-500 overflow-hidden relative">
                        <div class="absolute inset-0 opacity-5 bg-[repeating-linear-gradient(45deg,transparent,transparent_10px,#ef4444_10px,#ef4444_20px)]"></div>
                        <div class="p-6 relative z-10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600 shadow-sm">
                                    <i class="fas fa-radiation animate-pulse"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-impact font-black text-[#2c3856]">CRITICAL_STOCK_LEVEL</h3>
                                    <p class="text-xs font-bold text-rose-500 uppercase tracking-wide">
                                        Atención: {{ $lowStockAlerts->count() }} SKUs requieren reabastecimiento.
                                    </p>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                @foreach ($lowStockAlerts->take(8) as $product)
                                    <div class="bg-white/80 rounded-lg p-3 border border-rose-100 flex justify-between items-center shadow-sm">
                                        <div>
                                            <div class="text-[10px] font-bold text-slate-400">SKU: {{ $product['sku'] }}</div>
                                            <div class="text-xs font-bold text-[#2c3856] truncate max-w-[120px]" title="{{ $product['description'] }}">{{ $product['description'] }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[10px] text-slate-400">Disp.</div>
                                            <div class="text-lg font-black text-rose-600 leading-none">{{ $product['available'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if($lowStockAlerts->count() > 8)
                                    <a href="#inventory-table" class="bg-rose-50/50 rounded-lg p-3 border border-rose-100 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-rose-100 transition-colors group">
                                        <span class="text-xs font-bold text-rose-600 group-hover:scale-110 transition-transform">+ {{ $lowStockAlerts->count() - 8 }} Más</span>
                                        <span class="text-[9px] text-rose-400">Ver en tabla</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-complex border-l-4 border-emerald-500 p-6 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow-sm">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-impact font-black text-[#2c3856]">SISTEMA ÓPTIMO</h3>
                            <p class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Inventario saludable. Ningún producto por debajo del umbral de seguridad.</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1 card-complex p-6 flex flex-col animate-enter" style="animation-delay: 0.2s;">
                    <div class="mb-6">
                        <h3 class="text-base font-impact font-black text-[#2c3856] uppercase flex items-center gap-2">
                            <i class="fas fa-chart-pie text-[#ff9c00]"></i> Top 15 Retención
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">
                            Análisis de items con mayor volumen
                        </p>
                    </div>
                    
                    <div class="flex-grow relative w-full" style="min-height: 400px;">
                        <div id="chart-stock-vs-reserved" class="absolute inset-0"></div>
                    </div>
                </div>

                <div id="inventory-table" class="lg:col-span-2 card-complex overflow-hidden flex flex-col animate-enter" style="animation-delay: 0.3s;">
                    
                    <div class="p-6 border-b border-slate-100 bg-white/50 backdrop-blur-sm flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div>
                            <h3 class="text-base font-impact font-black text-[#2c3856] uppercase">Inventario Maestro</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">
                                {{ $paginatedData->total() }} Referencias Totales
                            </p>
                        </div>

                        <form method="GET" action="{{ route('ff.reports.stockAvailability') }}" class="w-full sm:w-auto flex flex-col sm:flex-row items-center gap-2">
                            
                            @if(Auth::user()->isSuperAdmin())
                                <div class="relative w-full sm:w-40">
                                    <select name="area_id" onchange="this.form.submit()" class="input-tech py-2 font-bold uppercase text-xs cursor-pointer">
                                        <option value="">GLOBAL</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                                {{ strtoupper($area->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="relative w-full sm:w-48">
                                <select name="warehouse_id" onchange="this.form.submit()" class="input-tech py-2 font-bold uppercase text-xs cursor-pointer">
                                    <option value="">TODOS (ALMACÉN)</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->code }} - {{ Str::limit($wh->description, 12) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="relative w-full sm:w-56">
                                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar SKU o Descripción..." class="input-tech pl-9">
                            </div>
                            
                            <button type="submit" class="bg-[#2c3856] text-white rounded-lg w-9 h-9 flex items-center justify-center hover:bg-[#ff9c00] transition-colors shadow-sm">
                                <i class="fas fa-arrow-right text-xs"></i>
                            </button>
                            
                            @if($search)
                                <a href="{{ route('ff.reports.stockAvailability') }}" class="bg-slate-200 text-slate-500 rounded-lg w-9 h-9 flex items-center justify-center hover:bg-slate-300 transition-colors" title="Limpiar Filtro">
                                    <i class="fas fa-times text-xs"></i>
                                </a>
                            @endif

                        </form>
                    </div>
                    
                    <div class="flex-grow overflow-x-auto custom-scroll">
                        <table class="min-w-full divide-y divide-slate-100 tech-table">
                            <thead>
                                <tr>
                                    <th class="text-left w-24">Código SKU</th>
                                    <th class="text-left">Descripción del Producto</th>
                                    <th class="text-right">Físico Total</th>
                                    <th class="text-right">En Reserva</th>
                                    <th class="text-right">Disponible Real</th>
                                    <th class="text-center w-24">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white/40">
                                @forelse ($paginatedData as $product)
                                    <tr class="tech-row transition-colors">
                                        <td class="font-mono text-xs font-bold text-[#2c3856]">
                                            {{ $product['sku'] }}
                                        </td>
                                        <td class="text-xs font-medium text-slate-600 uppercase">
                                            {{ $product['description'] }}
                                        </td>
                                        <td class="text-right font-mono text-xs text-slate-500">
                                            {{ number_format($product['total_stock']) }}
                                        </td>
                                        <td class="text-right font-mono text-xs text-blue-500 font-bold">
                                            {{ $product['total_reserved'] > 0 ? number_format($product['total_reserved']) : '-' }}
                                        </td>
                                        <td class="text-right">
                                            <span class="text-sm font-black font-mono 
                                                @if ($product['available'] <= 0) text-rose-600 
                                                @elseif ($product['available'] < 10) text-[#d97706] 
                                                @else text-emerald-600 @endif">
                                                {{ number_format($product['available']) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($product['available'] <= 0)
                                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-600 border border-rose-200">AGOTADO</span>
                                            @elseif ($product['available'] < 10)
                                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-600 border border-amber-100">BAJO</span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-50 text-emerald-500 text-[10px]"><i class="fas fa-check"></i></span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs font-bold uppercase">
                                            No se encontraron productos con el criterio de búsqueda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t border-slate-100 bg-white/30 backdrop-blur-sm flex justify-between items-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase">
                            Mostrando {{ $paginatedData->firstItem() ?? 0 }} - {{ $paginatedData->lastItem() ?? 0 }} de {{ $paginatedData->total() }}
                        </div>
                        <div class="flex gap-1">
                            {{ $paginatedData->links('pagination::simple-tailwind') }} 
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dataStockVsReserved = @json($chartStockVsReserved);

            var optionsStockVsReserved = {
                series: dataStockVsReserved.series,
                chart: {
                    type: 'bar',
                    height: '100%', 
                    stacked: true,
                    toolbar: { show: false },
                    fontFamily: 'Montserrat, sans-serif',
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        barHeight: '70%',
                        dataLabels: {
                            total: {
                                enabled: true,
                                formatter: (val) => val.toLocaleString(),
                                style: { fontSize: '10px', fontWeight: 900, color: '#2c3856' },
                                offsetX: 5
                            }
                        }
                    },
                },
                dataLabels: { enabled: false },
                stroke: { width: 1, colors: ['#fff'] },
                xaxis: {
                    categories: dataStockVsReserved.categories,
                    labels: { style: { colors: '#64748b', fontSize: '10px', fontWeight: 600 } },
                    axisBorder: { show: false }, axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: '#2c3856', fontSize: '10px', fontWeight: 700 }, maxWidth: 120 }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } },
                    padding: { top: 0, right: 20, bottom: 0, left: 10 } 
                },
                tooltip: {
                    theme: 'light',
                    y: { formatter: (val) => val.toLocaleString() + " uds" }
                },
                fill: { opacity: 0.9 },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '11px',
                    fontWeight: 600,
                    itemMargin: { horizontal: 10 }
                },
                colors: ['#2c3856', '#ff9c00'] 
            };

            var chart = new ApexCharts(document.querySelector("#chart-stock-vs-reserved"), optionsStockVsReserved);
            chart.render();
        });
    </script>
</x-app-layout>