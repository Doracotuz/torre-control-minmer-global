<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Productos') }}
            </h2>
            <div class="flex items-center gap-4">
                <a href="{{ route('customer-service.products.dashboard') }}" class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-teal-800">
                    Ver Dashboard
                </a>
                <!-- <button @click="isImportModalOpen = true" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700">
                    Importar CSV
                </button> -->
                <a href="{{ route('customer-service.products.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-green-700">
                    Añadir Producto
                </a>
                <a href="{{ route('customer-service.brands.index') }}" class="px-4 py-2 bg-[#FF9C00] text-white rounded-md text-sm font-semibold shadow-sm hover:bg-orange-600">
                    Gestionar Marcas
                </a>                
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="productManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label for="search" class="text-sm font-medium text-gray-700">Buscar por SKU o Descripción</label>
                        <input type="text" id="search" x-model.debounce.300ms="filters.search" placeholder="Escribe 2+ caracteres..." class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label for="brand_id" class="text-sm font-medium text-gray-700">Marca</label>
                        <select id="brand_id" x-model="filters.brand_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Todas las Marcas</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="type" class="text-sm font-medium text-gray-700">Tipo</label>
                        <select id="type" x-model="filters.type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Todos los Tipos</option>
                            <option value="Producto">Producto</option>
                            <option value="Promocional">Promocional</option>
                        </select>
                    </div>
                    <div>
                        <a :href="generateExportUrl()" class="w-full justify-center inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-gray-700">
                            Exportar Vista
                        </a>
                    </div>
                    <button @click="isImportModalOpen = true" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold shadow-sm hover:bg-blue-700">
                        Importar CSV
                    </button>                    
                </div>
            </div>

            <div id="products-table-container">
                @include('customer-service.products.partials.table', ['products' => $products])
            </div>
        </div>
        
        <div x-show="isImportModalOpen" @keydown.escape.window="isImportModalOpen = false" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
            <div @click.outside="isImportModalOpen = false" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
                <h3 class="text-xl font-bold text-[#2c3856] mb-4">Importar Productos desde CSV</h3>
                <p class="text-sm text-gray-600 mb-4">Las marcas deben existir previamente en el sistema.</p>
                <a href="{{ route('customer-service.products.template') }}" class="text-sm text-blue-600 font-semibold hover:underline mb-4 block">Descargar plantilla de ejemplo</a>
                <form action="{{ route('customer-service.products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="csv_file" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <div class="mt-6 flex justify-end gap-4">
                        <button type="button" @click="isImportModalOpen = false" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Importar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        
    document.addEventListener('alpine:init', () => {
        Alpine.data('productManager', () => ({
            isLoading: false,
            isImportModalOpen: false,
            filters: { search: '', brand_id: '', type: '', page: 1 },
            init() {
                this.$watch('filters', (newValue, oldValue) => {
                    if (newValue.page === oldValue.page) { this.filters.page = 1; }
                    this.applyFilters();
                });
            },
            applyFilters() {
                this.isLoading = true;
                const params = new URLSearchParams(this.filters);
                fetch(`{{ route('customer-service.products.filter') }}?${params.toString()}`)
                    .then(response => response.json()).then(data => {
                        document.getElementById('products-table-container').innerHTML = data.table;
                        this.isLoading = false;
                    });
            },
            changePage(page) { if (page) { this.filters.page = page; } },
            generateExportUrl() {
                const params = new URLSearchParams(this.filters);
                params.delete('page'); 
                return `{{ route('customer-service.products.export') }}?${params.toString()}`;
            }
        }));
    });
    document.addEventListener('click', function(event) {
        const link = event.target.closest('#products-table-container .pagination a');
        if (link) {
            event.preventDefault();
            const page = new URL(link.href).searchParams.get('page');
            document.querySelector('[x-data]').__x.$data.changePage(page);
        }
    });
    </script>
</x-app-layout>
