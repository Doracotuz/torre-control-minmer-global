<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Añadir Elemento a Carpeta') }}
            <span class="text-gray-500"> / {{ $folder->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('file_links.store', $folder) }}" enctype="multipart/form-data"
                        x-data="form()"
                        x-on:submit.prevent="if (elementType === 'link') uploadFiles()" {{-- Solo enviar el formulario si es un enlace --}}
                        x-on:dragover.prevent.stop="event.dataTransfer.dropEffect = 'copy'"
                        x-on:dragleave.prevent.stop=""
                        x-on:drop.prevent.stop="
                            $refs.fileInput.files = event.dataTransfer.files;
                            $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true })); {{-- El drop dispara el change --}}
                        "
                    >
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="type" :value="__('Tipo de Elemento')" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="elementType" @change="if(elementType !== 'file') { fileName = ''; if($refs.fileInput) $refs.fileInput.value = ''; } if(elementType !== 'link') { if($refs.urlInput) $refs.urlInput.value = ''; }">
                                <option value="file">{{ __('Archivo Local') }}</option>
                                <option value="link">{{ __('Enlace Externo') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="mb-5">
                            <x-input-label for="name" :value="__('Nombre del Elemento')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" x-model="fileName" x-bind:required="elementType === 'link'" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div x-show="elementType === 'file'" class="mb-5">
                            <x-input-label for="file" :value="__('Seleccionar Archivo')" />
                            <label for="file" class="flex items-center justify-center w-full h-32 mt-1 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-all duration-300 ease-in-out group {{ $errors->has('file') ? 'border-red-500' : '' }}">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-6 h-6 mb-3 text-gray-400 group-hover:text-[#2c3856] transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6H16a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h2"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12l-4-4m0 0L8 12m4-4v8"></path></svg>
                                    <p class="mb-2 text-sm text-gray-500 group-hover:text-[#2c3856] transition-colors duration-300"><span class="font-semibold">{{ __('Haz clic para subir') }}</span> {{ __('o arrastra y suelta') }}</p>
                                    <p class="text-xs text-gray-500 group-hover:text-[#2c3856] transition-colors duration-300">{{ __('PNG, JPG, PDF, DOCX, XLSX (Máx. 500MB por archivo)') }}</p>
                                    <p x-show="fileName" class="text-xs text-[#ff9c00] mt-1" x-text="fileName"></p>
                                </div>
                            </label>
                            {{-- x-bind:required para el input de archivo --}}
                            <input x-ref="fileInput" id="file" class="hidden" type="file" name="files[]" multiple x-bind:required="elementType === 'file' && !isUploading && $refs.fileInput.files.length === 0" x-on:change="
                                if ($refs.fileInput && $refs.fileInput.files.length > 0) {
                                    if (elementType === 'file') {
                                        if ($refs.fileInput.files.length > 1) {
                                            fileName = $refs.fileInput.files.length + ' archivos seleccionados';
                                        } else {
                                            fileName = $refs.fileInput.files[0].name.split('.').slice(0, -1).join('.');
                                        }
                                    }
                                    // Disparar la subida automáticamente al seleccionar/soltar archivos
                                    uploadFiles();
                                } else {
                                    fileName = '';
                                }
                            ">
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">{{ __('El nombre se rellenará automáticamente con el nombre del primer archivo seleccionado. Al seleccionar múltiples archivos, se conservarán sus nombres originales.') }}</p>
                        </div>

                        <div x-show="uploading" class="mb-5">
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="bg-indigo-600 h-4 rounded-full transition-all duration-300" x-bind:style="'width: ' + uploadProgress + '%'"></div>
                            </div>
                            <p class="mt-2 text-sm text-gray-600" x-text="uploadMessage"></p>
                        </div>


                        <div x-data="{ localShow: false }" x-show="localShow && successMessage" x-transition:enter="transition ease-out duration-300 transform scale-90 opacity-0" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200 transform scale-100 opacity-100" x-transition:leave-end="opacity-0 scale-90"
                             class="fixed top-4 right-4 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert"
                             x-init="$watch('successMessage', (value) => { if (value) localShow = true; })">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <strong class="font-bold mr-1">{{ __('¡Éxito!') }}</strong>
                                <span class="block sm:inline" x-text="successMessage"></span>
                            </div>
                            <div class="flex items-center">
                                <x-primary-button @click="window.location.href = '{{ route('folders.index', $folder) }}'" class="ml-4 text-sm">
                                    {{ __('Volver a la carpeta') }}
                                </x-primary-button>
                                <button @click="localShow = false; successMessage = ''; resetForm()" class="ml-2 text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>


                        <div x-data="{ localShow: false }" x-show="localShow && errorMessage" x-transition:enter="transition ease-out duration-300 transform scale-90 opacity-0" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200 transform scale-100 opacity-100" x-transition:leave-end="opacity-0 scale-90"
                             class="fixed top-4 right-4 z-50 bg-white border-l-4 border-red-500 text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert"
                             x-init="$watch('errorMessage', (value) => { if (value) localShow = true; })">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <strong class="font-bold mr-1">{{ __('¡Error!') }}</strong>
                                <span class="block sm:inline" x-text="errorMessage"></span>
                            </div>
                            <button @click="localShow = false; errorMessage = ''" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div x-show="elementType === 'link'" class="mb-5">
                            <x-input-label for="url" :value="__('URL del Enlace (Excel Online, Power BI, etc.)')" />
                            <x-text-input x-ref="urlInput" id="url" class="block mt-1 w-full" type="url" name="url" :value="old('url')" placeholder="https://ejemplo.com/reporte" x-bind:required="elementType === 'link'" />
                            <x-input-error :messages="$errors->get('url')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('folders.index', $folder) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-4">
                                {{ __('Cancelar') }}
                            </a>

                            <x-primary-button type="submit" x-bind:disabled="uploading || (elementType === 'file' && (!($refs.fileInput && $refs.fileInput.files.length > 0) && !successMessage))">
                                {{ __('Añadir Elemento(s)') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.8/dist/axios.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('form', () => ({
                elementType: '{{ old('type', 'file') }}',
                fileName: '{{ old('name') }}',
                uploading: false,
                isUploading: false,
                uploadProgress: 0,
                currentFile: 0,
                totalFiles: 0,
                uploadMessage: '',
                errorMessage: '',
                successMessage: '',

                init() {

                    if (this.elementType === 'file' && this.fileName) {

                    }
                },

                resetForm() {
                    this.fileName = '';
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                    if (this.$refs.urlInput) {
                        this.$refs.urlInput.value = '';
                    }
                    this.uploadProgress = 0;
                    this.uploadMessage = '';
                    this.errorMessage = '';
                    this.successMessage = '';
                    this.uploading = false;
                    this.isUploading = false;
                    this.elementType = 'file';
                },

                async uploadFiles() {
                    if (this.isUploading) {
                        return;
                    }

                    this.errorMessage = '';
                    this.successMessage = '';
                    this.uploading = true;
                    this.isUploading = true;
                    this.uploadProgress = 0;

                    if (this.elementType === 'link') {

                        const formData = new FormData(this.$el);
                        try {
                            const response = await axios.post(this.$el.action, formData);
                            this.successMessage = response.data.message || 'Enlace añadido exitosamente.';
                        } catch (error) {
                            this.errorMessage = `Error al añadir enlace: ${error.response?.data?.message || error.message}`;
                        } finally {
                            this.uploading = false;
                            this.isUploading = false;
                        }
                        return;
                    }

                    const files = this.$refs.fileInput?.files;
                    if (!files || files.length === 0) {
                        this.errorMessage = 'Por favor, selecciona al menos un archivo.';
                        this.uploading = false;
                        this.isUploading = false;
                        return;
                    }

                    this.totalFiles = files.length;
                    let successfulUploads = 0;

                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        this.currentFile = i + 1;
                        this.uploadProgress = 0;
                        this.uploadMessage = `Subiendo archivo ${this.currentFile} de ${this.totalFiles} (${file.name})...`;

                        const formData = new FormData();
                        formData.append('type', 'file');
                        formData.append('_token', document.querySelector('input[name="_token"]').value);
                        formData.append('files[]', file);

                        if (files.length === 1 && this.fileName) {
                            formData.append('name', this.fileName);
                        }

                        try {
                            const response = await axios.post('{{ route('file_links.store', $folder) }}', formData, {
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                },
                                onUploadProgress: (progressEvent) => {
                                    if (progressEvent.lengthComputable) {
                                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                                        this.uploadProgress = percentCompleted;
                                        this.uploadMessage = `Subiendo ${percentCompleted}% (archivo ${this.currentFile} de ${this.totalFiles})`;
                                    }
                                }
                            });
                            successfulUploads++;
                        } catch (error) {
                            this.errorMessage = `Error al subir el archivo "${file.name}": ${error.response?.data?.message || error.message}`;
                            this.uploading = false;
                            this.isUploading = false;
                            return;
                        }
                    }

                    this.successMessage = `Se subieron ${successfulUploads} de ${this.totalFiles} archivo(s) exitosamente.`;
                    this.uploading = false;
                    this.isUploading = false;
                }
            }));
        });
    </script>
</x-app-layout>