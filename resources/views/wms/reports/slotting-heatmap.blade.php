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
            transition: all 0.3s ease; width: 100%; font-size: 0.9rem;
        }
        .input-arch:focus { border-bottom-color: #ff9c00; box-shadow: none; outline: none; }
        .input-arch-select { background-image: none; cursor: pointer; padding-right: 1.5rem; }

        .btn-nexus { background: #2c3856; color: white; border-radius: 0.8rem; font-weight: 700; transition: all 0.2s; }
        .btn-nexus:hover { background: #1a253a; transform: translateY(-1px); }
        
        .btn-ghost { background: transparent; color: #2c3856; border: 2px solid #e5e7eb; border-radius: 1rem; font-weight: 700; }
        .btn-ghost:hover { border-color: #2c3856; background: #2c3856; color: white; }

        .heatmap-cell {
            position: relative;
            min-height: 40px;
            font-size: 9px;
            line-height: 1.1;
            transition: all 0.2s ease-in-out;
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 4px;
            cursor: pointer;
        }
        .heatmap-cell.active-cell {
            border: 2px solid #2c3856;
            transform: scale(1.1);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            z-index: 20;
        }
        .heatmap-cell:not(.active-cell):hover {
            transform: scale(1.05);
            z-index: 10;
            box-shadow: 0 4px 10px -2px rgba(0,0,0,0.1);
        }

        [x-cloak] { display: none !important; }
    </style>

    <div class="min-h-screen bg-transparent text-[#2b2b2b] font-montserrat pb-20 relative overflow-hidden" x-data="{ viewMode: 'mismatch', selectedLocation: null }">
        
        <div class="fixed inset-0 -z-10 pointer-events-none">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10"></div>
            <div class="absolute top-[-20%] right-[-10%] w-[50rem] h-[50rem] bg-gradient-to-b from-[#2c3856]/5 to-transparent rounded-full blur-[120px]"></div>
        </div>

        <div class="max-w-[1920px] mx-auto px-6 pt-10 relative z-10">
            
            <div class="flex flex-col xl:flex-row justify-between items-end mb-10 stagger-enter" style="animation-delay: 0.1s;">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="w-12 h-1 bg-[#ff9c00]"></span>
                        <span class="text-sm font-bold text-[#2c3856] tracking-[0.3em] uppercase">Optimización de Almacén</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-raleway font-black text-[#2c3856] leading-none">
                        HEATMAP DE <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ff9c00] to-orange-600">SLOTTING</span>
                    </h1>
                    <p class="text-gray-500 font-medium mt-2 text-sm max-w-2xl">
                        Visualización interactiva para detectar ineficiencias de ubicación y rotación.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3 mt-6 xl:mt-0 items-center">
                    <a href="{{ route('wms.reports.index') }}" class="flex items-center gap-2 px-5 py-3 bg-white border border-gray-200 text-[#666666] font-bold rounded-xl shadow-sm hover:shadow-md hover:border-[#2c3856] hover:text-[#2c3856] transition-all text-sm uppercase tracking-wider">
                        <i class="fas fa-arrow-left"></i> <span>Volver a Reportes</span>
                    </a>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] p-8 border border-gray-100 shadow-xl mb-8 stagger-enter" style="animation-delay: 0.2s;">
                <div class="flex flex-col lg:flex-row justify-between items-end gap-8">
                    <form method="GET" action="{{ route('wms.reports.slotting-heatmap') }}" class="w-full lg:w-3/4 grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                        
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Periodo (Frecuencia)</label>
                            <select name="days" id="days" class="input-arch input-arch-select text-sm">
                                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Últimos 30 Días</option>
                                <option value="60" {{ $days == 60 ? 'selected' : '' }}>Últimos 60 Días</option>
                                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Últimos 90 Días</option>
                                <option value="180" {{ $days == 180 ? 'selected' : '' }}>Últimos 180 Días</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Almacén</label>
                            <select name="warehouse_id" id="warehouse_id" class="input-arch input-arch-select text-sm">
                                <option value="">Todos</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $warehouseId == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-bold text-[#ff9c00] uppercase tracking-widest block mb-1">Área / Cliente</label>
                            <select name="area_id" id="area_id" class="input-arch input-arch-select text-sm text-[#ff9c00]">
                                <option value="">Todas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn-nexus px-6 py-3 shadow-lg uppercase tracking-wider text-xs w-full">
                            <i class="fas fa-search mr-2"></i> Analizar
                        </button>
                    </form>

                    <div class="flex items-center gap-2 bg-gray-100 p-1.5 rounded-xl w-full lg:w-auto">
                        <button @click="viewMode = 'mismatch'" 
                                :class="viewMode === 'mismatch' ? 'bg-white text-[#2c3856] shadow-sm' : 'text-gray-500 hover:text-[#2c3856]'"
                                class="flex-1 lg:flex-none px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
                            Errores
                        </button>
                        <button @click="viewMode = 'frequency'" 
                                :class="viewMode === 'frequency' ? 'bg-white text-[#2c3856] shadow-sm' : 'text-gray-500 hover:text-[#2c3856]'"
                                class="flex-1 lg:flex-none px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
                            Frecuencia
                        </button>
                        <button @click="viewMode = 'abc'" 
                                :class="viewMode === 'abc' ? 'bg-white text-[#2c3856] shadow-sm' : 'text-gray-500 hover:text-[#2c3856]'"
                                class="flex-1 lg:flex-none px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
                            Clase ABC
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8 stagger-enter" style="animation-delay: 0.3s;">

                <div class="w-full lg:w-3/4 bg-white rounded-[2.5rem] shadow-xl p-8 border border-gray-100 overflow-hidden">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-100 pb-4">Mapa del Almacén</h3>
                    <div class="overflow-x-auto pb-4 custom-scrollbar">
                        <div class="flex gap-3">
                            @foreach($heatmapData as $aisle => $locations)
                                <div class="flex flex-col gap-1.5" style="min-width: 100px;">
                                    <div class="bg-[#2c3856] text-white text-center font-black py-2 rounded-lg text-xs shadow-md tracking-widest">
                                        PASILLO {{ $aisle }}
                                    </div>
                                    @foreach($locations as $loc)
                                        @php
                                            $bgColor = 'bg-gray-50';
                                            if(!empty($loc->stock_items) && $loc->stock_items->count() > 0) $bgColor = 'bg-gray-200';

                                            if($loc->mismatch_score == 10) $mismatchColor = 'bg-emerald-500 text-white border-emerald-600';
                                            elseif($loc->mismatch_score == 5) $mismatchColor = 'bg-emerald-200 text-emerald-900 border-emerald-300';
                                            elseif($loc->mismatch_score == -5) $mismatchColor = 'bg-amber-300 text-amber-900 border-amber-400';
                                            elseif($loc->mismatch_score == -10) $mismatchColor = 'bg-red-500 text-white border-red-600';
                                            else $mismatchColor = $bgColor . ' text-gray-400';

                                            $freqIntensity = $loc->pick_intensity;
                                            if($freqIntensity > 80) $freqColor = 'bg-purple-600 text-white border-purple-700';
                                            elseif($freqIntensity > 60) $freqColor = 'bg-purple-400 text-white border-purple-500';
                                            elseif($freqIntensity > 40) $freqColor = 'bg-indigo-300 text-indigo-900 border-indigo-400';
                                            elseif($freqIntensity > 20) $freqColor = 'bg-indigo-100 text-indigo-800 border-indigo-200';
                                            elseif($freqIntensity > 0) $freqColor = 'bg-blue-50 text-blue-600 border-blue-100';
                                            else $freqColor = 'bg-gray-50 text-gray-300';

                                            $abcClass = $loc->product_class;
                                            if(str_contains($abcClass, 'A')) $abcColor = 'bg-blue-600 text-white border-blue-700';
                                            elseif(str_contains($abcClass, 'B')) $abcColor = 'bg-blue-300 text-blue-900 border-blue-400';
                                            elseif(str_contains($abcClass, 'C')) $abcColor = 'bg-blue-50 text-blue-600 border-blue-100';
                                            else $abcColor = 'bg-gray-50 text-gray-300';
                                        @endphp
                                        <div 
                                             class="heatmap-cell flex flex-col justify-center text-center p-1"
                                             @click="selectedLocation = {{ json_encode($loc) }}"
                                             :class="{
                                                'active-cell': selectedLocation && selectedLocation.id === {{ $loc->id }},
                                                '{{ $mismatchColor }}': viewMode === 'mismatch',
                                                '{{ $freqColor }}': viewMode === 'frequency',
                                                '{{ $abcColor }}': viewMode === 'abc'
                                             }">
                                            
                                            <div class="font-black text-[10px] tracking-tight">{{ $loc->full_location }}</div>
                                            
                                            <div x-show="viewMode === 'mismatch'" class="font-medium text-[8px] truncate mt-0.5 opacity-90">
                                                {{ $loc->stock_items->first()->product->sku ?? '-' }}
                                            </div>
                                            
                                            <div x-show="viewMode === 'frequency'" class="font-bold text-[9px] mt-0.5">
                                                {{ $loc->pick_frequency }} <span class="font-normal text-[7px] opacity-70">picks</span>
                                            </div>
                                            
                                            <div x-show="viewMode === 'abc'" class="font-black text-[10px] mt-0.5">
                                                {{ $loc->product_class ?? '-' }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/4">
                    <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 sticky top-10 overflow-hidden">
                        
                        <div class="bg-[#2c3856] p-6 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-[#ff9c00] rounded-full blur-[50px] opacity-30 -mr-6 -mt-6"></div>
                            <h3 class="text-white font-bold text-lg relative z-10">Inspector de Ubicación</h3>
                            <p class="text-white/60 text-xs mt-1 relative z-10">Detalles de slotting y stock.</p>
                        </div>

                        <div class="p-6">
                            <div x-show="!selectedLocation" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0">
                                <div class="text-center py-10">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                                        <i class="fas fa-hand-pointer text-2xl"></i>
                                    </div>
                                    <p class="text-gray-400 font-bold text-sm">Selecciona una ubicación del mapa.</p>
                                </div>
                            </div>

                            <template x-if="selectedLocation">
                                <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4">
                                    <div class="flex justify-between items-start mb-6">
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Ubicación Seleccionada</p>
                                            <h3 class="text-3xl font-raleway font-black text-[#2c3856]" x-text="selectedLocation.full_location"></h3>
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-mono bg-gray-100 text-gray-600 border border-gray-200" x-text="selectedLocation.code"></span>
                                        </div>
                                        <button @click="selectedLocation = null" class="w-8 h-8 rounded-full bg-gray-50 hover:bg-red-50 hover:text-red-500 text-gray-400 flex items-center justify-center transition-colors">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <div class="mb-6">
                                        <h4 class="text-xs font-bold text-[#2c3856] uppercase tracking-widest mb-3 border-b border-gray-100 pb-2">Diagnóstico de Slotting</h4>
                                        
                                        <div class="p-4 rounded-xl mb-3"
                                             :class="{
                                                'bg-emerald-50 border border-emerald-100': selectedLocation.mismatch_score > 0,
                                                'bg-amber-50 border border-amber-100': selectedLocation.mismatch_score < 0,
                                                'bg-rose-50 border border-rose-100': selectedLocation.mismatch_score <= -10,
                                                'bg-gray-50 border border-gray-100': selectedLocation.mismatch_score == 0
                                             }">
                                            <p class="font-bold text-sm leading-tight" 
                                               :class="{
                                                  'text-emerald-700': selectedLocation.mismatch_score > 0,
                                                  'text-amber-700': selectedLocation.mismatch_score < 0,
                                                  'text-rose-700': selectedLocation.mismatch_score <= -10,
                                                  'text-gray-600': selectedLocation.mismatch_score == 0
                                               }"
                                               x-text="selectedLocation.mismatch_message"></p>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                                <p class="text-[9px] text-gray-400 uppercase font-bold">Frecuencia</p>
                                                <p class="text-xl font-black text-[#2c3856]"><span x-text="selectedLocation.pick_frequency"></span> <span class="text-xs font-medium text-gray-400">picks</span></p>
                                            </div>
                                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                                <p class="text-[9px] text-gray-400 uppercase font-bold">Clase Prod.</p>
                                                <p class="text-xl font-black text-[#ff9c00]" x-text="selectedLocation.product_class || 'N/A'"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="text-xs font-bold text-[#2c3856] uppercase tracking-widest mb-3 border-b border-gray-100 pb-2">
                                            Contenido Actual (<span x-text="selectedLocation.stock_items.length"></span>)
                                        </h4>
                                        <div class="max-h-60 overflow-y-auto pr-1 space-y-2 custom-scrollbar">
                                            <template x-if="selectedLocation.stock_items.length === 0">
                                                <div class="text-center py-4 text-gray-400 italic text-xs">Ubicación Vacía</div>
                                            </template>
                                            <template x-for="item in selectedLocation.stock_items" :key="item.id">
                                                <div class="p-3 border border-gray-100 rounded-xl bg-white hover:shadow-md transition-shadow">
                                                    <div class="flex justify-between items-center mb-1">
                                                        <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded" x-text="item.pallet.lpn"></span>
                                                        <span class="font-black text-sm text-[#2c3856]" x-text="item.quantity"></span>
                                                    </div>
                                                    <div class="text-xs text-gray-600">
                                                        <p class="font-bold text-gray-800 truncate" x-text="item.product.name"></p>
                                                        <p class="text-[10px] text-gray-400 font-mono mt-0.5" x-text="item.product.sku"></p>
                                                        <p class="mt-1"><span class="text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-gray-100 text-gray-500" x-text="item.quality.name"></span></p>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>