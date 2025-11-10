<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center"> 
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Reportes: Análisis de Movimientos de Inventario') }}
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

                <div class="bg-white p-6 rounded-lg shadow-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Desglose de Movimientos por Razón (Entradas vs. Salidas)
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Analiza las causas de los cambios de stock más allá de las ventas (ej. ajustes positivos/negativos, mermas).
                    </p>
                    <div id="chart-movement-reasons" style="min-height: 400px;"></div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Rotación de Productos (Ventas vs. Stock vs. Precio)
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Cada burbuja es un producto. El Eje X es el Precio, el Eje Y es el Stock Actual, y el Tamaño de la Burbuja indica el Volumen de Venta (Rotación).
                    </p>
                    <div id="chart-rotation" style="min-height: 450px;"></div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const dataMovementReasons = @json($chartMovementReasons);
        const dataRotation = @json($chartRotation);

        var optionsMovementReasons = {
            series: dataMovementReasons.series,
            chart: {
                type: 'bar',
                height: 380,
                stacked: true,
                toolbar: { show: false }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: {
                        position: 'bottom',
                        offsetX: -10,
                        offsetY: 0
                    }
                }
            }],
            xaxis: {
                categories: dataMovementReasons.categories,
                title: {
                    text: 'Razón del Movimiento'
                }
            },
            yaxis: {
                title: {
                    text: 'Unidades'
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toLocaleString() + " unidades"
                    }
                }
            },
            fill: {
                opacity: 1
            },
            legend: {
                position: 'right',
                offsetX: 0,
                offsetY: 50
            },
            colors: ['#38A169', '#E53E3E']
        };

        var chartMovementReasons = new ApexCharts(document.querySelector("#chart-movement-reasons"), optionsMovementReasons);
        chartMovementReasons.render();

        var optionsRotation = {
            series: dataRotation.series,
            chart: {
                height: 400,
                type: 'bubble',
                toolbar: { show: true }
            },
            dataLabels: {
                enabled: false,
                formatter: function (val, opts) {
                    return opts.w.config.series[opts.seriesIndex].data[opts.dataPointIndex].label;
                }
            },
            fill: {
                opacity: 0.8
            },
            xaxis: {
                tickAmount: 10,
                type: 'numeric',
                labels: {
                    formatter: function (val) {
                        return '$' + parseFloat(val).toFixed(2);
                    }
                },
                title: {
                    text: 'Precio del Producto (Eje X)'
                }
            },
            yaxis: {
                tickAmount: 7,
                title: {
                    text: 'Stock Actual (Eje Y)'
                }
            },
            tooltip: {
                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                    const data = w.globals.series[seriesIndex][dataPointIndex];
                    const label = w.config.series[seriesIndex].data[dataPointIndex].label;
                    const precio = parseFloat(data[0]).toFixed(2);
                    const stock = data[1];
                    const vendido = data[2];

                    return '<div class="arrow_box p-2 bg-white border border-gray-300 rounded shadow-md">' +
                        '<b>' + label + '</b><br/>' +
                        'Precio: <b>$' + precio + '</b><br/>' +
                        'Stock Actual: <b>' + stock + '</b><br/>' +
                        'Unidades Vendidas (Rotación): <b>' + vendido + '</b>' +
                        '</div>'
                }
            }
        };

        var chartRotation = new ApexCharts(document.querySelector("#chart-rotation"), optionsRotation);
        chartRotation.render();

    </script>
</x-app-layout>