<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2c3856] leading-tight">
            {{ __('Editar Categoría') }} / <span class="text-gray-500">{{ $ticketCategory->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-[#E8ECF7]">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg border border-gray-200">
                
                <form method="POST" action="{{ route('admin.ticket-categories.update', $ticketCategory) }}" class="p-6 md:p-8">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('Nombre de la Categoría')" class="font-semibold" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $ticketCategory->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-8 border-t pt-6">
                        <a href="{{ route('admin.ticket-categories.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline mr-6">
                            Cancelar
                        </a>
                        <x-primary-button>
                            {{ __('Actualizar Categoría') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>