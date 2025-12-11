@extends('adminlte::page')

@section('title', 'Calculador de Empaques')

@section('content_header')
    <h1><i class="fas fa-calculator text-success"></i> Calculador de Carga - Empaques</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-success">
        <h3 class="card-title">
            <i class="fas fa-boxes"></i> Planificador de Carga
        </h3>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Elige el tipo de empaque (caja, bolsa o pallet). Cada uno ya tiene medidas y pesos estándar. 
            Ingresa la cantidad de productos y el peso total, y se calcula automáticamente la carga.
        </p>

        <div class="row">
            <!-- Columna 1: Selección de Producto y Empaque -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h5 class="mb-0">1. Producto y Empaque</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Nombre del Producto</label>
                            <input type="text" id="producto_nombre" class="form-control" placeholder="Ej: Manzanas">
                        </div>

                        <div class="form-group">
                            <label>Tipo de Empaque</label>
                            <select id="tipo_empaque" class="form-control">
                                <option value="">Seleccione...</option>
                                @foreach($empaques as $empaque)
                                    <option value="{{ $empaque->id }}" 
                                            data-largo="{{ $empaque->largo_cm }}"
                                            data-ancho="{{ $empaque->ancho_cm }}"
                                            data-alto="{{ $empaque->alto_cm }}"
                                            data-peso="{{ $empaque->peso_maximo_kg }}"
                                            data-volumen="{{ $empaque->volumen_cm3 }}"
                                            data-icono="{{ $empaque->icono }}">
                                        {{ $empaque->icono }} {{ $empaque->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="info_empaque" class="alert alert-info d-none">
                            <h6><strong>Características del empaque:</strong></h6>
                            <div class="row small">
                                <div class="col-6">
                                    <strong>Largo:</strong> <span id="info_largo">-</span> cm
                                </div>
                                <div class="col-6">
                                    <strong>Ancho:</strong> <span id="info_ancho">-</span> cm
                                </div>
                                <div class="col-6">
                                    <strong>Alto:</strong> <span id="info_alto">-</span> cm
                                </div>
                                <div class="col-6">
                                    <strong>Peso máx:</strong> <span id="info_peso">-</span> kg
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Cantidad -->
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">2. Cantidad y Peso</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Cantidad de Productos</label>
                            <input type="number" id="cantidad" class="form-control" min="1" value="100" placeholder="Ej: 100">
                        </div>

                        <div class="form-group">
                            <label>Peso Total (kg)</label>
                            <input type="number" id="peso_total" class="form-control" min="0.1" step="0.1" value="50" placeholder="Ej: 50.5">
                        </div>

                        <button id="btn_calcular" class="btn btn-success btn-block btn-lg">
                            <i class="fas fa-calculator"></i> Calcular Empaques
                        </button>
                    </div>
                </div>
            </div>

            <!-- Columna 2: Resultado del Cálculo -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success">
                        <h5 class="mb-0">3. Resultado Logístico</h5>
                    </div>
                    <div class="card-body">
                        <div id="resultado" class="d-none">
                            <div class="text-center mb-3">
                                <h3 class="text-success mb-0">
                                    <span id="empaques_necesarios" class="display-4">0</span>
                                </h3>
                                <p class="text-muted mb-0">Empaques Necesarios</p>
                            </div>

                            <hr>

                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="small text-muted">Peso por empaque</div>
                                    <div class="h5 mb-0"><span id="peso_por_empaque">0</span> kg</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="small text-muted">Items por empaque</div>
                                    <div class="h5 mb-0"><span id="items_por_empaque">0</span></div>
                                </div>
                            </div>

                            <div class="alert alert-success">
                                <strong><i class="fas fa-truck"></i> Camión Recomendado:</strong>
                                <p class="mb-0" id="camion_recomendado">Sin cálculo aún</p>
                            </div>

                            <h6 class="mt-3"><i class="fas fa-info-circle"></i> Lógica Usada:</h6>
                            <ul class="small">
                                <li>Peso bruto unidad = peso neto + tara</li>
                                <li>Peso total = peso bruto × cantidad de unidades</li>
                                <li>Volumen unidad = (L × A × H) / 1,000,000</li>
                                <li>Capacidades de referencia:</li>
                                <ul>
                                    <li>Camión ligero: hasta 3,500 kg o 18 m³</li>
                                    <li>Camión mediano: hasta 8,000 kg o 35 m³</li>
                                    <li>Camión grande/frío: hasta 24,000 kg o 60 m³</li>
                                </ul>
                            </ul>
                        </div>

                        <div id="sin_resultado" class="text-center text-muted py-5">
                            <i class="fas fa-box-open fa-4x mb-3"></i>
                            <p>Selecciona el empaque y presiona "Calcular"</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna 3: Animación de Cajas -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info">
                        <h5 class="mb-0">4. Visualización</h5>
                    </div>
                    <div class="card-body">
                        <div id="animacion_cajas" class="text-center p-4" style="min-height: 400px; background: #f8f9fa; border-radius: 10px;">
                            <div id="contenedor_cajas" style="display: flex; flex-wrap: wrap; justify-content: center; align-items: flex-end; gap: 10px; min-height: 350px;">
                                <!-- Las cajas se generarán aquí dinámicamente -->
                            </div>
                            <div id="mensaje_animacion" class="text-muted mt-3">
                                <i class="fas fa-info-circle"></i> Las cajas aparecerán aquí al calcular
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Al cambiar el tipo de empaque, mostrar info
    $('#tipo_empaque').change(function() {
        const selected = $(this).find(':selected');
        if (selected.val()) {
            $('#info_largo').text(selected.data('largo'));
            $('#info_ancho').text(selected.data('ancho'));
            $('#info_alto').text(selected.data('alto'));
            $('#info_peso').text(selected.data('peso'));
            $('#info_empaque').removeClass('d-none');
        } else {
            $('#info_empaque').addClass('d-none');
        }
    });

    // Botón calcular
    $('#btn_calcular').click(function() {
        const tipoEmpaqueId = $('#tipo_empaque').val();
        const cantidad = parseInt($('#cantidad').val());
        const pesoTotal = parseFloat($('#peso_total').val());
        const productoNombre = $('#producto_nombre').val() || 'Producto';

        if (!tipoEmpaqueId) {
            Swal.fire('Error', 'Selecciona un tipo de empaque', 'error');
            return;
        }

        if (!cantidad || cantidad < 1) {
            Swal.fire('Error', 'Ingresa una cantidad válida', 'error');
            return;
        }

        if (!pesoTotal || pesoTotal < 0.1) {
            Swal.fire('Error', 'Ingresa un peso válido', 'error');
            return;
        }

        // Mostrar loading
        Swal.fire({
            title: 'Calculando...',
            html: '<i class="fas fa-spinner fa-spin fa-3x text-primary"></i>',
            showConfirmButton: false,
            allowOutsideClick: false
        });

        // Llamar al API
        $.ajax({
            url: '{{ route("tiposempaque.calcular") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                tipo_empaque_id: tipoEmpaqueId,
                cantidad_items: cantidad,
                peso_total_kg: pesoTotal
            },
            success: function(response) {
                Swal.close();
                
                if (response.success) {
                    mostrarResultado(response, productoNombre);
                    animarCajas(response.empaques_necesarios, response.empaque.icono || '📦');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'No se pudo calcular. Intenta de nuevo.', 'error');
            }
        });
    });

    function mostrarResultado(data, productoNombre) {
        $('#sin_resultado').addClass('d-none');
        $('#resultado').removeClass('d-none');
        
        $('#empaques_necesarios').text(data.empaques_necesarios);
        $('#peso_por_empaque').text(data.peso_por_empaque);
        $('#items_por_empaque').text(data.items_por_empaque);
        
        // Determinar camión recomendado
        let camion = 'Sin cálculo aún';
        const pesoTotal = parseFloat($('#peso_total').val());
        
        if (pesoTotal <= 3500) {
            camion = '🚐 Camión ligero: hasta 3,500 kg o 18 m³';
        } else if (pesoTotal <= 8000) {
            camion = '🚚 Camión mediano: hasta 8,000 kg o 35 m³';
        } else {
            camion = '🚛 Camión grande/frío: hasta 24,000 kg o 60 m³';
        }
        
        $('#camion_recomendado').html(camion);
    }

    function animarCajas(cantidad, icono) {
        const contenedor = $('#contenedor_cajas');
        contenedor.empty();
        $('#mensaje_animacion').html('<i class="fas fa-box-open"></i> Llenando empaques...');

        let cajasMostradas = 0;
        const maxCajas = Math.min(cantidad, 50); // Máximo 50 cajas para no saturar

        const intervalo = setInterval(function() {
            if (cajasMostradas >= maxCajas) {
                clearInterval(intervalo);
                $('#mensaje_animacion').html(
                    `<strong class="text-success">✅ ${cantidad} empaques necesarios</strong>` +
                    (cantidad > 50 ? ' <small>(mostrando 50)</small>' : '')
                );
                return;
            }

            const caja = $('<div>')
                .css({
                    'font-size': '3rem',
                    'animation': 'fadeInUp 0.5s',
                    'opacity': '0'
                })
                .text(icono);

            contenedor.append(caja);

            setTimeout(function() {
                caja.css('opacity', '1');
            }, 50);

            cajasMostradas++;
        }, 100);
    }
});
</script>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@stop

