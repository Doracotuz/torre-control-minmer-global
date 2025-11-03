<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#2c3856] leading-tight">
            {{ __('Centro de Mando de Slotting') }}
        </h2>
        <p class="text-gray-600 text-sm mt-1">Análisis visual interactivo de la eficiencia de asignación de ubicaciones (slotting).</p>
    </x-slot>

    <style>
        .heatmap-cell {
            position: relative;
            min-height: 45px;
            font-size: 10px;
            line-height: 1.2;
            transition: all 0.2s ease-in-out;
            border: 2px solid transparent;
        }
        .heatmap-cell.active-cell {
            border-color: #ff9c00;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(255, 156, 0, 0.5);
            z-index: 10;
        }
        .heatmap-cell:not(.active-cell):hover {
            transform: scale(1.05);
            z-index: 9;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
    </style>

    <div class="py-6" x-data="{ viewMode: 'mismatch', selectedLocation: null }">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-4 rounded-lg shadow-md mb-6 flex justify-between items-center">
                <form method="GET" action="{{ route('wms.reports.slotting-heatmap') }}" class="flex items-end space-x-4">
                    <div>
                        <label for="days" class="block text-sm font-medium text-gray-700">Frecuencia de Picks (Últimos X Días)</label>
                        <select name="days" id="days" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 Días</option>
                            <option value="60" {{ $days == 60 ? 'selected' : '' }}>60 Días</option>
                            <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 Días</option>
                            <option value="180" {{ $days == 180 ? 'selected' : '' }}>180 Días</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#2c3856] hover:bg-[#1f2940]">
                        Analizar
                    </button>
                </form>

                <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700">Ver Mapa de:</span>
                    <button @click="viewMode = 'mismatch'" 
                            :class="viewMode === 'mismatch' ? 'bg-[#ff9c00] text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-semibold transition">
                        Errores de Slotting
                    </button>
                    <button @click="viewMode = 'frequency'" 
                            :class="viewMode === 'frequency' ? 'bg-[#ff9c00] text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-semibold transition">
                        Frecuencia de Picks
                    </button>
                    <button @click="viewMode = 'abc'" 
                            :class="viewMode === 'abc' ? 'bg-[#ff9c00] text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-semibold transition">
                        Clase de Producto
                    </button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row space-y-6 lg:space-y-0 lg:space-x-6">

                <div class="w-full lg:w-3/4 bg-white overflow-hidden shadow-lg rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <div class="flex space-x-2">
                            @foreach($heatmapData as $aisle => $locations)
                                <div class="flex flex-col space-y-1" style="min-width: 90px;">
                                    <div class="bg-gray-700 text-white text-center font-bold p-2 rounded-t-md text-sm">
                                        PASILLO {{ $aisle }}
                                    </div>
                                    @foreach($locations as $loc)
                                        @php
                                            $bgColor = 'bg-gray-100';
                                            if(!empty($loc->stock_items) && $loc->stock_items->count() > 0) $bgColor = 'bg-gray-300';

                                            if($loc->mismatch_score == 10) $mismatchColor = 'bg-green-600 text-white';
                                            elseif($loc->mismatch_score == 5) $mismatchColor = 'bg-green-300 text-green-900';
                                            elseif($loc->mismatch_score == -5) $mismatchColor = 'bg-yellow-400 text-yellow-900';
                                            elseif($loc->mismatch_score == -10) $mismatchColor = 'bg-red-600 text-white';
                                            else $mismatchColor = $bgColor;

                                            $freqIntensity = $loc->pick_intensity;
                                            if($freqIntensity > 80) $freqColor = 'bg-red-600 text-white';
                                            elseif($freqIntensity > 60) $freqColor = 'bg-red-400 text-red-900';
                                            elseif($freqIntensity > 40) $freqColor = 'bg-yellow-400 text-yellow-900';
                                            elseif($freqIntensity > 20) $freqColor = 'bg-yellow-200 text-yellow-800';
                                            elseif($freqIntensity > 0) $freqColor = 'bg-blue-200 text-blue-800';
                                            else $freqColor = 'bg-gray-200 text-gray-500';

                                            $abcClass = $loc->product_class;
                                            if(str_contains($abcClass, 'A')) $abcColor = 'bg-blue-700 text-white';
                                            elseif(str_contains($abcClass, 'B')) $abcColor = 'bg-blue-400 text-blue-900';
                                            elseif(str_contains($abcClass, 'C')) $abcColor = 'bg-blue-200 text-blue-800';
                                            else $abcColor = 'bg-gray-200 text-gray-500';
                                        @endphp
                                        <div 
                                             class="heatmap-cell p-1.5 rounded border border-gray-300 cursor-pointer flex flex-col justify-center text-center"
                                             @click="selectedLocation = {{ json_encode($loc) }}"
                                             :class="{
                                                'active-cell': selectedLocation && selectedLocation.id === {{ $loc->id }},
                                                '{{ $mismatchColor }}': viewMode === 'mismatch',
                                                '{{ $freqColor }}': viewMode === 'frequency',
                                                '{{ $abcColor }}': viewMode === 'abc'
                                             }">
                                            
                                            <div class="font-bold">{{ $loc->full_location }}</div>
                                            <div x-show="viewMode === 'mismatch'" class="font-medium text-xs truncate">
                                                {{ $loc->stock_items->first()->product->sku ?? 'Vacío' }}
                                            </div>
                                            <div x-show="viewMode === 'frequency'" class="font-medium">
                                                {{ $loc->pick_frequency }} picks
                                            </div>
                                            <div x-show="viewMode === 'abc'" class="font-bold">
                                                {{ $loc->product_class ?? 'N/A' }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/4">
                    <div class="bg-white rounded-lg shadow-lg p-5 sticky top-24">
                        
                        <div x-show="!selectedLocation" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0">
                            <h3 class="text-lg font-bold text-gray-800">Panel de Inspección</h3>
                            <p class="mt-4 text-gray-600">
                                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM12 11a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                                <br>
                                Haz clic en una ubicación del mapa de calor para ver su información detallada.
                            </p>
                        </div>

                        <div x-show="selectedLocation" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-2xl font-bold text-[#ff9c00]" x-text="selectedLocation.full_location"></h3>
                                    <p class="text-sm text-gray-500">Código: <span x-text="selectedLocation.code"></span></p>
                                </div>
                                <button @click="selectedLocation = null" class="text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
                            </div>

                            <div class="mt-4 border-t pt-4">
                                <h4 class="font-semibold text-gray-800 mb-2">Análisis de Slotting ({{ $days }} Días)</h4>
                                <div class="p-3 rounded-lg"
                                     :class="{
                                        'bg-green-100 border-green-300': selectedLocation && selectedLocation.mismatch_score > 0,
                                        'bg-yellow-100 border-yellow-300': selectedLocation && selectedLocation.mismatch_score < 0,
                                        'bg-red-100 border-red-300': selectedLocation && selectedLocation.mismatch_score <= -10,
                                        'bg-gray-100 border-gray-300': selectedLocation && selectedLocation.mismatch_score == 0
                                     }">
                                    <p class="font-bold text-sm" 
                                       :class="{
                                          'text-green-800': selectedLocation && selectedLocation.mismatch_score > 0,
                                          'text-yellow-800': selectedLocation && selectedLocation.mismatch_score < 0,
                                          'text-red-800': selectedLocation && selectedLocation.mismatch_score <= -10,
                                          'text-gray-800': selectedLocation && selectedLocation.mismatch_score == 0
                                       }"
                                       x-text="selectedLocation.mismatch_message"></p>
                                </div>
                                <p class="text-sm text-gray-600 mt-2">
                                    Esta ubicación ha tenido <strong x-text="selectedLocation.pick_frequency"></strong> picks.
                                    El producto principal es Clase <strong x-text="selectedLocation.product_class || 'N/A'"></strong>.
                                </p>
                            </div>

                            <div class="mt-4 border-t pt-4">
                                <h4 class="font-semibold text-gray-800 mb-2">
                                    Contenido (<span x-text="selectedLocation ? selectedLocation.stock_items.length : 0"></span> LPNs)
                                </h4>
                                <div class="max-h-64 overflow-y-auto space-y-2 pr-2">
                                    <template x-if="selectedLocation && selectedLocation.stock_items.length === 0">
                                        <p class="text-gray-500 text-sm">Ubicación Vacía.</p>
                                    </template>
                                    <template x-for="item in selectedLocation.stock_items" :key="item.id">
                                        <div class="p-2 border rounded-md bg-gray-50">
                                            <div class="flex justify-between">
                                                <span class="font-bold text-blue-700" x-text="item.pallet.lpn"></span>
                                                <span class="font-bold text-lg text-gray-900" x-text="item.quantity"></span>
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                <p x-text="item.product.sku"></p>
                                                <p class_="truncate" x-text="item.product.name"></p>
                                                <p class="mt-1"><span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800" x-text="item.quality.name"></span></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>