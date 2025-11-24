<x-app-layout>
    <div x-data="{ manager: productManager() }" x-init='manager.init(@json($products))'>

        <x-slot name="header">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 md:mb-0">
                    Gestión de Catálogo - {{ Auth::user()->area ? Auth::user()->area->name : 'N/A' }}
                </h2>
                <a href="{{ route('ff.dashboard.index') }}"
                class="inline-flex items-center px-6 py-2 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest bg-[#2c3856] hover:bg-[#ff9c00] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300 ease-in-out">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Volver a Panel Principal
                </a>                
            </div>
        </x-slot>
        <div class="flex space-x-3">     
            <button
                @click="manager.openUploadModal()"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition ease-in-out duration-150">
                <i class="fas fa-file-upload mr-2"></i> Cargar Plantilla
            </button>
            <!-- <button
                @click="manager.selectNewProduct()"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i> Nuevo Producto
            </button> -->
            <a href="{{ route('ff.catalog.exportCsv') }}" target="_blank"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition ease-in-out duration-150 shadow-sm">
                <i class="fas fa-file-csv mr-2 text-green-600"></i> Exportar CSV
            </a>

            <button 
            @click="manager.openPdfModal()"
            type="button"
            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                <i class="fas fa-file-pdf mr-2 text-red-400"></i> Catálogo PDF
            </button>         
        </div>        

        <div class="py-12">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <div class="lg:col-span-2 flex flex-col gap-6">
                        <div>
                            <input type="text" x-model="manager.filter" placeholder="Buscar por SKU, descripción, marca o UPC..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-gray-800 focus:ring-gray-800">
                        </div>

                        <div x-show="manager.loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <template x-for="i in 6">
                                <div class="bg-white rounded-lg shadow-md h-64 animate-pulse"></div>
                            </template>
                        </div>
                        
                        <div x-show="!manager.loading && manager.filteredProducts.length === 0" class="text-center text-gray-500 py-20">
                            <i class="fas fa-search fa-3x mb-4"></i>
                            <p x-text="manager.filter === '' ? 'No se encontraron productos.' : 'No hay productos que coincidan con tu búsqueda.'"></p>
                        </div>

                        <div x-show="!manager.loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-transition>
                            <template x-for="product in manager.filteredProducts" :key="product.id">
                                <div @click="manager.selectProductForEdit(product)"
                                    class="group bg-white rounded-lg shadow-md cursor-pointer transition-all duration-300 hover:shadow-xl hover:-translate-y-1"
                                    :class="{ 
                                        'ring-2 ring-gray-800': manager.form.id === product.id,
                                        'opacity-60 grayscale hover:grayscale-0': !product.is_active 
                                    }">
                                    
                                    <div class="relative h-64 w-full overflow-hidden rounded-t-lg p-2 bg-white"> 
                                        <img :src="product.photo_url" :alt="product.description" class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-110" style="object-position: center;"> 
                                        <span class="absolute top-4 right-4 px-2 py-0.5 text-xs font-semibold rounded-full" 
                                              :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                              x-text="product.is_active ? 'Activo' : 'Inactivo'">
                                        </span>
                                    </div>
                                    
                                    <div class="p-4">
                                        <p class="text-xs text-gray-400 font-mono" x-text="product.sku"></p>
                                        <h4 class="font-semibold text-gray-800 line-clamp-2" x-text="product.description"></h4>
                                        <div class="flex justify-between items-end mt-2">
                                            <p class="text-lg font-extrabold text-gray-900" x-text="`$${parseFloat(product.unit_price).toFixed(2)}`"></p>
                                            <p class="text-xs text-gray-500" x-text="product.brand"></p>
                                        </div>
                                        <div class="mt-2 text-xs text-gray-400 flex flex-wrap gap-2">
                                            <span x-show="product.upc" x-text="'UPC: ' + product.upc"></span>
                                            <span x-show="product.pieces_per_box" x-text="'Pzas/Caja: ' + product.pieces_per_box"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="sticky top-28">
                            <form @submit.prevent="manager.saveProduct()"
                                  class="bg-white rounded-xl shadow-2xl max-h-[85vh] flex flex-col">

                                <div class="flex justify-between items-center p-5 border-b">
                                    <h3 class="text-xl font-semibold text-gray-900" x-text="manager.form.id ? 'Editar Producto' : 'Nuevo Producto'"></h3>
                                    <button type="button" @click="manager.resetForm()" x-show="manager.form.id" class="text-gray-400 hover:text-gray-900" title="Cancelar edición">
                                        <i class="fas fa-times fa-lg"></i>
                                    </button>
                                </div>

                                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 overflow-y-auto">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">SKU</label>
                                        <input type="text" x-model="manager.form.sku" :disabled="manager.form.id" x-ref="skuInput" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm disabled:bg-gray-100" required>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Descripción</label>
                                        <input type="text" x-model="manager.form.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Precio Unitario</label>
                                        <input type="number" step="0.01" min="0" x-model="manager.form.unit_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-800 focus:ring-gray-800" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">UPC</label>
                                        <input type="text" x-model="manager.form.upc" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Marca</label>
                                        <input type="text" x-model="manager.form.brand" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                                        <input type="text" x-model="manager.form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Piezas/Caja</label>
                                        <input type="number" step="1" min="0" x-model="manager.form.pieces_per_box" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div class="md:col-span-2 grid grid-cols-3 gap-2">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Largo</label>
                                            <input type="number" step="0.01" min="0" x-model="manager.form.length" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Ancho</label>
                                            <input type="number" step="0.01" min="0" x-model="manager.form.width" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700">Alto</label>
                                            <input type="number" step="0.01" min="0" x-model="manager.form.height" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        </div>
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
                                            <input type="checkbox" x-model="manager.form.is_active" class="h-5 w-5 rounded border-gray-300 text-gray-800 shadow-sm focus:ring-gray-700">
                                            <span class="ml-2 text-sm text-gray-700">Producto Activo</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 border-t rounded-b-xl space-x-3">
                                    <button type="button" @click="manager.deleteProduct(manager.form)" 
                                            x-show="manager.form.id"
                                            class="inline-flex items-center text-red-600 hover:text-red-800 text-sm font-medium transition-colors">
                                        <i class="fas fa-trash mr-2"></i>Eliminar
                                    </button>
                                    <div class="flex space-x-3">
                                        <button type="button" @click="manager.resetForm()" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            Cancelar
                                        </button>
                                        <button type="submit" 
                                            class="inline-flex items-center bg-gray-800 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700"
                                            :disabled="manager.isSaving">
                                            <i x-show="manager.isSaving" class="fas fa-spinner fa-spin -ml-1 mr-2"></i>
                                            <span x-text="manager.isSaving ? 'Guardando...' : (manager.form.id ? 'Actualizar' : 'Guardar')">Guardar</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div> </div> </div>
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

                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <h4 class="text-sm font-bold text-blue-800 mb-2"><i class="fas fa-info-circle mr-1"></i> ¿Cómo editar masivamente?</h4>
                        <ol class="list-decimal list-inside text-xs text-blue-700 space-y-1">
                            <li>Descarga la plantilla (ahora incluye tus productos actuales).</li>
                            <li>Edita precios, nombres o añade nuevos productos en Excel.</li>
                            <li>Sube el archivo CSV editado aquí.</li>
                            <li><strong>El ZIP de imágenes es opcional</strong> (solo si subes fotos nuevas).</li>
                        </ol>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Paso 1: Descargar Catalogo Actual</label>
                        <a href="{{ route('ff.catalog.downloadTemplate') }}"
                        class="mt-1 w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-download mr-2 text-blue-600"></i>
                            Descargar plantilla.csv
                        </a>
                    </div>

                    <div class="border-t pt-4">
                        <label class="text-sm font-medium text-gray-700">Paso 2: Archivo CSV Editado <span class="text-red-500">*</span></label>
                        <input type="file" name="product_file" accept=".csv" required 
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 file:py-2 file:px-4 file:border-0 file:mr-4 file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Paso 3: Imágenes Nuevas (Opcional)</label>
                        <input type="file" name="image_zip" accept=".zip"
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 file:py-2 file:px-4 file:border-0 file:mr-4 file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300">
                        <p class="text-xs text-gray-500 mt-1">Sube un .zip solo si agregaste nombres de archivo en la columna "Foto" del CSV.</p>
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

        <div x-show="manager.isPdfModalOpen"
            @keydown.escape.window="manager.isPdfModalOpen = false"
            class="fixed inset-0 z-50 bg-gray-900 bg-opacity-60 flex items-center justify-center p-4 backdrop-blur-sm"
            x-transition style="display: none;">
            
            <div @click.outside="manager.isPdfModalOpen = false"
                class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all">
                
                <div class="bg-gray-800 p-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <i class="fas fa-percentage mr-2 text-yellow-500"></i> Ajuste de Precios
                    </h3>
                    <button @click="manager.isPdfModalOpen = false" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <p class="text-gray-600 text-sm mb-4">
                        Ingresa el porcentaje que deseas aumentar al precio original para este catálogo PDF.
                    </p>

                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Porcentaje de aumento (%)</label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="number" 
                                x-model="manager.pdfPercentage" 
                                x-ref="percentageInput"
                                @keydown.enter="manager.generatePdf()"
                                min="0" 
                                class="block w-full pr-12 border-gray-300 rounded-md focus:ring-gray-800 focus:border-gray-800 pl-4 py-3 text-lg" 
                                placeholder="Ej: 15">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">%</span>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            * Si ingresas <strong>0</strong>, se descargarán los precios originales.
                        </p>
                    </div>
                    
                    <div x-show="manager.pdfPercentage > 0" class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <p class="text-xs text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            Ejemplo: Un producto de $100 costará 
                            <strong x-text="'$' + (100 * (1 + (manager.pdfPercentage/100))).toFixed(2)"></strong>
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end p-4 bg-gray-50 border-t space-x-3">
                    <button @click="manager.isPdfModalOpen = false" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                        Cancelar
                    </button>
                    <button @click="manager.generatePdf()" 
                        class="px-4 py-2 bg-gray-800 border border-transparent rounded-md text-sm font-medium text-white hover:bg-gray-700 shadow-md transition-colors flex items-center">
                        <i class="fas fa-download mr-2"></i> Descargar PDF
                    </button>
                </div>
            </div>
        </div>        

    </div>

    <script>
        function productManager() {
            return {
                products: [],
                filter: '',
                loading: true,
                isSaving: false,
                photoPreview: null,
                form: {
                    id: null, 
                    sku: '', 
                    description: '', 
                    type: '', 
                    brand: '',
                    unit_price: 0.00, 
                    pieces_per_box: null,
                    length: null,
                    width: null,
                    height: null,
                    upc: '',
                    photo: null, 
                    photo_url: null, 
                    is_active: true,
                },
                isUploadModalOpen: false,
                uploadMessage: '',
                uploadSuccess: false,
                isPdfModalOpen: false,
                pdfPercentage: 0,
                
                init(initialProducts) {
                    this.products = initialProducts;
                    this.loading = false;
                },

                get filteredProducts() {
                    if (this.filter === '') return this.products;
                    const search = this.filter.toLowerCase();
                    return this.products.filter(p => 
                        p.sku.toLowerCase().includes(search) || 
                        p.description.toLowerCase().includes(search) ||
                        (p.brand && p.brand.toLowerCase().includes(search)) ||
                        (p.upc && p.upc.toLowerCase().includes(search))
                    );
                },

                selectProductForEdit(product) {
                    this.form = { ...product, photo: null };
                    this.photoPreview = null;
                },
                selectNewProduct() {
                    this.resetForm();
                    this.$nextTick(() => { this.$refs.skuInput.focus(); });
                },
                
                resetForm() {
                    this.form = { 
                        id: null, 
                        sku: '', 
                        description: '', 
                        type: '', 
                        brand: '', 
                        unit_price: 0.00, 
                        pieces_per_box: null,
                        length: null,
                        width: null,
                        height: null,
                        upc: '',
                        photo: null, 
                        photo_url: null, 
                        is_active: true 
                    };
                    this.photoPreview = null;
                },

                openUploadModal() {
                    this.isUploadModalOpen = true;
                    this.uploadMessage = '';
                    this.uploadSuccess = false;
                },
                closeUploadModal() {
                    this.isUploadModalOpen = false;
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
                        this.uploadSuccess = true;
                        this.uploadMessage = data.message;
                        event.target.reset();
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

                previewPhoto(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.form.photo = file;
                        const reader = new FileReader();
                        reader.onload = (e) => { this.photoPreview = e.target.result; };
                        reader.readAsDataURL(file);
                    }
                },

                openPdfModal() {
                    this.pdfPercentage = 0;
                    this.isPdfModalOpen = true;
                    this.$nextTick(() => this.$refs.percentageInput.focus());
                },

                generatePdf() {
                    const url = "{{ route('ff.catalog.exportPdf') }}?percentage=" + this.pdfPercentage;
                    window.open(url, '_blank');
                    this.isPdfModalOpen = false;
                },                

                async saveProduct() {
                    if (this.isSaving) return;
                    this.isSaving = true;
                    const formData = new FormData();
                    Object.keys(this.form).forEach(key => {
                        if (key !== 'photo_url') {
                            let value = this.form[key];
                            if (typeof value === 'boolean') value = value ? 1 : 0;
                            if (key === 'photo' && !value) return;
                            if (value === null) value = '';
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
                            let errorMessage = 'No se pudo guardar:\n';
                            if (response.status === 422 && errorData.errors) {
                                for (const field in errorData.errors) {
                                    errorMessage += `- ${errorData.errors[field].join(', ')}\n`;
                                }
                            } else {
                                errorMessage += errorData.message || 'Error desconocido.';
                            }
                            alert(errorMessage);
                            throw new Error('Error al guardar.');
                        }
                        const savedProduct = await response.json();
                        if (isUpdate) {
                            const index = this.products.findIndex(p => p.id === savedProduct.id);
                            if (index > -1) this.products.splice(index, 1, savedProduct);
                        } else {
                            this.products.unshift(savedProduct);
                        }
                        this.resetForm();
                    } catch (error) {
                        console.error(error);
                    } finally {
                        this.isSaving = false;
                    }
                },
                
                async deleteProduct(product) {
                    if (!product.id) return;
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
                        if (this.form.id === product.id) {
                            this.resetForm();
                        }
                    } catch (error) {
                        console.error(error);
                        alert('No se pudo eliminar el producto.');
                    }
                },
            }
        }
    </script>
</x-app-layout>