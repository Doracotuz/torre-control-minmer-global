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
    @if($searchQuery)
        <div class="mt-8">
            @forelse ($facturas as $factura)

                <div class="bg-white border rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-start border-b pb-3 mb-3">
                        <div>
                            <p class="text-xs text-gray-500">Factura</p>
                            <p class="font-bold text-lg text-gray-800">{{ $factura->numero_factura }}</p>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            @php
                                $order = $factura->csPlanning?->order;
                                $editor = $order?->updater;
                                $clientContact = $order?->client_contact ?? $order?->customer_name;
                                $editorPhoneNumber = $editor?->phone_number;
                            @endphp

                            @if($editorPhoneNumber)
                                @php
                                    // Lógica para el saludo dinámico
                                    $hour = now()->hour;
                                    if ($hour >= 5 && $hour < 12) {
                                        $greeting = 'buen día';
                                    } elseif ($hour >= 12 && $hour < 20) {
                                        $greeting = 'buena tarde';
                                    } else {
                                        $greeting = 'buena noche';
                                    }

                                    $editorFirstName = explode(' ', $editor->name)[0];

                                    $message = "Hola, {$greeting} {$editorFirstName}.\n\n";
                                    $message .= "Me gustaría obtener más detalles sobre la entrega de la factura *{$factura->numero_factura}* con destino a *{$clientContact}*.";
                                    $whatsAppNumber = "521" . $editorPhoneNumber;
                                    $whatsAppLink = "https://wa.me/{$whatsAppNumber}?text=" . urlencode($message);
                                @endphp
                            @endif
                            <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($factura->estatus_entrega == 'Pendiente') bg-yellow-100 text-yellow-800
                                @elseif($factura->estatus_entrega == 'Entregada') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $factura->estatus_entrega }}
                            </span>
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-gray-600 mb-2">Historial de Entrega:</h4>
                    @if($factura->eventos->isEmpty())
                        <p class="text-sm text-gray-500">Aún no hay eventos de entrega para esta factura.</p>
                        <div class="mt-4">
                            <a href="{{ $whatsAppLink }}" target="_blank" class="inline-flex items-center px-3 py-1.2 bg-[rgb(44,56,86)] text-white rounded-full font-semibold text-xs uppercase tracking-widest hover:bg-[#ff9c00] transition-all duration-300 transform hover:scale-105 shadow-md" title="Enviar WhatsApp a {{ $creator->name }}">
                                <!-- <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.894 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.886-.001 2.269.655 4.506 1.908 6.385l-1.292 4.72zm7.572-6.911c-.533-.266-3.143-1.552-3.64-1.725-.496-.173-.855-.265-1.214.265-.36.53-.137 1.251-.138 1.251-.36.53-.137 1.251-.138 1.251l-.149.174c-.379.444-.799.524-1.214.379-.414-.143-1.745-.636-3.328-2.049a11.583 11.583 0 0 1-2.3-2.828c-.266-.495-.034-.764.231-1.021.233-.232.496-.615.744-.913.249-.298.33-.495.496-.855.165-.36.083-.66-.034-.912-.117-.252-1.213-2.909-1.662-3.996-.448-1.087-.905-1.008-1.213-1.008h-.494c-.359 0-.912.117-1.385.579-.47.462-1.798 1.76-1.798 4.298s1.839 4.99 2.083 5.349c.243.359 3.593 5.493 8.718 7.669 1.144.495 2.062.793 2.76.995.894.243 1.706.215 2.333-.034.707-.282 2.196-1.12 2.502-2.208.307-1.087.307-2.008.216-2.208-.092-.2-.358-.321-.737-.533z"/></svg> -->
                                <span>Solicitar informes a CS</span>
                            </a>
                        </div>
                    @else
                        @foreach($factura->eventos as $evento)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center mb-2">
                                <div class="md:col-span-2">
                                    <p class="text-sm font-semibold {{ $evento->subtipo == 'Factura Entregada' ? 'text-green-700' : 'text-red-700' }}">
                                        {{ $evento->subtipo }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $evento->fecha_evento->format('d/m/Y h:i A') }}</p>
                                    @if(!empty($evento->url_evidencia))
                                        <div class="mt-2">
                                            <!-- <p class="text-xs font-semibold text-gray-500">Evidencias:</p> -->
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
                                <div class="md:col-span-2">
                                    <a href="https://www.google.com/maps?q={{$evento->latitud}},{{$evento->longitud}}" target="_blank">
                                        <img 
                                            src="https://maps.googleapis.com/maps/api/staticmap?center={{$evento->latitud}},{{$evento->longitud}}&zoom=16&size=1200x600&markers=color:red%7C{{$evento->latitud}},{{$evento->longitud}}&key={{ $googleMapsApiKey }}" 
                                            alt="Mapa de entrega" 
                                            class="w-full h-auto rounded-md border shadow-lg cursor-pointer hover:opacity-90 transition-opacity">
                                    </a>
                                </div>
                            </div>
                            <a href="{{ $whatsAppLink }}" target="_blank" class="inline-flex items-center px-3 py-1.2 bg-[rgb(44,56,86)] text-white rounded-full font-semibold text-xs uppercase tracking-widest hover:bg-[#ff9c00] transition-all duration-300 transform hover:scale-105 shadow-md" title="Enviar WhatsApp a {{ $creator->name }}">
                                <!-- <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.894 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.886-.001 2.269.655 4.506 1.908 6.385l-1.292 4.72zm7.572-6.911c-.533-.266-3.143-1.552-3.64-1.725-.496-.173-.855-.265-1.214.265-.36.53-.137 1.251-.138 1.251-.36.53-.137 1.251-.138 1.251l-.149.174c-.379.444-.799.524-1.214.379-.414-.143-1.745-.636-3.328-2.049a11.583 11.583 0 0 1-2.3-2.828c-.266-.495-.034-.764.231-1.021.233-.232.496-.615.744-.913.249-.298.33-.495.496-.855.165-.36.083-.66-.034-.912-.117-.252-1.213-2.909-1.662-3.996-.448-1.087-.905-1.008-1.213-1.008h-.494c-.359 0-.912.117-1.385.579-.47.462-1.798 1.76-1.798 4.298s1.839 4.99 2.083 5.349c.243.359 3.593 5.493 8.718 7.669 1.144.495 2.062.793 2.76.995.894.243 1.706.215 2.333-.034.707-.282 2.196-1.12 2.502-2.208.307-1.087.307-2.008.216-2.208-.092-.2-.358-.321-.737-.533z"/></svg> -->
                                <span>Solicitar informes a CS</span>
                            </a>
                        @endforeach
                    @endif
                </div>
            @empty
                <p class="text-center mt-6 text-gray-600">No se encontraron facturas con los números proporcionados.</p>
            @endforelse
        </div>
    @endif
@endsection