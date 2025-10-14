<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Centro de Control de LPNs (Etiquetas de Tarima)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert"><p>{{ session('success') }}</p></div>
            @endif
             @if (session('error'))
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert"><p>{{ session('error') }}</p></div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow"><p class="text-sm text-gray-500">LPNs Totales Generados</p><p class="text-3xl font-bold text-gray-800">{{ $totalLpns }}</p></div>
                <div class="bg-white p-6 rounded-lg shadow"><p class="text-sm text-gray-500">LPNs Disponibles</p><p class="text-3xl font-bold text-green-600">{{ $unusedLpns }}</p></div>
                <div class="bg-white p-6 rounded-lg shadow"><p class="text-sm text-gray-500">LPNs en Uso</p><p class="text-3xl font-bold text-blue-600">{{ $usedLpns }}</p></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-lg shadow space-y-8">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">1. Generar Nuevos LPNs</h3>
                        <p class="text-sm text-gray-600 mt-1">Crea un lote de nuevos IDs únicos para futuras tarimas.</p>
                        <form action="{{ route('wms.lpns.generate') }}" method="POST" class="mt-4 flex items-center space-x-3">
                            @csrf
                            <input type="number" name="quantity" min="1" max="100" value="20" required class="rounded-md border-gray-300 shadow-sm w-32">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700">Generar</button>
                        </form>
                    </div>
                    <div class="border-t pt-8">
                        <h3 class="font-bold text-lg text-gray-800">2. Imprimir LPNs Disponibles</h3>
                        <p class="text-sm text-gray-600 mt-1">Genera un PDF con códigos de barras para los LPNs que aún no se han utilizado.</p>
                        <form action="{{ route('wms.lpns.print') }}" method="GET" target="_blank" class="mt-4 flex items-center space-x-3">
                            <input type="number" name="quantity" min="1" max="100" value="20" required class="rounded-md border-gray-300 shadow-sm w-32">
                            <button type="submit" class="px-4 py-2 bg-gray-700 text-white font-semibold rounded-md hover:bg-gray-800">Imprimir PDF</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-lg shadow">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Reimprimir Etiqueta(s)</h3>
                        <p class="text-sm text-gray-600 mt-1">Busca uno o varios LPNs (separados por coma) para volver a imprimir sus etiquetas.</p>

                        <form action="{{ route('wms.lpns.reprint') }}" method="POST" target="_blank" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <label for="lpns" class="block text-sm font-medium text-gray-700">LPN o lista de LPNs</label>
                                {{-- Usamos un textarea para que sea más fácil pegar listas --}}
                                <textarea name="lpns" id="lpns" rows="3" required placeholder="LPN-ABC123, LPN-DEF456, ..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm font-mono"></textarea>
                                <p class="text-xs text-gray-500 mt-1">Separa cada LPN con una coma.</p>
                                @error('lpns') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Copias de cada etiqueta</label>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="50" required class="mt-1 block w-24 rounded-md border-gray-300 shadow-sm">
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-orange-500 text-white font-semibold rounded-md hover:bg-orange-600">
                                <i class="fas fa-print mr-2"></i> Reimprimir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>