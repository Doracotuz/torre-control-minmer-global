<nav x-data="{ open: false, search: '', suggestions: [], showSuggestions: false, timeout: null }"
     x-init="$watch('search', value => {
         clearTimeout(this.timeout);
         if (value.length > 2) { // Start searching after 2 characters
             this.timeout = setTimeout(() => {
                 // Usar la ruta web protegida por 'auth' en lugar de '/api/'
                 fetch(`{{ route('search.suggestions') }}?query=${value}`)
                     .then(response => response.json())
                     .then(data => {
                         this.suggestions = data;
                         this.showSuggestions = data.length > 0;
                     })
                     .catch(error => {
                         console.error('Error fetching search suggestions:', error);
                         this.suggestions = [];
                         this.showSuggestions = false;
                     });
             }, 300); // Debounce search input
         } else {
             this.suggestions = [];
             this.showSuggestions = false;
         }
     })"
     @click.away="showSuggestions = false"
     class="bg-white border-b border-gray-100 shadow-md relative z-20">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center space-x-2 sm:space-x-4">
                <div class="flex items-center text-gray-700 text-sm font-medium">
                    @if (Auth::user()->area)
                        @php
                            $areaName = Auth::user()->area->name;
                            $areaIconSvg = '';
                            $areaCustomIconPath = Auth::user()->area->icon_path;
                            
                            if (!$areaCustomIconPath) {
                                switch ($areaName) {
                                    case 'Recursos Humanos': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#2c3856]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h-5v-2a3 3 0 013-3h2a3 3 0 013 3v2h-5zM9 10a3 3 0 11-6 0 3 3 0 016 0zM11 12a3 3 0 10-6 0 3 3 0 006 0z"></path></svg>'; break;
                                    case 'Customer Service': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#2c3856]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>'; break;
                                    case 'Tráfico': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#2c3856]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17l-4 4m0 0l-4-4m4 4V3m6 18v-3.586a1 1 0 01.293-.707l2.414-2.414A1 1 0 0115.586 14H18a2 2 0 002-2V7a2 2 0 00-2-2h-3.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0110 2.414V1H4a2 2 0 00-2 2v14a2 2 0 002 2h5z"></path></svg>'; break;
                                    case 'Almacén': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#2c3856]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>'; break;
                                    case 'Valor Agregado': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#2c3856]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>'; break;
                                    case 'POSM': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#2c3856]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>'; break;
                                    case 'Brokerage': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#2c3856]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l-3 3m3-3V3m3 12h8a2 2 0 002-2V7a2 2 0 00-2-2h-3l-4-3H9a2 2 0 00-2 2v4m-7 10h14a2 2 0 002-2V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4"></path></svg>'; break;
                                    case 'Innovación y Desarrollo': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#2c3856]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 20v-3m0 0l.688-.688c.482-.482 1.132-.756 1.802-.756h.71a2 2 0 002-2V8.5a2 2 0 00-2-2h-.71c-.67 0-1.32-.274-1.802-.756L12 5m0 15v-3m0 0l-.688-.688c-.482-.482-1.132-.756-1.802-.756H6a2 2 0 01-2-2V8.5a2 2 0 012-2h.71c.67 0 1.32-.274 1.802-.756L12 5m0 15v-3m0 0l.688-.688c.482-.482 1.132-.756 1.802-.756h.71a2 2 0 002-2V8.5a2 2 0 00-2-2h-.71c-.67 0-1.32-.274-1.802-.756L12 5"></path></svg>'; break;
                                    case 'Administración': $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#2c3856]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.827 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.827 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.827-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.827-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>'; break;
                                    default: $areaIconSvg = '<svg class="w-5 h-5 sm:w-6 sm:h-6 mr-3 text-[#2c3856]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>'; break;
                                }
                            }
                        @endphp
                        
                        @if ($areaCustomIconPath)
                            <img src="{{ asset('storage/' . $areaCustomIconPath) }}" alt="{{ $areaName }} Icon" class="h-5 w-5 sm:h-6 sm:w-6 mr-3 object-contain">
                        @else
                            {!! $areaIconSvg !!}
                        @endif

                        <span class="text-[#2c3856] text-base sm:text-lg font-semibold" style="font-family: 'Raleway', sans-serif;">{{ $areaName }}</span>
                    @endif
                </div>
            </div>

            <div class="flex-1 max-w-md mx-4 sm:mx-8 relative">
                <form action="{{ route('folders.index') }}" method="GET" class="relative">
                    <input type="text" name="search" placeholder="Buscar documentos..."
                           x-model="search"
                           x-on:focus="showSuggestions = suggestions.length > 0 && search.length > 2"
                           class="w-full pl-8 pr-2 py-1.5 sm:pl-10 sm:pr-4 sm:py-2 rounded-full border-2 border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all duration-300 ease-in-out shadow-sm
                                  hover:border-[#2c3856] focus:shadow-lg text-sm sm:text-base"
                           value="{{ request('search') }}">
                    <button type="submit" class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2c3856] sm:left-3">
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
                     class="absolute z-30 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto"
                     style="display: none;"
                >
                    <template x-for="suggestion in suggestions" :key="suggestion.id">
                        <a :href="suggestion.type === 'folder' ? `{{ url('/folders') }}/${suggestion.id}` : (suggestion.type === 'file' ? `{{ url('/files') }}/${suggestion.id}/download` : suggestion.url)"
                           @click="search = suggestion.name; showSuggestions = false;"
                           class="flex flex-col px-3 py-2 sm:px-4 sm:py-2 hover:bg-gray-100 cursor-pointer transition-colors duration-150 border-b border-gray-100 last:border-b-0">
                            <div class="flex items-center text-sm sm:text-base">
                                <template x-if="suggestion.type === 'folder'">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#ff9c00] mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                </template>
                                <template x-if="suggestion.type === 'file'">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                </template>
                                <template x-if="suggestion.type === 'link'">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </template>
                                <span x-text="suggestion.name" class="font-medium text-gray-800 truncate"></span>
                                <span class="text-xxs sm:text-xs text-gray-500 ml-auto" x-text="suggestion.area"></span>
                            </div>
                            <div class="mt-0.5 text-xxs sm:text-xs text-gray-500 truncate" x-text="suggestion.full_path"></div>
                        </a>
                    </template>
                    <div x-show="suggestions.length === 0 && search.length > 2" class="px-3 py-2 sm:px-4 sm:py-2 text-gray-500 text-sm">
                        No hay sugerencias.
                    </div>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-2 py-1.5 sm:px-3 sm:py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            {{-- Foto de perfil --}}
                            @if (Auth::user()->profile_photo_path)
                                <img class="h-7 w-7 sm:h-8 sm:w-8 rounded-full object-cover mr-2 object-center" src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="{{ Auth::user()->name }}">
                            @else
                                {{-- Icono SVG de usuario por defecto si no hay foto --}}
                                <svg class="h-7 w-7 sm:h-8 sm:w-8 rounded-full text-gray-400 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                            @endif
                            <div class="hidden sm:block">{{ Auth::user()->name }}</div>

                            <div class="ms-0.5 sm:ms-1">
                                <svg class="fill-current h-3 w-3 sm:h-4 sm:w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Usar route() directamente para las rutas de Breeze --}}
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('folders.index')" :active="request()->routeIs('folders.index')">
                {{ __('Gestión de Archivos') }}
            </x-responsive-nav-link>

            @if (Auth::user()->area && Auth::user()->area->name === 'Administración')
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('Panel General (Super Admin)') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.areas.index')" :active="request()->routeIs('admin.areas.index')">
                        {{ __('Gestionar Áreas') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')">
                        {{ __('Gestionar Usuarios') }}
                    </x-responsive-nav-link>
                </div>
            @elseif (Auth::user()->is_area_admin)
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <x-responsive-nav-link :href="route('area_admin.dashboard')" :active="request()->routeIs('area_admin.dashboard')">
                        {{ __('Panel de Mi Área (Admin de Área)') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('area_admin.users.index')" :active="request()->routeIs('area_admin.users.index')">
                        {{ __('Usuarios de Mi Área') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('area_admin.folder_permissions.index')" :active="request()->routeIs('area_admin.folder_permissions.index')">
                        {{ __('Permisos de Carpetas') }}
                    </x-responsive-nav-link>
                </div>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>