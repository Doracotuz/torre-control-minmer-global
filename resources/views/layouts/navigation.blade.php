@php
    $whatsappNumber = "5215536583392";
    $whatsappMessage = urlencode("Hola, me gustaría recibir asistencia para la plataforma \"Control Tower - Minmer Global\"");
    $whatsappLink = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";

    $manageableAreas = collect();
    $currentManagingAreaId = Auth::user()->area_id;
    $currentManagingAreaName = Auth::user()->area?->name;

    if (Auth::user()->is_area_admin) {
        $primaryArea = Auth::user()->area;
        if ($primaryArea) {
            $manageableAreas->push($primaryArea);
        }
        $manageableAreas = $manageableAreas->merge(Auth::user()->accessibleAreas)->unique('id')->sortBy('name');
        
        $currentManagingAreaId = session('current_admin_area_id', Auth::user()->area_id);
        $currentManagingAreaName = session('current_admin_area_name', Auth::user()->area?->name);
    }
@endphp

<nav x-data="{
        open: false,
        search: '',
        suggestions: [],
        showSuggestions: false,
        loading: false,
        timeout: null,

        isMobileSuperAdminMenuOpen: {{ request()->routeIs('admin.*') ? 'true' : 'false' }},
        isMobileAreaAdminMenuOpen: {{ request()->routeIs('area_admin.*') ? 'true' : 'false' }},
        
     }"
    x-init="$watch('search', value => {
        clearTimeout(timeout);
        suggestions = [];

        if (value.length > 2) {
            loading = true;
            showSuggestions = true;

            timeout = setTimeout(() => {
                fetch(`{{ route('search.suggestions') }}?query=${value}`)
                    .then(response => response.json())
                    .then(data => {
                        suggestions = data;
                    })
                    .catch(error => {
                        console.error('Error en la búsqueda:', error);
                        showSuggestions = false;
                    })
                    .finally(() => {
                        loading = false;
                    });
            }, 300);
        } else {
            loading = false;
            showSuggestions = false;
        }
    })"
     @click.away="showSuggestions = false"
     class="nav-bg border-gray-100 relative z-20 sticky top-0 z-50">

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center space-x-2 sm:space-x-4">
                <button @click="toggleSidebar()" class="p-2 rounded-md nav-toggle-btn focus:outline-none focus:ring-2 focus:ring-white/50 hidden lg:block">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                </button>                
                <div class="flex items-center text-white text-sm font-medium">
                    @if (Auth::user()->area)
                        @php
                            $areaName = Auth::user()->area->name;
                            $areaIconSvg = '';
                            $areaCustomIconPath = Auth::user()->area->icon_path;

                            if (!$areaCustomIconPath) {
                                switch ($areaName) {
                                    case 'Recursos Humanos': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-5v-2a3 3 0 013-3h2a3 3 0 013 3v2h-5zM9 10a3 3 0 11-6 0 3 3 0 016 0zM11 12a3 3 0 10-6 0 3 3 0 006 0z"></path></svg>'; break;
                                    case 'Customer Service': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>'; break;
                                    case 'Tráfico': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17l-4 4m0 0l-4-4m4 4V3m6 18v-3.586a1 1 0 01.293-.707l2.414-2.414A1 1 0 0115.586 14H18a2 2 0 002-2V7a2 2 0 00-2-2h-3.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0110 2.414V1H4a2 2 0 00-2 2v14a2 2 0 002 2h5z"></path></svg>'; break;
                                    case 'Almacén': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>'; break;
                                    case 'Valor Agregado': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>'; break;
                                    case 'POSM': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>'; break;
                                    case 'Brokerage': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l-3 3m3-3V3m3 12h8a2 2 0 002-2V7a2 2 0 00-2-2h-3l-4-3H9a2 2 0 00-2 2v4m-7 10h14a2 2 0 002-2V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4"></path></svg>'; break;
                                    case 'Innovación y Desarrollo': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 20v-3m0 0l.688-.688c.482-.482 1.132-.756 1.802-.756h.71a2 2 0 002-2V8.5a2 2 0 00-2-2h-.71c-.67 0-1.32-.274-1.802-.756L12 5m0 15v-3m0 0l-.688-.688c-.482-.482-1.132-.756-1.802-.756H6a2 2 0 01-2-2V8.5a2 2 0 012-2h.71c.67 0 1.32-.274 1.802-.756L12 5m0 15v-3m0 0l.688-.688c.482-.482 1.132-.756 1.802-.756h.71a2 2 0 002-2V8.5a2 2 0 00-2-2h-.71c-.67 0-1.32-.274-1.802-.756L12 5"></path></svg>'; break;
                                    case 'Administración': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.827 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.827 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.827-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.827-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'; break;
                                    case 'Marbete Electrónico': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>'; break;
                                    default: $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>'; break;
                                }
                            }
                        @endphp

                        @if ($areaCustomIconPath)
                            <div class="flex items-center justify-center rounded-full p-1.5 mr-3" style="background-color: #DFE5F5;">
                                <img
                                    src="{{ Storage::disk('s3')->url($areaCustomIconPath) }}"
                                    alt="{{ $areaName }} Icon"
                                    class="h-7 w-7 sm:h-8 sm:w-8 object-contain"
                                    style="filter: brightness(0) saturate(100%) invert(15%) sepia(15%) saturate(1000%) hue-rotate(180deg) brightness(90%) contrast(90%);"
                                >
                            </div>
                        @else
                            {!! $areaIconSvg !!}
                        @endif

                        <span class="nav-area-name text-base sm:text-lg font-semibold" style="font-family: 'Raleway', sans-serif;">{{ $areaName }}</span>
                    @endif
                </div>
            </div>

            <div class="flex-1 max-w-md mx-4 sm:mx-8 relative">
                <form action="{{ route('folders.index') }}" method="GET" class="relative">
                    <input type="text" name="search" placeholder="Buscar documentos..."
                        x-model="search"
                        x-on:focus="if (suggestions.length > 0) showSuggestions = true"
                           class="search-bar w-full pl-8 pr-2 py-1.5 sm:pl-10 sm:pr-4 sm:py-2 rounded-md border-2 border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all duration-300 ease-in-out shadow-sm
                                  hover:border-[#2c3856] focus:shadow-lg text-sm sm:text-base"
                           value="{{ request('search') }}">
                    <button type="submit" class="search-bar-icon absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2c3856] sm:left-3">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>

                <div x-show="showSuggestions"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    class="absolute z-30 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto">
                    <div x-show="loading" class="px-3 py-2 sm:px-4 sm:py-2 text-gray-500 text-sm">Buscando...</div>
                    <template x-for="suggestion in suggestions" :key="suggestion.id">
                        <a :href="suggestion.type === 'folder' ? `{{ url('/folders') }}/${suggestion.id}` : (suggestion.type === 'file' ? `{{ url('/files') }}/${suggestion.id}/download` : suggestion.url)"
                           @click="search = suggestion.name; showSuggestions = false;"
                           class="flex flex-col px-3 py-2 sm:px-4 sm:py-2 hover:bg-gray-100 cursor-pointer transition-colors duration-150 border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center text-sm sm:text-base">
                                <template x-if="suggestion.type === 'folder'"><svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#ff9c00] mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg></template>
                                <template x-if="suggestion.type === 'file'"><svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg></template>
                                <template x-if="suggestion.type === 'link'"><svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg></template>
                                <span x-text="suggestion.name" class="font-medium text-gray-800 truncate"></span>
                                <span class="text-xxs sm:text-xs text-gray-500 ml-auto" x-text="suggestion.area"></span>
                            </div>
                            <div class="mt-0.5 text-xxs sm:text-xs text-gray-500 truncate" x-text="suggestion.full_path"></div>
                        </a>
                    </template>
                        <div x-show="!loading && suggestions.length === 0 && search.length > 2" class="px-3 py-2 sm:px-4 sm:py-2 text-gray-500 text-sm">
                            No hay sugerencias.
                        </div>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                
                @if (Auth::user()->is_area_admin && $manageableAreas->count() > 1 && request()->routeIs('area_admin.*'))
                    <div class="ms-3 relative">
                        <x-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-[#ff9c00] hover:bg-orange-500 focus:outline-none transition ease-in-out duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m-1 4h1m6-4h1m-1 4h1m-1-8h1m-1 4h1"></path></svg>
                                    <div>Gestionando: <span class="font-bold">{{ $currentManagingAreaName }}</span></div>
                                    <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2 text-xs text-gray-400">Cambiar área de gestión</div>
                                @foreach ($manageableAreas as $area)
                                    <form method="POST" action="{{ route('area_admin.switch_area') }}" class="block">
                                        @csrf
                                        <input type="hidden" name="area_id" value="{{ $area->id }}">
                                        <button type="submit" 
                                                class="block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out
                                                       {{ $area->id == $currentManagingAreaId ? 'font-bold text-indigo-600' : '' }}">
                                            {{ $area->name }}
                                        </button>
                                    </form>
                                @endforeach
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif

                <div x-data="{ userMenuOpen: false }">
                    <div class="relative">
                        <button @click="userMenuOpen = !userMenuOpen" 
                                @click.away="userMenuOpen = false"
                                class="flex items-center gap-3 pl-1 pr-3 py-1 rounded-full transition-all duration-300 bg-white hover:bg-gray-100 group border border-transparent hover:border-gray-200 focus:outline-none">
                            
                            <div class="relative">
                                @if (Auth::user()->profile_photo_path)
                                    <img class="h-9 w-9 rounded-full object-cover shadow-sm group-hover:shadow-md transition-all duration-300" 
                                         src="{{ Storage::disk('s3')->url(Auth::user()->profile_photo_path) }}" 
                                         alt="{{ Auth::user()->name }}">
                                @else
                                    <div class="h-9 w-9 rounded-full bg-[#2c3856] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="absolute -bottom-1 -right-1 block h-3 w-3 rounded-full ring-2 ring-white bg-green-400"></span>
                            </div>

                            <div class="text-left hidden lg:block">
                                <p class="text-xs font-bold text-[#2c3856] leading-tight" style="font-family: 'Raleway', sans-serif;">
                                    {{ Auth::user()->name }}
                                </p>
                                <p class="text-[10px] text-gray-500 font-medium truncate max-w-[100px]" style="font-family: 'Montserrat', sans-serif;">
                                    {{ Auth::user()->position ?? 'Usuario' }}
                                </p>
                            </div>

                            <svg class="h-4 w-4 text-gray-400 group-hover:text-[#ff9c00] transition-transform duration-300 transform" 
                                 :class="{'rotate-180': userMenuOpen}"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="userMenuOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl ring-1 ring-black ring-opacity-5 z-50 overflow-hidden origin-top-right">
                            
                            <div class="relative bg-[#2c3856] p-6 text-center group">
                                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 rounded-full bg-[#ff9c00] opacity-10 blur-xl"></div>
                                <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-20 h-20 rounded-full bg-white opacity-5 blur-xl"></div>

                                <div class="relative inline-block mb-3">
                                    @if (Auth::user()->profile_photo_path)
                                        <img class="h-24 w-24 rounded-2xl object-cover border-4 border-white/10 shadow-lg transform transition-transform duration-500 hover:scale-105 hover:rotate-1" 
                                             src="{{ Storage::disk('s3')->url(Auth::user()->profile_photo_path) }}" 
                                             alt="{{ Auth::user()->name }}">
                                    @else
                                        <div class="h-24 w-24 rounded-2xl bg-white/10 text-white flex items-center justify-center text-3xl font-bold border-4 border-white/10 shadow-lg backdrop-blur-sm">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-[#ff9c00] text-white text-[10px] font-bold px-3 py-0.5 rounded-full shadow-md whitespace-nowrap border border-[#2c3856]">
                                        {{ Auth::user()->area?->name ?? 'General' }}
                                    </div>
                                </div>

                                <h3 class="text-white text-lg font-extrabold tracking-wide" style="font-family: 'Raleway', sans-serif;">
                                    {{ Auth::user()->name }}
                                </h3>
                                <p class="text-gray-300 text-sm font-medium mt-1" style="font-family: 'Montserrat', sans-serif;">
                                    {{ Auth::user()->position ?? 'Sin Puesto Definido' }}
                                </p>
                            </div>

                            <div class="p-3 bg-white">
                                
                                <div class="mb-3 p-3 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center">
                                    <button @click="toggleTheme()" class="relative inline-flex items-center h-8 rounded-full w-32 transition-colors duration-300 focus:outline-none bg-white border border-gray-200 shadow-inner overflow-hidden">
                                        <div class="absolute inset-0 opacity-20 transition-colors duration-300" 
                                             :class="theme === 'default' ? 'bg-[#2c3856]' : 'bg-[#ff9c00]'"></div>
                                        
                                        <span class="absolute w-full text-center text-[10px] font-bold z-10 transition-opacity duration-300"
                                              :class="theme === 'default' ? 'text-[#2c3856] pr-6 opacity-100' : 'opacity-0'">
                                              MINMER
                                        </span>
                                        <span class="absolute w-full text-center text-[10px] font-bold z-10 transition-opacity duration-300"
                                              :class="theme === 'gold' ? 'text-[#B8860B] pl-6 opacity-100' : 'opacity-0'">
                                              GOLD
                                        </span>

                                        <span class="absolute left-1 inline-block w-6 h-6 transform transition-transform duration-300 ease-spring rounded-full shadow bg-white flex items-center justify-center z-20"
                                              :class="theme === 'default' ? 'translate-x-0' : 'translate-x-24'">
                                            <svg x-show="theme === 'default'" class="w-3.5 h-3.5 text-[#2c3856]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                                            <svg x-show="theme === 'gold'" class="w-3.5 h-3.5 text-[#ff9c00]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </span>
                                    </button>
                                </div>

                                <div class="space-y-1">
                                    <a href="{{ route('profile.edit') }}" 
                                       class="flex items-center px-4 py-3 text-sm text-gray-700 rounded-xl hover:bg-gray-50 hover:text-[#2c3856] transition-all duration-200 group">
                                        <div class="mr-3 p-2 rounded-lg bg-gray-100 text-gray-500 group-hover:bg-[#e8ebf5] group-hover:text-[#2c3856] transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <div>
                                            <span class="font-bold block">Mi Perfil</span>
                                            <span class="text-xs text-gray-400 font-medium">Configuración de cuenta</span>
                                        </div>
                                        <svg class="w-4 h-4 ml-auto text-gray-300 group-hover:text-[#ff9c00] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>

                                    <div class="border-t border-gray-100 my-1"></div>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full flex items-center px-4 py-3 text-sm text-red-600 rounded-xl hover:bg-red-50 transition-all duration-200 group">
                                            <div class="mr-3 p-2 rounded-lg bg-red-50 text-red-400 group-hover:bg-red-100 group-hover:text-red-600 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                            </div>
                                            <span class="font-bold">Cerrar Sesión</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-200 hover:bg-gray-700 focus:outline-none focus:bg-gray-700 focus:text-gray-200 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" 
         class="hidden sm:hidden mobile-menu-bg transition-all duration-300 ease-in-out shadow-lg max-h-[calc(100vh-4rem)] overflow-y-auto"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-4">
        
        <div class="pt-2 pb-3 space-y-1">
            
            @if(Auth::user()->hasModuleAccess('dashboard'))
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="mobile-menu-link">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->is_client && Auth::user()->hasModuleAccess('client_dashboard'))
                <x-responsive-nav-link :href="route('tablero.index')" :active="request()->routeIs('tablero.index')" class="mobile-menu-link">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasModuleAccess('files'))
                <x-responsive-nav-link :href="route('folders.index')" :active="request()->routeIs('folders.index')" class="mobile-menu-link">
                    @if (Auth::user()->is_client)
                        {{ __('Archivos') }}
                    @else
                        {{ __('Gestión de Archivos') }}
                    @endif
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasModuleAccess('orders'))
                <div x-data="{ 
                    isFnFMenuOpen: localStorage.getItem('isFnFMenuMobileOpen') !== null ? JSON.parse(localStorage.getItem('isFnFMenuMobileOpen')) : {{ request()->routeIs('ff.*') ? 'true' : 'false' }} 
                }" 
                x-init="$watch('isFnFMenuOpen', value => localStorage.setItem('isFnFMenuMobileOpen', value))"
                class="pt-1 pb-1">
                    <div class="flex items-center justify-between w-full px-4 text-white hover:bg-gray-700 transition duration-150 ease-in-out"
                        :class="isFnFMenuOpen ? 'text-[#ff9c00] bg-gray-700' : ''">
                        
                        <a href="{{ route('ff.dashboard.index') }}" 
                           class="flex-grow py-2 text-base font-medium text-inherit hover:text-[#ff9c00] transition-colors">
                            {{ __('Operaciones') }}
                        </a>

                        <button @click.prevent.stop="isFnFMenuOpen = !isFnFMenuOpen" 
                                class="p-2 -mr-2 hover:bg-white/10 rounded-full transition-colors focus:outline-none">
                            <svg class="h-5 w-5 transform transition-transform" :class="{'rotate-180': isFnFMenuOpen}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>

                    <div x-show="isFnFMenuOpen" x-transition class="mt-2 space-y-1 pl-4 border-l-2 border-[#ff9c00] ml-4 bg-black/10 rounded-r-lg">
                        @if(Auth::user()->canSeeFfTile('orders'))
                        <x-responsive-nav-link :href="route('ff.orders.index')" :active="request()->routeIs('ff.orders.*')" class="mobile-menu-link {{ request()->routeIs('ff.orders.*') ? 'text-[#ff9c00] bg-white/10' : '' }}">
                            {{ __('Pedidos') }}
                        </x-responsive-nav-link>
                        @endif

                        @if(Auth::user()->canSeeFfTile('inventory'))
                        <x-responsive-nav-link :href="route('ff.inventory.index')" :active="request()->routeIs('ff.inventory.*')" class="mobile-menu-link {{ request()->routeIs('ff.inventory.*') ? 'text-[#ff9c00] bg-white/10' : '' }}">
                            {{ __('Inventario') }}
                        </x-responsive-nav-link>
                        @endif

                        @if(Auth::user()->canSeeFfTile('catalog'))
                        <x-responsive-nav-link :href="route('ff.catalog.index')" :active="request()->routeIs('ff.catalog.*')" class="mobile-menu-link {{ request()->routeIs('ff.catalog.*') ? 'text-[#ff9c00] bg-white/10' : '' }}">
                            {{ __('Catálogo') }}
                        </x-responsive-nav-link>
                        @endif

                        @if(Auth::user()->canSeeFfTile('reports'))
                        <x-responsive-nav-link :href="route('ff.reports.index')" :active="request()->routeIs('ff.reports.*')" class="mobile-menu-link {{ request()->routeIs('ff.reports.*') ? 'text-[#ff9c00] bg-white/10' : '' }}">
                            {{ __('Reportes') }}
                        </x-responsive-nav-link>
                        @endif

                        @if(Auth::user()->canSeeFfTile('admin'))
                        <x-responsive-nav-link :href="route('ff.admin.index')" :active="request()->routeIs('ff.admin.*')" class="mobile-menu-link {{ request()->routeIs('ff.admin.*') ? 'text-[#ff9c00] bg-white/10' : '' }}">
                            {{ __('Administración') }}
                        </x-responsive-nav-link>
                        @endif
                    </div>
                </div>
            @endif

            @if(Auth::user()->hasModuleAccess('organigram'))
                @if (Auth::user()->is_client)
                    <x-responsive-nav-link :href="route('client.organigram.interactive')" :active="request()->routeIs('client.organigram.interactive')" class="mobile-menu-link">
                        {{ __('Organigrama') }}
                    </x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('admin.organigram.index')" :active="request()->routeIs('admin.organigram.*')" class="mobile-menu-link">
                        {{ __('Organigrama') }}
                    </x-responsive-nav-link>
                @endif
            @endif

            @if(Auth::user()->hasModuleAccess('tracking'))
                <x-responsive-nav-link :href="route('tracking.index')" :active="request()->routeIs('tracking.index')" class="mobile-menu-link" target="_blank" rel="noopener noreferrer">
                    {{ __('Tracking') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasModuleAccess('rfq'))
                <x-responsive-nav-link :href="route('rfq.index')" class="text-[#FF9C00] hover:text-orange-400 focus:text-orange-400 font-semibold focus:outline-none focus:bg-gray-700 mobile-menu-link">
                    {{ __('RFQ Moët Hennessy') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasModuleAccess('carbon'))
                <x-responsive-nav-link href="#" class="mobile-menu-link">
                    {{ __('Huella de Carbono') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasModuleAccess('certifications'))
                <x-responsive-nav-link href="#" class="mobile-menu-link">
                    {{ __('Certificaciones') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasModuleAccess('assistance'))
                <x-responsive-nav-link :href="$whatsappLink" target="_blank" class="mobile-menu-link">
                    {{ __('Asistencia') }}
                </x-responsive-nav-link>
            @endif

            @if(!Auth::user()->is_client && Auth::user()->hasModuleAccess('visits'))
                <x-responsive-nav-link :href="route('area_admin.visits.index')" :active="request()->routeIs('area_admin.visits.*')" class="mobile-menu-link">
                    {{ __('Gestión de Visitas') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasModuleAccess('routes'))
                <x-responsive-nav-link :href="route('rutas.dashboard')" :active="request()->routeIs('rutas.*')" class="mobile-menu-link">
                    {{ __('Gestión de Rutas') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasModuleAccess('tickets'))
                <x-responsive-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')" class="mobile-menu-link">
                    {{ __('Tickets de Soporte') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasModuleAccess('projects'))
                @can('viewAny', App\Models\Project::class)
                    <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')" class="mobile-menu-link">
                        {{ __('Proyectos') }}
                    </x-responsive-nav-link>  
                @endcan
            @endif

            @if(Auth::user()->hasModuleAccess('wms'))
                <x-responsive-nav-link :href="route('wms.dashboard')" :active="request()->routeIs('wms.*')" class="mobile-menu-link">
                    {{ __('WMS') }}
                </x-responsive-nav-link>
            @endif

            @if(Auth::user()->hasModuleAccess('customer_service'))
                <x-responsive-nav-link :href="route('customer-service.index')" :active="request()->routeIs('customer-service.*')" class="mobile-menu-link">
                    {{ __('Customer Service') }}
                </x-responsive-nav-link>
            @endif
            
            @if(Auth::user()->hasModuleAccess('electronic_label'))
                <x-responsive-nav-link :href="route('electronic-label.index')" :active="request()->routeIs('electronic-label.*')" class="mobile-menu-link">
                    {{ __('Marbete Electrónico') }}
                </x-responsive-nav-link>
            @endif            
            
            @if (Auth::user()->isSuperAdmin())
                <div class="pt-4 pb-3 mobile-menu-divider">
                    <button @click="isMobileSuperAdminMenuOpen = !isMobileSuperAdminMenuOpen" class="flex items-center justify-between w-full px-4 py-2 text-left text-base font-medium text-white hover:text-[#ff9c00] hover:bg-gray-700 focus:outline-none focus:text-[#ff9c00] focus:bg-gray-700 transition duration-150 ease-in-out">
                        <span class="font-semibold">Super Admin</span>
                        <svg class="h-5 w-5 transform transition-transform" :class="{'rotate-180': isMobileSuperAdminMenuOpen}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="isMobileSuperAdminMenuOpen" x-transition class="mt-2 space-y-1 pl-4 border-l-2 border-[#ff9c00] ml-4">
                        <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="mobile-menu-link">
                            {{ __('Panel General') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.statistics.index')" :active="request()->routeIs('admin.statistics.*')" class="mobile-menu-link">
                            {{ __('Estadísticas') }}
                        </x-responsive-nav-link>
                    </div>
                </div>
            @elseif (Auth::user()->hasModuleAccess('area_admin'))
                <div class="pt-4 pb-3 mobile-menu-divider">
                    <button @click="isMobileAreaAdminMenuOpen = !isMobileAreaAdminMenuOpen" class="flex items-center justify-between w-full px-4 py-2 text-left text-base font-medium text-white hover:text-[#ff9c00] hover:bg-gray-700 focus:outline-none focus:text-[#ff9c00] focus:bg-gray-700 transition duration-150 ease-in-out">
                        <span class="font-semibold">Admin de Área</span>
                        <svg class="h-5 w-5 transform transition-transform" :class="{'rotate-180': isMobileAreaAdminMenuOpen}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="isMobileAreaAdminMenuOpen" x-transition class="mt-2 space-y-1 pl-4 border-l-2 border-[#ff9c00] ml-4">
                        <x-responsive-nav-link :href="route('area_admin.dashboard')" :active="request()->routeIs('area_admin.dashboard')" class="mobile-menu-link">
                            {{ __('Panel de Área') }}
                        </x-responsive-nav-link>
                        @if (Auth::user()->isSuperAdmin())
                            <x-responsive-nav-link :href="route('area_admin.users.index')" :active="request()->routeIs('area_admin.users.*')" class="mobile-menu-link">
                                {{ __('Gestión de Usuarios') }}
                            </x-responsive-nav-link>
                        @endif
                        <x-responsive-nav-link :href="route('area_admin.folder_permissions.index')" :active="request()->routeIs('area_admin.folder_permissions.*')" class="mobile-menu-link">
                            {{ __('Permisos de Carpetas') }}
                        </x-responsive-nav-link>
                    </div>
                </div>
            @endif
        </div>

        <div class="pt-4 pb-3 mobile-menu-divider">
            <button @click="toggleTheme()" class="mobile-menu-link flex items-center w-full text-left">
                <svg x-show="theme === 'default'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg x-show="theme === 'gold'" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>

                <span x-show="theme === 'default'">Cambiar a Tema Dorado</span>
                <span x-show="theme === 'gold'">Cambiar a Tema Azul</span>
            </button>
        </div>

        <div class="pt-4 pb-1 mobile-menu-divider">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm mobile-menu-user-email">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="mobile-menu-link">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();" class="mobile-menu-link">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>