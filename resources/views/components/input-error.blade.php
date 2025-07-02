@props(['messages' => []]) {{-- Establece un valor por defecto de array vacío --}}

@if (! empty($messages)) {{-- Cambia la verificación para que sea más robusta --}}
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif