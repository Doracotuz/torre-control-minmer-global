<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Crear Nuevo Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#E8ECF7]">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                
                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="p-6 md:p-8"
                      x-data="{ 
                          photoName: null, 
                          photoPreview: null, 
                          isClient: {{ old('is_client', 'false') ? 'true' : 'false' }},
                          selectedFolderIds: @json(old('accessible_folder_ids', [])),
                          folders: [],
                          loadingFolders: true,
                          loadFolders(parentId = null) {
                              this.loadingFolders = true;
                              fetch('{{ route('admin.api.folders_for_client_access') }}?parent_id=' + (parentId || ''))
                                  .then(response => response.json())
                                  .then(data => {
                                      this.folders = data.map(folder => ({ ...folder, isOpen: false, children: [] }));
                                      this.loadingFolders = false;
                                  })
                                  .catch(error => {
                                      console.error('Error loading folders:', error);
                                      this.loadingFolders = false;
                                  });
                          },
                          toggleFolder(folder) {
                              folder.isOpen = !folder.isOpen;
                              if (folder.isOpen && folder.children.length === 0 && folder.has_children) {
                                  this.loadingFolders = true;
                                  fetch('{{ route('admin.api.folders_for_client_access') }}?parent_id=' + folder.id)
                                      .then(response => response.json())
                                  .then(data => {
                                      this.folders = data.map(child => ({ ...child, isOpen: false, children: [] }));
                                      this.loadingFolders = false;
                                  })
                                  .catch(error => {
                                      console.error('Error loading subfolders:', error);
                                      this.loadingFolders = false;
                                  });
                              }
                          },
                          init() {
                              this.loadFolders();
                              // Si ya es cliente por old('is_client'), deselecciona el área_id si no hay uno
                              // y deshabilita el campo.
                              if (this.isClient) {
                                  let areaSelect = document.getElementById('area_id'); // Usar 'let' o ninguna palabra clave
                                  if (areaSelect) {
                                      areaSelect.value = ''; // O null
                                      areaSelect.disabled = true;
                                  }
                              }
                          }
                      }">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        <div class="space-y-6">
                            <div>
                                <x-input-label for="name" :value="__('Nombre')" class="font-semibold" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" class="font-semibold" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password" :value="__('Contraseña')" class="font-semibold" />
                                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" class="font-semibold" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <x-input-label :value="__('Foto de Perfil (Opcional)')" class="font-semibold mb-3 text-center" />
                                
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="w-32 h-32 rounded-full bg-gray-100 border-4 border-gray-200 flex items-center justify-center overflow-hidden shadow-md">
                                        <template x-if="!photoPreview">
                                            <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                        </template>
                                        <template x-if="photoPreview">
                                            <img :src="photoPreview" class="w-full h-full object-cover">
                                        </template>
                                    </div>

                                    <label for="profile_photo" class="inline-flex items-center px-5 py-2 bg-[#ff9c00] text-white rounded-full font-semibold text-xs uppercase tracking-widest hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span x-text="photoName || 'Seleccionar Foto'"></span>
                                    </label>

                                    <input id="profile_photo" name="profile_photo" type="file" class="hidden" x-ref="photo"
                                           x-on:change="
                                                photoName = $refs.photo.files.length > 0 ? $refs.photo.files[0].name : null;
                                                if (photoName) {
                                                    const reader = new FileReader();
                                                    reader.onload = (e) => { photoPreview = e.target.result; };
                                                    reader.readAsDataURL($refs.photo.files[0]);
                                                } else {
                                                    photoPreview = null;
                                                }
                                           ">
                                    <p class="mt-1 text-xs text-gray-500">{{ __('JPG, PNG, GIF. Máx: 2MB.') }}</p>
                                    <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                                </div>
                            </div>
                            
                            {{-- Campo de Área, visible si NO es cliente --}}
                                <div x-show="!isClient" x-transition.opacity>
                                    <x-input-label for="area_id" :value="__('Área')" class="font-semibold" />
                                    <select id="area_id" name="area_id" class="block mt-1 w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm" required>
                                        <option value="">{{ __('Selecciona un Área') }}</option>
                                        @foreach ($areas as $area)
                                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('area_id')" class="mt-2" />
                                </div>
                            
                            {{-- Campo de Área, si es cliente y no se seleccionó área --}}
                            {{-- Esto es para el caso cuando el area_id no es requerido para clientes y se envía como null --}}
                            <div class="pt-2">
                                <label for="is_area_admin" class="inline-flex items-center">
                                    <input id="is_area_admin" type="checkbox" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]" name="is_area_admin" value="1" {{ old('is_area_admin') ? 'checked' : '' }}>
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Asignar como Administrador de Área') }}</span>
                                </label>
                            </div>  

                            {{-- NUEVO CAMPO PARA IS_CLIENT --}}
                            <div class="pt-2">
                                <label for="is_client" class="inline-flex items-center">
                                <input type="checkbox" name="is_client" id="is_client" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]" value="1" x-model="isClient"
                                    @change="
                                        // CORRECCIÓN: Accede directamente al elemento, sin 'let' ni 'const'
                                        document.getElementById('area_id').disabled = isClient;
                                        if (isClient) {
                                            document.getElementById('area_id').value = ''; // Deselecciona el área si se convierte en cliente
                                        } else {
                                            // Solo si en 'edit.blade.php', para reestablecer el valor original si se desmarca
                                            // document.getElementById('area_id').value = '{{ $user->area_id ?? '' }}'; 
                                        }
                                    ">
                                <span class="ms-2 text-sm text-gray-600">{{ __('Asignar como Usuario Cliente') }}</span>
                                </label>
                            </div>

                            {{-- EXPLORADOR DE CARPETAS INTERACTIVO --}}
                            <div x-show="isClient" x-transition.opacity class="mt-4">
                                <x-input-label :value="__('Carpetas Accesibles (para clientes)')" class="font-semibold mb-2" />
                                <div class="border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto bg-white">
                                    <p x-show="loadingFolders" class="text-sm text-gray-500">Cargando carpetas...</p>
                                    <ul class="space-y-1">
                                        <template x-for="folder in folders" :key="folder.id">
                                            <li class="flex items-start">
                                                <div class="flex items-center">
                                                    <template x-if="folder.has_children">
                                                        <button type="button" @click.prevent="toggleFolder(folder)" class="mr-1 text-gray-500 hover:text-gray-700 focus:outline-none">
                                                            <svg x-show="!folder.isOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                            <svg x-show="folder.isOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                        </button>
                                                    </template>
                                                    <template x-if="!folder.has_children">
                                                        <span class="mr-1 w-4 h-4 inline-block"></span>
                                                    </template>
                                                    <input type="checkbox" :id="'folder_' + folder.id" :value="folder.id" x-model="selectedFolderIds" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] mr-2">
                                                    <label :for="'folder_' + folder.id" x-text="folder.name" class="text-sm text-gray-700 cursor-pointer"></label>
                                                </div>
                                                <ul x-show="folder.isOpen" x-transition.opacity class="ml-6 mt-1 w-full space-y-1">
                                                    <template x-for="childFolder in folder.children" :key="childFolder.id">
                                                        <li class="flex items-start">
                                                            <div class="flex items-center">
                                                                <template x-if="childFolder.has_children">
                                                                    <button type="button" @click.prevent="toggleFolder(childFolder)" class="mr-1 text-gray-500 hover:text-gray-700 focus:outline-none">
                                                                        <svg x-show="!childFolder.isOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                                        <svg x-show="childFolder.isOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                    </button>
                                                                </template>
                                                                <template x-if="!childFolder.has_children">
                                                                    <span class="mr-1 w-4 h-4 inline-block"></span>
                                                                </template>
                                                                <input type="checkbox" :id="'folder_' + childFolder.id" :value="childFolder.id" x-model="selectedFolderIds" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] mr-2">
                                                                <label :for="'folder_' + childFolder.id" x-text="childFolder.name" class="text-sm text-gray-700 cursor-pointer"></label>
                                                            </div>
                                                            <ul x-show="childFolder.isOpen" x-transition.opacity class="ml-6 mt-1 w-full space-y-1">
                                                                <template x-for="grandchildFolder in childFolder.children" :key="grandchildFolder.id">
                                                                    <li class="flex items-start">
                                                                        <div class="flex items-center">
                                                                            <template x-if="grandchildFolder.has_children">
                                                                                <button type="button" @click.prevent="toggleFolder(grandchildFolder)" class="mr-1 text-gray-500 hover:text-gray-700 focus:outline-none">
                                                                                    <svg x-show="!grandchildFolder.isOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                                                    <svg x-show="grandchildFolder.isOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                                                </button>
                                                                            </template>
                                                                            <template x-if="!grandchildFolder.has_children">
                                                                                <span class="mr-1 w-4 h-4 inline-block"></span>
                                                                            </template>
                                                                            <input type="checkbox" :id="'folder_' + grandchildFolder.id" :value="grandchildFolder.id" x-model="selectedFolderIds" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] mr-2">
                                                                            <label :for="'folder_' + grandchildFolder.id" x-text="grandchildFolder.name" class="text-sm text-gray-700 cursor-pointer"></label>
                                                                        </div>
                                                                    </li>
                                                                </template>
                                                            </ul>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <input type="hidden" name="accessible_folder_ids[]" :value="selectedFolderIds.join(',')">
                                <p class="mt-1 text-xs text-gray-500">Selecciona las carpetas a las que este usuario cliente tendrá acceso.</p>
                                <x-input-error :messages="$errors->get('accessible_folder_ids')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 border-t pt-6">
                        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline mr-6">
                            Cancelar
                        </a>
                        <x-primary-button>
                            {{ __('Crear Usuario') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>