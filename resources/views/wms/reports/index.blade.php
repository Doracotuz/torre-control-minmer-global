<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">
                Central de Reportes WMS
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-screen-xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <a href="{{ route('wms.reports.inventory') }}" 
                   class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-blue-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-blue-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                    
                    <div class="mb-4 inline-block p-3 bg-blue-100 rounded-lg shadow-sm border border-blue-200">
                        <svg class="w-8 h-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-blue-700 transition-colors duration-300">
                        Dashboard de Inventario
                    </h3>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        Visión general del stock, productos top y antigüedad del inventario. 📈
                    </p>
                    
                    <span class="absolute bottom-6 right-6 text-blue-300 group-hover:text-blue-500 group-hover:translate-x-1 transition-transform duration-300">
                        <i class="fas fa-arrow-right fa-lg"></i>
                    </span>
                </a>

                <a href="{{ route('wms.reports.stock-movements') }}" 
                   class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-indigo-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                    <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-36 h-36 bg-indigo-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                   
                    <div class="mb-4 inline-block p-3 bg-indigo-100 rounded-lg shadow-sm border border-indigo-200">
                        <svg class="w-8 h-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-indigo-700 transition-colors duration-300">
                        Historial de Movimientos
                    </h3>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        Registro detallado de todas las entradas, salidas, ajustes y transferencias. 🔍
                    </p>
                    
                    <span class="absolute bottom-6 right-6 text-indigo-300 group-hover:text-indigo-500 group-hover:translate-x-1 transition-transform duration-300">
                        <i class="fas fa-arrow-right fa-lg"></i>
                    </span>
                </a>

                <div 
                   class="relative group block bg-gradient-to-br from-gray-100 to-gray-200 p-8 rounded-2xl shadow-inner border border-gray-300 cursor-not-allowed overflow-hidden">
                    <div class="absolute inset-0 bg-repeat opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%239C92AC\' fill-opacity=\'0.1\' fill-rule=\'evenodd\'%3E%3Cpath d=\'M10 18a8 8 0 100-16 8 8 0 000 16zm0 2a10 10 0 100-20 10 10 0 000 20z\'/%3E%3C/g%3E%3C/svg%3E');"></div>

                    <div class="mb-4 inline-block p-3 bg-gray-200 rounded-lg shadow-sm border border-gray-300">
                        <svg class="w-8 h-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-500 mb-2">
                        Próximos Reportes
                    </h3>
                    
                    <p class="text-sm text-gray-500 mb-4">
                        Más análisis y reportes estarán disponibles pronto. ⏳
                    </p>
                    
                    <span class="absolute bottom-6 right-6 text-gray-400">
                        <i class="fas fa-lock fa-lg"></i>
                    </span>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>