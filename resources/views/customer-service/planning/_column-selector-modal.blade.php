<div x-show="isColumnModalOpen" @keydown.escape.window="isColumnModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
    <div @click.outside="isColumnModalOpen = false" class="bg-white rounded-lg shadow-xl p-8 w-full max-w-2xl">
        <h3 class="text-xl font-bold text-[#2c3856] mb-4">Seleccionar Columnas Visibles</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <template x-for="(label, key) in allColumns" :key="key">
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" x-model="visibleColumns[key]" class="rounded">
                        <span class="ml-2" x-text="label"></span>
                    </label>
                </div>
            </template>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="button" @click="isColumnModalOpen = false" class="px-4 py-2 bg-blue-600 text-white rounded-md">Cerrar</button>
        </div>
    </div>
</div>