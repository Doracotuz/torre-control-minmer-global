<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gráficos de Estadísticas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                        <form method="GET" action="{{ route('admin.statistics.charts') }}" class="flex flex-wrap items-end gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Desde</label>
                                <input type="date" name="start_date" id="start_date_chart" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Hasta</label>
                                <input type="date" name="end_date" id="end_date_chart" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Actualizar Gráficos
                            </button>
                        </form>
                        <a href="{{ route('admin.statistics.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Volver a la Bitácora
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Acciones por Tipo de Actividad</h4>
                            <div id="actions-chart"></div>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Actividad por Tipo de Usuario</h4>
                            <div id="user-type-chart"></div>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Carpetas Creadas por Área</h4>
                            <div id="folders-by-area-chart"></div>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Archivos Subidos por Área</h4>
                            <div id="files-by-area-chart"></div>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Usuarios Activos por Mes</h4>
                            <div id="active-users-by-month-chart"></div>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Actividad Total por Mes</h4>
                            <div id="total-activity-by-month-chart"></div>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Archivos vs. Enlaces</h4>
                            <div id="file-link-comparison-chart"></div>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Tipo de Archivo más Común</h4>
                            <div id="file-types-chart"></div>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Eliminaciones vs. Creaciones</h4>
                            <div id="creation-deletion-chart"></div>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="font-semibold text-lg text-gray-800 mb-4">Actividad por Hora del Día</h4>
                            <div id="activity-by-hour-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const actionData = @json($actionData);
            const userTypeData = @json($userTypeData);
            const foldersByArea = @json($foldersByArea);
            const filesByArea = @json($filesByArea);
            const activeUsersByMonth = @json($activeUsersByMonth);
            const totalActivityByMonth = @json($totalActivityByMonth);
            const fileLinkComparison = @json($fileLinkComparison);
            const fileTypes = @json($fileTypes);
            const creationDeletion = @json($creationDeletion);
            const activityByHour = @json($activityByHour);

            function getChartColors() {
                return ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#d946ef', '#f97316', '#a855f7', '#64748b'];
            }

            const actionsOptions = {
                chart: { type: 'bar', height: 350 },
                series: [{ name: 'Total', data: actionData.map(d => d.total) }],
                xaxis: { categories: actionData.map(d => d.action) },
                colors: ['#2c3856']
            };
            new ApexCharts(document.querySelector("#actions-chart"), actionsOptions).render();

            const userTypeOptions = {
                chart: { type: 'donut', height: 350 },
                series: Object.values(userTypeData),
                labels: Object.keys(userTypeData),
                colors: getChartColors()
            };
            new ApexCharts(document.querySelector("#user-type-chart"), userTypeOptions).render();

            const foldersByAreaOptions = {
                chart: { type: 'bar', height: 350 },
                series: [{ name: 'Carpetas', data: Object.values(foldersByArea) }],
                xaxis: { categories: Object.keys(foldersByArea) },
                colors: ['#f59e0b']
            };
            new ApexCharts(document.querySelector("#folders-by-area-chart"), foldersByAreaOptions).render();

            const filesByAreaOptions = {
                chart: { type: 'bar', height: 350 },
                series: [{ name: 'Archivos', data: Object.values(filesByArea) }],
                xaxis: { categories: Object.keys(filesByArea) },
                colors: ['#10b981']
            };
            new ApexCharts(document.querySelector("#files-by-area-chart"), filesByAreaOptions).render();
            
            const activeUsersByMonthOptions = {
                chart: { type: 'line', height: 350 },
                series: [{ name: 'Usuarios', data: Object.values(activeUsersByMonth) }],
                xaxis: { categories: Object.keys(activeUsersByMonth) },
                colors: ['#8b5cf6']
            };
            new ApexCharts(document.querySelector("#active-users-by-month-chart"), activeUsersByMonthOptions).render();
            
            const totalActivityByMonthOptions = {
                chart: { type: 'line', height: 350 },
                series: [{ name: 'Acciones', data: Object.values(totalActivityByMonth) }],
                xaxis: { categories: Object.keys(totalActivityByMonth) },
                colors: ['#ef4444']
            };
            new ApexCharts(document.querySelector("#total-activity-by-month-chart"), totalActivityByMonthOptions).render();
            
            const fileLinkComparisonOptions = {
                chart: { type: 'donut', height: 350 },
                series: [fileLinkComparison.file || 0, fileLinkComparison.link || 0],
                labels: ['Archivos', 'Enlaces'],
                colors: ['#06b6d4', '#d946ef']
            };
            new ApexCharts(document.querySelector("#file-link-comparison-chart"), fileLinkComparisonOptions).render();

            const fileTypesOptions = {
                chart: { type: 'donut', height: 350 },
                series: Object.values(fileTypes),
                labels: Object.keys(fileTypes).map(ext => ext.toUpperCase()),
                colors: getChartColors()
            };
            new ApexCharts(document.querySelector("#file-types-chart"), fileTypesOptions).render();
            
            const creationDeletionOptions = {
                chart: { type: 'donut', height: 350 },
                series: Object.values(creationDeletion),
                labels: Object.keys(creationDeletion),
                colors: ['#3b82f6', '#ef4444']
            };
            new ApexCharts(document.querySelector("#creation-deletion-chart"), creationDeletionOptions).render();

            const allHours = Array.from({length: 24}, (_, i) => i);
            const seriesData = allHours.map(hour => activityByHour[hour] || 0);

            const activityByHourOptions = {
                chart: { type: 'line', height: 350 },
                series: [{ name: 'Acciones', data: seriesData }],
                xaxis: { categories: allHours.map(h => `${h}:00`), title: { text: 'Hora del Día' } },
                colors: ['#a855f7']
            };
            new ApexCharts(document.querySelector("#activity-by-hour-chart"), activityByHourOptions).render();
        });
    </script>
</x-app-layout>