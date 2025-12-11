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

@section('css')
<style>
/* Animación de entrada del visualizador */
.empaque-visual-container {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Info del empaque */
.empaque-info {
    text-align: center;
}

.empaque-icono {
    font-size: 3rem;
    margin-bottom: 10px;
    animation: iconoPulse 2s ease-in-out infinite;
}

@keyframes iconoPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.empaque-specs {
    text-align: left;
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

.medida-detalle {
    font-size: 0.9rem;
    margin-bottom: 8px;
    padding: 5px;
    background: white;
    border-radius: 4px;
}

.medida-detalle i {
    width: 20px;
    text-align: center;
}

.medida-detalle span {
    color: #28a745;
    font-weight: bold;
}

/* Visualización 3D de Caja */
.caja-3d-container {
    perspective: 1000px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 200px;
    width: 100%;
}

.caja-3d {
    width: 100%;
    max-width: 200px;
    height: 220px;
    position: relative;
    transform-style: preserve-3d;
    animation: rotarCaja 10s ease-in-out infinite;
}

@keyframes rotarCaja {
    0%, 100% {
        transform: rotateY(-15deg) rotateX(10deg);
    }
    50% {
        transform: rotateY(15deg) rotateX(10deg);
    }
}

.caja-frontal {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 50%, #8B4513 100%);
    border: 3px solid #654321;
    border-radius: 8px;
    position: relative;
    box-shadow: 
        inset 0 0 20px rgba(0,0,0,0.3),
        5px 5px 15px rgba(0,0,0,0.4);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 10px;
}

.caja-frontal::before {
    content: '';
    position: absolute;
    top: 10%;
    left: 10%;
    right: 10%;
    bottom: 10%;
    border: 2px dashed rgba(255,255,255,0.3);
    border-radius: 4px;
}

.productos-dentro {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 3px;
    width: 100%;
    height: 100%;
    padding: 8px;
    overflow: auto; /* Permite scroll vertical y horizontal */
    max-height: 180px; /* Altura máxima antes de scroll */
}

.productos-dentro::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.productos-dentro::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.1);
    border-radius: 3px;
}

.productos-dentro::-webkit-scrollbar-thumb {
    background: #28a745;
    border-radius: 3px;
}

.productos-dentro::-webkit-scrollbar-thumb:hover {
    background: #1e7e34;
}

.producto-item-mini {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    border-radius: 3px;
    border: 1px solid #FF8C00;
    animation: itemBounce 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    padding: 4px;
    min-height: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    transition: all 0.2s;
}

.producto-item-mini:hover {
    transform: scale(1.1);
    z-index: 10;
    box-shadow: 0 4px 8px rgba(0,0,0,0.25);
}

@keyframes itemBounce {
    0% {
        opacity: 0;
        transform: scale(0) rotate(-180deg);
    }
    60% {
        transform: scale(1.1) rotate(5deg);
    }
    100% {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }
}

.caja-etiqueta {
    position: absolute;
    bottom: -25px;
    left: 50%;
    transform: translateX(-50%);
    background: #28a745;
    color: white;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: bold;
    white-space: nowrap;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Resultado del cálculo */
.calculo-resultado {
    text-align: center;
}

.resultado-numero {
    margin-bottom: 5px;
}

/* Mini animación de cajas */
.mini-animacion-cajas {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: flex-end;
    min-height: 120px;
    gap: 8px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.mini-caja {
    font-size: 2rem;
    animation: cajaBounce 0.5s ease;
    transition: transform 0.2s;
}

.mini-caja:hover {
    transform: scale(1.2) rotate(5deg);
}

@keyframes cajaBounce {
    0% {
        opacity: 0;
        transform: scale(0) translateY(30px) rotate(-180deg);
    }
    50% {
        transform: scale(1.15) translateY(-10px) rotate(5deg);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0) rotate(0deg);
    }
}

.mini-caja-mas {
    font-size: 1rem;
    background: #28a745;
    color: white;
    padding: 5px 10px;
    border-radius: 50%;
    font-weight: bold;
}

/* Responsive */
@media (max-width: 768px) {
    .empaque-icono {
        font-size: 2.5rem;
    }
    
    .mini-caja {
        font-size: 1.5rem;
    }
    
    .resultado-numero .display-4 {
        font-size: 2.5rem;
    }
}
</style>
@endsection

@section('js')
<script>
console.log('✅ Script de crear envío cargado correctamente');

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

function agregarProducto() {
    try {
        console.log('🔵 agregarProducto() llamada. Contador actual:', contadorProductos);
        
        const container = document.getElementById('productos-container');
        if (!container) {
            console.error('❌ No se encontró el contenedor productos-container');
            alert('Error: No se encontró el contenedor de productos');
            return;
        }
        
        const index = contadorProductos++;
        console.log('📦 Creando producto #' + (index + 1));
        
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
                            <select name="productos[${index}][tipo_empaque_id]" 
                                    class="form-control tipo-empaque-select" 
                                    data-index="${index}"
                                    onchange="calcularEmpaqueProducto(${index})">
                                <option value="">Seleccione...</option>
                                @foreach($tiposEmpaque as $te)
                                    <option value="{{ $te->id }}"
                                            data-largo="{{ $te->largo_cm ?? 0 }}"
                                            data-ancho="{{ $te->ancho_cm ?? 0 }}"
                                            data-alto="{{ $te->alto_cm ?? 0 }}"
                                            data-peso="{{ $te->peso_maximo_kg ?? 0 }}"
                                            data-icono="{{ $te->icono ?? '📦' }}">
                                        {{ $te->icono ?? '📦' }} {{ $te->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- MEDIDAS OPCIONALES DEL PRODUCTO (para cálculo más preciso) -->
                <div class="row">
                    <div class="col-12 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleMedidasProducto(${index})">
                            <i class="fas fa-ruler-combined"></i> Medidas del Producto (Opcional)
                        </button>
                    </div>
                </div>
                <div class="row medidas-producto-opcional d-none" id="medidas-producto-${index}">
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label><small>Alto del Producto (cm)</small></label>
                            <input type="number" name="productos[${index}][alto_producto_cm]" 
                                   class="form-control form-control-sm" step="0.1" min="0" placeholder="Ej: 5.5">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label><small>Ancho del Producto (cm)</small></label>
                            <input type="number" name="productos[${index}][ancho_producto_cm]" 
                                   class="form-control form-control-sm" step="0.1" min="0" placeholder="Ej: 7.2">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-group">
                            <label><small>Largo del Producto (cm)</small></label>
                            <input type="number" name="productos[${index}][largo_producto_cm]" 
                                   class="form-control form-control-sm" step="0.1" min="0" placeholder="Ej: 10.0">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="col-12 mb-3">
                    <div id="empaque-visual-${index}" class="empaque-visual-container d-none">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white py-2">
                                <h6 class="mb-0"><i class="fas fa-calculator"></i> Cálculo de Empaque en Tiempo Real</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="empaque-info">
                                            <div class="empaque-icono" id="empaque-icono-${index}">📦</div>
                                            <div class="empaque-specs">
                                                <div class="medida-detalle">
                                                    <i class="fas fa-arrows-alt-h text-primary"></i>
                                                    <strong>Largo:</strong> <span id="empaque-largo-${index}">0</span> cm
                                                </div>
                                                <div class="medida-detalle">
                                                    <i class="fas fa-arrows-alt-v text-info"></i>
                                                    <strong>Ancho:</strong> <span id="empaque-ancho-${index}">0</span> cm
                                                </div>
                                                <div class="medida-detalle">
                                                    <i class="fas fa-arrow-up text-warning"></i>
                                                    <strong>Alto:</strong> <span id="empaque-alto-${index}">0</span> cm
                                                </div>
                                                <hr class="my-2">
                                                <div class="medida-detalle">
                                                    <i class="fas fa-weight-hanging text-success"></i>
                                                    <strong>Capacidad:</strong> <span id="empaque-capacidad-${index}">0</span> kg
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="text-center mb-2">
                                            <small class="text-muted"><strong>Vista de Caja Llena:</strong></small>
                                        </div>
                                        <div class="caja-3d-container">
                                            <div class="caja-3d" id="caja-3d-${index}">
                                                <div class="caja-frontal">
                                                    <div class="productos-dentro" id="productos-dentro-${index}">
                                                    </div>
                                                </div>
                                                <div class="caja-etiqueta" id="caja-etiqueta-${index}">0 items</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="calculo-resultado">
                                            <div class="resultado-numero">
                                                <span class="display-4 text-success font-weight-bold" id="empaques-necesarios-${index}">0</span>
                                            </div>
                                            <small class="text-muted">Empaques Necesarios</small>
                                            <hr class="my-2">
                                            <small class="d-block"><strong>Distribución:</strong></small>
                                            <small class="d-block" id="items-por-empaque-${index}">0 items</small>
                                            <small class="d-block" id="peso-por-empaque-${index}">0 kg</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3 col-md-6 mb-3">
                                        <div class="text-center mb-2">
                                            <small class="text-muted"><strong>Todos los Empaques:</strong></small>
                                        </div>
                                        <div class="mini-animacion-cajas" id="mini-animacion-${index}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
        container.insertAdjacentHTML('beforeend', html);
        console.log('✅ Producto #' + (index + 1) + ' agregado exitosamente');
    } catch (error) {
        console.error('❌ Error en agregarProducto():', error);
        alert('Error al agregar producto: ' + error.message);
    }
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

// ==========================================
// CALCULADOR DE EMPAQUES CON ANIMACIÓN
// ==========================================

/**
 * Calcular empaques necesarios para un producto en tiempo real
 */
function calcularEmpaqueProducto(index) {
    const empaqueSelect = document.querySelector(`.tipo-empaque-select[data-index="${index}"]`);
    const cantidadInput = document.querySelector(`#producto-${index} .cantidad-input`);
    const pesoInput = document.querySelector(`#producto-${index} .peso-input`);
    
    const cantidad = parseFloat(cantidadInput?.value || 0);
    const pesoUnitario = parseFloat(pesoInput?.value || 0);
    
    const visualContainer = document.getElementById(`empaque-visual-${index}`);
    
    // Validar que todos los datos estén presentes
    if (!empaqueSelect || !empaqueSelect.value || cantidad <= 0 || pesoUnitario <= 0) {
        if (visualContainer) {
            visualContainer.classList.add('d-none');
        }
        return;
    }
    
    const selectedOption = empaqueSelect.options[empaqueSelect.selectedIndex];
    const largo = parseFloat(selectedOption.dataset.largo) || 0;
    const ancho = parseFloat(selectedOption.dataset.ancho) || 0;
    const alto = parseFloat(selectedOption.dataset.alto) || 0;
    const pesoMax = parseFloat(selectedOption.dataset.peso) || 1;
    const icono = selectedOption.dataset.icono || '📦';
    
    // Mostrar info del empaque con medidas detalladas
    const iconoEl = document.getElementById(`empaque-icono-${index}`);
    const largoEl = document.getElementById(`empaque-largo-${index}`);
    const anchoEl = document.getElementById(`empaque-ancho-${index}`);
    const altoEl = document.getElementById(`empaque-alto-${index}`);
    const capacidadEl = document.getElementById(`empaque-capacidad-${index}`);
    
    if (iconoEl) iconoEl.textContent = icono;
    if (largoEl) largoEl.textContent = largo;
    if (anchoEl) anchoEl.textContent = ancho;
    if (altoEl) altoEl.textContent = alto;
    if (capacidadEl) capacidadEl.textContent = pesoMax;
    
    // Calcular empaques necesarios
    const pesoTotal = cantidad * pesoUnitario;
    const empaquesPorPeso = Math.ceil(pesoTotal / pesoMax);
    
    // Calcular por cantidad (asumiendo capacidad promedio de 10-20 items según tamaño)
    const capacidadItems = Math.max(Math.floor(pesoMax * 2), 10); // Heurística simple
    const empaquesPorCantidad = Math.ceil(cantidad / capacidadItems);
    
    // Tomar el mayor para garantizar que todo cabe
    const empaquesNecesarios = Math.max(empaquesPorPeso, empaquesPorCantidad, 1);
    
    // Calcular distribución
    const itemsPorEmpaque = Math.ceil(cantidad / empaquesNecesarios);
    const pesoPorEmpaque = (pesoTotal / empaquesNecesarios).toFixed(2);
    
    // Mostrar resultados
    const necesariosEl = document.getElementById(`empaques-necesarios-${index}`);
    const itemsEl = document.getElementById(`items-por-empaque-${index}`);
    const pesoEl = document.getElementById(`peso-por-empaque-${index}`);
    const etiquetaEl = document.getElementById(`caja-etiqueta-${index}`);
    
    if (necesariosEl) necesariosEl.textContent = empaquesNecesarios;
    if (itemsEl) itemsEl.textContent = `${itemsPorEmpaque} items por empaque`;
    if (pesoEl) pesoEl.textContent = `${pesoPorEmpaque} kg por empaque`;
    if (etiquetaEl) etiquetaEl.textContent = `${itemsPorEmpaque} items`;
    
    // Llenar caja 3D con productos
    llenarCaja3D(index, itemsPorEmpaque);
    
    // Animar cajas
    animarMiniCajas(index, empaquesNecesarios, icono);
    
    // Mostrar el contenedor con animación
    if (visualContainer) {
        visualContainer.classList.remove('d-none');
    }
}

/**
 * Llenar la caja 3D con productos visuales - TODOS los items
 */
function llenarCaja3D(index, itemsPorEmpaque) {
    const contenedorProductos = document.getElementById(`productos-dentro-${index}`);
    if (!contenedorProductos) return;
    
    contenedorProductos.innerHTML = '';
    
    // Determinar el grid óptimo según cantidad de items
    let columns, fontSize, padding, minHeight;
    
    if (itemsPorEmpaque <= 9) {
        columns = 3;
        fontSize = '1rem';
        padding = '5px';
        minHeight = '28px';
    } else if (itemsPorEmpaque <= 16) {
        columns = 4;
        fontSize = '0.85rem';
        padding = '4px';
        minHeight = '24px';
    } else if (itemsPorEmpaque <= 25) {
        columns = 5;
        fontSize = '0.75rem';
        padding = '3px';
        minHeight = '22px';
    } else if (itemsPorEmpaque <= 36) {
        columns = 6;
        fontSize = '0.7rem';
        padding = '2px';
        minHeight = '20px';
    } else if (itemsPorEmpaque <= 49) {
        columns = 7;
        fontSize = '0.65rem';
        padding = '2px';
        minHeight = '18px';
    } else {
        columns = 8;
        fontSize = '0.6rem';
        padding = '2px';
        minHeight = '16px';
    }
    
    // Actualizar grid y estilos
    contenedorProductos.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
    
    const emojis = ['🥕', '🥔', '🍎', '🍊', '🥬', '🌽', '🍌', '🍇', '🍓', '🍒'];
    
    // Agregar TODOS los items con animación
    for (let i = 0; i < itemsPorEmpaque; i++) {
        setTimeout(() => {
            const item = document.createElement('div');
            item.className = 'producto-item-mini';
            item.style.fontSize = fontSize;
            item.style.padding = padding;
            item.style.minHeight = minHeight;
            
            item.textContent = emojis[i % emojis.length];
            item.title = `Item ${i + 1} de ${itemsPorEmpaque}`;
            
            contenedorProductos.appendChild(item);
        }, i * 40); // 40ms entre cada item (más rápido)
    }
}

/**
 * Animar las mini cajas con efecto bounce
 */
function animarMiniCajas(index, cantidad, icono) {
    const contenedor = document.getElementById(`mini-animacion-${index}`);
    if (!contenedor) return;
    
    contenedor.innerHTML = '';
    
    const maxCajasVisible = 12; // Máximo 12 cajas visibles en la animación
    const cantidadAMostrar = Math.min(cantidad, maxCajasVisible);
    
    // Animar cada caja con delay
    for (let i = 0; i < cantidadAMostrar; i++) {
        setTimeout(() => {
            const caja = document.createElement('div');
            caja.className = 'mini-caja';
            caja.textContent = icono;
            caja.title = `Empaque ${i + 1}`;
            contenedor.appendChild(caja);
        }, i * 100); // 100ms entre cada caja
    }
    
    // Si hay más de 12, mostrar indicador "+X"
    if (cantidad > maxCajasVisible) {
        setTimeout(() => {
            const mas = document.createElement('div');
            mas.className = 'mini-caja mini-caja-mas';
            mas.textContent = `+${cantidad - maxCajasVisible}`;
            mas.title = `${cantidad - maxCajasVisible} empaques más`;
            contenedor.appendChild(mas);
        }, cantidadAMostrar * 100 + 200);
    }
}

/**
 * Recalcular empaques cuando cambia cantidad o peso
 */
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('cantidad-input') || 
        e.target.classList.contains('peso-input')) {
        const productoCard = e.target.closest('.producto-item');
        if (productoCard) {
            const index = productoCard.id.replace('producto-', '');
            const empaqueSelect = document.querySelector(`.tipo-empaque-select[data-index="${index}"]`);
            
            // Solo recalcular si ya hay un empaque seleccionado
            if (empaqueSelect && empaqueSelect.value) {
                calcularEmpaqueProducto(index);
            }
        }
    }
});

/**
 * Toggle medidas opcionales del producto
 */
function toggleMedidasProducto(index) {
    const medidasDiv = document.getElementById(`medidas-producto-${index}`);
    if (medidasDiv) {
        medidasDiv.classList.toggle('d-none');
    }
}

console.log('✅ Calculador de empaques con animación cargado correctamente');
</script>
@endsection
