<div class="p-6 text-gray-900">
    {{ __("¡Has iniciado sesión!") }}

    <div class="mt-4 flex flex-col space-y-3">
        <a href="{{ route('folders.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Ir a Gestión de Archivos y Carpetas
        </a>

        @if (Auth::user()->area && Auth::user()->area->name === 'Administración')
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Ir al Panel de Administración (Super Admin)
            </a>
        @elseif (Auth::user()->is_area_admin) {{-- Nuevo enlace para administradores de área --}}
            <a href="{{ route('area_admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Ir al Panel de Administración de mi Área
            </a>
        @endif
    </div>
</div>