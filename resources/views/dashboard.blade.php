<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-8"> {{-- Contenedor principal con sombra y padding --}}
                <h3 class="text-2xl font-extrabold text-[#2c3856] mb-8 text-center" style="font-family: 'Raleway', sans-serif;">{{ __('Métricas Generales de la Torre de Control') }}</h3>

                <!-- Contenedor de Tarjetas de Métricas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <!-- Tarjeta: Total de Usuarios -->
                    <div class="bg-white rounded-lg shadow-md p-6 flex items-center justify-between border border-gray-100 transition-transform duration-300 hover:scale-105 hover:shadow-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Total de Usuarios') }}</p>
                            <p class="text-3xl font-bold text-[#2c3856] mt-1" id="totalUsers">Cargando...</p>
                        </div>
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-5v-2a3 3 0 013-3h2a3 3 0 013 3v2h-5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 12a3 3 0 10-6 0 3 3 0 006 0z"></path></svg>
                        </div>
                    </div>

                    <!-- Tarjeta: Total de Áreas -->
                    <div class="bg-white rounded-lg shadow-md p-6 flex items-center justify-between border border-gray-100 transition-transform duration-300 hover:scale-105 hover:shadow-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Total de Áreas') }}</p>
                            <p class="text-3xl font-bold text-[#2c3856] mt-1" id="totalAreas">Cargando...</p>
                        </div>
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M18 10h4M2 10h4M12 2v4M9 20h6a2 2 0 002-2V6a2 2 0 00-2-2H9a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>

                    <!-- Tarjeta: Total de Carpetas -->
                    <div class="bg-white rounded-lg shadow-md p-6 flex items-center justify-between border border-gray-100 transition-transform duration-300 hover:scale-105 hover:shadow-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Total de Carpetas') }}</p>
                            <p class="text-3xl font-bold text-[#2c3856] mt-1" id="totalFolders">Cargando...</p>
                        </div>
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                        </div>
                    </div>

                    <!-- Tarjeta: Total de Archivos/Enlaces -->
                    <div class="bg-white rounded-lg shadow-md p-6 flex items-center justify-between border border-gray-100 transition-transform duration-300 hover:scale-105 hover:shadow-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('Total de Archivos/Enlaces') }}</p>
                            <p class="text-3xl font-bold text-[#2c3856] mt-1" id="totalFileLinks">Cargando...</p>
                        </div>
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de Gráficos -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Gráfico: Carpetas por Área -->
                    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
                        <h4 class="text-lg font-semibold text-[#2c3856] mb-4">{{ __('Carpetas por Área') }}</h4>
                        <div id="foldersByAreaChart"></div>
                    </div>

                    <!-- Gráfico: Distribución de Tipos de Archivo -->
                    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
                        <h4 class="text-lg font-semibold text-[#2c3856] mb-4">{{ __('Distribución de Tipos de Archivo') }}</h4>
                        <div id="fileTypesDistributionChart"></div>
                    </div>

                    <!-- Gráfico: Usuarios por Área (Solo para Super Admin) -->
                    @if (Auth::user()->area && Auth::user()->area->name === 'Administración')
                        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100 lg:col-span-2">
                            <h4 class="text-lg font-semibold text-[#2c3856] mb-4">{{ __('Usuarios por Área (Administración General)') }}</h4>
                            <div id="usersByAreaChart"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir ApexCharts library -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM Content Loaded. Starting dashboard rendering...');

            // Función para obtener datos del backend
            async function fetchDashboardData() {
                try {
                    console.log('Fetching data from /dashboard-data...');
                    const response = await fetch('{{ route('dashboard.data') }}'); // Usar route() helper
                    if (!response.ok) {
                        const errorText = await response.text();
                        throw new Error(`HTTP error! status: ${response.status}, response: ${errorText}`);
                    }
                    const data = await response.json();
                    console.log('Dashboard data fetched successfully:', data);
                    return data;
                } catch (error) {
                    console.error('Error fetching dashboard data:', error);
                    // Mostrar un mensaje de error en el dashboard si la carga falla
                    document.getElementById('totalUsers').innerText = 'Error';
                    document.getElementById('totalAreas').innerText = 'Error';
                    document.getElementById('totalFolders').innerText = 'Error';
                    document.getElementById('totalFileLinks').innerText = 'Error';
                    alert('Error al cargar los datos del dashboard. Por favor, revisa la consola para más detalles.');
                    return null;
                }
            }

            // Función para renderizar métricas y gráficos
            async function renderDashboard() {
                const data = await fetchDashboardData();
                if (!data) {
                    console.log('No data received, stopping dashboard rendering.');
                    return;
                }

                // Actualizar Tarjetas de Métricas
                document.getElementById('totalUsers').innerText = data.totalUsers;
                document.getElementById('totalAreas').innerText = data.totalAreas;
                document.getElementById('totalFolders').innerText = data.totalFolders;
                document.getElementById('totalFileLinks').innerText = data.totalFileLinks;

                console.log('Rendering charts...');

                // Gráfico: Carpetas por Área
                const foldersByAreaOptions = {
                    series: [{
                        name: 'Carpetas',
                        data: data.foldersByArea.map(item => item.count)
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: data.foldersByArea.map(item => item.area_name),
                        labels: {
                            style: {
                                colors: '#666666', // Gris de tu paleta
                                fontFamily: 'Montserrat, sans-serif',
                            },
                        },
                    },
                    yaxis: {
                        title: {
                            text: 'Número de Carpetas',
                            style: {
                                color: '#2c3856', // Azul oscuro de tu paleta
                                fontFamily: 'Montserrat, sans-serif',
                            }
                        },
                        labels: {
                            style: {
                                colors: '#666666',
                                fontFamily: 'Montserrat, sans-serif',
                            },
                        },
                    },
                    fill: {
                        opacity: 1,
                        colors: ['#ff9c00'] // Naranja de tu paleta
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val + " carpetas"
                            }
                        }
                    },
                    grid: {
                        borderColor: '#f1f1f1',
                    },
                };
                // Solo renderizar si el elemento existe y hay datos para el gráfico
                if (document.querySelector("#foldersByAreaChart") && data.foldersByArea.length > 0) {
                    const foldersByAreaChart = new ApexCharts(document.querySelector("#foldersByAreaChart"), foldersByAreaOptions);
                    foldersByAreaChart.render();
                    console.log('Folders by Area Chart rendered.');
                } else {
                    console.warn('Folders by Area Chart container not found or no data.');
                    if (document.querySelector("#foldersByAreaChart")) {
                        document.querySelector("#foldersByAreaChart").innerHTML = '<p class="text-center text-gray-500">No hay datos de carpetas por área para mostrar.</p>';
                    }
                }


                // Gráfico: Distribución de Tipos de Archivo
                const fileTypesDistributionOptions = {
                    series: data.fileTypesDistribution.map(item => item.count),
                    chart: {
                        width: 380,
                        type: 'pie',
                    },
                    labels: data.fileTypesDistribution.map(item => item.type_category), // Usar type_category
                    colors: ['#2c3856', '#ff9c00', '#666666', '#2b2b2b', '#000000'], // Colores de tu paleta
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
                    legend: {
                        labels: {
                            colors: '#666666',
                            fontFamily: 'Montserrat, sans-serif',
                        },
                    },
                    dataLabels: {
                        style: {
                            colors: ['#fff']
                        }
                    },
                };
                if (document.querySelector("#fileTypesDistributionChart") && data.fileTypesDistribution.length > 0) {
                    const fileTypesDistributionChart = new ApexCharts(document.querySelector("#fileTypesDistributionChart"), fileTypesDistributionOptions);
                    fileTypesDistributionChart.render();
                    console.log('File Types Distribution Chart rendered.');
                } else {
                    console.warn('File Types Distribution Chart container not found or no data.');
                    if (document.querySelector("#fileTypesDistributionChart")) {
                        document.querySelector("#fileTypesDistributionChart").innerHTML = '<p class="text-center text-gray-500">No hay datos de distribución de archivos para mostrar.</p>';
                    }
                }

                // Gráfico: Usuarios por Área (Solo para Super Admin)
                @if (Auth::user()->area && Auth::user()->area->name === 'Administración')
                    const usersByAreaOptions = {
                        series: [{
                            name: 'Usuarios',
                            data: data.usersByArea.map(item => item.count)
                        }],
                        chart: {
                            type: 'bar',
                            height: 350,
                            toolbar: { show: false }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '55%',
                                endingShape: 'rounded'
                            },
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            show: true,
                            width: 2,
                            colors: ['transparent']
                        },
                        xaxis: {
                            categories: data.usersByArea.map(item => item.area_name),
                            labels: {
                                style: {
                                    colors: '#666666',
                                    fontFamily: 'Montserrat, sans-serif',
                                },
                            },
                        },
                        yaxis: {
                            title: {
                                text: 'Número de Usuarios',
                                style: {
                                    color: '#2c3856',
                                    fontFamily: 'Montserrat, sans-serif',
                                }
                            },
                            labels: {
                                style: {
                                    colors: '#666666',
                                    fontFamily: 'Montserrat, sans-serif',
                                },
                            },
                        },
                        fill: {
                            opacity: 1,
                            colors: ['#2c3856'] // Azul oscuro de tu paleta
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return val + " usuarios"
                                }
                            }
                        },
                        grid: {
                            borderColor: '#f1f1f1',
                        },
                    };
                    if (document.querySelector("#usersByAreaChart") && data.usersByArea.length > 0) {
                        const usersByAreaChart = new ApexCharts(document.querySelector("#usersByAreaChart"), usersByAreaOptions);
                        usersByAreaChart.render();
                        console.log('Users by Area Chart rendered.');
                    } else {
                        console.warn('Users by Area Chart container not found or no data.');
                        if (document.querySelector("#usersByAreaChart")) {
                            document.querySelector("#usersByAreaChart").innerHTML = '<p class="text-center text-gray-500">No hay datos de usuarios por área para mostrar.</p>';
                        }
                    }
                @endif
            }

            renderDashboard(); // Renderizar el dashboard al cargar la página
        });
    </script>
</x-app-layout>
