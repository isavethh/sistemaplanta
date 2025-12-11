@extends('adminlte::page')
@section('title', 'Dashboard Almacén')
@section('content_header')
    <h1 class="m-0"><i class="fas fa-warehouse"></i> Dashboard - Almacén</h1>
@endsection

@section('content')
<!-- Mensaje de Bienvenida -->
<div class="row">
    <div class="col-12">
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <h5><i class="icon fas fa-box-open"></i> ¡Bienvenido, {{ auth()->user()->name }}!</h5>
            Gestiona las recepciones de envíos, inventario y documentación.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
</div>

<!-- Estadísticas del Almacén -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ \App\Models\Envio::whereIn('estado', ['entregado', 'en_almacen'])->count() }}</h3>
                <p>Envíos Recibidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-box-open"></i>
            </div>
            <a href="{{ route('envios.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ \App\Models\Envio::where('estado', 'en_transito')->count() }}</h3>
                <p>Por Recibir</p>
            </div>
            <div class="icon">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <a href="{{ route('envios.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ \App\Models\InventarioAlmacen::sum('cantidad') }}</h3>
                <p>Inventario Total</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
            <a href="{{ route('inventarios.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-primary">
            <div class="inner">
                <h3>{{ \App\Models\Envio::whereDate('updated_at', today())->whereIn('estado', ['entregado', 'en_almacen'])->count() }}</h3>
                <p>Recibidos Hoy</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <a href="{{ route('envios.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Acciones Rápidas -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title text-white"><i class="fas fa-tasks"></i> Acciones Rápidas</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-box-open fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Envíos Recibidos</h5>
                                <a href="{{ route('envios.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-eye"></i> Ver Recepciones
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card bg-primary hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-receipt fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Notas de Entrega</h5>
                                <a href="{{ route('notas-entrega.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-file-alt"></i> Ver Documentos
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card bg-info hover-card">
                            <div class="card-body text-center">
                                <i class="fas fa-cubes fa-3x text-white mb-3"></i>
                                <h5 class="text-white">Inventario</h5>
                                <a href="{{ route('inventarios.index') }}" class="btn btn-light btn-block">
                                    <i class="fas fa-boxes"></i> Ver Inventario
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="card bg-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-circle fa-2x text-white mb-2"></i>
                                <h5 class="text-white">¿Detectaste un problema con un envío?</h5>
                                <a href="{{ route('incidentes.index') }}" class="btn btn-light">
                                    <i class="fas fa-plus"></i> Reportar Incidente
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Últimos Envíos Recibidos -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-success">
                <h3 class="card-title"><i class="fas fa-history"></i> Últimas Recepciones</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Fecha Recepción</th>
                                <th>Origen</th>
                                <th>Transportista</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $ultimasRecepciones = DB::table('envios as e')
                                    ->leftJoin('envio_asignaciones as ea', 'e.id', '=', 'ea.envio_id')
                                    ->leftJoin('vehiculos as v', 'ea.vehiculo_id', '=', 'v.id')
                                    ->leftJoin('users as t', 'v.transportista_id', '=', 't.id')
                                    ->whereIn('e.estado', ['entregado', 'en_almacen'])
                                    ->select('e.*', 't.name as transportista_nombre')
                                    ->orderByDesc('e.updated_at')
                                    ->limit(10)
                                    ->get();
                            @endphp
                            @forelse($ultimasRecepciones as $envio)
                            <tr>
                                <td><strong>#{{ $envio->id }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($envio->updated_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $envio->direccion_origen ?? 'Planta' }}</td>
                                <td>{{ $envio->transportista_nombre ?? 'N/A' }}</td>
                                <td>
                                    @if($envio->estado == 'entregado')
                                        <span class="badge badge-success">Entregado</span>
                                    @elseif($envio->estado == 'en_almacen')
                                        <span class="badge badge-info">En Almacén</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $envio->estado }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('envios.show', $envio->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay envíos recibidos</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumen de Inventario -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title"><i class="fas fa-warehouse"></i> Resumen de Inventario</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box bg-gradient-success">
                            <span class="info-box-icon"><i class="fas fa-box"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Productos Únicos</span>
                                <span class="info-box-number">{{ \App\Models\Producto::count() }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-box bg-gradient-primary">
                            <span class="info-box-icon"><i class="fas fa-cubes"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Cantidad Total</span>
                                <span class="info-box-number">{{ \App\Models\InventarioAlmacen::sum('cantidad') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="info-box bg-gradient-warning">
                            <span class="info-box-icon"><i class="fas fa-warehouse"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Almacenes Activos</span>
                                <span class="info-box-number">{{ \App\Models\Almacen::count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .hover-card:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3) !important;
    }
    .small-box, .info-box {
        border-radius: 10px;
    }
</style>
@endsection
