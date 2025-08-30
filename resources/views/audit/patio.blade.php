@extends('layouts.audit-layout')
@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('audit.index') }}" class="text-sm font-semibold text-gray-600 mb-4 inline-block">&larr; Volver al Dashboard</a>
        <h1 class="text-2xl font-bold text-[#2c3856]">Auditoría de Arribo de Unidad</h1>
        <p class="text-gray-600 mb-6">Guía: {{ $guia->guia }}</p>

        <form action="{{ route('audit.patio.store', $guia->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Error al guardar:</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white p-4 rounded-lg shadow-md space-y-4">
                <div><label class="block text-sm font-medium">Operador</label><input type="text" name="operador" value="{{ old('operador', $guia->operador) }}" class="mt-1 block w-full rounded-md border-gray-300" required></div>
                <div><label class="block text-sm font-medium">Placas</label><input type="text" name="placas" value="{{ old('placas', $guia->placas) }}" class="mt-1 block w-full rounded-md border-gray-300" required></div>
                <div><label class="block text-sm font-medium">Fecha de Arribo</label><input type="date" name="arribo_fecha" value="{{ old('arribo_fecha', now()->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300" required></div>
                <div><label class="block text-sm font-medium">Hora de Arribo</label><input type="time" name="arribo_hora" value="{{ old('arribo_hora', now()->format('H:i')) }}" class="mt-1 block w-full rounded-md border-gray-300" required></div>
                <div><label class="block text-sm font-medium">Estado de la Caja</label><select name="caja_estado" class="mt-1 block w-full rounded-md border-gray-300" required><option @selected(old('caja_estado') == 'Bueno')>Bueno</option><option @selected(old('caja_estado') == 'Regular')>Regular</option><option @selected(old('caja_estado') == 'Malo')>Malo</option></select></div>
                <div><label class="block text-sm font-medium">Estado de las Llantas</label><select name="llantas_estado" class="mt-1 block w-full rounded-md border-gray-300" required><option @selected(old('llantas_estado') == 'Bueno')>Bueno</option><option @selected(old('llantas_estado') == 'Regular')>Regular</option><option @selected(old('llantas_estado') == 'Malo')>Malo</option></select></div>
                <div><label class="block text-sm font-medium">Nivel de Combustible</label><select name="combustible_nivel" class="mt-1 block w-full rounded-md border-gray-300" required><option @selected(old('combustible_nivel') == 'Lleno')>Lleno</option><option @selected(old('combustible_nivel') == '3/4')>3/4</option><option @selected(old('combustible_nivel') == '1/2')>1/2</option><option @selected(old('combustible_nivel') == '1/4')>1/4</option><option @selected(old('combustible_nivel') == 'Reserva')>Reserva</option></select></div>
                <div><label class="block text-sm font-medium">Equipo de Sujeción</label><select name="equipo_sujecion" class="mt-1 block w-full rounded-md border-gray-300" required><option @selected(old('equipo_sujecion') == 'No aplica')>No aplica</option><option @selected(old('equipo_sujecion') == 'Barras logísticas')>Barras logísticas</option><option @selected(old('equipo_sujecion') == 'Bandas')>Bandas</option><option @selected(old('equipo_sujecion') == 'Eslingas')>Eslingas</option><option @selected(old('equipo_sujecion') == 'Ambas')>Ambas</option></select></div>
                <label class="flex items-center"><input type="checkbox" name="presenta_maniobra" value="1" @checked(old('presenta_maniobra')) class="rounded mr-2">Presenta Maniobra</label>
                <div><label class="block text-sm font-medium">Fotografía de la Unidad <span class="text-red-500">*</span></label><input type="file" name="foto_unidad" class="mt-1 block w-full" required></div>
                <div><label class="block text-sm font-medium">Fotografía de Llantas <span class="text-red-500">*</span></label><input type="file" name="foto_llantas" class="mt-1 block w-full" required></div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg font-bold shadow-lg hover:bg-purple-700 transition-colors">Confirmar Arribo y Poner en Cortina</button>
                </div>
            </div>
        </form>
    </div>
@endsection
