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
                
                <a href="{{ route('wms.reports.inventory-aging') }}" 
                   class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-yellow-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 left-0 -mt-12 -ml-12 w-36 h-36 bg-yellow-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                    <div class="mb-4 inline-block p-3 bg-yellow-100 rounded-lg shadow-sm border border-yellow-200">
                        <svg class="w-8 h-8 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-yellow-700 transition-colors duration-300">
                        Antigüedad de Inventario
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Análisis de inventario por LPN y sus días en almacén. ⏳
                    </p>
                    <span class="absolute bottom-6 right-6 text-yellow-300 group-hover:text-yellow-500 group-hover:translate-x-1 transition-transform duration-300">
                        <i class="fas fa-arrow-right fa-lg"></i>
                    </span>
                </a>

                <a href="{{ route('wms.reports.non-available-inventory') }}" 
                   class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-red-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                    <div class="absolute bottom-0 right-0 -mb-10 -mr-10 w-32 h-32 bg-red-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                   
                    <div class="mb-4 inline-block p-3 bg-red-100 rounded-lg shadow-sm border border-red-200">
                        <svg class="w-8 h-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-red-700 transition-colors duration-300">
                        Inventario No Disponible
                    </h3>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        Stock dañado, en inspección o bloqueado que requiere acción. ⚠️
                    </p>
                    
                    <span class="absolute bottom-6 right-6 text-red-300 group-hover:text-red-500 group-hover:translate-x-1 transition-transform duration-300">
                        <i class="fas fa-arrow-right fa-lg"></i>
                    </span>
                </a>

                <a href="{{ route('wms.reports.abc-analysis') }}" 
                   class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-green-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-12 -mr-12 w-36 h-36 bg-green-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                   
                    <div class="mb-4 inline-block p-3 bg-green-100 rounded-lg shadow-sm border border-green-200">
                        <svg class="w-8 h-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1.125-1.5M13.5 16.5L12 15m1.5 1.5l1.125-1.5M13.5 16.5h-1.5m7.5 4.5v-1.5m0 1.5A2.25 2.25 0 0018 18.75h-2.25m-7.5 0h7.5m-7.5 0l-1.125 1.5M13.5 18.75L12 21m1.5-2.25l1.125 1.5M13.5 18.75h-1.5m-3-15.75v11.25c0 1.242.984 2.25 2.25 2.25h2.25c1.242 0 2.25-.984 2.25-2.25V3m-7.5 0h7.5" />
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-green-700 transition-colors duration-300">
                        Análisis ABC-XYZ
                    </h3>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        Clasificación de productos por Valor (ABC) y Frecuencia (XYZ). 📊
                    </p>
                    
                    <span class="absolute bottom-6 right-6 text-green-300 group-hover:text-green-500 group-hover:translate-x-1 transition-transform duration-300">
                        <i class="fas fa-arrow-right fa-lg"></i>
                    </span>
                </a>                

                <a href="{{ route('wms.reports.slotting-heatmap') }}" 
                   class="relative group block bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl hover:border-teal-300 transition-all duration-300 ease-in-out transform hover:-translate-y-1 overflow-hidden">
                    <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-32 h-32 bg-teal-100 rounded-full opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                   
                    <div class="mb-4 inline-block p-3 bg-teal-100 rounded-lg shadow-sm border border-teal-200">
                        <svg class="w-8 h-8 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-teal-700 transition-colors duration-300">
                        Mapa de Calor (Slotting)
                    </h3>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        Visualización interactiva de la eficiencia de ubicaciones vs. productos. 🔥
                    </p>
                    
                    <span class="absolute bottom-6 right-6 text-teal-300 group-hover:text-teal-500 group-hover:translate-x-1 transition-transform duration-300">
                        <i class="fas fa-arrow-right fa-lg"></i>
                    </span>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>