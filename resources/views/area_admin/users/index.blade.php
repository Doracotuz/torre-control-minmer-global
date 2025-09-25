<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            Gestión de Usuarios: <span class="text-[#ff9c00]">{{ $currentArea->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-[#E8ECF7]">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-6 md:p-8">

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

                <div class="mb-8 p-4 bg-gray-50 rounded-lg border">
                    <form action="{{ route('area_admin.users.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                                <input type="text" name="search" id="search" class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm text-sm" placeholder="Nombre, email, posición..." value="{{ $filters['search'] ?? '' }}">
                            </div>

                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700">Rol de Usuario</label>
                                <select name="role" id="role" class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm text-sm">
                                    <option value="">Todos los roles</option>
                                    <option value="admin" @selected(isset($filters['role']) && $filters['role'] == 'admin')>Admin. de Área</option>
                                    <option value="normal" @selected(isset($filters['role']) && $filters['role'] == 'normal')>Usuario Normal</option>
                                </select>
                            </div>

                            <div class="flex items-end space-x-2">
                                <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#2c3856] hover:bg-[#1a2233]">
                                    Filtrar
                                </button>
                                <a href="{{ route('area_admin.users.index') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>                
                <div x-data="{ view: localStorage.getItem('users_view_mode') || 'grid' }" x-init="$watch('view', val => localStorage.setItem('users_view_mode', val))">

                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                        <div class="inline-flex rounded-lg shadow-sm">
                            <button @click="view = 'grid'" :class="{ 'bg-[#2c3856] text-white': view === 'grid', 'bg-white text-gray-600 hover:bg-gray-50': view !== 'grid' }" class="px-4 py-2 text-sm font-semibold border border-gray-200 rounded-l-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </button>
                            <button @click="view = 'list'" :class="{ 'bg-[#2c3856] text-white': view === 'list', 'bg-white text-gray-600 hover:bg-gray-50': view !== 'list' }" class="px-4 py-2 text-sm font-semibold border-t border-b border-r border-gray-200 rounded-r-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            </button>
                        </div>
                        <a href="{{ route('area_admin.users.create') }}" class="inline-flex items-center px-5 py-2 bg-[#2c3856] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#ff9c00] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] transition-all duration-300 transform hover:scale-105 shadow-md w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Crear Usuario
                        </a>
                    </div>

                    <div x-show="view === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                        @forelse ($users as $user)
                            <div class="bg-white rounded-lg shadow border border-gray-100 p-4 flex flex-col text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                                <div class="flex-grow flex flex-col items-center">
                                    <img class="h-20 w-20 rounded-full object-cover mb-3 border-4
                                        {{ $user->is_area_admin ? 'border-[#ff9c00]' : 'border-gray-200' }}"
                                        src="{{ $user->profile_photo_path ? Storage::disk('s3')->url($user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=2C3856&background=F3F4F6' }}"
                                        alt="{{ $user->name }}">
                                    <h5 class="text-base font-bold text-[#2c3856]">{{ $user->name }}</h5>
                                    <p class="text-xs font-medium text-gray-500 mb-1 truncate w-full" title="{{ $user->position }}">{{ $user->position ?? 'Sin Posición' }}</p>
                                    <p class="text-xs font-medium text-gray-500 mb-1 truncate w-full" title="{{ $user->phone }}">{{ $user->phone_number ?? 'Sin Teléfono' }}</p>                                    
                                    <p class="text-xs text-gray-500 mb-2 truncate w-full">{{ $user->email }}</p>
                                    @if ($user->is_area_admin)
                                        <span class="mt-2 px-2 py-0.5 inline-flex text-xxs leading-5 font-semibold rounded-full bg-orange-100 text-[#ff9c00]">Admin. de Área</span>
                                    @else
                                        <span class="mt-2 px-2 py-0.5 inline-flex text-xxs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Usuario</span>
                                    @endif
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-center space-x-2">
                                    <a href="{{ route('area_admin.users.edit', $user) }}" class="text-sm text-gray-500 hover:text-[#2c3856] p-1">Editar</a>
                                    <form action="{{ route('area_admin.users.destroy', $user) }}" method="POST" 
                                        onsubmit="return confirm('¿Confirmas la eliminación de \'{{ addslashes($user->name) }}\'? El usuario será eliminado permanentemente. Su historial de auditoría se conservará, pero se desvinculará de su nombre (quedará como NULL).');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600 p-1">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="col-span-full text-center text-gray-500 py-12">No hay usuarios para mostrar.</p>
                        @endforelse
                    </div>

                    <div x-show="view === 'list'" class="overflow-x-auto bg-white rounded-lg shadow border">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-[#2c3856]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Posición</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Teléfono</th>                                    
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Rol</th>
                                    <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img class="h-9 w-9 rounded-full object-cover mr-3" src="{{ $user->profile_photo_path ? Storage::disk('s3')->url($user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=2C3856&background=F3F4F6' }}" alt="{{ $user->name }}">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500">{{ $user->position ?? 'Sin posición' }}</div></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500">{{ $user->phone_number ?? 'Sin teléfono' }}</div></td>                                        
                                        <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500">{{ $user->email }}</div></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($user->is_area_admin)
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-[#ff9c00]">Admin. de Área</span>
                                            @else
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Usuario</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-4">
                                                <a href="{{ route('area_admin.users.edit', $user) }}" class="text-[#2c3856] hover:text-[#ff9c00] font-semibold">Editar</a>
                                                <form action="{{ route('area_admin.users.destroy', $user) }}" method="POST" 
                                                    onsubmit="return confirm('¿Confirmas la eliminación de \'{{ addslashes($user->name) }}\'? El usuario será eliminado permanentemente. Su historial de auditoría se conservará, pero se desvinculará de su nombre (quedará como NULL).');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm text-gray-500 hover:text-red-600 p-1">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No hay usuarios registrados en esta área.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>