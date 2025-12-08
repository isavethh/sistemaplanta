@extends('adminlte::page')

@section('title', 'Gestión de Inventario')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-warehouse"></i> Inventario por Almacén</h1>
    </div>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Selector de Almacén -->
<div class="card shadow mb-4">
    <div class="card-header bg-gradient-info">
        <h3 class="card-title text-white"><i class="fas fa-filter"></i> Seleccionar Almacén</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('inventarios.index') }}" class="form-inline">
            <div class="form-group mr-3">
                <label for="almacen_id" class="mr-2"><strong>Almacén:</strong></label>
                <select name="almacen_id" id="almacen_id" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Seleccione un almacén --</option>
                    @foreach($almacenes as $almacen)
                        <option value="{{ $almacen->id }}" {{ $almacenSeleccionado == $almacen->id ? 'selected' : '' }}>
                            {{ $almacen->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Ver Inventario
            </button>
        </form>
    </div>
</div>

@if($almacenSeleccionado && $almacenActual)
<!-- Información del Almacén -->
<div class="alert alert-info">
    <h5><i class="fas fa-warehouse"></i> Mostrando inventario de: <strong>{{ $almacenActual->nombre }}</strong></h5>
    <p class="mb-0">📍 {{ $almacenActual->direccion_completa ?? 'Sin dirección' }}</p>
</div>

<!-- Tarjetas de Resumen -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $inventarios->count() }}</h3>
                <p>Productos Diferentes</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $inventarios->sum('cantidad') }}</h3>
                <p>Total Unidades</p>
            </div>
            <div class="icon">
                <i class="fas fa-cubes"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($inventarios->sum('peso'), 2) }}</h3>
                <p>Peso Total (kg)</p>
            </div>
            <div class="icon">
                <i class="fas fa-weight"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>Bs. {{ number_format($inventarios->sum('total_precio'), 2) }}</h3>
                <p>Valor Total</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Productos por Categoría -->
@php
    $productosPorCategoria = $inventarios->groupBy('categoria');
@endphp

@foreach($productosPorCategoria as $categoria => $productos)
<div class="card shadow mb-3">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white">
            <i class="fas fa-tag"></i> Categoría: {{ $categoria ?? 'Sin categoría' }}
            <span class="badge badge-light ml-2">{{ $productos->count() }} productos</span>
        </h3>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad Total</th>
                    <th>Peso Total (kg)</th>
                    <th>Precio Promedio</th>
                    <th>Valor Total</th>
                    <th>Última Llegada</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $item)
                <tr>
                    <td><strong>{{ $item->producto_nombre }}</strong></td>
                    <td>
                        <span class="badge badge-success badge-pill" style="font-size: 1em;">
                            {{ number_format($item->cantidad) }}
                        </span>
                    </td>
                    <td>{{ number_format($item->peso ?? 0, 2) }} kg</td>
                    <td>Bs. {{ number_format($item->precio_unitario ?? 0, 2) }}</td>
                    <td>
                        <strong class="text-success">
                            Bs. {{ number_format($item->total_precio ?? 0, 2) }}
                        </strong>
                    </td>
                    <td>
                        @if($item->fecha_llegada)
                            {{ \Carbon\Carbon::parse($item->fecha_llegada)->format('d/m/Y H:i') }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-info">
                    <td><strong>Subtotal Categoría</strong></td>
                    <td><strong>{{ number_format($productos->sum('cantidad')) }}</strong></td>
                    <td><strong>{{ number_format($productos->sum('peso'), 2) }} kg</strong></td>
                    <td>-</td>
                    <td><strong>Bs. {{ number_format($productos->sum('total_precio'), 2) }}</strong></td>
                    <td>-</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endforeach

@if($inventarios->isEmpty())
<div class="alert alert-warning text-center">
    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
    <h5>No hay productos en el inventario de este almacén</h5>
    <p>Este almacén no ha recibido envíos entregados todavía.</p>
</div>
@endif

@else
<!-- Mensaje cuando no hay almacén seleccionado -->
<div class="card shadow">
    <div class="card-body text-center py-5">
        <i class="fas fa-hand-point-up fa-4x text-info mb-3"></i>
        <h4>Seleccione un almacén para ver su inventario</h4>
        <p class="text-muted">El inventario muestra todos los productos de envíos entregados a cada almacén, agrupados por categoría.</p>
    </div>
</div>
@endif
@endsection

@section('css')
<style>
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    .small-box {
        border-radius: 10px;
    }
</style>
@endsection

