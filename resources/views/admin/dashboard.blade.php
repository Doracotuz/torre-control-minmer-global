<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel de Administración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 px-4 sm:px-0">
                <div>
                    <h3 class="text-3xl md:text-4xl font-bold text-[#2c3856]">
                        Torre de Control de Admin
                    </h3>
                    <p class="mt-1 text-lg text-gray-600">
                        Visión general del sistema (Datos de los últimos 30 días).
                    </p>
                </div>
            </div>

            <div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <a href="{{ route('admin.areas.index') }}" 
                       class="group bg-white rounded-xl shadow-lg p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">
                        
                        <div class="bg-gray-100 p-5 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                            <svg class="w-10 h-10 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                            </svg>
                        </div>
                        
                        <h4 class="mt-5 text-xl font-semibold text-[#2c3856]">
                            Gestionar Áreas
                        </h4>
                        <p class="mt-2 text-sm text-gray-600">
                            Crea, edita y elimina las áreas de la empresa.
                        </p>
                    </a>

                    <a href="{{ route('admin.users.index') }}" 
                       class="group bg-white rounded-xl shadow-lg p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">

                        <div class="bg-gray-100 p-5 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                            <svg class="w-10 h-10 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-4.663M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
                            </svg>
                        </div>

                        <h4 class="mt-5 text-xl font-semibold text-[#2c3856]">
                            Gestionar Usuarios
                        </h4>
                        <p class="mt-2 text-sm text-gray-600">
                            Administra los roles y permisos de los usuarios del sistema.
                        </p>
                    </a>
                    
                    <a href="{{ route('admin.organigram.index') }}" 
                       class="group bg-white rounded-xl shadow-lg p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">

                        <div class="bg-gray-100 p-5 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                            <svg class="w-8 h-8 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75H3a.75.75 0 0 1-.75-.75V5.25A.75.75 0 0 1 3 4.5ZM3 16.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75H3a.75.75 0 0 1-.75-.75V17.25a.75.75 0 0 1 .75-.75ZM19.5 4.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75H19.5a.75.75 0 0 1-.75-.75V5.25a.75.75 0 0 1 .75-.75ZM19.5 16.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75H19.5a.75.75 0 0 1-.75-.75V17.25a.75.75 0 0 1 .75-.75ZM11.25 4.5h1.5a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75V5.25a.75.75 0 0 1 .75-.75ZM8.25 10.5h7.5v.001a4.503 4.503 0 0 1-4.5 4.5h-1.5a4.503 4.503 0 0 1-4.5-4.5v-3.001A.75.75 0 0 1 3.75 6H6" />
                            </svg>
                        </div>

                        <h4 class="mt-5 text-xl font-semibold text-[#2c3856]">
                            Gestionar Organigrama
                        </h4>
                        <p class="mt-2 text-sm text-gray-600">
                            Define la estructura y jerarquía de la organización.
                        </p>
                    </a>

                    <a href="{{ route('asset-management.dashboard') }}" 
                       class="group bg-white rounded-xl shadow-lg p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">
                        
                        <div class="bg-gray-100 p-5 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                            <svg class="w-10 h-10 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.375 1.5-3 3 0 0 1-1.125 1.125m-3-3.633V18a3 3 0 0 0 3 3h3.75m-3.75 0V17.25m3.75 0v3M4.5 12V6.75A3 3 0 0 1 7.5 3.75h9a3 3 0 0 1 3 3V12m-3 0v6.75a3 3 0 0 1-3 3H7.5a3 3 0 0 1-3-3V12m3 0h9m-9 0c0-1.036.84-1.875 1.875-1.875h5.25c1.036 0 1.875.84 1.875 1.875m-9 0c0 1.036.84 1.875 1.875 1.875h5.25c1.036 0 1.875-.84 1.875-1.875m-9 0v-1.875c0-1.036.84-1.875 1.875-1.875h5.25c1.036 0 1.875.84 1.875 1.875v1.875" />
                            </svg>
                        </div>
                        
                        <h4 class="mt-5 text-xl font-semibold text-[#2c3856]">
                            Gestionar Activos
                        </h4>
                        <p class="mt-2 text-sm text-gray-600">
                            Asigna y controla el inventario de equipo (responsivas).
                        </p>
                    </a>
                    
                    <a href="{{ route('admin.ticket-categories.index') }}" 
                       class="group bg-white rounded-xl shadow-lg p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">

                        <div class="bg-gray-100 p-5 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                            <svg class="w-10 h-10 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                            </svg>
                        </div>

                        <h4 class="mt-5 text-xl font-semibold text-[#2c3856]">
                            Categorías de Tickets
                        </h4>
                        <p class="mt-2 text-sm text-gray-600">
                            Define los tipos de tickets para el sistema de soporte.
                        </p>
                    </a>
                    
                    <a href="{{ route('admin.statistics.index') }}" 
                       class="group bg-white rounded-xl shadow-lg p-6 flex flex-col items-center text-center transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">

                        <div class="bg-gray-100 p-5 rounded-full transition-colors duration-300 group-hover:bg-[#ff9c00]">
                            <svg class="w-10 h-10 text-[#2c3856] transition-colors duration-300 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 0 1 9.75 19.875V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </div>

                        <h4 class="mt-5 text-xl font-semibold text-[#2c3856]">
                            Estadísticas y Bitácora
                        </h4>
                        <p class="mt-2 text-sm text-gray-600">
                            Analiza a fondo toda la actividad del sistema.
                        </p>
                    </a>
                </div>
            </div>
            <br>
            <br>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <div class="bg-white rounded-xl shadow-lg p-6 flex items-center space-x-4 transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">
                    <div class="bg-gradient-to-br from-[#2c3856] to-[#4a5f8a] p-4 rounded-full">
                        <svg class="w-8 h-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-3.741-5.196m-1.591 6.358a9.09 9.09 0 0 1-2.08-1.5l-2.081-2.081a9 9 0 0 1-1.5-2.081m-1.591 6.358-2.081-2.081m0 0a9.09 9.09 0 0 1-2.081-1.5m-2.081 2.081a9 9 0 0 1-2.081-1.5m0 0a9.09 9.09 0 0 1-1.5-2.081M3 18.72a9.09 9.09 0 0 1 1.5-2.081m1.5-1.5a9 9 0 0 1 2.081-1.5m2.081 1.5c.877.877 1.763 1.763 2.65 2.65m2.65 2.65a9 9 0 0 0 1.5 2.081M12 6a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Usuarios Totales</p>
                        <p id="totalUsers" class="text-3xl font-bold text-[#2c3856]">...</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 flex items-center space-x-4 transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">
                    <div class="bg-gradient-to-br from-[#2c3856] to-[#4a5f8a] p-4 rounded-full">
                        <svg class="w-8 h-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12.75h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Áreas Totales</p>
                        <p id="totalAreas" class="text-3xl font-bold text-[#2c3856]">...</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 flex items-center space-x-4 transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">
                    <div class="bg-gradient-to-br from-[#2c3856] to-[#4a5f8a] p-4 rounded-full">
                        <svg class="w-8 h-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Carpetas Totales</p>
                        <p id="totalFolders" class="text-3xl font-bold text-[#2c3856]">...</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 flex items-center space-x-4 transition-all duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">
                    <div class="bg-gradient-to-br from-[#2c3856] to-[#4a5f8a] p-4 rounded-full">
                        <svg class="w-8 h-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Archivos y Enlaces</p>
                        <p id="totalFileLinks" class="text-3xl font-bold text-[#2c3856]">...</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Top 10 Acciones en el Sistema</h4>
                    <div id="topActionsChart" style="min-height: 365px;"></div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-xl font-semibold text-[#2c3856]">Actividad Reciente</h4>
                        <a href="{{ route('admin.statistics.index') }}" class="text-sm font-medium text-[#ff9c00] hover:text-[#b87100]">Ver todo</a>
                    </div>
                    <ul id="recent-activity-list" class="space-y-4">
                        <li id="activity-loader" class="flex items-center space-x-3">
                            <div class="bg-gray-200 p-2 rounded-full animate-pulse">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-400 bg-gray-200 rounded animate-pulse">Cargando actividad...</p>
                                <p class="text-xs text-gray-400 bg-gray-200 rounded mt-1 animate-pulse">por favor espere...</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Usuarios por Área</h4>
                    <div id="usersByAreaChart" style="min-height: 350px;"></div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Top 5 Tipos de Archivo</h4>
                    <div id="fileTypesChart" style="min-height: 350px;"></div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Top 5 Usuarios Activos</h4>
                    <div id="topUsersChart" style="min-height: 350px;"></div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Volumen de Carpetas por Área</h4>
                    <div id="foldersByAreaChart" style="min-height: 350px;"></div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Volumen de Archivos por Área</h4>
                    <div id="filesByAreaChart" style="min-height: 350px;"></div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Actividad por Tipo de Usuario</h4>
                    <div id="userTypeChart" style="min-height: 350px;"></div>
                </div>
                
                <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Horas de Mayor Actividad (UTC)</h4>
                    <div id="activityHeatmapChart" style="min-height: 350px;"></div>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 transition-all duration-300 ease-in-out hover:shadow-xl">
                    <h4 class="text-xl font-semibold text-[#2c3856] mb-4">Creación vs. Eliminación</h4>
                    <div id="creationDeletionChart" style="min-height: 350px;"></div>
                </div>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            const colorPrimary = '#2c3856';
            const colorSecondary = '#ff9c00';
            const colorPalette = [colorPrimary, colorSecondary, '#3e4e7a', '#f2b04c', '#5a6f9a', '#f5c07b', '#cdd4e3'];
            const colorGray = '#6c757d';
            const fontFamily = 'Raleway, sans-serif';
            const chartTopActions = document.getElementById('topActionsChart');
            const chartCreationDeletion = document.getElementById('creationDeletionChart');
            const chartFoldersByArea = document.getElementById('foldersByAreaChart');
            const chartFilesByArea = document.getElementById('filesByAreaChart');
            const chartUserType = document.getElementById('userTypeChart');
            const chartActivityHeatmap = document.getElementById('activityHeatmapChart');
            const chartUsersByArea = document.getElementById('usersByAreaChart');
            const chartFileTypes = document.getElementById('fileTypesChart');
            const chartTopUsers = document.getElementById('topUsersChart');
            const activityList = document.getElementById('recent-activity-list');
            const activityLoader = document.getElementById('activity-loader');

            function timeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);
                
                let interval = seconds / 31536000;
                if (interval > 1) return `hace ${Math.floor(interval)} años`;
                interval = seconds / 2592000;
                if (interval > 1) return `hace ${Math.floor(interval)} meses`;
                interval = seconds / 86400;
                if (interval > 1) return `hace ${Math.floor(interval)} días`;
                interval = seconds / 3600;
                if (interval > 1) return `hace ${Math.floor(interval)} horas`;
                interval = seconds / 60;
                if (interval > 1) return `hace ${Math.floor(interval)} minutos`;
                return `hace ${Math.floor(seconds)} segundos`;
            }

            function getActivityIcon(action) {
                action = action.toLowerCase();
                if (action.includes('subió') || action.includes('creó')) {
                    return { icon: `<svg class="w-5 h-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>`, color: 'text-green-500' };
                }
                if (action.includes('descargó')) {
                    return { icon: `<svg class="w-5 h-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>`, color: 'text-blue-500' };
                }
                if (action.includes('eliminó') || action.includes('eliminación')) {
                    return { icon: `<svg class="w-5 h-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12.54 0c-.27.041-.54.082-.811.124m-1.022-.165L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12.54 0c-.27.041-.54.082-.811.124" /></svg>`, color: 'text-red-500' };
                }
                return { icon: `<svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>`, color: 'text-gray-500' };
            }

            function populateKPIs(data) {
                document.getElementById('totalUsers').textContent = data.totalUsers || '0';
                document.getElementById('totalAreas').textContent = data.totalAreas || '0';
                document.getElementById('totalFolders').textContent = data.totalFolders || '0';
                document.getElementById('totalFileLinks').textContent = data.totalFileLinks || '0';
            }
            
            function populateRecentActivity(activities) {
                if (!activityList || !activities || activities.length === 0) {
                    activityLoader.innerHTML = '<p class="text-sm text-gray-500">No hay actividad reciente.</p>';
                    return;
                }
                activityList.innerHTML = '';
                activities.forEach(activity => {
                    const { icon, color } = getActivityIcon(activity.action);
                    const userName = activity.user ? activity.user.name : 'Usuario Desconocido';
                    const relativeTime = timeAgo(activity.created_at);
                    const listItem = document.createElement('li');
                    listItem.className = 'flex items-center space-x-3';
                    listItem.innerHTML = `
                        <div class="bg-gray-100 p-2 rounded-full">
                            ${icon}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">${activity.action}</p>
                            <p class="text-xs text-gray-500">por ${userName} (${relativeTime})</p>
                        </div>
                    `;
                    activityList.appendChild(listItem);
                });
            }

            function renderCharts(data) {
                
                const defaultOptions = {
                    chart: { fontFamily: fontFamily, toolbar: { show: false } },
                    colors: colorPalette,
                    tooltip: { theme: 'dark' },
                    legend: { position: 'bottom', fontFamily: fontFamily, labels: { colors: colorGray } }
                };

                if (chartTopActions && data.actionData && data.actionData.length > 0) {
                    new ApexCharts(chartTopActions, {
                        ...defaultOptions,
                        series: [{ name: 'Acciones', data: data.actionData.map(item => item.total) }],
                        chart: { ...defaultOptions.chart, type: 'bar', height: 350 },
                        plotOptions: { bar: { horizontal: true, borderRadius: 4, dataLabels: { position: 'top' } } },
                        colors: [colorPrimary],
                        dataLabels: { enabled: true, offsetX: 40, style: { fontSize: '12px', colors: ['#333'] } },
                        xaxis: { categories: data.actionData.map(item => item.action) },
                        yaxis: { labels: { style: { fontSize: '12px' } } },
                        tooltip: { y: { formatter: (val) => val + " veces" } }
                    }).render();
                } else if (chartTopActions) {
                    chartTopActions.innerHTML = '<p class="text-center text-gray-500 h-full flex items-center justify-center">No hay datos de acciones.</p>';
                }

                if (chartCreationDeletion && data.creationDeletion && Object.keys(data.creationDeletion).length > 0) {
                    new ApexCharts(chartCreationDeletion, {
                        ...defaultOptions,
                        series: Object.values(data.creationDeletion),
                        chart: { ...defaultOptions.chart, type: 'donut', height: 350 },
                        labels: Object.keys(data.creationDeletion),
                        colors: ['#28a745', '#dc3545'],
                        plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', color: colorPrimary } } } } },
                    }).render();
                } else if (chartCreationDeletion) {
                    chartCreationDeletion.innerHTML = '<p class="text-center text-gray-500 h-full flex items-center justify-center">No hay datos de C/D.</p>';
                }
                
                if (chartFoldersByArea && data.foldersByArea && data.foldersByArea.length > 0) {
                    new ApexCharts(chartFoldersByArea, {
                        ...defaultOptions,
                        series: data.foldersByArea.map(item => item.total_folders),
                        chart: { ...defaultOptions.chart, type: 'donut', height: 350 },
                        labels: data.foldersByArea.map(item => item.name),
                        plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total Carpetas', color: colorPrimary } } } } },
                    }).render();
                } else if (chartFoldersByArea) {
                     chartFoldersByArea.innerHTML = '<p class="text-center text-gray-500 h-full flex items-center justify-center">No hay datos de carpetas.</p>';
                }

                if (chartFilesByArea && data.filesByArea && data.filesByArea.length > 0) {
                    new ApexCharts(chartFilesByArea, {
                        ...defaultOptions,
                        series: data.filesByArea.map(item => item.total_files),
                        chart: { ...defaultOptions.chart, type: 'donut', height: 350 },
                        labels: data.filesByArea.map(item => item.name),
                        plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total Archivos', color: colorPrimary } } } } },
                    }).render();
                } else if (chartFilesByArea) {
                    chartFilesByArea.innerHTML = '<p class="text-center text-gray-500 h-full flex items-center justify-center">No hay datos de archivos.</p>';
                }
                
                if (chartUserType && data.userTypeData && data.userTypeData.length > 0) {
                    new ApexCharts(chartUserType, {
                        ...defaultOptions,
                        series: data.userTypeData.map(item => item.total_actions),
                        chart: { ...defaultOptions.chart, type: 'polarArea', height: 350 },
                        labels: data.userTypeData.map(item => item.user_type_label),
                        stroke: { colors: ['#fff'] },
                        fill: { opacity: 0.9 },
                    }).render();
                } else if (chartUserType) {
                    chartUserType.innerHTML = '<p class="text-center text-gray-500 h-full flex items-center justify-center">No hay datos de usuarios.</p>';
                }

                if (chartActivityHeatmap && data.activityByHour && Object.keys(data.activityByHour).length > 0) {
                    const heatmapData = new Array(24).fill(0).map((_, i) => {
                        return { x: `${i.toString().padStart(2, '0')}:00`, y: data.activityByHour[i] || 0 };
                    });
                    new ApexCharts(chartActivityHeatmap, {
                        ...defaultOptions,
                        series: [{ name: 'Actividad', data: heatmapData }],
                        chart: { ...defaultOptions.chart, type: 'heatmap', height: 350 },
                        plotOptions: {
                            heatmap: {
                                shadeIntensity: 0.5, radius: 0, useFillColorAsStroke: true,
                                colorScale: {
                                    ranges: [
                                        { from: 0, to: 0, name: 'Baja', color: '#cdd4e3' },
                                        { from: 1, to: 50, name: 'Media', color: '#5a6f9a' },
                                        { from: 51, to: 100, name: 'Alta', color: colorPrimary },
                                        { from: 101, to: 500, name: 'Muy Alta', color: colorSecondary }
                                    ]
                                }
                            }
                        },
                        dataLabels: { enabled: true, style: { colors: ['#333'] } },
                        xaxis: { type: 'category' },
                        title: { text: 'Actividad por Hora del Día (UTC)', style: { color: colorPrimary } },
                    }).render();
                } else if (chartActivityHeatmap) {
                     chartActivityHeatmap.innerHTML = '<p class="text-center text-gray-500 h-full flex items-center justify-center">No hay datos de actividad.</p>';
                }

                if (chartUsersByArea && data.usersByArea && data.usersByArea.length > 0) {
                    new ApexCharts(chartUsersByArea, {
                        ...defaultOptions,
                        series: [{ name: 'Usuarios', data: data.usersByArea.map(item => item.total_users) }],
                        chart: { ...defaultOptions.chart, type: 'bar', height: 350 },
                        plotOptions: { bar: { columnWidth: '50%', borderRadius: 6 } },
                        dataLabels: { enabled: false },
                        xaxis: { categories: data.usersByArea.map(item => item.name) },
                        yaxis: { title: { text: 'Número de Usuarios' } },
                        tooltip: { y: { formatter: (val) => val + " usuarios" } },
                    }).render();
                } else if (chartUsersByArea) {
                     chartUsersByArea.innerHTML = '<p class="text-center text-gray-500 h-full flex items-center justify-center">No hay datos de usuarios.</p>';
                }
                
                if (chartFileTypes && data.fileTypes && data.fileTypes.length > 0) {
                    new ApexCharts(chartFileTypes, {
                        ...defaultOptions,
                        series: data.fileTypes.map(item => item.total),
                        chart: { ...defaultOptions.chart, type: 'donut', height: 350 },
                        labels: data.fileTypes.map(item => item.file_extension.toUpperCase()),
                        plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Total', color: colorPrimary } } } } },
                    }).render();
                } else if (chartFileTypes) {
                    chartFileTypes.innerHTML = '<p class="text-center text-gray-500 h-full flex items-center justify-center">No hay datos de archivos.</p>';
                }
                
                if (chartTopUsers && data.topUsers && data.topUsers.length > 0) {
                    new ApexCharts(chartTopUsers, {
                        ...defaultOptions,
                        series: [{ name: 'Acciones', data: data.topUsers.map(item => item.total_actions) }],
                        chart: { ...defaultOptions.chart, type: 'bar', height: 350 },
                        plotOptions: { bar: { horizontal: true, borderRadius: 4, dataLabels: { position: 'top' } } },
                        colors: [colorSecondary],
                        dataLabels: { enabled: true, offsetX: 40, style: { fontSize: '12px', colors: ['#333'] } },
                        xaxis: { categories: data.topUsers.map(item => item.name) },
                        tooltip: { y: { formatter: (val) => val + " acciones" } },
                    }).render();
                } else if (chartTopUsers) {
                    chartTopUsers.innerHTML = '<p class="text-center text-gray-500 h-full flex items-center justify-center">No hay usuarios activos.</p>';
                }
            }


            fetch('{{ route("admin.dashboard.data") }}')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('La respuesta de la red no fue exitosa. Status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        populateKPIs(data);
                        populateRecentActivity(data.recentActivities);
                        renderCharts(data);
                    } else {
                        throw new Error('Los datos recibidos están vacíos.');
                    }
                })
                .catch(error => {
                    console.error('Error al cargar los datos del dashboard de admin:', error);
                    if (activityLoader) {
                        activityList.innerHTML = `<li class="text-sm text-red-500">Error al cargar la actividad.</li>`;
                    }
                    if (document.getElementById('totalUsers')) {
                        document.getElementById('totalUsers').textContent = 'Error';
                        document.getElementById('totalAreas').textContent = 'Error';
                        document.getElementById('totalFolders').textContent = 'Error';
                        document.getElementById('totalFileLinks').textContent = 'Error';
                    }
                });
        });
    </script>

</x-app-layout>