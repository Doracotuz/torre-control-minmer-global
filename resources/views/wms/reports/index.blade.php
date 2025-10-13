<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Centro de Reportes WMS</h2></x-slot>
    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('wms.reports.inventory') }}" class="block p-6 bg-white border rounded-lg shadow hover:shadow-lg transition-shadow">
                <h3 class="font-bold text-lg text-indigo-600">Dashboard de Inventario</h3>
                <p class="text-sm text-gray-600 mt-2">Analiza la salud, valor y movimiento de tu stock.</p>
            </a>
            </div>
    </div></div>
</x-app-layout>