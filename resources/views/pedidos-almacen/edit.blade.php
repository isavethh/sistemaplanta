@extends('adminlte::page')
@section('title', 'Editar Pedido')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit"></i> Editar Pedido {{ $pedido->codigo }}</h1>
        <a href="{{ route('pedidos-almacen.show', $pedido->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@endsection

@section('content')
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card shadow">
    <div class="card-header bg-gradient-warning">
        <h3 class="card-title text-white"><i class="fas fa-edit"></i> Modificar Pedido</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('pedidos-almacen.update', $pedido->id) }}" method="POST" id="formPedido">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-warehouse"></i> Almacén *</label>
                        <select name="almacen_id" id="almacen_id" class="form-control @error('almacen_id') is-invalid @enderror" required>
                            <option value="">Seleccione un almacén</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->id }}" {{ old('almacen_id', $pedido->almacen_id) == $almacen->id ? 'selected' : '' }}>
                                    {{ $almacen->nombre }} - {{ $almacen->direccion_completa ?? 'Santa Cruz, Bolivia' }}
                                </option>
                            @endforeach
                        </select>
                        @error('almacen_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Fecha Requerida *</label>
                        <input type="date" name="fecha_requerida" id="fecha_requerida" 
                               class="form-control @error('fecha_requerida') is-invalid @enderror" 
                               value="{{ old('fecha_requerida', $pedido->fecha_requerida->format('Y-m-d')) }}" 
                               min="{{ date('Y-m-d') }}" required>
                        @error('fecha_requerida')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Hora Requerida</label>
                        <input type="time" name="hora_requerida" id="hora_requerida" 
                               class="form-control @error('hora_requerida') is-invalid @enderror" 
                               value="{{ old('hora_requerida', $pedido->hora_requerida) }}">
                        @error('hora_requerida')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Productos -->
            <div class="card" id="seccion-productos">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-box-open"></i> Productos del Pedido
                        <button type="button" class="btn btn-success float-right" onclick="agregarProducto()">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </h4>
                </div>
                <div class="card-body">
                    <div id="productos-container">
                        @foreach($pedido->productos as $index => $producto)
                        <div class="card mb-3 producto-item" data-index="{{ $index }}">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-box"></i> Producto {{ $index + 1 }}</h5>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto({{ $index }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Producto *</label>
                                            <input type="text" name="productos[{{ $index }}][producto_nombre]" 
                                                   class="form-control producto-select" 
                                                   value="{{ $producto->producto_nombre }}" required>
                                            <input type="hidden" name="productos[{{ $index }}][producto_codigo]" 
                                                   value="{{ $producto->producto_codigo }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Cantidad *</label>
                                            <input type="number" name="productos[{{ $index }}][cantidad]" 
                                                   class="form-control cantidad-input" min="1" 
                                                   value="{{ $producto->cantidad }}" required
                                                   onchange="calcularTotalesProducto({{ $index }})">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Peso Unitario (kg)</label>
                                            <input type="number" step="0.01" name="productos[{{ $index }}][peso_unitario]" 
                                                   class="form-control peso-input" min="0" 
                                                   value="{{ $producto->peso_unitario }}"
                                                   onchange="calcularTotalesProducto({{ $index }})">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Precio Unitario</label>
                                            <input type="number" step="0.01" name="productos[{{ $index }}][precio_unitario]" 
                                                   class="form-control precio-input" min="0" 
                                                   value="{{ $producto->precio_unitario }}"
                                                   onchange="calcularTotalesProducto({{ $index }})">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-light mt-4">
                                            <small>
                                                <strong>Total:</strong> 
                                                <span class="total-peso-producto">{{ number_format($producto->total_peso, 2) }}</span> kg - 
                                                Bs <span class="total-precio-producto">{{ number_format($producto->total_precio, 2) }}</span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Totales -->
            <div class="card mt-4 border-success shadow-lg">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="fas fa-calculator"></i> TOTALES DEL PEDIDO</h3>
                </div>
                <div class="card-body bg-light">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-white rounded shadow-sm">
                                <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                <h2 class="mb-0"><span id="totalCantidad" class="text-primary">{{ $pedido->productos->sum('cantidad') }}</span></h2>
                                <small class="text-muted">Total Cantidad</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-white rounded shadow-sm">
                                <i class="fas fa-weight fa-2x text-info mb-2"></i>
                                <h2 class="mb-0"><span id="totalPeso" class="text-info">{{ number_format($pedido->productos->sum('total_peso'), 2) }}</span> <small>kg</small></h2>
                                <small class="text-muted">Total Peso</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-white rounded shadow-sm">
                                <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                                <h2 class="mb-0">Bs <span id="totalPrecio" class="text-success">{{ number_format($pedido->productos->sum('total_precio'), 2) }}</span></h2>
                                <small class="text-muted">Total Precio</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4">
                <label><i class="fas fa-comment"></i> Observaciones</label>
                <textarea name="observaciones" id="observaciones" class="form-control" rows="3" 
                          placeholder="Observaciones adicionales sobre el pedido...">{{ old('observaciones', $pedido->observaciones) }}</textarea>
            </div>

            <hr>
            <div class="form-group">
                <button type="submit" class="btn btn-warning btn-lg">
                    <i class="fas fa-save"></i> Actualizar Pedido
                </button>
                <a href="{{ route('pedidos-almacen.show', $pedido->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
    let productosData = @json($productos ?? []);
    let contadorProductos = {{ $pedido->productos->count() }};
    let productosAgregados = [@foreach($pedido->productos as $index => $p){{ $index }}{{ !$loop->last ? ',' : '' }}@endforeach];

    function agregarProducto() {
        contadorProductos++;
        const productoHtml = `
            <div class="card mb-3 producto-item" data-index="${contadorProductos}">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-box"></i> Producto ${contadorProductos + 1}</h5>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${contadorProductos})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Producto *</label>
                                <input type="text" name="productos[${contadorProductos}][producto_nombre]" 
                                       class="form-control producto-select" required>
                                <input type="hidden" name="productos[${contadorProductos}][producto_codigo]">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Cantidad *</label>
                                <input type="number" name="productos[${contadorProductos}][cantidad]" 
                                       class="form-control cantidad-input" min="1" value="1" required
                                       onchange="calcularTotalesProducto(${contadorProductos})">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Peso Unitario (kg)</label>
                                <input type="number" step="0.01" name="productos[${contadorProductos}][peso_unitario]" 
                                       class="form-control peso-input" min="0" value="0"
                                       onchange="calcularTotalesProducto(${contadorProductos})">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Precio Unitario</label>
                                <input type="number" step="0.01" name="productos[${contadorProductos}][precio_unitario]" 
                                       class="form-control precio-input" min="0" value="0"
                                       onchange="calcularTotalesProducto(${contadorProductos})">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-light mt-4">
                                <small>
                                    <strong>Total:</strong> 
                                    <span class="total-peso-producto">0.00</span> kg - 
                                    Bs <span class="total-precio-producto">0.00</span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#productos-container').append(productoHtml);
        productosAgregados.push(contadorProductos);
        actualizarTotales();
    }

    function eliminarProducto(index) {
        $(`.producto-item[data-index="${index}"]`).remove();
        productosAgregados = productosAgregados.filter(i => i !== index);
        actualizarTotales();
    }

    function calcularTotalesProducto(index) {
        const cantidad = parseFloat($(`.producto-item[data-index="${index}"] .cantidad-input`).val()) || 0;
        const pesoUnitario = parseFloat($(`.producto-item[data-index="${index}"] .peso-input`).val()) || 0;
        const precioUnitario = parseFloat($(`.producto-item[data-index="${index}"] .precio-input`).val()) || 0;
        
        const totalPeso = cantidad * pesoUnitario;
        const totalPrecio = cantidad * precioUnitario;
        
        $(`.producto-item[data-index="${index}"] .total-peso-producto`).text(totalPeso.toFixed(2));
        $(`.producto-item[data-index="${index}"] .total-precio-producto`).text(totalPrecio.toFixed(2));
        
        actualizarTotales();
    }

    function actualizarTotales() {
        let totalCantidad = 0;
        let totalPeso = 0;
        let totalPrecio = 0;
        
        productosAgregados.forEach(index => {
            const cantidad = parseFloat($(`.producto-item[data-index="${index}"] .cantidad-input`).val()) || 0;
            const pesoUnitario = parseFloat($(`.producto-item[data-index="${index}"] .peso-input`).val()) || 0;
            const precioUnitario = parseFloat($(`.producto-item[data-index="${index}"] .precio-input`).val()) || 0;
            
            totalCantidad += cantidad;
            totalPeso += cantidad * pesoUnitario;
            totalPrecio += cantidad * precioUnitario;
        });
        
        $('#totalCantidad').text(totalCantidad);
        $('#totalPeso').text(totalPeso.toFixed(2));
        $('#totalPrecio').text(totalPrecio.toFixed(2));
    }

    // Inicializar totales al cargar
    $(document).ready(function() {
        actualizarTotales();
    });
</script>
@endsection

