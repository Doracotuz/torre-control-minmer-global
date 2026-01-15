<x-app-layout>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@700;800;900&display=swap');

        :root {
            --c-navy: #2c3856;
            --c-orange: #ff9c00;
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
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 40px -10px rgba(44, 56, 86, 0.1), inset 0 0 20px rgba(255, 255, 255, 0.5);
            border-radius: 16px; transition: all 0.3s ease;
        }
        .card-complex:hover { transform: translateY(-3px); box-shadow: 0 20px 50px -10px rgba(44, 56, 86, 0.15); }

        .font-impact { font-family: 'Raleway', sans-serif; letter-spacing: -0.02em; }
        
        @keyframes floatOrb { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(30px, -50px); } }
        .animate-enter { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <div class="complex-bg">
        <div class="orb-float bg-[#ff9c00] w-80 h-80 top-20 left-20"></div>
        <div class="orb-float bg-[#2c3856] w-96 h-96 bottom-10 right-10"></div>
    </div>

    <div class="relative min-h-screen py-10 px-4 sm:px-6 lg:px-8 max-w-[1920px] mx-auto">
        
        <x-slot name="header"></x-slot>

        <div class="flex flex-col md:flex-row justify-between items-end mb-10 animate-enter gap-6"> 
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-3 h-3 bg-[#ff9c00] rounded-full animate-pulse"></div>
                    <span class="text-xs font-bold tracking-[0.2em] text-[#2c3856] uppercase">Módulo de Analítica • Inventario</span>
                </div>
                <h2 class="text-5xl font-impact font-black text-[#2c3856] leading-none">
                    ANÁLISIS DE <span class="text-[#ff9c00]">MOVIMIENTOS</span>
                </h2>
                <p class="text-lg text-slate-500 font-medium mt-2 max-w-2xl">
                    Visualización avanzada de flujo de stock, causas de ajuste y correlación de rotación.
                </p>
            </div>
            
            @if(Auth::user()->isSuperAdmin())
                <div class="mb-4 md:mb-0">
                    <form method="GET" action="{{ route('ff.reports.inventoryAnalysis') }}">
                        <div class="relative group">
                            <label class="absolute -top-2 left-2 bg-white px-1 text-[10px] font-bold text-[#ff9c00] z-10">FILTRAR ÁREA</label>
                            <select name="area_id" onchange="this.form.submit()" class="w-full md:w-64 bg-white border border-[#2c3856]/20 text-[#2c3856] text-xs font-bold uppercase rounded-lg py-2.5 px-3 focus:outline-none focus:border-[#ff9c00] shadow-sm cursor-pointer">
                                <option value="">VISTA GLOBAL</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                        {{ strtoupper($area->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            @endif

            <a href="{{ route('ff.reports.index') }}"
               class="group flex items-center gap-3 px-8 py-3 bg-white/90 border border-[#2c3856]/10 rounded-xl hover:bg-[#2c3856] hover:text-white transition-all duration-300 shadow-md transform hover:-translate-y-1">
                <i class="fas fa-undo-alt text-[#ff9c00] text-lg group-hover:text-white transition-colors"></i>
                <span class="text-sm font-black uppercase tracking-widest">Regresar al Panel</span>
            </a>
            
        </div>

        <div class="grid grid-cols-1 gap-8">

            <div class="card-complex p-8 animate-enter" style="animation-delay: 0.1s;">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 border-b border-slate-200 pb-6">
                    <div>
                        <h3 class="text-2xl font-impact font-black text-[#2c3856] uppercase flex items-center gap-3">
                            <i class="fas fa-exchange-alt text-[#ff9c00]"></i> Dinámica de Inventario
                        </h3>
                        <p class="text-sm font-semibold text-slate-500 mt-2 uppercase tracking-wide">
                            Desglose comparativo: Entradas (Positivas) vs Salidas (Negativas)
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 px-4 py-2 bg-slate-100 rounded-lg border border-slate-200">
                        <span class="text-xs font-bold text-slate-600 uppercase">Métrica: Unidades</span>
                    </div>
                </div>
                
                <div class="relative w-full" style="min-height: 450px;">
                    <div id="chart-movement-reasons" class="w-full h-full"></div>
                </div>
                
                <div class="mt-6 bg-blue-50/50 p-4 rounded-lg border border-blue-100">
                    <p class="text-sm text-[#2c3856] font-medium leading-relaxed">
                        <strong class="uppercase text-blue-600">Nota de Análisis:</strong> Este gráfico permite identificar fugas de inventario no relacionadas con ventas (como mermas o ajustes manuales) frente a los reabastecimientos operativos.
                    </p>
                </div>
            </div>

            <div class="card-complex p-8 animate-enter" style="animation-delay: 0.2s;">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 border-b border-slate-200 pb-6">
                    <div>
                        <h3 class="text-2xl font-impact font-black text-[#2c3856] uppercase flex items-center gap-3">
                            <i class="fas fa-bullseye text-[#ff9c00]"></i> Matriz de Rotación
                        </h3>
                        <p class="text-sm font-semibold text-slate-500 mt-2 uppercase tracking-wide">
                            Correlación Multivariable: Precio (X) • Stock (Y) • Volumen (Radio)
                        </p>
                    </div>
                    <div class="flex gap-4 mt-4 md:mt-0">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#ff9c00]"></span>
                            <span class="text-xs font-bold text-slate-600 uppercase">Alta Rotación</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-[#2c3856]"></span>
                            <span class="text-xs font-bold text-slate-600 uppercase">Stock Estático</span>
                        </div>
                    </div>
                </div>

                <div class="relative w-full" style="min-height: 500px;">
                    <div id="chart-rotation" class="w-full h-full"></div>
                </div>

                <div class="mt-6 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <p class="text-sm text-[#2c3856] font-medium leading-relaxed">
                        <strong class="uppercase">Interpretación:</strong> Las burbujas más grandes representan los productos más vendidos. Su posición vertical indica el nivel de stock actual, mientras que la horizontal indica su precio. Idealmente, los productos de alto valor (derecha) y alta rotación (grandes) deberían tener niveles de stock (arriba) optimizados para evitar capital congelado.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const dataMovementReasons = @json($chartMovementReasons);
            const dataRotation = @json($chartRotation);

            const commonOptions = {
                fontFamily: 'Montserrat, sans-serif',
                chart: {
                    background: 'transparent',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                theme: { mode: 'light' }
            };

            var optionsMovementReasons = {
                ...commonOptions,
                series: dataMovementReasons.series,
                chart: {
                    type: 'bar',
                    height: 450,
                    stacked: true,
                    toolbar: { show: false },
                    fontFamily: 'Montserrat, sans-serif'
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        borderRadius: 4,
                        columnWidth: '40%',
                    }
                },
                dataLabels: { enabled: false },
                stroke: { width: 0 },
                xaxis: {
                    categories: dataMovementReasons.categories,
                    labels: {
                        style: { colors: '#64748b', fontSize: '13px', fontWeight: 600, fontFamily: 'Montserrat, sans-serif' },
                        rotate: -45
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    title: { text: 'Volumen de Unidades', style: { fontSize: '14px', fontWeight: 700, color: '#94a3b8' } },
                    labels: { style: { colors: '#64748b', fontSize: '13px', fontWeight: 600 } }
                },
                grid: { borderColor: '#e2e8f0', strokeDashArray: 4, yaxis: { lines: { show: true } } },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: function (val) { return val.toLocaleString() + " uds" } }
                },
                fill: { opacity: 1 },
                legend: { 
                    position: 'top', 
                    horizontalAlign: 'right',
                    fontSize: '14px',
                    fontWeight: 600,
                    itemMargin: { horizontal: 10, vertical: 0 }
                },
                colors: ['#10b981', '#ef4444', '#f59e0b', '#3b82f6'] 
            };

            var chartMovementReasons = new ApexCharts(document.querySelector("#chart-movement-reasons"), optionsMovementReasons);
            chartMovementReasons.render();

            var optionsRotation = {
                ...commonOptions,
                series: dataRotation.series,
                chart: {
                    height: 500,
                    type: 'bubble',
                    toolbar: { show: false },
                    fontFamily: 'Montserrat, sans-serif'
                },
                dataLabels: { enabled: false },
                fill: { opacity: 0.7 },
                xaxis: {
                    tickAmount: 10,
                    type: 'numeric',
                    labels: {
                        formatter: function (val) { return '$' + parseFloat(val).toFixed(0); },
                        style: { colors: '#64748b', fontSize: '13px', fontWeight: 600 }
                    },
                    title: { text: 'Precio Unitario (MXN)', style: { fontSize: '14px', fontWeight: 700, color: '#94a3b8' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { show: true }
                },
                yaxis: {
                    tickAmount: 7,
                    title: { text: 'Stock Físico Actual', style: { fontSize: '14px', fontWeight: 700, color: '#94a3b8' } },
                    labels: { style: { colors: '#64748b', fontSize: '13px', fontWeight: 600 } }
                },
                grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
                tooltip: {
                    theme: 'light',
                    custom: function({ series, seriesIndex, dataPointIndex, w }) {
                        const rawData = w.config.series[seriesIndex].data[dataPointIndex];
                        
                        let label = rawData.label || 'Producto';
                        let precio = rawData.x;
                        let stock = rawData.y;
                        let vendido = rawData.z;

                        const precioFmt = parseFloat(precio).toFixed(2);
                        const stockFmt = parseInt(stock).toLocaleString();
                        const vendidoFmt = parseInt(vendido).toLocaleString();

                        return `
                            <div class="px-4 py-3 bg-white border border-slate-200 rounded-lg shadow-xl" style="min-width: 200px;">
                                <div class="text-xs font-bold text-[#ff9c00] uppercase tracking-wider mb-2 border-b border-slate-100 pb-1">Detalle de SKU</div>
                                <div class="text-lg font-black text-[#2c3856] mb-3">${label}</div>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-500 font-medium">Precio:</span>
                                        <span class="font-bold text-[#2c3856]">$${precioFmt}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-500 font-medium">Stock:</span>
                                        <span class="font-bold text-[#2c3856] bg-slate-100 px-2 rounded">${stockFmt} uds</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-slate-500 font-medium">Rotación:</span>
                                        <span class="font-bold text-emerald-600">${vendidoFmt} vendidos</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                },
                colors: ['#2c3856', '#ff9c00', '#64748b', '#ef4444', '#10b981']
            };

            var chartRotation = new ApexCharts(document.querySelector("#chart-rotation"), optionsRotation);
            chartRotation.render();
        });
    </script>
</x-app-layout>