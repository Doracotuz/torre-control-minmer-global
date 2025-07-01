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
                    <form method="POST" action="{{ route('file_links.store', $folder) }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Nombre del Elemento -->
                        <div>
                            <x-input-label for="name" :value="__('Nombre del Elemento')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Tipo de Elemento (Archivo o Enlace) -->
                        <div class="mt-4">
                            <x-input-label for="type" :value="__('Tipo de Elemento')" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="toggleFields()">
                                <option value="file" {{ old('type') == 'file' ? 'selected' : '' }}>Archivo Local</option>
                                <option value="link" {{ old('type') == 'link' ? 'selected' : '' }}>Enlace Externo</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Campo para Archivo (visible si type es 'file') -->
                        <div id="file-field" class="mt-4" style="{{ old('type') == 'link' ? 'display: none;' : '' }}">
                            <x-input-label for="file" :value="__('Seleccionar Archivo')" />
                            <input id="file" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" type="file" name="file">
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">Tamaño máximo: 10MB</p>
                        </div>

                        <!-- Campo para URL (visible si type es 'link') -->
                        <div id="url-field" class="mt-4" style="{{ old('type') == 'file' ? 'display: none;' : '' }}">
                            <x-input-label for="url" :value="__('URL del Enlace (Excel Online, Power BI, etc.)')" />
                            <x-text-input id="url" class="block mt-1 w-full" type="url" name="url" :value="old('url')" placeholder="https://ejemplo.com/reporte" />
                            <x-input-error :messages="$errors->get('url')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            {{-- CAMBIO AQUÍ: Asegurarse de que el enlace de cancelar apunta a folders.index --}}
                            <a href="{{ route('folders.index', $folder) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-4">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Añadir Elemento') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleFields() {
            const type = document.getElementById('type').value;
            const fileField = document.getElementById('file-field');
            const urlField = document.getElementById('url-field');

            if (type === 'file') {
                fileField.style.display = 'block';
                urlField.style.display = 'none';
                document.getElementById('url').value = ''; // Limpiar URL si se cambia a archivo
            } else {
                fileField.style.display = 'none';
                urlField.style.display = 'block';
                document.getElementById('file').value = ''; // Limpiar archivo si se cambia a enlace
            }
        }

        // Ejecutar al cargar la página para establecer el estado inicial
        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
</x-app-layout>
