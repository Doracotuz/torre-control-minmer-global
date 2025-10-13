<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Dashboard de Inventario</h2></x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="p-6 bg-white rounded-lg shadow"><p class="text-sm text-gray-500">Unidades Totales en Stock</p><p class="text-3xl font-bold">{{ number_format($totalUnits) }}</p></div>
                <div class="p-6 bg-white rounded-lg shadow"><p class="text-sm text-gray-500">SKUs Únicos con Stock</p><p class="text-3xl font-bold">{{ $skusWithStock }}</p></div>
                <div class="p-6 bg-white rounded-lg shadow"><p class="text-sm text-gray-500">Ubicaciones Utilizadas</p><p class="text-3xl font-bold">{{ $locationsUsed }}</p></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="p-6 bg-white rounded-lg shadow">
                    <h3 class="font-semibold mb-4">Top 10 Productos por Unidades</h3>
                    <div id="topProductsChart"></div>
                </div>
                <div class="p-6 bg-white rounded-lg shadow">
                    <h3 class="font-semibold mb-4">Antigüedad del Inventario (Aging)</h3>
                    <div id="agingChart"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gráfico Top 10 Productos
            const topProductsData = @json($topProducts);
            if (document.querySelector("#topProductsChart") && topProductsData.length > 0) {
                const topProductsOptions = {
                    series: [{ data: topProductsData.map(p => p.total_quantity) }],
                    chart: { type: 'bar', height: 350 },
                    plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: topProductsData.map(p => p.product.name) },
                };
                new ApexCharts(document.querySelector("#topProductsChart"), topProductsOptions).render();
            }

            // Gráfico Antigüedad de Inventario
            const agingData = @json($agingData);
            if (document.querySelector("#agingChart")) {
                const agingOptions = {
                    series: Object.values(agingData),
                    chart: { type: 'donut', height: 350 },
                    labels: Object.keys(agingData),
                    legend: { position: 'bottom' }
                };
                new ApexCharts(document.querySelector("#agingChart"), agingOptions).render();
            }
        });
    </script>
</x-app-layout>