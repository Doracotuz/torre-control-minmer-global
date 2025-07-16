<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Miembro del Organigrama: ') }} {{ $organigramMember->name }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Pre-procesa las trayectorias para Alpine.js --}}
            @php
                $trajectories = $organigramMember->trajectories->map(function ($t) {
                    return [
                        'id' => (int) $t->id, // Asegura que el ID sea un entero
                        'title' => htmlspecialchars($t->title ?? '', ENT_QUOTES, 'UTF-8'),
                        'description' => htmlspecialchars($t->description ?? '', ENT_QUOTES, 'UTF-8'),
                        'start_date' => optional($t->start_date)->format('Y-m-d') ?? '',
                        'end_date' => optional($t->end_date)->format('Y-m-d') ?? '',
                    ];
                });
            @endphp        
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-8">
                <form method="POST" action="{{ route('admin.organigram.update', $organigramMember) }}" enctype="multipart/form-data"
                    x-data="{
                        photoName: null,
                        // CAMBIO PARA S3: Inicializa photoPreview con la ruta S3 existente o null
                        photoPreview: '{{ $organigramMember->profile_photo_path ? Storage::url($organigramMember->profile_photo_path) : null }}',
                        trajectories: {{ $trajectories->toJson() }}, // Carga las trayectorias existentes
                        removingExistingPhoto: false // Para el checkbox de eliminar foto
                    }"
                    x-on:change="
                        if ($refs.photo && $refs.photo.files.length > 0) {
                            photoName = $refs.photo.files[0].name;
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                photoPreview = e.target.result;
                            };
                            reader.readAsDataURL(this.$refs.photo.files[0]); // CORRECCIÓN AQUÍ: Usar this.$refs.photo
                            removingExistingPhoto = false; // Desmarcar si se selecciona una nueva foto
                        } else {
                            photoName = null;
                            // CAMBIO PARA S3: Si no hay nueva foto y no se ha marcado para eliminar la existente, usa la URL de S3
                            if (!removingExistingPhoto && '{{ $organigramMember->profile_photo_path }}') {
                                photoPreview = '{{ Storage::url($organigramMember->profile_photo_path) }}';
                            } else { // Si se marcó para eliminar o no hay foto existente
                                photoPreview = null;
                            }
                        }
                    "
                >
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-[#2c3856] mb-4">{{ __('Información Personal') }}</h3>

                            <div class="flex flex-col items-center mb-6">
                                <x-input-label for="profile_photo" :value="__('Foto de Perfil')" class="mb-3 text-base font-medium" />
                                <div class="mt-2 mb-4">
                                    <template x-if="photoPreview">
                                        <img :src="photoPreview" class="h-24 w-24 rounded-full object-cover border-4 border-gray-200 shadow-md">
                                    </template>
                                    {{-- CAMBIO PARA S3: Muestra la foto existente desde S3 si no hay preview y no se ha marcado para eliminar --}}
                                    <template x-if="!photoPreview && '{{ $organigramMember->profile_photo_path }}' && !removingExistingPhoto">
                                        <img src="{{ Storage::url($organigramMember->profile_photo_path) }}" alt="{{ $organigramMember->name }}" class="h-24 w-24 rounded-full object-cover border-4 border-gray-200 shadow-md">
                                    </template>
                                    <template x-if="!photoPreview && !'{{ $organigramMember->profile_photo_path }}' || removingExistingPhoto">
                                        <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border-4 border-gray-300 shadow-md">
                                            <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                        </div>
                                    </template>
                                </div>
                                <input type="file" class="hidden" x-ref="photo" name="profile_photo" id="profile_photo" accept="image/*">
                                <label for="profile_photo" class="inline-flex items-center px-5 py-2 bg-[#ff9c00] text-white rounded-full font-semibold text-sm uppercase tracking-widest hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md cursor-pointer">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    <span x-text="photoName || 'Cambiar Foto'"></span>
                                </label>
                                <x-input-error class="mt-2" :messages="$errors->get('profile_photo') ?? []" />

                                @if ($organigramMember->profile_photo_path)
                                    <div class="mt-4">
                                        <label for="remove_profile_photo" class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="remove_profile_photo" id="remove_profile_photo" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500" x-model="removingExistingPhoto">
                                            <span class="ml-2 text-sm text-red-600 font-medium">{{ __('Eliminar foto de perfil actual') }}</span>
                                        </label>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-4">
                                <x-input-label for="name" :value="__('Nombre Completo')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $organigramMember->name)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('name') ?? []" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $organigramMember->email)" />
                                <x-input-error class="mt-2" :messages="$errors->get('email') ?? []" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="cell_phone" :value="__('Celular')" />
                                <x-text-input id="cell_phone" name="cell_phone" type="text" class="mt-1 block w-full" :value="old('cell_phone', $organigramMember->cell_phone)" />
                                <x-input-error class="mt-2" :messages="$errors->get('cell_phone') ?? []" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="position_id" :value="__('Posición')" />
                                <select id="position_id" name="position_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Selecciona una Posición</option>
                                    @foreach ($positions as $position)
                                        <option value="{{ $position->id }}" {{ old('position_id', $organigramMember->position_id) == $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('position_id') ?? []" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="area_id" :value="__('Área')" />
                                <select id="area_id" name="area_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Selecciona un Área</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}" {{ old('area_id', $organigramMember->area_id) == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('area_id') ?? []" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="manager_id" :value="__('Jefe Directo (Opcional)')" />
                                <select id="manager_id" name="manager_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Ninguno</option>
                                    @foreach ($managers as $manager)
                                        <option value="{{ $manager->id }}" {{ old('manager_id', $organigramMember->manager_id) == $manager->id ? 'selected' : '' }}>
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
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @forelse ($activities as $activity)
                                        <label for="activity_{{ $activity->id }}" class="inline-flex items-center">
                                            <input type="checkbox" name="activities_ids[]" id="activity_{{ $activity->id }}" value="{{ $activity->id }}" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]"
                                                {{ in_array($activity->id, old('activities_ids', $memberActivitiesIds)) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700">{{ $activity->name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-gray-500 col-span-2">No hay actividades registradas. Crea algunas desde "Gestionar Actividades".</p>
                                    @endforelse
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('activities_ids') ?? []" />
                            </div>

                            <div class="mb-6">
                                <x-input-label :value="__('Habilidades')" class="mb-2" />
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @forelse ($skills as $skill)
                                        <label for="skill_{{ $skill->id }}" class="inline-flex items-center">
                                            <input type="checkbox" name="skills_ids[]" id="skill_{{ $skill->id }}" value="{{ $skill->id }}" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]"
                                                {{ in_array($skill->id, old('skills_ids', $memberSkillsIds)) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700">{{ $skill->name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-gray-500 col-span-2">No hay habilidades registradas. Crea algunas desde "Gestionar Habilidades".</p>
                                    @endforelse
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('skills_ids') ?? []" />
                            </div>

                            <div class="mb-6">
                                <h4 class="text-base font-semibold text-[#2c3856] mb-2">{{ __('Trayectoria Profesional') }}</h4>
                                <template x-for="(trajectory, index) in trajectories" :key="index">
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
                            {{ __('Actualizar Miembro') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>