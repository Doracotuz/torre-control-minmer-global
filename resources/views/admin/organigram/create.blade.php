<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Añadir Nuevo Miembro al Organigrama') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#E8ECF7]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div x-data="{
                photoName: null,
                photoPreview: null,
                trajectories: [],
                openCsvUploadModal: false
            }">

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-8">
                    
                    <form method="POST" action="{{ route('admin.organigram.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-[#2c3856] mb-4">{{ __('Información Personal') }}</h3>

                                <div class="flex flex-col items-center mb-6">
                                    <x-input-label for="profile_photo" :value="__('Foto de Perfil')" class="mb-3 text-base font-medium" />
                                    <div class="mt-2 mb-4">
                                        <template x-if="photoPreview">
                                            <img :src="photoPreview" class="h-24 w-24 rounded-full object-cover border-4 border-gray-200 shadow-md">
                                        </template>
                                        <template x-if="!photoPreview">
                                            <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border-4 border-gray-300 shadow-md">
                                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                            </div>
                                        </template>
                                    </div>

                                    <input type="file" class="hidden" x-ref="photo" name="profile_photo" id="profile_photo" accept="image/*"
                                        x-on:change="
                                            if ($refs.photo.files.length > 0) {
                                                photoName = $refs.photo.files[0].name;
                                                const reader = new FileReader();
                                                reader.onload = (e) => {
                                                    photoPreview = e.target.result;
                                                };
                                                reader.readAsDataURL($refs.photo.files[0]);
                                            } else {
                                                photoName = null;
                                                photoPreview = null;
                                            }
                                        ">

                                    <label for="profile_photo" class="inline-flex items-center px-5 py-2 bg-[#ff9c00] text-white rounded-full font-semibold text-sm uppercase tracking-widest hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span x-text="photoName || 'Seleccionar Foto'"></span>
                                    </label>
                                    <x-input-error class="mt-2" :messages="$errors->get('profile_photo') ?? []" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="name" :value="__('Nombre Completo')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('name') ?? []" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('email') ?? []" />
                                </div>
                                <div class="mt-4 border-t pt-4">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="create_user_account" value="1" checked 
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-gray-700 font-semibold">Crear cuenta de usuario para este miembro</span>
                                    </label>
                                    <p class="text-sm text-gray-500 ml-6">
                                        Esto generará un usuario para que pueda iniciar sesión en el sistema y le enviará una notificación de bienvenida.
                                    </p>
                                </div>                                

                                <div class="mb-4">
                                    <x-input-label for="cell_phone" :value="__('Celular')" />
                                    <x-text-input id="cell_phone" name="cell_phone" type="text" class="mt-1 block w-full" :value="old('cell_phone')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('cell_phone') ?? []" />
                                </div>
                                
                                <div class="mb-4">
                                    <x-input-label for="position_id" :value="__('Posición')" />
                                    <select id="position_id" name="position_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Selecciona una Posición</option>
                                        @foreach ($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('position_id') ?? []" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="area_id" :value="__('Área')" />
                                    <select id="area_id" name="area_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Selecciona un Área</option>
                                        @foreach ($areas as $area)
                                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('area_id') ?? []" />
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="manager_id" :value="__('Jefe Directo')" />
                                    <select id="manager_id" name="manager_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Ninguno</option>
                                        @foreach ($managers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }} 
                                                ({{ $manager->position->name ?? 'Sin Posición' }}) 
                                                - Área: {{ $manager->area->name ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('manager_id') ?? []" />
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-[#2c3856] mb-4">{{ __('Detalles Adicionales') }}</h3>

                                <div class="mb-6">
                                    <x-input-label :value="__('Actividades')" class="mb-2" />
                                    <div class="max-h-56 overflow-y-auto p-4 border border-gray-200 rounded-md">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @forelse ($activities as $activity)
                                                <label for="activity_{{ $activity->id }}" class="inline-flex items-center">
                                                    <input type="checkbox" name="activities_ids[]" id="activity_{{ $activity->id }}" value="{{ $activity->id }}" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]"
                                                        {{ in_array($activity->id, old('activities_ids', [])) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-gray-700">{{ $activity->name }}</span>
                                                </label>
                                            @empty
                                                <p class="text-sm text-gray-500 col-span-2">No hay actividades registradas. Crea algunas desde "Gestionar Actividades".</p>
                                            @endforelse
                                        </div>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('activities_ids') ?? []" />
                                </div>

                                <div class="mb-6">
                                    <x-input-label :value="__('Habilidades')" class="mb-2" />
                                    <div class="max-h-56 overflow-y-auto p-4 border border-gray-200 rounded-md">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @forelse ($skills as $skill)
                                                <label for="skill_{{ $skill->id }}" class="inline-flex items-center">
                                                    <input type="checkbox" name="skills_ids[]" id="skill_{{ $skill->id }}" value="{{ $skill->id }}" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]"
                                                        {{ in_array($skill->id, old('skills_ids', [])) ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-gray-700">{{ $skill->name }}</span>
                                                </label>
                                            @empty
                                                <p class="text-sm text-gray-500 col-span-2">No hay habilidades registradas. Crea algunas desde "Gestionar Habilidades".</p>
                                            @endforelse
                                        </div>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('skills_ids') ?? []" />
                                </div>

                                <div class="mb-6">
                                    <h4 class="text-base font-semibold text-[#2c3856] mb-2">{{ __('Trayectoria Profesional') }}</h4>
                                    <template x-for="(trajectory, index) in trajectories" :key="trajectory.id ? trajectory.id : index">
                                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm mb-3 border border-gray-200">
                                            <div class="flex justify-end">
                                                <button type="button" @click="trajectories.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">Eliminar</button>
                                            </div>
                                            <input type="hidden" x-bind:name="'trajectories[' + index + '][id]'" x-bind:value="trajectory.id">
                                            <div class="mb-2">
                                                <x-input-label x-bind:for="'trajectory_title_' + index" :value="__('Título del Puesto')" />
                                                <x-text-input x-bind:id="'trajectory_title_' + index" x-bind:name="'trajectories[' + index + '][title]'" type="text" class="mt-1 block w-full" x-model="trajectory.title" required />
                                                <x-input-error class="mt-2" x-bind:messages="$errors->get('trajectories.' + index + '.title') ?? []" />
                                            </div>
                                            <div class="mb-2">
                                                <x-input-label x-bind:for="'trajectory_description_' + index" :value="__('Descripción')" />
                                                <textarea x-bind:id="'trajectory_description_' + index" x-bind:name="'trajectories[' + index + '][description]'" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" x-model="trajectory.description"></textarea>
                                                <x-input-error class="mt-2" x-bind:messages="$errors->get('trajectories.' + index + '.description') ?? []" />
                                            </div>
                                            <div class="grid grid-cols-2 gap-4 mb-2">
                                                <div>
                                                    <x-input-label x-bind:for="'trajectory_start_date_' + index" :value="__('Fecha Inicio')" />
                                                    <x-text-input x-bind:id="'trajectory_start_date_' + index" x-bind:name="'trajectories[' + index + '][start_date]'" type="date" class="mt-1 block w-full" x-model="trajectory.start_date" />
                                                    <x-input-error class="mt-2" x-bind:messages="$errors->get('trajectories.' + index + '.start_date') ?? []" />
                                                </div>
                                                <div>
                                                    <x-input-label x-bind:for="'trajectory_end_date_' + index" :value="__('Fecha Fin (Opcional)')" />
                                                    <x-text-input x-bind:id="'trajectory_end_date_' + index" x-bind:name="'trajectories[' + index + '][end_date]'" type="date" class="mt-1 block w-full" x-model="trajectory.end_date" />
                                                    <x-input-error class="mt-2" x-bind:messages="$errors->get('trajectories.' + index + '.end_date') ?? []" />
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" @click="trajectories.push({ id: null, title: '', description: '', start_date: '', end_date: '' })" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mt-3">
                                        {{ __('Añadir Trayectoria') }}
                                    </button>
                                    <x-input-error class="mt-2" :messages="$errors->get('trajectories') ?? []" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.organigram.index') }}" class="inline-flex items-center px-5 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md mr-4">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Crear Miembro') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
                
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-8 mt-6 flex justify-end items-center space-x-4">
                    <a href="{{ route('admin.organigram.download-template') }}" class="inline-flex items-center px-5 py-2 bg-blue-500 text-white rounded-full font-semibold text-xs uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        {{ __('Descargar Template CSV') }}
                    </a>
                    <button type="button" @click="openCsvUploadModal = true" class="inline-flex items-center px-5 py-2 bg-green-500 text-white rounded-full font-semibold text-xs uppercase tracking-widest hover:bg-green-600 focus:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path></svg>
                        {{ __('Cargar por CSV') }}
                    </button>
                </div>
            
                <div x-cloak x-show="openCsvUploadModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="openCsvUploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div x-show="openCsvUploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            {{ __('Carga Masiva de Miembros') }}
                                        </h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500">
                                                {{ __('Selecciona un archivo CSV para cargar masivamente los miembros del organigrama. Asegúrate de que el archivo siga el formato de la plantilla descargable.') }}
                                            </p>
                                            <p class="text-sm text-red-500 mt-2">
                                                {{ __('Importante: La columna "position_name" debe coincidir exactamente con una posición ya existente. Las actividades y habilidades se crearán si no existen.') }}
                                            </p>
                                        </div>
                                        <form id="csv-upload-form" action="{{ route('admin.organigram.import-csv') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                                            @csrf
                                            <div class="mb-4">
                                                <x-input-label for="csv_file" :value="__('Archivo CSV')" />
                                                {{-- Botón de selección de archivo estilizado --}}
                                                <label for="csv_file" class="w-full flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-md cursor-pointer hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                    <span x-text="csvFileName || 'Seleccionar Archivo CSV'"></span>
                                                </label>
                                                <input type="file" class="hidden" x-ref="csvFile" name="csv_file" id="csv_file" accept=".csv, .txt" required
                                                    x-on:change="csvFileName = $refs.csvFile.files.length > 0 ? $refs.csvFile.files[0].name : 'Seleccionar Archivo CSV';">
                                                <x-input-error class="mt-2" :messages="$errors->get('csv_file') ?? []" />
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" form="csv-upload-form" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    {{ __('Cargar') }}
                                </button>
                                <button type="button" @click="openCsvUploadModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                    {{ __('Cancelar') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>
