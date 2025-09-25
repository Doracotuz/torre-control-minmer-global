<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-[#2c3856] leading-tight">Módulo de Validación de UPC</h2>
    </x-slot>

    <div x-data="{
        openModal: false,
        selectedOrder: null,
        selectedIds: [],
        isPageSelected: false,

        init() {
            const savedIds = sessionStorage.getItem('validation_selected_ids');
            if (savedIds) {
                this.selectedIds = JSON.parse(savedIds);
            }
            this.$watch('selectedIds', (newValue) => {
                sessionStorage.setItem('validation_selected_ids', JSON.stringify(newValue));
                this.updateHeaderCheckboxState();
            });
            this.updateHeaderCheckboxState();

            const flashSuccess = sessionStorage.getItem('validation_flash_success');
            const flashError = sessionStorage.getItem('validation_flash_error');

            if (flashSuccess) {
                const successMsgEl = document.getElementById('flash-success-message');
                const successEl = document.getElementById('flash-success');
                if(successMsgEl && successEl) {
                    successMsgEl.innerText = flashSuccess;
                    successEl.style.display = 'flex';
                    sessionStorage.removeItem('validation_flash_success');
                    setTimeout(() => { successEl.style.display = 'none'; }, 5000);
                }
            }
            if (flashError) {
                const errorMsgEl = document.getElementById('flash-error-message');
                const errorEl = document.getElementById('flash-error');
                if(errorMsgEl && errorEl) {
                    errorMsgEl.innerText = flashError;
                    errorEl.style.display = 'flex';
                    sessionStorage.removeItem('validation_flash_error');
                    setTimeout(() => { errorEl.style.display = 'none'; }, 5000);
                }
            }
        },

        togglePageSelection() {
            const visibleIds = Array.from(document.querySelectorAll('.order_checkbox')).map(el => String(el.value));
            if (this.isPageSelected) {
                this.selectedIds = this.selectedIds.filter(id => !visibleIds.includes(id));
            } else {
                this.selectedIds = [...new Set([...this.selectedIds, ...visibleIds])];
            }
        },

        updateHeaderCheckboxState() {
            const visibleIds = Array.from(document.querySelectorAll('.order_checkbox')).map(el => String(el.value));
            if (visibleIds.length === 0) {
                this.isPageSelected = false;
                return;
            }
            this.isPageSelected = visibleIds.every(id => this.selectedIds.includes(id));
        },

        downloadTemplate() {
            if (this.selectedIds.length === 0) {
                alert('Por favor, selecciona al menos una orden para descargar la plantilla.');
                return;
            }
            let url = `{{ route('customer-service.validation.template') }}`;
            const params = new URLSearchParams();
            this.selectedIds.forEach(id => params.append('ids[]', id));
            window.location.href = `${url}?${params.toString()}`;
        },

        clearSelection() {
            this.selectedIds = [];
            sessionStorage.removeItem('validation_selected_ids');
        }
    }" x-init="init()" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div id="flash-success" class="fixed top-4 right-4 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert" style="display: none;">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <strong class="font-bold mr-1">¡Éxito!</strong>
                    <span id="flash-success-message" class="block sm:inline"></span>
                </div>
                <button onclick="document.getElementById('flash-success').style.display = 'none';" class="text-gray-500 hover:text-gray-700 focus:outline-none"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div id="flash-error" class="fixed top-4 right-4 z-50 bg-white border-l-4 border-red-600 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert" style="display: none;">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <strong class="font-bold mr-1">¡Error!</strong>
                    <span id="flash-error-message" class="block sm:inline"></span>
                </div>
                <button onclick="document.getElementById('flash-error').style.display = 'none';" class="text-gray-500 hover:text-gray-700 focus:outline-none"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-xl mb-6">
                <form x-ref="filtersForm" method="GET" action="{{ route('customer-service.validation.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                    <div class="lg:col-span-2">
                        <label for="search" class="text-sm font-semibold text-gray-600">Buscar</label>
                        <input @input.debounce.500ms="$refs.filtersForm.submit()" type="text" id="search" name="search" placeholder="Por SO, Cliente o Guía..." value="{{ request('search') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-sm font-semibold text-gray-600">Estatus</label>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-2">
                            @php $selected_statuses = request('status', []); @endphp
                            @foreach($allStatuses as $status)
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="status[]" value="{{ $status }}" @if(in_array($status, $selected_statuses)) checked @endif onchange="this.form.submit()" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">{{ $status }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label for="start_date" class="text-sm font-semibold text-gray-600">Fecha Inicio</label>
                        <input @change="$refs.filtersForm.submit()" type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="end_date" class="text-sm font-semibold text-gray-600">Fecha Fin</label>
                        <input @change="$refs.filtersForm.submit()" type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </form>
                
                <div class="mt-4 pt-4 border-t flex items-center gap-4 flex-wrap">
                    <button @click="downloadTemplate()" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold"><i class="fas fa-download mr-2"></i>Descargar Plantilla</button>
                    <button @click="document.getElementById('importModal').classList.remove('hidden')" class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm font-semibold"><i class="fas fa-file-upload mr-2"></i>Importar CSV</button>
                    <button id="open-spec-modal" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold"><i class="fas fa-tasks mr-2"></i>Especificaciones de Entrega</button>
                    <button x-show="selectedIds.length > 0" @click="clearSelection()" class="px-4 py-2 bg-red-500 text-white rounded-md text-sm font-semibold" x-transition><i class="fas fa-trash-alt mr-2"></i>Limpiar Selección (<span x-text="selectedIds.length"></span>)</button>
                    <a href="{{ route('customer-service.audit-reports.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold">
                        <i class="fas fa-history mr-2"></i>Historial de Auditorías
                    </a>                    
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-4"><input type="checkbox" x-model="isPageSelected" @click="togglePageSelection()" class="rounded border-gray-300"></th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">SO</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Cliente</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Fecha Creación</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Canal</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Estatus</th>
                            <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4"><input type="checkbox" class="order_checkbox rounded border-gray-300" value="{{ $order->id }}" x-model="selectedIds"></td>
                                <td class="py-3 px-4 text-sm">{{ $order->so_number }}</td>
                                <td class="py-3 px-4 text-sm">{{ $order->customer_name }}</td>
                                <td class="py-3 px-4 text-sm">{{ $order->creation_date->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 text-sm">{{ $order->channel }}</td>
                                <td class="py-3 px-4 text-sm"><span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $order->status }}</span></td>
                                <td class="py-3 px-4"><button @click="openModal = true; selectedOrder = {{ json_encode($order) }}" class="px-3 py-1 bg-[#ff9c00] text-white rounded-md text-xs font-semibold">Validar UPCs</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-4 px-4 text-center text-gray-500">No se encontraron pedidos con los filtros aplicados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $orders->appends(request()->query())->links() }}</div>
        </div>

        <div x-show="openModal" @keydown.escape.window="openModal = false" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4" style="display: none;">
            <div @click.away="openModal = false" class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-3xl"><form action="{{ route('customer-service.validation.store') }}" method="POST">@csrf<h3 class="text-xl font-bold text-[#2c3856] mb-4">Validar UPCs para SO: <span x-text="selectedOrder?.so_number"></span></h3><div class="max-h-[60vh] overflow-y-auto pr-4"><table class="min-w-full"><thead class="border-b sticky top-0 bg-white"><tr><th class="text-left py-2 px-3 text-sm font-semibold text-gray-600">SKU</th><th class="text-left py-2 px-3 text-sm font-semibold text-gray-600">Descripción</th><th class="text-left py-2 px-3 text-sm font-semibold text-gray-600">UPC</th></tr></thead><tbody><template x-if="selectedOrder"><template x-for="detail in selectedOrder.details" :key="detail.id"><tr class="border-b"><td class="py-2 px-3" x-text="detail.sku"></td><td class="py-2 px-3" x-text="detail.product?.description || 'N/A'"></td><td class="py-2 px-3"><input type="text" :name="`upcs[${detail.id}]`" :value="detail.upc?.upc || ''" placeholder="Ingresar UPC o dejar vacío para usar SKU" class="w-full rounded-md border-gray-300 shadow-sm text-sm"></td></tr></template></template></tbody></table></div><div class="mt-6 flex justify-end gap-4"><button type="button" @click="openModal = false" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button><button type="submit" class="px-4 py-2 bg-[#ff9c00] text-white rounded-md">Guardar Cambios</button></div></form></div>
        </div>
        <div id="importModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden"><div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md"><h3 class="text-xl font-bold text-[#2c3856] mb-4">Importar Archivo de UPCs</h3><form action="{{ route('customer-service.validation.importCsv') }}" method="POST" enctype="multipart/form-data">@csrf<input type="file" name="csv_file" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"><div class="mt-6 flex justify-end gap-4"><button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button><button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-md">Procesar Archivo</button></div></form></div></div>
        <div id="specificationsModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4 hidden"><div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col"><h3 class="text-xl font-bold text-[#2c3856] mb-4">Gestionar Especificaciones de Entrega</h3><div id="spec-error-alert" class="hidden bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"></div><div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 pb-4 border-b"><input type="text" id="customer_search" placeholder="Buscar por nombre..." class="rounded-md border-gray-300 shadow-sm"><select id="channel_filter" class="rounded-md border-gray-300 shadow-sm"><option value="">Todos los Canales</option>@foreach($channels as $channel)<option value="{{ $channel }}">{{ $channel }}</option>@endforeach</select><button id="search_customer_btn" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm">Buscar Cliente</button></div><div class="flex-grow overflow-y-auto"><form id="specifications_form" class="space-y-4">@csrf<div><label for="customer_select_list" class="font-semibold text-gray-700">Selecciona un Cliente:</label><select id="customer_select_list" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" disabled><option>Primero busca un cliente</option></select></div><div id="specifications_container" class="hidden space-y-6"></div></form></div><div class="mt-6 flex justify-end gap-4 pt-4 border-t"><button type="button" id="close-spec-modal" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button><button type="submit" form="specifications_form" id="save-spec-button" class="px-4 py-2 bg-indigo-600 text-white rounded-md" disabled>Guardar</button></div></div></div>
    </div>


    <script>
        @if (session('success'))
            sessionStorage.setItem('validation_flash_success', '{{ session('success') }}');
        @endif
        @if (session('error'))
            sessionStorage.setItem('validation_flash_error', '{{ session('error') }}');
        @endif

        document.addEventListener('DOMContentLoaded', function () {
            const specModal = document.getElementById('specificationsModal');
            const openBtn = document.getElementById('open-spec-modal');
            const closeBtn = document.getElementById('close-spec-modal');
            const searchBtn = document.getElementById('search_customer_btn');
            const customerSelect = document.getElementById('customer_select_list');
            const specContainer = document.getElementById('specifications_container');
            const specForm = document.getElementById('specifications_form');
            const saveBtn = document.getElementById('save-spec-button');
            const errorAlert = document.getElementById('spec-error-alert');
            const successAlert = document.getElementById('flash-success');

            const specifications = {
                'Entrega': ['REVISION DE UPC VS FACTURA', 'DISTRIBUCION POR TIENDA', 'RE-ETIQUETADO', 'COLOCACION DE SENSOR', 'PREPARADO ESPECIAL', 'TIPO DE UNIDAD ACEPTADA', 'EQUIPO DE SEGURIDAD', 'REGISTO PATRONAL (SUA)', 'ENTREGA CON OTROS PEDIDOS', 'INSUMOS Y HERRAMIENTAS', 'MANIOBRA', 'IDENTIFICACIONES PARA ACCESO', 'ETIQUETA DE FRAGIL'],
                'Documentación': ['FACTURA', 'DO', 'CARTA MANIOBRA', 'CARTA PODER', 'ORDEN DE COMPRA', 'CARTA CONFIANZA', 'CONFIRMACIÓN DE CITA', 'CARTA CAJA CERRADA', 'CONFIRMACION DE FACTURAS', 'CARATULA DE ENTREGA', 'PASE VEHICULAR']
            };

            const toggleModal = (show) => specModal.classList.toggle('hidden', !show);
            openBtn.addEventListener('click', () => toggleModal(true));
            closeBtn.addEventListener('click', () => toggleModal(false));

            searchBtn.addEventListener('click', async () => {
                const search = document.getElementById('customer_search').value;
                const channel = document.getElementById('channel_filter').value;
                searchBtn.disabled = true; searchBtn.textContent = 'Buscando...';
                try {
                    const response = await fetch(`{{ route('customer-service.customers.search') }}?search=${search}&channel=${channel}`);
                    if (!response.ok) throw new Error('Error en la red al buscar clientes.');
                    const customers = await response.json();
                    customerSelect.innerHTML = '<option value="">-- Selecciona un cliente --</option>';
                    if (customers.length > 0) {
                        customers.forEach(c => customerSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`);
                        customerSelect.disabled = false;
                    } else {
                        customerSelect.innerHTML = '<option>No se encontraron clientes</option>';
                        customerSelect.disabled = true;
                    }
                } catch (error) {
                    errorAlert.textContent = error.message; errorAlert.classList.remove('hidden');
                } finally {
                    searchBtn.disabled = false; searchBtn.textContent = 'Buscar Cliente';
                }
            });

            customerSelect.addEventListener('change', async (e) => {
                const customerId = e.target.value;
                specContainer.classList.add('hidden'); specContainer.innerHTML = ''; saveBtn.disabled = true;
                if (!customerId) return;
                try {
                    const response = await fetch(`{{ url('customer-service/customers') }}/${customerId}/specifications`);
                    if (!response.ok) throw new Error('No se pudieron cargar las especificaciones.');
                    const savedSpecs = await response.json();
                    let html = '';
                    for (const category in specifications) {
                        html += `<div class="p-4 border rounded-md"><h4 class="text-md font-semibold text-gray-800 mb-3">${category}</h4><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">`;
                        specifications[category].forEach(spec => {
                            const specFullName = `${spec} - ${category}`;
                            const isChecked = savedSpecs[specFullName] || false;
                            html += `<label class="flex items-center text-sm"><input type="checkbox" name="${specFullName}" ${isChecked ? 'checked' : ''} class="rounded text-indigo-600"><span class="ml-2 text-gray-700">${spec}</span></label>`;
                        });
                        html += `</div></div>`;
                    }
                    specContainer.innerHTML = html;
                    specContainer.classList.remove('hidden');
                    saveBtn.disabled = false;
                } catch (error) {
                    errorAlert.textContent = error.message; errorAlert.classList.remove('hidden');
                }
            });

            specForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const customerId = customerSelect.value;
                if (!customerId) { errorAlert.textContent = 'Selecciona un cliente.'; errorAlert.classList.remove('hidden'); return; }
                saveBtn.disabled = true; saveBtn.textContent = 'Guardando...';
                const formData = new FormData(specForm);
                const specsData = {};
                for (const category in specifications) {
                    specifications[category].forEach(spec => {
                        const specFullName = `${spec} - ${category}`;
                        specsData[specFullName] = formData.has(specFullName);
                    });
                }
                try {
                    const response = await fetch(`{{ url('customer-service/customers') }}/${customerId}/specifications`, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('form#specifications_form input[name=_token]').value},
                        body: JSON.stringify({ specifications: specsData })
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) throw new Error(result.message || 'Ocurrió un error al guardar.');
                    
                    sessionStorage.setItem('validation_flash_success', result.message);
                    window.location.reload();
                    
                } catch (error) {
                    errorAlert.textContent = error.message; errorAlert.classList.remove('hidden');
                } finally {
                    saveBtn.disabled = false; saveBtn.textContent = 'Guardar Cambios';
                }
            });
        });
    </script>


</x-app-layout>