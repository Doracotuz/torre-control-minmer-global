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
     class="bg-white border-b border-gray-100 shadow-md relative z-0">
    <!-- Primary Navigation Menu -->
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center">
                <!-- Título del Área y Nombre de la Carpeta -->
                <div class="ml-4 text-sm font-medium text-gray-700 flex items-center space-x-2">
                    @if (Auth::user()->area)
                        <span class="text-[#2c3856] text-lg font-semibold" style="font-family: 'Raleway', sans-serif;">{{ Auth::user()->area->name }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    @endif
                    @if (isset($currentFolder) && $currentFolder)
                        <span class="text-[#ff9c00] text-lg font-semibold" style="font-family: 'Montserrat', sans-serif;">{{ $currentFolder->name }}</span>
                    @else
                        <span class="text-gray-500 text-lg font-semibold" style="font-family: 'Montserrat', sans-serif;">Raíz</span>
                    @endif
                </div>
            </div>

            <!-- Search Bar with Predictive Search -->
            <div class="flex-1 max-w-md mx-8 relative">
                <form action="{{ route('folders.index') }}" method="GET" class="relative">
                    <input type="text" name="search" placeholder="Buscar documentos..."
                           x-model="search"
                           x-on:focus="showSuggestions = suggestions.length > 0 && search.length > 2"
                           class="w-full pl-10 pr-4 py-2 rounded-full border-2 border-gray-300 focus:border-[#ff9c00] focus:ring-[#ff9c00] transition-all duration-300 ease-in-out shadow-sm
                                  hover:border-[#2c3856] focus:shadow-lg"
                           value="{{ request('search') }}">
                    <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#2c3856]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>

                <!-- Search Suggestions Dropdown -->
                <div x-show="showSuggestions"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="absolute z-20 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto"
                     style="display: none;"
                >
                    <template x-for="suggestion in suggestions" :key="suggestion.id">
                        <a :href="suggestion.type === 'folder' ? `{{ url('/folders') }}/${suggestion.id}` : (suggestion.type === 'file' ? `{{ url('/files') }}/${suggestion.id}/download` : suggestion.url)"
                           @click="search = suggestion.name; showSuggestions = false;"
                           class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer transition-colors duration-150 border-b border-gray-100 last:border-b-0">
                            <template x-if="suggestion.type === 'folder'">
                                <svg class="w-5 h-5 text-[#ff9c00] mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            </template>
                            <template x-if="suggestion.type === 'file'">
                                <svg class="w-5 h-5 text-gray-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </template>
                            <template x-if="suggestion.type === 'link'">
                                <svg class="w-5 h-5 text-blue-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </template>
                            <span x-text="suggestion.name" class="truncate"></span>
                            <span class="text-xs text-gray-500 ml-auto" x-text="suggestion.area"></span>
                        </a>
                    </template>
                    <div x-show="suggestions.length === 0 && search.length > 2" class="px-4 py-2 text-gray-500">
                        No hay sugerencias.
                    </div>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Usar route() directamente para las rutas de Breeze --}}
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
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

    <!-- Responsive Navigation Menu -->
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

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
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
