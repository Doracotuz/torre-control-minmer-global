<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Gestionar Categorías y Subcategorías de Tickets') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border p-6 md:p-8">
                
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition class="fixed top-5 right-5 z-50 bg-white border-l-4 border-[#ff9c00] text-[#2c3856] px-6 py-4 rounded-lg shadow-xl flex items-center">
                        <svg class="w-6 h-6 mr-3 text-[#ff9c00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div><strong class="font-bold">{{ __('¡Éxito!') }}</strong><span class="block sm:inline ml-1">{{ session('success') }}</span></div>
                    </div>
                @endif
                
                <div class="flex justify-end items-center mb-6">
                    <a href="{{ route('admin.ticket-categories.create') }}" class="inline-flex items-center px-5 py-2 bg-[#2c3856] border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#ff9c00] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] transition-all duration-300 transform hover:scale-105 shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Crear Categoría Principal
                    </a>
                </div>

                <div class="space-y-6" x-data="{ openCategoryId: null, editingSubCategoryId: null }">
                    @forelse ($categories as $category)
                        <div class="bg-gray-50 rounded-lg p-4 border">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <button @click="openCategoryId = (openCategoryId === {{ $category->id }} ? null : {{ $category->id }})" class="mr-3 text-gray-400 hover:text-gray-800">
                                        <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-90': openCategoryId === {{ $category->id }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </button>
                                    <h3 class="text-lg font-bold text-gray-800">{{ $category->name }}</h3>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <a href="{{ route('admin.ticket-categories.edit', $category) }}" class="text-[#2c3856] hover:text-[#ff9c00] font-semibold text-sm">Editar</a>
                                    <form action="{{ route('admin.ticket-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar esta categoría y TODAS sus subcategorías?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm">Eliminar</button>
                                    </form>
                                </div>
                            </div>


                            <div x-show="openCategoryId === {{ $category->id }}" x-transition class="pl-8 pt-4 mt-2 border-t">
                                <ul class="space-y-2">
                                    @foreach ($category->subCategories as $subCategory)
                                        <li class="flex items-center justify-between p-2 rounded hover:bg-gray-100">
                                            <div class="flex items-center">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-700 mr-3">{{ $subCategory->tickets_count }} tickets</span>
                                                <p x-show="editingSubCategoryId !== {{ $subCategory->id }}" class="text-gray-700">{{ $subCategory->name }}</p>

                                                <form x-show="editingSubCategoryId === {{ $subCategory->id }}" action="{{ route('admin.ticket-sub-categories.update', $subCategory) }}" method="POST" class="flex items-center">
                                                    @csrf @method('PUT')
                                                    <input type="text" name="name" value="{{ $subCategory->name }}" class="border-gray-300 rounded-md shadow-sm text-sm py-1">
                                                    <button type="submit" class="ml-2 text-green-600 hover:text-green-800">Guardar</button>
                                                    <button type="button" @click="editingSubCategoryId = null" class="ml-2 text-gray-500 hover:text-gray-700">Cancelar</button>
                                                </form>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <button @click="editingSubCategoryId = {{ $subCategory->id }}" class="text-blue-600 hover:text-blue-800 text-sm">Editar</button>
                                                <form action="{{ route('admin.ticket-sub-categories.destroy', $subCategory) }}" method="POST" onsubmit="return confirm('¿Eliminar esta subcategoría?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Eliminar</button>
                                                </form>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>

                                <form action="{{ route('admin.ticket-sub-categories.store') }}" method="POST" class="mt-4 pt-3 border-t flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="ticket_category_id" value="{{ $category->id }}">
                                    <input type="text" name="name" placeholder="Nueva Subcategoría" class="flex-grow border-gray-300 rounded-md shadow-sm text-sm py-1">
                                    <button type="submit" class="px-3 py-1 bg-[#2c3856] text-white text-xs font-semibold rounded-md hover:bg-[#ff9c00]">Añadir</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500">No hay categorías creadas.</p>
                    @endforelse
                </div>
                
                {{-- @if($categories->hasPages())
                    <div class="mt-6">
                        {{ $categories->links() }}
                    </div>
                @endif --}}
            </div>
        </div>
    </div>
</x-app-layout>
