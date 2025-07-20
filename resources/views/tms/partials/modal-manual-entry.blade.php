<?php
// Archivo: resources/views/tms/partials/modal-manual-entry.blade.php
// NOTA: Este archivo ya no contiene la etiqueta <script> para evitar conflictos.
// La lógica de Alpine.js ahora es manejada por la vista principal (assign-routes.blade.php).
?>
<div x-show="isManualModalOpen" @keydown.escape.window="isManualModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="manual-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="isManualModalOpen" @click="isManualModalOpen = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="isManualModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full" 
             x-data="manualEntryForm()">
            
            <form :action="editFormAction" method="POST">
                @csrf
                <template x-if="isEditing">
                    @method('PATCH')
                </template>

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="manual-modal-title" x-text="isEditing ? 'Editar Embarque' : 'Registrar Nuevo Embarque'"></h3>
                </div>

                <div class="px-4 sm:p-6 max-h-[60vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Campos del Embarque -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
                            <select name="type" x-model="shipmentData.type" class="mt-1 block w-full form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="Entrega">Entrega</option>
                                <option value="Importacion">Importación</option>
                            </select>
                        </div>
                        <div>
                            <label for="guide_number" class="block text-sm font-medium text-gray-700">Guía</label>
                            <input type="text" name="guide_number" x-model="shipmentData.guide_number" class="mt-1 block w-full form-input rounded-md shadow-sm border-gray-300" required>
                        </div>
                        <div x-show="shipmentData.type === 'Entrega'">
                            <label for="so_number" class="block text-sm font-medium text-gray-700">SO</label>
                            <input type="text" name="so_number" x-model="shipmentData.so_number" class="mt-1 block w-full form-input rounded-md shadow-sm border-gray-300">
                        </div>
                        <div x-show="shipmentData.type === 'Importacion'">
                            <label for="pedimento" class="block text-sm font-medium text-gray-700">Pedimento</label>
                            <input type="text" name="pedimento" x-model="shipmentData.pedimento" class="mt-1 block w-full form-input rounded-md shadow-sm border-gray-300">
                        </div>
                        
                        <!-- Origen Dinámico -->
                        <div>
                            <label for="origin" class="block text-sm font-medium text-gray-700">Origen</label>
                            <select name="origin" x-model="shipmentData.origin" class="mt-1 block w-full form-select rounded-md shadow-sm border-gray-300">
                                <template x-if="shipmentData.type === 'Entrega'">
                                    <>
                                        <option value="MEX">MEX</option>
                                        <option value="GDL">GDL</option>
                                        <option value="MTY">MTY</option>
                                        <option value="SJD">SJD</option>
                                        <option value="CUN">CUN</option>
                                    </>
                                </template>
                                <template x-if="shipmentData.type === 'Importacion'">
                                    <>
                                        <option value="MEX">MEX</option>
                                        <option value="GDL">GDL</option>
                                        <option value="MTY">MTY</option>
                                        <option value="SJD">SJD</option>
                                        <option value="CUN">CUN</option>
                                        <option value="MZN">MZN</option>
                                        <option value="VER">VER</option>
                                    </>
                                </template>
                            </select>
                        </div>

                        <!-- Destino Dinámico -->
                        <div>
                            <label for="destination_type" class="block text-sm font-medium text-gray-700">Destino</label>
                            <template x-if="shipmentData.type === 'Entrega'">
                                <input type="text" name="destination_type" x-model="shipmentData.destination_type" class="mt-1 block w-full form-input rounded-md shadow-sm border-gray-300">
                            </template>
                            <template x-if="shipmentData.type === 'Importacion'">
                                <select name="destination_type" x-model="shipmentData.destination_type" class="mt-1 block w-full form-select rounded-md shadow-sm border-gray-300">
                                    <option value="MEX">MEX</option>
                                    <option value="GDL">GDL</option>
                                    <option value="MTY">MTY</option>
                                    <option value="SJD">SJD</option>
                                    <option value="CUN">CUN</option>
                                </select>
                            </template>
                        </div>

                        <div>
                            <label for="operator" class="block text-sm font-medium text-gray-700">Operador</label>
                            <input type="text" name="operator" x-model="shipmentData.operator" class="mt-1 block w-full form-input rounded-md shadow-sm border-gray-300">
                        </div>
                        <div>
                            <label for="license_plate" class="block text-sm font-medium text-gray-700">Placas</label>
                            <input type="text" name="license_plate" x-model="shipmentData.license_plate" class="mt-1 block w-full form-input rounded-md shadow-sm border-gray-300">
                        </div>
                    </div>
                    <!-- Facturas Dinámicas -->
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-800">Facturas</h4>
                        <div class="mt-2 space-y-4">
                            <template x-for="(invoice, index) in shipmentData.invoices" :key="index">
                                <div class="flex items-center space-x-2 p-2 border rounded-md">
                                    <input type="text" :name="`invoices[${index}][invoice_number]`" x-model="invoice.invoice_number" class="w-full form-input rounded-md" placeholder="No. Factura" required>
                                    <input type="number" :name="`invoices[${index}][box_quantity]`" x-model="invoice.box_quantity" class="w-1/4 form-input rounded-md" placeholder="Cajas">
                                    <input type="number" :name="`invoices[${index}][bottle_quantity]`" x-model="invoice.bottle_quantity" class="w-1/4 form-input rounded-md" placeholder="Botellas">
                                    <button type="button" @click="removeInvoice(index)" x-show="shipmentData.invoices.length > 1" class="text-red-500 hover:text-red-700">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addInvoice()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Añadir otra factura</button>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:grid sm:grid-cols-2 sm:gap-3">
                    <button type="button" @click="isManualModalOpen = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#2c3856] text-base font-medium text-white hover:bg-[#ff9c00]">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>