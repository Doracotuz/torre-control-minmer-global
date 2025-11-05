<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Friends & Family') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- 1. Venta (Deshabilitado por ahora) -->
                <a href="{{ route('ff.sales.index') }}" class="bg-white overflow-hidden shadow-2xl sm:rounded-lg p-6 flex flex-col items-center justify-center text-center transition-all transform hover:scale-105 hover:shadow-blue-200 ring-1 ring-blue-500 hover:ring-blue-600">
                    <div class="p-4 bg-blue-100 rounded-full mb-4">
                        <i class="fas fa-cash-register fa-3x text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Venta</h3>
                    <p class="text-sm text-gray-500">Realizar una venta</p>
                </a>

                <!-- 2. Inventario (Deshabilitado por ahora) -->
                <a href="{{ route('ff.inventory.index') }}" class="bg-white overflow-hidden shadow-2xl sm:rounded-lg p-6 flex flex-col items-center justify-center text-center transition-all transform hover:scale-105 hover:shadow-blue-200 ring-1 ring-blue-500 hover:ring-blue-600">
                    <div class="p-4 bg-blue-100 rounded-full mb-4">
                        <i class="fas fa-boxes fa-3x text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Inventario</h3>
                    <p class="text-sm text-gray-500">Gestionar stock</p>
                </a>

                <!-- 3. Catálogo (¡Este es el que funciona!) -->
                <a href="{{ route('ff.catalog.index') }}" class="bg-white overflow-hidden shadow-2xl sm:rounded-lg p-6 flex flex-col items-center justify-center text-center transition-all transform hover:scale-105 hover:shadow-blue-200 ring-1 ring-blue-500 hover:ring-blue-600">
                    <div class="p-4 bg-blue-100 rounded-full mb-4">
                        <i class="fas fa-book-open fa-3x text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Catálogo</h3>
                    <p class="text-sm text-gray-500">Gestionar productos</p>
                </a>

                <!-- 4. Reportes (Deshabilitado por ahora) -->
                <div class="bg-white/70 overflow-hidden shadow-xl sm:rounded-lg p-6 flex flex-col items-center justify-center text-center transition-all transform hover:scale-105 opacity-50 cursor-not-allowed">
                    <div class="p-4 bg-gray-200 rounded-full mb-4">
                        <i class="fas fa-chart-line fa-3x text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-500">Reportes</h3>
                    <p class="text-sm text-gray-400">Próximamente</p>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>