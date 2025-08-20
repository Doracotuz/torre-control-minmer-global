<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nota de Crédito para SO: {{ $order->so_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('customer-service.orders.reverse-logistics.store', $order) }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="request_type" class="block font-medium text-sm text-gray-700">
                                Tipo de Solicitud
                            </label>
                            <select id="request_type" name="request_type" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" required autofocus>
                                <option value="">Seleccione...</option>
                                @foreach($requestTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('request_type')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="capture_date" class="block font-medium text-sm text-gray-700">
                                Fecha de Captura
                            </label>
                            <input id="capture_date" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" type="date" name="capture_date" required />
                            @error('capture_date')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="customer_number" class="block font-medium text-sm text-gray-700">
                                Número de Cliente
                            </label>
                            <input id="customer_number" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="text" value="{{ $order->customer->client_id ?? 'N/A' }}" readonly />
                        </div>
                        <div>
                            <label for="customer_name" class="block font-medium text-sm text-gray-700">
                                Cliente
                            </label>
                            <input id="customer_name" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="text" value="{{ $order->customer_name }}" readonly />
                        </div>
                        <div>
                            <label for="invoice" class="block font-medium text-sm text-gray-700">
                                Factura
                            </label>
                            <input id="invoice" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="text" value="{{ $order->invoice_number }}" readonly />
                        </div>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-lg font-semibold text-gray-700">Detalles de la Orden</h4>
                        @foreach($order->details as $detail)
                            <div class="mt-4 p-4 border rounded-lg grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                                <div>
                                    <label for="sku-{{ $detail->id }}" class="block font-medium text-sm text-gray-700">
                                        SKU
                                    </label>
                                    <input id="sku-{{ $detail->id }}" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="text" name="sku_details[{{ $detail->id }}][sku]" value="{{ $detail->sku }}" readonly />
                                </div>
                                <div>
                                    <label for="description-{{ $detail->id }}" class="block font-medium text-sm text-gray-700">
                                        Descripción
                                    </label>
                                    <input id="description-{{ $detail->id }}" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="text" value="{{ $detail->product->item_description ?? 'N/A' }}" readonly />
                                </div>
                                <div>
                                    <label for="quantity-{{ $detail->id }}" class="block font-medium text-sm text-gray-700">
                                        Cantidad
                                    </label>
                                    <input id="quantity-{{ $detail->id }}" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="number" value="{{ $detail->quantity }}" readonly />
                                </div>
                                <div>
                                    <label for="quantity_returned-{{ $detail->id }}" class="block font-medium text-sm text-gray-700">
                                        Cantidad a Devolver
                                    </label>
                                    <input id="quantity_returned-{{ $detail->id }}" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" type="number" name="sku_details[{{ $detail->id }}][quantity_returned]" value="{{ old('sku_details.'.$detail->id.'.quantity_returned') }}" required min="1" max="{{ $detail->quantity }}" />
                                    @error('sku_details.'.$detail->id.'.quantity_returned')
                                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="warehouse_id" class="block font-medium text-sm text-gray-700">
                                Almacén
                            </label>
                            <select id="warehouse_id" name="warehouse_id" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" required>
                                <option value="">Seleccione...</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ $order->origin_warehouse == $warehouse->name ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="customs_document" class="block font-medium text-sm text-gray-700">
                                Pedimento
                            </label>
                            <input id="customs_document" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" type="text" name="customs_document" value="{{ old('customs_document') }}" required />
                            @error('customs_document')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="cause" class="block font-medium text-sm text-gray-700">
                                Causa
                            </label>
                            <select id="cause" name="cause" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" required>
                                <option value="">Seleccione...</option>
                                @foreach($causes as $cause)
                                    <option value="{{ $cause }}">{{ $cause }}</option>
                                @endforeach
                            </select>
                            @error('cause')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="cause_desc" class="block font-medium text-sm text-gray-700">
                                Causa - Desc
                            </label>
                            <input id="cause_desc" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" type="text" name="cause_desc" value="{{ old('cause_desc') }}" required />
                            @error('cause_desc')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="credit_note_date" class="block font-medium text-sm text-gray-700">
                                Fecha NC
                            </label>
                            <input id="credit_note_date" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" type="date" name="credit_note_date" value="{{ old('credit_note_date') }}" />
                            @error('credit_note_date')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="credit_note" class="block font-medium text-sm text-gray-700">
                                NC
                            </label>
                            <input id="credit_note" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" type="text" name="credit_note" value="{{ old('credit_note') }}" />
                            @error('credit_note')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="delivery_date" class="block font-medium text-sm text-gray-700">
                                Fecha de Entrega (del show)
                            </label>
                            <input id="delivery_date" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="date" value="{{ $order->delivery_date?->format('Y-m-d') }}" readonly />
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="arrival_date" class="block font-medium text-sm text-gray-700">
                                Fecha de Arribo
                            </label>
                            <input id="arrival_date" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" type="date" name="arrival_date" value="{{ old('arrival_date') }}" />
                            @error('arrival_date')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="asn_close_date" class="block font-medium text-sm text-gray-700">
                                Cierre de ASN
                            </label>
                            <input id="asn_close_date" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" type="date" name="asn_close_date" value="{{ old('asn_close_date') }}" />
                            @error('asn_close_date')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="asn" class="block font-medium text-sm text-gray-700">
                                ASN
                            </label>
                            <input id="asn" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full" type="text" name="asn" value="{{ old('asn') }}" />
                            @error('asn')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="observations" class="block font-medium text-sm text-gray-700">
                            Observaciones
                        </label>
                        <textarea id="observations" name="observations" rows="4" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block w-full mt-1">{{ old('observations') }}</textarea>
                        @error('observations')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Guardar Nota de Crédito
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>