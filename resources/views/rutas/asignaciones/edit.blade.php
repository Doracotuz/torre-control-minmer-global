<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Guía: {{ $guia->guia }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                        <p class="font-bold">Error</p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('rutas.asignaciones.update', $guia->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Campo para editar el Número de Guía --}}
                        <div>
                            <label for="guia" class="block font-medium text-sm text-gray-700">Número de Guía</label>
                            <input type="text" name="guia" id="guia" value="{{ old('guia', $guia->guia) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>                  

                        {{-- Otros campos que desees que sean editables --}}
                        <div>
                            <label for="origen" class="block font-medium text-sm text-gray-700">Origen</label>
                            <input type="text" name="origen" id="origen" value="{{ old('origen', $guia->origen) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ url()->previous(route('customer-service.planning.index')) }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-blue-700">
                            Actualizar Guía
                        </button>
                    </div>
                </form>

                <div class="mt-8 border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-700">Órdenes en esta Guía</h3>
                    <ul class="mt-2 list-disc list-inside text-sm text-gray-600">
                        @forelse($guia->plannings as $planning)
                            <li>SO: {{ $planning->order->so_number ?? 'N/A' }} | Factura: {{ $planning->factura ?? 'N/A' }}</li>
                        @empty
                            <li>No hay órdenes asociadas.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>