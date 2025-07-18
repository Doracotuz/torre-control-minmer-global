@extends('layouts.app') {{-- Asegúrate que este sea tu layout principal --}}

@section('content')
<style>
    .tms-dashboard-card {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border-radius: 15px;
        color: #ffffff;
        text-decoration: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        padding: 2rem;
        background-color: #2c3856; /* Azul Oscuro Corporativo */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .tms-dashboard-card:hover {
        transform: translateY(-10px);
        color: #ffffff;
        box-shadow: 0 8px 25px rgba(44, 56, 86, 0.5); /* Sombra con color azul */
    }
    .tms-dashboard-card .icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #ff9c00; /* Naranja Corporativo */
    }
    .tms-dashboard-card .title {
        font-size: 1.5rem;
        font-weight: bold;
    }
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Dashboard de Gestión de Transporte (TMS)</h1>
        </div>
    </div>

    <div class="row">
        {{-- Botón Ver Rutas --}}
        <div class="col-12 col-md-4 mb-4">
            <a href="{{ route('tms.viewRoutes') }}" class="tms-dashboard-card h-100">
                <div class="icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="title">
                    Ver Rutas
                </div>
            </a>
        </div>

        {{-- Botón Crear Rutas --}}
        <div class="col-12 col-md-4 mb-4">
            <a href="{{ route('tms.createRoute') }}" class="tms-dashboard-card h-100">
                <div class="icon">
                    <i class="fas fa-drafting-compass"></i>
                </div>
                <div class="title">
                    Crear Rutas
                </div>
            </a>
        </div>

        {{-- Botón Asignar Rutas --}}
        <div class="col-12 col-md-4 mb-4">
            <a href="{{ route('tms.assignRoutes') }}" class="tms-dashboard-card h-100">
                <div class="icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="title">
                    Asignar Rutas
                </div>
            </a>
        </div>
    </div>
</div>
@endsection