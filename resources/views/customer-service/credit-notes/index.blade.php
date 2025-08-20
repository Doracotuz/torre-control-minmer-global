<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Notas de Crédito</h2>
            <div class="flex space-x-2">
                <a href="{{ route('customer-service.credit-notes.dashboard') }}" class="px-4 py-2 bg-[#2c3856] text-white rounded-md text-sm font-semibold shadow-sm hover:bg-[#1a2333]">Dashboard</a>
                <a href="{{ route('customer-service.credit-notes.export.csv') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">Exportar a CSV</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="creditNoteManager()">
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

            <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
                <form @submit.prevent="" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Búsqueda General</label>
                        <div class="relative mt-1">
                            <input x-model.debounce.500ms="filters.search" type="text" name="search" id="search" placeholder="No. NC, Factura, SO..." class="w-full pl-10 pr-4 py-2 rounded-md border border-gray-300 focus:ring focus:ring-blue-200">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Fecha Desde</label>
                        <input x-model="filters.start_date" type="date" name="start_date" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Fecha Hasta</label>
                        <input x-model="filters.end_date" type="date" name="end_date" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="request_type" class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select x-model="filters.request_type" name="request_type" id="request_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todos</option>
                            @foreach($requestTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="warehouse_id" class="block text-sm font-medium text-gray-700">Almacén</label>
                        <select x-model="filters.warehouse_id" name="warehouse_id" id="warehouse_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todos</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-gray-700">Cliente</label>
                        <select x-model="filters.customer_name" name="customer_name" id="customer_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Todos</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer }}">{{ $customer }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. NC</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. de SO</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Solicitud</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Captura</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" x-html="tableRows"></tbody>
                    </table>
                </div>

                <div class="mt-4" x-html="paginationLinks"></div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function creditNoteManager() {
        return {
            filters: {
                search: '{{ request('search') }}',
                start_date: '{{ request('start_date') }}',
                end_date: '{{ request('end_date') }}',
                request_type: '{{ request('request_type') }}',
                warehouse_id: '{{ request('warehouse_id') }}',
                customer_name: '{{ request('customer_name') }}',
                page: 1,
            },
            tableRows: '',
            paginationLinks: '',
            paginationData: {
                from: null,
                to: null,
                total: null,
            },

            init() {
                this.applyFilters();
                this.$watch('filters', (newVal, oldVal) => {
                    // Evita disparar la solicitud si solo cambia el número de página
                    if (newVal.page === oldVal.page) {
                        this.applyFilters(1); // Reinicia a la página 1 al cambiar un filtro
                    }
                });
            },

            applyFilters(page = this.filters.page) {
                this.filters.page = page;
                const params = new URLSearchParams(this.filters);
                const url = `{{ route('customer-service.credit-notes.index') }}?${params.toString()}`;

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.json())
                    .then(data => {
                        this.renderTable(data.data);
                        this.renderPagination(data.links);
                        this.paginationData = {
                            from: data.from,
                            to: data.to,
                            total: data.total
                        };
                    })
                    .catch(error => console.error('Error fetching data:', error));
            },

            renderTable(notes) {
                let html = '';
                if (notes.length === 0) {
                    html = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No se encontraron notas de crédito.</td></tr>';
                } else {
                    notes.forEach(note => {
                        const editUrl = `{{ url('customer-service/credit-notes') }}/${note.id}/edit`;
                        const deleteUrl = `{{ url('customer-service/credit-notes') }}/${note.id}`;

                        const soNumber = note.order ? note.order.so_number : 'N/A';
                        const creditNoteNumber = note.credit_note ? note.credit_note : 'N/A';
                        const invoiceNumber = note.invoice ? note.invoice : 'N/A';
                        const customerName = note.customer_name ? note.customer_name : 'N/A';
                        const captureDate = new Date(note.capture_date).toLocaleDateString('es-MX');

                        html += `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">${creditNoteNumber}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${soNumber}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${invoiceNumber}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${customerName}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${note.request_type}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${captureDate}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="${editUrl}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                    <form action="${deleteUrl}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta nota de crédito?');" class="inline-block ml-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        `;
                    });
                }
                this.tableRows = html;
            },

            renderPagination(links) {
                let html = '<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">';
                
                // Muestra la leyenda si hay resultados
                if (links.from !== null) {
                    html += `<div><p class="text-sm text-gray-700 leading-5">Mostrando del <span class="font-medium">${links.from}</span> al <span class="font-medium">${links.to}</span> de <span class="font-medium">${links.total}</span> resultados</p></div>`;
                }

                // Genera los botones de paginación
                if (links.data.length > 0) {
                    html += '<div><span class="relative z-0 inline-flex shadow-sm rounded-md">';
                    
                    links.forEach(link => {
                        const pageNumber = link.url ? new URL(link.url).searchParams.get("page") : null;
                        
                        if (link.url === null) {
                            html += `<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">${link.label}</span>`;
                        } else if (link.active) {
                            html += `<span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-blue-600 border border-blue-600 leading-5 rounded-md" aria-current="page">${link.label}</span>`;
                        } else {
                            html += `<a href="${link.url}" @click.prevent="applyFilters(${pageNumber})" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:z-10 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">${link.label}</a>`;
                        }
                    });
                    
                    html += '</span></div>';
                }

                html += '</nav>';
                this.paginationLinks = html;
            },
            
            resetFilters() {
                this.filters = {
                    search: '',
                    start_date: '',
                    end_date: '',
                    request_type: '',
                    warehouse_id: '',
                    customer_name: '',
                    page: 1,
                };
            }
        };
    }
</script>