<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Estadísticas y Bitácora') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                
                <div class="lg:col-span-2">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Resumen General</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        
                        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.003c0 1.113.285 2.16.786 3.07M15 19.128c.501-.91.786-1.957.786-3.07M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0-1.036.84-1.875 1.875-1.875s1.875.84 1.875 1.875 1.875 1.875 1.875 1.875v.001c.001.001.001.001 0 0h-.002c-.001 0-.001 0 0 0v.002c0 1.035-.84 1.875-1.875 1.875S9.75 10.786 9.75 9.75z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Usuarios</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalUsers }}</dd>
                            </div>
                        </div>

                        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-19.5 0A2.25 2.25 0 004.5 15h15a2.25 2.25 0 002.25-2.25m-19.5 0v.28c0 .16.02.315.05.465a7.5 7.5 0 0014.9 0c.03-.15.05-.305.05-.465V12.75m-15.5 0A2.25 2.25 0 004.5 15h15a2.25 2.25 0 002.25-2.25m-19.5 0v.28c0 .16.02.315.05.465a7.5 7.5 0 0014.9 0c.03-.15.05-.305.05-.465V12.75" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Carpetas</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalFolders }}</dd>
                            </div>
                        </div>

                        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Archivos</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalFiles }}</dd>
                            </div>
                        </div>

                        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Enlaces</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalLinks }}</dd>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Top 3 Usuarios Activos</h3>
                    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                        <ol class="space-y-4">
                            @if(is_iterable($topUsers) && count($topUsers) > 0)
                                @foreach($topUsers as $index => $topUser)
                                    <li class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 rounded-full {{ $index == 0 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }} flex items-center justify-center font-bold text-sm">
                                                {{ $index + 1 }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">
                                                {{ $topUser->user->name ?? 'Usuario Eliminado' }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ $topUser->user->email ?? 'N/A' }}
                                            </p>
                                        </div>
                                        <div class="text-lg font-bold text-gray-800">
                                            {{ $topUser->total_activity }}
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li class="text-gray-500">No hay actividad registrada en este periodo.</li>
                            @endif
                        </ol>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">Bitácora de Actividad</h3>
                        
                        <div class="flex flex-shrink-0 flex-wrap gap-2">
                             <a href="{{ route('admin.statistics.charts', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Ver Gráficos
                            </a>
                            <a href="{{ route('admin.statistics.export-csv', request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Exportar CSV
                            </a>
                            <a href="{{ route('admin.notifications.settings.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Gestionar Notificaciones
                            </a>   
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                        <form id="filter-form" method="GET" action="{{ route('admin.statistics.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div class="md:col-span-2 lg:col-span-2 relative">
                                    <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                                    <input type="text" name="search" id="search" value="{{ $searchQuery }}" placeholder="Buscar por acción, usuario, email..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pl-10">
                                    <div class="absolute inset-y-0 left-0 pl-3 pt-5 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <label for="area-filter" class="block text-sm font-medium text-gray-700">Área</label>
                                    <select id="area-filter" name="area" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Todas</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}" {{ $filterArea == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="user-type-filter" class="block text-sm font-medium text-gray-700">Tipo de Usuario</label>
                                    <select id="user-type-filter" name="user_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Todos</option>
                                        @foreach($userTypes as $key => $label)
                                            <option value="{{ $key }}" {{ $filterUserType == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label for="start_date" class="block text-sm font-medium text-gray-700">Desde</label>
                                        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </div>
                                    <div>
                                        <label for="end_date" class="block text-sm font-medium text-gray-700">Hasta</label>
                                        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-3 mt-4">
                                <a href="{{ route('admin.statistics.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Limpiar</a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Filtrar
                                </button>
                            </div>
                        </form>
                    </div>

                    
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Usuario</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acción</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Detalles</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($activities as $activity)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $activity->created_at->format('d M Y') }}
                                            <span class="block text-xs">{{ $activity->created_at->format('H:i A') }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $activity->user->name ?? 'Usuario Eliminado' }}</div>
                                            <div class="text-xs text-gray-500">{{ $activity->user->area->name ?? 'Sin Área' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{ $activity->action }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            @if($activity->details)
                                                <div class="space-y-1">
                                                @foreach($activity->details as $key => $value)
                                                    <div>
                                                        <span class="font-semibold text-gray-700">{{ Str::title(str_replace('_', ' ', $key)) }}:</span> 
                                                        <span class="text-gray-600">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                                    </div>
                                                @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                            No se encontraron actividades que coincidan con los filtros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-6">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Hemos simplificado el JS.
        // El 'debounce' para la búsqueda en vivo es excelente, así que lo mantenemos.
        // Para los demás filtros, hemos añadido un botón explícito "Filtrar"
        // que es una mejor UX que recargar la página en cada 'change'.
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            
            if (searchInput) {
                searchInput.addEventListener('input', debounce(function() {
                    document.getElementById('filter-form').submit();
                }, 500));
            }

            function debounce(func, timeout = 300) {
                let timer;
                return (...args) => {
                    clearTimeout(timer);
                    timer = setTimeout(() => { func.apply(this, args); }, timeout);
                };
            }
        });
    </script>
</x-app-layout>