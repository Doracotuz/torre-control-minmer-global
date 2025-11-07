<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        .bg-aether {
            background-color: #f4f7fc;
        }

        .card {
            background-color: #ffffff;
            transition: all 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: 1px solid #e5e7eb;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.15);
        }
        .fade-in-up {
            opacity: 0;
            transform: translateY(25px);
            animation: fadeInUp 0.8s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
        }
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }
        .pulse-glow {
            animation: pulseGlow 3s infinite ease-in-out;
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 40px rgba(44, 56, 86, 0.1); }
            50% { box-shadow: 0 0 60px rgba(44, 56, 86, 0.2); }
        }

        .nav-item { position: relative; }
        .nav-item .nav-icon, .nav-item .nav-text, .nav-item .nav-arrow {
            transition: all 0.3s ease;
        }
        .nav-item:hover .nav-icon {
            color: #ff9c00;
            transform: scale(1.1);
        }
        .nav-item:hover .nav-text {
            color: #2c3856;
        }
        .nav-item:hover .nav-arrow {
            opacity: 1;
            transform: translateX(0);
        }

        .timeline-item:not(:last-child)::after {
            content: ''; position: absolute; top: 2.5rem; left: 1.25rem;
            bottom: -0.75rem; width: 2px;
            background-color: #e5e7eb;
            transform: translateX(-50%);
        }
         .apexcharts-tooltip {
            background: #ffffff;
            color: #2b2b2b;
            border: 1px solid #e5e7eb;
        }
        .apexcharts-legend-text {
            color: #666666 !important;
            font-family: 'Montserrat', sans-serif;
        }
    </style>

    <div class="bg-aether min-h-screen rounded-2xl">
        <x-slot name="header">
            <div x-data="{
                greeting: 'Buenas Noches',
                init() {
                    const hour = new Date().getHours();
                    if (hour < 12) this.greeting = 'Buenos Días';
                    else if (hour < 19) this.greeting = 'Buenas Tardes';
                }
            }" class="fade-in-up">
                <h2 class="font-bold text-3xl text-[#2c3856] leading-tight tracking-tight">
                    <span x-text="greeting"></span>, {{ Auth::user()->name }}.
                </h2>
                <p class="text-md text-[#666666] mt-1">
                    Bienvenido al área de Customer Service.
                </p>
            </div>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="card p-6 rounded-2xl shadow-xl fade-in-up" style="animation-delay: 200ms;">
                                <div class="flex items-center">
                                    <div class="bg-[#ff9c00] text-white rounded-lg p-4 pulse-glow">
                                        <i class="fas fa-file-invoice fa-2x"></i>
                                    </div>
                                    <div class="ml-5">
                                        <p class="text-[#666666] text-sm">Pedidos Pendientes</p>
                                        <p x-data="countUp({{ $pedidosPendientes }})" x-text="displayValue" class="text-4xl font-bold text-[#2c3856]"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="card p-6 rounded-2xl shadow-xl fade-in-up" style="animation-delay: 300ms;">
                                <div class="flex items-center">
                                    <div class="bg-[#2c3856] text-white rounded-lg p-4">
                                        <i class="fas fa-route fa-2x"></i>
                                    </div>
                                    <div class="ml-5">
                                        <p class="text-[#666666] text-sm">En Planificación</p>
                                        <p x-data="countUp({{ $enPlanificacion }})" x-text="displayValue" class="text-4xl font-bold text-[#2c3856]"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="card p-6 rounded-2xl shadow-xl fade-in-up" style="animation-delay: 400ms;">
                                <div class="flex items-center">
                                    <div class="bg-gray-200 text-[#2c3856] rounded-lg p-4">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <div class="ml-5">
                                        <p class="text-[#666666] text-sm">Completados (Mes)</p>
                                        <p x-data="countUp({{ $pedidosCompletadosMes }})" x-text="displayValue" class="text-4xl font-bold text-[#2c3856]"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card p-8 rounded-2xl shadow-xl fade-in-up" style="animation-delay: 500ms;">
                            <h3 class="font-bold text-[#2c3856] mb-2 text-xl">Top 10 Clientes por Pedidos</h3>
                            <div id="topClientsChart" class="h-80"></div>
                        </div>
                        <div class="card p-8 rounded-2xl shadow-xl fade-in-up" style="animation-delay: 600ms;">
                            <h3 class="font-bold text-[#2c3856] mb-2 text-xl">Distribución de Órdenes por Canal</h3>
                            <div id="ordersByChannelChart" class="h-80 flex justify-center"></div>
                        </div>
                    </div>

                    <div class="lg:col-span-1 space-y-8">
                        <div class="card p-8 rounded-2xl shadow-xl fade-in-up" style="animation-delay: 700ms;">
                            <h3 class="font-bold text-[#2c3856] mb-6 text-xl">Módulos de Gestión</h3>
                            <div class="space-y-4">
                                @if(in_array(Auth::user()->area?->name, ['Customer Service', 'Administración']))
                                <a href="{{ route('customer-service.orders.index') }}" class="nav-item flex items-center p-4 rounded-xl group">
                                    <i class="nav-icon fas fa-box-open fa-fw fa-lg text-gray-400"></i>
                                    <span class="nav-text ml-4 font-semibold text-[#2b2b2b]">Gestión de Pedidos</span>
                                    <i class="nav-arrow fas fa-arrow-right text-gray-300 ml-auto opacity-0 transform -translate-x-2"></i>
                                </a>
                                @endif
                                <a href="{{ route('customer-service.planning.index') }}" class="nav-item flex items-center p-4 rounded-xl group">
                                    <i class="nav-icon fas fa-shipping-fast fa-fw fa-lg text-gray-400"></i>
                                    <span class="nav-text ml-4 font-semibold text-[#2b2b2b]">Planificación</span>
                                    <i class="nav-arrow fas fa-arrow-right text-gray-300 ml-auto opacity-0 transform -translate-x-2"></i>
                                </a>
                                @if(in_array(Auth::user()->area?->name, ['Customer Service', 'Administración']))
                                <a href="{{ route('customer-service.credit-notes.index') }}" class="nav-item flex items-center p-4 rounded-xl group">
                                    <i class="nav-icon fas fa-receipt fa-fw fa-lg text-gray-400"></i>
                                    <span class="nav-text ml-4 font-semibold text-[#2b2b2b]">Notas de Crédito</span>
                                    <i class="nav-arrow fas fa-arrow-right text-gray-300 ml-auto opacity-0 transform -translate-x-2"></i>
                                </a>
                                <a href="{{ route('customer-service.validation.index') }}" class="nav-item flex items-center p-4 rounded-xl group">
                                    <i class="nav-icon fas fa-barcode fa-fw fa-lg text-gray-400"></i>
                                    <span class="nav-text ml-4 font-semibold text-[#2b2b2b]">Validación de UPC</span>
                                    <i class="nav-arrow fas fa-arrow-right text-gray-300 ml-auto opacity-0 transform -translate-x-2"></i>
                                </a>
                                @endif
                                @if(in_array(Auth::user()->area?->name, ['Customer Service', 'Administración']))                                
                                @if(Auth::user()->is_area_admin)
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="nav-item w-full flex items-center p-4 rounded-xl group">
                                        <i class="nav-icon fas fa-book-open fa-fw fa-lg text-gray-400"></i>
                                        <span class="nav-text ml-4 font-semibold text-[#2b2b2b]">Catálogos (Admin)</span>
                                        <i class="fas fa-chevron-down text-gray-400 ml-auto transition-transform duration-300" :class="{'rotate-180': open}"></i>
                                    </button>
                                    <div x-show="open" x-transition class="mt-2 ml-4 pl-8 border-l-2 border-gray-200 space-y-2">
                                        <a href="{{ route('customer-service.products.index') }}" class="block text-sm font-semibold text-[#666666] hover:text-[#ff9c00] transition-colors">Productos</a>
                                        <a href="{{ route('customer-service.customers.index') }}" class="block text-sm font-semibold text-[#666666] hover:text-[#ff9c00] transition-colors">Clientes</a>
                                        <a href="{{ route('customer-service.warehouses.index') }}" class="block text-sm font-semibold text-[#666666] hover:text-[#ff9c00] transition-colors">Almacenes</a>
                                    </div>
                                </div>
                                @endif
                                @endif
                            </div>
                        </div>
                        <div class="card p-8 rounded-2xl shadow-xl fade-in-up" style="animation-delay: 800ms;">
                            <h3 class="font-bold text-[#2c3856] mb-6">Actividad Reciente</h3>
                                <ul class="space-y-6 max-h-[400px] overflow-y-auto pr-4">
                                    @forelse($actividadReciente as $event)
                                        @php
                                            $descripcionAccion = Str::after($event->description, $event->user->name ?? 'El usuario');
                                            $esLargo = strlen($descripcionAccion) > 100;
                                        @endphp
                                        <li class="relative flex items-start timeline-item" @if($esLargo) x-data="{ expanded: false }" @endif>
                                            <div class="bg-gray-100 text-[#2c3856] rounded-full h-10 w-10 flex-shrink-0 flex items-center justify-center z-10 ring-8 ring-white">
                                                @if(str_contains($event->description, 'creó')) <i class="fas fa-plus"></i>
                                                @elseif(str_contains($event->description, 'canceló')) <i class="fas fa-ban"></i>
                                                @else <i class="fas fa-pencil-alt"></i> @endif
                                            </div>
                                            <div class="ml-4 text-sm">
                                                <p class="text-gray-700">
                                                    <span class="font-semibold text-[#2b2b2b]">{{ $event->user->name ?? 'Sistema' }}</span>
                                                    @if($esLargo)
                                                        <span x-show="!expanded">{{ Str::limit($descripcionAccion, 100, '...') }}</span>
                                                        <button @click="expanded = true" x-show="!expanded" class="text-blue-600 text-xs font-semibold hover:underline">Ver más</button>
                                                        <span x-show="expanded">{{ $descripcionAccion }}</span>
                                                        <button @click="expanded = false" x-show="expanded" class="text-blue-600 text-xs font-semibold hover:underline">Ver menos</button>
                                                    @else
                                                        {{ $descripcionAccion }}
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    <span>{{ $event->created_at->diffForHumans() }}</span>
                                                    
                                                    @if($event->type === 'order' && $event->order)
                                                        <span class="mx-1">&middot;</span>
                                                        <a href="{{ route('customer-service.orders.show', $event->order) }}" class="text-blue-600 hover:underline font-semibold" title="Ver detalle del pedido">
                                                            SO: {{ $event->order->so_number }}
                                                        </a>
                                                    @elseif($event->type === 'planning' && $event->planning)
                                                        <span class="mx-1">&middot;</span>
                                                        <a href="{{ route('customer-service.planning.show', $event->planning) }}" class="text-purple-600 hover:underline font-semibold" title="Ver detalle de la planificación">
                                                            Plan: {{ $event->planning->factura ?? $event->planning->id }}
                                                        </a>
                                                    @endif
                                                </p>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="text-sm text-gray-400">No hay actividad reciente.</li>
                                    @endforelse
                                </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function countUp(target) {
            return {
                displayValue: 0,
                targetValue: target,
                init() {
                    const duration = 1500;
                    const frameRate = 1000 / 60;
                    const totalFrames = Math.round(duration / frameRate);
                    let frame = 0;
                    const counter = setInterval(() => {
                        frame++;
                        const progress = frame / totalFrames;
                        const easedProgress = 1 - Math.pow(1 - progress, 3); 
                        this.displayValue = Math.round(this.targetValue * easedProgress);
                        if (frame === totalFrames) {
                            this.displayValue = this.targetValue;
                            clearInterval(counter);
                        }
                    }, frameRate);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const chartData = @json($chartData);
            const apexChartFont = "'Montserrat', sans-serif";

            if (document.querySelector("#topClientsChart")) {
                const topClientsOptions = {
                    series: [{ name: 'Nº de Pedidos', data: chartData.topClientes.series }],
                    chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: apexChartFont },
                    plotOptions: { bar: { borderRadius: 4, horizontal: true, } },
                    colors: ['#2c3856'],
                    dataLabels: { enabled: false },
                    xaxis: { categories: chartData.topClientes.labels, labels: { style: { colors: '#666666' } } },
                    yaxis: { labels: { style: { colors: '#666666', maxWidth: 150 } } },
                    grid: { borderColor: '#e5e7eb', }
                };
                new ApexCharts(document.querySelector("#topClientsChart"), topClientsOptions).render();
            }

            if (document.querySelector("#ordersByChannelChart")) {
                const ordersByChannelOptions = {
                    series: chartData.ordenesPorCanal.series,
                    chart: { type: 'donut', height: 350, fontFamily: apexChartFont },
                    labels: chartData.ordenesPorCanal.labels,
                    colors: ['#2c3856', '#ff9c00', '#666666', '#2b2b2b', '#e5e7eb'],
                    plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total Órdenes', color: '#2c3856' } } } } },
                    legend: { position: 'bottom' },
                    responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: 'bottom' } } }]
                };
                new ApexCharts(document.querySelector("#ordersByChannelChart"), ordersByChannelOptions).render();
            }
        });
    </script>
</x-app-layout>
