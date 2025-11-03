<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-[#2c3856] leading-tight">
            {{ __('Análisis ABC-XYZ de Inventario') }}
        </h2>
        <p class="text-gray-600 text-sm mt-1">Clasificación de productos por Volumen (ABC) y Frecuencia de Picking (XYZ).</p>
    </x-slot>

    <div class="py-6" x-data="{ showMatrix: true }">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <form method="GET" action="{{ route('wms.reports.abc-analysis') }}" class="flex items-end space-x-4">
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
                    
                    <a href="{{ route('wms.reports.abc-analysis.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        Exportar a CSV
                    </a>

                    <div class="flex-grow flex justify-end">
                        <button @click="showMatrix = !showMatrix" type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <span x-show="!showMatrix">Mostrar Matriz Resumen</span>
                            <span x-show="showMatrix">Ocultar Matriz Resumen</span>
                        </button>
                    </div>
                </form>
            </div>

            <div x-show="showMatrix" x-transition class="mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Matriz de Clasificación (Conteo de SKUs)</h3>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <table class="min-w-full border border-gray-300 text-center">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-3 border border-gray-300">Volumen <span class="text-gray-500">↓</span> / Frecuencia <span class="text-gray-500">→</span></th>
                                <th class="p-3 border border-gray-300 font-bold text-green-700">Clase X (Alta Frec.)</th>
                                <th class="p-3 border border-gray-300 font-bold text-yellow-700">Clase Y (Media Frec.)</th>
                                <th class="p-3 border border-gray-300 font-bold text-red-700">Clase Z (Baja Frec.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="p-3 border border-gray-300 font-bold text-blue-700">Clase A (Alto Vol.)</td>
                                <td class="p-4 border border-gray-300 bg-green-100 text-green-800 font-bold text-lg">{{ $matrix['AX'] }}</td>
                                <td class="p-4 border border-gray-300 bg-yellow-50 text-yellow-800 font-bold text-lg">{{ $matrix['AY'] }}</td>
                                <td class="p-4 border border-gray-300 bg-red-50 text-red-800 font-bold text-lg">{{ $matrix['AZ'] }}</td>
                            </tr>
                            <tr>
                                <td class="p-3 border border-gray-300 font-bold text-blue-700">Clase B (Medio Vol.)</td>
                                <td class="p-4 border border-gray-300 bg-green-50 text-green-800 font-bold text-lg">{{ $matrix['BX'] }}</td>
                                <td class="p-4 border border-gray-300 bg-yellow-50 text-yellow-800 font-bold text-lg">{{ $matrix['BY'] }}</td>
                                <td class="p-4 border border-gray-300 bg-red-50 text-red-800 font-bold text-lg">{{ $matrix['BZ'] }}</td>
                            </tr>
                            <tr>
                                <td class="p-3 border border-gray-300 font-bold text-blue-700">Clase C (Bajo Vol.)</td>
                                <td class="p-4 border border-gray-300 bg-green-50 text-green-800 font-bold text-lg">{{ $matrix['CX'] }}</td>
                                <td class="p-4 border border-gray-300 bg-yellow-50 text-yellow-800 font-bold text-lg">{{ $matrix['CY'] }}</td>
                                <td class="p-4 border border-gray-300 bg-red-50 text-red-800 font-bold text-lg">{{ $matrix['CZ'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>


            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU / Producto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clase ABC-XYZ</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volumen (Unidades)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% Acum. Volumen</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frecuencia (Picks)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% Acum. Frecuencia</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($analysisData as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->sku }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($item->name, 40) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $volColor = $item->volume_class == 'A' ? 'bg-blue-600' : ($item->volume_class == 'B' ? 'bg-blue-400' : 'bg-blue-200 text-blue-800');
                                            $freqColor = $item->freq_class == 'X' ? 'bg-green-600' : ($item->freq_class == 'Y' ? 'bg-green-400' : 'bg-green-200 text-green-800');
                                        @endphp
                                        <span class="px-3 py-1 inline-flex text-base font-bold rounded-full text-white {{ $volColor }}">
                                            {{ $item->volume_class }}
                                        </span>
                                        <span class="px-3 py-1 inline-flex text-base font-bold rounded-full text-white {{ $freqColor }}">
                                            {{ $item->freq_class }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ number_format($item->total_volume) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->volume_cum_perc * 100, 2) }}%</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ number_format($item->total_frequency) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($item->freq_cum_perc * 100, 2) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No se encontró inventario en stock para analizar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>