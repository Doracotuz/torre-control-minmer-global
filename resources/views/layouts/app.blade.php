<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Raleway:wght@800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script> -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
         
        <style>
            /* ==== REFINED STYLES FOR ELEGANCE AND INTERACTIVITY ==== */

            /* --- General Nav Link Style --- */
            .nav-link-custom {
                position: relative;
                display: flex;
                align-items: center;
                padding: 12px 16px;
                border-radius: 8px;
                font-weight: 500;
                color: #e5e7eb;
                transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
            }

            /* --- Left Border Indicator for Hover/Active --- */
            .nav-link-custom::before {
                content: '';
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                height: 0;
                width: 4px;
                background-color: #ff9c00; /* Brand Orange */
                border-radius: 2px;
                transition: height 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            }

            /* --- Hover State (Non-Active) --- */
            .nav-link-custom:hover:not(.active-link) {
                background-color: rgba(255, 255, 255, 0.05);
                color: #ffffff;
            }
            .nav-link-custom:hover:not(.active-link)::before {
                height: 60%;
            }

            /* --- Active Link State --- */
            .nav-link-custom.active-link {
                background-color: #ff9c00; /* Brand Orange */
                color: #ffffff;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(255, 156, 0, 0.2);
            }
            .nav-link-custom.active-link::before {
                height: 100%;
            }

            /* --- Icon and Text Styling --- */
            .nav-link-custom .nav-icon {
                flex-shrink: 0;
                width: 1.25rem;
                height: 1.25rem;
                margin-right: 12px;
                transition: transform 0.3s ease;
            }

            .nav-link-custom:hover .nav-text {
                transform: translateX(4px);
            }

            /* --- Elegant Logo Hover Effect --- */
            .logo-container {
                transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.4s ease;
                border-radius: 8px;
            }
            .logo-container:hover {
                transform: scale(1.03);
                background-color: rgba(255, 255, 255, 0.03);
            }
            .logo-container .logo-text {
                font-family: 'Raleway', sans-serif;
            }
            .logo-container .logo-subtitle {
                font-family: 'Montserrat', sans-serif;
            }

            /* --- Dropdown Styles --- */
            .dropdown-toggle {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
                padding: 10px 16px;
                font-family: 'Raleway', sans-serif;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .05em;
                color: #a0aec0;
                border-radius: 8px;
                transition: background-color 0.3s ease;
            }
            .dropdown-toggle:hover {
                background-color: rgba(255, 255, 255, 0.05);
                color: #cbd5e0;
            }
            .dropdown-toggle .chevron-icon {
                transition: transform 0.3s ease-in-out;
            }

            .sticky-sidebar {
                position: sticky;
                top: 0;
                min-height: 100vh; /* Asegura que la sidebar tenga al menos la altura de la ventana */
                align-self: flex-start; /* Ayuda a que sticky funcione correctamente dentro de un flex container */
            }


            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased" style="font-family: 'Montserrat', sans-serif;" x-cloak
        x-data="{
            /* State for collapsible menus - automatically opens if the current route matches */
            isSuperAdminMenuOpen: {{ request()->routeIs('admin.*') ? 'true' : 'false' }},
            isAreaAdminMenuOpen: {{ request()->routeIs('area_admin.*') ? 'true' : 'false' }}
        }"
    >
        <div class="min-h-screen bg-gray-100 flex">
            <div class="w-64 bg-[#2c3856] text-white flex-col min-h-screen shadow-2xl relative z-10 hidden lg:flex sticky-sidebar">
            <!-- <div class="w-64 bg-[#2c3856] text-white flex-col min-h-screen shadow-2xl relative z-10 hidden lg:flex"> -->
                <div class="p-6 text-center">
                    <div class="logo-container py-4">
                        <img src="{{ Storage::disk('s3')->url('LogoBlanco.png') }}" alt="Minmer Global Logo" class="h-20 mx-auto mb-3">
                        <span class="text-xl font-extrabold text-white tracking-wide logo-text">TORRE DE CONTROL</span>
                        <span class="text-xs text-gray-300 mt-1 block logo-subtitle">MINMER GLOBAL</span>
                    </div>
                </div>

                <div class="px-6"><div class="border-t border-white/10"></div></div>

                <nav class="flex-1 px-4 py-6 space-y-2">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active-link' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        <span class="nav-text">{{ __('Dashboard') }}</span>
                    </x-nav-link>

                    <x-nav-link :href="route('folders.index')" :active="request()->routeIs('folders.index')" class="nav-link-custom {{ request()->routeIs('folders.index') ? 'active-link' : '' }}">
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                        <span class="nav-text">{{ __('Gestión de Archivos') }}</span>
                    </x-nav-link>
                    <x-nav-link :href="route('tms.index')" :active="request()->routeIs('tms.*')" class="nav-link-custom {{ request()->routeIs('tms.*') ? 'active-link' : '' }}">
                        {{-- Icono de Camión (Truck) en formato SVG para mantener la consistencia --}}
                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5v-1.875a3.375 3.375 0 013.375-3.375h9.75a3.375 3.375 0 013.375 3.375v1.875m-17.25 4.5h16.5M5.625 9h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                        </svg>
                        <span class="nav-text">{{ __('TMS') }}</span>
                    </x-nav-link>               

                    {{-- Super Admin Collapsible Menu --}}
                    @if (Auth::user()->is_area_admin && Auth::user()->area?->name === 'Administración')
                        <div class="pt-4 mt-2 border-t border-white/10">
                            <button @click="isSuperAdminMenuOpen = !isSuperAdminMenuOpen" class="dropdown-toggle text-xs">
                                <span>Super Admin</span>
                                <svg class="chevron-icon w-4 h-4" :class="{'rotate-180': isSuperAdminMenuOpen}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </button>

                            <div x-show="isSuperAdminMenuOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="overflow-hidden">
                                <div class="pl-4 mt-2 space-y-2">
                                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="nav-link-custom {{ request()->routeIs('admin.dashboard') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-1.621-1.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span class="nav-text">{{ __('Panel General') }}</span>
                                    </x-nav-link>
                                    {{-- El resto de los links de admin... --}}
                                </div>
                            </div>
                        </div>
                    @elseif (Auth::user()->is_area_admin)
                        <div class="pt-4 mt-2 border-t border-white/10">
                            <button @click="isAreaAdminMenuOpen = !isAreaAdminMenuOpen" class="dropdown-toggle text-xs">
                                <span>Admin de Área</span>
                                <svg class="chevron-icon w-4 h-4" :class="{'rotate-180': isAreaAdminMenuOpen}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <div x-show="isAreaAdminMenuOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                class="overflow-hidden">
                                <div class="pl-4 mt-2 space-y-2">
                                    {{-- Links para Admin de Área --}}
                                    <x-nav-link :href="route('area_admin.dashboard')" :active="request()->routeIs('area_admin.dashboard')" class="nav-link-custom {{ request()->routeIs('area_admin.dashboard') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-1.621-1.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{-- Icono de ejemplo, puedes usar otro --}}
                                        <span class="nav-text">{{ __('Panel de Área') }}</span>
                                    </x-nav-link>

                                    <x-nav-link :href="route('area_admin.users.index')" :active="request()->routeIs('area_admin.users.*')" class="nav-link-custom {{ request()->routeIs('area_admin.users.*') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a8.967 8.967 0 0015 0H4.501z" /></svg> {{-- Icono de ejemplo, puedes usar otro --}}
                                        <span class="nav-text">{{ __('Gestión de Usuarios') }}</span>
                                    </x-nav-link>

                                    <x-nav-link :href="route('area_admin.folder_permissions.index')" :active="request()->routeIs('area_admin.folder_permissions.*')" class="nav-link-custom {{ request()->routeIs('area_admin.folder_permissions.*') ? 'active-link' : '' }}">
                                        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25H9.75m4.006-7.03a3.375 3.375 0 00-3.375-3.375H9.75M19.5 19.5h-1.5a3.375 3.375 0 00-3.375-3.375M12 2.253A8.962 8.962 0 0121 12c0 1.133-.213 2.21-.613 3.223M12 2.253A8.962 8.962 0 003 12c0 1.133.213 2.21.613 3.223" /></svg> {{-- Icono de ejemplo, puedes usar otro --}}
                                        <span class="nav-text">{{ __('Permisos de Carpetas') }}</span>
                                    </x-nav-link>

                                </div>
                            </div>
                        </div>
                    @endif
                </nav>
            </div>

            <div class="flex-1 flex flex-col bg-gray-100 w-full lg:w-auto">
                @include('layouts.navigation', ['currentFolder' => $currentFolder ?? null])
                @if (isset($header))
                    <header class="bg-white shadow-sm">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{{ $header }}</div>
                    </header>
                @endif
                <main class="flex-1 p-8">
                    @if (isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </main>

            </div>
        </div>
    </body>
</html>