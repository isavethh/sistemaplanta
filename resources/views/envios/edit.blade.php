@extends('adminlte::page')

@section('title', 'Editar Envío')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Editar Envío: {{ $envio->codigo }}</h1>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-gradient-warning">
        <h3 class="card-title text-dark"><i class="fas fa-box"></i> Modificar Envío de Productos</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('envios.update', $envio) }}" method="POST" id="formEnvio">
            @csrf
            @method('PUT')
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Código:</strong> {{ $envio->codigo }} | 
                <strong>Estado:</strong> 
                <span class="badge badge-{{ $envio->estado == 'pendiente' ? 'warning' : ($envio->estado == 'entregado' ? 'success' : 'info') }}">
                    {{ ucfirst($envio->estado) }}
                </span>
            </div>

            <div class="row">
                <!-- PLANTA (ORIGEN - Solo Lectura) -->
                <div class="col-12 col-lg-6 mb-3">
                    <div class="form-group">
                        <label><i class="fas fa-industry"></i> Origen (Planta) *</label>
                        <input type="text" class="form-control bg-light" value="{{ $planta->nombre ?? 'Planta Principal' }}" readonly>
                        <small class="text-muted d-block">📍 {{ $planta->direccion_completa ?? 'Santa Cruz' }}</small>
                    </div>
                </div>

                <!-- ALMACÉN DESTINO -->
                <div class="col-12 col-lg-6 mb-3">
                    <div class="form-group">
                        <label><i class="fas fa-warehouse"></i> Almacén Destino *</label>
                        <select name="almacen_destino_id" id="almacen_destino_id" class="form-control @error('almacen_destino_id') is-invalid @enderror" required>
                            <option value="">Seleccione almacén destino</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->id }}" {{ $envio->almacen_destino_id == $almacen->id ? 'selected' : '' }}>
                                    📦 {{ $almacen->nombre }} - {{ $almacen->direccion_completa }}
                                </option>
                            @endforeach
                        </select>
                        @error('almacen_destino_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- FECHAS -->
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Fecha Estimada de Entrega</label>
                        <input type="date" name="fecha_estimada_entrega" class="form-control" 
                               value="{{ $envio->fecha_estimada_entrega ? \Carbon\Carbon::parse($envio->fecha_estimada_entrega)->format('Y-m-d') : '' }}">
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Hora Estimada</label>
                        <input type="time" name="hora_estimada" class="form-control" value="{{ $envio->hora_estimada }}">
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- PRODUCTOS ACTUALES -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-box-open"></i> Productos del Envío
                    </h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                                <th>Peso Unit.</th>
                                <th>Precio Unit.</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($envio->productos as $producto)
                            <tr>
                                <td><strong>{{ $producto->producto_nombre }}</strong></td>
                                <td><span class="badge badge-info">{{ $producto->categoria ?? 'N/A' }}</span></td>
                                <td>{{ $producto->cantidad }}</td>
                                <td>{{ number_format($producto->peso_unitario, 2) }} kg</td>
                                <td>Bs. {{ number_format($producto->precio_unitario, 2) }}</td>
                                <td><strong>Bs. {{ number_format($producto->total_precio, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-success">
                                <td colspan="2"><strong>TOTALES</strong></td>
                                <td><strong>{{ $envio->productos->sum('cantidad') }}</strong></td>
                                <td><strong>{{ number_format($envio->productos->sum('total_peso'), 2) }} kg</strong></td>
                                <td>-</td>
                                <td><strong>Bs. {{ number_format($envio->productos->sum('total_precio'), 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Para modificar los productos, elimina este envío y crea uno nuevo.
                    </small>
                </div>
            </div>

            <!-- OBSERVACIONES -->
            <div class="form-group mt-3">
                <label><i class="fas fa-comment"></i> Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="3" placeholder="Observaciones adicionales...">{{ $envio->observaciones }}</textarea>
            </div>

            <hr>

            <button type="submit" class="btn btn-warning btn-lg">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
            <a href="{{ route('envios.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <a href="{{ route('envios.show', $envio) }}" class="btn btn-info btn-lg">
                <i class="fas fa-eye"></i> Ver Detalle
            </a>
        </form>
    </div>
</div>
@endsection
