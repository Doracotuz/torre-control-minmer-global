<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Generador de LPNs (Etiquetas de Tarima)</h2></x-slot>
    <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-8 rounded-lg shadow-xl space-y-8">
            <div>
                <h3 class="font-bold text-lg">Generar Nuevos LPNs</h3>
                <p class="text-sm text-gray-600 mt-1">Crea nuevos IDs únicos para futuras tarimas. Actualmente tienes <span class="font-bold">{{ $unusedLpns }}</span> LPNs sin usar.</p>
                <form action="{{ route('wms.lpns.generate') }}" method="POST" class="mt-4 flex items-center space-x-3">
                    @csrf
                    <input type="number" name="quantity" min="1" max="100" value="10" required class="rounded-md border-gray-300">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Generar</button>
                </form>
            </div>
            <div class="border-t pt-8">
                <h3 class="font-bold text-lg">Imprimir LPNs</h3>
                <p class="text-sm text-gray-600 mt-1">Genera un PDF con códigos de barras para los LPNs no utilizados, listos para imprimir y pegar en las tarimas físicas.</p>
                <form action="{{ route('wms.lpns.print') }}" method="GET" target="_blank" class="mt-4 flex items-center space-x-3">
                    <input type="number" name="quantity" min="1" max="100" value="10" required class="rounded-md border-gray-300">
                    <button type="submit" class="px-4 py-2 bg-gray-700 text-white rounded-md">Imprimir PDF</button>
                </form>
            </div>
        </div>
    </div></div>
</x-app-layout>