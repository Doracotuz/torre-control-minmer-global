<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard de Clientes') }}
            </h2>
            <a href="{{ route('customer-service.customers.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                &larr; Volver a Gestión de Clientes
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Gráfico 1: Clientes por Canal -->
                <div class="bg-white p-6 rounded-lg shadow-md col-span-1 h-96">
                    <canvas id="customersByChannelChart"></canvas>
                </div>

                <!-- Gráfico 2: Top 10 Clientes -->
                <div class="bg-white p-6 rounded-lg shadow-md col-span-1 h-96">
                    <canvas id="topCustomersChart"></canvas>
                </div>

                <!-- Gráfico 3: Clientes Creados Recientemente -->
                <div class="bg-white p-6 rounded-lg shadow-md lg:col-span-2 h-96">
                    <canvas id="recentCustomersChart"></canvas>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = @json($chartData);
        const colorPalette = ['#2c3856', '#ff9c00', '#3498db', '#95a5a6', '#e74c3c', '#2ecc71', '#f1c40f'];

        // Gráfico 1: Clientes por Canal (Pie)
        const ctxChannel = document.getElementById('customersByChannelChart').getContext('2d');
        new Chart(ctxChannel, {
            type: 'pie',
            data: {
                labels: chartData.customersByChannel.labels,
                datasets: [{
                    label: 'Clientes por Canal',
                    data: chartData.customersByChannel.data,
                    backgroundColor: colorPalette,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Distribución de Clientes por Canal' } } }
        });

        // Gráfico 2: Top 10 Clientes (Barra Horizontal)
        const ctxTopCustomers = document.getElementById('topCustomersChart').getContext('2d');
        new Chart(ctxTopCustomers, {
            type: 'bar',
            data: {
                labels: chartData.topCustomers.labels,
                datasets: [{
                    label: 'Total de Registros por Canal',
                    data: chartData.topCustomers.data,
                    backgroundColor: 'rgba(44, 60, 86, 0.8)',
                    borderColor: 'rgba(44, 60, 86, 1)',
                    borderWidth: 1
                }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Top 10 Clientes (por # de canales)' } }, scales: { x: { beginAtZero: true } } }
        });

        // Gráfico 3: Clientes Creados Recientemente (Línea)
        const ctxRecent = document.getElementById('recentCustomersChart').getContext('2d');
        new Chart(ctxRecent, {
            type: 'line',
            data: {
                labels: chartData.recentCustomers.labels,
                datasets: [{
                    label: 'Clientes Nuevos',
                    data: chartData.recentCustomers.data,
                    fill: true,
                    backgroundColor: 'rgba(255, 156, 0, 0.2)',
                    borderColor: 'rgba(255, 156, 0, 1)',
                    tension: 0.1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Clientes Creados en el Último Año' } }, scales: { y: { beginAtZero: true } } }
        });
    });
    </script>
</x-app-layout>
