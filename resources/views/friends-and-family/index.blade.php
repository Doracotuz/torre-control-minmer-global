<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-end">
            <div class="mb-2 md:mb-0">
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-3xl">
                    Bienvenido, 
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                        {{ Auth::user()->name }}
                    </span>
                </h2>
                <p class="mt-2 text-md text-gray-500 font-medium">
                    Aquí se muestra el panel general para la gestión de tu operación.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 md:grid-rows-2 gap-6 h-[75vh]">

                <a href="{{ route('ff.sales.index') }}" 
                   class="group relative md:col-span-2 md:row-span-2 rounded-lg overflow-hidden shadow-lg transition-all duration-500 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                    
                    <img src="https://akasia.com.mx/blog/wp-content/uploads/imagenb.png" 
                         alt="Venta" 
                         class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-110">
                    
                    <div class="absolute bottom-0 left-0 right-0 p-5 bg-white/80 backdrop-blur-sm shadow-inner-t">
                        <h3 class="text-2xl font-semibold text-gray-900">Captura de pedidos</h3>
                        
                        <div class="overflow-hidden transition-all duration-500 ease-in-out max-h-0 group-hover:max-h-40">
                            <p class="text-sm text-gray-600 mt-2 font-light">
                                Iniciar una nueva transacción.
                            </p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('ff.inventory.index') }}" 
                   class="group relative md:col-span-2 rounded-lg overflow-hidden shadow-lg transition-all duration-500 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                    
                    <img src="https://www.beetrack.com/hs-fs/hubfs/Fotos%20Blog/manager-of-warehouse-check-inventory-with-laptop-w-2021-08-29-22-34-00-utc.jpg" 
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
                    
                    <img src="https://blog.agenciaclepsidra.com/hubfs/person-using-laptop-computer-during-daytime-196655.jpg" 
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
                                    
                    <img src="https://www.grupocibernos.com/hubfs/42.png" 
                        alt="Reportes" 
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-110">
                    
                    <div class="absolute bottom-0 left-0 right-0 p-5 bg-white/80 backdrop-blur-sm shadow-inner-t">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Reportes
                        </h3>
                        <div class="overflow-hidden transition-all duration-500 ease-in-out max-h-0 group-hover:max-h-40">
                            <p class="text-sm text-gray-600 mt-2 font-light">
                                Análisis y métricas de la operación.
                            </p>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>