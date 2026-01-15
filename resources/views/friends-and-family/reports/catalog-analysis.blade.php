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

        .font-impact { font-family: 'Raleway', sans-serif; letter-spacing: -0.02em; }
        
        @keyframes floatOrb { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(40px, -60px); } }
        .animate-enter { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    <div class="complex-bg">
        <div class="orb-float bg-teal-400 w-96 h-96 top-0 right-1/4 mix-blend-multiply"></div>
        <div class="orb-float bg-indigo-400 w-80 h-80 bottom-0 left-1/4 mix-blend-multiply" style="animation-delay: -5s;"></div>
    </div>

    <div class="relative min-h-screen py-10 px-4 sm:px-6 lg:px-8 max-w-[1920px] mx-auto">
        
        <x-slot name="header"></x-slot>

        <div class="flex flex-col md:flex-row justify-between items-end mb-10 animate-enter gap-6"> 
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 bg-[#ff9c00] rounded-full animate-pulse"></div>
                    <span class="text-[10px] font-bold tracking-[0.25em] text-[#2c3856] uppercase">Inteligencia de Producto</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-impact font-black text-[#2c3856] leading-none">
                    CATÁLOGO <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">& PRECIOS</span>
                </h2>
                <p class="text-sm text-slate-500 font-medium mt-2 max-w-xl">
                    Segmentación de precios, estado operativo del catálogo y penetración de marcas.
                </p>
            </div>

            <div class="flex gap-3 items-center">
                @if(Auth::user()->isSuperAdmin())
                    <form method="GET" action="{{ route('ff.reports.catalogAnalysis') }}">
                        <select name="area_id" onchange="this.form.submit()" class="bg-white/60 border border-[#2c3856]/10 rounded-lg py-2.5 px-4 text-xs font-black uppercase text-[#2c3856] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] cursor-pointer hover:bg-white transition-colors shadow-sm">
                            <option value="">CATÁLOGO GLOBAL</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                    {{ strtoupper($area->name) }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
            
            <a href="{{ route('ff.reports.index') }}"
               class="group flex items-center gap-3 px-6 py-2.5 bg-white/60 backdrop-blur-sm border border-[#2c3856]/10 rounded-lg hover:bg-[#2c3856] hover:text-white transition-all duration-300 shadow-sm">
                <i class="fas fa-arrow-left text-[#ff9c00] group-hover:text-white transition-colors"></i>
                <span class="text-xs font-black uppercase tracking-widest">Panel Principal</span>
            </a>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 card-complex p-8 animate-enter" style="animation-delay: 0.1s;">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-impact font-black text-[#2c3856] uppercase flex items-center gap-2">
                            <i class="fas fa-tags text-[#ff9c00]"></i> Segmentación de Precios
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">
                            Distribución de SKUs por valor unitario
                        </p>
                    </div>
                    <div class="mt-2 sm:mt-0">
                        <span class="px-3 py-1 bg-slate-100 text-[#2c3856] rounded text-[10px] font-bold uppercase border border-slate-200">
                            Estrategia de Costos
                        </span>
                    </div>
                </div>
                
                <div class="relative w-full" style="min-height: 400px;">
                    <div id="chart-price-distribution" class="absolute inset-0"></div>
                </div>
            </div>

            <div class="lg:col-span-1 card-complex p-8 animate-enter" style="animation-delay: 0.2s;">
                <div class="mb-6 border-b border-slate-100 pb-4">
                    <h3 class="text-base font-impact font-black text-[#2c3856] uppercase flex items-center gap-2">
                        <i class="fas fa-toggle-on text-[#ff9c00]"></i> Estado Operativo
                    </h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">
                        Ratio Activos vs. Inactivos
                    </p>
                </div>
                
                <div class="relative w-full flex items-center justify-center" style="min-height: 400px;">
                    <div id="chart-active-inactive" class="w-full"></div>
                </div>
                
                <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-center">
                    <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Total SKUs Registrados</div>
                    <div class="text-2xl font-black text-[#2c3856]" id="total-catalog-display">0</div>
                </div>
            </div>

            <div class="lg:col-span-3 card-complex p-8 animate-enter" style="animation-delay: 0.3s;">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-impact font-black text-[#2c3856] uppercase flex items-center gap-2">
                            <i class="fas fa-certificate text-[#ff9c00]"></i> Posicionamiento de Marcas
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-1">
                            Top 10 Marcas con mayor presencia en inventario
                        </p>
                    </div>
                </div>
                
                <div class="relative w-full" style="min-height: 450px;">
                    <div id="chart-brand" class="absolute inset-0"></div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dataPriceDistribution = @json($chartPriceDistribution);
            const dataActiveInactive = @json($chartActiveInactive);
            const dataBrand = @json($chartBrand);

            const commonOptions = {
                fontFamily: 'Montserrat, sans-serif',
                chart: {
                    background: 'transparent',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                theme: { mode: 'light' }
            };

            var optionsPriceDistribution = {
                ...commonOptions,
                series: [{
                    name: 'Número de Productos',
                    data: dataPriceDistribution.series
                }],
                chart: {
                    type: 'bar',
                    height: '100%',
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '45%',
                        borderRadius: 6,
                        distributed: true, 
                    },
                },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                xaxis: {
                    categories: dataPriceDistribution.categories,
                    title: { text: 'Rango de Precios (MXN)', style: { fontSize: '11px', fontWeight: 700, color: '#94a3b8' } },
                    labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    title: { text: 'Conteo de SKUs', style: { fontSize: '11px', fontWeight: 700, color: '#94a3b8' } },
                    labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4,
                },
                colors: ['#2c3856', '#3b4b72', '#4b5e8e', '#5c72ab'], 
                legend: { show: false },
                tooltip: {
                    theme: 'light',
                    y: { formatter: (val) => val + " productos" }
                }
            };
            var chartPriceDistribution = new ApexCharts(document.querySelector("#chart-price-distribution"), optionsPriceDistribution);
            chartPriceDistribution.render();

            var optionsActiveInactive = {
                ...commonOptions,
                series: dataActiveInactive.series,
                chart: {
                    type: 'donut',
                    height: 380,
                },
                labels: dataActiveInactive.labels,
                colors: ['#10b981', '#64748b'], 
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: { fontSize: '11px', fontWeight: 600, color: '#64748b', offsetY: -5 },
                                value: { fontSize: '24px', fontWeight: 900, color: '#2c3856', offsetY: 10 },
                                total: {
                                    show: true,
                                    label: 'CATÁLOGO',
                                    fontSize: '10px',
                                    fontWeight: 700,
                                    color: '#ff9c00',
                                    formatter: function (w) {
                                        const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        document.getElementById('total-catalog-display').innerText = total.toLocaleString();
                                        return total.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    fontWeight: 600,
                    itemMargin: { horizontal: 10 }
                },
                dataLabels: { enabled: false }
            };
            var chartActiveInactive = new ApexCharts(document.querySelector("#chart-active-inactive"), optionsActiveInactive);
            chartActiveInactive.render();

            var optionsBrand = {
                ...commonOptions,
                series: [{
                    name: 'SKUs',
                    data: dataBrand.series
                }],
                chart: {
                    type: 'bar',
                    height: '100%',
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        barHeight: '60%',
                        dataLabels: { position: 'bottom' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    textAnchor: 'start',
                    style: { colors: ['#fff'], fontSize: '10px', fontWeight: 600 },
                    formatter: function (val, opt) { return val.toLocaleString() },
                    offsetX: 0,
                    dropShadow: { enabled: true }
                },
                xaxis: {
                    categories: dataBrand.labels,
                    title: { text: 'Número de Productos', style: { fontSize: '11px', fontWeight: 700, color: '#94a3b8' } },
                    labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: '#2c3856', fontSize: '11px', fontWeight: 700 }, maxWidth: 160 }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } }
                },
                colors: ['#2c3856'],
                tooltip: {
                    theme: 'light',
                    y: { formatter: (val) => val + " SKUs" }
                }
            };
            var chartBrand = new ApexCharts(document.querySelector("#chart-brand"), optionsBrand);
            chartBrand.render();
        });
    </script>
</x-app-layout>