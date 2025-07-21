@extends('layouts.app')

@section('content')
<style>
    /* Custom styles to match your color palette */
    .btn-custom-primary {
        background-color: #2c3856;
        color: #ffffff;
        transition: background-color 0.3s ease;
    }
    .btn-custom-primary:hover {
        background-color: #1a2233;
        color: #ffffff;
    }
    .form-check-input:checked {
        background-color: #ff9c00;
        border-color: #ff9c00;
    }
    .form-control:focus, .form-select:focus {
        border-color: #ff9c00;
        box-shadow: 0 0 0 0.25rem rgba(255, 156, 0, 0.25);
    }
</style>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white p-6 sm:p-8 rounded-xl shadow-lg">
        
        <div class="text-center mb-8">
            <img src="{{ Storage::disk('s3')->url('LogoAzul.png') }}" alt="Logotipo Minmer Global" class="mx-auto h-16 w-auto mb-4">
            <h1 class="text-2xl font-bold text-[#2b2b2b]">Gestión de Visitas</h1>
            <p class="text-[#666666]">Registra los datos para generar una nueva invitación con código QR.</p>
        </div>

        {{-- Notificaciones de Éxito o Error --}}
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
                <p class="font-bold">Éxito</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
                <p class="font-bold">Se encontraron errores:</p>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('area_admin.visits.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Columna Izquierda --}}
                <div>
                    <h3 class="text-lg font-semibold text-[#2b2b2b] border-b pb-2 mb-4">Información del Visitante</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="visitor_name" class="block text-sm font-medium text-gray-700">Nombre(s)</label>
                            <input type="text" name="visitor_name" id="visitor_name" value="{{ old('visitor_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-control" required>
                        </div>
                        <div>
                            <label for="visitor_last_name" class="block text-sm font-medium text-gray-700">Apellido(s)</label>
                            <input type="text" name="visitor_last_name" id="visitor_last_name" value="{{ old('visitor_last_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-control" required>
                        </div>
                        <div>
                            <label for="company" class="block text-sm font-medium text-gray-700">Empresa (Opcional)</label>
                            <input type="text" name="company" id="company" value="{{ old('company') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-control">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-control" required>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="send_email" id="send_email" value="1" checked>
                            <label class="form-check-label text-sm text-gray-600" for="send_email">
                                Enviar correo con QR al visitante (y copia a mi correo)
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Columna Derecha --}}
                <div>
                    <h3 class="text-lg font-semibold text-[#2b2b2b] border-b pb-2 mb-4">Detalles de la Visita</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="visit_datetime" class="block text-sm font-medium text-gray-700">Fecha y Hora de Visita</label>
                            <input type="datetime-local" name="visit_datetime" id="visit_datetime" value="{{ old('visit_datetime') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-control" required>
                        </div>
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700">Motivo</label>
                            <textarea name="reason" id="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-control" required>{{ old('reason') }}</textarea>
                        </div>
                        <div>
                            <label for="companions" class="block text-sm font-medium text-gray-700">Acompañantes (Opcional, uno por línea)</label>
                            <textarea name="companions" id="companions" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-control">{{ old('companions') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-lg font-semibold text-[#2b2b2b] border-b pb-2 mb-4">Información del Vehículo (Opcional)</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="vehicle_make" class="block text-sm font-medium text-gray-700">Marca</label>
                        <input type="text" name="vehicle_make" id="vehicle_make" value="{{ old('vehicle_make') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-control">
                    </div>
                    <div>
                        <label for="vehicle_model" class="block text-sm font-medium text-gray-700">Modelo</label>
                        <input type="text" name="vehicle_model" id="vehicle_model" value="{{ old('vehicle_model') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-control">
                    </div>
                    <div>
                        <label for="license_plate" class="block text-sm font-medium text-gray-700">Placas</label>
                        <input type="text" name="license_plate" id="license_plate" value="{{ old('license_plate') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm form-control">
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t">
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium btn-custom-primary">
                    <i class="fas fa-qrcode mr-2"></i>Generar Invitación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
