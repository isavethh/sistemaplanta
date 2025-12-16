@extends('adminlte::page')
@section('title', 'Dashboard - Propietario')
@section('content_header')
    <h1><i class="fas fa-warehouse"></i> Dashboard - Propietario de Almacén</h1>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \App\Models\PedidoAlmacen::where('usuario_propietario_id', auth()->id())->count() }}</h3>
                <p>Total Pedidos</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <a href="{{ route('pedidos-almacen.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \App\Models\PedidoAlmacen::where('usuario_propietario_id', auth()->id())->where('estado', 'enviado_trazabilidad')->count() }}</h3>
                <p>Pendientes Trazabilidad</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('pedidos-almacen.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ \App\Models\PedidoAlmacen::where('usuario_propietario_id', auth()->id())->where('estado', 'entregado')->count() }}</h3>
                <p>Pedidos Entregados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('pedidos-almacen.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ \App\Models\Almacen::where('usuario_almacen_id', auth()->id())->count() }}</h3>
                <p>Mis Almacenes</p>
            </div>
            <div class="icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <a href="{{ route('almacenes.index') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Pedidos Recientes</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Almacén</th>
                            <th>Fecha Requerida</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\Models\PedidoAlmacen::where('usuario_propietario_id', auth()->id())->orderBy('created_at', 'desc')->limit(5)->get() as $pedido)
                        <tr>
                            <td>{{ $pedido->codigo }}</td>
                            <td>{{ $pedido->almacen->nombre ?? 'N/A' }}</td>
                            <td>{{ $pedido->fecha_requerida->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge badge-{{ $pedido->estado == 'entregado' ? 'success' : ($pedido->estado == 'cancelado' ? 'danger' : 'warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('pedidos-almacen.show', $pedido->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

