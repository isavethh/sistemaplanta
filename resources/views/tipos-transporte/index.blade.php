@extends('adminlte::page')

@section('title', 'Tipos de Transporte')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-truck-loading"></i> Tipos de Transporte</h1>
        <a href="{{ route('tipos-transporte.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Tipo
        </a>
    </div>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div class="card shadow">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Tipos de Transporte</h3>
    </div>
    <div class="card-body">
        <table id="tiposTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Control Temp.</th>
                    <th>Estado</th>
                    <th width="150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tipos as $tipo)
                <tr>
                    <td>{{ $tipo->id }}</td>
                    <td><strong>{{ $tipo->nombre }}</strong></td>
                    <td>{{ $tipo->descripcion ?? 'N/A' }}</td>
                    <td>
                        @if($tipo->requiere_temperatura_controlada)
                            <span class="badge badge-info">
                                {{ $tipo->temperatura_minima }}° - {{ $tipo->temperatura_maxima }}°
                            </span>
                        @else
                            <span class="badge badge-secondary">No requiere</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $tipo->activo ? 'success' : 'danger' }}">
                            {{ $tipo->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('tipos-transporte.edit', $tipo) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('tipos-transporte.destroy', $tipo) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Eliminar?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">
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
    $('#tiposTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        }
    });
});
</script>
@endsection

@section('plugins.Datatables', true)

