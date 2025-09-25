@extends('layouts.audit-layout')

@section('content')
    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('audit.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 mb-4 inline-block transition-colors">
            &larr; Volver al Dashboard de Auditoría
        </a>
        <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl">
            <div class="border-b pb-4 mb-6">
                <h1 class="text-3xl font-bold text-[#2c3856]">Auditoría de Almacén</h1>
                <p class="text-gray-600 mt-1">SO: <span class="font-semibold text-gray-800">{{ $audit->order->so_number }}</span> | {{ $audit->order->customer_name }}</p>
                <p class="text-gray-500 text-sm mt-1">Ubicación: <span class="font-medium">{{ $audit->location }}</span></p>
            </div>

            <form action="{{ route('audit.warehouse.store', $audit) }}" method="POST">
                @csrf
                <div class="space-y-8">
                    <div>
                        <h3 class="font-bold text-xl text-[#2c3856]">Validación por SKU</h3>
                        <div class="space-y-4 mt-4">
                            @forelse($audit->order->details as $detail)
                                <div class="border rounded-lg p-4 bg-gray-50">
                                    <div class="mb-4">
                                        <p class="font-bold text-gray-800">{{ $detail->sku }} - {{ $detail->product->description ?? 'N/A' }}</p>
                                        <p class="text-sm text-gray-500">Cantidad Pedida: {{ $detail->quantity }} | UPC: {{ $detail->upc->upc ?? $detail->sku }}</p>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                                        <div class="space-y-3">
                                            <label class="flex items-center text-sm font-medium"><input type="checkbox" name="items[{{ $detail->id }}][sku_validado]" value="1" class="rounded mr-2 text-indigo-600 shadow-sm">SKU Coincide</label>
                                            <label class="flex items-center text-sm font-medium"><input type="checkbox" name="items[{{ $detail->id }}][piezas_validadas]" value="1" class="rounded mr-2 text-indigo-600 shadow-sm">Piezas Coinciden</label>
                                            <label class="flex items-center text-sm font-medium"><input type="checkbox" name="items[{{ $detail->id }}][upc_validado]" value="1" class="rounded mr-2 text-indigo-600 shadow-sm">UPC Coincide</label>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Calidad del Producto <span class="text-red-500">*</span></label>
                                            <select name="items[{{ $detail->id }}][calidad]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                                <option value="Buen estado">Buen estado</option>
                                                <option value="Regular">Regular</option>
                                                <option value="Malo">Malo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="border rounded-lg p-4 bg-gray-50 text-center text-gray-500">
                                    Esta orden no tiene detalles para auditar.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-6 border-t">
                        <label for="observaciones" class="block text-sm font-medium text-gray-700">Observaciones Generales</label>
                        <textarea name="observaciones" id="observaciones" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-bold shadow-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Completar Auditoría y Dejar Listo para Patio
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection