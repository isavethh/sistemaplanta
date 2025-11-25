@extends('adminlte::page')

@section('title', 'Crear Envío')

@section('content_header')
    <h1><i class="fas fa-shipping-fast"></i> Crear Envío desde Planta</h1>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-gradient-success">
        <h3 class="card-title text-white"><i class="fas fa-box"></i> Nuevo Envío de Productos</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('envios.store') }}" method="POST" id="formEnvio">
    @csrf
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Origen:</strong> Todos los envíos salen desde la Planta Principal.
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
                                <option value="{{ $almacen->id }}">
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

            <!-- Eliminado: La categoría ahora va por producto -->

            <!-- FECHAS RESPONSIVE -->
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Fecha Estimada de Entrega</label>
                        <input type="date" name="fecha_estimada_entrega" class="form-control" min="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Hora Estimada</label>
                        <input type="time" name="hora_estimada" class="form-control">
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- PRODUCTOS -->
            <div class="card" id="seccion-productos">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-box-open"></i> Productos del Envío
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

            <!-- TOTALES GRANDES RESPONSIVE -->
            <div class="card mt-4 border-success shadow-lg">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0"><i class="fas fa-calculator"></i> TOTALES DEL ENVÍO</h3>
                </div>
                <div class="card-body bg-light p-3 p-md-4">
                    <div class="row">
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <div class="p-3 p-md-4 bg-white rounded shadow-sm h-100">
                                <i class="fas fa-boxes fa-2x fa-md-3x text-primary mb-2 mb-md-3 d-block"></i>
                                <h2 class="mb-0 display-4 display-md-3"><span id="totalCantidad" class="text-primary">0</span></h2>
                                <p class="text-muted mb-0 small">unidades totales</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3 mb-md-0">
                            <div class="p-3 p-md-4 bg-white rounded shadow-sm h-100">
                                <i class="fas fa-weight fa-2x fa-md-3x text-info mb-2 mb-md-3 d-block"></i>
                                <h2 class="mb-0 display-4 display-md-3"><span id="totalPeso" class="text-info">0 kg</span></h2>
                                <p class="text-muted mb-0 small">peso total</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 p-md-4 bg-white rounded shadow-sm h-100">
                                <i class="fas fa-dollar-sign fa-2x fa-md-3x text-success mb-2 mb-md-3 d-block"></i>
                                <h2 class="mb-0 display-4 display-md-3">Bs <span id="totalPrecio" class="text-success">0.00</span></h2>
                                <p class="text-muted mb-0 small">precio en Bolivianos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OBSERVACIONES -->
            <div class="form-group mt-3">
                <label><i class="fas fa-comment"></i> Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="3" placeholder="Observaciones adicionales..."></textarea>
            </div>

            <hr>

            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save"></i> Crear Envío
            </button>
            <a href="{{ route('envios.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
// Productos hardcodeados por categoría (solo nombres)
const productosVerduras = [
    'Tomate',
    'Lechuga',
    'Zanahoria',
    'Cebolla',
    'Papa',
    'Brócoli',
    'Coliflor',
    'Pimiento',
    'Pepino',
    'Rábano'
];

const productosFrutas = [
    'Manzana',
    'Naranja',
    'Plátano',
    'Uva',
    'Sandía',
    'Melón',
    'Fresa',
    'Piña',
    'Papaya',
    'Mango'
];

let contadorProductos = 0;

// Ya no necesitamos esta función porque la categoría va por producto

