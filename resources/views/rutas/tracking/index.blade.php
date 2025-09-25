@extends('layouts.guest-rutas')

@section('content')
    <h2 class="text-2xl font-bold text-center text-[#2c3856] mb-2">Seguimiento de Entregas</h2>
    <p class="text-center text-gray-600 text-sm mb-6">Ingresa uno o más números de factura, separados por comas.</p>

    <form method="GET" action="{{ route('tracking.index') }}">
        <div class="flex gap-2">
            <input id="facturas" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#ff9c00] focus:ring-[#ff9c00]" type="text" name="facturas" value="{{ $searchQuery ?? '' }}" required placeholder="Ej: 50046649, 50046650" />
            <button type="submit" class="inline-flex items-center px-6 py-2 bg-[#ff9c00] text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ff9c00] transition">
                Buscar
            </button>
        </div>
    </form>

    @if($searchQuery)
        <div class="mt-8">

            @if(!empty($notFoundNumbers))
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg shadow">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.257 3.099c.636-1.026 2.073-1.026 2.709 0l5.428 8.752c.621 1.002-.12 2.273-1.355 2.273H4.184c-1.235 0-1.976-1.271-1.355-2.273l5.428-8.752zM10 12a1 1 0 110-2 1 1 0 010 2zm-1-4a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                No se encontraron registros para la(s) siguiente(s) factura(s):
                                <span class="font-bold">{{ implode(', ', $notFoundNumbers) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @forelse ($results as $item)
                @if($item->source === 'factura')
                    @php $factura = $item; // Renombramos para compatibilidad @endphp
                    <div class="bg-white border rounded-lg p-4 mb-4 shadow-md">
                        <div class="flex flex-col sm:flex-row justify-between items-start border-b pb-3 mb-3 gap-2">
                            <div>
                                <p class="text-xs text-gray-500">Factura en Ruta</p>
                                <p class="font-bold text-lg text-gray-800">{{ $factura->numero_factura }}</p>
                            </div>
                            <div class="flex items-center space-x-3 flex-shrink-0">
                                @php
                                    $order = $factura->csPlanning?->order;
                                    $editor = $order?->updater;
                                    $clientContact = $order?->client_contact ?? $order?->customer_name;
                                    $editorPhoneNumber = $editor?->phone_number;
                                @endphp

                                @if($editorPhoneNumber)
                                    @php
                                        $hour = now()->hour;
                                        $greeting = ($hour >= 5 && $hour < 12) ? 'buen día' : (($hour >= 12 && $hour < 20) ? 'buena tarde' : 'buena noche');
                                        $editorFirstName = explode(' ', $editor->name)[0];
                                        $message = "Hola, {$greeting} {$editorFirstName}.\n\nMe gustaría obtener más detalles sobre la entrega de la factura *{$factura->numero_factura}* con destino a *{$clientContact}*.";
                                        $whatsAppNumber = "521" . $editorPhoneNumber;
                                        $whatsAppLink = "https://wa.me/{$whatsAppNumber}?text=" . urlencode($message);
                                    @endphp
                                    <a href="{{ $whatsAppLink }}" target="_blank" class="inline-flex items-center px-3 py-1.3 bg-[rgb(44,56,86)] text-white rounded-full font-semibold text-xs uppercase tracking-widest hover:bg-[#ff9c00] transition-all duration-300 transform hover:scale-105 shadow-md" title="Enviar WhatsApp a {{ $editor->name }}">
                                        <!-- <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.894 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.886-.001 2.269.655 4.506 1.908 6.385l-1.292 4.72zm7.572-6.911c-.533-.266-3.143-1.552-3.64-1.725-.496-.173-.855-.265-1.214.265-.36.53-.137 1.251-.138 1.251-.36.53-.137 1.251-.138 1.251l-.149.174c-.379.444-.799.524-1.214.379-.414-.143-1.745-.636-3.328-2.049a11.583 11.583 0 0 1-2.3-2.828c-.266-.495-.034-.764.231-1.021.233-.232.496-.615.744-.913.249-.298.33-.495.496-.855.165-.36.083-.66-.034-.912-.117-.252-1.213-2.909-1.662-3.996-.448-1.087-.905-1.008-1.213-1.008h-.494c-.359 0-.912.117-1.385.579-.47.462-1.798 1.76-1.798 4.298s1.839 4.99 2.083 5.349c.243.359 3.593 5.493 8.718 7.669 1.144.495 2.062.793 2.76.995.894.243 1.706.215 2.333-.034.707-.282 2.196-1.12 2.502-2.208.307-1.087.307-2.008.216-2.208-.092-.2-.358-.321-.737-.533z"/></svg> -->
                                        <span>Contacto CS</span>
                                    </a>
                                @endif
                                
                                <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full 
                                    @if($factura->estatus_entrega == 'Pendiente') bg-yellow-100 text-yellow-800
                                    @elseif($factura->estatus_entrega == 'Entregada') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    @if($factura->estatus_entrega == 'Pendiente')
                                        Asignada
                                    @else
                                        {{ $factura->estatus_entrega }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <h4 class="text-sm font-bold text-gray-600 mb-3">Historial de Entrega:</h4>
                        @if($factura->eventos->isEmpty())
                            <p class="text-sm text-gray-500">Aún no hay eventos de entrega para esta factura.</p>
                        @else
                            @foreach($factura->eventos as $evento)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start mb-4">
                                    <div>
                                        <p class="text-sm font-semibold {{ $evento->subtipo == 'Factura Entregada' ? 'text-green-700' : 'text-red-700' }}">
                                            {{ $evento->subtipo }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $evento->fecha_evento->format('d/m/Y h:i A') }}</p>
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
                                    <div>
                                        <a href="https://www.google.com/maps/search/?api=1&query={{$evento->latitud}},{{$evento->longitud}}" target="_blank">
                                            <img 
                                                src="https://maps.googleapis.com/maps/api/staticmap?center={{$evento->latitud}},{{$evento->longitud}}&zoom=16&size=600x300&markers=color:red%7C{{$evento->latitud}},{{$evento->longitud}}&key={{ $googleMapsApiKey }}" 
                                                alt="Mapa de entrega" 
                                                class="w-full h-auto rounded-md border shadow-lg cursor-pointer hover:opacity-90 transition-opacity">
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                
                @elseif($item->source === 'order')
                    @php $order = $item; // Renombramos para claridad @endphp
                    <div class="bg-white border-l-4 border-[#ff9c00] rounded-r-lg p-4 mb-4 shadow-md">
                        <div class="flex flex-col sm:flex-row justify-between items-start pb-3 mb-3 border-b gap-2">
                            <div>
                                <p class="text-xs text-gray-500">Orden de Venta</p>
                                <p class="font-bold text-lg text-gray-800">{{ $order->invoice_number }}</p>
                            </div>
                            <div class="flex items-center space-x-3 flex-shrink-0">
                                @php
                                    $editor = $order->updater;
                                    $clientContact = $order->client_contact ?? $order->customer_name;
                                    $editorPhoneNumber = $editor?->phone_number;
                                @endphp

                                @if($editorPhoneNumber)
                                    @php
                                        $hour = now()->hour;
                                        $greeting = ($hour >= 5 && $hour < 12) ? 'buen día' : (($hour >= 12 && $hour < 20) ? 'buena tarde' : 'buena noche');
                                        $editorFirstName = explode(' ', $editor->name)[0];
                                        $message = "Hola, {$greeting} {$editorFirstName}.\n\nMe gustaría obtener más detalles sobre el estado de la orden con factura {$order->invoice_number} para el cliente {$clientContact}.";
                                        $whatsAppNumber = "521" . $editorPhoneNumber;
                                        $whatsAppLink = "https://wa.me/{$whatsAppNumber}?text=" . urlencode($message);
                                    @endphp
                                    <a href="{{ $whatsAppLink }}" target="_blank" class="inline-flex items-center px-3 py-1.3 bg-[rgb(44,56,86)] text-white rounded-full font-semibold text-xs uppercase tracking-widest hover:bg-[#ff9c00] transition-all duration-300 transform hover:scale-105 shadow-md" title="Enviar WhatsApp a {{ $editor->name }}">
                                        <!-- <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.894 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.886-.001 2.269.655 4.506 1.908 6.385l-1.292 4.72zm7.572-6.911c-.533-.266-3.143-1.552-3.64-1.725-.496-.173-.855-.265-1.214.265-.36.53-.137 1.251-.138 1.251-.36.53-.137 1.251-.138 1.251l-.149.174c-.379.444-.799.524-1.214.379-.414-.143-1.745-.636-3.328-2.049a11.583 11.583 0 0 1-2.3-2.828c-.266-.495-.034-.764.231-1.021.233-.232.496-.615.744-.913.249-.298.33-.495.496-.855.165-.36.083-.66-.034-.912-.117-.252-1.213-2.909-1.662-3.996-.448-1.087-.905-1.008-1.213-1.008h-.494c-.359 0-.912.117-1.385.579-.47.462-1.798 1.76-1.798 4.298s1.839 4.99 2.083 5.349c.243.359 3.593 5.493 8.718 7.669 1.144.495 2.062.793 2.76.995.894.243 1.706.215 2.333-.034.707-.282 2.196-1.12 2.502-2.208.307-1.087.307-2.008.216-2.208-.092-.2-.358-.321-.737-.533z"/></svg> -->
                                        <span>Contacto CS</span>
                                    </a>
                                @endif
                                <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Procesando
                                </span>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600">
                            <p><strong>Cliente:</strong> {{ $order->customer_name }}</p>
                            <p><strong>Fecha de Creación:</strong> {{ \Carbon\Carbon::parse($order->creation_date)->format('d/m/Y') }}</p>
                            <p class="mt-3 pt-3 border-t text-gray-500 italic">Esta orden está siendo procesada y aún no ha sido asignada a una ruta. Los detalles de seguimiento aparecerán una vez que se genere la guía de entrega.</p>
                        </div>
                    </div>
                @endif

            @empty
                <div class="text-center mt-8 bg-white p-8 rounded-lg shadow-md">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Sin resultados</h3>
                    <p class="mt-1 text-sm text-gray-500">No se encontraron facturas ni órdenes de venta con los números proporcionados.</p>
                </div>
            @endforelse
        </div>
    @endif
@endsection