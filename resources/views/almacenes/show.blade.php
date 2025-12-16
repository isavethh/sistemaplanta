@extends('adminlte::page')
@section('title', 'Detalle Almacén - ' . $almacen->nombre)
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-warehouse"></i> Almacén: {{ $almacen->nombre }}</h1>
        <div>
            <a href="{{ route('almacenes.inventario', $almacen) }}" class="btn btn-info">
                <i class="fas fa-boxes"></i> Ver Inventario
            </a>
            <a href="{{ route('almacenes.edit', $almacen) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('almacenes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-info-circle"></i> Información del Almacén</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-tag"></i> Nombre:</strong></div>
                    <div class="col-md-8">{{ $almacen->nombre }}</div>
                </div>

                @if($almacen->direccion_completa)
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-map-marker-alt"></i> Dirección:</strong></div>
                    <div class="col-md-8">{{ $almacen->direccion_completa }}</div>
                </div>
                @endif

                @if($almacen->latitud && $almacen->longitud)
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-map-pin"></i> Coordenadas:</strong></div>
                    <div class="col-md-8">
                        Lat: {{ $almacen->latitud }}, Lng: {{ $almacen->longitud }}
                    </div>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-user"></i> Usuario Asignado:</strong></div>
                    <div class="col-md-8">{{ $almacen->usuarioAlmacen->name ?? 'N/A' }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-info-circle"></i> Estado:</strong></div>
                    <div class="col-md-8">
                        @if($almacen->activo)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-danger">Inactivo</span>
                        @endif
                        @if($almacen->es_planta)
                            <span class="badge badge-info">Planta</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-calendar"></i> Fecha Creación:</strong></div>
                    <div class="col-md-8">{{ $almacen->created_at->format('d/m/Y H:i:s') }}</div>
                </div>

                @if($almacen->updated_at != $almacen->created_at)
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-calendar-alt"></i> Última Actualización:</strong></div>
                    <div class="col-md-8">{{ $almacen->updated_at->format('d/m/Y H:i:s') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-gradient-info">
                <h5 class="card-title text-white"><i class="fas fa-tasks"></i> Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('almacenes.inventario', $almacen) }}" class="btn btn-info btn-block mb-2">
                    <i class="fas fa-boxes"></i> Ver Inventario
                </a>
                <a href="{{ route('almacenes.edit', $almacen) }}" class="btn btn-warning btn-block mb-2">
                    <i class="fas fa-edit"></i> Editar Almacén
                </a>
                <a href="{{ route('almacenes.monitoreo') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-map-marked-alt"></i> Monitoreo
                </a>
                <a href="{{ route('almacenes.index') }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

