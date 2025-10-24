<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <h2 class="font-bold text-3xl text-gray-800 leading-tight tracking-tight">
                Consultar Información de Tarima (LPN)
            </h2>
            <a href="{{ route('wms.inventory.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg shadow-sm hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i> Volver al Inventario
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white p-8 rounded-2xl shadow-xl border mb-8">
                <form action="{{ route('wms.inventory.pallet-info.find') }}" method="POST">
                    @csrf
                    <div class="text-center">
                        <i class="fas fa-barcode text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-2xl font-bold text-gray-800">Escanee o digite LPN a consultar</h3>
                        <p class="text-gray-600 my-4">Ingresa el LPN de la tarima de la cual deseas obtener información.</p>
                    </div>
                    <div class="mt-1 flex flex-col sm:flex-row gap-2">
                        <input type="text" name="lpn" id="lpn" class="flex-grow w-full rounded-md font-mono text-lg" value="{{ $pallet->lpn ?? old('lpn') }}" required autofocus>
                        <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                    </div>
                </form>
            </div>

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-8" role="alert">
                    <p class="font-bold">{{ session('error') }}</p>
                </div>
            @endif

            @if($pallet)
            <div class="bg-white p-8 rounded-2xl shadow-xl border space-y-8 animate-pulse-once" style="--animate-duration: 0.5s;">
                
                <div class="text-center">
                    <p class="text-sm text-gray-500">LPN</p>
                    <h2 class="text-4xl font-bold font-mono text-indigo-600">{{ $pallet->lpn }}</h2>
                </div>

                <div>
                    <h3 class="font-bold text-xl text-gray-800 border-b pb-2 mb-4">Contenido de la Tarima</h3>
                    <ul class="mt-2 space-y-3">
                        @forelse ($pallet->items as $item)
                        <li class="p-3 bg-gray-50 rounded-lg border">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $item->product->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">
                                        <span class="font-mono">{{ $item->product->sku ?? 'N/A' }}</span> | 
                                        <strong class="text-indigo-700">{{ $item->quality->name ?? 'N/A' }}</strong>
                                    </p>
                                </div>
                                <p class="font-bold text-xl text-gray-800">x{{ $item->quantity }}</p>
                            </div>
                        </li>
                        @empty
                        <li class="text-center text-gray-500">Esta tarima no tiene contenido registrado.</li>
                        @endforelse
                    </ul>
                </div>                

                <div>
                    <h3 class="font-bold text-xl text-gray-800 border-b pb-2 mb-4">Información de la Tarima</h3>
                    <dl class="text-sm grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                        <dt class="font-semibold text-gray-500">Ubicación Actual:</dt>
                        <dd class="font-medium">{{ $pallet->location ? "{$pallet->location->aisle}-{$pallet->location->rack}-{$pallet->location->shelf}-{$pallet->location->bin}" : 'N/A' }}</dd>
                        
                        <dt class="font-semibold text-gray-500">Tipo de Ubicación:</dt>
                        <dd>{{ $pallet->location->type ?? 'N/A' }}</dd>
                        
                        <dt class="font-semibold text-gray-500">Recibido por:</dt>
                        <dd>{{ $pallet->user->name ?? 'N/A' }}</dd>
                        
                        <dt class="font-semibold text-gray-500">Fecha de Recepción:</dt>
                        <dd>{{ $pallet->updated_at->format('d/m/Y h:i A') }}</dd>
                    </dl>
                </div>

                <div>
                    <h3 class="font-bold text-xl text-gray-800 border-b pb-2 mb-4">Información del Arribo de Origen</h3>
                    <dl class="text-sm grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                        <dt class="font-semibold text-gray-500">Orden de Compra:</dt>
                        <dd class="font-mono">{{ $pallet->purchaseOrder->po_number ?? 'N/A' }}</dd>

                        <dt class="font-semibold text-gray-500">Contenedor:</dt>
                        <dd class="font-mono">{{ $pallet->purchaseOrder->container_number ?? 'N/A' }}</dd>

                        <dt class="font-semibold text-gray-500">Pedimento A4:</dt>
                        <dd class="font-mono">{{ $pallet->purchaseOrder->pedimento_a4 ?? 'N/A' }}</dd>

                        <dt class="font-semibold text-gray-500">Pedimento G1:</dt>
                        <dd class="font-mono">{{ $pallet->purchaseOrder->pedimento_g1 ?? 'N/A' }}</dd>

                        <dt class="font-semibold text-gray-500">Operador / Placas:</dt>
                        <dd>{{ $pallet->purchaseOrder->operator_name ?? 'N/A' }} / {{ $pallet->purchaseOrder->latestArrival->truck_plate ?? 'N/A' }}</dd>
                    </dl>
                </div>

            </div>
            @endif
        </div>
    </div>
</x-app-layout>