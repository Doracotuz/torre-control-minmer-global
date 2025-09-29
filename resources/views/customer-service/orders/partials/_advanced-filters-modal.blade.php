<div x-show="isAdvancedFilterModalOpen" @keydown.escape.window="isAdvancedFilterModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
    <div @click.outside="isAdvancedFilterModalOpen = false" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-3xl">
        <h3 class="text-xl font-bold text-[#2c3856] mb-6">Filtros Avanzados</h3>

        <div class="space-y-6 max-h-[60vh] overflow-y-auto pr-4">
            
            <fieldset class="border-t border-gray-200 pt-4">
                <legend class="text-lg font-semibold text-gray-700 px-2 -mt-7 bg-white w-auto">Datos del Pedido y Cliente</legend>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Orden de Compra</label>
                        <input type="text" x-model.debounce.300ms="filters.purchase_order_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">BT de OC</label>
                        <input type="text" x-model.debounce.300ms="filters.bt_oc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Razón Social</label>
                        <input type="text" x-model.debounce.300ms="filters.customer_name_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div x-data="{ open: false }" class="relative">
                        <label class="block text-sm font-medium text-gray-700">Canal</label>
                        <button @click="open = !open" type="button" class="mt-1 relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <span class="block truncate" x-text="filters.channel.length > 0 ? filters.channel.join(', ') : 'Todos'"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10 border border-gray-300"><ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            @foreach(['Corporate', 'Especialista', 'Moderno', 'On', 'On trade', 'POSM', 'Private'] as $channelOption)
                                <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"><label class="flex items-center space-x-3 w-full"><input type="checkbox" x-model="filters.channel" value="{{ $channelOption }}" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"><span class="font-normal">{{ $channelOption }}</span></label></li>
                            @endforeach
                        </ul></div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="border-t border-gray-200 pt-4">
                <legend class="text-lg font-semibold text-gray-700 px-2 -mt-7 bg-white w-auto">Datos de Facturación y Logística</legend>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nº Factura</label>
                        <input type="text" x-model.debounce.300ms="filters.invoice_number_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Factura</label>
                        <input type="date" x-model="filters.invoice_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div x-data="{ open: false }" class="relative">
                        <label class="block text-sm font-medium text-gray-700">Almacén Origen</label>
                        <button @click="open = !open" type="button" class="mt-1 relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <span class="block truncate" x-text="filters.origin_warehouse.length > 0 ? filters.origin_warehouse.join(', ') : 'Todos'"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10 border border-gray-300"><ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            @foreach($warehouses as $warehouse)
                                <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"><label class="flex items-center space-x-3 w-full"><input type="checkbox" x-model="filters.origin_warehouse" value="{{ $warehouse }}" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"><span class="font-normal">{{ $warehouse }}</span></label></li>
                            @endforeach
                        </ul></div>
                    </div>
                    <div x-data="{ open: false }" class="relative">
                        <label class="block text-sm font-medium text-gray-700">Localidad Destino</label>
                        <button @click="open = !open" type="button" class="mt-1 relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <span class="block truncate" x-text="filters.destination_locality.length > 0 ? filters.destination_locality.join(', ') : 'Todos'"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10 border border-gray-300"><ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            @foreach($localities as $locality)
                                <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"><label class="flex items-center space-x-3 w-full"><input type="checkbox" x-model="filters.destination_locality" value="{{ $locality }}" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"><span class="font-normal">{{ $locality }}</span></label></li>
                            @endforeach
                        </ul></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Entrega</label>
                        <input type="date" x-model="filters.delivery_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div x-data="{ open: false }" class="relative">
                        <label class="block text-sm font-medium text-gray-700">Ejecutivo</label>
                        <button @click="open = !open" type="button" class="mt-1 relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <span class="block truncate" x-text="filters.executive.length > 0 ? filters.executive.join(', ') : 'Todos'"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"><svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10 border border-gray-300"><ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            @foreach($executives as $executive)
                                <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"><label class="flex items-center space-x-3 w-full"><input type="checkbox" x-model="filters.executive" value="{{ $executive }}" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"><span class="font-normal">{{ $executive }}</span></label></li>
                            @endforeach
                        </ul></div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="border-t border-gray-200 pt-4">
                <legend class="text-lg font-semibold text-gray-700 px-2 -mt-7 bg-white w-auto">Fechas de Evidencia</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Recepción de Evidencia</label>
                        <input type="date" x-model="filters.evidence_reception_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Corte de Evidencias</label>
                        <input type="date" x-model="filters.evidence_cutoff_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
            </fieldset>

        </div>

        <div class="flex justify-end gap-4 mt-8">
            <button type="button" @click="clearAdvancedFilters()" class="px-4 py-2 bg-gray-200 rounded-md">Limpiar Filtros</button>
            <button type="button" @click="isAdvancedFilterModalOpen = false" class="px-4 py-2 bg-blue-600 text-white rounded-md">Aplicar y Cerrar</button>
        </div>
    </div>
</div>