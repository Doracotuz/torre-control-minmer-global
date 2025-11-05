<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reportes: Análisis de Precios y Catálogo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="bg-white p-6 rounded-lg shadow-xl lg:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Distribución de Productos por Rango de Precios
                    </h3>
                    <div id="chart-price-distribution" style="min-height: 400px;"></div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-xl lg:col-span-1">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Estado del Catálogo
                    </h3>
                    <div id="chart-active-inactive" style="min-height: 400px;"></div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-xl lg:col-span-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Productos por Marca (Top 10)
                    </h3>
                    <div id="chart-brand" style="min-height: 450px;"></div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>    

    <script>
        const dataPriceDistribution = @json($chartPriceDistribution);
        const dataActiveInactive = @json($chartActiveInactive);
        const dataBrand = @json($chartBrand);

        var optionsPriceDistribution = {
            series: [{
                name: 'Número de Productos',
                data: dataPriceDistribution.series
            }],
            chart: {
                type: 'bar',
                height: 380,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
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
                categories: dataPriceDistribution.categories,
                title: {
                    text: 'Rango de Precios (MXN)'
                }
            },
            yaxis: {
                title: {
                    text: 'Conteo de SKUs'
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " productos"
                    }
                }
            }
        };

        var chartPriceDistribution = new ApexCharts(document.querySelector("#chart-price-distribution"), optionsPriceDistribution);
        chartPriceDistribution.render();

        var optionsActiveInactive = {
            series: dataActiveInactive.series,
            chart: {
                type: 'donut',
                height: 380,
            },
            labels: dataActiveInactive.labels,
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
                                label: 'Total Catálogo',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                }
                            }
                        }
                    }
                }
            }
        };

        var chartActiveInactive = new ApexCharts(document.querySelector("#chart-active-inactive"), optionsActiveInactive);
        chartActiveInactive.render();

        var optionsBrand = {
            series: [{
                data: dataBrand.series
            }],
            chart: {
                type: 'bar',
                height: 400,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    endingShape: 'rounded'
                }
            },
            dataLabels: {
                enabled: true
            },
            xaxis: {
                categories: dataBrand.labels,
                title: {
                    text: 'Número de Productos'
                }
            },
            title: {
                text: 'Distribución por Marca'
            }
        };

        var chartBrand = new ApexCharts(document.querySelector("#chart-brand"), optionsBrand);
        chartBrand.render();

    </script>
</x-app-layout>