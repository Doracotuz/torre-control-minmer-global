@extends('layouts.audit-layout')
@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('audit.index') }}" class="text-sm font-semibold text-gray-600 mb-4 inline-block">&larr; Volver al Dashboard</a>
        <h1 class="text-2xl font-bold text-[#2c3856]">Auditoría de Arribo de Unidad</h1>
        <p class="text-gray-600 mb-6">Guía: {{ $guia->guia }}</p>

        <form action="{{ route('audit.patio.store', $guia->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-white p-4 rounded-lg shadow-md space-y-4">
                <div><label class="block text-sm font-medium">Operador</label><input type="text" name="operador" value="{{ $guia->operador }}" class="mt-1 block w-full rounded-md border-gray-300" required></div>
                <div><label class="block text-sm font-medium">Placas</label><input type="text" name="placas" value="{{ $guia->placas }}" class="mt-1 block w-full rounded-md border-gray-300" required></div>
                <div><label class="block text-sm font-medium">Fecha de Arribo</label><input type="date" name="arribo_fecha" value="{{ now()->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300" required></div>
                <div><label class="block text-sm font-medium">Hora de Arribo</label><input type="time" name="arribo_hora" value="{{ now()->format('H:i') }}" class="mt-1 block w-full rounded-md border-gray-300" required></div>
                <div><label class="block text-sm font-medium">Estado de la Caja</label><select name="caja_estado" class="mt-1 block w-full rounded-md border-gray-300" required><option>Bueno</option><option>Regular</option><option>Malo</option></select></div>
                <div><label class="block text-sm font-medium">Estado de las Llantas</label><select name="llantas_estado" class="mt-1 block w-full rounded-md border-gray-300" required><option>Bueno</option><option>Regular</option><option>Malo</option></select></div>
                <div><label class="block text-sm font-medium">Nivel de Combustible</label><select name="combustible_nivel" class="mt-1 block w-full rounded-md border-gray-300" required><option>Lleno</option><option>3/4</option><option>1/2</option><option>1/4</option><option>Reserva</option></select></div>
                <div><label class="block text-sm font-medium">Equipo de Sujeción</label><select name="equipo_sujecion" class="mt-1 block w-full rounded-md border-gray-300" required><option>No aplica</option><option>Barras logísticas</option><option>Bandas</option><option>Eslingas</option><option>Ambas</option></select></div>
                <label class="flex items-center"><input type="checkbox" name="presenta_maniobra" value="1" class="rounded mr-2">Presenta Maniobra</label>
                <div><label class="block text-sm font-medium">Fotografía de la Unidad (1)</label><input type="file" name="fotos_unidad[]" class="mt-1 block w-full" required></div>
                <div><label class="block text-sm font-medium">Fotografía de Llantas (1)</label><input type="file" name="fotos_llantas[]" class="mt-1 block w-full" required></div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full px-6 py-3 bg-purple-600 text-white rounded-lg font-bold shadow-lg hover:bg-purple-700 transition-colors">Confirmar Arribo y Poner en Cortina</button>
                </div>
            </div>
        </form>
    </div>
@endsection
