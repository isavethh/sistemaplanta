@extends('adminlte::page')

@section('title', 'Tamaños de Transporte')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-ruler-combined"></i> Tamaños de Transporte</h1>
        <a href="{{ route('tamanos-transporte.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Tamaño
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
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Listado de Tamaños</h3>
    </div>
    <div class="card-body">
        <table id="tamanosTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th width="150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tamanosTransporte as $tamano)
                <tr>
                    <td>{{ $tamano->id }}</td>
                    <td><strong>{{ $tamano->nombre }}</strong></td>
                    <td>{{ $tamano->descripcion ?? 'Sin descripción' }}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('tamanos-transporte.edit', $tamano) }}" 
                               class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('tamanos-transporte.destroy', $tamano) }}" 
                                  method="POST" 
                                  style="display: inline-block;"
                                  onsubmit="return confirm('¿Está seguro de eliminar este tamaño de transporte?');">
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
        $('#tamanosTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[0, 'asc']],
            pageLength: 25,
        });
    });
</script>
@endsection

