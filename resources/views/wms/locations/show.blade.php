@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalles de la Ubicación: {{ $location->code }}</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Ubicación Completa: {{ $location->aisle }}-{{ $location->rack }}-{{ $location->shelf }}-{{ $location->bin }}</h5>
            <p class="card-text"><strong>ID:</strong> {{ $location->id }}</p>
            <p class="card-text"><strong>Almacén:</strong> {{ $location->warehouse->name ?? 'N/A' }}</p>
            <p class="card-text"><strong>Tipo:</strong> {{ $location->type }}</p>
            <p class="card-text"><strong>Pasillo (Aisle):</strong> {{ $location->aisle }}</p>
            <p class="card-text"><strong>Rack:</strong> {{ $location->rack }}</p>
            <p class="card-text"><strong>Nivel (Shelf):</strong> {{ $location->shelf }}</p>
            <p class="card-text"><strong>Contenedor (Bin):</strong> {{ $location->bin }}</p>
            <p class="card-text"><strong>Secuencia de Picking:</strong> {{ $location->pick_sequence ?? 'No definida' }}</p>
        </div>
        <div class="card-footer">
            <a href="{{ route('wms.locations.index') }}" class="btn btn-secondary">Volver a la lista</a>
            <a href="{{ route('wms.locations.edit', $location->id) }}" class="btn btn-primary">Editar</a>
        </div>
    </div>
</div>
@endsection