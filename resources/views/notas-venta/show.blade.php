@extends('adminlte::page')

@section('title', 'Detalle Nota de Venta')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice-dollar text-success"></i> Nota de Venta</h1>
        <a href="{{ route('notas-venta.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-success text-white">
                    <h4 class="m-0">
                        <i class="fas fa-receipt"></i> {{ $nota->numero_nota }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase mb-3">
                                        <i class="fas fa-info-circle"></i> Información General
                                    </h6>
                                    <p class="mb-2">
                                        <strong>Número:</strong> 
                                        <span class="text-success font-weight-bold">{{ $nota->numero_nota }}</span>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Envío:</strong>
                                        <a href="{{ route('envios.show', $nota->envio_id) }}" class="badge badge-primary">
                                            <i class="fas fa-shipping-fast"></i> {{ $nota->envio_codigo }}
                                        </a>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Fecha:</strong>
                                        <i class="far fa-calendar-alt text-info"></i>
                                        {{ \Carbon\Carbon::parse($nota->fecha_emision)->format('d/m/Y H:i:s') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase mb-3">
                                        <i class="fas fa-warehouse"></i> Almacén Destino
                                    </h6>
                                    <p class="mb-2">
                                        <strong>Nombre:</strong> {{ $nota->almacen_nombre }}
                                    </p>
                                    <p class="mb-0">
                                        <strong>Dirección:</strong> {{ $nota->almacen_direccion }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="text-muted mb-3">
                        <i class="fas fa-boxes"></i> Productos
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th><i class="fas fa-box"></i> Producto</th>
                                    <th class="text-center"><i class="fas fa-sort-numeric-up"></i> Cantidad</th>
                                    <th class="text-right"><i class="fas fa-tag"></i> Precio Unit.</th>
                                    <th class="text-right"><i class="fas fa-weight"></i> Peso</th>
                                    <th class="text-right"><i class="fas fa-calculator"></i> Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productos as $producto)
                                    <tr>
                                        <td><strong>{{ $producto->producto_nombre }}</strong></td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $producto->cantidad }}</span>
                                        </td>
                                        <td class="text-right">Bs {{ number_format($producto->precio_unitario, 2) }}</td>
                                        <td class="text-right">{{ number_format($producto->total_peso, 2) }} kg</td>
                                        <td class="text-right">
                                            <strong class="text-success">Bs {{ number_format($producto->total_precio, 2) }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-success text-white">
                                <tr>
                                    <th colspan="1"><i class="fas fa-check-circle"></i> TOTALES</th>
                                    <th class="text-center">{{ $nota->total_cantidad }}</th>
                                    <th colspan="2"></th>
                                    <th class="text-right">
                                        <h4 class="mb-0">Bs {{ number_format($nota->total_precio, 2) }}</h4>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($nota->observaciones)
                        <div class="alert alert-info mt-4">
                            <h6><i class="fas fa-sticky-note"></i> Observaciones</h6>
                            <p class="mb-0">{{ $nota->observaciones }}</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('notas-venta.html', $nota->id) }}" 
                       class="btn btn-success btn-lg" 
                       target="_blank">
                        <i class="fas fa-print"></i> Imprimir Nota de Venta
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow bg-gradient-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                    <h6 class="text-uppercase">Total a Pagar</h6>
                    <h1 class="display-4 font-weight-bold">Bs {{ number_format($nota->total_precio, 2) }}</h1>
                    <p class="mb-0">{{ $nota->total_cantidad }} productos</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    .display-4 {
        font-size: 2.5rem;
    }
</style>
@stop
