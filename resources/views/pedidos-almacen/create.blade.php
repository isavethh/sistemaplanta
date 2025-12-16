@extends('adminlte::page')
@section('title', 'Crear Pedido')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-shopping-cart"></i> Crear Nuevo Pedido</h1>
        <a href="{{ route('pedidos-almacen.index') }}" class="btn btn-secondary">
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
    <div class="card-header bg-gradient-success">
        <h3 class="card-title text-white"><i class="fas fa-box"></i> Nuevo Pedido de Almacén</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('pedidos-almacen.store') }}" method="POST" id="formPedido">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-warehouse"></i> Almacén *</label>
                        <select name="almacen_id" id="almacen_id" class="form-control @error('almacen_id') is-invalid @enderror" required>
                            <option value="">Seleccione un almacén</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->id }}" {{ old('almacen_id') == $almacen->id ? 'selected' : '' }}>
                                    {{ $almacen->nombre }} - {{ $almacen->direccion_completa ?? 'Santa Cruz, Bolivia' }}
                                </option>
                            @endforeach
                        </select>
                        @error('almacen_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        @if($almacenes->isEmpty())
                            <small class="text-info">
                                <i class="fas fa-info-circle"></i> 
                                <a href="{{ route('almacenes.create') }}">Crear un almacén primero</a>
                            </small>
                        @endif
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Fecha Requerida *</label>
                        <input type="date" name="fecha_requerida" id="fecha_requerida" 
                               class="form-control @error('fecha_requerida') is-invalid @enderror" 
                               value="{{ old('fecha_requerida', date('Y-m-d')) }}" 
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
                               value="{{ old('hora_requerida') }}">
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
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> Haz clic en "Agregar Producto" para empezar
                        </div>
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
                                <h2 class="mb-0"><span id="totalCantidad" class="text-primary">0</span></h2>
                                <small class="text-muted">Total Cantidad</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-white rounded shadow-sm">
                                <i class="fas fa-weight fa-2x text-info mb-2"></i>
                                <h2 class="mb-0"><span id="totalPeso" class="text-info">0.00</span> <small>kg</small></h2>
                                <small class="text-muted">Total Peso</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-white rounded shadow-sm">
                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                <h2 class="mb-0">Bs <span id="totalPrecio" class="text-success">0.00</span></h2>
                                <small class="text-muted">Total Precio</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4">
                <label><i class="fas fa-comment"></i> Observaciones</label>
                <textarea name="observaciones" id="observaciones" class="form-control" rows="3" 
                          placeholder="Observaciones adicionales sobre el pedido...">{{ old('observaciones') }}</textarea>
            </div>

            <hr>
            <div class="form-group">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-paper-plane"></i> Crear Pedido y Enviar a Trazabilidad
                </button>
                <a href="{{ route('pedidos-almacen.index') }}" class="btn btn-secondary">
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
    let contadorProductos = 0;
    let productosAgregados = [];

    function agregarProducto() {
        contadorProductos++;
        const productoHtml = `
            <div class="card mb-3 producto-item" data-index="${contadorProductos}">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-box"></i> Producto ${contadorProductos}</h5>
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
                                <select name="productos[${contadorProductos}][producto_nombre]" 
                                        class="form-control producto-select" required
                                        onchange="cargarDatosProducto(${contadorProductos}, this.value)">
                                    <option value="">Seleccione un producto</option>
                                    ${productosData.map(p => `
                                        <option value="${p.nombre}" 
                                                data-codigo="${p.codigo || ''}"
                                                data-peso="${p.peso || 0}"
                                                data-precio="${p.precio || 0}">
                                            ${p.nombre} ${p.codigo ? '(' + p.codigo + ')' : ''}
                                        </option>
                                    `).join('')}
                                </select>
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
                                <label>Peso Unitario (kg) *</label>
                                <input type="number" step="0.01" name="productos[${contadorProductos}][peso_unitario]" 
                                       class="form-control peso-input" min="0" value="0" required
                                       placeholder="Ej: 0.900"
                                       onchange="calcularTotalesProducto(${contadorProductos})">
                                <small class="text-muted">Ingrese en kilogramos (ej: 0.900 para 900g)</small>
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
                            <input type="hidden" name="productos[${contadorProductos}][producto_codigo]" class="codigo-input">
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

    function cargarDatosProducto(index, nombreProducto) {
        const option = $(`.producto-item[data-index="${index}"] .producto-select option:selected`);
        const codigo = option.data('codigo') || '';
        let peso = parseFloat(option.data('peso')) || 0;
        const precio = parseFloat(option.data('precio')) || 0;
        
        // Convertir peso de gramos a kilogramos si es mayor a 10 (asumiendo que viene en gramos)
        // Si el peso es mayor a 10, probablemente está en gramos, convertir a kg
        if (peso > 10) {
            peso = peso / 1000; // Convertir gramos a kilogramos
        }
        
        $(`.producto-item[data-index="${index}"] .codigo-input`).val(codigo);
        $(`.producto-item[data-index="${index}"] .peso-input`).val(peso.toFixed(2));
        $(`.producto-item[data-index="${index}"] .precio-input`).val(precio);
        
        calcularTotalesProducto(index);
    }

    function calcularTotalesProducto(index) {
        const cantidad = parseFloat($(`.producto-item[data-index="${index}"] .cantidad-input`).val()) || 0;
        const pesoUnitario = parseFloat($(`.producto-item[data-index="${index}"] .peso-input`).val()) || 0;
        const precioUnitario = parseFloat($(`.producto-item[data-index="${index}"] .precio-input`).val()) || 0;
        
        // Validar que el peso unitario no sea mayor a 10 (probablemente está en gramos)
        if (pesoUnitario > 10) {
            showAlert('⚠️ Advertencia: El peso parece estar en gramos. Por favor, ingrese el peso en kilogramos.<br><br>Ejemplo: Para 900g, ingrese 0.900 kg', 'Advertencia', 'fa-exclamation-triangle', 'bg-warning');
            // Convertir automáticamente de gramos a kilogramos
            pesoUnitario = pesoUnitario / 1000;
            $(`.producto-item[data-index="${index}"] .peso-input`).val(pesoUnitario.toFixed(2));
        }
        
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

    // Validar formulario antes de enviar
    $('#formPedido').on('submit', function(e) {
        if (productosAgregados.length === 0) {
            e.preventDefault();
            showAlert('Debe agregar al menos un producto al pedido.', 'Validación', 'fa-exclamation-triangle', 'bg-warning');
            return false;
        }
        
        // Validar que todos los productos tengan datos
        let productosValidos = true;
        productosAgregados.forEach(index => {
            const nombre = $(`.producto-item[data-index="${index}"] .producto-select`).val();
            const cantidad = $(`.producto-item[data-index="${index}"] .cantidad-input`).val();
            if (!nombre || !cantidad || cantidad <= 0) {
                productosValidos = false;
            }
        });
        
        if (!productosValidos) {
            e.preventDefault();
            showAlert('Por favor complete todos los datos de los productos.', 'Validación', 'fa-exclamation-triangle', 'bg-warning');
            return false;
        }
    });
</script>
@include('partials.modal-alert')
@endsection

