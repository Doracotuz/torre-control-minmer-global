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
                        x-data="{ elementType: '{{ old('type', 'file') }}', fileName: '{{ old('name') }}' }" {{-- Alpine.js data --}}
                        x-on:change="
                            if ($refs.fileInput && $refs.fileInput.files.length > 0) {
                                if (elementType === 'file') { // Only auto-fill name if type is file
                                    if ($refs.fileInput.files.length > 1) {
                                        fileName = $refs.fileInput.files.length + ' archivos seleccionados';
                                    } else {
                                        fileName = $refs.fileInput.files[0].name.split('.').slice(0, -1).join('.'); // Get name without extension
                                    }
                                }
                            } else if ($refs.urlInput) {
                                // If type is link, keep user input or empty
                            } else {
                                fileName = ''; // Clear name if file input is cleared
                            }
                        "
                    >
                        @csrf

                        <!-- Tipo de Elemento (Archivo o Enlace) -->
                        <div class="mb-4">
                            <x-input-label for="type" :value="__('Tipo de Elemento')" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="elementType" @change="if(elementType !== 'file') { fileName = ''; $refs.fileInput.value = ''; } if(elementType !== 'link') { $refs.urlInput.value = ''; }" >
                                <option value="file">{{ __('Archivo Local') }}</option>
                                <option value="link">{{ __('Enlace Externo') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>
                        
                        <!-- Nombre del Elemento -->
                        <div class="mb-5">
                            <x-input-label for="name" :value="__('Nombre del Elemento')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" x-model="fileName" required x-bind:required="elementType === 'link'" /> {{-- Required only for links --}}
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>


                        <!-- Campo para Archivo (visible si type es 'file') -->
                        <div x-show="elementType === 'file'" class="mb-5">
                            <x-input-label for="file" :value="__('Seleccionar Archivo')" />
                            
                            {{-- Estilo de carga de archivo mejorado --}}
                            <label for="file" class="flex items-center justify-center w-full h-32 mt-1 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-all duration-300 ease-in-out group
                                {{ $errors->has('file') ? 'border-red-500' : '' }}"
                                x-on:dragover.prevent.stop="event.dataTransfer.dropEffect = 'copy'" {{-- Prevent default and stop propagation, set dropEffect --}}
                                x-on:dragleave.prevent.stop="" {{-- Prevent default and stop propagation --}}
                                x-on:drop.prevent.stop="
                                    $refs.fileInput.files = event.dataTransfer.files;
                                    $refs.fileInput.dispatchEvent(new Event('change'));
                                    $event.target.closest('form').submit();
                                "
                            >
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-10 h-10 mb-3 text-gray-400 group-hover:text-[#2c3856] transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6H16a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h2"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12l-4-4m0 0L8 12m4-4v8"></path></svg>
                                    <p class="mb-2 text-sm text-gray-500 group-hover:text-[#2c3856] transition-colors duration-300"><span class="font-semibold">{{ __('Haz clic para subir') }}</span> {{ __('o arrastra y suelta') }}</p>
                                    <p class="text-xs text-gray-500 group-hover:text-[#2c3856] transition-colors duration-300">{{ __('PNG, JPG, PDF, DOCX, XLSX (Máx. 10MB por archivo)') }}</p>
                                    <p x-show="fileName" class="text-xs text-[#ff9c00] mt-1" x-text="fileName"></p> {{-- Muestra el nombre del archivo seleccionado --}}
                                </div>
                            </label>
                            <input x-ref="fileInput" id="file" class="hidden" type="file" name="files[]" multiple x-bind:required="elementType === 'file'" > {{-- Added multiple and changed name to files[] --}}
                            
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">{{ __('El nombre se rellenará automáticamente con el nombre del primer archivo seleccionado.') }}</p>
                        </div>

                        <!-- Campo para URL (visible si type es 'link') -->
                        <div x-show="elementType === 'link'" class="mb-5">
                            <x-input-label for="url" :value="__('URL del Enlace (Excel Online, Power BI, etc.)')" />
                            <x-text-input x-ref="urlInput" id="url" class="block mt-1 w-full" type="url" name="url" :value="old('url')" placeholder="https://ejemplo.com/reporte" x-bind:required="elementType === 'link'" /> {{-- Required only for links --}}
                            <x-input-error :messages="$errors->get('url')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('folders.index', $folder) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-4">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Añadir Elemento(s)') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>