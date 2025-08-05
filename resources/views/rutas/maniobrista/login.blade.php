@extends('layouts.guest-rutas')
@section('content')
    <h2 class="text-2xl font-bold text-center text-[#2c3856] mb-6">Portal de Maniobrista</h2>
    <form id="login-form" method="POST" action="{{ route('maniobrista.access') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <label for="numero_empleado">Número de Empleado</label>
                <input id="numero_empleado" class="block mt-1 w-full" type="text" name="numero_empleado" required autofocus />
            </div>
            <div>
                <label for="guia">Número de Guía</label>
                <input id="guia" class="block mt-1 w-full" type="text" name="guia" required />
            </div>
        </div>
        @if(session('error')) <p class="text-sm text-red-600 mt-2">{{ session('error') }}</p> @endif
        <div class="mt-6">
            <button type="button" onclick="confirmAndSubmit()" class="w-full justify-center ...">Acceder</button>
        </div>
    </form>
    <script>
        function confirmAndSubmit() {
            const employee = document.getElementById('numero_empleado').value;
            if (confirm(`¿Confirmas que tu número de empleado es ${employee}?`)) {
                document.getElementById('login-form').submit();
            }
        }
    </script>
@endsection