@extends('layouts.audit-layout')
@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('audit.index') }}" class="text-sm font-semibold text-gray-600 mb-4 inline-block">&larr; Volver al Dashboard</a>
        <h1 class="text-2xl font-bold text-[#2c3856]">Auditoría de Almacén</h1>
        <p class="text-gray-600 mb-6">SO: {{ $order->so_number }} | {{ $order->customer_name }}</p>

        <form action="{{ route('audit.warehouse.store', $order->id) }}" method="POST">
            @csrf
            <div class="bg-white p-4 rounded-lg shadow-md space-y-6">
                @foreach($order->details as $detail)
                    <div class="border rounded-lg p-4">
                        <p class="font-bold">{{ $detail->sku }} - {{ $detail->product->description ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">Cantidad Pedida: {{ $detail->quantity }} | UPC: {{ $detail->upc->upc ?? $detail->sku }}</p>
                        <div class="mt-4 space-y-3">
                            <label class="flex items-center"><input type="checkbox" name="items[{{ $detail->id }}][sku_validado]" value="1" class="rounded mr-2">SKU Coincide</label>
                            <label class="flex items-center"><input type="checkbox" name="items[{{ $detail->id }}][piezas_validadas]" value="1" class="rounded mr-2">Piezas Coinciden</label>
                            <label class="flex items-center"><input type="checkbox" name="items[{{ $detail->id }}][upc_validado]" value="1" class="rounded mr-2">UPC Coincide</label>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Calidad del Producto</label>
                                <select name="items[{{ $detail->id }}][calidad]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="Buen estado">Buen estado</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Malo">Malo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div>
                    <label class="block text-sm font-medium text-gray-700">Observaciones Generales</label>
                    <textarea name="observaciones" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg font-bold shadow-lg hover:bg-blue-700 transition-colors">Completar Auditoría y Dejar Listo para Enviar</button>
                </div>
            </div>
        </form>
    </div>
@endsection
