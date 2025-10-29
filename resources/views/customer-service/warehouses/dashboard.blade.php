<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard de Almacenes') }}
            </h2>
            <a href="{{ route('customer-service.warehouses.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                &larr; Volver a Gestión de Almacenes
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="bg-white p-6 rounded-lg shadow-md col-span-1 h-96">
                    <canvas id="topCreatorsChart"></canvas>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md col-span-1 h-96 flex flex-col items-center justify-center text-center">
                    <h3 class="text-lg font-semibold text-gray-500 mb-4">Total de Almacenes Registrados</h3>
                    <p class="text-7xl font-bold text-[#2c3856]">{{ $chartData['totalWarehouses'] }}</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md col-span-1 h-96 flex items-center justify-center text-gray-400">
                    <canvas id="recentWarehousesChart"></canvas>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = @json($chartData);

        const ctxTopCreators = document.getElementById('topCreatorsChart').getContext('2d');
        new Chart(ctxTopCreators, {
            type: 'bar',
            data: {
                labels: chartData.topCreators.labels,
                datasets: [{
                    label: 'Almacenes Creados',
                    data: chartData.topCreators.data,
                    backgroundColor: 'rgba(44, 60, 86, 0.8)',
                    borderColor: 'rgba(44, 60, 86, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Top 5 Usuarios Creadores' } }, scales: { y: { beginAtZero: true } } }
        });

        const ctxRecent = document.getElementById('recentWarehousesChart').getContext('2d');
        new Chart(ctxRecent, {
            type: 'line',
            data: {
                labels: chartData.recentWarehouses.labels,
                datasets: [{
                    label: 'Almacenes Creados',
                    data: chartData.recentWarehouses.data,
                    fill: true,
                    backgroundColor: 'rgba(255, 156, 0, 0.2)',
                    borderColor: 'rgba(255, 156, 0, 1)',
                    tension: 0.1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Almacenes Creados en el Último Año' } }, scales: { y: { beginAtZero: true } } }
        });
    });
    </script>
</x-app-layout>
