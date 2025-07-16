<x-app-layout>
    {{-- Se mantienen las fuentes de la guía de estilo desde Google Fonts --}}
    <x-slot name="head">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&family=Raleway:wght@700;800&display=swap" rel="stylesheet">
    </x-slot>

    <x-slot name="header" >
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight" style="font-family: 'Raleway', sans-serif; font-weight: 800;">
            {{ __('Gestión de Organigrama') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100" style="font-family: 'Montserrat', sans-serif;">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-6 sm:p-8">
                
                {{-- Mensajes de éxito y error se mantienen igual --}}
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
                         class="fixed top-5 right-5 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <strong class="font-bold mr-1" style="font-family: 'Raleway', sans-serif;">¡Éxito!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none ml-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
                         class="fixed top-5 right-5 z-50 bg-white border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <strong class="font-bold mr-1" style="font-family: 'Raleway', sans-serif;">¡Error!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none ml-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @endif

                {{-- Barra de Título y Botones de Acción (Ahora más prominentes) --}}
                <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                    <h3 class="text-2xl font-bold text-[#2c3856] w-full text-center sm:w-auto sm:text-left" style="font-family: 'Raleway', sans-serif;">Miembros del Organigrama</h3>
                    <div class="flex flex-wrap justify-center sm:justify-end gap-4 w-full sm:w-auto">
                        {{-- Botón "Interactivo" más grande y prominente --}}
                        <a href="{{ route('admin.organigram.interactive') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#ff9c00] border border-transparent rounded-full font-bold text-sm text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-[#2c3856] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            Organigrama
                        </a>

                        {{-- Menú desplegable para "Acciones" más grande y prominente --}}
                        <div x-data="{ open: false }" class="relative inline-block text-left">
                            <div>
                                <button @click="open = !open" type="button" class="inline-flex items-center justify-center w-full px-5 py-2.5 bg-[#2c3856] border border-transparent rounded-full font-bold text-sm text-white uppercase tracking-widest hover:bg-[#4a5d8c] focus:bg-[#4a5d8c] active:bg-[#1a233a] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-lg" aria-expanded="true" aria-haspopup="true">
                                    Acciones
                                    <svg class="w-5 h-5 ml-2 -mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </div>
                            <div x-show="open" x-transition @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu" aria-orientation="vertical">
                                <div class="py-1" role="none">
                                    <a href="{{ route('admin.organigram.create') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM12 15v-1a4 4 0 00-4-4H6a4 4 0 00-4 4v1h10z"></path></svg>
                                        Añadir Miembro
                                    </a>
                                    <a href="{{ route('admin.organigram.positions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Gestionar Posiciones</a>
                                    <a href="{{ route('admin.organigram.skills.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Gestionar Habilidades</a>
                                    <a href="{{ route('admin.organigram.activities.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Gestionar Actividades</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FORMULARIO DE FILTROS (Minimalista) --}}
                <form action="{{ route('admin.organigram.index') }}" method="GET" class="mb-8 px-4 py-4 md:px-0 md:py-0">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end"> {{-- Cambiado a 4 columnas para el buscador --}}
                        {{-- Nuevo: Filtro por Nombre/Email --}}
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" id="search" name="search" placeholder="Nombre o Email"
                                   class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#ff9c00] focus:border-[#ff9c00] sm:text-sm rounded-md shadow-sm"
                                   value="{{ $searchQuery ?? '' }}"> {{-- Mantener el valor de búsqueda --}}
                        </div>

                        {{-- Filtro por Posición --}}
                        <div>
                            <label for="position_id" class="block text-sm font-medium text-gray-700 mb-1">Posición</label>
                            <select id="position_id" name="position_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#ff9c00] focus:border-[#ff9c00] sm:text-sm rounded-md shadow-sm">
                                <option value="">Todas</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" {{ (string)$position->id === (string)$selectedPosition ? 'selected' : '' }}>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filtro por Jefe Directo --}}
                        <div>
                            <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-1">Jefe Directo</label>
                            <select id="manager_id" name="manager_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#ff9c00] focus:border-[#ff9c00] sm:text-sm rounded-md shadow-sm">
                                <option value="">Todos</option>
                                <option value="null" {{ 'null' === (string)$selectedManager ? 'selected' : '' }}>Sin Jefe</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}" {{ (string)$manager->id === (string)$selectedManager ? 'selected' : '' }}>
                                        {{ $manager->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filtro por Área --}}
                        <div>
                            <label for="area_id" class="block text-sm font-medium text-gray-700 mb-1">Área</label>
                            <select id="area_id" name="area_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#ff9c00] focus:border-[#ff9c00] sm:text-sm rounded-md shadow-sm">
                                <option value="">Todas</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ (string)$area->id === (string)$selectedArea ? 'selected' : '' }}>
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-6 space-x-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#2c3856] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#4a5d8c] focus:bg-[#4a5d8c] active:bg-[#1a233a] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V19l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Aplicar
                        </button>
                        <a href="{{ route('admin.organigram.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                            Limpiar
                        </a>
                    </div>
                </form>

                @if ($members->isEmpty())
                    <p class="text-lg text-[#666666] py-8 text-center">No hay miembros en el organigrama para mostrar con los filtros aplicados.</p>
                @else
                    {{-- TABLA para pantallas medianas y grandes --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#666666] uppercase tracking-wider rounded-tl-lg" style="font-family: 'Montserrat', sans-serif;">Nombre</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#666666] uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">Posición</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#666666] uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">Área</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-[#666666] uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">Jefe Directo</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-[#666666] uppercase tracking-wider rounded-tr-lg" style="font-family: 'Montserrat', sans-serif;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($members as $member)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if ($member->profile_photo_path)
                                                    <img class="h-11 w-11 rounded-full object-cover mr-4" src="{{ Storage::disk('s3')->url($member->profile_photo_path) }}" alt="{{ $member->name }}">
                                                @else
                                                    <svg class="h-11 w-11 rounded-full text-gray-300 bg-gray-100 mr-4" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 0c1.347 0 2.659.182 3.935.503l.366.096A14.977 14.977 0 0124 20.993zM12 11a6 6 0 100-12 6 6 0 000 12zm-2 2h4a1 1 0 110 2h-4a1 1 0 110-2z" />
                                                    </svg>
                                                @endif
                                                <div class="text-base font-bold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">{{ $member->name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2b2b2b]">{{ $member->position->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2b2b2b]">{{ $member->area->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#2b2b2b]">{{ $member->manager->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <a href="{{ route('admin.organigram.edit', $member) }}" class="text-[#2c3856] hover:text-[#ff9c00] font-semibold transition-colors duration-200">Editar</a>
                                            <form action="{{ route('admin.organigram.destroy', $member) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold transition-colors duration-200">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- LISTA DE TARJETAS para pantallas pequeñas --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 md:hidden">
                        @foreach ($members as $member)
                            <div class="bg-white rounded-lg shadow-md p-5 border border-gray-100 flex flex-col">
                                <div class="flex items-center mb-4">
                                    @if ($member->profile_photo_path)
                                        <img class="h-16 w-16 rounded-full object-cover mr-4" src="{{ Storage::disk('s3')->url($member->profile_photo_path) }}" alt="{{ $member->name }}">
                                    @else
                                        <svg class="h-16 w-16 rounded-full text-gray-300 bg-gray-100 mr-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 0c1.347 0 2.659.182 3.935.503l.366.096A14.977 14.977 0 0124 20.993zM12 11a6 6 0 100-12 6 6 0 000 12zm-2 2h4a1 1 0 110 2h-4a1 1 0 110-2z" />
                                        </svg>
                                    @endif
                                    <div class="flex-grow">
                                        <div class="font-bold text-lg text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">{{ $member->name }}</div>
                                        <div class="text-sm text-[#666666]">{{ $member->position->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="space-y-2 text-sm text-[#2b2b2b] flex-grow">
                                    <div><strong>Área:</strong> {{ $member->area->name ?? 'N/A' }}</div>
                                    <div><strong>Jefe:</strong> {{ $member->manager->name ?? 'N/A' }}</div>
                                    <div><strong>Email:</strong> {{ $member->email }}</div>
                                </div>
                                <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-200">
                                    <a href="{{ route('admin.organigram.edit', $member) }}" class="flex-1 text-center px-3 py-1.5 bg-[#2c3856] text-white rounded-full text-xs font-semibold hover:bg-[#4a5d8c] transition">Editar</a>
                                    <form action="{{ route('admin.organigram.destroy', $member) }}" method="POST" class="flex-1" onsubmit="return confirm('¿Seguro?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-center px-3 py-1.5 bg-red-600 text-white rounded-full text-xs font-semibold hover:bg-red-700 transition">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        
        {{-- El modal se mantiene sin cambios, ya que su diseño es adaptable --}}
    </div>
</x-app-layout>