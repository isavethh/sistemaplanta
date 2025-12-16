@extends('adminlte::page')
@section('title', 'Dashboard - Operador Trazabilidad')
@section('content_header')
    <h1><i class="fas fa-industry"></i> Dashboard - Operador de Trazabilidad</h1>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \App\Models\PedidoAlmacen::where('estado', 'enviado_trazabilidad')->count() }}</h3>
                <p>Pedidos Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('trazabilidad.pedidos-pendientes') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \App\Models\PedidoAlmacen::where('estado', 'propuesta_enviada')->count() }}</h3>
                <p>Propuestas Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <a href="{{ route('trazabilidad.propuestas-envios') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ \App\Models\PedidoAlmacen::where('estado', 'propuesta_aceptada')->count() }}</h3>
                <p>Propuestas Aprobadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('trazabilidad.pedidos-aceptados') }}" class="small-box-footer">
                Ver más <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ \App\Models\Envio::where('estado', 'pendiente_aprobacion_trazabilidad')->count() }}</h3>
                <p>Envíos Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-shipping-fast"></i>
            </div>
            <a href="{{ route('envios.index') }}" class="small-box-footer">
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
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\App\Models\PedidoAlmacen::orderBy('created_at', 'desc')->limit(5)->get() as $pedido)
                        <tr>
                            <td>{{ $pedido->codigo }}</td>
                            <td>{{ $pedido->almacen->nombre ?? 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $pedido->estado == 'entregado' ? 'success' : ($pedido->estado == 'cancelado' ? 'danger' : 'warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                                </span>
                            </td>
                            <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('trazabilidad.propuestas.ver', $pedido->id) }}" class="btn btn-sm btn-info">
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

