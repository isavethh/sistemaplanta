@extends('adminlte::page')
@section('title', 'Incidentes')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-exclamation-triangle text-danger"></i> Gestión de Incidentes</h1>
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

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $estadisticas['pendientes'] }}</h3>
                <p>Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
            <a href="{{ route('incidentes.index', ['estado' => 'pendiente']) }}" class="small-box-footer">
                Ver pendientes <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $estadisticas['en_proceso'] }}</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon">
                <i class="fas fa-spinner"></i>
            </div>
            <a href="{{ route('incidentes.index', ['estado' => 'en_proceso']) }}" class="small-box-footer">
                Ver en proceso <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $estadisticas['resueltos'] }}</h3>
                <p>Resueltos</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('incidentes.index', ['estado' => 'resuelto']) }}" class="small-box-footer">
                Ver resueltos <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $estadisticas['total'] }}</h3>
                <p>Total Incidentes</p>
            </div>
            <div class="icon">
                <i class="fas fa-list"></i>
            </div>
            <a href="{{ route('incidentes.index') }}" class="small-box-footer">
                Ver todos <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @if(isset($estadisticas['solicitan_ayuda']) && $estadisticas['solicitan_ayuda'] > 0)
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $estadisticas['solicitan_ayuda'] }}</h3>
                <p>Solicitan Ayuda</p>
            </div>
            <div class="icon">
                <i class="fas fa-bell"></i>
            </div>
            <a href="{{ route('incidentes.index', ['solicitar_ayuda' => '1']) }}" class="small-box-footer">
                Ver solicitudes <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endif
</div>

<!-- Alerta de Solicitudes de Ayuda -->
@if(isset($estadisticas['solicitan_ayuda']) && $estadisticas['solicitan_ayuda'] > 0)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <h5><i class="fas fa-exclamation-triangle"></i> <strong>Solicitudes de Ayuda Pendientes</strong></h5>
    <p class="mb-0">
        Hay <strong>{{ $estadisticas['solicitan_ayuda'] }}</strong> incidente(s) que solicitan ayuda urgente del administrador.
        <a href="{{ route('incidentes.index', ['solicitar_ayuda' => '1']) }}" class="btn btn-warning btn-sm ml-2">
            <i class="fas fa-bell"></i> Ver Solicitudes de Ayuda
        </a>
    </p>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<!-- Filtros -->
