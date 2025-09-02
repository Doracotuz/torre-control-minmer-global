<div x-show="isAddToGuiaModalOpen" @keydown.escape.window="isAddToGuiaModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" style="display: none;">
    <div @click.outside="isAddToGuiaModalOpen = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <h3 class="text-xl font-bold text-[#2c3856] mb-4">Añadir a Guía Existente</h3>

        <form action="/rutas/asignaciones/add-orders-to-guia" method="POST">
            @csrf
            
            <template x-for="id in selectedPlannings" :key="id">
                <input type="hidden" name="planning_ids[]" :value="id">
            </template>
            <div>
                <label for="guia_search" class="block text-sm font-medium text-gray-700">Buscar Guía (por número, operador o placas)</label>
                <input type="text" id="guia_search" x-model="guiaSearch" @input.debounce.300ms="searchGuias()" class="mt-1 block w-full rounded-md border-gray-300" placeholder="Escribe para buscar...">
            </div>

            <div class="mt-4 max-h-60 overflow-y-auto border rounded-lg">
                <template x-for="guia in guiaSearchResults" :key="guia.id">
                    <label class="flex items-center p-3 hover:bg-gray-100 border-b cursor-pointer">
                        <input type="radio" name="guia_id" :value="guia.id" class="rounded-full text-[#ff9c00] focus:ring-[#ff9c00]" required>
                        <div class="ml-3 text-sm">
                            <p class="font-semibold" x-text="guia.guia"></p>
                            <p class="text-gray-600" x-text="`${guia.operador} - ${guia.placas}`"></p>
                        </div>
                    </label>
                </template>
                <template x-if="guiaSearch.length > 1 && guiaSearchResults.length === 0">
                    <p class="p-3 text-sm text-gray-500">No se encontraron guías.</p>
                </template>
            </div>

            <div class="mt-6 flex justify-end gap-4">
                <button type="button" @click="isAddToGuiaModalOpen = false" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md">Añadir a Guía</button>
            </div>
        </form>
    </div>
</div>