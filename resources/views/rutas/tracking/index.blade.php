@extends('layouts.guest-rutas')

@section('content')
    <h2 class="text-2xl font-bold text-center text-[#2c3856] mb-2">Seguimiento de Entregas</h2>
    <p class="text-center text-gray-600 text-sm mb-6">Ingresa uno o más números de factura, separados por comas.</p>

    <form method="GET" action="{{ route('tracking.index') }}">
        <div class="flex gap-2">
            <input id="facturas" class="block w-full rounded-md border-gray-300 shadow-sm" type="text" name="facturas" value="{{ $searchQuery ?? '' }}" required placeholder="Ej: 50046649, 50046650" />
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-[#ff9c00] text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-orange-600">
                Buscar
            </button>
        </div>
    </form>

    {{-- Detalles --}}
    @if($searchQuery)
        <div class="mt-8">
            @forelse ($facturas as $factura)
                <div class="bg-white border rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-start border-b pb-3 mb-3">
                        <div>
                            <p class="text-xs text-gray-500">Factura</p>
                            <p class="font-bold text-lg text-gray-800">{{ $factura->numero_factura }}</p>
                        </div>
                        <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full 
                            @if($factura->estatus_entrega == 'Pendiente') bg-yellow-100 text-yellow-800
                            @elseif($factura->estatus_entrega == 'Entregada') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $factura->estatus_entrega }}
                        </span>
                    </div>

                    <h4 class="text-sm font-bold text-gray-600 mb-2">Historial de Entrega:</h4>
                    @if($factura->eventos->isEmpty())
                        <p class="text-sm text-gray-500">Aún no hay eventos de entrega para esta factura.</p>
                    @else
                        @foreach($factura->eventos as $evento)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center mb-2">
                                {{-- Info del Evento --}}
                                <div class="md:col-span-2">
                                    <p class="text-sm font-semibold {{ $evento->subtipo == 'Factura Entregada' ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $evento->subtipo }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $evento->fecha_evento->format('d/m/Y h:i A') }}</p>
                                    @if(!empty($evento->url_evidencia))
                                        <div class="mt-2">
                                            <p class="text-xs font-semibold text-gray-500">Evidencias:</p>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                @if(!empty($evento->url_evidencia))
                                                    <div class="mt-2">
                                                        <p class="text-xs font-semibold text-gray-500">Evidencias:</p>
                                                        <div class="flex flex-wrap gap-2 mt-1">
                                                            @foreach($evento->url_evidencia as $url)
                                                                <a href="{{ $url }}" target="_blank" class="block border p-1 rounded-md hover:shadow-lg hover:border-blue-500 transition-all duration-300">
                                                                    <img src="{{ $url }}" alt="Evidencia de entrega {{ $loop->iteration }}" class="h-20 w-20 object-cover rounded-sm">
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                {{-- Mapa Estático --}}
<div class="md:col-span-2">
    <a href="https://www.google.com/maps?q={{$evento->latitud}},{{$evento->longitud}}" target="_blank">
        <img 
            src="https://maps.googleapis.com/maps/api/staticmap?center={{$evento->latitud}},{{$evento->longitud}}&zoom=16&size=1200x600&markers=color:red%7C{{$evento->latitud}},{{$evento->longitud}}&key={{ $googleMapsApiKey }}" 
            alt="Mapa de entrega" 
            class="w-full h-auto rounded-md border shadow-lg cursor-pointer hover:opacity-90 transition-opacity">
    </a>
</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @empty
                <p class="text-center mt-6 text-gray-600">No se encontraron facturas con los números proporcionados.</p>
            @endforelse
        </div>
    @endif
@endsection