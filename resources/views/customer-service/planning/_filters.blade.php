<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="md:col-span-1">
        <label for="search" class="block text-sm font-medium text-gray-700">Búsqueda Rápida</label>
        <input type="text" x-model.debounce.300ms="filters.search" id="search" placeholder="Buscar por SO, Factura, Cliente..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
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
        <label for="date_from" class="block text-sm font-medium text-gray-700">Fecha Entrega Desde</label>
        <input type="date" x-model="filters.date_from" id="date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>
    <div>
        <label for="date_to" class="block text-sm font-medium text-gray-700">Fecha Entrega Hasta</label>
        <input type="date" x-model="filters.date_to" id="date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>
</div>