<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-g">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Portal de Operador - {{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col items-center justify-center p-4">
            {{-- Logo --}}
            <div class="mb-8">
                <img src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Minmer Global Logo" class="h-24 mx-auto">
            </div>
            {{-- Contenido de la página --}}
            <div class="w-full sm:max-w-md bg-white shadow-md rounded-lg p-8">
                @yield('content')
            </div>
        </div>
        @stack('scripts')        
    </body>
</html>