<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard de Pedidos') }}
            </h2>
            <a href="{{ route('customer-service.orders.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                &larr; Volver a Gestión de Pedidos
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-1 lg:col-span-1 h-80"><canvas id="ordersByChannelChart"></canvas></div>
                <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-1 lg:col-span-1 h-80"><canvas id="ordersByStatusChart"></canvas></div>
                
                <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-2 lg:col-span-2 h-80"><canvas id="amountByChannelChart"></canvas></div>

                <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-1 lg:col-span-2 h-96"><canvas id="topCustomersByOrdersChart"></canvas></div>
                <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-1 lg:col-span-2 h-96"><canvas id="topCustomersByAmountChart"></canvas></div>

                <div class="bg-white p-4 rounded-lg shadow md:col-span-2 lg:col-span-4 h-96"><canvas id="recentOrdersChart"></canvas></div>

                <div class="bg-white p-4 rounded-lg shadow col-span-1 lg:col-span-2 h-80"><canvas id="bottlesByChannelChart"></canvas></div>
                <div class="bg-white p-4 rounded-lg shadow col-span-1 lg:col-span-1 h-80"><canvas id="completionStatusChart"></canvas></div>
                <div class="bg-white p-4 rounded-lg shadow col-span-1 lg:col-span-1 h-80"><canvas id="topWarehousesChart"></canvas></div>

            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = @json($chartData);
        const colorPalette = ['#2c3856', '#ff9c00', '#3498db', '#95a5a6', '#e74c3c', '#2ecc71', '#f1c40f', '#8e44ad'];

        const dynamicColors = (num) => {
            var colors = [];
            for (let i = 0; i < num; i++) {
                colors.push(colorPalette[i % colorPalette.length]);
            }
            return colors;
        };

        new Chart(document.getElementById('ordersByChannelChart'), {
            type: 'doughnut', data: { labels: chartData.ordersByChannel.labels, datasets: [{ data: chartData.ordersByChannel.data, backgroundColor: dynamicColors(chartData.ordersByChannel.labels.length) }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Pedidos por Canal' } } }
        });

        new Chart(document.getElementById('ordersByStatusChart'), {
            type: 'pie', data: { labels: chartData.ordersByStatus.labels, datasets: [{ data: chartData.ordersByStatus.data, backgroundColor: ['#95a5a6', '#3498db', '#e74c3c'] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Pedidos por Estatus' } } }
        });
        
        new Chart(document.getElementById('amountByChannelChart'), {
            type: 'bar', data: { labels: chartData.amountByChannel.labels, datasets: [{ label: 'Monto Total', data: chartData.amountByChannel.data, backgroundColor: colorPalette[0] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Monto Total por Canal' } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('topCustomersByOrdersChart'), {
            type: 'bar', data: { labels: chartData.topCustomersByOrders.labels, datasets: [{ label: '# de Pedidos', data: chartData.topCustomersByOrders.data, backgroundColor: colorPalette[1] }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Top 10 Clientes (por # de Pedidos)' } } }
        });
        
        new Chart(document.getElementById('topCustomersByAmountChart'), {
            type: 'bar', data: { labels: chartData.topCustomersByAmount.labels, datasets: [{ label: 'Monto Total', data: chartData.topCustomersByAmount.data, backgroundColor: colorPalette[2] }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Top 10 Clientes (por Monto)' } } }
        });

        new Chart(document.getElementById('recentOrdersChart'), {
            type: 'line', data: { labels: chartData.recentOrders.labels, datasets: [{ label: 'Pedidos Nuevos', data: chartData.recentOrders.data, borderColor: colorPalette[3], tension: 0.1, fill: true, backgroundColor: 'rgba(149, 165, 166, 0.2)' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Pedidos Creados (Últimos 30 Días)' } } }
        });

        new Chart(document.getElementById('bottlesByChannelChart'), {
            type: 'bar', data: { labels: chartData.bottlesByChannel.labels, datasets: [{ label: 'Total de Botellas', data: chartData.bottlesByChannel.data, backgroundColor: dynamicColors(chartData.bottlesByChannel.labels.length) }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Total de Botellas por Canal' } } }
        });

        new Chart(document.getElementById('completionStatusChart'), {
            type: 'doughnut', data: { labels: ['No Cancelados', 'Cancelados'], datasets: [{ data: [chartData.completionStatus.not_cancelled, chartData.completionStatus.cancelled], backgroundColor: [colorPalette[5], colorPalette[4]] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Proporción de Cancelación' } } }
        });

        new Chart(document.getElementById('topWarehousesChart'), {
            type: 'pie', data: { labels: chartData.topWarehouses.labels, datasets: [{ data: chartData.topWarehouses.data, backgroundColor: dynamicColors(chartData.topWarehouses.labels.length) }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Top 5 Almacenes de Origen' } } }
        });
    });
    </script>

</x-app-layout>