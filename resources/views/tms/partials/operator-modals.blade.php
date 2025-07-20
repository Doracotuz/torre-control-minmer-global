<?php
// Archivo: resources/views/tms/partials/operator-modals.blade.php
?>
<div x-show="modalType === 'updateInvoice'" @keydown.escape.window="closeModal()" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div @click.away="closeModal()" class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form action="{{ route('operator.updateInvoiceStatus') }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitWithLocation($el)"
                  x-data="{
                      previews: [],
                      handleFiles(event) {
                          this.previews = []; // Limpia las vistas previas anteriores
                          let files = Array.from(event.target.files);
                          files.forEach(file => {
                              const reader = new FileReader();
                              reader.onload = (e) => {
                                  this.previews.push(e.target.result);
                              };
                              reader.readAsDataURL(file);
                          });
                      }
                  }">
                @csrf
                <input type="hidden" name="invoice_id" x-model="modalData.id">
                <input type="hidden" name="status" x-model="modalData.status">
                <input type="hidden" name="latitude" x-model="latitude">
                <input type="hidden" name="longitude" x-model="longitude">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">Confirmar <span x-text="modalData.status"></span></h3>
                    <p class="mt-2 text-sm text-gray-500">Puedes adjuntar hasta 10 fotos como evidencia.</p>
                    
                    <div class="mt-4">
                        <input type="file" name="photos[]" @change="handleFiles" multiple accept="image/*" capture="environment" x-ref="cameraInputMulti" class="hidden">
                        <input type="file" name="photos_gallery[]" @change="handleFiles" multiple accept="image/*" x-ref="galleryInputMulti" class="hidden">
                        {{-- Nota: El controlador debe revisar ambos inputs --}}

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button type="button" @click="$refs.cameraInputMulti.click()" class="flex items-center justify-center w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                <i class="fas fa-camera mr-2"></i> Tomar Foto(s)
                            </button>
                            <button type="button" @click="$refs.galleryInputMulti.click()" class="flex items-center justify-center w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                <i class="fas fa-images mr-2"></i> Adjuntar de Galería
                            </button>
                        </div>
                    </div>
                    <div x-show="previews.length > 0" class="mt-4">
                        <p class="text-sm font-medium text-gray-600 mb-2">Vista Previa:</p>
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                            <template x-for="preview in previews" :key="preview">
                                <img :src="preview" class="rounded-lg shadow-md w-full h-16 object-cover">
                            </template>
                        </div>
                    </div>

                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 bg-white border rounded-md">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-[#2c3856] text-white rounded-md hover:bg-[#1e293b]">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div x-show="modalType === 'registerEvent'" @keydown.escape.window="closeModal()" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div @click.away="closeModal()" class="bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
            <form action="{{ route('operator.registerEvent') }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitWithLocation($el)"
                  x-data="{
                      photoPreview: null,
                      handleFile(event) {
                          if (event.target.files.length > 0) {
                              const reader = new FileReader();
                              reader.onload = (e) => { this.photoPreview = e.target.result; };
                              reader.readAsDataURL(event.target.files[0]);
                          }
                      }
                  }">
                @csrf
                <input type="hidden" name="guide_number" value="{{ $guide_number ?? '' }}">
                <input type="hidden" name="latitude" x-model="latitude">
                <input type="hidden" name="longitude" x-model="longitude">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">Registrar Evento</h3>
                    <select name="event_type" class="mt-4 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2c3856] focus:ring focus:ring-[#2c3856] focus:ring-opacity-50" required>
                        <option value="En pension">En pensión</option>
                        <option value="Alimentos">Alimentos</option>
                        <option value="Altercado">Altercado</option>
                    </select>
                    <p class="mt-4 text-sm text-gray-500">Puedes adjuntar 1 foto como evidencia.</p>
                    
                    <div class="mt-2">
                        <input type="file" name="photo_camera" @change="handleFile" accept="image/*" capture="environment" x-ref="cameraInput" class="hidden">
                        <input type="file" name="photo_gallery" @change="handleFile" accept="image/*" x-ref="galleryInput" class="hidden">
                        {{-- Nota: El controlador debe revisar ambos inputs --}}
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button type="button" @click="$refs.cameraInput.click()" class="flex items-center justify-center w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                <i class="fas fa-camera mr-2"></i> Tomar Foto
                            </button>
                            <button type="button" @click="$refs.galleryInput.click()" class="flex items-center justify-center w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                <i class="fas fa-images mr-2"></i> Adjuntar de Galería
                            </button>
                        </div>
                    </div>
                    <div x-show="photoPreview" class="mt-4">
                        <p class="text-sm font-medium text-gray-600 mb-2">Vista Previa:</p>
                        <img :src="photoPreview" class="rounded-lg shadow-md max-w-full h-auto">
                    </div>

                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-2">
                    <button type="button" @click="closeModal()" class="px-4 py-2 bg-white border rounded-md">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-[#2c3856] text-white rounded-md hover:bg-[#1e293b]">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>