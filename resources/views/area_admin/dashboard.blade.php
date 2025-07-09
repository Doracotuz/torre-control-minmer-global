<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Administración de Área') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Título principal con el color corporativo --}}
            <h3 class="text-2xl md:text-3xl font-bold text-[#2c3856] mb-6 px-4 sm:px-0">
                Panel de Gestión de tu Área
            </h3>

            {{-- Grid responsivo para las tarjetas de opciones --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <a href="{{ route('area_admin.users.index') }}" 
                   class="group bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                    
                    <div class="bg-gray-100 p-4 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                        <svg class="w-8 h-8 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m-7.5-2.962a3.75 3.75 0 1 0-7.5 0 3.75 3.75 0 0 0 7.5 0ZM10.5 1.5a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z" />
                        </svg>
                    </div>
                    
                    <h4 class="mt-4 text-lg font-semibold text-[#2c3856]">
                        Gestionar Usuarios del Área
                    </h4>
                    <p class="mt-2 text-sm text-[#666666]">
                        Asigna o remueve el acceso de los usuarios a tu área específica.
                    </p>
                </a>

                <a href="{{ route('area_admin.folder_permissions.index') }}" 
                   class="group bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-2">

                    <div class="bg-gray-100 p-4 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                        <svg class="w-8 h-8 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286Z" />
                        </svg>
                    </div>

                    <h4 class="mt-4 text-lg font-semibold text-[#2c3856]">
                        Gestionar Permisos
                    </h4>
                    <p class="mt-2 text-sm text-[#666666]">
                        Define qué roles pueden ver o editar las carpetas y archivos de tu área.
                    </p>
                </a>
                
            </div>
        </div>
    </div>
</x-app-layout>