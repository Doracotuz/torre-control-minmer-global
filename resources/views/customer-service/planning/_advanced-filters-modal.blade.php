<div x-show="isAdvancedFilterModalOpen" @keydown.escape.window="isAdvancedFilterModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
    <div @click.outside="isAdvancedFilterModalOpen = false" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-3xl">
        <h3 class="text-xl font-bold text-[#2c3856] mb-6">Filtros Avanzados</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            
            {{-- Columna 1 --}}
            <div class="space-y-4">
                <div>
                    <label class="block font-medium text-gray-700"># Guía</label>
                    <input type="text" x-model.lazy="filters.guia_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Fecha de Carga</label>
                    <input type="date" x-model.lazy="filters.fecha_carga_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Razón Social</label>
                    <input type="text" x-model.lazy="filters.razon_social_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            {{-- Columna 2 --}}
            <div class="space-y-4">
                <div>
                    <label class="block font-medium text-gray-700">Factura</label>
                    <input type="text" x-model.lazy="filters.factura_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Origen</label>
                    <select x-model="filters.origen_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos</option>
                        <template x-for="warehouse in warehouses" :key="warehouse.id">
                            <option :value="warehouse.name" x-text="warehouse.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Destino</label>
                     <select x-model="filters.destino_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos</option>
                        <template x-for="warehouse in warehouses" :key="warehouse.id">
                            <option :value="warehouse.name" x-text="warehouse.name"></option>
                        </template>
                    </select>
                </div>
            </div>

            {{-- Columna 3 --}}
            <div class="space-y-4">
                <div>
                    <label class="block font-medium text-gray-700">Transporte</label>
                    <input type="text" x-model.lazy="filters.transporte_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Operador</label>
                    <input type="text" x-model.lazy="filters.operador_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                 <div>
                    <label class="block font-medium text-gray-700">Tipo de Ruta</label>
                    <select x-model="filters.tipo_ruta_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos</option>
                        <option value="Consolidado">Consolidado</option>
                        <option value="Dedicado">Dedicado</option>
                        <option value="Directo">Directo</option>
                    </select>
                </div>
            </div>

        </div>

        <div class="mt-8 flex justify-end gap-4">
            <button type="button" @click="resetAdvancedFilters()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md">Limpiar Filtros</button>
            <button type="button" @click="applyAdvancedFilters()" class="px-4 py-2 bg-teal-600 text-white rounded-md">Aplicar Filtros</button>
        </div>
    </div>
</div>