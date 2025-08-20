<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Nota de Crédito') }}
            </h2>
            <div class="flex space-x-2">
                 <a href="{{ route('customer-service.orders.show', $creditNote->order) }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                    &larr; Volver al Pedido
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('customer-service.credit-notes.update', $creditNote) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="request_type" class="block font-medium text-sm text-gray-700">
                                Tipo de Solicitud
                            </label>
                            <select id="request_type" name="request_type" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" required>
                                @foreach($requestTypes as $type)
                                    <option value="{{ $type }}" {{ old('request_type', $creditNote->request_type) == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
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
                            <input id="capture_date" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" type="date" name="capture_date" value="{{ old('capture_date', $creditNote->capture_date) }}" required />
                            @error('capture_date')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customer_name" class="block font-medium text-sm text-gray-700">
                                Cliente
                            </label>
                            <input id="customer_name" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="text" name="customer_name" value="{{ old('customer_name', $creditNote->customer_name) }}" disabled />
                        </div>

                        <div>
                            <label for="invoice" class="block font-medium text-sm text-gray-700">
                                Factura
                            </label>
                            <input id="invoice" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="text" name="invoice" value="{{ old('invoice', $creditNote->invoice) }}" disabled />
                        </div>

                        <div>
                            <label for="credit_note" class="block font-medium text-sm text-gray-700">
                                No. NC
                            </label>
                            <input id="credit_note" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" type="text" name="credit_note" value="{{ old('credit_note', $creditNote->credit_note) }}" />
                            @error('credit_note')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="credit_note_date" class="block font-medium text-sm text-gray-700">
                                Fecha de NC
                            </label>
                            <input id="credit_note_date" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" type="date" name="credit_note_date" value="{{ old('credit_note_date', $creditNote->credit_note_date) }}" />
                            @error('credit_note_date')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customs_document" class="block font-medium text-sm text-gray-700">
                                Pedimento
                            </label>
                            <input id="customs_document" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" type="text" name="customs_document" value="{{ old('customs_document', $creditNote->customs_document) }}" required />
                            @error('customs_document')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="warehouse_id" class="block font-medium text-sm text-gray-700">
                                Almacén
                            </label>
                            <select id="warehouse_id" name="warehouse_id" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" required>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $creditNote->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cause" class="block font-medium text-sm text-gray-700">
                                Causa
                            </label>
                            <select id="cause" name="cause" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" required>
                                @foreach($causes as $cause)
                                    <option value="{{ $cause }}" {{ old('cause', $creditNote->cause) == $cause ? 'selected' : '' }}>
                                        {{ $cause }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cause')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cause_description" class="block font-medium text-sm text-gray-700">
                                Descripción de Causa
                            </label>
                            <textarea id="cause_description" name="cause_description" rows="2" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block w-full mt-1" required>{{ old('cause_description', $creditNote->cause_description) }}</textarea>
                            @error('cause_description')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="arrival_date" class="block font-medium text-sm text-gray-700">
                                Fecha de Arribo
                            </label>
                            <input id="arrival_date" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" type="date" name="arrival_date" value="{{ old('arrival_date', $creditNote->arrival_date) }}" />
                            @error('arrival_date')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="asn" class="block font-medium text-sm text-gray-700">
                                ASN
                            </label>
                            <input id="asn" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" type="text" name="asn" value="{{ old('asn', $creditNote->asn) }}" />
                            @error('asn')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="asn_close_date" class="block font-medium text-sm text-gray-700">
                                Fecha de Cierre ASN
                            </label>
                            <input id="asn_close_date" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" type="date" name="asn_close_date" value="{{ old('asn_close_date', $creditNote->asn_close_date) }}" />
                            @error('asn_close_date')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="observations" class="block font-medium text-sm text-gray-700">
                                Observaciones
                            </label>
                            <textarea id="observations" name="observations" rows="4" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block w-full mt-1">{{ old('observations', $creditNote->observations) }}</textarea>
                            @error('observations')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-lg font-semibold text-gray-700">Detalles de los SKUs</h4>
                        @foreach($creditNote->details as $detail)
                            <div class="mt-4 p-4 border rounded-lg grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                                <div>
                                    <label for="sku-{{ $detail->id }}" class="block font-medium text-sm text-gray-700">
                                        SKU
                                    </label>
                                    <input id="sku-{{ $detail->id }}" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="text" name="sku_details[{{ $detail->id }}][sku]" value="{{ $detail->sku }}" readonly />
                                </div>
                                <div>
                                    <label for="description-{{ $detail->id }}" class="block font-medium text-sm text-gray-700">
                                        Descripción
                                    </label>
                                    <input id="description-{{ $detail->id }}" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full bg-gray-100" type="text" value="{{ $detail->product->description ?? 'N/A' }}" readonly />
                                </div>
                                <div>
                                    <label for="quantity_returned-{{ $detail->id }}" class="block font-medium text-sm text-gray-700">
                                        Cantidad Devuelta
                                    </label>
                                    <input id="quantity_returned-{{ $detail->id }}" class="border-gray-300 focus:border-indigo-300 rounded-md shadow-sm block mt-1 w-full" type="number" name="sku_details[{{ $detail->id }}][quantity_returned]" value="{{ old('sku_details.'.$detail->id.'.quantity_returned', $detail->quantity_returned) }}" required min="1" />
                                    @error('sku_details.'.$detail->id.'.quantity_returned')
                                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Actualizar Nota de Crédito
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>