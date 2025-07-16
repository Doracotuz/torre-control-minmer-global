<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Editar Área') }} / <span class="text-gray-500">{{ $area->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                
                <form method="POST" action="{{ route('admin.areas.update', $area) }}" enctype="multipart/form-data" class="p-6 md:p-8">
                    @csrf
                    @method('PUT')

                    {{-- Usamos un grid para un layout responsivo --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        {{-- Columna 1: Campos de texto --}}
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
                        </div>

                        {{-- Columna 2: Icono del Área --}}
                        <div class="space-y-6">
                            <div>
                                <x-input-label :value="__('Icono del Área (Opcional)')" class="font-semibold mb-3 text-center" />
                                
                                <div class="flex flex-col items-center space-y-4">
                                    {{-- Previsualización del Icono Actual --}}
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
                                    
                                    {{-- Opción para eliminar el icono --}}
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