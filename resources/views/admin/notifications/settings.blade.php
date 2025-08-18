<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestionar Notificaciones por Correo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md" role="alert">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form id="notification-form" action="{{ route('admin.notifications.settings.store') }}" method="POST">
                @csrf
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Asignación de Notificaciones
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Selecciona un usuario de la lista para configurar sus notificaciones.
                                </p>
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Guardar Cambios
                            </button>
                        </div>
                    </div>

                    <div class="flex">
                        <div class="w-1/3 border-r border-gray-200 bg-gray-50">
                            <div class="p-4 border-b border-gray-200">
                                <input type="text" id="user-search" placeholder="Buscar usuario..." class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            </div>
                            <ul id="user-list" class="divide-y divide-gray-200 max-h-[60vh] overflow-y-auto">
                                @forelse ($users as $user)
                                    <li class="p-4 cursor-pointer hover:bg-blue-100 transition-colors duration-150 user-item" data-user-id="{{ $user->id }}" data-user-name="{{ strtolower($user->name) }} {{ strtolower($user->email) }}">
                                        <div class="font-medium text-gray-800">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </li>
                                @empty
                                    <li class="p-4 text-center text-gray-500">No se encontraron usuarios.</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="w-2/3 p-6">
                            @foreach ($users as $user)
                                <div id="settings-for-{{ $user->id }}" class="user-settings hidden">
                                    <h4 class="text-md font-semibold text-gray-700 mb-4">Eventos para notificar a {{ $user->name }}:</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach ($events as $eventKey => $eventLabel)
                                            <label for="setting-{{ $user->id }}-{{ $eventKey }}" class="flex items-center text-sm text-gray-700 cursor-pointer">
                                                <input 
                                                    type="checkbox"
                                                    id="setting-{{ $user->id }}-{{ $eventKey }}"
                                                    name="settings[{{ $user->id }}][]"
                                                    value="{{ $eventKey }}"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                                    @checked(in_array($eventKey, $settings->get($user->id, collect())->all()))

                                                >
                                                <span class="ml-2">{{ $eventLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                            <div id="settings-placeholder" class="text-center text-gray-500 pt-16">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Selecciona un usuario</h3>
                                <p class="mt-1 text-sm text-gray-500">Elige un usuario de la lista de la izquierda para ver y editar sus notificaciones.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userListItems = document.querySelectorAll('.user-item');
            const userSettingsPanels = document.querySelectorAll('.user-settings');
            const placeholder = document.getElementById('settings-placeholder');
            const searchInput = document.getElementById('user-search');

            userListItems.forEach(item => {
                item.addEventListener('click', function () {
                    const userId = this.dataset.userId;

                    // Ocultar todos los paneles de configuración y el placeholder
                    userSettingsPanels.forEach(panel => panel.classList.add('hidden'));
                    placeholder.classList.add('hidden');
                    
                    // Resaltar el usuario seleccionado
                    userListItems.forEach(li => li.classList.remove('bg-blue-200', 'font-semibold'));
                    this.classList.add('bg-blue-200', 'font-semibold');

                    // Mostrar el panel de configuración del usuario seleccionado
                    const activePanel = document.getElementById(`settings-for-${userId}`);
                    if (activePanel) {
                        activePanel.classList.remove('hidden');
                    }
                });
            });

            // Lógica del buscador
            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                userListItems.forEach(item => {
                    const userName = item.dataset.userName;
                    if (userName.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</x-app-layout>