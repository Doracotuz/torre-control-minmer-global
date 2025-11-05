<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Friends & Family') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 md:grid-rows-2 gap-6 h-[75vh]">

                <a href="{{ route('ff.sales.index') }}" 
                   class="group relative md:col-span-2 md:row-span-2 rounded-lg overflow-hidden shadow-lg transition-all duration-500 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                    
                    <img src="https://cdnx.jumpseller.com/la-vinateria/image/63870582/Captura_de_pantalla_2025-05-28_a_la_s__3.14.58_p.m..png?1748459725" 
                         alt="Venta" 
                         class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-110">
                    
                    <div class="absolute bottom-0 left-0 right-0 p-5 bg-white/80 backdrop-blur-sm shadow-inner-t">
                        <h3 class="text-2xl font-semibold text-gray-900">Punto de Venta</h3>
                        
                        <div class="overflow-hidden transition-all duration-500 ease-in-out max-h-0 group-hover:max-h-40">
                            <p class="text-sm text-gray-600 mt-2 font-light">
                                Iniciar una nueva transacción.
                            </p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('ff.inventory.index') }}" 
                   class="group relative md:col-span-2 rounded-lg overflow-hidden shadow-lg transition-all duration-500 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                    
                    <img src="https://bsmedia.business-standard.com/_media/bs/img/article/2025-05/14/full/1747220660-1474.jpg" 
                         alt="Inventario" 
                         class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-110">
                    
                    <div class="absolute bottom-0 left-0 right-0 p-5 bg-white/80 backdrop-blur-sm shadow-inner-t">
                        <h3 class="text-2xl font-semibold text-gray-900">Inventario</h3>
                        <div class="overflow-hidden transition-all duration-500 ease-in-out max-h-0 group-hover:max-h-40">
                            <p class="text-sm text-gray-600 mt-2 font-light">
                                Gestionar el stock de productos.
                            </p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('ff.catalog.index') }}" 
                   class="group relative rounded-lg overflow-hidden shadow-lg transition-all duration-500 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                    
                    <img src="https://www.dondeir.com/wp-content/uploads/2024/10/nahual-bebidas-para-dia-de-muertos-con-moet-hennessy.jpg" 
                         alt="Catálogo" 
                         class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-110">
                    
                    <div class="absolute bottom-0 left-0 right-0 p-5 bg-white/80 backdrop-blur-sm shadow-inner-t">
                        <h3 class="text-2xl font-semibold text-gray-900">Catálogo</h3>
                        <div class="overflow-hidden transition-all duration-500 ease-in-out max-h-0 group-hover:max-h-40">
                            <p class="text-sm text-gray-600 mt-2 font-light">
                                Administrar productos y precios.
                            </p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('ff.reports.index') }}" 
                class="group relative rounded-lg overflow-hidden shadow-lg transition-all duration-500 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                                    
                    <img src="https://vino-joy.com/wp-content/uploads/2025/05/MOET-HENNESSY-IMAGE.jpg" 
                        alt="Reportes" 
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-110">
                    
                    <div class="absolute bottom-0 left-0 right-0 p-5 bg-white/80 backdrop-blur-sm shadow-inner-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Reportes
                        </h3>
                        <div class="overflow-hidden transition-all duration-500 ease-in-out max-h-0 group-hover:max-h-40">
                            <p class="text-sm text-gray-600 mt-2 font-light">
                                Análisis y métricas del evento.
                            </p>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>