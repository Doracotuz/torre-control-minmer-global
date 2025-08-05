<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Gestionar Categorías de Tickets') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#E8ECF7]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200 p-6 md:p-8">
                
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="fixed top-5 right-5 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center">
                        <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div><strong class="font-bold">{{ __('¡Éxito!') }}</strong><span class="block sm:inline ml-1">{{ session('success') }}</span></div>
                    </div>
                @endif
                
                <div class="flex justify-end items-center mb-6">
                    <a href="{{ route('admin.ticket-categories.create') }}" class="inline-flex items-center px-5 py-2 bg-[#2c3856] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#ff9c00] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] transition-all duration-300 transform hover:scale-105 shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Crear Categoría
                    </a>
                </div>

                <div class="overflow-x-auto bg-white rounded-lg shadow border">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#2c3856]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider">Tickets Asociados</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($categories as $category)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $category->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $category->tickets_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-4">
                                            <a href="{{ route('admin.ticket-categories.edit', $category) }}" class="text-[#2c3856] hover:text-[#ff9c00] font-semibold">Editar</a>
                                            <form action="{{ route('admin.ticket-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('¿Estás seguro? Los tickets asociados no se eliminarán, pero quedarán sin categoría.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-6 py-12 text-center text-gray-500">No hay categorías creadas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>