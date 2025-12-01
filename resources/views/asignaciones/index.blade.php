@extends('adminlte::page')

@section('title', 'Asignación de Envíos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-clipboard-check text-info"></i> Asignación de Envíos</h1>
        <div>
            <span class="badge badge-warning badge-lg">{{ $enviosPendientes->count() }} Pendientes</span>
            <span class="badge badge-info badge-lg ml-2">{{ $enviosAsignados->count() }} Asignados</span>
        </div>
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
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- ENVÍOS PENDIENTES DE ASIGNACIÓN -->
<div class="card shadow mb-4">
    <div class="card-header bg-gradient-warning">
        <h3 class="card-title text-white"><i class="fas fa-hourglass-half"></i> Envíos Pendientes de Asignación</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        @if($enviosPendientes->isEmpty())
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-muted">No hay envíos pendientes de asignación</p>
            </div>
        @else
            <div class="table-responsive">
                <table id="pendientesTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Almacén Destino</th>
                            <th>Fecha Estimada</th>
                            <th>Total</th>
                            <th>Creado</th>
                            <th width="250px">Asignar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enviosPendientes as $envio)
                        <tr>
                            <td><strong class="text-primary">{{ $envio->codigo }}</strong></td>
                            <td>
                                <i class="fas fa-warehouse text-success"></i> 
                                {{ $envio->almacenDestino->nombre ?? 'N/A' }}
                            </td>
                            <td>
                                <i class="fas fa-calendar"></i> 
                                {{ $envio->fecha_estimada_entrega ? \Carbon\Carbon::parse($envio->fecha_estimada_entrega)->format('d/m/Y') : 'N/A' }}
                                @if($envio->hora_estimada)
                                    <br><small><i class="fas fa-clock"></i> {{ $envio->hora_estimada }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $envio->productos->sum('cantidad') }} unidades
                                </span>
                                <br>
                                <span class="badge badge-secondary">
                                    {{ number_format($envio->productos->sum('total_peso'), 2) }} kg
                                </span>
                            </td>
                            <td>
                                <small>{{ \Carbon\Carbon::parse($envio->created_at)->diffForHumans() }}</small>
                            </td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-primary btn-block" 
                                        data-toggle="modal" 
                                        data-target="#asignarModal{{ $envio->id }}">
                                    <i class="fas fa-user-plus"></i> Asignar
                                </button>
                            </td>
                        </tr>

                        <!-- Modal de Asignación -->
                        <div class="modal fade" id="asignarModal{{ $envio->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-clipboard-check"></i> 
                                            Asignar Envío: {{ $envio->codigo }}
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('asignaciones.asignar') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="envio_id" value="{{ $envio->id }}">
                                        
                                        <div class="modal-body">
                                            <!-- Información del envío -->
                                            <div class="alert alert-info">
                                                <strong>Destino:</strong> {{ $envio->almacenDestino->nombre ?? 'N/A' }}<br>
                                                <strong>Productos:</strong> {{ $envio->productos->count() }} items<br>
                                                <strong>Peso Total:</strong> {{ number_format($envio->productos->sum('total_peso'), 2) }} kg<br>
                                                <strong>Valor Total:</strong> ${{ number_format($envio->productos->sum('total_precio'), 2) }}
                                            </div>

                                            <!-- Selección de Transportista -->
                                            <div class="form-group">
                                                <label for="transportista_{{ $envio->id }}">
                                                    <i class="fas fa-user"></i> Transportista <span class="text-danger">*</span>
                                                </label>
                                                <select name="transportista_id" 
                                                        id="transportista_{{ $envio->id }}" 
                                                        class="form-control select2" 
                                                        required>
                                                    <option value="">Seleccione un transportista</option>
                                                    @foreach($transportistas as $transportista)
                                                        <option value="{{ $transportista->id }}">
                                                            {{ $transportista->name }}
                                                            @if($transportista->licencia)
                                                                - Lic: {{ $transportista->licencia }}
                                                            @endif
                                                            @if($transportista->telefono)
                                                                - Tel: {{ $transportista->telefono }}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Selección de Vehículo -->
                                            <div class="form-group">
                                                <label for="vehiculo_{{ $envio->id }}">
                                                    <i class="fas fa-truck"></i> Vehículo <span class="text-danger">*</span>
                                                </label>
                                                <select name="vehiculo_id" 
                                                        id="vehiculo_{{ $envio->id }}" 
                                                        class="form-control select2" 
                                                        required>
                                                    <option value="">Seleccione un vehículo</option>
                                                    @foreach($vehiculos as $vehiculo)
                                                        <option value="{{ $vehiculo->id }}">
                                                            {{ $vehiculo->placa }} - 
                                                            {{ $vehiculo->marca }} {{ $vehiculo->modelo }}
                                                            ({{ $vehiculo->capacidad_carga }} kg)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                <i class="fas fa-times"></i> Cancelar
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-check"></i> Asignar Envío
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- ENVÍOS ASIGNADOS (Esperando Aceptación) -->
<div class="card shadow">
    <div class="card-header bg-gradient-info">
        <h3 class="card-title text-white"><i class="fas fa-user-check"></i> Envíos Asignados (Esperando Aceptación del Transportista)</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        @if($enviosAsignados->isEmpty())
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No hay envíos asignados actualmente</p>
            </div>
        @else
            <div class="table-responsive">
                <table id="asignadosTable" class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Almacén Destino</th>
                            <th>Transportista</th>
                            <th>Vehículo</th>
                            <th>Estado</th>
                            <th>Asignado</th>
                            <th width="120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enviosAsignados as $envio)
                        <tr>
                            <td><strong class="text-primary">{{ $envio->codigo }}</strong></td>
                            <td>
                                <i class="fas fa-warehouse text-success"></i> 
                                {{ $envio->almacenDestino->nombre ?? 'N/A' }}
                            </td>
                            <td>
                                <i class="fas fa-user"></i> 
                                {{ $envio->asignacion->transportista->name ?? 'N/A' }}
                                @if($envio->asignacion->transportista->licencia)
                                    <br><small class="text-muted">Lic: {{ $envio->asignacion->transportista->licencia }}</small>
                                @endif
                            </td>
                            <td>
                                <i class="fas fa-truck"></i> 
                                {{ $envio->asignacion->vehiculo->placa ?? 'N/A' }}
                                <br>
                                <small class="text-muted">
                                    {{ $envio->asignacion->vehiculo->marca ?? '' }} 
                                    {{ $envio->asignacion->vehiculo->modelo ?? '' }}
                                </small>
                            </td>
                            <td>
                                @if($envio->estado == 'asignado')
                                    <span class="badge badge-info">
                                        <i class="fas fa-clock"></i> Esperando Aceptación
                                    </span>
                                @elseif($envio->estado == 'aceptado')
                                    <span class="badge badge-primary">
                                        <i class="fas fa-check"></i> Aceptado
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    {{ \Carbon\Carbon::parse($envio->asignacion->fecha_asignacion)->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td>
                                @if($envio->estado == 'asignado')
                                    <form action="{{ route('asignaciones.remover', $envio->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('¿Remover esta asignación?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-block">
                                            <i class="fas fa-times"></i> Remover
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted"><small>Aceptado por transportista</small></span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Inicializar DataTables
        $('#pendientesTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[4, 'desc']], // Ordenar por fecha de creación
            pageLength: 10,
        });

        $('#asignadosTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[5, 'desc']], // Ordenar por fecha de asignación
            pageLength: 10,
        });

        // Inicializar Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    });
</script>
@endsection

@section('css')
<style>
    .badge-lg {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }
    
    .card {
        border-radius: 10px;
    }
    
    .modal-header {
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
    }
    
    .btn-block {
        white-space: nowrap;
    }
</style>
@endsection












