@extends('layouts.guest-rutas')

@section('content')
    <h2 class="text-2xl font-bold text-center text-[#2c3856] mb-6">Portal de Operador</h2>
    
    <form method="POST" action="{{ route('operador.access') }}">
        @csrf
        <div>
            <label for="guia" class="block font-medium text-sm text-gray-700">Número de Guía</label>
            <input id="guia" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" type="text" name="guia" required autofocus />
        </div>

        @if(session('error'))
            <p class="text-sm text-red-600 mt-2">{{ session('error') }}</p>
        @endif

        <div class="flex items-center justify-end mt-6">
            <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-[#ff9c00] text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-orange-600">
                Acceder
            </button>
        </div>
    </form>
@endsection