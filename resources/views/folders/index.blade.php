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

    {{-- Main content wrapper with x-data for properties modal and drag-drop --}}
    <div class="py-6 sm:py-12 bg-gray-100"
         x-data="{
             showPropertiesModal: false,
             propertiesData: {},
             isTileView: true,
             tileSize: 'medium',
             openPropertiesModal: function(itemData) {
                 this.showPropertiesModal = true;
                 this.propertiesData = itemData;
                 console.log('openPropertiesModal called from Alpine:', itemData);
             },
             draggingFolderId: null,
             dropTargetFolderId: null,
             handleDragStart(event, folderId) {
                 this.draggingFolderId = folderId;
                 event.dataTransfer.setData('text/plain', folderId);
                 event.dataTransfer.effectAllowed = 'move';
                 console.log('Started dragging folder:', folderId);
             },
             handleDragOver(event, targetFolderId) {
                 event.preventDefault();
                 if (this.draggingFolderId && this.draggingFolderId != targetFolderId) {
                     this.dropTargetFolderId = targetFolderId;
                     event.dataTransfer.dropEffect = 'move';
                 } else {
                     event.dataTransfer.dropEffect = 'none';
                 }
             },
             handleDragLeave(event) {
                 this.dropTargetFolderId = null;
             },
             handleDrop(event, targetFolderId) {
                 event.preventDefault();
                 this.dropTargetFolderId = null;
                 const draggedId = event.dataTransfer.getData('text/plain');

                 if (draggedId && draggedId != targetFolderId) {
                     console.log(`Dropped folder ${draggedId} into folder ${targetFolderId}`);
                     fetch('/folders/move', {
                         method: 'PUT',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         },
                         body: JSON.stringify({
                             folder_id: draggedId,
                             target_folder_id: targetFolderId
                         })
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.success) {
                             window.location.reload();
                         } else {
                             alert('Error al mover carpeta: ' + data.message);
                         }
                     })
                     .catch(error => {
                         console.error('Error moving folder:', error);
                         alert('Error al mover carpeta.');
                     });
                 }
                 this.draggingFolderId = null;
             },
             isDraggingFile: false,
             handleFileDragOver(event) {
                 event.preventDefault();
                 event.stopPropagation();
                 this.isDraggingFile = true;
                 event.dataTransfer.dropEffect = 'copy';
             },
             handleFileDragLeave(event) {
                 event.preventDefault();
                 event.stopPropagation();
                 this.isDraggingFile = false;
             },
             handleFileDrop(event, targetFolderId) {
                 event.preventDefault();
                 event.stopPropagation();
                 this.isDraggingFile = false;

                 const files = event.dataTransfer.files;
                 if (files.length > 0) {
                     console.log('Dropped files:', files);
                     const formData = new FormData();
                     formData.append('folder_id', targetFolderId);
                     for (let i = 0; i < files.length; i++) {
                         formData.append('files[]', files[i]);
                         formData.append('names[]', files[i].name);
                     }

                     fetch('/folders/upload-dropped-files', {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         },
                         body: formData
                     })
                     .then(response => response.json())
                     .then(data => {
                         if (data.success) {
                             window.location.reload();
                         } else {
                             alert('Error al subir archivos: ' + data.message);
                         }
                     })
                     .catch(error => {
                         console.error('Error uploading files:', error);
                         alert('Error al subir archivos.');
                     });
                 }
             }
         }"
    >
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Notificaciones Flash (Estilo y Posición Mejorados) --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300 transform scale-90 opacity-0" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200 transform scale-100 opacity-100" x-transition:leave-end="opacity-0 scale-90"
                     class="fixed top-4 right-4 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <strong class="font-bold mr-1">{{ __('¡Éxito!') }}</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300 transform scale-90 opacity-0" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200 transform scale-100 opacity-100" x-transition:leave-end="opacity-0 scale-90"
                     class="fixed top-4 right-4 z-50 bg-white border-l-4 border-red-600 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <strong class="font-bold mr-1">{{ __('¡Error!') }}</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            @endif


            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-4 sm:p-8">
                <nav class="text-sm font-medium text-gray-500 mb-4 sm:mb-6">
                    <ol class="list-none p-0 inline-flex items-center flex-wrap">
                        <li class="flex items-center">
                            <a href="{{ route('folders.index') }}" class="text-[#2c3856] hover:text-[#ff9c00] transition-colors duration-200 font-semibold">{{ __('Raíz') }}</a>
                            <svg class="fill-current w-3 h-3 mx-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 67.254c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.476 239.029c9.373 9.372 9.373 24.568 0 33.942z"/></svg>
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
                                        <svg class="fill-current w-3 h-3 mx-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 67.254c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.476 239.029c9.373 9.372 9.373 24.568 0 33.942z"/></svg>
                                    @endif
                                </li>
                            @endforeach
                        @endif
                    </ol>
                </nav>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
                    <h3 class="text-xl font-semibold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">{{ __('Contenido Actual') }}</h3>
                    <div class="flex flex-wrap items-center justify-start sm:justify-end gap-3">
                        {{-- Botones de Alternancia de Vista --}}
                        <div class="flex rounded-full border border-gray-300 overflow-hidden">
                            <button @click="isTileView = true"
                                    :class="{'bg-[#ff9c00] text-white': isTileView, 'bg-gray-100 text-gray-700 hover:bg-gray-200': !isTileView}"
                                    class="px-3 py-1.5 text-sm font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-1"
                                    title="{{ __('Vista de Mosaico') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </button>
                            <button @click="isTileView = false"
                                    :class="{'bg-[#ff9c00] text-white': !isTileView, 'bg-gray-100 text-gray-700 hover:bg-gray-200': isTileView}"
                                    class="px-3 py-1.5 text-sm font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-1"
                                    title="{{ __('Vista de Lista') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            </button>
                        </div>

                        {{-- Botones de Control de Tamaño de Mosaico (NUEVO) --}}
                        <div x-show="isTileView" class="flex rounded-full border border-gray-300 overflow-hidden">
                            <button @click="tileSize = 'small'"
                                    :class="{'bg-[#2c3856] text-white': tileSize === 'small', 'bg-gray-100 text-gray-700 hover:bg-gray-200': tileSize !== 'small'}"
                                    class="px-2 py-1.5 text-xs font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-1"
                                    title="{{ __('Mosaicos Pequeños') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </button>
                            <button @click="tileSize = 'medium'"
                                    :class="{'bg-[#2c3856] text-white': tileSize === 'medium', 'bg-gray-100 text-gray-700 hover:bg-gray-200': tileSize !== 'medium'}"
                                    class="px-2 py-1.5 text-xs font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-1"
                                    title="{{ __('Mosaicos Medianos') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </button>
                            <button @click="tileSize = 'large'"
                                    :class="{'bg-[#2c3856] text-white': tileSize === 'large', 'bg-gray-100 text-gray-700 hover:bg-gray-200': tileSize !== 'large'}"
                                    class="px-2 py-1.5 text-xs font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-1"
                                    title="{{ __('Mosaicos Grandes') }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </button>
                        </div>

                        <a href="{{ route('folders.create', ['folder' => $currentFolder ? $currentFolder->id : null]) }}" class="inline-flex items-center px-4 py-2 bg-[#2b2b2b] border border-transparent rounded-full font-semibold text-xxs sm:text-xs text-white uppercase tracking-widest hover:bg-[#666666] focus:bg-[#666666] active:bg-[#000000] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                            <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="hidden sm:inline">{{ __('Crear Carpeta') }}</span>
                            <span class="sm:hidden">{{ __('Carpeta') }}</span>
                        </a>
                        @if (Auth::user()->is_area_admin || (Auth::user()->area && Auth::user()->area->name === 'Administración'))
                            @if ($currentFolder)
                                <a href="{{ route('file_links.create', $currentFolder) }}" class="inline-flex items-center px-4 py-2 bg-[#ff9c00] border border-transparent rounded-full font-semibold text-xxs sm:text-xs text-white uppercase tracking-widest hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                                    <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    <span class="hidden sm:inline">{{ __('Añadir Elemento') }}</span>
                                    <span class="sm:hidden">{{ __('Elemento') }}</span>
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
                    {{-- VISTA DE MOSAICO (con x-show y clases dinámicas) --}}
                    <div x-show="isTileView"
                         :class="{
                             'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4': tileSize === 'small',
                             'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6': tileSize === 'medium',
                             'grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8': tileSize === 'large'
                         }">
                        {{-- Mosaicos de Carpetas --}}
                        @foreach ($folders as $folderItem)
                            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border border-gray-200 flex flex-col items-center justify-center text-center hover:shadow-lg transition-shadow duration-200 group
                                        hover:bg-gray-50 transform hover:scale-105"
                                 draggable="true"
                                 x-on:dragstart="handleDragStart($event, {{ $folderItem->id }})"
                                 x-on:dragover.prevent="handleDragOver($event, {{ $folderItem->id }})"
                                 x-on:dragleave="handleDragLeave($event)"
                                 x-on:drop.prevent="handleDrop($event, {{ $folderItem->id }})"
                                 x-on:contextmenu.prevent="openPropertiesModal({
                                     name: '{{ $folderItem->name }}',
                                     type: 'Carpeta',
                                     creator: '{{ $folderItem->user->name ?? 'N/A' }}',
                                     date: '{{ $folderItem->created_at->format('d M Y, H:i') }}',
                                     isFolder: true,
                                     path: '{{ $folderItem->parent ? $folderItem->parent->name . '/' : '' }}{{ $folderItem->name }}'
                                 })"
                                 :class="{'border-blue-400 border-dashed bg-blue-100': dropTargetFolderId == {{ $folderItem->id }}}"
                                 x-data="{ showDetails: false }"
                            >
                                {{-- ENVOLVER ICONO Y NOMBRE EN UN <a> PARA LA ACCIÓN PRINCIPAL --}}
                                <a href="{{ route('folders.index', $folderItem) }}"
                                   class="flex flex-col items-center justify-center w-full"
                                   onclick="event.stopPropagation()" {{-- Importante para que no interfiera con botones internos --}}
                                >
                                    <svg :class="{ 'w-12 h-12': tileSize === 'small', 'w-16 h-16': tileSize === 'medium', 'w-20 h-20': tileSize === 'large' }" class="text-[#ff9c00] mb-2 sm:mb-3 group-hover:text-orange-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                    </svg>
                                    <span :class="{ 'text-base': tileSize === 'small', 'text-lg': tileSize === 'medium', 'text-xl': tileSize === 'large' }" class="font-semibold text-[#2c3856] mb-1 truncate w-full px-1 sm:px-2">
                                        {{ $folderItem->name }}
                                    </span>
                                    <span class="text-sm text-gray-500">Carpeta</span>
                                    <span class="text-xs text-gray-400 mt-1">{{ $folderItem->created_at->format('d M Y') }}</span>
                                </a> {{-- FIN DEL <a> --}}

                                {{-- Botón para desplegar/ocultar Detalles --}}
                                <button @click.stop="showDetails = !showDetails" class="mt-2 text-xs text-gray-500 hover:text-gray-700 focus:outline-none px-2 py-1 rounded-full border border-gray-300 hover:bg-gray-100 transition-colors duration-150">
                                    <span x-text="showDetails ? '{{ __('Ocultar Detalles') }}' : '{{ __('Ver Detalles') }}'"></span>
                                </button>

                                {{-- INFO DE CREADO POR, TIPO, FECHA (ahora condicional) --}}
                                <div x-show="showDetails" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-y-0" x-transition:enter-end="opacity-100 transform scale-y-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-y-100" x-transition:leave-end="opacity-0 transform scale-y-0" class="text-center text-gray-600 text-xxs sm:text-xs mt-2 space-y-1 opacity-75 origin-top" style="display: none;">
                                    <p><span class="font-semibold">{{ __('Creado por:') }}</span> {{ $folderItem->user->name ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">{{ __('Tipo:') }}</p>
                                    <p>{{ $folderItem->created_at->format('d M Y') }}</p>
                                </div>
                                {{-- FIN INFO ADICIONAL --}}

                                {{-- Acciones (botones con estilo mejorado y responsivo) --}}
                                <div class="mt-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full justify-center">
                                    <a href="{{ route('folders.edit', $folderItem) }}" :class="{'px-1 py-0.5 text-xxs': tileSize === 'small', 'px-2 py-1 text-xs': tileSize === 'medium', 'px-3 py-1.5 text-sm': tileSize === 'large'}" class="inline-flex items-center justify-center bg-indigo-500 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="event.stopPropagation()">
                                        {{ __('Editar') }}
                                    </a>
                                    <form action="{{ route('folders.destroy', $folderItem) }}" method="POST" class="inline-block w-full sm:w-auto" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta carpeta? Esto también eliminará todo su contenido (subcarpetas, archivos y enlaces).'); event.stopPropagation();"> {{-- event.stopPropagation() aquí para el submit --}}
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" :class="{'px-1 py-0.5 text-xxs': tileSize === 'small', 'px-2 py-1 text-xs': tileSize === 'medium', 'px-3 py-1.5 text-sm': tileSize === 'large'}" class="inline-flex items-center justify-center bg-red-500 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full">
                                            {{ __('Eliminar') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach

                        {{-- Mosaicos de Archivos y Enlaces (sin cambios si ya funcionan) --}}
                        @foreach ($fileLinks as $fileLink)
                            @php
                                $fileExtension = $fileLink->type == 'file' ? pathinfo($fileLink->path, PATHINFO_EXTENSION) : null;
                                $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                $isPdf = strtolower($fileExtension) == 'pdf';
                                $fileUrl = $fileLink->type == 'file' ? asset('storage/' . $fileLink->path) : $fileLink->url;
                            @endphp
                            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border border-gray-200 flex flex-col items-center justify-center text-center hover:shadow-lg transition-shadow duration-200 group
                                        hover:bg-gray-50 transform hover:scale-105"
                                 x-on:contextmenu.prevent="openPropertiesModal({
                                     name: '{{ $fileLink->name }}',
                                     type: '{{ $fileLink->type == 'file' ? 'Archivo (' . strtoupper($fileExtension) . ')' : 'Enlace' }}',
                                     creator: '{{ $fileLink->user->name ?? 'N/A' }}',
                                     date: '{{ $fileLink->created_at->format('d M Y, H:i') }}',
                                     isFolder: false,
                                     path: '{{ $fileLink->type == 'file' ? $fileLink->path : $fileLink->url }}'
                                 })"
                                 x-data="{ showDetails: false }"
                            >
                                <a href="@if ($fileLink->type == 'file') {{ $isImage || $isPdf ? $fileUrl : route('files.download', $fileLink) }} @else {{ $fileUrl }} @endif"
                                   target="_blank"
                                   class="flex flex-col items-center justify-center w-full"
                                   onclick="event.stopPropagation()"
                                >
                                    @if ($fileLink->type == 'file')
                                        @if ($isImage)
                                            <svg :class="{ 'w-12 h-12': tileSize === 'small', 'w-16 h-16': tileSize === 'medium', 'w-20 h-20': tileSize === 'large' }" class="text-green-600 mb-2 sm:mb-3 group-hover:text-green-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        @elseif ($isPdf)
                                            <svg :class="{ 'w-12 h-12': tileSize === 'small', 'w-16 h-16': tileSize === 'medium', 'w-20 h-20': tileSize === 'large' }" class="text-red-600 mb-2 sm:mb-3 group-hover:text-red-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        @else
                                            <svg :class="{ 'w-12 h-12': tileSize === 'small', 'w-16 h-16': tileSize === 'medium', 'w-20 h-20': tileSize === 'large' }" class="text-gray-600 mb-2 sm:mb-3 group-hover:text-gray-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        @endif
                                        <span :class="{ 'text-base': tileSize === 'small', 'text-lg': tileSize === 'medium', 'text-xl': tileSize === 'large' }" class="font-semibold text-[#2c3856] mb-1 truncate w-full px-1 sm:px-2">
                                            {{ $fileLink->name }}
                                        </span>
                                        <span class="text-sm text-gray-500">{{ __('Archivo') }} ({{ strtoupper($fileExtension) }})</span>
                                    @else
                                        <svg :class="{ 'w-12 h-12': tileSize === 'small', 'w-16 h-16': tileSize === 'medium', 'w-20 h-20': tileSize === 'large' }" class="text-blue-600 mb-2 sm:mb-3 group-hover:text-blue-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        <span :class="{ 'text-base': tileSize === 'small', 'text-lg': tileSize === 'medium', 'text-xl': tileSize === 'large' }" class="font-semibold text-[#2c3856] mb-1 truncate w-full px-1 sm:px-2">
                                            {{ $fileLink->name }}
                                        </span>
                                        <span class="text-sm text-gray-500">{{ __('Enlace') }}</span>
                                    @endif
                                    <span class="text-xxs text-gray-400 mt-1" :class="{'text-xxs': tileSize === 'small', 'text-xs': tileSize === 'medium', 'text-sm': tileSize === 'large'}">{{ $fileLink->created_at->format('d M Y') }}</span>
                                </a>
                                {{-- Botón para desplegar/ocultar Detalles --}}
                                <button @click.stop="showDetails = !showDetails" class="mt-2 text-xxs sm:text-xs text-gray-500 hover:text-gray-700 focus:outline-none px-2 py-1 rounded-full border border-gray-300 hover:bg-gray-100 transition-colors duration-150">
                                    <span x-text="showDetails ? '{{ __('Ocultar Detalles') }}' : '{{ __('Ver Detalles') }}'"></span>
                                </button>

                                {{-- INFO DE CREADO POR, TIPO, FECHA (ahora condicional) --}}
                                <div x-show="showDetails" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-y-0" x-transition:enter-end="opacity-100 transform scale-y-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-y-100" x-transition:leave-end="opacity-0 transform scale-y-0" class="text-center text-gray-600 text-xxs sm:text-xs mt-2 space-y-1 opacity-75 origin-top" style="display: none;">
                                    <p><span class="font-semibold">{{ __('Creado por:') }}</span> {{ $fileLink->user->name ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">{{ __('Tipo:') }}</span> {{ $fileLink->type == 'file' ? 'Archivo' : 'Enlace' }}</p>
                                    <p><span class="font-semibold">{{ __('Fecha:') }}</p>
                                    <p>{{ $fileLink->created_at->format('d M Y') }}</p>
                                </div>
                                {{-- FIN INFO ADICIONAL --}}

                                {{-- Acciones (botones con estilo mejorado y responsivo) --}}
                                <div class="mt-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full justify-center">
                                    <a href="{{ route('file_links.edit', $fileLink) }}" :class="{'px-1 py-0.5 text-xxs': tileSize === 'small', 'px-2 py-1 text-xs': tileSize === 'medium', 'px-3 py-1.5 text-sm': tileSize === 'large'}" class="inline-flex items-center justify-center bg-indigo-500 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="event.stopPropagation()">
                                        {{ __('Editar') }}
                                    </a>
                                    <form action="{{ route('file_links.destroy', $fileLink) }}" method="POST" class="inline-block w-full sm:w-auto" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este elemento?'); event.stopPropagation();">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" :class="{'px-1 py-0.5 text-xxs': tileSize === 'small', 'px-2 py-1 text-xs': tileSize === 'medium', 'px-3 py-1.5 text-sm': tileSize === 'large'}" class="inline-flex items-center justify-center bg-red-500 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full">
                                            {{ __('Eliminar') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- VISTA DE TABLA/FILAS --}}
                    <div x-show="!isTileView" class="overflow-x-auto">
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
                                        draggable="true"
                                        x-on:dragstart="handleDragStart($event, {{ $folderItem->id }})"
                                        x-on:dragover.prevent="handleDragOver($event, {{ $folderItem->id }})"
                                        x-on:dragleave="handleDragLeave($event)"
                                        x-on:drop.prevent="handleDrop($event, {{ $folderItem->id }})"
                                        x-on:contextmenu.prevent="openPropertiesModal({
                                            name: '{{ $folderItem->name }}',
                                            type: 'Carpeta',
                                            creator: '{{ $folderItem->user->name ?? 'N/A' }}',
                                            date: '{{ $folderItem->created_at->format('d M Y, H:i') }}',
                                            isFolder: true,
                                            path: '{{ $folderItem->parent ? $folderItem->parent->name . '/' : '' }}{{ $folderItem->name }}'
                                        })"
                                        :class="{'bg-blue-100 border-blue-400 border-dashed': dropTargetFolderId == {{ $folderItem->id }}}"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{-- ENVOLVER ICONO Y NOMBRE EN UN <a> PARA LA ACCIÓN PRINCIPAL --}}
                                            <a href="{{ route('folders.index', $folderItem) }}" class="flex items-center" onclick="event.stopPropagation()">
                                                <svg class="w-7 h-7 text-[#ff9c00] mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                </svg>
                                                <span class="text-lg font-medium text-[#2c3856] truncate">{{ $folderItem->name }}</span>
                                            </a> {{-- FIN DEL <a> --}}
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
                                            <a href="{{ route('folders.edit', $folderItem) }}" class="inline-flex items-center px-3 py-1 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="event.stopPropagation()">
                                                {{ __('Editar') }}
                                            </a>
                                            <form action="{{ route('folders.destroy', $folderItem) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta carpeta? Esto también eliminará todo su contenido (subcarpetas, archivos y enlaces).'); event.stopPropagation();">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    {{ __('Eliminar') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach

                                @foreach ($fileLinks as $fileLink)
                                    @php
                                        $fileExtension = $fileLink->type == 'file' ? pathinfo($fileLink->path, PATHINFO_EXTENSION) : null;
                                        $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                        $isPdf = strtolower($fileExtension) == 'pdf';
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
                                        x-on:click.stop="
                                            @if ($fileLink->type == 'file')
                                                window.open('{{ $isImage || $isPdf ? $fileUrl : route('files.download', $fileLink) }}', '_blank')
                                            @else
                                                window.open('{{ $fileUrl }}', '_blank')
                                            @endif
                                        "
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if ($fileLink->type == 'file')
                                                    @if ($isImage)
                                                        <svg class="w-7 h-7 text-green-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    @elseif ($isPdf)
                                                        <svg class="w-7 h-7 text-red-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="w-7 h-7 text-gray-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                        </svg>
                                                    @endif
                                                    <span class="text-lg font-medium text-[#2c3856] truncate">{{ $fileLink->name }}</span>
                                                @else
                                                    <svg class="w-7 h-7 text-blue-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                    </svg>
                                                    <span class="text-lg font-medium text-[#2c3856] truncate">{{ $fileLink->name }}</span>
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
                                            <a href="{{ route('file_links.edit', $fileLink) }}" class="inline-flex items-center px-3 py-1 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="event.stopPropagation()">
                                                {{ __('Editar') }}
                                            </a>
                                            <form action="{{ route('file_links.destroy', $fileLink) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este elemento?'); event.stopPropagation();">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                    {{ __('Eliminar') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- FIN VISTA DE TABLA/FILAS --}}
                @endif
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
         style="display: none;" {{-- x-cloak handles display, but keep for initial render --}}
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
                <div class="mb-3">
                    <span class="font-semibold text-[#2c3856]">Fecha de Subida:</span> <span x-text="propertiesData.date"></span>
                </div>
                <div x-show="propertiesData.path" class="mb-3">
                    <span class="font-semibold text-[#2c3856]" x-text="propertiesData.isFolder ? 'Ruta:' : 'Ubicación:'"></span> <span x-text="propertiesData.path"></span>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button @click="showPropertiesModal = false"
                   class="inline-flex items-center px-5 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 shadow-md">
                    {{ __('Cerrar') }}
                </button>
            </div>
        </div>
    </div>
</x-app-layout>