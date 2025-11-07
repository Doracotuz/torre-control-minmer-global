<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Gestión de Usuarios') }}
        </h2>
    </x-slot>

    <div x-data="{ openDeleteModal: false, userToDelete: null, transferMode: false, newOwnerId: '' }">
        <div class="py-12">
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

                    <div x-data="{ 
                        selectedUsers: [], 
                        selectAll: false, 
                        usersOnPage: {{ json_encode($users->pluck('id')) }},
                        toggleSelectAll() {
                            this.selectAll = !this.selectAll;
                            this.selectedUsers = this.selectAll ? [...this.usersOnPage] : [];
                        },
                        get isAllSelected() {
                            return this.usersOnPage.length > 0 && this.selectedUsers.length === this.usersOnPage.length;
                        }
                    }" x-init="$watch('selectedUsers', () => selectAll = isAllSelected)">

                    <div class="mb-8 p-4 bg-gray-50 rounded-lg border">
                        <form action="{{ route('admin.users.index') }}" method="GET">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                                    <input type="text" name="search" id="search" class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm text-sm" placeholder="Nombre, email, posición..." value="{{ $filters['search'] ?? '' }}">
                                </div>
                                <div>
                                    <label for="area_id" class="block text-sm font-medium text-gray-700">Área</label>
                                    <select name="area_id" id="area_id" class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm text-sm">
                                        <option value="">Todas las áreas</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}" @selected(isset($filters['area_id']) && $filters['area_id'] == $area->id)>
                                                {{ $area->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700">Rol de Usuario</label>
                                    <select name="role" id="role" class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm text-sm">
                                        <option value="">Todos los roles</option>
                                        <option value="admin" @selected(isset($filters['role']) && $filters['role'] == 'admin')>Admin. de Área</option>
                                        <option value="client" @selected(isset($filters['role']) && $filters['role'] == 'client')>Cliente</option>
                                        <option value="normal" @selected(isset($filters['role']) && $filters['role'] == 'normal')>Normal</option>
                                    </select>
                                </div>
                                <div class="flex items-end space-x-2">
                                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#2c3856] hover:bg-[#1a2233] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2c3856]">
                                        Filtrar
                                    </button>
                                    <a href="{{ route('admin.users.index') }}" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00]">
                                        Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div x-data="{ view: localStorage.getItem('users_view_mode') || 'grid' }" x-init="$watch('view', val => localStorage.setItem('users_view_mode', val))">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
                            <div class="inline-flex rounded-lg shadow-sm">
                                <button @click="view = 'grid'" :class="{ 'bg-[#2c3856] text-white': view === 'grid', 'bg-white text-gray-600 hover:bg-gray-50': view !== 'grid' }" class="px-4 py-2 text-sm font-semibold border border-gray-200 rounded-l-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:z-10" title="Vista de tarjetas">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button @click="view = 'list'" :class="{ 'bg-[#2c3856] text-white': view === 'list', 'bg-white text-gray-600 hover:bg-gray-50': view !== 'list' }" class="px-4 py-2 text-sm font-semibold border-t border-b border-r border-gray-200 rounded-r-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:z-10" title="Vista de lista">
                                    <i class="fas fa-bars"></i>
                                </button>
                            </div>
                            
                            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-5 py-2 bg-[#2c3856] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#ff9c00] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] transition-all duration-300 transform hover:scale-105 shadow-md w-full sm:w-auto justify-center">
                                <i class="fas fa-plus mr-2"></i>
                                Crear Usuario
                            </a>
                        </div>

                        <div x-show="view === 'grid'" x-transition class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                            @forelse ($users as $user)
                                <div class="relative bg-white rounded-lg shadow border border-gray-100 p-4 flex flex-col text-center transition-all duration-300 hover:shadow-xl hover:-translate-y-1" 
                                     :class="{ 'ring-2 ring-offset-2 ring-[#ff9c00]': selectedUsers.includes({{ $user->id }}) }">
                                    <div class="absolute top-2 left-2">
                                        <input type="checkbox" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]" 
                                               :value="{{ $user->id }}" x-model="selectedUsers">
                                    </div>
                                    <div class="flex-grow flex flex-col items-center">
                                        <img class="h-20 w-20 rounded-full object-cover mb-3 border-4 
                                            {{ $user->is_area_admin ? 'border-[#ff9c00]' : ($user->is_client ? 'border-blue-400' : 'border-gray-200') }}" 
                                            src="{{ $user->profile_photo_path ? Storage::disk('s3')->url($user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=2C3856&background=F3F4F6' }}" 
                                            alt="{{ $user->name }}">
                                        <h5 class="text-base font-bold text-[#2c3856] truncate w-full" title="{{ $user->name }}">{{ $user->name }}</h5>
                                        <p class="text-xs font-medium text-gray-500 mb-1 truncate w-full" title="{{ $user->position }}">{{ $user->position ?? 'Sin Posición' }}</p>
                                        <p class="text-xs font-medium text-gray-500 mb-1 truncate w-full" title="{{ $user->phone_number }}">{{ $user->phone_number ?? 'Sin Teléfono' }}</p>
                                        <p class="text-xs text-gray-500 mb-2 truncate w-full" title="{{ $user->email }}">{{ $user->email }}</p>
                                        <p class="text-xs text-gray-600 font-medium">{{ $user->area->name ?? 'Sin área' }}</p>
                                        
                                        <div class="mt-2 flex flex-wrap justify-center gap-1">
                                            @if ($user->is_area_admin)
                                                <span class="px-2 py-0.5 inline-flex text-xxs leading-5 font-semibold rounded-full bg-orange-100 text-[#ff9c00]">Admin. de Área</span>
                                            @elseif ($user->is_client)
                                                <span class="px-2 py-0.5 inline-flex text-xxs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Cliente</span>
                                            @endif
                                            @if ($user->is_active)
                                                <span class="px-2 py-0.5 inline-flex text-xxs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                            @else
                                                <span class="px-2 py-0.5 inline-flex text-xxs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-center space-x-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-sm text-gray-500 hover:text-[#2c3856] p-1">Editar</a>
                                        <button type="button" 
                                                @click="openDeleteModal = true; userToDelete = {{ $user->toJson() }}; transferMode = false; newOwnerId = ''"
                                                class="text-sm text-gray-500 hover:text-red-600 p-1">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <p class="col-span-full text-center text-gray-500 py-12">No se encontraron usuarios con los filtros aplicados.</p>
                            @endforelse
                        </div>

                        <div x-show="view === 'list'" x-transition class="overflow-x-auto bg-white rounded-lg shadow border">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-[#2c3856]">
                                    <tr>
                                        <th class="px-6 py-3"><input type="checkbox" @click="toggleSelectAll()" :checked="isAllSelected" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]"></th>                                    
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Teléfono</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Área</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Rol</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Estado</th>
                                        <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($users as $user)
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-6 py-4"><input type="checkbox" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00]" :value="{{ $user->id }}" x-model="selectedUsers"></td>                                        
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <img class="h-9 w-9 rounded-full object-cover mr-3" src="{{ $user->profile_photo_path ? Storage::disk('s3')->url($user->profile_photo_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=2C3856&background=F3F4F6' }}" alt="{{ $user->name }}">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $user->position ?? 'Sin posición' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500">{{ $user->phone_number ?? 'Sin teléfono' }}</div></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500">{{ $user->email }}</div></td>
                                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900 font-medium">{{ $user->area->name ?? 'N/A' }}</div></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($user->is_area_admin)
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-[#ff9c00]">Admin. de Área</span>
                                                @elseif ($user->is_client)
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Cliente</span>
                                                @else
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Normal</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($user->is_active)
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activo</span>
                                                @else
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactivo</span>
                                                @endif
                                            </td>                                        
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-4">
                                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-[#2c3856] hover:text-[#ff9c00] font-semibold">Editar</a>
                                                    <button type="button" 
                                                            @click="openDeleteModal = true; userToDelete = {{ $user->toJson() }}; transferMode = false; newOwnerId = ''"
                                                            class="text-sm text-gray-500 hover:text-red-600 p-1">
                                                        Eliminar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500">No se encontraron usuarios con los filtros aplicados.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div x-show="selectedUsers.length > 0" 
                        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-4"
                        class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-4xl mb-4" style="display: none;">
                        <div class="bg-[#2c3856] text-white rounded-lg shadow-xl p-4 flex items-center justify-between">
                            <div>
                                <span x-text="selectedUsers.length"></span>
                                <span x-text="selectedUsers.length === 1 ? 'usuario seleccionado' : 'usuarios seleccionados'"></span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <form id="bulk-resend-form" action="{{ route('admin.users.bulk_resend_welcome') }}" method="POST" class="m-0">
                                    @csrf
                                    <template x-for="userId in selectedUsers" :key="userId"><input type="hidden" name="ids[]" :value="userId"></template>
                                    <button type="submit" class="px-4 py-2 text-sm font-semibold bg-[#ff9c00] rounded-md hover:bg-orange-500">Reenviar Bienvenida</button>
                                </form>
                                <form id="bulk-delete-form" action="{{ route('admin.users.bulk_delete') }}" method="POST" class="m-0" 
                                    onsubmit="return confirm('¿Confirmas la eliminación de los ' + document.querySelector('[x-data]').__x.$data.selectedUsers.length + ' usuarios seleccionados? Serán eliminados permanentemente.');">
                                    @csrf
                                    <template x-for="userId in selectedUsers" :key="userId"><input type="hidden" name="ids[]" :value="userId"></template>
                                    <button type="submit" class="px-4 py-2 text-sm font-semibold bg-red-600 rounded-md hover:bg-red-700">Eliminar Seleccionados</button>
                                </form>
                                <button @click="selectedUsers = []" class="text-sm text-gray-300 hover:text-white">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    {{ $users->links() }}
                </div>
            </div>
        </div>

        <div x-show="openDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="openDeleteModal" @click="openDeleteModal = false" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75"></div>

            <div x-show="openDeleteModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-white rounded-xl shadow-2xl p-6 md:p-8 w-full max-w-lg mx-auto z-50">
                
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-10 w-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </div>

                <div class="mt-4 text-center">
                    <h3 class="text-2xl font-bold text-[#2c3856]" id="modal-title">Eliminar Usuario</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Estás a punto de eliminar a <strong x-text="userToDelete ? userToDelete.name : 'usuario'"></strong>.
                        ¿Qué deseas hacer con sus carpetas y archivos?
                    </p>
                </div>

                <div class="mt-6 space-y-4">
                    <div>
                        <button @click="transferMode = true" class="w-full text-left p-4 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#ff9c00]">
                            <span class="font-semibold text-[#2c3856]">Transferir contenido a otro usuario</span>
                            <span class="block text-sm text-gray-500">El usuario será eliminado, pero sus carpetas y archivos serán reasignados.</span>
                        </button>
                    </div>

                    <div>
                        <button @click="transferMode = false" class="w-full text-left p-4 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <span class="font-semibold text-red-600">Eliminar permanentemente todo</span>
                            <span class="block text-sm text-gray-500">El usuario y todo su contenido (carpetas y archivos en S3) serán eliminados.</span>
                        </button>
                    </div>
                </div>

                <div x-show="transferMode" x-transition class="mt-6 pt-4 border-t">
                    <form :action="userToDelete ? '{{ url('admin/users') }}/' + userToDelete.id + '/transfer-and-destroy' : ''" method="POST">
                        @csrf
                        <label for="new_owner_id" class="block text-sm font-medium text-gray-700">Selecciona el nuevo propietario:</label>
                        <select name="new_owner_id" id="new_owner_id" x-model="newOwnerId" class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm">
                            <option value="" disabled>Selecciona un usuario...</option>
                            @foreach ($allUsers as $ownerUser)
                                <template x-if="userToDelete && {{ $ownerUser->id }} !== userToDelete.id">
                                    <option value="{{ $ownerUser->id }}">{{ $ownerUser->name }}</option>
                                </template>
                            @endforeach
                        </select>
                        <button type="submit" :disabled="!newOwnerId" 
                                class="mt-4 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#2c3856] hover:bg-[#1a2233] disabled:bg-gray-300">
                            Transferir y Eliminar
                        </button>
                    </form>
                </div>

                <div x-show="!transferMode" x-transition class="mt-6 pt-4 border-t">
                    <form :action="userToDelete ? '{{ url('admin/users') }}/' + userToDelete.id : ''" method="POST">
                        @csrf
                        @method('DELETE')
                        <p class="text-sm text-center text-red-700">Esta acción no se puede deshacer.</p>
                        <button type="submit" class="mt-4 w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                            Sí, Eliminar Todo Permanentemente
                        </button>
                    </form>
                </div>

                <div class="mt-4">
                    <button @click="openDeleteModal = false" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancelar
                    </button>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>