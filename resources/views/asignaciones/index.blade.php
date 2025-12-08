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
                                        class="btn btn-sm btn-primary btn-block btn-asignar" 
                                        data-envio-id="{{ $envio->id }}"
                                        data-envio-codigo="{{ $envio->codigo }}"
                                        data-envio-destino="{{ $envio->almacenDestino->nombre ?? 'N/A' }}"
                                        data-envio-productos="{{ $envio->productos->count() }}"
                                        data-envio-peso="{{ number_format($envio->productos->sum('total_peso'), 2) }}"
                                        data-envio-precio="{{ number_format($envio->productos->sum('total_precio'), 2) }}">
                                    <i class="fas fa-user-plus"></i> Asignar
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- MODAL ÚNICO DE ASIGNACIÓN (FUERA DEL DATATABLE) -->
<div class="modal fade" id="modalAsignar" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-check"></i> 
                    Asignar Envío: <span id="modal-envio-codigo"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('asignaciones.asignar') }}" method="POST" id="formAsignar">
                @csrf
                <input type="hidden" name="envio_id" id="modal-envio-id">
                
                <div class="modal-body">
                    <!-- Información del envío -->
                    <div class="alert alert-info" id="modal-info-envio">
                        <strong>Destino:</strong> <span id="modal-destino"></span><br>
                        <strong>Productos:</strong> <span id="modal-productos"></span> items<br>
                        <strong>Peso Total:</strong> <span id="modal-peso"></span> kg<br>
                        <strong>Valor Total:</strong> $<span id="modal-precio"></span>
                    </div>

                    <!-- Selección de Transportista -->
                    <div class="form-group">
                        <label for="modal-transportista">
                            <i class="fas fa-user"></i> Transportista <span class="text-danger">*</span>
                        </label>
                        <select name="transportista_id" id="modal-transportista" class="form-control" required>
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
                        <label for="modal-vehiculo">
                            <i class="fas fa-truck"></i> Vehículo <span class="text-danger">*</span>
                        </label>
                        <select name="vehiculo_id" id="modal-vehiculo" class="form-control" required>
                            <option value="">Seleccione un vehículo</option>
                            @foreach($vehiculos as $vehiculo)
                                <option value="{{ $vehiculo->id }}">
                                    {{ $vehiculo->placa }} - 
                                    {{ $vehiculo->tipoTransporte->nombre ?? $vehiculo->tipo_vehiculo ?? 'Sin tipo' }}
                                    (Lic: {{ $vehiculo->licencia_requerida ?? 'N/A' }})
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
                                {{ $envio->asignacion && $envio->asignacion->transportista ? $envio->asignacion->transportista->name : 'N/A' }}
                                @if($envio->asignacion && $envio->asignacion->transportista && $envio->asignacion->transportista->licencia)
                                    <br><small class="text-muted">Lic: {{ $envio->asignacion->transportista->licencia }}</small>
                                @endif
                            </td>
                            <td>
                                <i class="fas fa-truck"></i> 
                                {{ $envio->asignacion && $envio->asignacion->vehiculo ? $envio->asignacion->vehiculo->placa : 'N/A' }}
                                @if($envio->asignacion && $envio->asignacion->vehiculo)
                                    <br>
                                    <small class="text-muted">
                                        {{ $envio->asignacion->vehiculo->marca ?? '' }} 
                                        {{ $envio->asignacion->vehiculo->modelo ?? '' }}
                                    </small>
                                @endif
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
                                    {{ $envio->asignacion && $envio->asignacion->fecha_asignacion ? \Carbon\Carbon::parse($envio->asignacion->fecha_asignacion)->format('d/m/Y H:i') : 'N/A' }}
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

<!-- Sección de Envíos Rechazados -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-warning text-white">
        <h5 class="mb-0">
            <i class="fas fa-exclamation-triangle"></i> 
            Envíos Rechazados por Transportistas
            <span class="badge badge-light ml-2">{{ count($enviosRechazados) }}</span>
        </h5>
    </div>
    <div class="card-body">
        @if(count($enviosRechazados) == 0)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No hay envíos rechazados actualmente.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="rechazadosTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Destino</th>
                            <th>Transportista que Rechazó</th>
                            <th>Motivo</th>
                            <th>Fecha Rechazo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($enviosRechazados as $envio)
                        <tr>
                            <td><strong class="text-danger">{{ $envio->codigo }}</strong></td>
                            <td>
                                <i class="fas fa-warehouse text-success"></i> 
                                {{ $envio->almacenDestino->nombre ?? 'N/A' }}
                            </td>
                            <td>
                                <i class="fas fa-user text-warning"></i> 
                                {{ $envio->asignacion && $envio->asignacion->transportista ? $envio->asignacion->transportista->name : 'N/A' }}
                            </td>
                            <td>
                                <small>{{ $envio->motivo_rechazo ?? 'Sin motivo' }}</small>
                            </td>
                            <td>
                                <small>
                                    {{ $envio->fecha_rechazo ? \Carbon\Carbon::parse($envio->fecha_rechazo)->format('d/m/Y H:i') : 'N/A' }}
                                </small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" 
                                        data-toggle="modal" 
                                        data-target="#asignarModal"
                                        data-envio-id="{{ $envio->id }}"
                                        data-envio-codigo="{{ $envio->codigo }}">
                                    <i class="fas fa-redo"></i> Reasignar
                                </button>
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

        $('#rechazadosTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            order: [[4, 'desc']], // Ordenar por fecha de rechazo
            pageLength: 10,
        });

        // Manejar clic en botón asignar
        $(document).on('click', '.btn-asignar', function() {
            var envioId = $(this).data('envio-id');
            var envioCodigo = $(this).data('envio-codigo');
            var envioDestino = $(this).data('envio-destino');
            var envioProductos = $(this).data('envio-productos');
            var envioPeso = $(this).data('envio-peso');
            var envioPrecio = $(this).data('envio-precio');
            
            // Llenar datos en el modal
            $('#modal-envio-id').val(envioId);
            $('#modal-envio-codigo').text(envioCodigo);
            $('#modal-destino').text(envioDestino);
            $('#modal-productos').text(envioProductos);
            $('#modal-peso').text(envioPeso);
            $('#modal-precio').text(envioPrecio);
            
            // Resetear selects
            $('#modal-transportista').val('');
            $('#modal-vehiculo').val('');
            
            // Abrir modal
            $('#modalAsignar').modal('show');
        });

        // Limpiar formulario al cerrar
        $('#modalAsignar').on('hidden.bs.modal', function () {
            $('#formAsignar')[0].reset();
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












