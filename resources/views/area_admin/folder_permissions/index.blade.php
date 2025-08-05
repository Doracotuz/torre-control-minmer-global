<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestionar Permisos de Carpetas (Mi Área)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="fixed top-5 right-5 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]">
                            <div class="flex items-center"><svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><div><strong class="font-bold">{{ __('¡Éxito!') }}</strong><span class="block sm:inline ml-1">{{ session('success') }}</span></div></div>
                            <button @click="show = false" class="text-gray-400 hover:text-gray-700">&times;</button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="fixed top-5 right-5 z-50 bg-white border-l-4 border-red-600 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]">
                            <div class="flex items-center"><svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><div><strong class="font-bold">{{ __('¡Error!') }}</strong><span class="block sm:inline ml-1">{{ session('error') }}</span></div></div>
                            <button @click="show = false" class="text-gray-400 hover:text-gray-700">&times;</button>
                        </div>
                    @endif

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Selecciona una carpeta para gestionar sus permisos:</h3>


                    <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
                        @if ($folders->isEmpty())
                            <p class="text-center text-gray-500 py-4">No hay carpetas disponibles para gestionar permisos en esta área.</p>
                        @else
                            <ul class="space-y-2">
                                @foreach ($folders as $folder)
                                    {{-- Pasa la carpeta y los componentes de ruta iniciales al parcial --}}
                                    @include('area_admin.folder_permissions.partials.folder_item', ['folder' => $folder, 'level' => 0])
                                @endforeach
                            </ul>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>