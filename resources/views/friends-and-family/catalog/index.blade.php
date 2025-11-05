<x-app-layout>
    <div x-data="{ manager: productManager() }" x-init="manager.init(@json($products))">

        <x-slot name="header">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 md:mb-0">
                    Gestión de Catálogo (Friends & Family)
                </h2>
            </div>
        </x-slot>
        <div class="flex space-x-2">
            <button
                @click="manager.openUploadModal()"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 transition ease-in-out duration-150">
                <i class="fas fa-file-upload mr-2"></i> Cargar Plantilla
            </button>
            <button
                @click="manager.openFormModal(null)"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i> Nuevo Producto
            </button>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-4">
                    <input type="text" x-model="manager.filter" placeholder="Buscar por SKU o descripción..." class="w-full rounded-md border-gray-300 shadow-sm">
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marca / Tipo</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" x-show="!manager.loading" x-transition>
                                <template x-for="product in manager.filteredProducts" :key="product.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img :src="product.photo_url" :alt="product.description" class="w-12 h-12 rounded-lg object-cover shadow-md">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="product.sku"></td>
                                        <td class="px-6 py-4 text-sm text-gray-700" style="max-width: 300px;" x-text="product.description"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div x-text="product.brand || 'N/A'" class="font-semibold"></div>
                                            <div x-text="product.type || 'N/A'" class="text-xs"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right" x-text="`$${parseFloat(product.price).toFixed(2)}`"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button @click="manager.toggleStatus(product)"
                                                :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                class="px-3 py-1 text-xs font-semibold rounded-full transition-colors">
                                                <span x-text="product.is_active ? 'Activo' : 'Inactivo'"></span>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button @click="manager.openFormModal(product)" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="Editar">
                                                <i class="fas fa-edit fa-lg"></i>
                                            </button>
                                            <button @click="manager.deleteProduct(product)" class="text-red-600 hover:text-red-900 transition-colors" title="Eliminar">
                                                <i class="fas fa-trash fa-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="manager.filteredProducts.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-search fa-2x mb-2"></i>
                                        <p x-show="manager.filter === ''">No se encontraron productos.</p>
                                        <p x-show="manager.filter !== ''">No hay productos que coincidan con tu búsqueda.</p>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody x-show="manager.loading">
                                <template x-for="i in 3">
                                    <tr>
                                        <td class="px-6 py-4"><div class="h-12 w-12 rounded-lg bg-gray-200 animate-pulse"></div></td>
                                        <td class="px-6 py-4"><div class="h-4 w-20 rounded bg-gray-200 animate-pulse"></div></td>
                                        <td class="px-6 py-4"><div class="h-4 w-40 rounded bg-gray-200 animate-pulse"></div></td>
                                        <td class="px-6 py-4"><div class="h-4 w-24 rounded bg-gray-200 animate-pulse"></div></td>
                                        <td class="px-6 py-4"><div class="h-4 w-16 rounded bg-gray-200 animate-pulse"></div></td>
                                        <td class="px-6 py-4 text-center"><div class="h-6 w-16 rounded-full bg-gray-200 animate-pulse mx-auto"></div></td>
                                        <td class="px-6 py-4"><div class="h-4 w-12 rounded bg-gray-200 animate-pulse ml-auto"></div></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="manager.isFormModalOpen"
            @keydown.escape.window="manager.closeFormModal()"
            class="fixed inset-0 z-50 bg-gray-900 bg-opacity-60 flex items-center justify-center p-4 backdrop-blur-sm"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;">

            <form @submit.prevent="manager.saveProduct()"
                @click.outside="manager.closeFormModal()"
                class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[95vh] flex flex-col"
                x-show="manager.isFormModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90">

                <div class="flex justify-between items-center p-5 border-b rounded-t-xl">
                    <h3 class="text-xl font-semibold text-gray-900" x-text="manager.form.id ? 'Editar Producto' : 'Nuevo Producto'"></h3>
                    <button type="button" @click="manager.closeFormModal()" class="text-gray-400 hover:text-gray-900">
                        <i class="fas fa-times fa-lg"></i>
                    </button>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 overflow-y-auto">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SKU</label>
                        <input type="text" x-model="manager.form.sku" :disabled="manager.form.id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm disabled:bg-gray-100" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Precio</label>
                        <input type="number" step="0.01" min="0" x-model="manager.form.price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                        <input type="text" x-model="manager.form.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Marca</label>
                        <input type="text" x-model="manager.form.brand" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <input type="text" x-model="manager.form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Fotografía</label>
                        <div class="mt-1 flex items-center space-x-4">
                            <span class="h-20 w-20 rounded-lg overflow-hidden bg-gray-100 shadow-inner">
                                <img x-show="manager.photoPreview" :src="manager.photoPreview" class="h-full w-full object-cover">
                                <img x-show="!manager.photoPreview && manager.form.photo_url" :src="manager.form.photo_url" class="h-full w-full object-cover">
                                <svg x-show="!manager.photoPreview && !manager.form.photo_url" class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 3H5C3.9 3 3 3.9 3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 16H6c-.55 0-1-.45-1-1V6c0-.55.45-1 1-1h12c.55 0 1 .45 1 1v12c0 .55-.45 1-1 1zm-4.5-3.5L15 12l-2.5 3-1.5-1.5L9 16h9z"></path>
                                </svg>
                            </span>
                            <input type="file" @change="manager.previewPhoto($event)" class="hidden" x-ref="photoInput">
                            <button type="button" @click="$refs.photoInput.click()" class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50">
                                Cambiar Foto
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-center mt-2">
                            <input type="checkbox" x-model="manager.form.is_active" class="h-5 w-5 rounded border-gray-300 text-blue-600 shadow-sm">
                            <span class="ml-2 text-sm text-gray-700">Producto Activo</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-xl space-x-3">
                    <button type="button" @click="manager.closeFormModal()" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" 
                        class="inline-flex items-center bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700"
                        :disabled="manager.isSaving">
                        <i x-show="manager.isSaving" class="fas fa-spinner fa-spin -ml-1 mr-2"></i>
                        <span x-text="manager.isSaving ? 'Guardando...' : 'Guardar'">Guardar</span>
                    </button>
                </div>
            </form>
        </div>

        <div x-show="manager.isUploadModalOpen"
            @keydown.escape.window="manager.closeUploadModal()"
            class="fixed inset-0 z-50 bg-gray-900 bg-opacity-60 flex items-center justify-center p-4 backdrop-blur-sm"
            x-transition style="display: none;">
            
            <form @submit.prevent="manager.submitImport($event)"
                @click.outside="manager.closeUploadModal()"
                class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
                
                <div class="flex justify-between items-center p-5 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Cargar por Plantilla</h3>
                    <button type="button" @click="manager.closeUploadModal()" class="text-gray-400 hover:text-gray-900"><i class="fas fa-times fa-lg"></i></button>
                </div>

                <div class="p-6 space-y-6">
                    <div x-show="manager.uploadMessage"
                        :class="manager.uploadSuccess ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'"
                        class="border p-4 rounded-md text-sm"
                        x-text="manager.uploadMessage"
                        style="display: none;">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Paso 1: Descargar la plantilla</label>
                        <a href="{{ route('ff.catalog.downloadTemplate') }}"
                        class="mt-1 w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-file-csv mr-2 text-green-600"></i>
                        Descargar plantilla_productos_ff.csv
                        </a>
                        <p class="mt-1 text-xs text-gray-500">Llena esta plantilla con tus productos. Asegúrate de que los nombres de las fotos coincidan con las imágenes del ZIP.</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Paso 2: Subir archivo de plantilla (.csv)</label>
                        <input type="file" name="product_file" accept=".csv" required 
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 file:py-2 file:px-4 file:border-0 file:mr-4 file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Paso 3: Subir ZIP con imágenes (.zip)</label>
                        <input type="file" name="image_zip" accept=".zip" required
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 file:py-2 file:px-4 file:border-0 file:mr-4 file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                    </div>
                </div>

                <div class="flex items-center justify-end p-4 bg-gray-50 border-t rounded-b-xl space-x-3">
                    <button type="button" @click="manager.closeUploadModal()" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cerrar
                    </button>
                    <button type="submit" 
                        class="inline-flex items-center bg-green-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-green-700"
                        :disabled="manager.isSaving">
                        <i x-show="manager.isSaving" class="fas fa-spinner fa-spin -ml-1 mr-2" style="display: none;"></i>
                        <span x-text="manager.isSaving ? 'Importando...' : 'Iniciar Importación'"></span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        function productManager() {
            return {
                products: [],
                filter: '',
                loading: false,
                isSaving: false,
                isFormModalOpen: false,
                photoPreview: null,
                form: {
                    id: null, sku: '', description: '', type: '', brand: '',
                    price: 0.00, photo: null, photo_url: null, is_active: true,
                },

                // --- Nuevas variables para el modal de carga ---
                isUploadModalOpen: false,
                uploadMessage: '',
                uploadSuccess: false,
                // ---------------------------------------------

                init(initialProducts) {
                    this.products = initialProducts;
                },

                get filteredProducts() {
                    if (this.filter === '') return this.products;
                    const search = this.filter.toLowerCase();
                    return this.products.filter(p => 
                        p.sku.toLowerCase().includes(search) || 
                        p.description.toLowerCase().includes(search)
                    );
                },

                openFormModal(product = null) {
                    if (product) {
                        this.form = { ...product, photo: null };
                    } else {
                        this.resetForm();
                    }
                    this.photoPreview = null;
                    this.isFormModalOpen = true;
                },
                closeFormModal() {
                    this.isFormModalOpen = false;
                    this.resetForm();
                },
                resetForm() {
                    this.form = { id: null, sku: '', description: '', type: '', brand: '', price: 0.00, photo: null, photo_url: null, is_active: true };
                    this.photoPreview = null;
                },

                // --- Métodos para el modal de carga ---
                openUploadModal() {
                    this.isUploadModalOpen = true;
                    this.uploadMessage = '';
                    this.uploadSuccess = false;
                },
                closeUploadModal() {
                    this.isUploadModalOpen = false;
                    // Si la carga fue exitosa, recargamos la página para ver los cambios
                    if (this.uploadSuccess) {
                        location.reload();
                    }
                },
                async submitImport(event) {
                    if (this.isSaving) return;
                    this.isSaving = true;
                    this.uploadMessage = '';
                    this.uploadSuccess = false;

                    const formData = new FormData(event.target);
                    
                    try {
                        const response = await fetch("{{ route('ff.catalog.import') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            this.uploadSuccess = false;
                            this.uploadMessage = data.message || 'Error al subir los archivos.';
                            if(data.errors) {
                                this.uploadMessage += ' ' + Object.values(data.errors).join(' ');
                            }
                            throw new Error('Error en la subida.');
                        }

                        // ¡Éxito! El controlador terminó todo el trabajo.
                        this.uploadSuccess = true;
                        this.uploadMessage = data.message;
                        event.target.reset(); // Limpia el formulario

                    } catch (error) {
                        console.error(error);
                        if (!this.uploadMessage) {
                            this.uploadSuccess = false;
                            this.uploadMessage = 'Ocurrió un error inesperado.';
                        }
                    } finally {
                        this.isSaving = false;
                    }
                },
                // --------------------------------------

                previewPhoto(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.form.photo = file;
                        const reader = new FileReader();
                        reader.onload = (e) => { this.photoPreview = e.target.result; };
                        reader.readAsDataURL(file);
                    }
                },

                async saveProduct() {
                    // ... (El resto de esta función sigue igual que antes) ...
                    if (this.isSaving) return;
                    this.isSaving = true;
                    const formData = new FormData();
                    Object.keys(this.form).forEach(key => {
                        if (key !== 'photo_url') {
                            let value = this.form[key];
                            if (typeof value === 'boolean') value = value ? 1 : 0;
                            if (key === 'photo' && !value) return;
                            formData.append(key, value);
                        }
                    });
                    const isUpdate = !!this.form.id;
                    if (isUpdate) formData.append('_method', 'PUT');
                    const url = isUpdate ? `{{ url('ff/catalog') }}/${this.form.id}` : `{{ route('ff.catalog.store') }}`;
                    try {
                        const response = await fetch(url, {
                            method: 'POST', body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                        });
                        if (!response.ok) {
                            const errorData = await response.json();
                            alert(`Error: ${errorData.message || 'No se pudo guardar.'}`);
                            throw new Error('Error al guardar.');
                        }
                        const savedProduct = await response.json();
                        if (isUpdate) {
                            const index = this.products.findIndex(p => p.id === savedProduct.id);
                            if (index > -1) this.products.splice(index, 1, savedProduct);
                        } else {
                            this.products.unshift(savedProduct);
                        }
                        this.closeFormModal();
                    } catch (error) {
                        console.error(error);
                        alert('Ocurrió un error. Revisa la consola.');
                    } finally {
                        this.isSaving = false;
                    }
                },
                
                async toggleStatus(product) {
                    const updatedProduct = { ...product, is_active: !product.is_active };
                    this.openFormModal(updatedProduct);
                    await this.saveProduct();
                },

                async deleteProduct(product) {
                    if (!confirm(`¿Estás seguro de que deseas eliminar "${product.description}"?`)) return;
                    try {
                        const response = await fetch(`{{ url('ff/catalog') }}/${product.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                        });
                        if (!response.ok) throw new Error('Error al eliminar.');
                        this.products = this.products.filter(p => p.id !== product.id);
                    } catch (error) {
                        console.error(error);
                        alert('No se pudo eliminar el producto.');
                    }
                },
            }
        }
    </script>
</x-app-layout>