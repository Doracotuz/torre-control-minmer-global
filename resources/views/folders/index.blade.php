<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-2xl text-[#2C3856]">
                    Bienvenido, {{ Auth::user()->name }}
                </span>
            </div>
            <div class="flex items-center space-x-4">
                @if ($currentFolder && !in_array(Auth::id(), ['4', '24', '25', '26', '27']))
                    <a href="{{ route('indicadores.show', ['folder' => $currentFolder->id]) }}" class="inline-flex items-center px-4 py-2 bg-[#FF9C00] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#2C3856] active:bg-[#9CB3ED] focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Ver Indicadores
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12" 
        x-data="fileManager(
            {{ $currentFolder ? $currentFolder->id : 'null' }},
            {{ json_encode($manageableAreas) }}
        )"
        x-init="init()">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-base font-semibold text-gray-700">
                @if ($currentFolder)
                    @if (!Auth::user()->is_client)
                    <a href="{{ route('folders.index') }}" class="text-[#2C3856] hover:text-blue-800">Dashboard</a>
                    @else
                    <a href="{{ route('tablero.index') }}" class="text-[#2C3856] hover:text-blue-800">Dashboard</a>
                    @endif
                    @foreach ($breadcrumbs as $breadcrumb)
                        <span class="text-gray-500">/</span>
                        <a href="{{ route('folders.index', ['folder' => $breadcrumb->id]) }}" class="text-[#2C3856] hover:text-blue-800">{{ $breadcrumb->name }}</a>
                    @endforeach
                    <span class="text-gray-500">/</span>
                    <span class="text-[#FF9C00]">{{ $currentFolder->name }}</span>
                @endif
            </h2>

            <div id="flash-success" class="fixed top-4 right-4 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert" style="display: none;">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <strong class="font-bold mr-1">{{ __('¡Éxito!') }}</strong>
                    <span id="flash-success-message" class="block sm:inline"></span>
                </div>
                <button @click="document.getElementById('flash-success').style.display = 'none';" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div id="flash-error" class="fixed top-4 right-4 z-50 bg-white border-l-4 border-red-600 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert" style="display: none;">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <strong class="font-bold mr-1">{{ __('¡Error!') }}</strong>
                    <span id="flash-error-message" class="block sm:inline"></span>
                </div>
                <button @click="document.getElementById('flash-error').style.display = 'none';" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="bg-[#F0F3FA] overflow-hidden shadow-xl rounded-[40px] border border-gray-200 p-4 sm:p-8"
                 @dragover.prevent="handleDragOver($event, {{ $currentFolder ? $currentFolder->id : 'null' }})"
                 @dragleave="handleDragLeave($event)"
                 @dragenter.self="handleMainDragEnter($event)"
                 @drop.prevent="handleDrop($event, {{ $currentFolder ? $currentFolder->id : 'null' }})"
                 @dragend="handleDragEnd($event)"
                 :class="{ 'border-blue-400 border-dashed bg-blue-100': highlightMainDropArea }"
            >

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 space-y-4 sm:space-y-0">
                    <h3 class="text-xl font-semibold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">{{ __('Contenido Actual') }}</h3>
                    
                    <div class="flex flex-wrap items-center justify-start sm:justify-end gap-3">

                        <div class="flex items-center">
                            @if(!Auth::user()->is_client)
                            <input type="checkbox" @change="selectAll($event)"
                                   :checked="selectedItems.length > 0 && selectedItems.length === ({{ count($folders) }} + {{ count($fileLinks) }})"
                                   class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] mr-2">
                            <label class="text-sm text-gray-700">{{ __('Seleccionar Todos') }}</label>
                            @endif
                        </div>

                        <input type="file" webkitdirectory directory multiple style="display: none;" 
                            x-ref="folderInput" 
                            @change="handleFolderSelected($event)">

                        @if(!Auth::user()->is_client)
                            <button @click="$refs.folderInput.click()"
                                    title="{{ __('Subir Carpeta') }}"
                                    class="inline-flex items-center justify-center p-2 sm:px-3 sm:py-1.5 bg-[#FF9C00] border border-transparent rounded-lg font-semibold text-xs text-white hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                <svg class="w-4 h-4 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                <span class="hidden sm:inline-block">{{ __('Subir Carpeta') }}</span>
                            </button>    
                        @endif                    

                        <button @click="deleteSelected()" x-show="isAnySelected()"
                                title="{{ __('Eliminar Seleccionados') }}"
                                class="inline-flex items-center justify-center p-2 sm:px-3 sm:py-1.5 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                            <svg class="w-4 h-4 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            <span class="hidden sm:inline-block">{{ __('Eliminar') }}</span>
                        </button>

                        <button @click="openMoveModal()" x-show="isAnySelected()"
                                title="{{ __('Mover Seleccionados') }}"
                                class="inline-flex items-center justify-center p-2 sm:px-3 sm:py-1.5 bg-[#2C3856] border border-transparent rounded-lg font-semibold text-xs text-white hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                            <svg class="w-4 h-4 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            <span class="hidden sm:inline-block">{{ __('Mover') }}</span>
                        </button>


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
                        @if(!Auth::user()->is_client)
                        <a href="{{ route('folders.create', ['folder' => $currentFolder ? $currentFolder->id : null]) }}" 
                           title="{{ __('Crear Carpeta') }}"
                           class="inline-flex items-center justify-center p-2 sm:px-3 sm:py-1.5 bg-[#2C3856] border border-transparent rounded-lg font-semibold text-xs text-white hover:bg-blue-800 focus:bg-blue-800 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                            <svg class="w-4 h-4 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="hidden sm:inline-block">{{ __('Crear Carpeta') }}</span>
                        </a>
                        @endif

                        @if (Auth::user()->isSuperAdmin() || Auth::user()->is_area_admin || (!Auth::user()->is_client && $currentFolder && Auth::user()->area_id == $currentFolder->area_id))
                            @if ($currentFolder)
                                <a href="{{ route('file_links.create', $currentFolder) }}"
                                   title="{{ __('Añadir Elemento') }}"
                                   class="inline-flex items-center justify-center p-2 sm:px-3 sm:py-1.5 bg-[#ff9c00] border border-transparent rounded-lg font-semibold text-xs text-white hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                                    <svg class="w-4 h-4 sm:mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    <span class="hidden sm:inline-block">{{ __('Añadir Elemento') }}</span>
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
                    <div x-show="isTileView"
                         :class="{
                             'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4': tileSize === 'small',
                             'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6': tileSize === 'medium',
                             'grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8': tileSize === 'large'
                         }">
                        @foreach ($folders as $folderItem)
                            <div class="bg-white rounded-3xl shadow-md p-4 sm:p-6 border border-gray-200 flex flex-col items-center justify-center text-center hover:shadow-xl transition-all duration-200 group
                                        hover:bg-gray-50 transform relative"
                                 draggable="true"
                                 x-on:dragstart="handleDragStart($event, {{ $folderItem->id }}, 'folder')"
                                 x-on:dragover.prevent="handleDragOver($event, {{ $folderItem->id }})"
                                 x-on:dragleave="handleDragLeave($event)"
                                 x-on:drop.prevent.stop="handleDrop($event, {{ $folderItem->id }})"
                                 x-on:contextmenu.prevent="openPropertiesModal({
                                     name: '{{ $folderItem->name }}',
                                     type: 'Carpeta',
                                     creator: '{{ $folderItem->user->name ?? 'N/A' }}',
                                     date: '{{ $folderItem->created_at->format('d M Y, H:i') }}',
                                     isFolder: true,
                                     path: '{{ $folderItem->parent ? $folderItem->parent->name . '/' : '' }}{{ $folderItem->name }}',
                                     item_count: '{{ $folderItem->items_count ?? 0 }}'
                                 })"
                                 :class="{'!bg-indigo-200 !border-indigo-600 !border-2 ring-4 ring-indigo-300 transform scale-110 z-20 shadow-2xl': dropTargetFolderId == {{ $folderItem->id }}}"
                                 x-data="{ showDetails: false }"
                            >
                                @if(!Auth::user()->is_client)
                                <input type="checkbox"
                                    class="absolute top-2 left-2 rounded border-gray-300 text-[black] shadow-sm focus:ring-[black] z-10"
                                    @click.stop="toggleSelection({{ $folderItem->id }}, 'folder')"
                                    :checked="isSelected({{ $folderItem->id }}, 'folder')"
                                >
                                @endif
                                <a href="{{ route('folders.index', $folderItem) }}"
                                   class="flex flex-col items-center justify-center w-full"
                                   onclick="event.stopPropagation()"
                                >
                                    <div class="inline-flex items-center justify-center rounded-full bg-blue-100 p-2 transition-transform duration-200"
                                        :class="{
                                            'w-14 h-14': tileSize === 'small',
                                            'w-20 h-20': tileSize === 'medium',
                                            'w-24 h-24': tileSize === 'large',
                                            'scale-110': dropTargetFolderId == {{ $folderItem->id }}
                                        }">
                                        <svg :class="{
                                                'w-8 h-8': tileSize === 'small',
                                                'w-12 h-12': tileSize === 'medium',
                                                'w-16 h-16': tileSize === 'large'
                                            }"
                                            class="text-[#2C3856] group-hover:text-orange-500 transition-colors duration-200"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                        </svg>
                                    </div>
                                        <span :class="{ 
                                                'text-sm': tileSize === 'small', 
                                                'text-base': tileSize === 'medium',
                                                'text-lg': tileSize === 'large' 
                                            }" 
                                            class="font-semibold text-[#2c3856] mb-1 w-full px-1 sm:px-2 break-words text-center">
                                            {{ $folderItem->name }}
                                        </span>
                                    <span class="text-xs text-gray-400 mt-1">{{ $folderItem->created_at->format('d M Y') }}</span>
                                    </a>

                                <button @click.stop="showDetails = !showDetails" class="mt-2 text-xs text-gray-500 hover:text-gray-700 focus:outline-none px-2 py-1 rounded-full border border-gray-300 hover:bg-gray-100 transition-colors duration-150">
                                    <span x-text="showDetails ? '{{ __('Ocultar Detalles') }}' : '{{ __('Ver Detalles') }}'"></span>
                                </button>

                                <div x-show="showDetails" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-y-0" x-transition:enter-end="opacity-100 transform scale-y-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-y-100" x-transition:leave-end="opacity-0 transform scale-y-0" class="text-center text-gray-600 text-xxs sm:text-xs mt-2 space-y-1 opacity-75 origin-top" style="display: none;">
                                    <p><span class="font-semibold">{{ __('Creado por:') }}</span> {{ $folderItem->user->name ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">{{ __('Tipo:') }}</span> Carpeta</p>
                                    <p><span class="font-semibold">{{ __('Fecha:') }}</span> {{ $folderItem->created_at->format('d M Y') }}</p>
                                    <p><span class="font-semibold">{{ __('Elementos Totales:') }}</span> {{ $folderItem->items_count ?? 0 }}</p>
                                </div>
                                @if(!Auth::user()->is_client)
                                <div class="mt-4 flex items-center justify-center space-x-2 w-full">
                                    <a href="{{ route('folders.edit', $folderItem) }}" 
                                       title="{{ __('Editar') }}"
                                       class="p-2 bg-slate-700 border border-transparent rounded-full font-bold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <button @click.prevent="deleteSingleItem({{ $folderItem->id }}, 'folder')"
                                        title="{{ __('Eliminar') }}"
                                        class="p-2 bg-red-500 border border-transparent rounded-full font-bold text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                                @endif
                            </div>
                        @endforeach

                        @foreach ($fileLinks as $fileLink)
                            @php
                                $fileExtension = $fileLink->type == 'file' ? pathinfo($fileLink->path, PATHINFO_EXTENSION) : null;
                                $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                $isPdf = strtolower($fileExtension) == 'pdf';
                                $isAudio = in_array(strtolower($fileExtension), ['mp3', 'wav', 'ogg']);
                                $isVideo = in_array(strtolower($fileExtension), ['mp4', 'webm', 'mov']);
                                $fileUrl = $fileLink->type == 'file' ? \Illuminate\Support\Facades\Storage::disk('s3')->url($fileLink->path) : $fileLink->url;
                                if ($isPdf) {
                                    $fileUrl .= '#toolbar=0';
                                }
                            @endphp
                            <div class="bg-white rounded-lg shadow-md p-4 sm:p-6 border border-gray-200 flex flex-col items-center justify-center text-center hover:shadow-lg transition-shadow duration-200 group
                                        hover:bg-gray-50 transform hover:scale-105 relative"
                                 draggable="true"
                                 x-on:dragstart="handleDragStart($event, {{ $fileLink->id }}, 'file_link')"
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
                                @if(!Auth::user()->is_client)
                                <input type="checkbox"
                                    class="absolute top-2 left-2 rounded border-gray-300 text-[black] shadow-sm focus:ring-[black] z-10"
                                    @click.stop="toggleSelection({{ $fileLink->id }}, 'file_link')"
                                    :checked="isSelected({{ $fileLink->id }}, 'file_link')"
                                >
                                @endif

                                @if ($fileLink->type == 'link')
                                    <a href="{{ $fileLink->url }}" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center justify-center w-full">
                                        <svg :class="{ 'w-12 h-12': tileSize === 'small', 'w-16 h-16': tileSize === 'medium', 'w-20 h-20': tileSize === 'large' }" class="text-blue-600 mb-2 sm:mb-3 group-hover:text-blue-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                            <span :class="{ 
                                                    'text-sm': tileSize === 'small', 
                                                    'text-base': tileSize === 'medium', 
                                                    'text-lg': tileSize === 'large' 
                                                }" 
                                                class="font-semibold text-[#2c3856] mb-1 w-full px-1 sm:px-2 break-words whitespace-normal text-center">
                                                {{ $fileLink->name }}
                                            </span>
                                        <span class="text-sm text-gray-500">{{ __('Enlace') }}</span>
                                    </a>
                                @else
                                    <button @click.prevent.stop="openMediaModal('{{ $fileLink->name }}', '{{ $fileLink->type == 'file' ? pathinfo($fileLink->path, PATHINFO_EXTENSION) : 'link' }}', '{{ $fileUrl }}', '{{ route('files.download', $fileLink) }}')"
                                        class="flex flex-col items-center justify-center w-full">
                                            @if ($isImage)
                                                <svg :class="{ 'w-12 h-12': tileSize === 'small', 'w-16 h-16': tileSize === 'medium', 'w-20 h-20': tileSize === 'large' }" class="text-green-600 mb-2 sm:mb-3 group-hover:text-green-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            @elseif ($isPdf)
                                                <svg :class="{ 'w-12 h-12': tileSize === 'small', 'w-16 h-16': tileSize === 'medium', 'w-20 h-20': tileSize === 'large' }" class="text-red-600 mb-2 sm:mb-3 group-hover:text-red-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            @elseif ($isVideo || $isAudio)
                                                <svg :class="{ 'w-12 h-12': tileSize === 'small', 'w-16 h-16': tileSize === 'medium', 'w-20 h-20': tileSize === 'large' }" class="text-indigo-600 mb-2 sm:mb-3 group-hover:text-indigo-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.196l-3.321-2.484a.5.5 0 00-.731.428v4.981a.5.5 0 00.73.429l3.322-2.484a.5.5 0 000-.858zM4 6v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z"></path>
                                                </svg>
                                            @else
                                                <svg :class="{ 'w-12 h-12': tileSize === 'small', 'w-16 h-16': tileSize === 'medium', 'w-20 h-20': tileSize === 'large' }" class="text-gray-600 mb-2 sm:mb-3 group-hover:text-gray-700 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                            @endif
                                            <span :class="{ 'text-base': tileSize === 'small', 'text-base': tileSize === 'medium', 'text-base': tileSize === 'large' }" class="font-semibold text-[#2c3856] mb-1 break-words w-full px-1 sm:px-2">
                                                {{ $fileLink->name }}
                                            </span>
                                            <span class="text-sm text-gray-500">{{ __('Archivo') }} ({{ strtoupper($fileExtension) }})</span>
                                    </button>
                                @endif

                                <span class="text-xxs text-gray-400 mt-1" :class="{'text-xxs': tileSize === 'small', 'text-xs': tileSize === 'medium', 'text-sm': tileSize === 'large'}">{{ $fileLink->created_at->format('d M Y') }}</span>
                                <button @click.stop="showDetails = !showDetails" class="mt-2 text-xxs sm:text-xs text-gray-500 hover:text-gray-700 focus:outline-none px-2 py-1 rounded-full border border-gray-300 hover:bg-gray-100 transition-colors duration-150">
                                    <span x-text="showDetails ? '{{ __('Ocultar Detalles') }}' : '{{ __('Ver Detalles') }}'"></span>
                                </button>

                                <div x-show="showDetails" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-y-0" x-transition:enter-end="opacity-100 transform scale-y-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-y-100" x-transition:leave-end="opacity-0 transform scale-y-0" class="text-center text-gray-600 text-xxs sm:text-xs mt-2 space-y-1 opacity-75 origin-top" style="display: none;">
                                    <p><span class="font-semibold">{{ __('Creado por:') }}</span> {{ $fileLink->user->name ?? 'N/A' }}</p>
                                    <p><span class="font-semibold">{{ __('Tipo:') }}</span> {{ $fileLink->type == 'file' ? 'Archivo' : 'Enlace' }}</p>
                                    <p><span class="font-semibold">{{ __('Fecha:') }}</span> {{ $fileLink->created_at->format('d M Y') }}</p>
                                </div>

                                @if(!Auth::user()->is_client)
                                <div class="mt-4 flex items-center justify-center space-x-2 w-full">
                                    <a href="{{ route('file_links.edit', $fileLink) }}" 
                                       title="{{ __('Editar') }}"
                                       class="p-2 bg-slate-700 border border-transparent rounded-full font-bold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <button @click.prevent="deleteSingleItem({{ $fileLink->id }}, 'file_link')"
                                        title="{{ __('Eliminar') }}"
                                        class="p-2 bg-red-500 border border-transparent rounded-full font-bold text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div x-show="!isTileView">
                        <div class="overflow-x-auto hidden sm:block">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-[#F0F3FA]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                                            @if(!Auth::user()->is_client)
                                            <input type="checkbox" @change="selectAll($event)"
                                                   :checked="selectedItems.length > 0 && selectedItems.length === ({{ count($folders) }} + {{ count($fileLinks) }})"
                                                   class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] mr-2">
                                            @endif
                                            {{ __('Nombre del Elemento') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Tipo') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Creado Por') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Fecha de Carga') }}
                                        </th>
                                        @if(!Auth::user()->is_client)
                                        <th scope="col" class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                                            {{ __('Acciones') }}
                                        </th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-[#F8F9FD] divide-y divide-gray-200">
                                    @foreach ($folders as $folderItem)
                                        <tr class="hover:bg-gray-100 transition-colors duration-150 cursor-pointer"
                                            @click="window.location.href = '{{ route('folders.index', $folderItem) }}'"
                                            draggable="true"
                                            x-on:dragstart="handleDragStart($event, {{ $folderItem->id }}, 'folder')"
                                            x-on:dragover.prevent="handleDragOver($event, {{ $folderItem->id }})"
                                            x-on:dragleave="handleDragLeave($event)"
                                            x-on:drop.prevent.stop="handleDrop($event, {{ $folderItem->id }})"
                                            x-on:contextmenu.prevent="openPropertiesModal({
                                                name: '{{ $folderItem->name }}',
                                                type: 'Carpeta',
                                                creator: '{{ $folderItem->user->name ?? 'N/A' }}',
                                                date: '{{ $folderItem->created_at->format('d M Y, H:i') }}',
                                                isFolder: true,
                                                path: '{{ $folderItem->parent ? $folderItem->parent->name . '/' : '' }}{{ $folderItem->name }}',
                                                item_count: '{{ $folderItem->items_count ?? 0 }}'
                                            })"
                                            :class="{'!bg-indigo-200 !border-indigo-600 !border-l-4': dropTargetFolderId == {{ $folderItem->id }}}"
                                        >
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if(!Auth::user()->is_client)
                                                    <input type="checkbox"
                                                        class="rounded border-gray-300 text-[Black] shadow-sm focus:ring-[Black] mr-2"
                                                        @click.stop="toggleSelection({{ $folderItem->id }}, 'folder')"
                                                        :checked="isSelected({{ $folderItem->id }}, 'folder')"
                                                    >
                                                    @endif
                                                    <div class="flex items-center">
                                                            <div class="inline-flex items-center justify-center rounded-full bg-blue-100 p-2"
                                                                :class="{
                                                                    'w-14 h-14': tileSize === 'small',
                                                                    'w-20 h-20': tileSize === 'medium',
                                                                    'w-24 h-24': tileSize === 'large'
                                                                }">
                                                                <svg :class="{
                                                                        'w-8 h-8': tileSize === 'small',
                                                                        'w-12 h-12': tileSize === 'medium',
                                                                        'w-16 h-16': tileSize === 'large'
                                                                    }"
                                                                    class="text-[Black] group-hover:text-orange-500 transition-colors duration-200"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    viewBox="0 0 24 24"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                                </svg>
                                                            </div>
                                                        <div class="flex flex-col gap-1">
                                                            <span class="pl-2 text-base font-medium text-[#2c3856] break-words whitespace-normal">
                                                                {{ $folderItem->name }}
                                                            </span>
                                                            <span class="pl-2 text-sm text-gray-500">
                                                                ({{ $folderItem->items_count ?? 0 }} elementos)
                                                            </span>
                                                        </div>
                                                    </div>
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
                                            @if(!Auth::user()->is_client)
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3" @click.stop="">
                                                <a href="{{ route('folders.edit', $folderItem) }}" 
                                                   title="{{ __('Editar') }}"
                                                   class="inline-flex items-center p-2 bg-slate-700 border border-transparent rounded-full font-bold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </a>
                                                <button @click.prevent="deleteSingleItem({{ $folderItem->id }}, 'folder')"
                                                    title="{{ __('Eliminar') }}"
                                                    class="inline-flex items-center p-2 bg-red-500 border border-transparent rounded-full font-bold text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                            @endif
                                        </tr>
                                    @endforeach

                                    @foreach ($fileLinks as $fileLink)
                                        @php
                                            $fileExtension = $fileLink->type == 'file' ? pathinfo($fileLink->path, PATHINFO_EXTENSION) : null;
                                            $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                            $isPdf = strtolower($fileExtension) == 'pdf';
                                            $isAudio = in_array(strtolower($fileExtension), ['mp3', 'wav', 'ogg']);
                                            $isVideo = in_array(strtolower($fileExtension), ['mp4', 'webm', 'mov']);
                                            $fileUrl = $fileLink->type == 'file' ? \Illuminate\Support\Facades\Storage::disk('s3')->url($fileLink->path) : $fileLink->url;
                                            if ($isPdf) {
                                                $fileUrl .= '#toolbar=0';
                                            }
                                        @endphp
                                        <tr class="hover:bg-gray-100 transition-colors duration-150"
                                            draggable="true"
                                            x-on:dragstart="handleDragStart($event, {{ $fileLink->id }}, 'file_link')"
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
                                                    @if(!Auth::user()->is_client)
                                                    <input type="checkbox"
                                                        class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] mr-2"
                                                        @click.stop="toggleSelection({{ $fileLink->id }}, 'file_link')"
                                                        :checked="isSelected({{ $fileLink->id }}, 'file_link')"
                                                    >
                                                    @endif

                                                    @if ($fileLink->type == 'link')
                                                        <a href="{{ $fileLink->url }}" target="_blank" rel="noopener noreferrer" class="flex items-center cursor-pointer group">
                                                            <svg class="w-7 h-7 text-blue-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                            </svg>
                                                            <span class="text-base font-medium text-[#2c3856] break-words group-hover:underline">{{ $fileLink->name }}</span>
                                                        </a>
                                                    @else
                                                        <button @click.prevent.stop="openMediaModal('{{ $fileLink->name }}', '{{ $fileLink->type == 'file' ? pathinfo($fileLink->path, PATHINFO_EXTENSION) : 'link' }}', '{{ $fileUrl }}', '{{ route('files.download', $fileLink) }}')"
                                                                class="flex items-center cursor-pointer"
                                                        >
                                                            @if ($isImage)
                                                                <svg class="w-7 h-7 text-green-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                </svg>
                                                            @elseif ($isPdf)
                                                                <svg class="w-7 h-7 text-red-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                                </svg>
                                                            @elseif ($isVideo || $isAudio)
                                                                <svg class="w-7 h-7 text-indigo-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.196l-3.321-2.484a.5.5 0 00-.731.428v4.981a.5.5 0 00.73.429l3.322-2.484a.5.5 0 000-.858zM4 6v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z"></path>
                                                                </svg>
                                                            @else
                                                                <svg class="w-7 h-7 text-gray-600 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                                </svg>
                                                            @endif
                                                            <span class="text-base font-medium text-[#2c3856] break-words whitespace-normal text-left w-full">{{ $fileLink->name }}</span>
                                                        </button>
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
                                            @if(!Auth::user()->is_client)
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3" @click.stop="">
                                                <a href="{{ route('file_links.edit', $fileLink) }}" 
                                                   title="{{ __('Editar') }}"
                                                   class="inline-flex items-center p-2 bg-slate-700 border border-transparent rounded-full font-bold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </a>
                                                <button @click.prevent="deleteSingleItem({{ $fileLink->id }}, 'file_link')"
                                                    title="{{ __('Eliminar') }}"
                                                    class="inline-flex items-center p-2 bg-red-500 border border-transparent rounded-full font-bold text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="sm:hidden space-y-4">
                            @foreach ($folders as $folderItem)
                                <div class="bg-white shadow overflow-hidden rounded-lg border border-gray-200 p-4 relative">
                                    @if(!Auth::user()->is_client)
                                    <input type="checkbox"
                                        class="absolute top-2 left-2 rounded border-gray-300 text-[black] shadow-sm focus:ring-[black] z-10"
                                        @click.stop="toggleSelection({{ $folderItem->id }}, 'folder')"
                                        :checked="isSelected({{ $folderItem->id }}, 'folder')"
                                    >
                                    @endif
                                    <div class="flex items-center space-x-3 mb-2">
                                        <a href="{{ route('folders.index', $folderItem) }}" class="flex items-center flex-shrink-0" onclick="event.stopPropagation()">
                                            <svg class="w-7 h-7 text-[Black]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                            </svg>
                                        </a>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-base font-semibold text-[#2c3856] break-words">
                                                <a href="{{ route('folders.index', $folderItem) }}" class="hover:underline" onclick="event.stopPropagation()">
                                                    {{ $folderItem->name }}
                                                </a>
                                            </p>
                                            <p class="text-sm text-gray-500 truncate">{{ __('Carpeta') }} ({{ $folderItem->items_count ?? 0 }} elementos)</p>
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-100 pt-3 mt-3 space-y-1 text-sm text-gray-700">
                                        <p><span class="font-medium text-gray-600">{{ __('Creado Por:') }}</span> {{ $folderItem->user->name ?? 'N/A' }}</p>
                                        <p><span class="font-medium text-gray-600">{{ __('Fecha:') }}</span> {{ $folderItem->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                    @if(!Auth::user()->is_client)
                                    <div class="flex justify-end gap-2 mt-4">
                                        <a href="{{ route('folders.edit', $folderItem) }}" 
                                           title="{{ __('Editar') }}"
                                           class="inline-flex items-center p-2 bg-slate-700 border border-transparent rounded-full font-bold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                        <form action="{{ route('folders.destroy', $folderItem) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta carpeta? Esto también eliminará todo su contenido (subcarpetas, archivos y enlaces).'); event.stopPropagation();">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    title="{{ __('Eliminar') }}"
                                                    class="inline-flex items-center p-2 bg-red-500 border border-transparent rounded-full font-bold text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            @endforeach

                            @foreach ($fileLinks as $fileLink)
                                @php
                                    $fileExtension = $fileLink->type == 'file' ? pathinfo($fileLink->path, PATHINFO_EXTENSION) : null;
                                    $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                    $isPdf = strtolower($fileExtension) == 'pdf';
                                    $isAudio = in_array(strtolower($fileExtension), ['mp3', 'wav', 'ogg']);
                                    $isVideo = in_array(strtolower($fileExtension), ['mp4', 'webm', 'mov']);
                                    $fileUrl = $fileLink->type == 'file' ? \Illuminate\Support\Facades\Storage::disk('s3')->url($fileLink->path) : $fileLink->url;
                                    if ($isPdf) {
                                        $fileUrl .= '#toolbar=0';
                                    }
                                @endphp
                                <div class="bg-white shadow overflow-hidden rounded-lg border border-gray-200 p-4 relative"
                                     draggable="true"
                                     x-on:dragstart="handleDragStart($event, {{ $fileLink->id }}, 'file_link')"
                                >
                                    @if(!Auth::user()->is_client)
                                    <input type="checkbox"
                                        class="absolute top-2 left-2 rounded border-gray-300 text-[black] shadow-sm focus:ring-[black] z-10"
                                        @click.stop="toggleSelection({{ $fileLink->id }}, 'file_link')"
                                        :checked="isSelected({{ $fileLink->id }}, 'file_link')"
                                    >
                                    @endif
                                    <div class="flex items-center space-x-3 mb-2">
                                        @if ($fileLink->type == 'link')
                                            <a href="{{ $fileLink->url }}" target="_blank" rel="noopener noreferrer" class="flex items-center space-x-3 w-full group">
                                                <svg class="w-7 h-7 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-base font-semibold text-[#2c3856] break-words group-hover:underline">
                                                        {{ $fileLink->name }}
                                                    </p>
                                                    <p class="text-sm text-gray-500 truncate">
                                                        {{ __('Enlace') }}
                                                    </p>
                                                </div>
                                            </a>
                                        @else
                                            <button @click.prevent.stop="openMediaModal('{{ $fileLink->name }}', '{{ pathinfo($fileLink->path, PATHINFO_EXTENSION) }}', '{{ $fileUrl }}', '{{ route('files.download', $fileLink) }}')"
                                                    class="flex items-center space-x-3 w-full text-left">
                                                @if ($isImage)
                                                    <svg class="w-7 h-7 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                @elseif ($isPdf)
                                                    <svg class="w-7 h-7 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                @elseif ($isVideo || $isAudio)
                                                    <svg class="w-7 h-7 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.196l-3.321-2.484a.5.5 0 00-.731.428v4.981a.5.5 0 00.73.429l3.322-2.484a.5.5 0 000-.858zM4 6v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-7 h-7 text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-base font-semibold text-[#2c3856] break-words">
                                                        {{ $fileLink->name }}
                                                    </p>
                                                    <p class="text-sm text-gray-500 truncate">
                                                        {{ __('Archivo') }} ({{ strtoupper($fileExtension) }})
                                                    </p>
                                                </div>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="border-t border-gray-100 pt-3 mt-3 space-y-1 text-sm text-gray-700">
                                        <p><span class="font-medium text-gray-600">{{ __('Creado Por:') }}</span> {{ $fileLink->user->name ?? 'N/A' }}</p>
                                        <p><span class="font-medium text-gray-600">{{ __('Fecha:') }}</span> {{ $fileLink->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                    @if(!Auth::user()->is_client)
                                    <div class="flex justify-end gap-2 mt-4">
                                        <a href="{{ route('file_links.edit', $fileLink) }}" 
                                           title="{{ __('Editar') }}"
                                           class="inline-flex items-center p-2 bg-slate-700 border border-transparent rounded-full font-bold text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>
                                        <form action="{{ route('file_links.destroy', $fileLink) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este elemento?'); event.stopPropagation();">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    title="{{ __('Eliminar') }}"
                                                    class="inline-flex items-center p-2 bg-red-500 border border-transparent rounded-full font-bold text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
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
                    <button @click="showPropertiesModal = false" class="text-gray-500 hover:text-gray-700 focus:outline-none">
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
                    <div x-show="propertiesData.isFolder" class="mb-3">
                        <span class="font-semibold text-[#2c3856]">Elementos en carpeta:</span> <span x-text="propertiesData.item_count"></span>
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
        <div id="loading-overlay" x-show="isUploading"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-75"
            style="display: none;">
            <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center w-full max-w-md">
                <h3 class="text-xl font-semibold text-[#2c3856] mb-2">Subiendo Carpeta</h3>
                
                <p class="text-gray-600 mb-4">
                    <span x-text="`Archivo ${uploadCurrentFile} de ${uploadTotalFiles}`"></span>
                </p>

                <p class="text-sm text-gray-500 mb-2 w-full text-center truncate" x-text="uploadCurrentFileName"></p>

                <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                    <div class="bg-[#FF9C00] h-4 rounded-full transition-all duration-150" 
                        :style="`width: ${uploadProgress}%`">
                    </div>
                </div>
                
                <p class="text-[#2c3856] font-semibold text-lg">
                    <span x-text="`${uploadProgress}%`"></span>
                </p>
            </div>
        </div>

        <div x-show="showMoveModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50"
             style="display: none;"
             @click.away="showMoveModal = false"
             @keydown.escape.window="showMoveModal = false"
        >
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md max-h-[90vh] overflow-hidden flex flex-col" @click.stop="">
                <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-4">
                    <h3 class="text-xl font-semibold text-[#2c3856]">{{ __('Mover Elementos Seleccionados') }}</h3>
                    <button @click="showMoveModal = false" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto text-gray-700 text-base">
                    <p class="mb-3 text-lg font-semibold">Mover a: <span class="text-[#ff9c00]" x-text="moveTargetFolderName"></span></p>

                    <div class="border border-gray-200 rounded-lg p-3">
                        <p class="font-medium text-gray-800 mb-2">{{ __('Navegar a carpeta de destino:') }}</p>
                        <div class="mb-3 text-sm font-medium text-gray-500">
                            <ol class="list-none p-0 inline-flex items-center flex-wrap">
                                <li class="flex items-center">
                                    <a href="#" @click.prevent="browseMovePathSegment(-1)" class="text-[#2c3856] hover:text-[#ff9c00] transition-colors duration-200 font-semibold">{{ __('Raíz') }}</a>
                                    <svg x-show="currentMoveBrowseFolder" class="fill-current w-3 h-3 mx-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 67.254c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.476 239.029c9.373 9.372 9.373 24.568 0 33.942z"/></svg>
                                </li>
                                <template x-if="currentMoveBrowseFolder && currentMoveBrowseFolder.pathSegments">
                                    <template x-for="(segment, index) in currentMoveBrowseFolder.pathSegments" :key="segment.folder.id">
                                        <li class="flex items-center">
                                            <a href="#" @click.prevent="browseMovePathSegment(index)" class="text-[#2c3856] hover:text-[#ff9c00] transition-colors duration-200 font-semibold" x-text="segment.folder.name"></a>
                                            <svg x-show="index < currentMoveBrowseFolder.pathSegments.length - 1" class="fill-current w-3 h-3 mx-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 67.254c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.476 239.029c9.373 9.372 9.373 24.568 0 33.942z"/></svg>
                                        </li>
                                    </template>
                                </template>
                            </ol>
                        </div>


                        <ul class="space-y-2 max-h-64 overflow-y-auto border border-gray-300 rounded-lg p-3 bg-gray-50">
                            <template x-if="availableMoveFolders.length === 0">
                                <li class="text-gray-500 py-2 px-3">{{ __('No hay subcarpetas disponibles en esta ubicación.') }}</li>
                            </template>
                            <template x-for="folder in availableMoveFolders" :key="folder.id">
                                <li class="flex items-center justify-between p-2 rounded-md hover:bg-gray-100 cursor-pointer"
                                    @click="browseMoveFolder(folder)">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-[Black] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                        <span class="text-gray-800" x-text="folder.name"></span>
                                    </div>
                                    <span class="text-xs text-gray-500" x-text="folder.items_count + ' elementos'"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200 mt-4">
                    <button @click="showMoveModal = false"
                       class="inline-flex items-center px-5 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 shadow-md">
                        {{ __('Cancelar') }}
                    </button>
                    <button @click="confirmMove()"
                       :disabled="selectedItems.length === 0"
                       class="ml-3 inline-flex items-center px-5 py-2 bg-blue-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105 shadow-md">
                        {{ __('Mover Aquí') }}
                    </button>
                </div>
            </div>
        </div>

        <div x-show="showMediaModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="fixed inset-0 bg-gray-900 bg-opacity-80 flex items-center justify-center p-4 z-50"
            style="display: none;"
            @click.away="closeMediaModal()"
            @keydown.escape.window="closeMediaModal()">
            
            <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl max-h-[95vh] overflow-hidden flex flex-col relative"
                :class="fullscreen && ['pdf', 'word', 'excel', 'image', 'video'].includes(mediaModalData.type) ? '!max-w-none !rounded-none !max-h-none !h-screen !w-screen !m-0 fixed inset-0 z-[60]' : ''"
                @click.stop>
                
                <div class="flex justify-between items-center p-4 border-b border-gray-200 z-50 bg-white"
                    :class="fullscreen ? 'shadow-sm' : ''">
                    
                    <div class="flex items-center gap-3 overflow-hidden">
                        <h3 class="text-xl font-semibold text-[#2c3856] truncate max-w-md" x-text="mediaModalData.name"></h3>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <template x-if="fullscreen">
                            <div class="flex items-center bg-gray-100 rounded-lg mr-4 border border-gray-200">
                                <button @click="zoomOut()" class="p-2 text-gray-600 hover:text-[#ff9c00] hover:bg-gray-200 rounded-l-lg transition-colors" title="Reducir">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                                </button>
                                <span class="text-xs font-bold text-gray-500 w-12 text-center" x-text="Math.round(zoomLevel * 100) + '%'"></span>
                                <button @click="zoomIn()" class="p-2 text-gray-600 hover:text-[#ff9c00] hover:bg-gray-200 rounded-r-lg transition-colors" title="Aumentar">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </button>
                            </div>
                        </template>

                        <template x-if="['pdf', 'word', 'excel', 'image', 'video'].includes(mediaModalData.type)">
                            <button @click="fullscreen = !fullscreen; zoomLevel = 1;" 
                                    class="text-gray-500 hover:text-[#2c3856] p-2 rounded-full hover:bg-gray-100 transition-all mr-1"
                                    :title="fullscreen ? 'Restaurar tamaño' : 'Pantalla completa'">
                                <svg x-show="!fullscreen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                                <svg x-show="fullscreen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0l5 0m-5 0l0 5M15 9l5-5m0 0l-5 0m5 0l0 5M9 15l-5 5m0 0l5 0m-5 0l0-5M15 15l5 5m0 0l-5 0m5 0l0-5"></path></svg>
                            </button>
                        </template>
                        
                        <button @click="closeMediaModal()" class="text-gray-400 hover:text-red-500 p-2 rounded-full hover:bg-red-50 transition-colors" title="Cerrar">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 bg-gray-100 overflow-hidden relative flex flex-col w-full h-full">
                    
                    <div x-show="mediaModalData.loading" class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 z-20">
                        <div class="loader-spinner mb-3"></div>
                        <p class="text-sm font-bold text-gray-500">Cargando documento...</p>
                    </div>

                    <div id="fs-container" class="w-full h-full overflow-auto flex transition-all duration-200" 
                        :class="fullscreen ? '!p-0' : 'p-4'"
                        :style="mediaModalData.type === 'excel' ? 'justify-content: flex-start;' : 'justify-content: center;'">
                        
                        <div class="flex min-h-full min-w-full transition-transform duration-200 ease-out"
                            :class="mediaModalData.type === 'excel' ? 'items-start justify-start' : 'items-center justify-center'"
                            :style="zoomLevel === 1 ? '' : 'transform: scale(' + zoomLevel + '); transform-origin: ' + (mediaModalData.type === 'excel' ? 'top left' : 'top center') + ';'">

                            <template x-if="mediaModalData.type === 'image'">
                                <div class="flex items-center justify-center w-full min-h-full py-4">
                                    <img :src="mediaModalData.url" alt="Vista previa" 
                                        class="max-w-full rounded-lg shadow-lg object-contain transition-all duration-300"
                                        :class="fullscreen ? 'max-h-none shadow-none' : 'max-h-[70vh]'">
                                </div>
                            </template>
                            
                            <template x-if="mediaModalData.type === 'video'">
                                <div class="flex items-center justify-center w-full min-h-full">
                                    <video :src="mediaModalData.url" 
                                        controls 
                                        preload="metadata"
                                        class="max-w-full rounded-lg shadow-lg transition-all duration-300"
                                        :class="fullscreen ? 'max-h-[95vh] !rounded-none !shadow-none' : 'max-h-[70vh]'">
                                        Tu navegador no soporta la reproducción de videos.
                                    </video>
                                </div>
                            </template>
                            
                            <template x-if="mediaModalData.type === 'pdf'">
                                <iframe :src="mediaModalData.url" class="w-full bg-white shadow-lg"
                                        :class="fullscreen ? 'h-[95vh] w-[95vw]' : 'h-[70vh] w-full rounded-lg border-2 border-gray-300'"></iframe>
                            </template>
                            
                            <template x-if="mediaModalData.type === 'audio'">
                                <div class="flex items-center justify-center w-full min-h-full">
                                    <audio :src="mediaModalData.url" controls class="w-full max-w-sm shadow-md rounded-full"></audio>
                                </div>
                            </template>

                            <div x-show="mediaModalData.type === 'word'" id="word-container" class="w-full"
                                :class="fullscreen ? 'h-auto' : 'h-[70vh]'"></div>

                            <div x-show="mediaModalData.type === 'excel'" 
                                class="bg-white border border-gray-200 overflow-visible inline-block"
                                :class="fullscreen ? 'h-auto min-w-full' : 'h-[70vh] w-full rounded-lg'">
                                <div id="excel-container" class="excel-viewer"></div>
                            </div>
                            
                            <template x-if="mediaModalData.type === 'other'">
                                <div class="flex flex-col items-center justify-center p-10 w-full h-full">
                                    <svg class="w-24 h-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-gray-600 font-semibold text-lg">Vista previa no disponible</p>
                                    <a :href="mediaModalData.downloadUrl" class="mt-4 text-[#ff9c00] hover:underline font-bold">Descargar archivo</a>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end p-4 border-t border-gray-200 bg-white z-10">
                    <a :href="mediaModalData.downloadUrl" :download="mediaModalData.name"
                    class="inline-flex items-center px-4 py-2 bg-[#FF9C00] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 focus:outline-none shadow-md transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Descargar
                    </a>
                    <button @click="closeMediaModal()"
                        class="ml-3 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none shadow-md transition-all">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        <div x-show="showAreaModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-[60]"
             style="display: none;"
             @keydown.escape.window="showAreaModal = false"
        >
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.stop="">
                <h3 class="text-xl font-semibold text-[#2c3856] mb-4">Seleccionar Área de Destino</h3>
                <p class="text-gray-600 text-sm mb-4">
                    Vas a subir elementos a la carpeta raíz. Por favor, selecciona a qué área pertenecerán.
                </p>
                
                <div class="mb-4">
                    <label for="area_id_select" class="block text-sm font-medium text-gray-700">Área</label>
                    <select id="area_id_select" x-model="selectedUploadAreaId" 
                            class="mt-1 block w-full border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] rounded-md shadow-sm">
                        <option value="" disabled>Selecciona un área...</option>
                        <template x-for="area in manageableAreas" :key="area.id">
                            <option :value="area.id" x-text="area.name"></option>
                        </template>
                    </select>
                </div>
                
                <div class="flex justify-end pt-4 border-t border-gray-200 mt-4 space-x-3">
                    <button @click="showAreaModal = false; areaModalCallback = null;"
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Cancelar') }}
                    </button>
                    <button @click="confirmAreaSelection()"
                       :disabled="selectedUploadAreaId === ''"
                       class="inline-flex items-center px-4 py-2 bg-[#2c3856] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#1a2233] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25">
                        {{ __('Confirmar y Subir') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.8/dist/axios.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                sessionStorage.setItem('flash_success', '{{ session('success') }}');
            @endif
            @if (session('error'))
                sessionStorage.setItem('flash_error', '{{ session('error') }}');
            @endif
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('fileManager', (currentFolderId, manageableAreas) => ({
                showPropertiesModal: false,
                propertiesData: {},
                isUploading: false,
                uploadProgress: 0,
                uploadTotalFiles: 0,
                uploadCurrentFile: 0,
                uploadCurrentFileName: '',
                isTileView: true,
                tileSize: 'medium',
                selectedItems: [],
                draggingItemId: null,
                draggingItemType: null,
                dropTargetFolderId: null,
                highlightMainDropArea: false,
                isDraggingFile: false,
                showMediaModal: false,
                fullscreen: false,
                zoomLevel: 1,
                mediaModalData: {
                    name: '',
                    type: '',
                    url: '',
                    downloadUrl: ''
                },
                showMoveModal: false,
                moveTargetFolderId: null,
                moveTargetFolderName: 'Selecciona una carpeta...',
                availableMoveFolders: [],
                currentMoveBrowseFolder: null,
                showAreaModal: false,
                areaModalCallback: null,
                selectedUploadAreaId: '{{ Auth::user()->area_id }}',
                currentFolderId: currentFolderId,
                manageableAreas: manageableAreas,

                openPropertiesModal(itemData) {
                    this.showPropertiesModal = true;
                    this.propertiesData = itemData;
                },

                handleDragStart(event, itemId, itemType) {
                    this.draggingItemId = itemId;
                    this.draggingItemType = itemType;
                    event.dataTransfer.setData('text/plain', JSON.stringify({ id: itemId, type: itemType }));
                    event.dataTransfer.effectAllowed = 'move';
                },

                handleDragOver(event, targetFolderId) {
                    event.preventDefault();
                    const isFileDrag = event.dataTransfer.types.includes('Files');
                    const isInternalItemDrag = this.draggingItemId !== null;

                    if (isFileDrag || isInternalItemDrag) {
                        this.dropTargetFolderId = targetFolderId;
                        this.isDraggingFile = isFileDrag;
                        this.highlightMainDropArea = isFileDrag && (targetFolderId === null || targetFolderId === {{ $currentFolder ? $currentFolder->id : 'null' }});
                        event.dataTransfer.dropEffect = isFileDrag ? 'copy' : 'move';
                    } else {
                        event.dataTransfer.dropEffect = 'none';
                        this.dropTargetFolderId = null;
                        this.isDraggingFile = false;
                        this.highlightMainDropArea = false;
                    }
                },

                handleMainDragEnter(event) {
                    event.preventDefault();
                    const isFileDrag = event.dataTransfer.types.includes('Files');
                    if (isFileDrag) {
                        this.highlightMainDropArea = true;
                        this.dropTargetFolderId = {{ $currentFolder ? $currentFolder->id : 'null' }};
                    }
                },

                handleDragLeave(event, targetFolderId = null) {
                    if (!event.relatedTarget || !event.currentTarget.contains(event.relatedTarget)) {
                        this.dropTargetFolderId = null;
                        this.isDraggingFile = false;
                        this.highlightMainDropArea = false;
                    }
                },

                handleDragEnd(event) {
                    this.draggingItemId = null;
                    this.draggingItemType = null;
                    this.dropTargetFolderId = null;
                    this.isDraggingFile = false;
                    this.highlightMainDropArea = false;
                },

                async handleDrop(event, targetFolderId) {
                    event.preventDefault();
                    this.highlightMainDropArea = false;
                    this.dropTargetFolderId = null;

                    const items = event.dataTransfer.items;
                    const draggedData = event.dataTransfer.getData('text/plain');

                    if (items && items.length > 0 && !draggedData) {
                        const fileList = await this.getFilesFromDroppedItems(items);
                        if (fileList.length > 0) {
                            if (targetFolderId === null) {
                                this.showAreaModal = true;
                                this.areaModalCallback = () => this.uploadFilesWithProgress(fileList, null, this.selectedUploadAreaId);
                            } else {
                                this.uploadFilesWithProgress(fileList, targetFolderId, null);
                            }
                        }
                    } else if (draggedData) {
                        let draggedItem = null;
                        try {
                            draggedItem = JSON.parse(draggedData);
                        } catch (e) {
                            return;
                        }

                        if (draggedItem && draggedItem.id && (draggedItem.type !== 'folder' || draggedItem.id != targetFolderId)) {
                            
                            let requestBody = { target_folder_id: targetFolderId };
                            if (draggedItem.type === 'folder') {
                                requestBody.folder_ids = [draggedItem.id];
                                requestBody.file_link_ids = [];
                            } else if (draggedItem.type === 'file_link') {
                                requestBody.folder_ids = [];
                                requestBody.file_link_ids = [draggedItem.id];
                            } else { 
                                return;
                            }
                            
                            const loadingOverlay = document.getElementById('loading-overlay');
                            if (loadingOverlay) loadingOverlay.style.display = 'flex';

                            fetch('{{ route("items.bulk_move") }}', {
                                method: 'POST',
                                headers: { 
                                    'Content-Type': 'application/json', 
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                                },
                                body: JSON.stringify(requestBody)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    sessionStorage.setItem('flash_success', data.message);
                                } else {
                                    sessionStorage.setItem('flash_error', data.message);
                                }
                                window.location.reload();
                            })
                            .catch(error => {
                                sessionStorage.setItem('flash_error', 'Ocurrió un error de red al intentar mover el elemento.');
                                window.location.reload();
                            });
                        }
                    }
                },

                toggleSelection(itemId, itemType) {
                    const index = this.selectedItems.findIndex(item => item.id === itemId && item.type === itemType);
                    if (index > -1) {
                        this.selectedItems.splice(index, 1);
                    } else {
                        this.selectedItems.push({ id: itemId, type: itemType });
                    }
                },

                isSelected(itemId, itemType) {
                    return this.selectedItems.some(item => item.id === itemId && item.type === itemType);
                },

                isAnySelected() {
                    return this.selectedItems.length > 0;
                },

                selectAll(event) {
                    this.selectedItems = [];

                    if (event.target.checked) {
                        @foreach($folders as $folderItem)
                            this.selectedItems.push({ id: {{ $folderItem->id }}, type: 'folder' });
                        @endforeach
                        @foreach($fileLinks as $fileLink)
                            this.selectedItems.push({ id: {{ $fileLink->id }}, type: 'file_link' });
                        @endforeach
                    }
                },

                deleteSingleItem(id, type) {
                    if (!confirm('¿Estás seguro de que quieres eliminar este elemento?')) {
                        return;
                    }

                    document.getElementById('loading-overlay').classList.remove('hidden');

                    let url;
                    if (type === 'folder') {
                        url = `{{ route('folders.destroy', ['folder' => 'ITEM_ID']) }}`.replace('ITEM_ID', id);
                    } else {
                        url = `{{ route('file_links.destroy', ['fileLink' => 'ITEM_ID']) }}`.replace('ITEM_ID', id);
                    }

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'X-HTTP-Method-Override': 'DELETE'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('loading-overlay').classList.add('hidden');
                        if (data.success) {
                            document.getElementById('flash-success-message').innerText = data.message;
                            document.getElementById('flash-success').style.display = 'flex';
                            setTimeout(() => {
                                document.getElementById('flash-success').style.display = 'none';
                                window.location.reload();
                            }, 2000);
                        } else {
                            document.getElementById('flash-error-message').innerText = data.message;
                            document.getElementById('flash-error').style.display = 'flex';
                            setTimeout(() => {
                                document.getElementById('flash-error').style.display = 'none';
                                window.location.reload();
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        document.getElementById('loading-overlay').classList.add('hidden');
                        document.getElementById('flash-error-message').innerText = 'Ocurrió un error de red al intentar eliminar el elemento.';
                        document.getElementById('flash-error').style.display = 'flex';
                        setTimeout(() => {
                            document.getElementById('flash-error').style.display = 'none';
                            window.location.reload();
                        }, 2000);
                    });
                },

                zoomIn() {
                    this.zoomLevel = Math.min(this.zoomLevel + 0.25, 3);
                },
                zoomOut() {
                    this.zoomLevel = Math.max(this.zoomLevel - 0.25, 0.1);
                },
                
                closeMediaModal() {
                    this.showMediaModal = false;
                    
                    setTimeout(() => {
                        this.fullscreen = false;
                        this.zoomLevel = 1;
                        this.mediaModalData.type = ''; 
                        this.mediaModalData.url = '';
                    }, 200); 
                },                

                async openMediaModal(name, extension, url, downloadUrl) {
                    const fileExtension = extension.toLowerCase();
                    const videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi'];
                    const audioExtensions = ['mp3', 'wav', 'ogg'];
                    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'];
                    const wordExtensions = ['docx'];
                    const excelExtensions = ['xlsx', 'xls', 'csv'];
                    const pdfExtension = 'pdf';

                    let mediaType = 'other';
                    
                    const wordContainer = document.getElementById('word-container');
                    const excelContainer = document.getElementById('excel-container');
                    if(wordContainer) wordContainer.innerHTML = '';
                    if(excelContainer) excelContainer.innerHTML = '';

                    if (imageExtensions.includes(fileExtension)) mediaType = 'image';
                    else if (videoExtensions.includes(fileExtension)) mediaType = 'video';
                    else if (audioExtensions.includes(fileExtension)) mediaType = 'audio';
                    else if (fileExtension === pdfExtension) mediaType = 'pdf';
                    else if (wordExtensions.includes(fileExtension)) mediaType = 'word';
                    else if (excelExtensions.includes(fileExtension)) mediaType = 'excel';

                    this.mediaModalData = {
                        name: name,
                        type: mediaType,
                        url: url,
                        downloadUrl: downloadUrl,
                        loading: false
                    };
                    
                    this.showMediaModal = true;
                    this.zoomLevel = 1;
                    this.fullscreen = false;                    

                    if (mediaType === 'word' || mediaType === 'excel') {
                        this.mediaModalData.loading = true;
                        
                        try {
                            const response = await fetch(downloadUrl);
                            if (!response.ok) throw new Error('Error al descargar archivo');
                            const blob = await response.blob();

                            if (mediaType === 'word') {
                                const options = {
                                    className: "docx", 
                                    inWrapper: true, 
                                    ignoreWidth: false, 
                                    breakPages: true,
                                    trimXmlDeclaration: true
                                };
                                await docx.renderAsync(blob, wordContainer, null, options);
                            } 
                            else if (mediaType === 'excel') {
                                const arrayBuffer = await blob.arrayBuffer();
                                const workbook = XLSX.read(arrayBuffer);
                                const firstSheetName = workbook.SheetNames[0];
                                const worksheet = workbook.Sheets[firstSheetName];
                                const html = XLSX.utils.sheet_to_html(worksheet);
                                excelContainer.innerHTML = html;
                            }
                        } catch (error) {
                            console.error("Error renderizando documento:", error);
                            alert("No se pudo visualizar el documento. Por favor descárgalo.");
                            this.mediaModalData.type = 'other';
                        } finally {
                            this.mediaModalData.loading = false;
                        }
                    }
                },

                deleteSelected() {
                    if (!confirm('¿Estás seguro de que quieres eliminar los elementos seleccionados?')) {
                        return;
                    }

                    document.getElementById('loading-overlay').classList.remove('hidden');

                    const folderIdsToDelete = this.selectedItems.filter(item => item.type === 'folder').map(item => item.id);
                    const fileLinkIdsToDelete = this.selectedItems.filter(item => item.type === 'file_link').map(item => item.id);

                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('folder_ids', JSON.stringify(folderIdsToDelete));
                    formData.append('file_link_ids', JSON.stringify(fileLinkIdsToDelete));

                    fetch('{{ route('folders.bulk_delete') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('loading-overlay').classList.add('hidden');
                        if (data.success) {
                            document.getElementById('flash-success-message').innerText = data.message;
                            document.getElementById('flash-success').style.display = 'flex';
                            setTimeout(() => {
                                document.getElementById('flash-success').style.display = 'none';
                                window.location.reload();
                            }, 2000);
                        } else {
                            document.getElementById('flash-error-message').innerText = data.message;
                            document.getElementById('flash-error').style.display = 'flex';
                            setTimeout(() => {
                                document.getElementById('flash-error').style.display = 'none';
                                window.location.reload();
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        document.getElementById('loading-overlay').classList.add('hidden');
                        document.getElementById('flash-error-message').innerText = 'Ocurrió un error de red al intentar eliminar los elementos.';
                        document.getElementById('flash-error').style.display = 'flex';
                        setTimeout(() => {
                            document.getElementById('flash-error').style.display = 'none';
                            window.location.reload();
                        }, 2000);
                    });
                },

                openMoveModal() {
                    this.showMoveModal = true;
                    this.moveTargetFolderId = null;
                    this.moveTargetFolderName = 'Raíz';
                    this.currentMoveBrowseFolder = null;
                    this.fetchAvailableMoveFolders(null);
                },

                fetchAvailableMoveFolders(parentId) {
                    let url = '{{ route('folders.api.children') }}';
                    if (parentId !== null) {
                        url += '?parent_id=' + parentId;
                    }

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.availableMoveFolders = data;
                    })
                    .catch(error => {
                        sessionStorage.setItem('flash_error', 'Error al cargar carpetas de destino.');
                        window.location.reload();
                    });
                },

                browseMoveFolder(folder) {
                    this.currentMoveBrowseFolder = {
                        id: folder.id,
                        name: folder.name,
                        pathSegments: [...(this.currentMoveBrowseFolder ? this.currentMoveBrowseFolder.pathSegments : []), { folder: folder }]
                    };
                    this.selectMoveTarget(folder.id, folder.name);
                    this.fetchAvailableMoveFolders(folder.id);
                },

                browseMovePathSegment(segmentIndex) {
                    if (segmentIndex === -1) {
                        this.currentMoveBrowseFolder = null;
                        this.moveTargetFolderId = null;
                        this.moveTargetFolderName = 'Raíz';
                        this.fetchAvailableMoveFolders(null);
                    } else {
                        const targetSegment = this.currentMoveBrowseFolder.pathSegments[segmentIndex];
                        this.currentMoveBrowseFolder.pathSegments = this.currentMoveBrowseFolder.pathSegments.slice(0, segmentIndex + 1);
                        this.currentMoveBrowseFolder = {
                            id: targetSegment.folder.id,
                            name: targetSegment.folder.name,
                            pathSegments: this.currentMoveBrowseFolder.pathSegments
                        };
                        this.selectMoveTarget(targetSegment.folder.id, targetSegment.folder.name);
                        this.fetchAvailableMoveFolders(targetSegment.folder.id);
                    }
                },

                selectMoveTarget(folderId, folderName) {
                    this.moveTargetFolderId = folderId;
                    this.moveTargetFolderName = folderName;
                },

                confirmMove() {
                    if (this.selectedItems.length === 0) {
                        alert('No hay elementos seleccionados para mover.');
                        return;
                    }
                    if (this.moveTargetFolderId === null && this.moveTargetFolderName !== 'Raíz') {
                        alert('Por favor, selecciona una carpeta de destino o la Raíz.');
                        return;
                    }

                    if (!confirm(`¿Estás seguro de que quieres mover los elementos seleccionados a "${this.moveTargetFolderName}"?`)) {
                        return;
                    }

                    const folderIdsToMove = this.selectedItems.filter(item => item.type === 'folder').map(item => item.id);
                    const fileLinkIdsToMove = this.selectedItems.filter(item => item.type === 'file_link').map(item => item.id);

                    fetch('{{ route('items.bulk_move') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            folder_ids: folderIdsToMove,
                            file_link_ids: fileLinkIdsToMove,
                            target_folder_id: this.moveTargetFolderId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            sessionStorage.setItem('flash_success', data.message);
                        } else {
                            sessionStorage.setItem('flash_error', data.message);
                        }
                        window.location.reload();
                    })
                    .catch(error => {
                        sessionStorage.setItem('flash_error', 'Ocurrió un error de red al intentar mover los elementos.');
                        window.location.reload();
                    })
                    .finally(() => {
                        this.showMoveModal = false;
                    });
                },

                handleFolderSelected(event) {
                    const files = event.target.files;
                    if (!files || files.length === 0) return;

                    const itemsList = [];
                    for (const file of files) {
                        itemsList.push({ file: file, path: file.webkitRelativePath });
                    }

                    if (this.currentFolderId === null) {
                        this.showAreaModal = true;
                        this.areaModalCallback = () => this.uploadFilesWithProgress(itemsList, null, this.selectedUploadAreaId);
                    } else {
                        this.uploadFilesWithProgress(itemsList, this.currentFolderId, null);
                    }
                    
                    event.target.value = null;
                },

                triggerFolderUpload() {
                    this.$refs.folderInput.click();
                },
                
                confirmAreaSelection() {
                    if (this.selectedUploadAreaId === '') {
                        alert('Por favor, selecciona un área.');
                        return;
                    }
                    if (typeof this.areaModalCallback === 'function') {
                        this.areaModalCallback();
                    }
                    this.showAreaModal = false;
                    this.areaModalCallback = null;
                },                

                uploadFilesWithProgress(itemsList, targetFolderId, targetAreaId = null) {
                    if (!itemsList || itemsList.length === 0) return;

                    if (targetFolderId === null && targetAreaId === null) {
                        sessionStorage.setItem('flash_error', 'Error: No se especificó un área de destino.');
                        window.location.reload();
                        return;
                    }
                    
                    this.isUploading = true;
                    this.uploadTotalFiles = itemsList.length;
                    this.uploadCurrentFile = 0;
                    this.uploadProgress = 0;
                    this.uploadCurrentFileName = '';

                    const uploadAllItems = async () => {
                        for (let i = 0; i < itemsList.length; i++) {
                            const item = itemsList[i];
                            const formData = new FormData();
                            
                            if (targetFolderId) {
                                formData.append('target_folder_id', targetFolderId);
                            }
                            if (targetAreaId) {
                                formData.append('area_id', targetAreaId);
                            }
                            
                            formData.append('files[]', item.file);
                            formData.append('paths[]', item.path);
                            
                            this.uploadCurrentFile = i + 1;
                            this.uploadCurrentFileName = item.file.name;
                            this.uploadProgress = 0;

                            try {
                                await axios.post('{{ route("folders.uploadDirectory") }}', formData, {
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                    onUploadProgress: (progressEvent) => {
                                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                                        this.uploadProgress = percentCompleted;
                                    }
                                });
                            } catch (error) {
                                sessionStorage.setItem('flash_error', `Error al subir "${item.file.name}". La carga se ha detenido.`);
                                this.isUploading = false;
                                window.location.reload();
                                return; 
                            }
                        }
                        
                        sessionStorage.setItem('flash_success', `Se subieron ${itemsList.length} archivos exitosamente.`);
                        this.isUploading = false;
                        window.location.reload();
                    };

                    uploadAllItems();
                },
                
                async getFilesFromDroppedItems(dataTransferItems) {
                    const itemsList = [];
                    const promises = [];

                    for (const item of dataTransferItems) {
                        const entry = item.webkitGetAsEntry();
                        if (entry) {
                            promises.push(this.traverseDirectory(entry, "", itemsList));
                        }
                    }
                    
                    await Promise.all(promises);
                    return itemsList;
                },

                async traverseDirectory(entry, currentPath, itemsList) {
                    if (entry.isFile) {
                        return new Promise(resolve => {
                            entry.file(file => {
                                itemsList.push({ file: file, path: currentPath + file.name });
                                resolve();
                            });
                        });
                    } else if (entry.isDirectory) {
                        const reader = entry.createReader();
                        const promises = [];
                        const newPath = currentPath + entry.name + "/";

                        return new Promise(resolve => {
                            const readEntries = () => {
                                reader.readEntries(entries => {
                                    if (entries.length > 0) {
                                        for (const subEntry of entries) {
                                            promises.push(this.traverseDirectory(subEntry, newPath, itemsList));
                                        }
                                        readEntries();
                                    } else {
                                        Promise.all(promises).then(() => resolve());
                                    }
                                });
                            };
                            readEntries();
                        });
                    }
                },

                init() {
                    const savedView = localStorage.getItem('file_manager_view');
                    if (savedView === 'list') {
                        this.isTileView = false;
                    } else {
                        this.isTileView = true;
                    }

                    const savedTileSize = localStorage.getItem('file_manager_tile_size');
                    if (savedTileSize) {
                        this.tileSize = savedTileSize;
                    }

                    const flashSuccess = sessionStorage.getItem('flash_success');
                    const flashError = sessionStorage.getItem('flash_error');

                    if (flashSuccess) {
                        document.getElementById('flash-success-message').innerText = flashSuccess;
                        document.getElementById('flash-success').style.display = 'flex';
                        sessionStorage.removeItem('flash_success');
                        setTimeout(() => {
                            document.getElementById('flash-success').style.display = 'none';
                        }, 5000);
                    }
                    if (flashError) {
                        document.getElementById('flash-error-message').innerText = flashError;
                        document.getElementById('flash-error').style.display = 'flex';
                        sessionStorage.removeItem('flash_error');
                        setTimeout(() => {
                            document.getElementById('flash-error').style.display = 'none';
                        }, 5000);
                    }

                    this.$watch('isTileView', value => {
                        localStorage.setItem('file_manager_view', value ? 'tile' : 'list');
                    });
                    this.$watch('tileSize', value => {
                        localStorage.setItem('file_manager_tile_size', value);
                    });
                }
            }));
        });
    </script>
    <style>
        #fs-container:fullscreen { background: #e5e7eb; padding: 20px; display: flex; align-items: center; justify-content: center; overflow: auto; }
        #fs-container:fullscreen img, #fs-container:fullscreen iframe { height: 100%; width: 100%; }
        
        .loader-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #ff9c00; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .excel-viewer { width: 100%; overflow: visible; }
        .excel-viewer table { 
            min-width: 100%;
            width: auto;
            border-collapse: collapse; 
            font-size: 12px; 
            background: white; 
        }
        
        .excel-viewer td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; color: #2c3856; white-space: nowrap; }
        
        .excel-viewer tr:first-child td { 
            background-color: #f1f5f9; 
            font-weight: bold; 
            color: #1e293b;
            position: sticky; 
            top: 0; 
            z-index: 10; 
            border-bottom: 2px solid #cbd5e1;
        }

        .excel-viewer tr:not(:first-child):nth-child(even) { background-color: #f8fafc; }
        .excel-viewer tr:hover td { background-color: #e2e8f0; }
        
        #word-container { width: 100%; overflow-y: auto; background-color: #e5e7eb; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; }
        #word-container .docx-wrapper { background: transparent !important; padding: 0 !important; width: 100%; display: flex; flex-direction: column; align-items: center; }
        #word-container section.docx { background: white !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 20px !important; padding: 40px !important; width: 21cm !important; min-height: 29.7cm !important; color: black !important; }
        #word-container section.docx p { margin-bottom: 1em; line-height: 1.5; }
        #word-container section.docx table { border-collapse: collapse; }
        #word-container section.docx table td, #word-container section.docx table th { border: 1px solid #000; padding: 4px; }
    </style>

    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script src="https://unpkg.com/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://unpkg.com/docx-preview@0.1.15/dist/docx-preview.min.js"></script>
</x-app-layout>