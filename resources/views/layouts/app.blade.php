<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Google Fonts: Raleway Extrabold and Montserrat Regular -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            /* Custom CSS for more refined active state and hover effects */
            .nav-link-custom {
                position: relative;
                overflow: hidden;
                transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); /* Smoother transition */
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for all links */
            }
            .nav-link-custom::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1); /* Slower, elegant shine */
            }
            .nav-link-custom:hover::before {
                left: 100%;
            }
            .nav-link-custom.active-link {
                background-color: #ff9c00; /* Orange background for active */
                color: white;
                box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3); /* More pronounced shadow for active */
                transform: translateY(-2px); /* Slight lift for active */
            }
            .nav-link-custom:hover:not(.active-link) {
                background-color: #2b2b2b; /* Dark gray on hover for non-active */
                color: white;
                transform: translateY(-2px); /* Slight lift on hover */
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); /* Medium shadow on hover */
            }
            .nav-link-custom span {
                position: relative; /* Ensure text is above ::before pseudo-element */
                z-index: 1;
            }
        </style>
    </head>
    <body class="font-sans antialiased" style="font-family: 'Montserrat', sans-serif;">
        <div class="min-h-screen bg-gray-100 flex">

            <!-- Sidebar (Nueva Sección con estilos modernos y animaciones) -->
            <div class="w-64 bg-[#2c3856] text-white flex flex-col min-h-screen shadow-2xl relative z-10 transition-all duration-500 ease-in-out"> {{-- Color de fondo principal, sombra más pronunciada --}}
                <div class="p-6 text-center border-b border-gray-700/50 flex flex-col items-center justify-center"> {{-- Más padding y centrado --}}
                    <img src="{{ asset('storage/LogoBlanco.png') }}" alt="Minmer Global Logo" class="h-24 mx-auto mb-3 transition-transform duration-700 ease-in-out hover:scale-115 transform origin-center"> {{-- Logo más grande y con animación más marcada --}}
                    <span class="text-xl font-extrabold text-white tracking-wide" style="font-family: 'Raleway', sans-serif;">TORRE DE CONTROL</span> {{-- Fuente Raleway, espaciado de letras --}}
                    <span class="text-xs text-gray-300 mt-1" style="font-family: 'Montserrat', sans-serif;">MINMER GLOBAL</span>
                </div>
                <nav class="flex-1 px-4 py-8 space-y-4"> {{-- Más padding y espacio entre elementos --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="nav-link-custom group block px-4 py-3 text-base font-medium rounded-lg text-gray-100
                        {{ request()->routeIs('dashboard') ? 'active-link' : '' }}">
                        <span class="transition-transform duration-300 ease-out group-hover:translate-x-2">{{ __('Dashboard') }}</span>
                    </x-nav-link>

                    <x-nav-link :href="route('folders.index')" :active="request()->routeIs('folders.index')" class="nav-link-custom group block px-4 py-3 text-base font-medium rounded-lg text-gray-100
                        {{ request()->routeIs('folders.index') ? 'active-link' : '' }}">
                        <span class="transition-transform duration-300 ease-out group-hover:translate-x-2">{{ __('Gestión de Archivos') }}</span>
                    </x-nav-link>

                    @if (Auth::user()->area && Auth::user()->area->name === 'Administración')
                        <div class="border-t border-gray-700/50 pt-6 mt-6"> {{-- Separador con transparencia y más espacio --}}
                            <span class="block px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider" style="font-family: 'Raleway', sans-serif;">Super Admin</span>
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="nav-link-custom group block px-4 py-3 text-base font-medium rounded-lg text-gray-100
                                {{ request()->routeIs('admin.dashboard') ? 'active-link' : '' }}">
                                <span class="transition-transform duration-300 ease-out group-hover:translate-x-2">{{ __('Panel General') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.areas.index')" :active="request()->routeIs('admin.areas.index')" class="nav-link-custom group block px-4 py-3 text-base font-medium rounded-lg text-gray-100
                                {{ request()->routeIs('admin.areas.index') ? 'active-link' : '' }}">
                                <span class="transition-transform duration-300 ease-out group-hover:translate-x-2">{{ __('Gestionar Áreas') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')" class="nav-link-custom group block px-4 py-3 text-base font-medium rounded-lg text-gray-100
                                {{ request()->routeIs('admin.users.index') ? 'active-link' : '' }}">
                                <span class="transition-transform duration-300 ease-out group-hover:translate-x-2">{{ __('Gestionar Usuarios') }}</span>
                            </x-nav-link>
                        </div>
                    @elseif (Auth::user()->is_area_admin)
                        <div class="border-t border-gray-700/50 pt-6 mt-6"> {{-- Separador con transparencia y más espacio --}}
                            <span class="block px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider" style="font-family: 'Raleway', sans-serif;">Admin de Área</span>
                            <x-nav-link :href="route('area_admin.dashboard')" :active="request()->routeIs('area_admin.dashboard')" class="nav-link-custom group block px-4 py-3 text-base font-medium rounded-lg text-gray-100
                                {{ request()->routeIs('area_admin.dashboard') ? 'active-link' : '' }}">
                                <span class="transition-transform duration-300 ease-out group-hover:translate-x-2">{{ __('Panel de Mi Área') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('area_admin.users.index')" :active="request()->routeIs('area_admin.users.index')" class="nav-link-custom group block px-4 py-3 text-base font-medium rounded-lg text-gray-100
                                {{ request()->routeIs('area_admin.users.index') ? 'active-link' : '' }}">
                                <span class="transition-transform duration-300 ease-out group-hover:translate-x-2">{{ __('Usuarios de Mi Área') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('area_admin.folder_permissions.index')" :active="request()->routeIs('area_admin.folder_permissions.index')" class="nav-link-custom group block px-4 py-3 text-base font-medium rounded-lg text-gray-100
                                {{ request()->routeIs('area_admin.folder_permissions.index') ? 'active-link' : '' }}">
                                <span class="transition-transform duration-300 ease-out group-hover:translate-x-2">{{ __('Permisos de Carpetas') }}</span>
                            </x-nav-link>
                        </div>
                    @endif
                </nav>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col bg-gray-100"> {{-- Fondo del contenido principal --}}
                @include('layouts.navigation') {{-- Esto es el nav de Breeze que ya tenías --}}

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white shadow-sm"> {{-- Sombra más sutil --}}
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="flex-1 p-8"> {{-- Más padding al contenido principal --}}
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>