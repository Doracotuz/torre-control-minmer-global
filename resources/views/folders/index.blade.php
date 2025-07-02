<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Explorar carpetas') }}
            @if ($currentFolder)
                <span class="text-gray-500"> / {{ $currentFolder->name }}</span>
            @else
                <span class="text-gray-500"> / Global</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100"
         x-data="{
             showPropertiesModal: false,
             propertiesData: {},
             openPropertiesModal: function(itemData) {
                 this.showPropertiesModal = true;
                 this.propertiesData = itemData;
                 console.log('openPropertiesModal called from Alpine:', itemData);
             }
         }"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                <div class="p-8 text-gray-900">
                    @if (session('success'))
                        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-90"
                             class="bg-[#ff9c00] text-white px-6 py-4 rounded-lg shadow-xl relative mb-6 flex items-center justify-between" role="alert">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <strong class="font-bold mr-1">¡Éxito!</strong>
                                <span class="block sm:inline">{{ session('success') }}</span>
                            </div>
                            <button @click="show = false" class="text-white hover:text-gray-200 transition-colors duration-200 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-90"
                             class="bg-red-600 text-white px-6 py-4 rounded-lg shadow-xl relative mb-6 flex items-center justify-between" role="alert">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <strong class="font-bold mr-1">¡Error!</strong>
                                <span class="block sm:inline">{{ session('error') }}</span>
                            </div>
                            <button @click="show = false" class="text-white hover:text-gray-200 transition-colors duration-200 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    @endif

                    <nav class="text-sm font-medium text-gray-500 mb-6">
                        <ol class="list-none p-0 inline-flex items-center">
                            <li class="flex items-center">
                                <a href="{{ route('folders.index') }}" class="text-[#2c3856] hover:text-[#ff9c00] transition-colors duration-200 font-semibold">{{ __('Raíz') }}</a>
                                <svg class="fill-current w-3 h-3 mx-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 67.254c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.476 239.029c9.373 9flags: {
  "isValid": true
}
.372 9.373 24.568 0 33.942z"/></svg>
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
                                        <a href="{{ route('folders.index', $pFolder) }}" class="text-[#2c3856] hover:text-[#ff9c00] transition-colors duration-200 font-semibold">{{ $pFolder->name }}</a>
                                        @if (!$loop->last)
                                            <svg class="fill-current w-3 h-3 mx-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 67.254c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.476 239.029c9.373 9.372 9.373 24.568 0 33.942z"/></svg>
                                        @endif
                                    </li>
                                @endforeach
                            @endif
                        </ol>
                    </nav>

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">{{ __('') }}</h3>
                        <div>
                            <a href="{{ route('folders.create', ['folder' => $currentFolder ? $currentFolder->id : null]) }}" class="inline-flex items-center px-5 py-2 bg-[#2b2b2b] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#666666] focus:bg-[#666666] active:bg-[#000000] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md mr-3">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                {{ __('Crear Carpeta') }}
                            </a>
                            @if (Auth::user()->is_area_admin || (Auth::user()->area && Auth::user()->area->name === 'Administración'))
                                @if ($currentFolder)
                                    <a href="{{ route('file_links.create', $currentFolder) }}" class="inline-flex items-center px-5 py-2 bg-[#ff9c00] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        {{ __('Añadir Elemento') }}
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    @if ($folders->isEmpty() && $fileLinks->isEmpty())
                        @if (isset($searchQuery) && $searchQuery)
                            <p class="text-lg text-gray-600 py-8 text-center" style="font-family: 'Montserrat', sans-serif;">Ningún resultado para tu búsqueda: "<span class="font-semibold text-[#2c3856]">{{ $searchQuery }}</span>".</p>
                        @else
                            <p class="text-lg text-gray-600 py-8 text-center" style="font-family: 'Montserrat', sans-serif;">Esta carpeta está vacía.</p>
                        @endif
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                                            {{ __('Nombre del Elemento') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Tipo') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Creado Por') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Fecha de Subida') }}
                                        </th>
                                        <th scope="col" class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                                            {{ __('Acciones') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($folders as $folderItem)
                                        <tr class="hover:bg-gray-100 transition-colors duration-150 cursor-pointer"
                                            x-on:click="window.location.href='{{ route('folders.index', $folderItem) }}'"
                                            x-on:contextmenu.prevent="openPropertiesModal({
                                                name: '{{ $folderItem->name }}',
                                                type: 'Carpeta',
                                                creator: '{{ $folderItem->user->name ?? 'N/A' }}',
                                                date: '{{ $folderItem->created_at->format('d M Y, H:i') }}',
                                                isFolder: true,
                                                path: '{{ $folderItem->parent ? $folderItem->parent->name . '/' : '' }}{{ $folderItem->name }}'
                                            })"
                                        >
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <svg class="w-7 h-7 text-[#ff9c00] mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                    </svg>
                                                    <span class="text-lg font-medium text-[#2c3856] truncate">{{ $folderItem->name }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ __('Carpeta') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $folderItem->user->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $folderItem->created_at->format('d M Y, H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                                <a href="{{ route('folders.edit', $folderItem) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" onclick="event.stopPropagation()">Editar</a>
                                                <form action="{{ route('folders.destroy', $folderItem) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta carpeta? Esto también eliminará todo su contenido (subcarpetas, archivos y enlaces).'); event.stopPropagation();">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach ($fileLinks as $fileLink)
                                        @php
                                            $fileExtension = $fileLink->type == 'file' ? pathinfo($fileLink->path, PATHINFO_EXTENSION) : null;
                                            $fileUrl = $fileLink->type == 'file' ? asset('storage/' . $fileLink->path) : $fileLink->url;
                                        @endphp
                                        <tr class="hover:bg-gray-100 transition-colors duration-150 cursor-pointer"
                                            x-on:contextmenu.prevent="openPropertiesModal({
                                                name: '{{ $fileLink->name }}',
                                                type: '{{ $fileLink->type == 'file' ? 'Archivo (' . strtoupper($fileExtension) . ')' : 'Enlace' }}',
                                                creator: '{{ $fileLink->user->name ?? 'N/A' }}',
                                                date: '{{ $fileLink->created_at->format('d M Y, H:i') }}',
                                                isFolder: false,
                                                path: '{{ $fileLink->type == 'file' ? $fileLink->path : $fileLink->url }}'
                                            })"
                                        >
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if ($fileLink->type == 'file')
                                                        @if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']))
                                                            <svg class="w-7 h-7 text-green-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        @elseif (strtolower($fileExtension) == 'pdf')
                                                            <svg class="w-7 h-7 text-red-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        @else
                                                            <svg class="w-7 h-7 text-gray-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                            </svg>
                                                        @endif
                                                        <a href="{{ $fileUrl }}" download="{{ $fileLink->name }}" target="_blank" class="text-lg font-medium text-[#2c3856] truncate">{{ $fileLink->name }}</a>
                                                    @else
                                                        <svg class="w-7 h-7 text-blue-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                        </svg>
                                                        <a href="{{ $fileUrl }}" target="_blank" class="text-lg font-medium text-[#2c3856] truncate">{{ $fileLink->name }}</a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                @if ($fileLink->type == 'file')
                                                    {{ __('Archivo') }} ({{ strtoupper($fileExtension) }})
                                                @else
                                                    {{ __('Enlace') }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $fileLink->user->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $fileLink->created_at->format('d M Y, H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                                <a href="{{ route('file_links.edit', $fileLink) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" onclick="event.stopPropagation()">Editar</a>
                                                <form action="{{ route('file_links.destroy', $fileLink) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este elemento?'); event.stopPropagation();">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div x-show="showPropertiesModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50"
         style="display: none;"
         @click.away="showPropertiesModal = false"
         @keydown.escape.window="showPropertiesModal = false"
    >
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md max-h-[90vh] overflow-hidden flex flex-col" @click.stop="">
            <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-4">
                <h3 class="text-xl font-semibold text-[#2c3856]">{{ __('Propiedades del Elemento') }}</h3>
                <button @click="showPropertiesModal = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto text-gray-700 text-base">
                <div class="mb-3">
                    <span class="font-semibold text-[#2c3856]">Nombre:</span> <span x-text="propertiesData.name"></span>
                </div>
                <div class="mb-3">
                    <span class="font-semibold text-[#2c3856]">Tipo:</span> <span x-text="propertiesData.type"></span>
                </div>
                <div class="mb-3">
                    <span class="font-semibold text-[#2c3856]">Creado Por:</span> <span x-text="propertiesData.creator"></span>
                </div>
                <div x-show="propertiesData.path" class="mb-3">
                    <span class="font-semibold text-[#2c3856]" x-text="propertiesData.isFolder ? 'Ruta:' : 'Ubicación:'"></span> <span x-text="propertiesData.path"></span>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button @click="showPropertiesModal = false"
                   class="inline-flex items-center px-5 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                    {{ __('Cerrar') }}
                </button>
            </div>
        </div>
    </div>
</x-app-layout>