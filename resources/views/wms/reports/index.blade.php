<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .btn-nexus { 
            background: #2c3856; color: white; border-radius: 1rem; font-weight: 700;
            transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(44, 56, 86, 0.3); }
        
        .btn-ghost {
            background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700;
        }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        .dashboard-card {
            background: white; border-radius: 2rem; border: 1px solid #f3f4f6;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative; overflow: hidden;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(44, 56, 86, 0.1);
            border-color: #e5e7eb;
        }
        
        .icon-square {
            width: 3.5rem; height: 3.5rem; border-radius: 1rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }
        .dashboard-card:hover .icon-square { transform: scale(1.1) rotate(-5deg); }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen text-[#2b2b2b] font-montserrat pb-20 relative overflow-x-hidden" x-data="reportsMenu()">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 right-0 w-[60vw] h-[60vh] bg-gradient-to-bl from-[#e0e7ff] to-transparent opacity-50 blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 w-[40rem] h-[40rem] bg-[#fff7ed] rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-12 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-12 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00] rounded-full"></span>
                        <span class="text-xs font-bold text-gray-400 tracking-[0.2em] uppercase">Business Intelligence</span>
                    </div>
                    <h1 class="text-5xl md:text-6xl font-raleway font-black text-[#2c3856] leading-none">
                        CENTRAL DE <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-500">REPORTES</span>
                    </h1>
                </div>

                <div class="mt-8 xl:mt-0 flex gap-4">
                    <a href="{{ route('wms.dashboard') }}" class="btn-ghost px-6 py-3 flex items-center gap-2 text-xs uppercase tracking-wider bg-white hover:shadow-md">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 stagger-enter" style="animation-delay: 0.2s;">

                <a :href="getLink('{{ route('wms.reports.inventory') }}')" class="dashboard-card p-8 group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-chart-pie text-8xl text-blue-500"></i>
                    </div>
                    
                    <div class="icon-square bg-blue-50 text-blue-600">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    
                    <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-2 group-hover:text-blue-600 transition-colors">
                        Inventario General
                    </h3>
                    <p class="text-sm text-gray-500 font-medium leading-relaxed mb-6">
                        KPIs en tiempo real, ocupación global y exactitud.
                    </p>
                    
                    <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-blue-600 transition-colors">
                        <span>Ver Dashboard</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <a :href="getLink('{{ route('wms.reports.stock-movements') }}')" class="dashboard-card p-8 group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-exchange-alt text-8xl text-purple-500"></i>
                    </div>

                    <div class="icon-square bg-purple-50 text-purple-600">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    
                    <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-2 group-hover:text-purple-600 transition-colors">
                        Historial Movimientos
                    </h3>
                    <p class="text-sm text-gray-500 font-medium leading-relaxed mb-6">
                        Bitácora detallada de entradas, salidas y ajustes.
                    </p>
                    
                    <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-purple-600 transition-colors">
                        <span>Consultar Trazabilidad</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <a :href="getLink('{{ route('wms.reports.inventory-aging') }}')" class="dashboard-card p-8 group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-clock text-8xl text-amber-500"></i>
                    </div>

                    <div class="icon-square bg-amber-50 text-amber-600">
                        <i class="fas fa-clock"></i>
                    </div>
                    
                    <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-2 group-hover:text-amber-600 transition-colors">
                        Antigüedad
                    </h3>
                    <p class="text-sm text-gray-500 font-medium leading-relaxed mb-6">
                        Análisis de rotación y permanencia de stock.
                    </p>
                    
                    <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-amber-600 transition-colors">
                        <span>Analizar Días</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <a :href="getLink('{{ route('wms.reports.non-available-inventory') }}')" class="dashboard-card p-8 group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-ban text-8xl text-red-500"></i>
                    </div>

                    <div class="icon-square bg-red-50 text-red-600">
                        <i class="fas fa-ban"></i>
                    </div>
                    
                    <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-2 group-hover:text-red-600 transition-colors">
                        Stock No Disponible
                    </h3>
                    <p class="text-sm text-gray-500 font-medium leading-relaxed mb-6">
                        Inventario dañado, en cuarentena o bloqueado.
                    </p>
                    
                    <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-red-600 transition-colors">
                        <span>Gestionar Calidad</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <a :href="getLink('{{ route('wms.reports.abc-analysis') }}')" class="dashboard-card p-8 group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-layer-group text-8xl text-emerald-500"></i>
                    </div>

                    <div class="icon-square bg-emerald-50 text-emerald-600">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    
                    <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-2 group-hover:text-emerald-600 transition-colors">
                        Análisis ABC-XYZ
                    </h3>
                    <p class="text-sm text-gray-500 font-medium leading-relaxed mb-6">
                        Clasificación estratégica por volumen y frecuencia.
                    </p>
                    
                    <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-emerald-600 transition-colors">
                        <span>Ver Clasificación</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

                <a :href="getLink('{{ route('wms.reports.slotting-heatmap') }}')" class="dashboard-card p-8 group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-th text-8xl text-cyan-500"></i>
                    </div>

                    <div class="icon-square bg-cyan-50 text-cyan-600">
                        <i class="fas fa-th"></i>
                    </div>
                    
                    <h3 class="text-2xl font-raleway font-black text-[#2c3856] mb-2 group-hover:text-cyan-600 transition-colors">
                        Heatmap Slotting
                    </h3>
                    <p class="text-sm text-gray-500 font-medium leading-relaxed mb-6">
                        Mapa visual de eficiencia de ubicaciones.
                    </p>
                    
                    <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-cyan-600 transition-colors">
                        <span>Optimizar Layout</span>
                        <i class="fas fa-arrow-right ml-2 transform group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </a>

            </div>
        </div>
    </div>

    <script>
        function reportsMenu() {
            return {
                warehouseId: '',
                areaId: '',
                
                getLink(baseUrl) {
                    const params = new URLSearchParams();
                    if (this.warehouseId) params.append('warehouse_id', this.warehouseId);
                    if (this.areaId) params.append('area_id', this.areaId);
                    
                    const queryString = params.toString();
                    return queryString ? `${baseUrl}?${queryString}` : baseUrl;
                },

                updateLinks() {
                }
            }
        }
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('reportsMenu', reportsMenu);
        });
    </script>
</x-app-layout>