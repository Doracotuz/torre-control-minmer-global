<x-app-layout>
    <x-slot name="header" >
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Organigrama') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-100">
        {{-- CAMBIO AQUÍ: Usamos max-w-full para que ocupe el 100% del ancho disponible --}}
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-8">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300 transform scale-90 opacity-0" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200 transform scale-100 opacity-100" x-transition:leave-end="opacity-0 scale-90"
                         class="fixed top-4 right-4 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <strong class="font-bold mr-1">{{ __('¡Éxito!') }}</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300 transform scale-90 opacity-0" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200 transform scale-100 opacity-100" x-transition:leave-end="opacity-0 scale-90"
                         class="fixed top-4 right-4 z-50 bg-white border-l-4 border-red-600 text-red-700 px-6 py-4 rounded-lg shadow-xl flex items-center justify-between min-w-[300px]" role="alert">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <strong class="font-bold mr-1">{{ __('¡Error!') }}</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
                @endif

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-[#2c3856]" style="font-family: 'Raleway', sans-serif;">{{ __('Miembros del Organigrama') }}</h3>
                    <div>
                        <a href="{{ route('admin.organigram.activities.index') }}" class="inline-flex items-center px-5 py-2 bg-gray-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md mr-3">
                            {{ __('Gestionar Actividades') }}
                        </a>
                        <a href="{{ route('admin.organigram.skills.index') }}" class="inline-flex items-center px-5 py-2 bg-gray-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md mr-3">
                            {{ __('Gestionar Habilidades') }}
                        </a>
                        <a href="{{ route('admin.organigram.create') }}" class="inline-flex items-center px-5 py-2 bg-[#2c3856] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#4a5d8c] focus:bg-[#4a5d8c] active:bg-[#1a233a] focus:outline-none focus:ring-2 focus:ring-[#ff9c00] focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM12 15v-1a4 4 0 00-4-4H6a4 4 0 00-4 4v1h10z"></path></svg>
                            {{ __('Añadir Miembro') }}
                        </a>
                    </div>
                </div>

                @if ($members->isEmpty())
                    <p class="text-lg text-gray-600 py-8 text-center" style="font-family: 'Montserrat', sans-serif;">No hay miembros en el organigrama para mostrar.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">
                                        {{ __('Nombre') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Posición') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Área') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Jefe Directo') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Contacto') }}
                                    </th>
                                    <th scope="col" class="relative px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">
                                        {{ __('Acciones') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($members as $member)
                                    <tr class="hover:bg-gray-100 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if ($member->profile_photo_path)
                                                    <img class="h-10 w-10 rounded-full object-cover mr-3" src="{{ asset('storage/' . $member->profile_photo_path) }}" alt="{{ $member->name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 mr-3">
                                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                                    </div>
                                                @endif
                                                <div class="text-lg font-medium text-[#2c3856]">{{ $member->name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $member->position }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $member->area->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $member->manager->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $member->email }}<br>
                                            {{ $member->cell_phone }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <a href="{{ route('admin.organigram.edit', $member) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200">Editar</a>
                                            <form action="{{ route('admin.organigram.destroy', $member) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a este miembro del organigrama? Esto también eliminará a sus subordinados.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 transition-colors duration-200">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div x-data="{ showPropertiesModal: false, propertiesData: {} }"
             x-show="showPropertiesModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50"
             style="display: none;"
             @click.away="showPropertiesModal = false"
             @keydown.escape.window="showPropertiesModal = false"
        >
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col" @click.stop="">
                <div class="flex justify-between items-center pb-4 border-b border-gray-200 mb-4">
                    <h3 class="text-xl font-semibold text-[#2c3856]" x-text="propertiesData.name + ' - Detalles'"></h3>
                    <button @click="showPropertiesModal = false" class="text-gray-500 hover:text-gray-700 transition-colors duration-200 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto text-gray-700 text-base grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="mb-3 flex items-center">
                            <img x-show="propertiesData.profile_photo_path" :src="'{{ asset('storage') }}/' + propertiesData.profile_photo_path" class="h-24 w-24 rounded-full object-cover border-4 border-gray-200 shadow-md mr-4">
                            <div x-show="!propertiesData.profile_photo_path" class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border-4 border-gray-300 shadow-md mr-4">
                                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                            </div>
                            <div>
                                <span class="font-semibold text-[#2c3856]">Nombre:</span> <span x-text="propertiesData.name"></span><br>
                                <span class="font-semibold text-[#2c3856]">Posición:</span> <span x-text="propertiesData.position"></span><br>
                                <span class="font-semibold text-[#2c3856]">Área:</span> <span x-text="propertiesData.area_name"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <span class="font-semibold text-[#2c3856]">Email:</span> <span x-text="propertiesData.email"></span>
                        </div>
                        <div class="mb-3">
                            <span class="font-semibold text-[#2c3856]">Celular:</span> <span x-text="propertiesData.cell_phone"></span>
                        </div>
                        <div class="mb-3">
                            <span class="font-semibold text-[#2c3856]">Jefe Directo:</span> <span x-text="propertiesData.manager_name || 'N/A'"></span>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4">
                            <span class="font-semibold text-[#2c3856]">Actividades:</span>
                            <ul class="list-disc list-inside text-sm">
                                <template x-for="activity in propertiesData.activities" :key="activity.id">
                                    <li x-text="activity.name"></li>
                                </template>
                                <template x-if="propertiesData.activities && propertiesData.activities.length === 0">
                                    <li>N/A</li>
                                </template>
                            </ul>
                        </div>
                        <div class="mb-4">
                            <span class="font-semibold text-[#2c3856]">Habilidades:</span>
                            <ul class="list-disc list-inside text-sm">
                                <template x-for="skill in propertiesData.skills" :key="skill.id">
                                    <li x-text="skill.name"></li>
                                </template>
                                <template x-if="propertiesData.skills && propertiesData.skills.length === 0">
                                    <li>N/A</li>
                                </template>
                            </ul>
                        </div>
                        <div class="mb-4">
                            <span class="font-semibold text-[#2c3856]">Trayectoria:</span>
                            <ul class="list-disc list-inside text-sm">
                                <template x-for="trajectory in propertiesData.trajectories" :key="trajectory.id">
                                    <li>
                                        <span x-text="trajectory.title"></span>
                                        (<span x-text="trajectory.start_date"></span> - <span x-text="trajectory.end_date || 'Actual'"></span>)
                                        <p x-show="trajectory.description" class="text-xs text-gray-500 pl-4" x-text="trajectory.description"></p>
                                    </li>
                                </template>
                                <template x-if="propertiesData.trajectories && propertiesData.trajectories.length === 0">
                                    <li>N/A</li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button @click="showPropertiesModal = false"
                       class="inline-flex items-center px-5 py-2 bg-gray-200 border border-transparent rounded-full font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-300 transform hover:scale-105 shadow-md">
                        {{ __('Cerrar') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>