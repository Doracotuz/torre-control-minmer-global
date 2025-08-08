@extends('layouts.app')

@section('content')
<style>
    .detail-card {
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .detail-header {
        background-color: #2c3856;
        color: #ffffff;
        padding: 1.5rem;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .detail-body {
        padding: 2rem;
    }
    .detail-section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #2b2b2b;
        border-bottom: 2px solid #ff9c00;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #e5e7eb;
    }
    .detail-item:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #666666;
    }
    .detail-value {
        color: #2b2b2b;
        text-align: right;
    }
    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .badge-programada { background-color: #2c3856; color: white; }
    .badge-ingresado { background-color: #ff9c00; color: white; }
    .badge-no-ingresado { background-color: #666666; color: white; }
    .badge-cancelada { background-color: #2b2b2b; color: white; }
    .badge-finalizada { background-color: #10B981; color: white; }

</style>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto detail-card">
        
        <div class="detail-header flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Detalles de la Visita</h1>
                <p class="text-sm opacity-80">Información completa del registro.</p>
            </div>
            <a href="{{ route('area_admin.visits.index') }}" class="text-white hover:text-gray-200 transition">
                <i class="fas fa-arrow-left mr-2"></i> Volver a la Bitácora
            </a>
        </div>

        <div class="detail-body grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
            {{-- SECCIÓN INFORMACIÓN DEL VISITANTE --}}
            <div class="col-span-1">
                <h3 class="detail-section-title">Información del Visitante</h3>
                <div class="space-y-2">
                    <div class="detail-item">
                        <span class="detail-label">Nombre(s):</span>
                        <span class="detail-value">{{ $visit->visitor_name }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Apellido(s):</span>
                        <span class="detail-value">{{ $visit->visitor_last_name }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Correo Electrónico:</span>
                        <span class="detail-value">{{ $visit->email }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Empresa:</span>
                        <span class="detail-value">{{ $visit->company ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN DETALLES DE LA VISITA --}}
            <div class="col-span-1">
                <h3 class="detail-section-title">Detalles de la Visita</h3>
                <div class="space-y-2">
                    <div class="detail-item">
                        <span class="detail-label">Estatus:</span>
                        <span class="detail-value">
                            <span class="badge badge-{{ strtolower(str_replace(' ', '-', $visit->status)) }}">
                                {{ $visit->status }}
                            </span>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Fecha y Hora Programada:</span>
                        <span class="detail-value">{{ $visit->visit_datetime->format('d/m/Y h:i A') }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Hora de Salida:</span>
                        <span class="detail-value">{{ $visit->exit_datetime ? $visit->exit_datetime->format('d/m/Y h:i A') : '—' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Motivo:</span>
                        <span class="detail-value text-left pl-4">{{ $visit->reason }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Acompañantes:</span>
                        <span class="detail-value text-left pl-4">{!! nl2br(e($visit->companions ?? 'Ninguno')) !!}</span>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN INFORMACIÓN DEL VEHÍCULO (si aplica) --}}
            @if($visit->vehicle_make || $visit->vehicle_model || $visit->license_plate)
            <div class="md:col-span-2">
                <h3 class="detail-section-title">Información del Vehículo</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="detail-item flex-col items-start !border-0">
                        <span class="detail-label">Marca:</span>
                        <span class="detail-value !text-left">{{ $visit->vehicle_make ?? 'N/A' }}</span>
                    </div>
                     <div class="detail-item flex-col items-start !border-0">
                        <span class="detail-label">Modelo:</span>
                        <span class="detail-value !text-left">{{ $visit->vehicle_model ?? 'N/A' }}</span>
                    </div>
                     <div class="detail-item flex-col items-start !border-0">
                        <span class="detail-label">Placas:</span>
                        <span class="detail-value !text-left">{{ $visit->license_plate ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection