<?php
// Archivo: resources/views/tms/partials/modal-import.blade.php

// Modal de Importación con Alpine.js
?>
<div x-show="isImportModalOpen" @keydown.escape.window="isImportModalOpen = false" class="fixed inset-0 z-30 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="isImportModalOpen" @click="isImportModalOpen = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="isImportModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('tms.importShipments') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Importar Embarques desde CSV</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Selecciona un archivo CSV para importar los datos.</p>
                        <div class="mt-4">
                            <label for="csv_file" class="block text-sm font-medium text-gray-700">Archivo CSV</label>
                            <input class="w-full mt-1 form-input" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2c3856] text-base font-medium text-white hover:bg-[#ff9c00] sm:ml-3 sm:w-auto sm:text-sm">Importar</button>
                    <button type="button" @click="isImportModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>