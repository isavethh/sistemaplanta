@extends('adminlte::page')
@section('title', 'Inventario de Almacén')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-warehouse"></i> Inventario de {{ $almacen->nombre }}</h1>
        <div>
            <a href="{{ route('inventarios.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Añadir al Inventario
            </a>
            <a href="{{ route('almacenes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<!-- Información del Almacén -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-info">
                <h3 class="card-title text-white"><i class="fas fa-info-circle"></i> Información del Almacén</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong><i class="fas fa-warehouse"></i> Nombre:</strong> {{ $almacen->nombre }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong><i class="fas fa-map-marker-alt"></i> Dirección:</strong> {{ $almacen->direccion ? $almacen->direccion->descripcion : 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong><i class="fas fa-boxes"></i> Total Items:</strong> 
                            <span class="badge badge-success badge-pill">{{ $inventario->count() }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $inventario->sum('cantidad') }}</h3>
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
                <h3>{{ number_format($inventario->sum('peso'), 2) }}</h3>
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
                <h3>${{ number_format($inventario->sum(function($item) { return $item->cantidad * $item->precio_unitario; }), 2) }}</h3>
                <p>Valor Total</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $inventario->count() }}</h3>
                <p>Tipos de Productos</p>
            </div>
            <div class="icon">
                <i class="fas fa-box-open"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Inventario -->
<div class="card shadow">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Detalle del Inventario</h3>
    </div>
    <div class="card-body">
        <table id="inventarioTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Peso (kg)</th>
                    <th>Precio Unit.</th>
                    <th>Valor Total</th>
                    <th>Fecha Llegada</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventario as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td><strong>{{ $item->producto_nombre }}</strong></td>
                    <td>
                        <span class="badge badge-success badge-pill">
                            {{ $item->cantidad }}
                        </span>
                    </td>
                    <td>{{ number_format($item->peso ?? 0, 2) }}</td>
                    <td>${{ number_format($item->precio_unitario ?? 0, 2) }}</td>
                    <td>
                        <strong class="text-success">
                            ${{ number_format($item->cantidad * ($item->precio_unitario ?? 0), 2) }}
                        </strong>
                    </td>
                    <td>{{ $item->fecha_llegada ? \Carbon\Carbon::parse($item->fecha_llegada)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-weight-bold bg-light">
                    <td colspan="2" class="text-right">TOTALES:</td>
                    <td>
                        <span class="badge badge-success badge-pill">
                            {{ $inventario->sum('cantidad') }}
                        </span>
                    </td>
                    <td>{{ number_format($inventario->sum('peso'), 2) }}</td>
                    <td>-</td>
                    <td>
                        <strong class="text-success">
                            ${{ number_format($inventario->sum(function($item) { return $item->cantidad * $item->precio_unitario; }), 2) }}
                        </strong>
                    </td>
                    <td>-</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#inventarioTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            order: [[6, 'desc']] // Ordenar por fecha
        });
    });
</script>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
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

@section('plugins.Datatables', true)
