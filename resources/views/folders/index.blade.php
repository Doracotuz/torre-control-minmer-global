<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Carpetas') }}
            @if ($currentFolder)
                <span class="text-gray-500"> / {{ $currentFolder->name }}</span>
            @else
                <span class="text-gray-500"> / Raíz</span>
            @endif
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

                    <nav class="text-sm font-medium text-gray-500 mb-4">
                        <ol class="list-none p-0 inline-flex">
                            <li class="flex items-center">
                                <a href="{{ route('folders.index') }}" class="text-blue-600 hover:text-blue-800">Raíz</a>
                                <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 67.254c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.476 239.029c9.373 9.372 9.373 24.568 0 33.942z"/></svg>
                            </li>
                            @if ($currentFolder)
                                @php
                                    $path = [];
                                    $tempFolder = $currentFolder;
                                    while ($tempFolder) {
                                        array_unshift($path, $tempFolder);
                                        $tempFolder = $tempFolder->parent;
                                    }
                                @endphp
                                @foreach ($path as $pFolder)
                                    <li class="flex items-center">
                                        {{-- CAMBIO AQUÍ: folders.show a folders.index --}}
                                        <a href="{{ route('folders.index', $pFolder) }}" class="text-blue-600 hover:text-blue-800">{{ $pFolder->name }}</a>
                                        @if (!$loop->last)
                                            <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 67.254c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.476 239.029c9.373 9.372 9.373 24.568 0 33.942z"/></svg>
                                        @endif
                                    </li>
                                @endforeach
                            @endif
                        </ol>
                    </nav>

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Contenido de la Carpeta:</h3>
                        <div>
                            <a href="{{ route('folders.create', ['folder' => $currentFolder ? $currentFolder->id : null]) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                Crear Nueva Carpeta
                            </a>
                            @if ($currentFolder)
                                <a href="{{ route('file_links.create', $currentFolder) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Añadir Archivo / Enlace
                                </a>
                            @endif
                        </div>
                    </div>

                    @if ($folders->isEmpty() && $fileLinks->isEmpty())
                        <p class="text-gray-600">Esta carpeta está vacía.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($folders as $folderItem)
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-center justify-between shadow-sm">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                        {{-- CAMBIO AQUÍ: folders.show a folders.index --}}
                                        <a href="{{ route('folders.index', $folderItem) }}" class="text-lg font-medium text-blue-600 hover:underline">{{ $folderItem->name }}</a>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('folders.edit', $folderItem) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Editar</a>
                                        <form action="{{ route('folders.destroy', $folderItem) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta carpeta? Esto también eliminará todo su contenido (subcarpetas, archivos y enlaces).');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach

                            @foreach ($fileLinks as $fileLink)
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-center justify-between shadow-sm">
                                    <div class="flex items-center">
                                        @if ($fileLink->type == 'file')
                                            <svg class="w-6 h-6 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0113 3.414L16.586 7A2 2 0 0117 8.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h9V8.414L10.586 4H6z" clip-rule="evenodd"></path></svg>
                                            <a href="{{ asset('storage/' . $fileLink->path) }}" target="_blank" class="text-lg font-medium text-gray-700 hover:underline">{{ $fileLink->name }}</a>
                                        @else
                                            <svg class="w-6 h-6 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.414 1.414a1 1 0 001.414 1.414l1.414-1.414zM5.414 15.414a2 2 0 11-2.828-2.828l3-3a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 005.656 5.656l1.414-1.414a1 1 0 00-1.414-1.414l-1.414 1.414z" clip-rule="evenodd"></path></svg>
                                            <a href="{{ $fileLink->url }}" target="_blank" class="text-lg font-medium text-blue-600 hover:underline">{{ $fileLink->name }}</a>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('file_links.edit', $fileLink) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Editar</a>
                                        <form action="{{ route('file_links.destroy', $fileLink) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este elemento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
