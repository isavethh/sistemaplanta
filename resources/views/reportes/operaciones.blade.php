@extends('adminlte::page')

@section('title', 'Reporte de Operaciones')

@section('adminlte_css_pre')
    @include('layouts.preloader-killer')
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-truck-loading text-primary"></i> Reporte de Operaciones</h1>
            <small class="text-muted">Período: {{ \Carbon\Carbon::parse($filtros['fecha_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filtros['fecha_fin'])->format('d/m/Y') }}</small>
        </div>
        <div>
            <a href="{{ route('reportes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@endsection

@section('content')
<!-- Filtros -->
<div class="card card-outline card-primary mb-4">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-filter"></i> Filtros</h5>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.operaciones') }}" id="filtroForm">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" 
                               value="{{ $filtros['fecha_inicio'] }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" 
                               value="{{ $filtros['fecha_fin'] }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Estado</label>
                        <select name="estado" class="form-control">
                            <option value="">Todos</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="asignado" {{ request('estado') == 'asignado' ? 'selected' : '' }}>Asignado</option>
                            <option value="en_transito" {{ request('estado') == 'en_transito' ? 'selected' : '' }}>En Tránsito</option>
                            <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                            <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><i class="fas fa-warehouse"></i> Almacén</label>
                        <select name="almacen_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->id }}" {{ request('almacen_id') == $almacen->id ? 'selected' : '' }}>
                                    {{ $almacen->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Transportista</label>
                        <select name="transportista_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($transportistas as $transportista)
                                <option value="{{ $transportista->id }}" {{ request('transportista_id') == $transportista->id ? 'selected' : '' }}>
                                    {{ $transportista->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Estadísticas -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-primary">
            <div class="inner">
                <h3>{{ number_format($estadisticas['total_envios']) }}</h3>
                <p>Total Envíos</p>
            </div>
            <div class="icon"><i class="fas fa-shipping-fast"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ number_format($estadisticas['entregados']) }}</h3>
                <p>Entregados</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ number_format($estadisticas['total_peso'], 1) }} <small>kg</small></h3>
                <p>Peso Total</p>
            </div>
            <div class="icon"><i class="fas fa-weight"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>Bs {{ number_format($estadisticas['total_valor'], 2) }}</h3>
                <p>Valor Total</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>
</div>

<!-- Mini estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="info-box bg-light">
            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pendientes</span>
                <span class="info-box-number">{{ $estadisticas['pendientes'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-light">
            <span class="info-box-icon bg-info"><i class="fas fa-truck"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">En Tránsito</span>
                <span class="info-box-number">{{ $estadisticas['en_transito'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-light">
            <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Cancelados</span>
                <span class="info-box-number">{{ $estadisticas['cancelados'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-light">
            <span class="info-box-icon bg-success"><i class="fas fa-boxes"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Items</span>
                <span class="info-box-number">{{ number_format($estadisticas['total_items']) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Botones de exportación -->
<div class="row mb-3">
    <div class="col-12">
        <div class="btn-group">
            <a href="{{ route('reportes.operaciones.pdf', request()->all()) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
            <a href="{{ route('reportes.operaciones.csv', request()->all()) }}" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
        </div>
    </div>
</div>

<!-- Tabla de envíos -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-list"></i> Detalle de Envíos</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Almacén Destino</th>
                        <th>Transportista</th>
                        <th>Vehículo</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Peso (kg)</th>
                        <th class="text-right">Valor (Bs)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($envios as $envio)
                    <tr>
                        <td><strong>{{ $envio->codigo }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($envio->fecha_creacion)->format('d/m/Y') }}</td>
                        <td>
                            @switch($envio->estado)
                                @case('pendiente')
                                    <span class="badge badge-warning">Pendiente</span>
                                    @break
                                @case('asignado')
                                    <span class="badge badge-info">Asignado</span>
                                    @break
                                @case('en_transito')
                                    <span class="badge badge-primary">En Tránsito</span>
                                    @break
                                @case('entregado')
                                    <span class="badge badge-success">Entregado</span>
                                    @break
                                @case('cancelado')
                                    <span class="badge badge-danger">Cancelado</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ $envio->estado }}</span>
                            @endswitch
                        </td>
                        <td>{{ $envio->almacen_nombre ?? 'N/A' }}</td>
                        <td>{{ $envio->transportista_nombre ?? 'Sin asignar' }}</td>
                        <td>{{ $envio->vehiculo_placa ?? '-' }}</td>
                        <td class="text-right">{{ number_format($envio->total_cantidad) }}</td>
                        <td class="text-right">{{ number_format($envio->total_peso, 2) }}</td>
                        <td class="text-right">{{ number_format($envio->total_precio, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay envíos en el período seleccionado</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($envios->hasPages())
    <div class="card-footer">
        {{ $envios->appends(request()->all())->links() }}
    </div>
    @endif
</div>
@endsection

@section('css')
<style>
    .small-box {
        border-radius: 8px;
    }
    .info-box {
        border-radius: 8px;
    }
</style>
@endsection

