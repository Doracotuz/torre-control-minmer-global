<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Crear Nuevo Rol') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-6 md:p-8">

                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Rol</label>
                        <input type="text" name="name" id="name" required class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm" value="{{ old('name') }}" placeholder="Ej: Jefe de Almacén">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-[#2c3856] mb-4 border-b pb-2">Permisos del Rol</h3>
                        
                        <div class="space-y-6">
                            @foreach(\App\Models\User::getGroupedPermissions() as $moduleName => $subGroups)
                                <div x-data="{ open: false }" class="border border-gray-200 rounded-2xl bg-white overflow-hidden shadow-sm transition-all duration-300 hover:shadow-md">
                                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between p-5 bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-8 rounded-full {{ $moduleName === 'WMS' ? 'bg-[#ff9c00]' : 'bg-[#2c3856]' }}"></div>
                                            <h3 class="text-lg font-bold text-[#2c3856] uppercase tracking-wide">{{ $moduleName }}</h3>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-gray-400 font-medium" x-show="!open">Expandir</span>
                                            <span class="text-xs text-gray-400 font-medium" x-show="open">Contraer</span>
                                            <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-300" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </div>
                                    </button>
                                    
                                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 bg-white">
                                            @foreach($subGroups as $groupName => $permissions)
                                                <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                                    <div class="bg-gray-50/50 px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                                        <h4 class="font-bold text-xs uppercase tracking-widest text-[#2c3856]">{{ $groupName }}</h4>
                                                    </div>
                                                    <div class="p-4 space-y-3">
                                                        @foreach($permissions as $key => $label)
                                                            <label class="flex items-start cursor-pointer group">
                                                                <div class="relative flex items-start mt-0.5">
                                                                    <input type="checkbox" name="permissions[]" value="{{ $key }}" class="peer sr-only" 
                                                                        {{ (is_array(old('permissions')) && in_array($key, old('permissions'))) ? 'checked' : '' }}>
                                                                    <div class="w-4 h-4 border-2 border-gray-300 rounded bg-white peer-checked:bg-[#ff9c00] peer-checked:border-[#ff9c00] flex items-center justify-center transition-all">
                                                                        <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                                    </div>
                                                                </div>
                                                                <span class="ml-3 text-sm text-gray-600 group-hover:text-[#2c3856] transition-colors leading-tight select-none">{{ $label }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('admin.roles.index') }}" class="mr-4 text-sm text-gray-600 hover:text-gray-900 underline">Cancelar</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#2c3856] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1a2233] active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Crear Rol
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
