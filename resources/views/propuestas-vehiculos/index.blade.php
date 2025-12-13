@extends('adminlte::page')
@section('title', 'Propuestas de Vehículos')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clipboard-check"></i> Propuestas de Vehículos</h1>
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

<!-- Estadísticas -->
<div class="row mb-3">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Propuestas</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['aprobadas'] }}</h3>
                <p>Aprobadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['rechazadas'] }}</h3>
                <p>Rechazadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['pendientes'] }}</h3>
                <p>Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
</div>

<div class="card shadow">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Listado de Propuestas</h3>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form method="GET" action="{{ route('propuestas-vehiculos.index') }}" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select name="estado" id="estado" class="form-control">
                            <option value="">Todos</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="aprobada" {{ request('estado') == 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                            <option value="rechazada" {{ request('estado') == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="codigo">Código de Envío</label>
                        <input type="text" name="codigo" id="codigo" class="form-control" 
                               value="{{ request('codigo') }}" placeholder="Buscar por código...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('propuestas-vehiculos.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <table id="propuestasTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Código Envío</th>
                    <th>Fecha Propuesta</th>
                    <th>Vehículos Propuestos</th>
                    <th>Peso Total (kg)</th>
                    <th>Volumen Total (m³)</th>
                    <th>Estado</th>
                    <th>Fecha Decisión</th>
                    <th width="150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($propuestas as $propuesta)
                <tr>
                    <td>{{ $propuesta->id }}</td>
                    <td>
                        <strong>{{ $propuesta->codigo_envio }}</strong>
                    </td>
                    <td>
                        <i class="fas fa-calendar-alt text-primary"></i> 
                        {{ $propuesta->fecha_propuesta->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <span class="badge badge-info">
                            {{ count($propuesta->propuesta_data['vehiculos_propuestos'] ?? []) }} vehículo(s)
                        </span>
                    </td>
                    <td>
                        <strong>{{ number_format($propuesta->propuesta_data['totales']['peso_kg'] ?? 0, 2) }}</strong> kg
                    </td>
                    <td>
                        <strong>{{ number_format($propuesta->propuesta_data['totales']['volumen_m3'] ?? 0, 2) }}</strong> m³
                    </td>
                    <td>
                        @if($propuesta->estado == 'aprobada')
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aprobada</span>
                        @elseif($propuesta->estado == 'rechazada')
                            <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rechazada</span>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                        @endif
                    </td>
                    <td>
                        @if($propuesta->fecha_decision)
                            <i class="fas fa-calendar-check text-success"></i> 
                            {{ $propuesta->fecha_decision->format('d/m/Y H:i') }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('propuestas-vehiculos.show', $propuesta->id) }}" 
                           class="btn btn-sm btn-info" title="Ver Detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($propuesta->envio)
                            <a href="{{ route('envios.show', $propuesta->envio->id) }}" 
                               class="btn btn-sm btn-primary" title="Ver Envío">
                                <i class="fas fa-box"></i>
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Paginación -->
        <div class="d-flex justify-content-center">
            {{ $propuestas->links() }}
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    $('#propuestasTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "columnDefs": [
            { "orderable": false, "targets": 8 }
        ]
    });
});
</script>
@endpush

