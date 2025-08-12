<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard de Productos') }}
            </h2>
            <a href="{{ route('customer-service.products.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                &larr; Volver a Gestión de Productos
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Gráfico 1: Productos por Tipo -->
                <div class="bg-white p-6 rounded-lg shadow-md col-span-1 h-80">
                    <canvas id="productsByTypeChart"></canvas>
                </div>

                <!-- Gráfico 2: Top 5 Marcas -->
                <div class="bg-white p-6 rounded-lg shadow-md col-span-1 h-80">
                    <canvas id="topBrandsChart"></canvas>
                </div>

                <!-- Gráfico 3: Productos por Marca (Top 10) -->
                <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2 h-96">
                    <canvas id="productsByBrandChart"></canvas>
                </div>

                <!-- Gráfico 4: Productos Creados Recientemente -->
                <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2 h-96">
                    <canvas id="recentProductsChart"></canvas>
                </div>


            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = @json($chartData);

        // Gráfico 1: Productos por Tipo (Doughnut)
        const ctxType = document.getElementById('productsByTypeChart').getContext('2d');
        new Chart(ctxType, {
            type: 'doughnut',
            data: {
                labels: chartData.productsByType.labels,
                datasets: [{
                    label: 'Productos por Tipo',
                    data: chartData.productsByType.data,
                    backgroundColor: ['rgba(54, 162, 235, 0.7)', 'rgba(255, 159, 64, 0.7)'],
                    borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 159, 64, 1)'],
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Distribución por Tipo' } } }
        });

        // Gráfico 2: Top 5 Marcas (Pie)
        const ctxTopBrands = document.getElementById('topBrandsChart').getContext('2d');
        new Chart(ctxTopBrands, {
            type: 'pie',
            data: {
                labels: chartData.topBrands.labels,
                datasets: [{
                    label: 'Top 5 Marcas',
                    data: chartData.topBrands.data,
                    backgroundColor: ['#2c3856', '#ff9c00', '#3498db', '#95a5a6', '#e74c3c'],
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Top 5 Marcas con más Productos' } } }
        });

        // Gráfico 3: Productos por Marca (Barra Horizontal)
        const ctxBrand = document.getElementById('productsByBrandChart').getContext('2d');
        new Chart(ctxBrand, {
            type: 'bar',
            data: {
                labels: chartData.productsByBrand.labels,
                datasets: [{
                    label: 'Total de Productos',
                    data: chartData.productsByBrand.data,
                    backgroundColor: 'rgba(44, 60, 86, 0.8)',
                    borderColor: 'rgba(44, 60, 86, 1)',
                    borderWidth: 1
                }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Productos por Marca (Top 10)' } }, scales: { x: { beginAtZero: true } } }
        });

        // Gráfico 4: Productos Creados Recientemente (Línea)
        const ctxRecent = document.getElementById('recentProductsChart').getContext('2d');
        new Chart(ctxRecent, {
            type: 'line',
            data: {
                labels: chartData.recentProducts.labels,
                datasets: [{
                    label: 'Productos Creados',
                    data: chartData.recentProducts.data,
                    fill: true,
                    backgroundColor: 'rgba(255, 156, 0, 0.2)',
                    borderColor: 'rgba(255, 156, 0, 1)',
                    tension: 0.1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Productos Creados en los Últimos 30 Días' } }, scales: { y: { beginAtZero: true } } }
        });
    });
    </script>
</x-app-layout>
