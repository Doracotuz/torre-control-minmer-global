<x-app-layout>
    <x-slot name="head">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Raleway:wght@700;800&display=swap" rel="stylesheet">
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        
        <style>
            body { font-family: 'Montserrat', sans-serif; background-color: #F3F4F6; } 
            h1, h2, h3, h4, h5, h6, .font-raleway { font-family: 'Raleway', sans-serif; }
            .member-checkbox:checked { background-color: #ff9c00; border-color: #ff9c00; }
            .member-checkbox:focus { --tw-ring-color: rgba(255, 156, 0, 0.5); box-shadow: 0 0 0 2px var(--tw-ring-color); }
            
            .member-card, .member-row { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
            .member-card { border: 1px solid #e5e7eb; border-radius: 0.75rem; background-color: white; overflow: hidden; display: flex; flex-direction: column; }
            .member-card:hover { transform: translateY(-6px); box-shadow: 0 15px 25px -5px rgba(44, 56, 86, 0.1), 0 8px 10px -6px rgba(44, 56, 86, 0.1); }
            .member-row:hover { background-color: #FFF7ED; transform: scale(1.01); box-shadow: 0 6px 12px rgba(0,0,0,0.06); z-index: 5; position: relative; }
            
            .view-toggle-active { background-color: #ff9c00 !important; color: white !important; box-shadow: inset 0 2px 5px rgba(0,0,0,0.15); border-color: #ff9c00 !important; }
            
            [x-cloak] { display: none !important; }
            .fade-enter-active, .fade-leave-active { transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
            .fade-enter-from, .fade-leave-to { opacity: 0; }
            .list-enter-active { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
            .list-leave-active { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); } 
            .list-enter-from, .list-leave-to { opacity: 0; transform: translateY(15px); }
            .slide-up-fade-enter-active, .slide-up-fade-leave-active { transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
            .slide-up-fade-enter-from, .slide-up-fade-leave-to { opacity: 0; transform: translateY(30px); }
            
            .quick-view-modal-bg { background-color: rgba(31, 41, 55, 0.7); backdrop-filter: blur(8px); }
            .quick-view-modal-content { max-height: 90vh; transform: scale(0.9); opacity: 0; transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
            .quick-view-modal-content.show { transform: scale(1); opacity: 1; }

            .spinner { border: 3px solid rgba(44, 56, 86, 0.1); width: 24px; height: 24px; border-radius: 50%; border-left-color: #ff9c00; animation: spin 1s linear infinite; }
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            
            button:focus-visible, a:focus-visible, input:focus-visible, select:focus-visible { outline: 2px solid #fdba74; outline-offset: 2px; box-shadow: none; } 
            select:focus-visible { border-color: #ff9c00 !important; }
            
            .pagination-link { display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1rem; margin: 0 0.25rem; border: 1px solid #d1d5db; background-color: white; color: #374151; font-size: 0.875rem; font-weight: 500; border-radius: 0.375rem; transition: all 0.2s ease-in-out; }
            .pagination-link:hover:not(.disabled):not(.active) { background-color: #E8ECF7; border-color: #9ca3af; } 
            .pagination-link.active { background-color: #ff9c00; border-color: #ff9c00; color: white; cursor: default; }
            .pagination-link.disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
        </style>
    </x-slot>

    <x-slot name="header" >
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight font-raleway font-extrabold">
            {{ __('Gestión de Organigrama') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#E8ECF7]" style="font-family: 'Montserrat', sans-serif;">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div x-data="{ 
                    selectedMembers: [], 
                    selectAll: false,
                    membersOnPageIds: {{ $members->pluck('id')->toJson() }},
                    viewMode: localStorage.getItem('organigramViewMode') || 'grid',
                    showFilters: {{ $errors->any() || $searchQuery || $selectedPosition || $selectedManager || $selectedArea ? 'true' : 'false' }},
                    quickViewOpen: false, 
                    quickViewMember: null,
                    
                    toggleSelectAll() { this.selectAll = !this.selectAll; if (this.selectAll) { this.selectedMembers = [...this.membersOnPageIds]; } else { this.selectedMembers = []; } },
                    updateSelectAllState() { this.selectAll = this.membersOnPageIds.length > 0 && this.selectedMembers.length === this.membersOnPageIds.length; },
                    setViewMode(mode) { this.viewMode = mode; localStorage.setItem('organigramViewMode', mode); },
                    openQuickView(member) { this.quickViewMember = member; if (this.quickViewMember) { this.quickViewOpen = true; } },
                    closeQuickView() { this.quickViewOpen = false; setTimeout(() => { this.quickViewMember = null; }, 300); },
                }" 
                 x-init="$watch('selectedMembers', () => updateSelectAllState());"
                 @keydown.escape.window="closeQuickView()"
                 class="relative" x-cloak>

                @if (session('success')) <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="fixed top-5 right-5 z-[60] bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert"> <div class="flex items-center"> <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <strong class="font-bold mr-1 font-raleway">¡Éxito!</strong> <span class="block sm:inline">{!! session('success') !!}</span> </div> <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none ml-4"> <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> </button> </div> @endif
                @if (session('error')) <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="fixed top-5 right-5 z-[60] bg-white border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert"> <div class="flex items-center"> <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <strong class="font-bold mr-1 font-raleway">¡Error!</strong> <span class="block sm:inline">{!! session('error') !!}</span> </div> <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none ml-4"> <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> </button> </div> @endif
                @if (session('warning')) <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 8000)" class="fixed top-5 right-5 z-[60] bg-white border-l-4 border-yellow-500 text-yellow-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert"> <div class="flex items-center"> <svg class="w-6 h-6 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> <strong class="font-bold mr-1 font-raleway">Aviso:</strong> <span class="block sm:inline">{!! session('warning') !!}</span> </div> <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none ml-4"> <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> </button> </div> @endif

                <div class="bg-white shadow-xl sm:rounded-lg border border-gray-200">
                    
                     <form action="{{ route('admin.organigram.index') }}" method="GET" id="filter-search-form">
                        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/75 rounded-t-lg flex flex-wrap justify-between items-center gap-4"> 
                            
                            <div class="hidden"> 
                                <input type="text" id="search_hidden" name="search" value="{{ $searchQuery ?? '' }}">
                            </div> 

                            <div class="flex flex-wrap justify-center sm:justify-end gap-3 w-full md:w-auto ml-auto"> 
                                <div class="inline-flex rounded-full shadow-sm bg-white border border-gray-300"> 
                                    <button type="button" @click="setViewMode('grid')" :class="{'view-toggle-active': viewMode === 'grid', 'text-gray-500 hover:bg-gray-50': viewMode !== 'grid'}" class="relative inline-flex items-center px-3 py-2 rounded-l-full text-sm font-medium focus:z-10 focus:outline-none focus:ring-1 focus:ring-[#ff9c00]"> 
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                        </svg> 
                                    </button> 
                                    <button type="button" @click="setViewMode('list')" :class="{'view-toggle-active': viewMode === 'list', 'text-gray-500 hover:bg-gray-50': viewMode !== 'list'}" class="-ml-px relative inline-flex items-center px-3 py-2 rounded-r-full text-sm font-medium focus:z-10 focus:outline-none focus:ring-1 focus:ring-[#ff9c00]"> 
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                        </svg> 
                                    </button> 
                                </div> 
                                <button type="button" @click="showFilters = !showFilters" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-full font-bold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-1 transition ease-in-out duration-300 transform hover:scale-105 shadow-sm whitespace-nowrap"> 
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V19l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                    </svg> Filtros 
                                    <svg :class="{'rotate-180': showFilters}" class="w-4 h-4 ml-1 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg> 
                                </button>
                                <a href="{{ route('admin.organigram.export-csv') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-full font-bold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-1 transition ease-in-out duration-300 transform hover:scale-105 shadow-sm whitespace-nowrap">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Exportar CSV
                                </a>                            
                                <a href="{{ route('admin.organigram.interactive') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#ff9c00] border border-transparent rounded-full font-bold text-xs text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-1 transition ease-in-out duration-300 transform hover:scale-105 shadow-md whitespace-nowrap"> 
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg> Ver Organigrama 
                                </a> 
                                <a href="{{ route('admin.organigram.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#2c3856] border border-transparent rounded-full font-bold text-xs text-white uppercase tracking-widest hover:bg-[#4a5d8c] focus:bg-[#4a5d8c] active:bg-[#1a233a] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-1 transition ease-in-out duration-300 transform hover:scale-105 shadow-md whitespace-nowrap"> 
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg> Añadir Miembro 
                                </a> 
                                <div x-data="{ open: false }" class="relative inline-block text-left"> 
                                    <div> 
                                        <button type="button" @click="open = !open" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-gray-600 border border-transparent rounded-full font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-1 transition ease-in-out duration-300 transform hover:scale-105 shadow-md" aria-expanded="true" aria-haspopup="true"> 
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                            </svg> 
                                        </button> 
                                    </div> 
                                    <div x-show="open" x-transition @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu" aria-orientation="vertical" style="display: none;"> 
                                        <div class="py-1" role="none"> 
                                            <a href="{{ route('admin.organigram.positions.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem"> 
                                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                                </svg> Posiciones 
                                            </a> 
                                            <a href="{{ route('admin.organigram.skills.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem"> 
                                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                                </svg> Habilidades 
                                            </a> 
                                            <a href="{{ route('admin.organigram.activities.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem"> 
                                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                                </svg> Actividades 
                                            </a> 
                                        </div> 
                                    </div> 
                                </div> 
                            </div>
                        </div>

                        <div x-show="showFilters" x-transition class="p-6 border-b border-gray-200 bg-gray-50/75" style="display: none;"> 
                            <h4 class="text-md font-semibold text-[#2c3856] mb-4 font-raleway">Filtrar y Buscar</h4>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end"> 
                                
                                <div> 
                                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                                    <div class="relative">
                                        <input type="text" id="search" name="search" placeholder="Nombre o email..." value="{{ $searchQuery ?? '' }}" class="block w-full pl-10 pr-4 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#ff9c00] focus:border-[#ff9c00] sm:text-sm rounded-md shadow-sm placeholder-gray-400" autocomplete="off">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"> <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg> </div> 
                                    </div>
                                </div> 
                                
                                <div> <label for="position_id" class="block text-sm font-medium text-gray-700 mb-1">Posición</label> <select id="position_id" name="position_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#ff9c00] focus:border-[#ff9c00] sm:text-sm rounded-md shadow-sm"> <option value="">Todas</option> @foreach($positions as $position) <option value="{{ $position->id }}" {{ (string)$position->id === (string)$selectedPosition ? 'selected' : '' }}> {{ $position->name }} </option> @endforeach </select> </div> 
                                <div> <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-1">Jefe Directo</label> <select id="manager_id" name="manager_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#ff9c00] focus:border-[#ff9c00] sm:text-sm rounded-md shadow-sm"> <option value="">Todos</option> <option value="null" {{ 'null' === (string)$selectedManager ? 'selected' : '' }}>Sin Jefe</option> @foreach($managers as $manager) <option value="{{ $manager->id }}" {{ (string)$manager->id === (string)$selectedManager ? 'selected' : '' }}> {{ $manager->name }} </option> @endforeach </select> </div> 
                                <div> <label for="area_id" class="block text-sm font-medium text-gray-700 mb-1">Área</label> <select id="area_id" name="area_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#ff9c00] focus:border-[#ff9c00] sm:text-sm rounded-md shadow-sm"> <option value="">Todas</option> @foreach($areas as $area) <option value="{{ $area->id }}" {{ (string)$area->id === (string)$selectedArea ? 'selected' : '' }}> {{ $area->name }} </option> @endforeach </select> </div> 
                            </div> 
                            <div class="flex justify-end mt-4 space-x-3"> 
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#2c3856] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#4a5d8c] focus:bg-[#4a5d8c] active:bg-[#1a233a] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-1 transition ease-in-out duration-300 transform hover:scale-105 shadow-md"> <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V19l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg> Aplicar </button> 
                                <a href="{{ route('admin.organigram.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 transition ease-in-out duration-300 transform hover:scale-105 shadow-md"> Limpiar </a> 
                            </div>
                        </div>
                    </form>
                    
                    <div class="px-6 py-3 border-t border-b border-gray-200 bg-gray-50/75 flex items-center justify-between">
                         <span class="text-sm text-gray-600">
                             <strong class="font-medium">{{ $members->total() }}</strong> {{ $members->total() === 1 ? 'miembro' : 'miembros' }} encontrados
                         </span>
                         @if ($members->count() > 0)
                             <label class="flex items-center cursor-pointer">
                                <input type="checkbox" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] member-checkbox h-5 w-5 mr-2" 
                                       :checked="selectAll" @click="toggleSelectAll">
                                <span class="text-sm font-medium text-gray-700">Seleccionar página ({{ $members->count() }})</span>
                            </label>
                         @endif
                    </div>

                    <div class="relative"> 

                        <div x-show="viewMode === 'grid'" 
                             x-transition:enter="fade-enter-active" x-transition:enter-start="fade-enter-from"
                             x-transition:leave="fade-leave-active" x-transition:leave-end="fade-leave-to">
                            
                            @if($members->isEmpty())
                                <div class="text-center py-16 px-6"> <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <h3 class="text-xl font-semibold text-gray-600 font-raleway mb-2">No se encontraron miembros</h3> 
                                @if (!empty($searchQuery))
                                    <p class="text-gray-500">Intenta ajustar tu búsqueda para "<strong>{{ $searchQuery }}</strong>"</p> 
                                @else
                                    <p class="text-gray-500">Prueba usando filtros o <a href="{{ route('admin.organigram.create') }}" class="text-[#ff9c00] hover:underline font-medium">añade un miembro</a>.</p> 
                                @endif
                                </div>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6 p-6">
                                    @foreach($members as $member)
                                        <div class="member-card relative" :class="{'ring-2 ring-[#ff9c00] ring-offset-2': selectedMembers.includes({{ $member->id }})}">
                                            <div class="absolute top-4 left-4 z-10"> <input type="checkbox" class="rounded border-gray-400 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] member-checkbox h-5 w-5" value="{{ $member->id }}" x-model="selectedMembers" @click.stop> </div> 
                                            <div class="flex flex-col items-center p-6 pt-8 flex-grow"> 
                                                <div class="mb-4 relative"> 
                                                    @if($member->profile_photo_path_url)
                                                        <img class="h-24 w-24 rounded-full object-cover ring-4 ring-offset-1 ring-[#ff9c00]" src="{{ $member->profile_photo_path_url }}" alt="{{ $member->name }}" loading="lazy"> 
                                                    @else
                                                        <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 ring-4 ring-offset-1 ring-gray-300"> <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg> </div> 
                                                    @endif
                                                </div> 
                                                <h4 class="text-lg font-bold text-[#2c3856] text-center font-raleway truncate w-full" title="{{ $member->name }}">{{ $member->name }}</h4> 
                                                <p class="text-sm text-[#ff9c00] font-semibold text-center mb-3 truncate w-full" title="{{ $member->position->name ?? 'N/A' }}">{{ $member->position->name ?? 'N/A' }}</p> 
                                                <div class="text-xs text-gray-500 space-y-1 text-center w-full mt-auto pt-3"> 
                                                    <p class="flex items-center justify-center truncate" title="{{ $member->area->name ?? 'N/A' }}"> <svg class="w-3 h-3 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg> <span>{{ $member->area->name ?? 'N/A' }}</span> </p> 
                                                    <p class="flex items-center justify-center truncate" title="{{ $member->manager->name ?? 'Sin Jefe' }}"> <svg class="w-3 h-3 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> <span>{{ $member->manager->name ?? 'Sin Jefe' }}</span> </p> 
                                                </div> 
                                            </div> 
                                            <div class="bg-gradient-to-t from-gray-100 to-gray-50 px-4 py-3 border-t border-gray-200 flex justify-end items-center space-x-2"> 
                                                <button @click="openQuickView({{ $member->toJson() }})" title="Vista Rápida" class="text-gray-400 hover:text-[#ff9c00] p-1.5 rounded-full hover:bg-orange-100 transition duration-150 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-[#ff9c00]"> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> </button> 
                                                <a href="{{ route('admin.organigram.edit', $member) }}" class="inline-flex items-center px-3 py-1 bg-white border border-gray-300 rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-400 focus:ring-offset-1 transition ease-in-out duration-150 shadow-sm"> Editar </a> 
                                                <form action="{{ route('admin.organigram.destroy', $member) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro?');"> @csrf @method('DELETE') <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-50 border border-red-200 rounded-full font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-100 focus:outline-none focus:ring-1 focus:ring-red-400 focus:ring-offset-1 transition ease-in-out duration-150 shadow-sm"> Eliminar </button> </form> 
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div x-show="viewMode === 'list'" 
                             x-transition:enter="fade-enter-active" x-transition:enter-start="fade-enter-from"
                             x-transition:leave="fade-leave-active" x-transition:leave-end="fade-leave-to" 
                             class="overflow-x-auto">
                             
                            @if($members->isEmpty())
                                <div class="text-center py-16 px-6"> <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <h3 class="text-xl font-semibold text-gray-600 font-raleway mb-2">No se encontraron miembros</h3> 
                                @if (!empty($searchQuery))
                                    <p class="text-gray-500">Intenta ajustar tu búsqueda para "<strong>{{ $searchQuery }}</strong>"</p> 
                                @else
                                    <p class="text-gray-500">Prueba usando filtros o <a href="{{ route('admin.organigram.create') }}" class="text-[#ff9c00] hover:underline font-medium">añade un miembro</a>.</p> 
                                @endif
                                </div>
                            @else
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50/75 sticky top-0 z-10">
                                        <tr> <th scope="col" class="pl-6 py-3 w-12 text-left"> <input type="checkbox" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] member-checkbox h-4 w-4" :checked="selectAll" @click="toggleSelectAll"> </th> <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th> <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Posición</th> <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Área</th> <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jefe Directo</th> <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th> </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($members as $member)
                                            <tr class="member-row group" :class="{'bg-orange-50': selectedMembers.includes({{ $member->id }})}">
                                                 <td class="pl-6 py-4 whitespace-nowrap"> <input type="checkbox" class="rounded border-gray-300 text-[#ff9c00] shadow-sm focus:ring-[#ff9c00] member-checkbox h-4 w-4" value="{{ $member->id }}" x-model="selectedMembers"> </td> 
                                                 <td class="px-6 py-4 whitespace-nowrap"> 
                                                     <div class="flex items-center"> 
                                                        @if($member->profile_photo_path_url)
                                                            <img class="h-10 w-10 rounded-full object-cover mr-4 flex-shrink-0" src="{{ $member->profile_photo_path_url }}" alt="{{ $member->name }}" loading="lazy"> 
                                                        @else
                                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 mr-4 flex-shrink-0"> <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg> </div> 
                                                        @endif
                                                         <div> 
                                                            <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div> 
                                                            @if($member->email)
                                                                <a href="mailto:{{ $member->email }}" class="text-xs text-blue-600 hover:underline" @click.stop>{{ $member->email }}</a> 
                                                            @else
                                                                <span class="text-xs text-gray-400">Sin Email</span>
                                                            @endif
                                                         </div> 
                                                     </div> 
                                                 </td> 
                                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $member->position->name ?? 'N/A' }}</td> 
                                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $member->area->name ?? 'N/A' }}</td> 
                                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $member->manager->name ?? 'N/A' }}</td> 
                                                 <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"> 
                                                    <div class="flex items-center justify-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200"> 
                                                        <button @click="openQuickView({{ $member->toJson() }})" title="Vista Rápida" class="text-gray-400 hover:text-[#ff9c00] p-1.5 rounded-full hover:bg-orange-100 transition duration-150"> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> </button> 
                                                        <a href="{{ route('admin.organigram.edit', $member) }}" title="Editar" class="text-gray-400 hover:text-[#2c3856] p-1.5 rounded-full hover:bg-gray-100 transition duration-150"> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> </a> 
                                                        <form action="{{ route('admin.organigram.destroy', $member) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro?');"> @csrf @method('DELETE') <button type="submit" title="Eliminar" class="text-gray-400 hover:text-red-600 p-1.5 rounded-full hover:bg-red-100 transition duration-150"> <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> </button> </form> 
                                                    </div> 
                                                 </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>

                    @if ($members->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/75 rounded-b-lg flex items-center justify-between flex-wrap gap-4">
                            <span class="text-sm text-gray-600">
                                Mostrando <strong class="font-medium">{{ $members->firstItem() }}</strong> a <strong class="font-medium">{{ $members->lastItem() }}</strong> de <strong class="font-medium">{{ $members->total() }}</strong> resultados
                            </span>
                            
                            <nav role="navigation" aria-label="Pagination">
                                {{-- Esto renderizará los enlaces de paginación con el estilo de Tailwind --}}
                                {{ $members->links() }} 
                            </nav>
                        </div>
                    @endif

                </div>

                 <div x-show="selectedMembers.length > 0" x-transition:enter="slide-up-fade-enter-active" x-transition:leave="slide-up-fade-leave-active" class="fixed bottom-0 left-0 right-0 bg-gradient-to-t from-gray-100 via-white to-white border-t border-gray-300 shadow-[0_-6px_15px_rgba(0,0,0,0.1)] p-4 z-50 flex items-center justify-between" style="display: none;"> <span class="text-sm font-medium text-gray-700"> <strong x-text="selectedMembers.length"></strong> seleccionado(s) </span> <div> <form id="bulk-delete-form" action="{{ route('admin.organigram.bulk-delete') }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro de eliminar ' + document.getElementById('selected-count').innerText + ' miembros?');"> @csrf @method('DELETE') <template x-for="id in selectedMembers" :key="id"> <input type="hidden" name="selected_ids[]" :value="id"> </template> <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition ease-in-out duration-150 shadow-md transform hover:scale-105"> <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Eliminar (<span id="selected-count" x-text="selectedMembers.length"></span>) </button> </form> </div> </div>

                <div x-show="quickViewOpen" x-transition:enter="fade-enter-active ease-out duration-300" x-transition:leave="fade-leave-active ease-in duration-200" class="fixed inset-0 z-[70] flex items-center justify-center p-4 quick-view-modal-bg" @click="closeQuickView" style="display: none;"> 
                    <div class="quick-view-modal-content bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden" :class="{'show': quickViewOpen}" @click.stop> 
                        <template x-if="quickViewMember"> 
                            <div> 
                                <div class="p-5 border-b border-gray-200 flex justify-between items-start bg-gradient-to-r from-[#2c3856] to-[#4a5d8c] text-white rounded-t-xl">
                                    <div> 
                                        <h3 class="text-xl font-bold font-raleway leading-tight" x-text="quickViewMember.name"></h3> 
                                        <p class="text-sm text-gray-300" x-text="quickViewMember.position?.name || 'Sin Posición'"></p> 
                                    </div> 
                                    <button @click="closeQuickView" class="text-gray-400 hover:text-white transition duration-150 p-1 -m-1 rounded-full focus:outline-none focus:ring-2 focus:ring-white"> <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> </button> 
                                </div> 
                                <div class="p-6 space-y-5 max-h-[65vh] overflow-y-auto"> 
                                    <div class="flex items-center space-x-5"> 
                                        <template x-if="quickViewMember.profile_photo_path_url"> <img class="h-24 w-24 rounded-full object-cover ring-4 ring-offset-2 ring-[#ff9c00]" :src="quickViewMember.profile_photo_path_url" :alt="quickViewMember.name"> </template> 
                                        <template x-if="!quickViewMember.profile_photo_path_url"> <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 ring-4 ring-offset-2 ring-gray-300 flex-shrink-0"> <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg> </div> </template> 
                                        <div class="space-y-1 flex-grow"> 
                                            <h4 class="text-xl font-semibold text-[#2c3856] font-raleway" x-text="quickViewMember.name"></h4> 
                                            <p class="text-md text-gray-600" x-text="quickViewMember.position?.name || 'N/A'"></p> 
                                            <p class="text-sm text-gray-500" x-text="quickViewMember.area?.name || 'N/A'"></p> 
                                        </div> 
                                    </div> 
                                    <div class="border-t border-gray-200 pt-5 space-y-3 text-sm"> 
                                        <div class="flex items-center text-gray-700"> <svg class="w-5 h-5 mr-3 text-[#ff9c00] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> <a :href="'mailto:' + quickViewMember.email" class="text-blue-600 hover:underline" x-text="quickViewMember.email || 'No disponible'" :class="{'pointer-events-none text-gray-500 hover:no-underline': !quickViewMember.email}" @click.stop></a> </div> 
                                        <div class="flex items-center text-gray-700"> <svg class="w-5 h-5 mr-3 text-[#ff9c00] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> <span x-text="quickViewMember.cell_phone || 'No disponible'"></span> </div> 
                                        <div class="flex items-center text-gray-700"> <svg class="w-5 h-5 mr-3 text-[#ff9c00] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> <span class="text-gray-500 mr-1">Jefe:</span> <span class="font-medium" x-text="quickViewMember.manager?.name || 'N/A'"></span> </div> 
                                    </div> 
                                </div> 
                                <div class="bg-gray-100 px-6 py-4 border-t border-gray-200 rounded-b-xl flex justify-end"> <a :href="`/admin/organigram/${quickViewMember.id}/edit`" class="inline-flex items-center px-4 py-2 bg-[#2c3856] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#4a5d8c] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-1 transition ease-in-out duration-150 shadow-sm transform hover:scale-105"> Ver Perfil Completo </a> </div> 
                            </div> 
                        </template> 
                        <template x-if="!quickViewMember && quickViewOpen"> <div class="p-10 text-center"><div class="spinner !w-8 !h-8 mx-auto"></div></div> </template> 
                    </div> 
                </div>

            </div>
        </div>
    </div>
</x-app-layout>