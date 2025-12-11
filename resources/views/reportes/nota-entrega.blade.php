@extends('adminlte::page')

@section('title', 'Notas de Entrega')

@section('adminlte_css_pre')
    @include('layouts.preloader-killer')
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-file-signature text-success"></i> Notas de Entrega</h1>
            <small class="text-muted">Documentos legales de recepción de mercancías</small>
        </div>
        <a href="{{ route('reportes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@endsection

@section('content')
<!-- Información Legal -->
<div class="callout callout-success mb-4">
    <h5><i class="fas fa-gavel"></i> Validez Legal - Bolivia</h5>
    <p class="mb-0">
        Las notas de entrega generadas cumplen con los requisitos establecidos en el 
        <strong>Código de Comercio de Bolivia (Art. 815-819)</strong> para el transporte de mercancías.
        Incluyen: identificación de las partes, descripción de mercancía, fecha y hora de entrega, 
        y firma de conformidad.
    </p>
</div>

<!-- Búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.nota-entrega') }}">
            <div class="input-group">
                <input type="text" name="buscar" class="form-control" 
                       placeholder="Buscar por código de envío o almacén..." 
                       value="{{ request('buscar') }}">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    @if(request('buscar'))
                    <a href="{{ route('reportes.nota-entrega') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Entregas -->
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="card-title m-0"><i class="fas fa-list"></i> Envíos Entregados</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>Código</th>
                        <th>Fecha Entrega</th>
                        <th>Almacén Destino</th>
                        <th>Transportista</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enviosEntregados as $envio)
                    <tr>
                        <td>
                            <strong>{{ $envio->codigo }}</strong>
                            <br>
                            <small class="text-muted">
                                Creado: {{ \Carbon\Carbon::parse($envio->fecha_creacion)->format('d/m/Y') }}
                            </small>
                        </td>
                        <td>
                            @if($envio->fecha_entrega)
                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i>
                                    {{ \Carbon\Carbon::parse($envio->fecha_entrega)->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            {{ $envio->almacen_nombre ?? 'N/A' }}
                            <br>
                            <small class="text-muted">{{ Str::limit($envio->almacen_direccion, 40) }}</small>
                        </td>
                        <td>{{ $envio->transportista_nombre ?? 'N/A' }}</td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('reportes.nota-entrega.html', $envio->id) }}" 
                                   class="btn btn-sm btn-info" title="Ver HTML" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('reportes.nota-entrega.pdf', $envio->id) }}" 
                                   class="btn btn-sm btn-danger" title="Descargar PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay envíos entregados</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($enviosEntregados->hasPages())
    <div class="card-footer">
        {{ $enviosEntregados->appends(request()->all())->links() }}
    </div>
    @endif
</div>
@endsection

