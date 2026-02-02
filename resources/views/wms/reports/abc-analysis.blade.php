<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Raleway:wght@800;900&display=swap');
        
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .stagger-enter { opacity: 0; transform: translateY(20px); animation: enterUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        @keyframes enterUp { to { opacity: 1; transform: translateY(0); } }
        
        .input-arch {
            background: transparent; border: none; border-bottom: 2px solid #e5e7eb; border-radius: 0;
            padding: 0.5rem 0; font-family: 'Montserrat', sans-serif; font-weight: 600; color: #2c3856;
            transition: all 0.3s ease; width: 100%;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .btn-nexus { background: #2c3856; color: white; border-radius: 0.8rem; font-weight: 700; transition: all 0.2s; }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); }
        
        .btn-ghost { background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700; }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0 0.5rem; }
        .nexus-table thead th {
            font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: #9ca3af; font-weight: 800;
            padding: 1rem; text-align: left;
        }
        .nexus-row { background: white; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .nexus-row td { padding: 1rem; vertical-align: middle; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; }
        .nexus-row td:first-child { border-top-left-radius: 1rem; border-bottom-left-radius: 1rem; border-left: 1px solid #f3f4f6; }
        .nexus-row td:last-child { border-top-right-radius: 1rem; border-bottom-right-radius: 1rem; border-right: 1px solid #f3f4f6; }
        .nexus-row:hover { box-shadow: 0 10px 30px -10px rgba(44, 56, 86, 0.08); z-index: 10; position: relative; }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden" x-data="{ showMatrix: true }">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10"></div>
            <div class="absolute top-[-20%] right-[-10%] w-[50rem] h-[50rem] bg-gradient-to-b from-[#2c3856]/5 to-transparent rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Inteligencia de Inventario</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] leading-none">
                        ANÁLISIS <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">ABC-XYZ</span>
                    </h1>
                    <p class="text-gray-500 font-medium mt-2 text-sm max-w-2xl">
                        Clasificación estratégica basada en Volumen (ABC) y Frecuencia de Picking (XYZ).
                    </p>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0 items-center">
                    <a href="{{ route('wms.reports.index') }}" class="flex items-center gap-2 px-5 py-3 bg-white border border-gray-200 text-[#666666] font-bold rounded-xl shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all text-sm uppercase tracking-wider">
                        <i class="fas fa-arrow-left"></i> <span>Volver a Reportes</span>
                    </a>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-8 border border-gray-100 shadow-xl mb-8 stagger-enter" style="animation-delay: 0.2s;">
                <form method="GET" action="{{ route('wms.reports.abc-analysis') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 items-end">
                    
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Periodo de Análisis</label>
                        <select name="days" id="days" class="input-arch input-arch-select text-lg">
                            <option value="30" {{ $days == 30 ? 'selected' : '' }}>Últimos 30 Días</option>
                            <option value="60" {{ $days == 60 ? 'selected' : '' }}>Últimos 60 Días</option>
                            <option value="90" {{ $days == 90 ? 'selected' : '' }}>Últimos 90 Días</option>
                            <option value="180" {{ $days == 180 ? 'selected' : '' }}>Últimos 180 Días</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Almacén</label>
                        <select name="warehouse_id" id="warehouse_id" class="input-arch input-arch-select text-lg">
                            <option value="">Global</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $warehouseId == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-[#ff9c00] uppercase tracking-widest block mb-1">Área / Cliente</label>
                        <select name="area_id" id="area_id" class="input-arch input-arch-select text-lg text-[#ff9c00]">
                            <option value="">Todas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn-nexus px-6 py-3 w-full shadow-lg">
                            <i class="fas fa-sync-alt mr-2"></i> Analizar
                        </button>
                        
                        <a href="{{ route('wms.reports.abc-analysis.export', request()->query()) }}" class="btn-ghost px-4 py-3 flex items-center justify-center border-green-200 text-green-600 hover:bg-green-50 hover:border-green-300">
                            <i class="fas fa-file-csv text-xl"></i>
                        </a>
                    </div>

                    <div class="text-right">
                        <button @click="showMatrix = !showMatrix" type="button" class="text-xs font-bold text-gray-400 uppercase tracking-widest hover:text-[#2c3856] underline transition-colors">
                            <span x-show="!showMatrix">Mostrar Matriz</span>
                            <span x-show="showMatrix">Ocultar Matriz</span>
                        </button>
                    </div>
                </form>
            </div>

            <div x-show="showMatrix" x-transition class="mb-10 stagger-enter" style="animation-delay: 0.3s;">
                <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-gray-100">
                    <h3 class="text-lg font-raleway font-black text-[#2c3856] mb-6 border-b border-gray-100 pb-4">
                        Matriz de Distribución de SKUs
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-center border-collapse">
                            <thead>
                                <tr>
                                    <th class="p-4 text-xs font-bold text-gray-400 uppercase tracking-widest border-b-2 border-gray-100 text-left">Volumen ↓ / Frecuencia →</th>
                                    <th class="p-4 text-sm font-black text-green-600 border-b-2 border-green-100 bg-green-50/30 rounded-t-xl">Clase X <span class="text-[10px] font-normal text-gray-500 block">Alta Rotación</span></th>
                                    <th class="p-4 text-sm font-black text-[#ff9c00] border-b-2 border-orange-100 bg-orange-50/30 rounded-t-xl">Clase Y <span class="text-[10px] font-normal text-gray-500 block">Media Rotación</span></th>
                                    <th class="p-4 text-sm font-black text-red-500 border-b-2 border-red-100 bg-red-50/30 rounded-t-xl">Clase Z <span class="text-[10px] font-normal text-gray-500 block">Baja Rotación</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="p-4 text-left font-bold text-[#2c3856] border-r border-gray-100">
                                        Clase A <span class="text-[10px] font-normal text-gray-400 block">Alto Volumen (80%)</span>
                                    </td>
                                    <td class="p-6 border border-gray-100 bg-white hover:bg-green-50 transition-colors">
                                        <span class="text-3xl font-black text-green-700">{{ $matrix['AX'] }}</span>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase mt-1">Estrellas</p>
                                    </td>
                                    <td class="p-6 border border-gray-100 bg-white hover:bg-orange-50 transition-colors">
                                        <span class="text-3xl font-black text-[#ff9c00]">{{ $matrix['AY'] }}</span>
                                    </td>
                                    <td class="p-6 border border-gray-100 bg-white hover:bg-red-50 transition-colors">
                                        <span class="text-3xl font-black text-red-400">{{ $matrix['AZ'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="p-4 text-left font-bold text-[#2c3856] border-r border-gray-100">
                                        Clase B <span class="text-[10px] font-normal text-gray-400 block">Medio Volumen (15%)</span>
                                    </td>
                                    <td class="p-6 border border-gray-100 bg-white hover:bg-green-50 transition-colors">
                                        <span class="text-3xl font-black text-green-600">{{ $matrix['BX'] }}</span>
                                    </td>
                                    <td class="p-6 border border-gray-100 bg-white hover:bg-orange-50 transition-colors">
                                        <span class="text-3xl font-black text-[#ff9c00]">{{ $matrix['BY'] }}</span>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase mt-1">Estables</p>
                                    </td>
                                    <td class="p-6 border border-gray-100 bg-white hover:bg-red-50 transition-colors">
                                        <span class="text-3xl font-black text-red-400">{{ $matrix['BZ'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="p-4 text-left font-bold text-[#2c3856] border-r border-gray-100">
                                        Clase C <span class="text-[10px] font-normal text-gray-400 block">Bajo Volumen (5%)</span>
                                    </td>
                                    <td class="p-6 border border-gray-100 bg-white hover:bg-green-50 transition-colors">
                                        <span class="text-3xl font-black text-green-600">{{ $matrix['CX'] }}</span>
                                    </td>
                                    <td class="p-6 border border-gray-100 bg-white hover:bg-orange-50 transition-colors">
                                        <span class="text-3xl font-black text-[#ff9c00]">{{ $matrix['CY'] }}</span>
                                    </td>
                                    <td class="p-6 border border-gray-100 bg-white hover:bg-red-50 transition-colors">
                                        <span class="text-3xl font-black text-red-400">{{ $matrix['CZ'] }}</span>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase mt-1">Obsoletos</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto pb-12 stagger-enter" style="animation-delay: 0.4s;">
                <table class="nexus-table w-full">
                    <thead>
                        <tr>
                            <th>SKU / Producto</th>
                            <th class="text-center">Clasificación</th>
                            <th class="text-right">Volumen (Uds)</th>
                            <th class="text-right text-xs">% Acum. Vol</th>
                            <th class="text-right">Frecuencia (Picks)</th>
                            <th class="text-right text-xs">% Acum. Frec</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($analysisData as $item)
                            <tr class="nexus-row group">
                                <td>
                                    <div class="font-bold text-[#2c3856] text-sm group-hover:text-[#ff9c00] transition-colors">{{ $item->sku }}</div>
                                    <div class="text-xs text-gray-500 font-medium truncate max-w-md">{{ $item->name }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="inline-flex rounded-lg overflow-hidden shadow-sm">
                                        @php
                                            $volColor = match($item->volume_class) {
                                                'A' => 'bg-[#2c3856] text-white',
                                                'B' => 'bg-blue-100 text-blue-800',
                                                default => 'bg-gray-100 text-gray-500'
                                            };
                                            $freqColor = match($item->freq_class) {
                                                'X' => 'bg-green-500 text-white',
                                                'Y' => 'bg-[#ff9c00] text-white',
                                                default => 'bg-red-100 text-red-500'
                                            };
                                        @endphp
                                        <span class="px-3 py-1 text-xs font-black {{ $volColor }}">{{ $item->volume_class }}</span>
                                        <span class="px-3 py-1 text-xs font-black {{ $freqColor }}">{{ $item->freq_class }}</span>
                                    </div>
                                </td>
                                <td class="text-right font-bold text-gray-700">{{ number_format($item->total_volume) }}</td>
                                <td class="text-right text-xs font-mono text-gray-400">{{ number_format($item->volume_cum_perc * 100, 1) }}%</td>
                                <td class="text-right font-bold text-gray-700">{{ number_format($item->total_frequency) }}</td>
                                <td class="text-right text-xs font-mono text-gray-400">{{ number_format($item->freq_cum_perc * 100, 1) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12">
                                    <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                                        <i class="fas fa-search text-gray-300 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-bold text-sm">No se encontraron datos para el periodo seleccionado.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>