<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-g">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
            @if(request()->routeIs('tracking.*'))
                Rastreo de factura -
            @else
                Portal de Operador -
            @endif
            {{ config('app.name', 'Laravel') }}
        </title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#E8ECF7]">
        <div class="min-h-screen flex flex-col items-center justify-center p-4">
            <div class="mb-8">
                <img src="{{ Storage::disk('s3')->url('LogoAzulm.PNG') }}" alt="Minmer Global Logo" class="h-24 mx-auto">
            </div>
            <div class="w-full sm:max-w-md bg-white shadow-md rounded-lg p-8">
                @yield('content')
            </div>
        </div>
        @stack('scripts')        
    </body>
</html>