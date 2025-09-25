<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 items-end">
    <div class="lg:col-span-2">
        <label for="search" class="block text-sm font-medium text-gray-700">Búsqueda Rápida</label>
        <div class="flex items-center">
            <input type="text" x-model.debounce.300ms="filters.search" id="search" placeholder="SO, Factura, Cliente..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            <button @click="clearBasicFilters()" class="ml-2 mt-1 p-2 bg-gray-200 text-gray-600 hover:bg-gray-300 rounded-md" title="Limpiar todos los filtros">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>

    <div>
        <label for="date_created_from" class="block text-sm font-medium text-gray-700">Creado Desde</label>
        <input type="date" x-model="filters.date_created_from" id="date_created_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="date_created_to" class="block text-sm font-medium text-gray-700">Creado Hasta</label>
        <input type="date" x-model="filters.date_created_to" id="date_created_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>    

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Estatus</label>
        <select x-model="filters.status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">Todos</option>
            <option value="En Espera">En Espera</option>
            <option value="Programada">Programada</option>
            <option value="Asignado en Guía">Asignado en Guía</option>
            <option value="Cancelado">Cancelado</option>
        </select>
    </div>

    <div>
        <label for="origen" class="block text-sm font-medium text-gray-700">Origen</label>
        <select x-model="filters.origen" id="origen" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">Todos</option>
            <template x-for="warehouse in warehouses" :key="warehouse.name">
                <option :value="warehouse.name" x-text="warehouse.name"></option>
            </template>
        </select>
    </div>
    
    <div>
        <label for="destino" class="block text-sm font-medium text-gray-700">Destino</label>
        <select x-model="filters.destino" id="destino" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">Todos</option>
            <template x-for="warehouse in warehouses" :key="warehouse.name">
                <option :value="warehouse.name" x-text="warehouse.name"></option>
            </template>
        </select>
    </div>
</div>