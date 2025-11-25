@extends('adminlte::page')
@section('title', 'Almacenes')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-warehouse"></i> Almacenes</h1>
        <a href="{{ route('almacenes.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Almacén
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

<div class="card shadow">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Listado de Almacenes</h3>
    </div>
    <div class="card-body">
        <table id="almacenesTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Inventario</th>
                    <th width="200px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($almacenes as $almacen)
                <tr>
                    <td>{{ $almacen->id }}</td>
                    <td><strong>{{ $almacen->nombre }}</strong></td>
                    <td>{{ $almacen->direccion ? $almacen->direccion->descripcion : 'Sin dirección' }}</td>
                    <td class="text-center">
                        <a href="{{ route('almacenes.inventario', $almacen) }}" class="btn btn-sm btn-info" title="Ver Inventario">
                            <i class="fas fa-boxes"></i> Ver Inventario
                        </a>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('almacenes.edit', $almacen) }}" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('almacenes.destroy', $almacen) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar este almacén?')">
                                @csrf @method('DELETE')
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
        $('#almacenesTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
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
</style>
@endsection

@section('plugins.Datatables', true)
