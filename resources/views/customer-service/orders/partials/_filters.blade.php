<div class="grid grid-cols-1 md:grid-cols-6 gap-4">
    {{-- Filtros existentes --}}
    <div class="md:col-span-2">
        <label for="search" class="block text-sm font-medium text-gray-700">Búsqueda Rápida</label>
        <input type="text" x-model.debounce.300ms="filters.search" id="search" placeholder="Buscar por SO, OC, Cliente..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Estatus</label>
        <select x-model="filters.status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">Todos</option>
            <option value="Pendiente">Pendiente</option>
            <option value="En Planificación">En Planificación</option>
            <option value="En Planificación">Terminado</option>
            <option value="Cancelado">Cancelado</option>
        </select>
    </div>
    <div>
        <label for="date_from" class="block text-sm font-medium text-gray-700">Fecha Desde</label>
        <input type="date" x-model="filters.date_from" id="date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>
    <div>
        <label for="date_to" class="block text-sm font-medium text-gray-700">Fecha Hasta</label>
        <input type="date" x-model="filters.date_to" id="date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    {{-- Nuevo Botón de Filtros Avanzados --}}
    <div class="flex items-end">
        <button @click="isAdvancedFilterModalOpen = true" class="w-full h-10 px-4 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-semibold shadow-sm hover:bg-gray-50 flex items-center justify-center">
            <i class="fas fa-filter mr-2"></i>
            Avanzados
            <span x-show="advancedFilterCount > 0" x-text="`(${advancedFilterCount})`" class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs"></span>
        </button>
    </div>
</div>