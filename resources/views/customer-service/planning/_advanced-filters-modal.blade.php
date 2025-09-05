<div x-show="isAdvancedFilterModalOpen" @keydown.escape.window="isAdvancedFilterModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
    <div @click.outside="isAdvancedFilterModalOpen = false" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-5xl">
        <h3 class="text-xl font-bold text-[#2c3856] mb-6">Filtros Avanzados</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-4 text-sm">
            
            {{-- Columna 1 --}}
            <div class="space-y-4">
                <div><label class="block font-medium text-gray-700"># Guía</label><input type="text" x-model.lazy="filters.guia_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block font-medium text-gray-700">SO</label><input type="text" x-model.lazy="filters.so_number_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block font-medium text-gray-700">Factura</label><input type="text" x-model.lazy="filters.factura_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block font-medium text-gray-700">Razón Social</label><input type="text" x-model.lazy="filters.razon_social_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block font-medium text-gray-700">Dirección</label><input type="text" x-model.lazy="filters.direccion_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
            </div>

            {{-- Columna 2 --}}
            <div class="space-y-4">
                <div><label class="block font-medium text-gray-700">Fecha de Entrega</label><input type="date" x-model.lazy="filters.fecha_entrega_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block font-medium text-gray-700">Fecha de Carga</label><input type="date" x-model.lazy="filters.fecha_carga_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block font-medium text-gray-700">Origen</label><select x-model="filters.origen_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Todos</option><template x-for="warehouse in warehouses" :key="warehouse.name"><option :value="warehouse.name" x-text="warehouse.name"></option></template></select></div>
                <div><label class="block font-medium text-gray-700">Destino</label><select x-model="filters.destino_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Todos</option><template x-for="warehouse in warehouses" :key="warehouse.name"><option :value="warehouse.name" x-text="warehouse.name"></option></template></select></div>
                <div><label class="block font-medium text-gray-700">Estado (Destino)</label><input type="text" x-model.lazy="filters.estado_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
            </div>

            {{-- Columna 3 --}}
            <div class="space-y-4">
                <div><label class="block font-medium text-gray-700">Transporte</label><input type="text" x-model.lazy="filters.transporte_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block font-medium text-gray-700">Operador</label><input type="text" x-model.lazy="filters.operador_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block font-medium text-gray-700">Placas</label><input type="text" x-model.lazy="filters.placas_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                 <div><label class="block font-medium text-gray-700">Tipo de Ruta</label><select x-model="filters.tipo_ruta_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Todos</option><option value="Consolidado">Consolidado</option><option value="Dedicado">Dedicado</option><option value="Directo">Directo</option></select></div>
                 <div><label class="block font-medium text-gray-700">Servicio</label><select x-model="filters.servicio_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Todos</option><option value="Local">Local</option><option value="Foraneo">Foraneo</option><option value="Ejecutivo">Ejecutivo</option></select></div>
            </div>

            {{-- Columna 4 --}}
            <div class="space-y-4">
                <div><label class="block font-medium text-gray-700">Canal</label><input type="text" x-model.lazy="filters.canal_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block font-medium text-gray-700">Custodia</label><select x-model="filters.custodia_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Todos</option><option value="Sepsa">Sepsa</option><option value="Planus">Planus</option><option value="Ninguna">Ninguna</option></select></div>
                <div><label class="block font-medium text-gray-700">¿Urgente?</label><select x-model="filters.urgente_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Todos</option><option value="Si">Si</option><option value="No">No</option></select></div>
                <div><label class="block font-medium text-gray-700">¿Devolución?</label><select x-model="filters.devolucion_adv" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Todos</option><option value="Si">Si</option><option value="No">No</option></select></div>
            </div>

        </div>

        <div class="mt-8 flex justify-end gap-4">
            <button type="button" @click="resetAdvancedFilters()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md">Limpiar Filtros</button>
            <button type="button" @click="applyAdvancedFilters()" class="px-4 py-2 bg-teal-600 text-white rounded-md">Aplicar Filtros</button>
        </div>
    </div>
</div>