<div x-show="isScalesModalOpen" @keydown.escape.window="isScalesModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
    <div @click.outside="closeScalesModal()" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl">
        <h3 class="text-xl font-bold text-[#2c3856] mb-4">Añadir Escalas para SO: <span x-text="selectedPlanning.so_number"></span></h3>
        
        <div x-show="scales.length === 0">
            <label for="scales_count" class="block text-sm font-medium text-gray-700">¿Cuántas escalas se harán?</label>
            <div class="flex items-center gap-4 mt-2">
                <input type="number" id="scales_count" x-model.number="scalesCount" min="1" class="block w-full rounded-md border-gray-300 shadow-sm">
                <button @click="generateScales()" class="px-4 py-2 bg-blue-600 text-white rounded-md">Generar</button>
            </div>
        </div>

        <div x-show="scales.length > 0" class="space-y-4 max-h-80 overflow-y-auto pr-2">
            <template x-for="(scale, index) in scales" :key="index">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border p-3 rounded-md bg-gray-50">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Escala <span x-text="index + 1"></span>: Origen</label>
                        <select x-model="scale.origen" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Seleccione un origen</option>
                            <template x-for="warehouse in warehouses" :key="warehouse.name">
                                <option :value="warehouse.name" x-text="warehouse.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Escala <span x-text="index + 1"></span>: Destino</label>
                        <select x-model="scale.destino" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                             <option value="">Seleccione un destino</option>
                            <template x-for="warehouse in warehouses" :key="warehouse.name">
                                <option :value="warehouse.name" x-text="warehouse.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-6 flex justify-end gap-4">
            <button type="button" @click="closeScalesModal()" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
            <button type="button" @click="saveScales()" x-show="scales.length > 0" class="px-4 py-2 bg-purple-600 text-white rounded-md">Guardar Escalas</button>
        </div>
    </div>
</div>