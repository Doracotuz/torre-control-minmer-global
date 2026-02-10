<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Editar Área') }} / <span class="text-gray-500">{{ $area->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                
                <form method="POST" action="{{ route('admin.areas.update', $area) }}" enctype="multipart/form-data" class="p-6 md:p-8">
                    @csrf
                    @method('PUT')

                    {{-- grid para un layout responsivo --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        {{-- columna para campos de texto --}}
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="name" :value="__('Nombre del Área')" class="font-semibold" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $area->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Descripción (Opcional)')" class="font-semibold" />
                                <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm">{{ old('description', $area->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <h3 class="text-md font-semibold text-gray-700 mb-3">Datos de Emisor / Contacto</h3>
                                
                                <div class="mb-4">
                                    <label for="is_client" class="inline-flex items-center">
                                        <input id="is_client" type="checkbox" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]" name="is_client" value="1" {{ old('is_client', $area->is_client) ? 'checked' : '' }}>
                                        <span class="ms-2 text-sm font-semibold text-gray-700">{{ __('¿Es un Cliente?') }}</span>
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1 ml-6">Marcar si esta área representa a un cliente externo.</p>
                                </div>
                                
                                <div>
                                    <x-input-label for="storage_rate" :value="__('Tarifa de Almacenaje (Diaria)')" class="font-semibold" />
                                    <x-text-input id="storage_rate" class="block mt-1 w-full" type="number" step="0.01" name="storage_rate" :value="old('storage_rate', $area->storage_rate)" placeholder="15.00" />
                                    <x-input-error :messages="$errors->get('storage_rate')" class="mt-2" />
                                    <p class="text-xs text-gray-500 mt-1">Costo por pallet por día.</p>
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="emitter_name" :value="__('Razón Social')" />
                                    <x-text-input id="emitter_name" class="block mt-1 w-full" type="text" name="emitter_name" :value="old('emitter_name', $area->emitter_name)" />
                                    <x-input-error :messages="$errors->get('emitter_name')" class="mt-2" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="emitter_phone" :value="__('Teléfono')" />
                                    <x-text-input id="emitter_phone" class="block mt-1 w-full" type="text" name="emitter_phone" :value="old('emitter_phone', $area->emitter_phone)" />
                                    <x-input-error :messages="$errors->get('emitter_phone')" class="mt-2" />
                                </div>

                                <div class="mt-4">
                                    <x-input-label for="emitter_address" :value="__('Dirección (Calle y Número)')" />
                                    <x-text-input id="emitter_address" class="block mt-1 w-full" type="text" name="emitter_address" :value="old('emitter_address', $area->emitter_address)" />
                                    <x-input-error :messages="$errors->get('emitter_address')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <x-input-label for="emitter_colonia" :value="__('Colonia')" />
                                        <x-text-input id="emitter_colonia" class="block mt-1 w-full" type="text" name="emitter_colonia" :value="old('emitter_colonia', $area->emitter_colonia)" />
                                        <x-input-error :messages="$errors->get('emitter_colonia')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="emitter_cp" :value="__('C.P.')" />
                                        <x-text-input id="emitter_cp" class="block mt-1 w-full" type="text" name="emitter_cp" :value="old('emitter_cp', $area->emitter_cp)" />
                                        <x-input-error :messages="$errors->get('emitter_cp')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- columna para icono del área --}}
                        <div class="space-y-6">
                            <div>
                                <x-input-label :value="__('Icono del Área (Opcional)')" class="font-semibold mb-3 text-center" />
                                
                                <div class="flex flex-col items-center space-y-4">
                                    {{-- previsualización del icono actual --}}
                                    <div class="w-32 h-32 rounded-full bg-gray-100 border-4 border-gray-200 flex items-center justify-center overflow-hidden shadow-md">
                                        <img src="{{ $area->icon_path ? Storage::disk('s3')->url($area->icon_path) : Storage::disk('s3')->url('images/placeholder_user.jpg') }}" 
                                             alt="{{ $area->name }} Icon" 
                                             class="w-full h-full object-cover" 
                                             id="iconPreview">
                                    </div>

                                    <label for="icon" class="inline-flex items-center px-5 py-2 bg-[#ff9c00] text-white rounded-full font-semibold text-xs uppercase tracking-widest hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        <span id="iconName">Seleccionar Icono</span>
                                    </label>

                                    <input id="icon" name="icon" type="file" class="hidden" 
                                           onchange="
                                                const file = this.files[0];
                                                if (file) {
                                                    document.getElementById('iconName').textContent = file.name;
                                                    const reader = new FileReader();
                                                    reader.onload = (e) => { document.getElementById('iconPreview').src = e.target.result; };
                                                    reader.readAsDataURL(file);
                                                }
                                           ">
                                    <p class="mt-1 text-xs text-gray-500">{{ __('JPG, PNG, GIF, SVG. Máx: 2MB.') }}</p>
                                    <x-input-error :messages="$errors->get('icon')" class="mt-2" />
                                    
                                    {{-- opción para eliminar el icono --}}
                                    @if ($area->icon_path)
                                        <div class="pt-2">
                                            <label for="remove_icon" class="inline-flex items-center">
                                                <input id="remove_icon" type="checkbox" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500" name="remove_icon" value="1">
                                                <span class="ms-2 text-sm text-red-600">{{ __('Eliminar icono actual') }}</span>
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 border-t pt-6">
                        <a href="{{ route('admin.areas.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline mr-6">
                            Cancelar
                        </a>
                        <x-primary-button>
                            {{ __('Actualizar Área') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>