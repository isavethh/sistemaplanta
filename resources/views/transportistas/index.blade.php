@extends('adminlte::page')

@section('title', 'Transportistas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-user-tie"></i> Transportistas</h1>
        <a href="{{ route('transportistas.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Transportista
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

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card shadow">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Listado de Transportistas</h3>
    </div>
    <div class="card-body">
        <table id="transportistasTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Licencia</th>
                    <th>Disponible</th>
                    <th width="150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transportistas as $t)
                <tr>
                    <td>{{ $t->id }}</td>
                    <td><strong>{{ $t->name }}</strong></td>
                    <td>{{ $t->email }}</td>
                    <td>{{ $t->telefono ?? '-' }}</td>
                    <td>
                        @if($t->licencia)
                            <span class="badge badge-secondary">{{ $t->licencia }}</span>
                        @else
                            <span class="text-muted">Sin licencia</span>
                        @endif
                    </td>
                    <td>
                        @if($t->disponible)
                            <span class="badge badge-success">Sí</span>
                        @else
                            <span class="badge badge-danger">No</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('transportistas.edit', $t) }}" class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('transportistas.destroy', $t) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar este transportista?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No hay transportistas registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        $('#transportistasTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            }
        });
    });
</script>
@endsection

@section('plugins.Datatables', true)
