<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Título principal con el color corporativo --}}
            <h3 class="text-2xl md:text-3xl font-bold text-[#2c3856] mb-6 px-4 sm:px-0">
                Opciones de Administración
            </h3>

            {{-- Grid responsivo para las tarjetas de opciones --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <a href="{{ route('admin.areas.index') }}" 
                   class="group bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-2">
                    
                    <div class="bg-gray-100 p-4 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                        <svg class="w-8 h-8 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                    </div>
                    
                    <h4 class="mt-4 text-lg font-semibold text-[#2c3856]">
                        Gestionar Áreas
                    </h4>
                    <p class="mt-2 text-sm text-[#666666]">
                        Crea, edita y elimina las áreas de la empresa.
                    </p>
                </a>

                <a href="{{ route('admin.users.index') }}" 
                   class="group bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-2">

                    <div class="bg-gray-100 p-4 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                        <svg class="w-8 h-8 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-4.663M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
                        </svg>
                    </div>

                    <h4 class="mt-4 text-lg font-semibold text-[#2c3856]">
                        Gestionar Usuarios
                    </h4>
                    <p class="mt-2 text-sm text-[#666666]">
                        Administra los roles y permisos de los usuarios del sistema.
                    </p>
                </a>
                
                <a href="{{ route('admin.organigram.index') }}" 
                   class="group bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-2">

                    <div class="bg-gray-100 p-4 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                        <svg class="w-8 h-8 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75H3a.75.75 0 0 1-.75-.75V5.25A.75.75 0 0 1 3 4.5ZM3 16.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75H3a.75.75 0 0 1-.75-.75V17.25a.75.75 0 0 1 .75-.75ZM19.5 4.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75H19.5a.75.75 0 0 1-.75-.75V5.25a.75.75 0 0 1 .75-.75ZM19.5 16.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75H19.5a.75.75 0 0 1-.75-.75V17.25a.75.75 0 0 1 .75-.75ZM11.25 4.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75V5.25a.75.75 0 0 1 .75-.75ZM8.25 10.5h7.5v.001a4.503 4.503 0 0 1-4.5 4.5h-1.5a4.503 4.503 0 0 1-4.5-4.5v-3.001A.75.75 0 0 1 3.75 6H6" />
                        </svg>
                    </div>

                    <h4 class="mt-4 text-lg font-semibold text-[#2c3856]">
                        Gestionar Organigrama
                    </h4>
                    <p class="mt-2 text-sm text-[#666666]">
                        Define la estructura, miembros y jerarquía de la organización.
                    </p>
                </a>

                {{-- TARJETA AÑADIDA --}}
                @if (Auth::user()->isSuperAdmin())
                <a href="{{ route('admin.ticket-categories.index') }}" 
                   class="group bg-white rounded-xl shadow-md p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-2">

                    <div class="bg-gray-100 p-4 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                        <svg class="w-8 h-8 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                    </div>

                    <h4 class="mt-4 text-lg font-semibold text-[#2c3856]">
                        Gestionar Categorías de Tickets
                    </h4>
                    <p class="mt-2 text-sm text-[#666666]">
                        Define los tipos de tickets para el sistema de soporte.
                    </p>
                </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>