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
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">¡Éxito!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">¡Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Selecciona una carpeta para gestionar sus permisos:</h3>

                    {{-- Versión de tabla para pantallas grandes --}}
                    <div class="overflow-x-auto bg-white rounded-lg shadow hidden sm:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-[#2c3856]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Nombre de la Carpeta
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Ruta
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($folders as $folder)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $folder->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @php
                                                $path = [];
                                                $tempFolder = $folder;
                                                while ($tempFolder) {
                                                    array_unshift($path, $tempFolder->name);
                                                    $tempFolder = $tempFolder->parent;
                                                }
                                                echo implode(' / ', $path);
                                            @endphp
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('area_admin.folder_permissions.edit', $folder) }}" class="text-indigo-600 hover:text-indigo-900">Gestionar Permisos</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Versión de lista/tarjetas para pantallas pequeñas --}}
                    <div class="sm:hidden">
                        @forelse ($folders as $folder)
                            <div class="bg-white shadow overflow-hidden rounded-lg mb-4 border border-gray-200 p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <div class="text-sm font-bold text-gray-700">Nombre de la Carpeta:</div>
                                    <div class="text-sm text-gray-900">{{ $folder->name }}</div>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <div class="text-sm font-bold text-gray-700">Ruta:</div>
                                    <div class="text-sm text-gray-500">
                                        @php
                                            $path = [];
                                            $tempFolder = $folder;
                                            while ($tempFolder) {
                                                array_unshift($path, $tempFolder->name);
                                                $tempFolder = $tempFolder->parent;
                                            }
                                            echo implode(' / ', $path);
                                        @endphp
                                    </div>
                                </div>
                                <div class="flex justify-end pt-4 border-t mt-4">
                                    <a href="{{ route('area_admin.folder_permissions.edit', $folder) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-sm">Gestionar Permisos</a>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center text-gray-500 bg-white rounded-lg shadow">
                                No hay carpetas disponibles para gestionar permisos en esta área.
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>