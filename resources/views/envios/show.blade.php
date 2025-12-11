@extends('adminlte::page')
@section('title', 'Detalle Envío - ' . $envio->codigo)
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-box text-primary"></i> Envío {{ $envio->codigo }}</h1>
        <div>
            <a href="{{ route('reportes.trazabilidad', $envio->id) }}" class="btn btn-info">
                <i class="fas fa-route"></i> Trazabilidad Completa
            </a>
            <a href="{{ route('envios.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
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

<div class="row">
    <!-- Información Principal del Envío -->
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-info-circle"></i> Información del Envío</h3>
            </div>
            <div class="card-body">
                <!-- Estado -->
                <div class="mb-4 text-center">
                    @if($envio->estado == 'pendiente')
                        <span class="badge badge-warning p-3" style="font-size: 1.2em;">
                            <i class="fas fa-clock fa-lg"></i> PENDIENTE
                        </span>
                    @elseif($envio->estado == 'aprobado')
                        <span class="badge badge-primary p-3" style="font-size: 1.2em;">
                            <i class="fas fa-check fa-lg"></i> APROBADO
                        </span>
                    @elseif($envio->estado == 'en_transito')
                        <span class="badge badge-info p-3" style="font-size: 1.2em;">
                            <i class="fas fa-truck fa-lg"></i> EN TRÁNSITO
                        </span>
                    @elseif($envio->estado == 'entregado')
                        <span class="badge badge-success p-3" style="font-size: 1.2em;">
                            <i class="fas fa-check-circle fa-lg"></i> ENTREGADO
                        </span>
                    @else
                        <span class="badge badge-secondary p-3" style="font-size: 1.2em;">
                            {{ strtoupper($envio->estado) }}
                        </span>
                    @endif
                </div>

                <!-- Datos del Envío -->
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-barcode"></i> Código:</strong></div>
                    <div class="col-md-8">{{ $envio->codigo }}</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-calendar"></i> Fecha Creación:</strong></div>
                    <div class="col-md-8">{{ $envio->created_at->format('d/m/Y H:i:s') }}</div>
                </div>

                @if($envio->fecha_entrega)
                <div class="row mb-3">
                    <div class="col-md-4"><strong><i class="fas fa-calendar-check"></i> Fecha Entrega:</strong></div>
                    <div class="col-md-8">{{ \Carbon\Carbon::parse($envio->fecha_entrega)->format('d/m/Y H:i:s') }}</div>
                </div>
                @endif

                <hr>

                <!-- Origen y Destino -->
                <h5 class="mb-3"><i class="fas fa-route"></i> Ruta del Envío</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-industry text-primary"></i> <strong>Origen (Planta)</strong></h6>
                                <p class="mb-1">{{ $planta->nombre ?? 'N/A' }}</p>
                                <small class="text-muted">{{ $planta->direccion_completa ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6><i class="fas fa-warehouse text-success"></i> <strong>Destino (Almacén)</strong></h6>
                                <p class="mb-1">{{ $envio->almacenDestino->nombre ?? 'N/A' }}</p>
                                <small class="text-muted">{{ $envio->almacenDestino->direccion_completa ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Productos -->
                <h5 class="mb-3"><i class="fas fa-shopping-basket"></i> Productos del Envío</h5>
                
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Tipo Empaque</th>
                            <th>Precio Unit.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalGeneral = 0; @endphp
                        @forelse($envio->productos as $producto)
                        @php $totalGeneral += $producto->total_precio; @endphp
                        <tr>
                            <td>{{ $producto->producto_nombre }}</td>
                            <td><span class="badge badge-info">{{ $producto->categoria ?? 'N/A' }}</span></td>
                            <td>{{ $producto->cantidad }} {{ $producto->unidad_medida ?? '' }}</td>
                            <td>{{ $producto->tipo_empaque ?? 'N/A' }}</td>
                            <td>Bs. {{ number_format($producto->precio_unitario ?? 0, 2) }}</td>
                            <td><strong>Bs. {{ number_format($producto->total_precio ?? 0, 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay productos registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-primary">
                            <td colspan="5" class="text-right"><strong>TOTAL:</strong></td>
                            <td><strong>Bs. {{ number_format($totalGeneral, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Incidentes Reportados -->
        @php
            $incidentes = DB::table('incidentes')->where('envio_id', $envio->id)->get();
        @endphp
        @if($incidentes->count() > 0)
        <div class="card shadow mt-3">
            <div class="card-header bg-gradient-danger">
                <h3 class="card-title text-white"><i class="fas fa-exclamation-triangle"></i> Incidentes Reportados ({{ $incidentes->count() }})</h3>
            </div>
            <div class="card-body">
                @foreach($incidentes as $incidente)
                <div class="alert alert-danger">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>
                                @php
                                    $tiposTexto = [
                                        'producto_danado' => 'Producto Dañado',
                                        'cantidad_incorrecta' => 'Cantidad Incorrecta',
                                        'producto_faltante' => 'Producto Faltante',
                                        'producto_equivocado' => 'Producto Equivocado',
                                        'empaque_malo' => 'Empaque en Mal Estado',
                                        'otro' => 'Otro Problema',
                                    ];
                                @endphp
                                <i class="fas fa-exclamation-circle"></i> 
                                {{ $tiposTexto[$incidente->tipo_incidente] ?? $incidente->tipo_incidente }}
                            </strong>
                            <span class="badge badge-{{ $incidente->estado == 'resuelto' ? 'success' : ($incidente->estado == 'en_proceso' ? 'warning' : 'danger') }} ml-2">
                                {{ strtoupper($incidente->estado) }}
                            </span>
                        </div>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($incidente->fecha_reporte)->format('d/m/Y H:i') }}</small>
                    </div>
                    <hr class="my-2">
                    <p class="mb-2"><strong>Descripción del almacén:</strong></p>
                    <p class="mb-2" style="background: #fff; padding: 10px; border-radius: 5px; border-left: 4px solid #dc3545;">
                        {{ $incidente->descripcion }}
                    </p>
                    @if($incidente->foto_url)
                    <p class="mb-1">
                        <a href="http://10.26.14.34:3001{{ $incidente->foto_url }}" target="_blank" class="btn btn-sm btn-info">
                            <i class="fas fa-camera"></i> Ver Foto de Evidencia
                        </a>
                    </p>
                    @endif
                    <a href="{{ route('incidentes.show', $incidente->id) }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-eye"></i> Ver Incidente Completo
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Panel Lateral -->
    <div class="col-md-4">
        <!-- Transportista Asignado -->
        <div class="card shadow mb-3">
            <div class="card-header bg-info">
                <h5 class="card-title text-white mb-0"><i class="fas fa-user-tie"></i> Transportista</h5>
            </div>
            <div class="card-body">
                @if($envio->asignacion && $envio->asignacion->transportista)
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-4x text-info"></i>
                    </div>
                    <p><strong>Nombre:</strong> {{ $envio->asignacion->transportista->name }}</p>
                    <p><strong>Email:</strong> {{ $envio->asignacion->transportista->email }}</p>
                    @if($envio->asignacion->vehiculo)
                    <hr>
                    <p><strong><i class="fas fa-truck"></i> Vehículo:</strong></p>
                    <p>{{ $envio->asignacion->vehiculo->placa ?? 'N/A' }} - {{ $envio->asignacion->vehiculo->tipo ?? '' }}</p>
                    @endif
                @else
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-user-slash fa-2x mb-2"></i>
                        <p class="mb-0">Sin transportista asignado</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Firma de Entrega -->
        @if($envio->firma_entrega)
        <div class="card shadow mb-3">
            <div class="card-header bg-success">
                <h5 class="card-title text-white mb-0"><i class="fas fa-signature"></i> Firma de Entrega</h5>
            </div>
            <div class="card-body text-center">
                <img src="{{ $envio->firma_entrega }}" class="img-fluid img-thumbnail" style="max-height: 150px;" alt="Firma">
                <p class="text-muted mt-2"><small>Firma del transportista al entregar</small></p>
            </div>
        </div>
        @endif

        <!-- Acciones -->
        <div class="card shadow mb-3">
            <div class="card-header bg-primary">
                <h5 class="card-title text-white mb-0"><i class="fas fa-cogs"></i> Acciones</h5>
            </div>
            <div class="card-body">
                @if($envio->estado == 'pendiente')
                <form action="{{ route('envios.aprobar', $envio) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block" onclick="return confirm('¿Aprobar este envío?')">
                        <i class="fas fa-check-circle"></i> Aprobar Envío
                    </button>
                </form>
                @endif

                <a href="{{ route('envios.edit', $envio) }}" class="btn btn-warning btn-block">
                    <i class="fas fa-edit"></i> Editar Envío
                </a>

                <a href="{{ route('envios.tracking', $envio) }}" class="btn btn-info btn-block">
                    <i class="fas fa-map-marker-alt"></i> Ver Tracking
                </a>

                <hr>

                <form action="{{ route('envios.destroy', $envio) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('¿Estás seguro de eliminar este envío?')">
                        <i class="fas fa-trash"></i> Eliminar Envío
                    </button>
                </form>
            </div>
        </div>

        <!-- Notas de Entrega -->
        @php
            $notaEntrega = DB::table('notas_venta')->where('envio_id', $envio->id)->first();
        @endphp
        @if($notaEntrega)
        <div class="card shadow">
            <div class="card-header bg-secondary">
                <h5 class="card-title text-white mb-0"><i class="fas fa-file-invoice"></i> Nota de Entrega</h5>
            </div>
            <div class="card-body">
                <p><strong>Número:</strong> {{ $notaEntrega->numero_nota }}</p>
                <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($notaEntrega->fecha_emision)->format('d/m/Y') }}</p>
                <p><strong>Total:</strong> Bs. {{ number_format($notaEntrega->total_precio, 2) }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('css')
<style>
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    .img-thumbnail {
        cursor: pointer;
        transition: transform 0.2s;
    }
    .img-thumbnail:hover {
        transform: scale(1.02);
    }
</style>
@endsection
