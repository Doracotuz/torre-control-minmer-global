@extends('layouts.app')

@section('content')
<style>
    /* Estilos personalizados mínimos que no entran en conflicto con Tailwind */
    .status-badge {
        padding: 0.35em 0.65em;
        font-size: .75em;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: .25rem;
    }
    .status-por-asignar { background-color: #6c757d; } /* Gris */
    .status-transito { background-color: #ff9c00; } /* Naranja */
    .status-entregado { background-color: #198754; } /* Verde */
    .status-revisar { background-color: #dc3545; } /* Rojo */
    .status-cancelado { background-color: #2b2b2b; } /* Negro */
</style>

<div class="container mx-auto px-4" x-data="shipmentAssigner()">
    <!-- Cabecera -->
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800">Asignar Rutas</h1>
        <div class="flex items-center space-x-2">
            <button @click="isManualModalOpen = true" class="bg-[#ff9c00] text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-opacity-80 transition-colors duration-300 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Añadir Manualmente
            </button>
            <button @click="isImportModalOpen = true" class="bg-[#2c3856] text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-opacity-80 transition-colors duration-300 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Importar desde CSV
            </button>
            <a href="{{ route('tms.exportShipments') }}" class="bg-green-600 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-green-700 transition-colors duration-300 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Exportar a CSV
            </a>
        </div>
    </div>

    <!-- Notificaciones -->
    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p>{{ session('success') }}</p></div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>{{ session('error') }}</p></div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Por favor, corrige los siguientes errores:</p>
            <ul>@foreach ($errors->all() as $error)<li class="list-disc ml-4">{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <!-- Tabla de Embarques -->
    <div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-[#2c3856] text-white">
            <h2 class="text-lg font-bold">Listado de Embarques</h2>
        </div>
        <div class="p-6">
            <!-- Filtros y Búsqueda -->
            <div class="mb-4">
                <input type="text" x-model="searchTerm" @keyup.debounce="filterRows" class="w-full md:w-1/3 form-input rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Buscar por guía, factura, operador...">
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap" id="shipmentsTable">
                    <thead class="bg-gray-50">
                        <tr class="text-left font-bold">
                            <th class="px-6 py-3 w-12"><input type="checkbox" @change="toggleSelectAll($event)"></th>
                            <th class="px-6 py-3">Guía</th>
                            <th class="px-6 py-3">Tipo</th>
                            <th class="px-6 py-3">Facturas</th>
                            <th class="px-6 py-3">Origen</th>
                            <th class="px-6 py-3">Destino</th>
                            <th class="px-6 py-3">Operador / Placas</th>
                            <th class="px-6 py-3">Estatus</th>
                            <th class="px-6 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($shipments as $shipment)
                            <tr class="hover:bg-gray-50 shipment-row" data-status="{{ $shipment->status }}">
                                <td class="px-6 py-4">
                                    @if($shipment->status === 'Por asignar')
                                        <input type="checkbox" x-model="selectedShipments" value="{{ $shipment->id }}" class="form-checkbox h-5 w-5 text-indigo-600 shipment-checkbox">
                                    @endif
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $shipment->guide_number }}</td>
                                <td class="px-6 py-4">{{ $shipment->type }}</td>
                                <td class="px-6 py-4">
                                    <ul class="list-disc list-inside text-sm">
                                        @foreach($shipment->invoices as $invoice)
                                            <li>{{ $invoice->invoice_number }} ({{ $invoice->box_quantity }} cajas)</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-6 py-4">{{ $shipment->origin }}</td>
                                <td class="px-6 py-4">{{ $shipment->destination_type }}</td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold">{{ $shipment->operator ?? 'N/A' }}</p>
                                    <p class="text-sm text-gray-500">{{ $shipment->license_plate ?? 'N/A' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="status-badge status-{{ Str::slug($shipment->status) }}">{{ $shipment->status }}</span>
                                </td>
                                <td class="px-6 py-4 flex items-center space-x-2">
                                    <button @click='openEditModal({{ $shipment->load("invoices") }})' class="text-indigo-600 hover:text-indigo-900 font-semibold text-sm">Editar</button>
                                    @if($shipment->status === 'Por asignar')
                                    <form action="{{ route('tms.shipments.destroy', $shipment) }}" method="POST" @submit.prevent="confirmDelete($el)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-semibold text-sm">Eliminar</button>
                                    </form>
                                    @endif
                                </td>                               
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-10 text-gray-500">No hay embarques registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sección de Asignación Fija -->
    <div class="sticky bottom-0 left-0 right-0 bg-white p-4 border-t-2 border-[#ff9c00] shadow-2xl mt-8" x-show="selectedShipments.length > 0" x-transition>
        <div class="flex flex-wrap items-center justify-between max-w-7xl mx-auto">
            <p class="text-lg font-semibold"><span x-text="selectedShipments.length"></span> embarques seleccionados</p>
            <div class="flex items-center space-x-4 mt-2 sm:mt-0">
                <select x-model="selectedRouteId" class="form-select rounded-md shadow-sm border-gray-300">
                    <option value="">-- Elige una ruta --</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}">{{ $route->name }} ({{ $route->status }})</option>
                    @endforeach
                </select>
                <button @click="assignSelected" class="bg-green-500 text-white font-bold py-2 px-6 rounded-lg shadow-md hover:bg-green-600 transition-colors duration-300">Asignar</button>
            </div>
        </div>
    </div>

    <!-- Modales -->
    @include('tms.partials.modal-import')
    @include('tms.partials.modal-manual-entry')

</div>

<script>
    function manualEntryForm() {
        return {
            type: 'Entrega',
            invoices: [{ invoice_number: '', box_quantity: '', bottle_quantity: '' }],
            addInvoice() {
                this.invoices.push({ invoice_number: '', box_quantity: '', bottle_quantity: '' });
            },
            removeInvoice(index) {
                this.invoices.splice(index, 1);
            }
        }
    }

    function shipmentAssigner() {
        return {
            isImportModalOpen: false,
            isManualModalOpen: false,
            searchTerm: '',
            selectedShipments: [],
            selectedRouteId: '{{ $selectedRouteId ?? '' }}',
            isEditing: false,
            editFormAction: '',
            shipmentData: {
                type: 'Entrega',
                guide_number: '',
                so_number: '',
                pedimento: '',
                origin: 'MEX',
                destination_type: 'Cliente Final',
                operator: '',
                license_plate: '',
                invoices: [{ invoice_number: '', box_quantity: '', bottle_quantity: '' }]
            },

            addInvoice() {
                this.shipmentData.invoices.push({ invoice_number: '', box_quantity: '', bottle_quantity: '' });
            },
            removeInvoice(index) {
                this.shipmentData.invoices.splice(index, 1);
            },
            
            openNewModal() {
                this.isEditing = false;
                this.editFormAction = '{{ route("tms.storeShipment") }}';
                this.shipmentData = { type: 'Entrega', invoices: [{ invoice_number: '', box_quantity: '', bottle_quantity: '' }] };
                this.isManualModalOpen = true;
            },

            openEditModal(shipment) {
                this.isEditing = true;
                this.editFormAction = `/tms/shipments/${shipment.id}`;
                this.shipmentData = {
                    type: shipment.type,
                    guide_number: shipment.guide_number,
                    so_number: shipment.so_number,
                    pedimento: shipment.pedimento,
                    origin: shipment.origin,
                    destination_type: shipment.destination_type,
                    operator: shipment.operator,
                    license_plate: shipment.license_plate,
                    invoices: shipment.invoices.length > 0 ? shipment.invoices : [{ invoice_number: '', box_quantity: '', bottle_quantity: '' }]
                };
                this.isManualModalOpen = true;
            },            
            
            init() {
                // Si hay errores de validación de Laravel, abre el modal de registro manual automáticamente
                @if ($errors->any())
                    this.isManualModalOpen = true;
                @endif
            },

            toggleSelectAll(event) {
                let checkboxes = document.querySelectorAll('.shipment-checkbox');
                this.selectedShipments = event.target.checked ? Array.from(checkboxes).map(cb => cb.value) : [];
            },

            filterRows() {
                let term = this.searchTerm.toLowerCase();
                document.querySelectorAll('#shipmentsTable tbody tr').forEach(row => {
                    row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
                });
            },

            assignSelected() {
                if (this.selectedShipments.length === 0) {
                    alert('Por favor, selecciona al menos un embarque.');
                    return;
                }
                if (!this.selectedRouteId) {
                    alert('Por favor, selecciona una ruta a la cual asignar los embarques.');
                    return;
                }

                fetch('{{ route("tms.assignShipmentsToRoute") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        route_id: this.selectedRouteId,
                        shipment_ids: this.selectedShipments
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error de conexión al intentar asignar los embarques.');
                });
            },

            confirmDelete(form) {
                if (confirm('¿Estás seguro de que quieres eliminar este embarque? Esta acción no se puede deshacer.')) {
                    form.submit();
                }
            },

        }
    }
</script>
@endsection