<div class="card shadow mb-4">
    <div class="card-header bg-light">
        <form action="{{ route('incidentes.index') }}" method="GET" class="form-inline">
            <div class="form-group mr-3">
                <label class="mr-2">Estado:</label>
                <select name="estado" class="form-control form-control-sm">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="resuelto" {{ request('estado') == 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <label class="mr-2">Tipo:</label>
                <select name="tipo" class="form-control form-control-sm">
                    <option value="">Todos</option>
                    <option value="producto_danado" {{ request('tipo') == 'producto_danado' ? 'selected' : '' }}>Producto Dañado</option>
                    <option value="cantidad_incorrecta" {{ request('tipo') == 'cantidad_incorrecta' ? 'selected' : '' }}>Cantidad Incorrecta</option>
                    <option value="producto_faltante" {{ request('tipo') == 'producto_faltante' ? 'selected' : '' }}>Producto Faltante</option>
                    <option value="producto_equivocado" {{ request('tipo') == 'producto_equivocado' ? 'selected' : '' }}>Producto Equivocado</option>
                    <option value="empaque_malo" {{ request('tipo') == 'empaque_malo' ? 'selected' : '' }}>Empaque en Mal Estado</option>
                    <option value="otro" {{ request('tipo') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
            <div class="form-group mr-3">
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar..." value="{{ request('buscar') }}">
            </div>
            <div class="form-group mr-3">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="solicitar_ayuda" id="solicitar_ayuda" value="1" 
                           class="custom-control-input" {{ request('solicitar_ayuda') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="solicitar_ayuda">
                        <strong class="text-warning">Solo solicitudes de ayuda</strong>
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm mr-2">
                <i class="fas fa-search"></i> Filtrar
            </button>
            <a href="{{ route('incidentes.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-times"></i> Limpiar
            </a>
        </form>
    </div>
</div>

<!-- Lista de Incidentes -->
<div class="card shadow">
    <div class="card-header bg-gradient-danger">
        <h3 class="card-title text-white"><i class="fas fa-exclamation-triangle"></i> Listado de Incidentes</h3>
    </div>
    <div class="card-body">
        @if($incidentes->count() > 0)
        <table class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th width="80">ID</th>
                    <th>Envío</th>
                    <th>Almacén</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Foto</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th width="150">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incidentes as $incidente)
                <tr class="{{ ($incidente->solicitar_ayuda ?? false) ? 'table-warning' : ($incidente->estado == 'pendiente' ? 'table-danger' : ($incidente->estado == 'en_proceso' ? 'table-warning' : '')) }}">
                    <td><strong>#{{ $incidente->id }}</strong></td>
                    <td>
                        <a href="{{ route('envios.show', $incidente->envio_id) }}">
                            <i class="fas fa-box"></i> {{ $incidente->envio_codigo ?? 'N/A' }}
                        </a>
                    </td>
                    <td>{{ $incidente->almacen_nombre ?? 'N/A' }}</td>
                    <td>
                        @php
                            $tiposIconos = [
                                'producto_danado' => ['icon' => 'box-open', 'color' => 'danger', 'label' => 'Producto Dañado'],
                                'cantidad_incorrecta' => ['icon' => 'calculator', 'color' => 'warning', 'label' => 'Cantidad Incorrecta'],
                                'producto_faltante' => ['icon' => 'minus-circle', 'color' => 'danger', 'label' => 'Producto Faltante'],
                                'producto_equivocado' => ['icon' => 'exchange-alt', 'color' => 'info', 'label' => 'Producto Equivocado'],
                                'empaque_malo' => ['icon' => 'box', 'color' => 'secondary', 'label' => 'Empaque Malo'],
                                'accidente_vehiculo' => ['icon' => 'car-crash', 'color' => 'danger', 'label' => 'Accidente de Vehículo'],
                                'averia_vehiculo' => ['icon' => 'tools', 'color' => 'warning', 'label' => 'Avería de Vehículo'],
                                'robo' => ['icon' => 'shield-alt', 'color' => 'danger', 'label' => 'Robo'],
                                'perdida_mercancia' => ['icon' => 'box-open', 'color' => 'danger', 'label' => 'Pérdida de Mercancía'],
                                'daño_mercancia' => ['icon' => 'exclamation-triangle', 'color' => 'warning', 'label' => 'Daño de Mercancía'],
                                'retraso' => ['icon' => 'clock', 'color' => 'info', 'label' => 'Retraso en Entrega'],
                                'problema_ruta' => ['icon' => 'route', 'color' => 'warning', 'label' => 'Problema en Ruta'],
                                'problema_cliente' => ['icon' => 'user-times', 'color' => 'info', 'label' => 'Problema con Cliente'],
                                'otro' => ['icon' => 'question-circle', 'color' => 'dark', 'label' => 'Otro'],
                            ];
                            $tipo = $tiposIconos[$incidente->tipo_incidente] ?? ['icon' => 'question', 'color' => 'secondary', 'label' => ucfirst(str_replace('_', ' ', $incidente->tipo_incidente))];
                        @endphp
                        <span class="badge badge-{{ $tipo['color'] }}">
                            <i class="fas fa-{{ $tipo['icon'] }}"></i> {{ $tipo['label'] }}
                        </span>
                        @if($incidente->solicitar_ayuda ?? false)
                            <br><small class="badge badge-warning mt-1">
                                <i class="fas fa-exclamation-triangle"></i> Solicita Ayuda
                            </small>
                        @endif
                    </td>
                    <td>
                        {{ Str::limit($incidente->descripcion, 50) }}
                        @if($incidente->solicitar_ayuda ?? false)
                            <br><small class="text-warning">
                                <i class="fas fa-bell"></i> <strong>Ayuda solicitada</strong>
                            </small>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($incidente->foto_url)
                            <a href="http://10.26.14.34:3001{{ $incidente->foto_url }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-image"></i>
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($incidente->estado == 'pendiente')
                            <span class="badge badge-danger"><i class="fas fa-clock"></i> Pendiente</span>
                        @elseif($incidente->estado == 'en_proceso')
                            <span class="badge badge-warning"><i class="fas fa-spinner fa-spin"></i> En Proceso</span>
                        @elseif($incidente->estado == 'resuelto')
                            <span class="badge badge-success"><i class="fas fa-check"></i> Resuelto</span>
                        @endif
                    </td>
                    <td>
                        <small>{{ \Carbon\Carbon::parse($incidente->fecha_reporte)->format('d/m/Y H:i') }}</small>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('incidentes.show', $incidente->id) }}" class="btn btn-primary" title="Ver Detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($incidente->estado != 'resuelto')
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalResolver{{ $incidente->id }}" title="Resolver">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                        </div>
                        
                        <!-- Modal Resolver -->
                        <div class="modal fade" id="modalResolver{{ $incidente->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('incidentes.cambiarEstado', $incidente->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title"><i class="fas fa-check-circle"></i> Resolver Incidente #{{ $incidente->id }}</h5>
                                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="estado" value="resuelto">
                                            <div class="form-group">
                                                <label>Notas de Resolución</label>
                                                <textarea name="notas" class="form-control" rows="3" placeholder="Describe cómo se resolvió el incidente..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Marcar como Resuelto</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="d-flex justify-content-center">
            {{ $incidentes->appends(request()->query())->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
            <h4>¡No hay incidentes!</h4>
            <p class="text-muted">No se encontraron incidentes con los filtros seleccionados.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@section('css')
<style>
    .small-box .icon i {
        font-size: 70px;
    }
    .table-danger {
        background-color: #f8d7da !important;
    }
    .table-warning {
        background-color: #fff3cd !important;
    }
</style>
@endsection
