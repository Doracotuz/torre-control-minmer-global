<x-app-layout>
    <style>
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.6s ease-out forwards;
            opacity: 0;
        }
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        .skeleton-bar {
            animation: shimmer 2s infinite linear;
            background: linear-gradient(to right, #e0e0e0 4%, #f0f0f0 25%, #e0e0e0 36%);
            background-size: 1000px 100%;
            border-radius: 0.375rem;
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Panel de Control de Área
            </h2>

            @php
                $user = Auth::user();
                $manageableAreas = $user->accessibleAreas;
                if (!$manageableAreas->contains('id', $user->area_id)) {
                    $manageableAreas->prepend($user->area);
                }
                $currentAreaId = session('current_admin_area_id', $user->area_id);
            @endphp
        </div>
    </x-slot>

    <div class="py-12 min-h-screen" x-data="areaDashboardData()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-8 px-4 sm:px-0">
                <h3 class="text-3xl font-bold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">
                    <span id="welcome-title">Panel de</span> <span id="area-name-title" class="text-[#ff9c00]">...</span>
                </h3>
                <p class="mt-1 text-lg text-gray-600">
                    Bienvenido, {{ Auth::user()->name }}.
                </p>
            </div>

            <div x-show="isAreaAdmin === true" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('area_admin.users.index') }}" 
                           class="group bg-white rounded-xl shadow-lg p-6 flex items-center space-x-5 transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-1 animate-fade-in-up" style="animation-delay: 0.1s;">
                            <div class="p-4 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700 transition-all duration-300 group-hover:from-blue-700 group-hover:to-blue-500 group-hover:text-white">
                                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m-7.5-2.962a3.75 3.75 0 1 0-7.5 0 3.75 3.75 0 0 0 7.5 0ZM10.5 1.5a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z" /></svg>
                            </div>
                            <div><h4 class="text-lg font-semibold text-[#2c3856] group-hover:text-blue-700">Gestionar Usuarios</h4><p class="text-sm text-gray-600">Añade, edita y gestiona tu equipo.</p></div>
                        </a>
                        <a href="{{ route('area_admin.folder_permissions.index') }}" 
                           class="group bg-white rounded-xl shadow-lg p-6 flex items-center space-x-5 transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-1 animate-fade-in-up" style="animation-delay: 0.2s;">
                            <div class="p-4 rounded-full bg-gradient-to-br from-orange-100 to-orange-200 text-[#ff9c00] transition-all duration-300 group-hover:from-[#ff9c00] group-hover:to-[#f2b04c] group-hover:text-white">
                                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286Z" /></svg>
                            </div>
                            <div><h4 class="text-lg font-semibold text-[#2c3856] group-hover:text-orange-600">Gestionar Permisos</h4><p class="text-sm text-gray-600">Define el acceso a las carpetas.</p></div>
                        </a>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="activity-breakdown-title">Desglose de Actividad (Últ. 30 días)</h4>
                        <div id="activity-breakdown-chart" class="flex justify-center items-center" style="min-height: 300px;"><div class="skeleton-bar h-64 w-64 rounded-full"></div></div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.4s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="activity-feed-title">Actividad Reciente</h4>
                        <ul id="recent-activity-list" class="space-y-4"><li id="activity-loader"><div class="flex items-center space-x-3"><div class="skeleton-bar rounded-full w-10 h-10"></div><div class="flex-1 space-y-2"><div class="skeleton-bar h-4 w-3/4"></div><div class="skeleton-bar h-3 w-1/2"></div></div></div></li></ul>
                    </div>
                </div>
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up border-l-4 border-purple-500 relative overflow-hidden" style="animation-delay: 0.05s;">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-purple-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-xl font-bold text-[#2c3856]">Deuda de Stock</h4>
                                <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-1 rounded-full animate-pulse">En espera</span>
                            </div>
                            <div class="flex items-end gap-2 mb-4">
                                <div id="backorder-count-container">
                                    <div class="skeleton-bar h-10 w-16 rounded"></div>
                                </div>
                                <p class="text-sm text-gray-500 mb-1 font-medium">pedidos pendientes</p>
                            </div>
                            <div class="space-y-3" id="backorder-list-container">
                                <div class="flex items-center space-x-3">
                                    <div class="skeleton-bar rounded w-8 h-8"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="skeleton-bar h-3 w-3/4"></div>
                                        <div class="skeleton-bar h-2 w-1/2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-gray-100 text-center">
                                <a href="{{ route('ff.inventory.backorders') }}" class="text-xs font-bold text-purple-600 hover:text-purple-800 hover:underline">
                                    Ir a Surtido <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.1s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="kpi-title">Métricas</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center" id="userCount-wrapper">
                                <div id="userCount-container"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Usuarios</p>
                            </div>
                            <div class="text-center">
                                <div id="folderCount-container"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Carpetas</p>
                            </div>
                            <div class="text-center">
                                <div id="fileCount-container"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Archivos</p>
                            </div>
                            <div class="text-center">
                                <div id="linkCount-container"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Enlaces</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.2s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="file-types-title">Tipos de Archivo</h4>
                        <div id="file-types-chart" class="flex justify-center items-center" style="min-height: 250px;"><div class="skeleton-bar h-48 w-48 rounded-full"></div></div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Miembros del Equipo</h4>
                        <ul id="team-members-list" class="space-y-3 max-h-96 overflow-y-auto"><li id="team-loader"><div class="flex items-center space-x-3"><div class="skeleton-bar rounded-full w-10 h-10"></div><div class="flex-1 space-y-2"><div class="skeleton-bar h-4 w-3/4"></div><div class="skeleton-bar h-3 w-1/2"></div></div></div></li></ul>
                    </div>
                </div>
            </div>

            <div x-show="isAreaAdmin === false" class="grid grid-cols-1 lg:grid-cols-3 gap-6" style="display: none;">
                <div class="lg:col-span-2 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a href="{{ route('folders.index') }}" 
                           class="group bg-white rounded-xl shadow-lg p-6 flex items-center space-x-5 transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-1 animate-fade-in-up" style="animation-delay: 0.1s;">
                            <div class="p-4 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-blue-700 transition-all duration-300 group-hover:from-blue-700 group-hover:to-blue-500 group-hover:text-white">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            </div>
                            <div><h4 class="text-lg font-semibold text-[#2c3856] group-hover:text-blue-700">Explorar Archivos</h4><p class="text-sm text-gray-600">Ver todas mis carpetas y archivos.</p></div>
                        </a>
                        
                        <a href="{{ route('profile.edit') }}" 
                           class="group bg-white rounded-xl shadow-lg p-6 flex items-center space-x-5 transition-all duration-300 ease-in-out hover:shadow-2xl hover:-translate-y-1 animate-fade-in-up" style="animation-delay: 0.2s;">
                            <div class="p-4 rounded-full bg-gradient-to-br from-purple-100 to-purple-200 text-purple-700 transition-all duration-300 group-hover:from-purple-700 group-hover:to-purple-500 group-hover:text-white">
                                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-[#2c3856] group-hover:text-purple-700">Editar Mi Perfil</h4>
                                <p class="text-sm text-gray-600">Actualizar mis datos y foto.</p>
                            </div>
                        </a>
                        </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Mis Archivos Modificados Recientemente</h4>
                        <ul id="recent-files-list" class="space-y-3">
                            <li id="files-loader"><div class="flex items-center space-x-3"><div class="skeleton-bar rounded-md w-10 h-10"></div><div class="flex-1 space-y-2"><div class="skeleton-bar h-4 w-3/4"></div><div class="skeleton-bar h-3 w-1/2"></div></div></div></li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.4s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="activity-feed-title-user">Mi Actividad Reciente</h4>
                        <ul id="recent-activity-list-user" class="space-y-4"><li id="activity-loader-user"><div class="flex items-center space-x-3"><div class="skeleton-bar rounded-full w-10 h-10"></div><div class="flex-1 space-y-2"><div class="skeleton-bar h-4 w-3/4"></div><div class="skeleton-bar h-3 w-1/2"></div></div></div></li></ul>
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.1s;">
                        <div id="my-profile-widget" class="flex items-center space-x-4">
                            <div class="skeleton-bar rounded-full w-16 h-16"></div>
                            <div class="flex-1 space-y-2">
                                <div class="skeleton-bar h-5 w-3/4"></div>
                                <div class="skeleton-bar h-4 w-1/2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.2s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="kpi-title-user">Mis Métricas</h4>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div id="folderCount-container-user"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Carpetas</p>
                            </div>
                            <div class="text-center">
                                <div id="fileCount-container-user"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Archivos</p>
                            </div>
                            <div class="text-center">
                                <div id="linkCount-container-user"><div class="skeleton-bar h-8 w-16 mx-auto mb-1"></div></div>
                                <p class="text-sm font-medium text-gray-500">Enlaces</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4" id="file-types-title-user">Mis Tipos de Archivo</h4>
                        <div id="file-types-chart-user" class="flex justify-center items-center" style="min-height: 250px;"><div class="skeleton-bar h-48 w-48 rounded-full"></div></div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-fade-in-up" style="animation-delay: 0.4s;">
                        <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Miembros del Equipo</h4>
                        <ul id="team-members-list-user" class="space-y-3 max-h-96 overflow-y-auto"><li id="team-loader-user"><div class="flex items-center space-x-3"><div class="skeleton-bar rounded-full w-10 h-10"></div><div class="flex-1 space-y-2"><div class="skeleton-bar h-4 w-3/4"></div><div class="skeleton-bar h-3 w-1/2"></div></div></div></li></ul>
                    </div>
                </div>
            </div>

        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('areaDashboardData', () => ({
                isAreaAdmin: null,
                
                colorPrimary: '#2c3856',
                colorSecondary: '#ff9c00',
                colorPalette: ['#2c3856', '#ff9c00', '#3e4e7a', '#f2b04c', '#5a6f9a'],
                fontFamily: 'Raleway, sans-serif',

                timeAgo(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const seconds = Math.floor((now - date) / 1000);
                    let interval = seconds / 3600;
                    if (interval > 24) return `hace ${Math.floor(interval / 24)} días`;
                    if (interval > 1) return `hace ${Math.floor(interval)} horas`;
                    interval = seconds / 60;
                    if (interval > 1) return `hace ${Math.floor(interval)} minutos`;
                    return `hace ${Math.floor(seconds)} segundos`;
                },

                getActivityIcon(action) {
                    action = (action || '').toLowerCase();
                    if (action.includes('subió') || action.includes('creó') || action.includes('creaciones')) {
                        return `<svg class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>`;
                    }
                    if (action.includes('eliminó') || action.includes('eliminaciones')) {
                        return `<svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12.54 0c-.27.041-.54.082-.811.124m-1.022-.165L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12.54 0c-.27.041-.54.082-.811.124" /></svg>`;
                    }
                    if (action.includes('descargó') || action.includes('descargas')) {
                        return `<svg class="w-5 h-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>`;
                    }
                    return `<svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>`;
                },

                getFileIcon(extension) {
                    extension = (extension || '').toLowerCase();
                    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
                        return `<svg class="w-7 h-7 text-green-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>`;
                    }
                    if (extension === 'pdf') {
                        return `<svg class="w-7 h-7 text-red-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>`;
                    }
                    if (['mp4', 'webm', 'mov'].includes(extension)) {
                        return `<svg class="w-7 h-7 text-indigo-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.196l-3.321-2.484a.5.5 0 00-.731.428v4.981a.5.5 0 00.73.429l3.322-2.484a.5.5 0 000-.858zM4 6v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2z"></path></svg>`;
                    }
                    if (['xls', 'xlsx', 'csv'].includes(extension)) {
                        return `<svg class="w-7 h-7 text-green-700 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>`;
                    }
                    return `<svg class="w-7 h-7 text-gray-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>`;
                },

                populateKPIs(data) {
                    const areaTitle = document.getElementById('area-name-title');
                    const welcomeTitle = document.getElementById('welcome-title');

                    if (areaTitle) areaTitle.textContent = data.areaName || 'No Asignada';
                    
                    if(data.isAreaAdmin) {
                        const kpiTitle = document.getElementById('kpi-title');
                        const userCountEl = document.getElementById('userCount-container');
                        const folderCountEl = document.getElementById('folderCount-container');
                        const fileCountEl = document.getElementById('fileCount-container');
                        const linkCountEl = document.getElementById('linkCount-container');

                        if (welcomeTitle) welcomeTitle.textContent = 'Panel de Gestión:';
                        if (kpiTitle) kpiTitle.textContent = 'Métricas de Área';
                        if (userCountEl) userCountEl.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.userCount}</p>`;
                        if (folderCountEl) folderCountEl.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.folderCount}</p>`;
                        if (fileCountEl) fileCountEl.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.fileCount}</p>`;
                        if (linkCountEl) linkCountEl.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.linkCount}</p>`;
                    } else {
                        const kpiTitleUser = document.getElementById('kpi-title-user');
                        const userCountWrapper = document.getElementById('userCount-wrapper');
                        const folderCountElUser = document.getElementById('folderCount-container-user');
                        const fileCountElUser = document.getElementById('fileCount-container-user');
                        const linkCountElUser = document.getElementById('linkCount-container-user');

                        if (welcomeTitle) welcomeTitle.textContent = 'Mi Panel de';
                        if (kpiTitleUser) kpiTitleUser.textContent = 'Mis Métricas';
                        if (userCountWrapper) userCountWrapper.style.display = 'none';
                        if (folderCountElUser) folderCountElUser.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.folderCount}</p>`;
                        if (fileCountElUser) fileCountElUser.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.fileCount}</p>`;
                        if (linkCountElUser) linkCountElUser.innerHTML = `<p class="text-3xl font-bold text-[#2c3856]">${data.linkCount}</p>`;
                    }
                },
                
                populateRecentActivity(activities, listElId) {
                    const listEl = document.getElementById(listElId);
                    if (!listEl) return;
                    
                    listEl.innerHTML = '';
                    if (!activities || activities.length === 0) {
                        listEl.innerHTML = '<p class="text-sm text-gray-500">No hay actividad reciente.</p>';
                        return;
                    }
                    activities.forEach(activity => {
                        const icon = this.getActivityIcon(activity.action);
                        const userName = activity.user ? activity.user.name : 'Usuario Desconocido';
                        const relativeTime = this.timeAgo(activity.created_at);
                        listEl.innerHTML += `
                            <li class="flex items-center space-x-3">
                                <div class="bg-gray-100 p-2 rounded-full">${icon}</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">${activity.action}</p>
                                    <p class="text-xs text-gray-500">por ${userName} (${relativeTime})</p>
                                </div>
                            </li>
                        `;
                    });
                },

                populateTeamList(members, listElId) {
                    const listEl = document.getElementById(listElId);
                    if (!listEl) return;

                    listEl.innerHTML = '';
                    if (!members || members.length === 0) {
                        listEl.innerHTML = '<p class="text-sm text-gray-500">No hay miembros en esta área.</p>';
                        return;
                    }
                    members.forEach(member => {
                        const profilePhoto = member.profile_photo_path_url
                            ? `<img class="h-10 w-10 rounded-full object-cover" src="${member.profile_photo_path_url}" alt="${member.name}">`
                            : `<span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-500"><span class="font-medium leading-none text-white">${member.name.charAt(0)}</span></span>`;
                        const positionName = (member.position ? member.position.name : (member.email || ''));
                        
                        listEl.innerHTML += `
                            <li class="flex items-center space-x-3">
                                <div>${profilePhoto}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-[#2c3856] truncate">${member.name}</p>
                                    <p class="text-xs text-gray-500 truncate">${positionName}</p>
                                </div>
                            </li>
                        `;
                    });
                },

                populateMyProfile(profile) {
                    const widgetEl = document.getElementById('my-profile-widget');
                    if (!widgetEl) return;

                    if (!profile) {
                        widgetEl.innerHTML = '<p class="text-sm text-gray-500">No se encontró perfil de organigrama.</p>';
                        return;
                    }
                    
                    const profilePhoto = profile.profile_photo_path_url
                        ? `<img class="h-16 w-16 rounded-full object-cover" src="${profile.profile_photo_path_url}" alt="${profile.name}">`
                        : `<span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-500"><span class="text-xl font-medium leading-none text-white">${profile.name.charAt(0)}</span></span>`;
                    
                    const positionName = profile.position ? profile.position.name : 'Sin Puesto Asignado';

                    widgetEl.innerHTML = `
                        <div>${profilePhoto}</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-lg font-bold text-[#2c3856] truncate">${profile.name}</h4>
                            <p class="text-sm text-gray-600 truncate">${positionName}</p>
                            
                            <p class="text-xs text-gray-500 truncate mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0h18" />
                                </svg>
                                <span>${profile.antiquity}</span>
                            </p>
                        </div>
                    `;
                },

                populateRecentFiles(files) {
                    const listEl = document.getElementById('recent-files-list');
                    if (!listEl) return;

                    listEl.innerHTML = '';
                    if (!files || files.length === 0) {
                        listEl.innerHTML = '<p class="text-sm text-gray-500">No has modificado archivos recientemente.</p>';
                        return;
                    }
                    files.forEach(file => {
                        const extension = (file.name || '').split('.').pop();
                        const icon = this.getFileIcon(extension);
                        const folderName = file.folder ? file.folder.name : 'Raíz';
                        
                        listEl.innerHTML += `
                            <li class="flex items-center space-x-3 p-3 -m-3 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                <div class="flex-shrink-0">${icon}</div>
                                <div class="flex-1 min-w-0">
                                    <a href="${file.folder ? '{{ url('folders') }}/' + file.folder.id : '{{ route('folders.index') }}'}" class="text-sm font-medium text-[#2c3856] truncate hover:underline">${file.name}</a>
                                    <p class="text-xs text-gray-500 truncate">En: ${folderName} &bull; ${this.timeAgo(file.updated_at)}</p>
                                </div>
                                <a href="{{ url('files') }}/${file.id}/download" title="Descargar" class="text-gray-400 hover:text-[#2c3856]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </a>
                            </li>
                        `;
                    });
                },
                
                renderActivityChart(breakdown, elementId) {
                    const element = document.getElementById(elementId);
                    if (!element || !breakdown || breakdown.length === 0) {
                        if (element) element.innerHTML = '<p class="text-center text-gray-500 h-64 flex items-center justify-center">No hay datos de actividad.</p>';
                        return;
                    }
                    element.innerHTML = '';
                    new ApexCharts(element, {
                        series: breakdown.map(item => item.total),
                        chart: { type: 'donut', height: 300, fontFamily: this.fontFamily },
                        labels: breakdown.map(item => item.action_type),
                        colors: this.colorPalette,
                        plotOptions: { pie: { donut: { size: '60%', labels: { show: true, total: { show: true, label: 'Total Acciones', color: this.colorPrimary } } } } },
                        legend: { position: 'bottom', fontFamily: this.fontFamily, labels: { colors: '#6c757d' } },
                        dataLabels: { enabled: false },
                        tooltip: { theme: 'dark', y: { formatter: (val) => val + " acciones" } }
                    }).render();
                },

                renderFileTypesChart(fileTypes, elementId) {
                    const element = document.getElementById(elementId);
                    if (!element || !fileTypes || fileTypes.length === 0) {
                        if (element) element.innerHTML = '<p class="text-center text-gray-500 h-64 flex items-center justify-center">No hay archivos para mostrar.</p>';
                        return;
                    }
                    element.innerHTML = '';
                    new ApexCharts(element, {
                        series: fileTypes.map(item => item.total),
                        chart: { type: 'donut', height: 250, fontFamily: this.fontFamily },
                        labels: fileTypes.map(item => item.extension.toUpperCase()),
                        colors: this.colorPalette,
                        plotOptions: { pie: { donut: { size: '60%', labels: { show: true, total: { show: true, label: 'Total Archivos', color: this.colorPrimary } } } } },
                        legend: { position: 'bottom', fontFamily: this.fontFamily, labels: { colors: '#6c757d' } },
                        dataLabels: { enabled: false },
                        tooltip: { theme: 'dark', y: { formatter: (val) => val + " archivos" } }
                    }).render();
                },

                populateBackorders(count, list) {
                    const countContainer = document.getElementById('backorder-count-container');
                    const listContainer = document.getElementById('backorder-list-container');

                    if (countContainer) {
                        countContainer.innerHTML = `<h2 class="text-4xl font-black text-purple-600">${count}</h2>`;
                    }

                    if (listContainer) {
                        listContainer.innerHTML = '';
                        
                        if (!list || list.length === 0) {
                            listContainer.innerHTML = `
                                <div class="text-center py-4 opacity-50">
                                    <svg class="w-8 h-8 mx-auto text-green-500 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-xs font-bold text-gray-500">Todo surtido</p>
                                </div>`;
                            return;
                        }

                        list.forEach(item => {
                            listContainer.innerHTML += `
                                <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-purple-50 transition-colors border border-transparent hover:border-purple-100 group">
                                    <div class="bg-purple-100 text-purple-600 w-8 h-8 flex items-center justify-center rounded-lg font-bold text-xs flex-shrink-0">
                                        ${item.quantity}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-[#2c3856] truncate" title="${item.product}">
                                            ${item.product}
                                        </p>
                                        <p class="text-[10px] text-gray-500 flex justify-between">
                                            <span>#${item.folio} - ${item.client}</span>
                                            <span class="text-purple-400 font-medium">${item.date}</span>
                                        </p>
                                    </div>
                                </div>
                            `;
                        });
                    }
                },

                async init() {
                    try {
                        const response = await fetch('{{ route("area_admin.dashboard.data") }}');
                        if (!response.ok) throw new Error('Error al cargar los datos del dashboard.');
                        
                        const data = await response.json();
                        if (!data) throw new Error('Los datos recibidos están vacíos.');
                        
                        this.isAreaAdmin = data.isAreaAdmin;

                        await this.$nextTick(); 

                        if (data.isAreaAdmin) {
                            this.populateKPIs(data);
                            this.populateRecentActivity(data.recentActivities, 'recent-activity-list');
                            this.populateTeamList(data.teamMembers, 'team-members-list');
                            this.renderActivityChart(data.activityBreakdown, 'activity-breakdown-chart');
                            this.renderFileTypesChart(data.fileTypes, 'file-types-chart');
                            this.populateBackorders(data.backorderCount, data.backorderList);
                        } else {
                            this.populateKPIs(data);
                            this.populateMyProfile(data.myProfile);
                            this.populateRecentFiles(data.recentFiles);
                            this.populateRecentActivity(data.recentActivities, 'recent-activity-list-user');
                            this.populateTeamList(data.teamMembers, 'team-members-list-user');
                            this.renderFileTypesChart(data.fileTypes, 'file-types-chart-user');
                            
                            const userActivityChartEl = document.getElementById('activity-breakdown-chart');
                            if(userActivityChartEl) userActivityChartEl.style.display = 'none';
                        }
                        
                    } catch (error) {
                        console.error(error);
                        const areaTitle = document.getElementById('area-name-title');
                        if (areaTitle) areaTitle.textContent = 'Error';
                    }
                }
            }));
        });
    </script>
</x-app-layout>