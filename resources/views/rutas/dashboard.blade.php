<x-app-layout>
    <x-slot name="head">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Raleway:wght@700;800;900&display=swap" rel="stylesheet">
        
        <style>
            .font-brand-heading { font-family: 'Raleway', sans-serif; }
            .font-brand-body { font-family: 'Montserrat', sans-serif; }
            .animate-enter { animation: enter 0.6s ease-out forwards; opacity: 0; transform: translateY(20px); }
            .delay-100 { animation-delay: 0.1s; }
            .delay-200 { animation-delay: 0.2s; }
            .delay-300 { animation-delay: 0.3s; }
            
            @keyframes enter {
                to { opacity: 1; transform: translateY(0); }
            }

            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
        </style>
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-brand-heading font-extrabold text-3xl text-[#2c3856] leading-tight tracking-tight">
                    CONTROL TOWER <span class="text-[#ff9c00]"> TRANSPORTES</span>
                </h2>
                <p class="font-brand-body text-sm text-[#666666] mt-1">Visión estratégica de operaciones en tiempo real</p>
            </div>
            <div class="hidden sm:flex items-center space-x-2 bg-white px-3 py-1 rounded-full shadow-sm border border-gray-100">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span class="text-xs font-brand-body font-bold text-[#2c3856]">SISTEMA ONLINE</span>
            </div>
        </div>
    </x-slot>

    <div class="py-10 min-h-screen font-brand-body">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-enter">
                
                <a href="{{ route('rutas.plantillas.index') }}" class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-lg border-l-4 border-[#ff9c00] transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl hover:shadow-[#ff9c00]/20">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-[#ff9c00] opacity-10 transition-transform group-hover:scale-150"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-sm font-bold text-[#666666] uppercase tracking-wider mb-1">Planeación</p>
                            <h3 class="text-2xl font-brand-heading font-extrabold text-[#2c3856]">Gestión de Rutas</h3>
                            <p class="text-xs text-gray-500 mt-2 font-medium">Administrar plantillas maestras</p>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-[#fff5e0] text-[#ff9c00] flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0121 18.382V7.618a1 1 0 01-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('rutas.asignaciones.index') }}" class="group relative overflow-hidden rounded-2xl bg-[#2c3856] p-6 shadow-lg border border-[#2c3856] transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-white opacity-5 transition-transform group-hover:scale-150"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-sm font-bold text-gray-300 uppercase tracking-wider mb-1">Operación</p>
                            <h3 class="text-2xl font-brand-heading font-extrabold text-white">Asignaciones</h3>
                            <p class="text-xs text-gray-300 mt-2 font-medium">Despacho de guías y facturas</p>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-white/10 text-white flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('rutas.monitoreo.index') }}" class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-lg border-l-4 border-[#666666] transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-[#666666] opacity-10 transition-transform group-hover:scale-150"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <p class="text-sm font-bold text-[#666666] uppercase tracking-wider mb-1">Control</p>
                            <h3 class="text-2xl font-brand-heading font-extrabold text-[#2c3856]">Monitoreo en Vivo</h3>
                            <p class="text-xs text-gray-500 mt-2 font-medium">Tracking y eventos críticos</p>
                        </div>
                        <div class="h-12 w-12 rounded-xl bg-gray-100 text-[#2c3856] flex items-center justify-center shadow-sm">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="animate-enter delay-100 bg-white rounded-xl shadow-md border border-gray-100 p-1">
                <form action="{{ route('rutas.dashboard') }}" method="GET" class="flex flex-col lg:flex-row items-end lg:items-center justify-between gap-4 p-4">
                    
                    <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto items-center">
                        <div class="flex items-center gap-2 text-[#2c3856]">
                            <svg class="w-5 h-5 text-[#ff9c00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span class="font-bold text-sm uppercase">Rango de Datos:</span>
                        </div>
                        
                        <div class="relative group">
                            <input type="date" name="start_date" value="{{ request('start_date', $startDate) }}" 
                                class="block w-full pl-3 pr-10 py-2 text-sm border-gray-200 rounded-lg focus:ring-[#ff9c00] focus:border-[#ff9c00] bg-gray-50 group-hover:bg-white transition-colors cursor-pointer">
                        </div>
                        <span class="text-gray-400 font-bold">-</span>
                        <div class="relative group">
                            <input type="date" name="end_date" value="{{ request('end_date', $endDate) }}" 
                                class="block w-full pl-3 pr-10 py-2 text-sm border-gray-200 rounded-lg focus:ring-[#ff9c00] focus:border-[#ff9c00] bg-gray-50 group-hover:bg-white transition-colors cursor-pointer">
                        </div>
                        
                        <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2 border border-transparent text-sm font-bold rounded-lg text-white bg-[#2c3856] hover:bg-[#1a2b41] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2c3856] transition-all shadow-md hover:shadow-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                            Filtrar Datos
                        </button>
                    </div>

                    <div class="flex items-center gap-3 w-full lg:w-auto justify-end border-t lg:border-t-0 pt-4 lg:pt-0 border-gray-100">
                        <a href="{{ route('rutas.dashboard.export', ['start_date' => request('start_date', $startDate), 'end_date' => request('end_date', $endDate)]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-200 text-xs font-bold uppercase rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:text-[#2c3856] transition-colors">
                            <svg class="w-4 h-4 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            CSV General
                        </a>
                        <a href="{{ route('rutas.dashboard.exportTiempos', ['start_date' => request('start_date', $startDate), 'end_date' => request('end_date', $endDate)]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-200 text-xs font-bold uppercase rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:text-[#2c3856] transition-colors">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Rep. Tiempos
                        </a>
                    </div>
                </form>
            </div>       
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-enter delay-200">
                
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 col-span-1 lg:col-span-2">
                    <h4 class="font-brand-heading text-lg text-[#2c3856] font-bold mb-4 border-b border-gray-100 pb-2">Distribución de Rutas</h4>
                    <div class="relative h-64 w-full">
                        <canvas id="chart1"></canvas>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 col-span-1 flex flex-col justify-center">
                    <h4 class="font-brand-heading text-lg text-[#2c3856] font-bold mb-2 text-center">Salud de Guías</h4>
                    <div class="relative h-56 w-full flex justify-center">
                        <canvas id="chart2"></canvas>
                    </div>
                    <div class="mt-4 text-center">
                        <span class="text-xs text-gray-400 uppercase tracking-widest font-bold">Efectividad Global</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-enter delay-300">
                
                <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100 col-span-1 lg:col-span-2">
                    <h5 class="font-brand-heading text-base text-[#666666] font-bold mb-3">Cobertura por Región</h5>
                    <div class="h-48"><canvas id="chart3"></canvas></div>
                </div>

                <div class="bg-[#2c3856] p-5 rounded-2xl shadow-md border border-gray-600 col-span-1 text-white">
                    <h5 class="font-brand-heading text-base text-white font-bold mb-3">Tipificación de Eventos</h5>
                    <div class="h-48"><canvas id="chart4"></canvas></div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100 col-span-1">
                    <h5 class="font-brand-heading text-base text-[#666666] font-bold mb-3">Status Entregas</h5>
                    <div class="h-48 flex justify-center"><canvas id="chart6"></canvas></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-enter delay-300">
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 col-span-1">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-brand-heading text-lg text-[#2c3856] font-bold">Top Operadores</h4>
                        <span class="px-2 py-1 bg-[#fff5e0] text-[#ff9c00] text-xs font-bold rounded">MVP</span>
                    </div>
                    <div class="h-64"><canvas id="chart5"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 col-span-1 lg:col-span-2 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-[#2c3856] opacity-5 rounded-bl-full -mr-10 -mt-10"></div>
                    <h4 class="font-brand-heading text-lg text-[#2c3856] font-bold mb-4">Tendencia de Productividad (7 Días)</h4>
                    <div class="h-64"><canvas id="chart7"></canvas></div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Chart.defaults.font.family = "'Montserrat', sans-serif";
        Chart.defaults.color = '#666666';
        
        const BRAND = {
            navy: '#2c3856',
            orange: '#ff9c00',
            grey: '#666666',
            darkGrey: '#2b2b2b',
            white: '#ffffff',
            navyLight: 'rgba(44, 60, 86, 0.7)',
            orangeLight: 'rgba(255, 156, 0, 0.7)',
            success: '#10B981',
            danger: '#EF4444'
        };

        const rawData1 = @json($chart1Data);
        const rawData2 = @json($chart2Data);
        const rawData3 = @json($chart3Data);
        const rawData4 = @json($chart4Data);
        const rawData5 = @json($chart5Data);
        const rawData6 = @json($chart6Data);
        const rawData7 = @json($chart7Data);

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { usePointStyle: true, padding: 20, font: { size: 11, weight: 600 } } },
                tooltip: {
                    backgroundColor: BRAND.navy,
                    titleFont: { family: "'Raleway', sans-serif", size: 13 },
                    bodyFont: { family: "'Montserrat', sans-serif" },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            layout: { padding: 10 }
        };

        const ctx1 = document.getElementById('chart1').getContext('2d');
        const gradient1 = ctx1.createLinearGradient(0, 0, 0, 400);
        gradient1.addColorStop(0, BRAND.orange);
        gradient1.addColorStop(1, 'rgba(255, 156, 0, 0.2)');

        new Chart(ctx1, {
            type: 'bar',
            data: { 
                labels: rawData1.labels,
                datasets: [{ 
                    label: 'Volumen de Rutas', 
                    data: rawData1.data, 
                    backgroundColor: [BRAND.orange, BRAND.navy, BRAND.grey], 
                    borderRadius: 6,
                    barThickness: 40
                }] 
            },
            options: {
                ...commonOptions,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { grid: { borderDash: [5, 5], color: '#f0f0f0' }, border: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });

        new Chart(document.getElementById('chart2').getContext('2d'), {
            type: 'doughnut', 
            data: { 
                labels: rawData2.labels, 
                datasets: [{ 
                    data: rawData2.data, 
                    backgroundColor: [BRAND.grey, BRAND.navy, BRAND.orange, '#e0e0e0'], 
                    borderWidth: 0,
                    hoverOffset: 10
                }] 
            },
            options: {
                ...commonOptions,
                cutout: '75%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10 } }
                }
            }
        });

        new Chart(document.getElementById('chart3').getContext('2d'), {
            type: 'bar', 
            data: { 
                labels: rawData3.labels, 
                datasets: [{ 
                    label: 'Rutas', 
                    data: rawData3.data, 
                    backgroundColor: BRAND.navy, 
                    borderRadius: 4,
                    barPercentage: 0.6
                }] 
            },
            options: {
                ...commonOptions,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { grid: { display: false } } }
            }
        });

        Chart.defaults.color = '#ffffff'; 
        new Chart(document.getElementById('chart4').getContext('2d'), {
            type: 'pie', 
            data: { 
                labels: rawData4.labels, 
                datasets: [{ 
                    data: rawData4.data, 
                    backgroundColor: [BRAND.orange, '#4b5563', '#9ca3af'], 
                    borderWidth: 2,
                    borderColor: BRAND.navy
                }] 
            },
            options: {
                ...commonOptions,
                plugins: { legend: { position: 'right', labels: { color: '#ffffff' } } }
            }
        });
        Chart.defaults.color = '#666666';

        new Chart(document.getElementById('chart5').getContext('2d'), {
            type: 'bar', 
            data: { 
                labels: rawData5.labels, 
                datasets: [{ 
                    label: 'Guías', 
                    data: rawData5.data, 
                    backgroundColor: BRAND.orange, 
                    borderRadius: 20,
                    barThickness: 15
                }] 
            },
            options: {
                ...commonOptions,
                indexAxis: 'y',
                scales: { x: { display: false }, y: { grid: { display: false } } }
            }
        });

        new Chart(document.getElementById('chart6').getContext('2d'), {
            type: 'doughnut', 
            data: { 
                labels: rawData6.labels, 
                datasets: [{ 
                    data: rawData6.data, 
                    backgroundColor: [BRAND.success, BRAND.danger], 
                    borderWidth: 0
                }] 
            },
            options: { ...commonOptions, cutout: '60%' }
        });

        const ctx7 = document.getElementById('chart7').getContext('2d');
        const gradient7 = ctx7.createLinearGradient(0, 0, 0, 300);
        gradient7.addColorStop(0, 'rgba(44, 60, 86, 0.4)');
        gradient7.addColorStop(1, 'rgba(44, 60, 86, 0.0)');

        new Chart(ctx7, {
            type: 'line', 
            data: { 
                labels: rawData7.labels, 
                datasets: [{
                    label: 'Guías Completadas', 
                    data: rawData7.data,
                    fill: true, 
                    backgroundColor: gradient7, 
                    borderColor: BRAND.navy, 
                    borderWidth: 3,
                    pointBackgroundColor: BRAND.orange,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4
                }] 
            },
            options: {
                ...commonOptions,
                scales: {
                    y: { grid: { borderDash: [5, 5] }, beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    });
    </script>
</x-app-layout>