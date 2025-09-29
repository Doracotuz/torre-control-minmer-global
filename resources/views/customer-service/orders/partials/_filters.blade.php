<div class="grid grid-cols-1 md:grid-cols-9 gap-4">
    
    <div class="md:col-span-2">
        <label for="search" class="block text-sm font-medium text-gray-700">Búsqueda Rápida</label>
        <input type="text" x-model.debounce.300ms="filters.search" id="search" placeholder="Buscar por SO, OC, Cliente..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div x-data="{ open: false }" class="relative">
        <label for="status" class="block text-sm font-medium text-gray-700">Estatus</label>
        <button @click="open = !open" type="button" class="mt-1 relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <span class="block truncate" x-text="filters.status.length > 0 ? filters.status.join(', ') : 'Todos'"></span>
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </span>
        </button>
        <div x-show="open" @click.away="open = false" x-transition class="absolute mt-1 w-full rounded-md bg-white shadow-lg z-10 border border-gray-300">
            <ul class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                @foreach(['Pendiente', 'En Planificación', 'Terminado', 'Cancelado'] as $statusOption)
                    <li class="text-gray-900 cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-gray-100">
                        <label class="flex items-center space-x-3 w-full">
                            <input type="checkbox" x-model="filters.status" value="{{ $statusOption }}" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="font-normal">{{ $statusOption }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    
    <div>
        <label for="date_from" class="block text-sm font-medium text-gray-700">Fecha Desde</label>
        <input type="date" x-model="filters.date_from" id="date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="date_to" class="block text-sm font-medium text-gray-700">Fecha Hasta</label>
        <input type="date" x-model="filters.date_to" id="date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
    </div>

    <div>
        <label for="has_delivery_date" class="block text-sm font-medium text-gray-700">Entrega</label>
        <select x-model="filters.has_delivery_date" id="has_delivery_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">Todos</option>
            <option value="yes">Sí</option>
            <option value="no">No</option>
        </select>
    </div>

    <div>
        <label for="has_invoice" class="block text-sm font-medium text-gray-700">Evidencia</label>
        <select x-model="filters.has_invoice" id="has_invoice" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
            <option value="">Todos</option>
            <option value="yes">Sí</option>
            <option value="no">No</option>
        </select>
    </div>

    <div class="flex items-end gap-2 md:col-span-2">
        <button @click="clearFilters()" class="w-full h-10 px-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-semibold shadow-sm hover:bg-gray-50 flex items-center justify-center">
            <i class="fas fa-eraser mr-2"></i>
            Limpiar
        </button>        
        <button @click="isAdvancedFilterModalOpen = true" class="w-full h-10 px-4 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-semibold shadow-sm hover:bg-gray-50 flex items-center justify-center">
            <i class="fas fa-filter mr-2"></i>
            Avanzados
            <span x-show="advancedFilterCount > 0" x-text="`(${advancedFilterCount})`" class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs"></span>
        </button>
    </div>
</div>