function agregarProducto() {
    const container = document.getElementById('productos-container');
    const index = contadorProductos++;
    
    // Limpiar mensaje si es el primer producto
    if (index === 0) {
        container.innerHTML = '';
    }
    
    const html = `
        <div class="card mb-3 producto-item border-primary" id="producto-${index}">
            <div class="card-header bg-light">
                <strong>Producto #${index + 1}</strong>
                <button type="button" class="btn btn-sm btn-danger float-right" onclick="eliminarProducto(${index})">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <div class="form-group">
                            <label>Categoría *</label>
                            <select class="form-control categoria-producto" data-index="${index}" required onchange="cargarProductosCategoria(${index})">
                                <option value="">Seleccione categoría</option>
                                <option value="Verduras">🥬 Verduras</option>
                                <option value="Frutas">🍎 Frutas</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <div class="form-group">
                            <label>Producto *</label>
                            <select name="productos[${index}][producto_nombre]" id="producto-select-${index}" class="form-control producto-select" required disabled>
                                <option value="">Primero seleccione categoría</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-4 col-lg-2 mb-3">
                        <div class="form-group">
                            <label>Cantidad *</label>
                            <input type="number" name="productos[${index}][cantidad]" class="form-control cantidad-input" min="1" value="1" required onchange="calcularTotales()">
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-4 col-lg-2 mb-3">
                        <div class="form-group">
                            <label class="peso-label">Peso Unit. *</label>
                            <input type="number" name="productos[${index}][peso_unitario]" class="form-control peso-input peso-input-manual" step="0.01" min="0" placeholder="0.00" required disabled onchange="calcularTotales()">
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-4 col-lg-2 mb-3">
                        <div class="form-group">
                            <label>Precio (Bs) *</label>
                            <input type="number" name="productos[${index}][precio_unitario]" class="form-control precio-input precio-input-manual" step="0.01" min="0" placeholder="0.00" required disabled onchange="calcularTotales()">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <div class="form-group">
                            <label>Unidad de Medida</label>
                            <select name="productos[${index}][unidad_medida_id]" class="form-control unidad-medida-select" onchange="cambiarUnidadMedida(${index})">
                                <option value="" data-abrev="kg">Seleccione...</option>
                                @foreach($unidadesMedida as $um)
                                    <option value="{{ $um->id }}" data-abrev="{{ $um->abreviatura }}">{{ $um->nombre }} ({{ $um->abreviatura }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-6 mb-3">
                        <div class="form-group">
                            <label>Tipo de Empaque</label>
                            <select name="productos[${index}][tipo_empaque_id]" class="form-control">
                                <option value="">Seleccione...</option>
                                @foreach($tiposEmpaque as $te)
                                    <option value="{{ $te->id }}">{{ $te->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function cargarProductosCategoria(index) {
    const categoriaSelect = document.querySelector(`[data-index="${index}"]`);
    const categoria = categoriaSelect.value;
    const productoSelect = document.getElementById(`producto-select-${index}`);
    
    if (!categoria) {
        productoSelect.disabled = true;
        productoSelect.innerHTML = '<option value="">Primero seleccione categoría</option>';
        return;
    }
    
    const productos = categoria === 'Verduras' ? productosVerduras : productosFrutas;
    const emoji = categoria === 'Verduras' ? '🥬' : '🍎';
    
    let options = '<option value="">Seleccione un producto...</option>';
    productos.forEach(nombre => {
        options += `<option value="${nombre}">${emoji} ${nombre}</option>`;
    });
    
    productoSelect.innerHTML = options;
    productoSelect.disabled = false;
    
    // Habilitar inputs de peso y precio
    const productoCard = document.getElementById(`producto-${index}`);
    productoCard.querySelector('.peso-input-manual').disabled = false;
    productoCard.querySelector('.precio-input-manual').disabled = false;
}

function cambiarUnidadMedida(index) {
    const productoCard = document.getElementById(`producto-${index}`);
    const unidadSelect = productoCard.querySelector('.unidad-medida-select');
    const option = unidadSelect.options[unidadSelect.selectedIndex];
    const abreviatura = option.dataset.abrev || 'kg';
    
    // Cambiar el label del peso unitario
    const pesoLabel = productoCard.querySelector('.peso-label');
    pesoLabel.textContent = `Peso Unit. (${abreviatura}) *`;
    
    // Actualizar totales
    calcularTotales();
}

function eliminarProducto(index) {
    document.getElementById(`producto-${index}`).remove();
    calcularTotales();
}

function calcularTotales() {
    let totalCantidad = 0;
    let totalPrecio = 0;
    let pesosPorUnidad = {}; // Agrupar pesos por unidad
    
    document.querySelectorAll('.producto-item').forEach((item) => {
        const cantidad = parseFloat(item.querySelector('.cantidad-input')?.value || 0);
        const peso = parseFloat(item.querySelector('.peso-input')?.value || 0);
        const precio = parseFloat(item.querySelector('.precio-input')?.value || 0);
        
        totalCantidad += cantidad;
        totalPrecio += cantidad * precio;
        
        // Obtener la unidad de medida
        const unidadSelect = item.querySelector('.unidad-medida-select');
        const option = unidadSelect.options[unidadSelect.selectedIndex];
        const abrev = option.dataset.abrev || 'kg';
        
        // Sumar peso por unidad
        if (!pesosPorUnidad[abrev]) {
            pesosPorUnidad[abrev] = 0;
        }
        pesosPorUnidad[abrev] += cantidad * peso;
    });
    
    // Actualizar totales GRANDES
    document.getElementById('totalCantidad').textContent = totalCantidad;
    document.getElementById('totalPrecio').textContent = totalPrecio.toFixed(2);
    
    // Mostrar peso total
    let pesoTexto = '';
    let unidades = Object.keys(pesosPorUnidad);
    
    if (unidades.length === 0) {
        pesoTexto = '0 kg';
    } else if (unidades.length === 1) {
        // Una sola unidad
        pesoTexto = pesosPorUnidad[unidades[0]].toFixed(2) + ' ' + unidades[0];
    } else {
        // Múltiples unidades
        pesoTexto = unidades.map(u => pesosPorUnidad[u].toFixed(2) + ' ' + u).join(' + ');
    }
    
    document.getElementById('totalPeso').innerHTML = pesoTexto;
}

// Validación del formulario
document.getElementById('formEnvio').addEventListener('submit', function(e) {
    const productos = document.querySelectorAll('.producto-item');
    
    if (productos.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un producto');
        return false;
    }
});
</script>
@endsection
