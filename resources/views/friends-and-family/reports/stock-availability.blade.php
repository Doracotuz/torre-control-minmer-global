<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reportes: Disponibilidad de Stock y Reservas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">

                @if ($lowStockAlerts->count() > 0)
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg shadow-md" role="alert">
                        <p class="font-bold">🚨 Alerta de Bajo Stock (Umbral < 10 disponibles)</p>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($lowStockAlerts as $product)
                                <li>
                                    **{{ $product['sku'] }}** ({{ $product['description'] }}): 
                                    Stock Total: **{{ $product['total_stock'] }}**, 
                                    Reservado: **{{ $product['total_reserved'] }}**, 
                                    **Disponible: {{ $product['available'] }}**
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-md">
                        <p class="font-bold">✅ Nivel de Stock Óptimo</p>
                        <p>No hay productos activos por debajo del umbral de disponibilidad (10 unidades).</p>
                    </div>
                @endif

                <div class="bg-white p-6 rounded-lg shadow-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Stock Disponible vs. Stock Comprometido (Top 10 SKUs)
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Representación visual del stock disponible para venta inmediata y el stock retenido en carritos de otros usuarios.
                    </p>
                    <div id="chart-stock-vs-reserved" style="min-height: 400px;"></div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Inventario Detallado (Disponibilidad Real)
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Reservado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">DISPONIBLE REAL</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($data as $product)
                                    <tr class="@if ($product['available'] <= 0) bg-red-50 @elseif ($product['available'] < 10) bg-yellow-50 @endif">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product['sku'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product['description'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">{{ number_format($product['total_stock']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-500">{{ number_format($product['total_reserved']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-extrabold @if ($product['available'] <= 0) text-red-600 @elseif ($product['available'] < 10) text-yellow-600 @else text-green-600 @endif">
                                            {{ number_format($product['available']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>    

    <script>
        const dataStockVsReserved = @json($chartStockVsReserved);

        var optionsStockVsReserved = {
            series: dataStockVsReserved.series,
            chart: {
                type: 'bar',
                height: 380,
                stacked: true,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    dataLabels: {
                        total: {
                            enabled: true,
                            formatter: function (w) {
                                return w.globals.stackedSeriesTotals[w.globals.series.length - 1][w.dataPointIndex].toLocaleString();
                            },
                            style: {
                                fontSize: '13px',
                                fontWeight: 900
                            }
                        }
                    }
                },
            },
            stroke: {
                width: 1,
                colors: ['#fff']
            },
            xaxis: {
                categories: dataStockVsReserved.categories,
                title: {
                    text: 'Unidades'
                }
            },
            yaxis: {
                title: {
                    text: 'SKU'
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toLocaleString() + " unidades"
                    }
                }
            },
            fill: {
                opacity: 1
            },
            legend: {
                position: 'bottom',
            },
            colors: ['#48BB78', '#E53E3E']
        };

        var chartStockVsReserved = new ApexCharts(document.querySelector("#chart-stock-vs-reserved"), optionsStockVsReserved);
        chartStockVsReserved.render();

    </script>
</x-app-layout>