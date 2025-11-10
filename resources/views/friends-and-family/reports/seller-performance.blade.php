<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">         
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Reportes: Detalle de Consumo por Vendedor') }}
            </h2>
            <a href="{{ route('ff.reports.index') }}"
            class="inline-flex items-center px-6 py-2 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest bg-[#2c3856] hover:bg-[#ff9c00] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 ease-in-out">
                <i class="fas fa-tachometer-alt mr-2"></i>
                Volver a "Reportes"
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-6">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-lg shadow-xl lg:col-span-1">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Distribución de Ingresos por Vendedor
                        </h3>
                        <div id="chart-valor-vendedor" style="min-height: 400px;"></div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-xl lg:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Métricas Clave de Desempeño
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 bg-indigo-50 border-l-4 border-indigo-600 rounded-lg">
                                <p class="text-sm font-medium text-indigo-600">Top Vendedor (Valor)</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ $sellerPerformanceData->first()['name'] ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ '$' . number_format($sellerPerformanceData->first()['valor_total'] ?? 0, 2) }}
                                </p>
                            </div>
                            <div class="p-4 bg-green-50 border-l-4 border-green-600 rounded-lg">
                                <p class="text-sm font-medium text-green-600">Ticket Promedio Mayor</p>
                                @php $maxTicket = $sellerPerformanceData->max('ticket_promedio'); @endphp
                                <p class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ '$' . number_format($maxTicket ?? 0, 2) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Vendedor: {{ $sellerPerformanceData->where('ticket_promedio', $maxTicket)->first()['name'] ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="p-4 bg-yellow-50 border-l-4 border-yellow-600 rounded-lg">
                                <p class="text-sm font-medium text-yellow-600">Total Pedidos Generados</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900">
                                    {{ number_format($sellerPerformanceData->sum('total_pedidos')) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    En todo el evento.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Detalle por Vendedor
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendedor</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total Vendido</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pedidos</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unidades Vendidas</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket Promedio</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">SKUs Únicos Vendidos</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($sellerPerformanceData as $seller)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $seller['name'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-indigo-600">{{ '$' . number_format($seller['valor_total'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ number_format($seller['total_pedidos']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ number_format($seller['total_unidades']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ '$' . number_format($seller['ticket_promedio'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ number_format($seller['skus_unicos']) }}</td>
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
        const dataValorVendedor = @json($chartValorVendedor);

        var optionsValorVendedor = {
            series: dataValorVendedor.series,
            chart: {
                type: 'donut',
                height: 380,
            },
            labels: dataValorVendedor.labels,
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Valor Total',
                                formatter: function (w) {
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return '$' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                                }
                            }
                        }
                    }
                }
            }
        };

        var chartValorVendedor = new ApexCharts(document.querySelector("#chart-valor-vendedor"), optionsValorVendedor);
        chartValorVendedor.render();
    </script>
</x-app-layout>