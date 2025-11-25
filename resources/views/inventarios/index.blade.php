@extends('adminlte::page')

@section('title', 'Gestión de Inventario')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-warehouse"></i> Gestión de Inventario</h1>
        <a href="{{ route('inventarios.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Registro de Inventario
        </a>
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

<!-- Tarjetas de Resumen -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $inventarios->count() }}</h3>
                <p>Total Registros</p>
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
                <h3>${{ number_format($inventarios->sum(function($item) { return $item->cantidad * $item->precio_unitario; }), 2) }}</h3>
                <p>Valor Total</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Registros de Inventario</h3>
    </div>
    <div class="card-body">
        <table id="inventariosTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Almacén</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Peso (kg)</th>
                    <th>Precio Unit.</th>
                    <th>Valor Total</th>
                    <th>Fecha Llegada</th>
                    <th width="150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventarios as $inventario)
                <tr>
                    <td>{{ $inventario->id }}</td>
                    <td>
                        <span class="badge badge-primary">
                            {{ $inventario->almacen ? $inventario->almacen->nombre : 'N/A' }}
                        </span>
                    </td>
                    <td><strong>{{ $inventario->producto_nombre }}</strong></td>
                    <td>
                        <span class="badge badge-success badge-pill">
                            {{ $inventario->cantidad }}
                        </span>
                    </td>
                    <td>{{ number_format($inventario->peso ?? 0, 2) }}</td>
                    <td>${{ number_format($inventario->precio_unitario ?? 0, 2) }}</td>
                    <td>
                        <strong class="text-success">
                            ${{ number_format($inventario->cantidad * ($inventario->precio_unitario ?? 0), 2) }}
                        </strong>
                    </td>
                    <td>{{ $inventario->fecha_llegada ? \Carbon\Carbon::parse($inventario->fecha_llegada)->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('inventarios.edit', $inventario) }}" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('inventarios.destroy', $inventario) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar este registro?')">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#inventariosTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            order: [[7, 'desc']] // Ordenar por fecha
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
    .btn-group .btn {
        margin: 0 2px;
    }
</style>
@endsection

@section('plugins.Datatables', true)

