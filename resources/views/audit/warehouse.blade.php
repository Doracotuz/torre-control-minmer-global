@extends('layouts.audit-layout')
@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('audit.index') }}" class="text-sm font-semibold text-gray-600 mb-4 inline-block">&larr; Volver al Dashboard</a>
        <h1 class="text-2xl font-bold text-[#2c3856]">Auditoría de Almacén</h1>
        <p class="text-gray-600 mb-6">SO: {{ $order->so_number }} | {{ $order->customer_name }}</p>

        <form action="{{ route('audit.warehouse.store', $order->id) }}" method="POST">
            @csrf
            <div class="bg-white p-4 rounded-lg shadow-md space-y-6">
                <!-- Auditoría General de la Carga -->
                <div class="border rounded-lg p-4">
                    <h3 class="font-bold text-lg mb-4 text-[#2c3856]">Validación General de Carga</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cantidad de Tarimas</label>
                            <input type="number" name="tarimas_cantidad" value="0" class="mt-1 block w-full rounded-md border-gray-300" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo de Tarima</label>
                            <select name="tarimas_tipo" class="mt-1 block w-full rounded-md border-gray-300" required>
                                <option>Chep</option>
                                <option>Estándar</option>
                                <option>Sin tarima</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 space-y-3 mt-4">
                            <label class="flex items-center"><input type="checkbox" name="emplayado_correcto" value="1" class="rounded mr-2 text-[#ff9c00] focus:ring-[#ff9c00]">Emplayado Correcto</label>
                            <label class="flex items-center"><input type="checkbox" name="etiquetado_correcto" value="1" class="rounded mr-2 text-[#ff9c00] focus:ring-[#ff9c00]">Etiquetado Correcto</label>
                            <label class="flex items-center"><input type="checkbox" name="distribucion_correcta" value="1" class="rounded mr-2 text-[#ff9c00] focus:ring-[#ff9c00]">Distribución Correcta</label>
                        </div>
                    </div>
                </div>

                <!-- Auditoría por SKU -->
                <h3 class="font-bold text-lg text-[#2c3856] mt-6">Validación por SKU</h3>
                @foreach($order->details as $detail)
                    <div class="border rounded-lg p-4">
                        <p class="font-bold">{{ $detail->sku }} - {{ $detail->product->description ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">Cantidad Pedida: {{ $detail->quantity }} | UPC: {{ $detail->upc->upc ?? $detail->sku }}</p>
                        
                        <!-- INICIAN CHECKBOXES CORREGIDOS -->
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <label class="flex items-center"><input type="checkbox" name="items[{{ $detail->id }}][sku_validado]" value="1" class="rounded mr-2 text-[#ff9c00] focus:ring-[#ff9c00]">SKU Coincide</label>
                                <label class="flex items-center"><input type="checkbox" name="items[{{ $detail->id }}][piezas_validadas]" value="1" class="rounded mr-2 text-[#ff9c00] focus:ring-[#ff9c00]">Piezas Coinciden</label>
                                <label class="flex items-center"><input type="checkbox" name="items[{{ $detail->id }}][upc_validado]" value="1" class="rounded mr-2 text-[#ff9c00] focus:ring-[#ff9c00]">UPC Coincide</label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Calidad del Producto</label>
                                <select name="items[{{ $detail->id }}][calidad]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="Buen estado">Buen estado</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Malo">Malo</option>
                                </select>
                            </div>
                        </div>
                        <!-- TERMINAN CHECKBOXES CORREGIDOS -->
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
