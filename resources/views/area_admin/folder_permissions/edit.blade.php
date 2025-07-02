<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestionar Permisos para Carpeta: ') }} {{ $folder->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('area_admin.folder_permissions.update', $folder) }}">
                        @csrf
                        @method('PUT')

                        <p class="mb-4 text-gray-600">Selecciona los usuarios de tu área que tendrán acceso a esta carpeta y a su contenido.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse ($areaUsers as $user)
                                <div class="flex items-center">
                                    <input type="checkbox" name="users_with_access[]" id="user_{{ $user->id }}" value="{{ $user->id }}"
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                           {{ in_array($user->id, $usersWithAccessIds) ? 'checked' : '' }}>
                                    <label for="user_{{ $user->id }}" class="ml-2 text-sm text-gray-900">{{ $user->name }} ({{ $user->email }})</label>
                                </div>
                            @empty
                                <p class="col-span-3 text-gray-600">No hay otros usuarios en tu área para asignar permisos.</p>
                            @endforelse
                        </div>
                        <x-input-error :messages="$errors->get('users_with_access')" class="mt-2" />
                        <x-input-error :messages="$errors->get('users_with_access.*')" class="mt-2" />


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('area_admin.folder_permissions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-4">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Actualizar Permisos') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>