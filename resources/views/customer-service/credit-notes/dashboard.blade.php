<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard de Notas de Crédito') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-2">Notas de Crédito por Tipo de Solicitud</h3>
                        <div id="chart-request-types"></div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-2">Notas de Crédito Mensuales</h3>
                        <div id="chart-monthly-counts"></div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-2">Distribución por Causa</h3>
                        <div id="chart-causes"></div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm lg:col-span-1">
                        <h3 class="text-lg font-semibold mb-2">Top 10 SKUs Devueltos</h3>
                        <div id="chart-top-skus"></div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-2">Top 10 Clientes</h3>
                        <div id="chart-customers"></div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                        <h3 class="text-lg font-semibold mb-2">Top 10 Almacenes</h3>
                        <div id="chart-warehouses"></div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mt-8">
                    <h3 class="text-lg font-semibold mb-4">Últimas 5 Notas de Crédito</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. NC</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. de SO</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Solicitud</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Causa</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha NC</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($latestNotes as $note)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $note->credit_note ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $note->order->so_number ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $note->invoice }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $note->customer_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $note->request_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $note->cause }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $note->credit_note_date ?? 'N/A' }}</td>
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
        const requestTypesData = @json($requestTypes);
        const monthlyCountsData = @json($monthlyCounts);
        const causesData = @json($causes);
        const topReturnedSkusData = @json($topReturnedSkus);
        const topCustomersData = @json($topCustomers);
        const topWarehousesData = @json($topWarehouses);
        
        const monthNames = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        const requestTypesOptions = {
            chart: { type: 'bar', height: 250 },
            series: [{ name: 'Notas de Crédito', data: Object.values(requestTypesData) }],
            xaxis: { categories: Object.keys(requestTypesData) },
        };
        new ApexCharts(document.querySelector("#chart-request-types"), requestTypesOptions).render();

        const monthlyCountsOptions = {
            chart: { type: 'line', height: 250 },
            series: [{ name: 'Notas de Crédito', data: Object.values(monthlyCountsData) }],
            xaxis: {
                categories: Object.keys(monthlyCountsData).map(m => monthNames[m]),
            },
        };
        new ApexCharts(document.querySelector("#chart-monthly-counts"), monthlyCountsOptions).render();

        const causesOptions = {
            chart: { type: 'pie', height: 250 },
            series: Object.values(causesData),
            labels: Object.keys(causesData),
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: { width: 200 },
                    legend: { position: 'bottom' }
                }
            }]
        };
        new ApexCharts(document.querySelector("#chart-causes"), causesOptions).render();

        const topSkusOptions = {
            chart: { type: 'bar', height: 350 },
            plotOptions: { bar: { horizontal: true } },
            series: [{
                name: 'Cantidad Devuelta',
                data: topReturnedSkusData.map(item => item.total_returned)
            }],
            xaxis: {
                categories: topReturnedSkusData.map(item => item.sku),
            }
        };
        new ApexCharts(document.querySelector("#chart-top-skus"), topSkusOptions).render();

        const customersOptions = {
            chart: { type: 'bar', height: 350 },
            plotOptions: { bar: { horizontal: true } },
            series: [{ name: 'Notas de Crédito', data: Object.values(topCustomersData) }],
            xaxis: { categories: Object.keys(topCustomersData) },
        };
        new ApexCharts(document.querySelector("#chart-customers"), customersOptions).render();

        const warehousesOptions = {
            chart: { type: 'bar', height: 350 },
            plotOptions: { bar: { horizontal: true } },
            series: [{ name: 'Notas de Crédito', data: Object.values(topWarehousesData) }],
            xaxis: { categories: Object.keys(topWarehousesData) },
        };
        new ApexCharts(document.querySelector("#chart-warehouses"), warehousesOptions).render();

    </script>

</x-app-layout>