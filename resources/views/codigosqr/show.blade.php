<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento de Envío - {{ $envio->codigo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
        }
        .documento-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin: -20px -20px 30px -20px;
        }
        .qr-code {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .info-box {
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .productos-table th {
            background: #667eea;
            color: white;
        }
        .stamp-box {
            border: 2px solid #000;
            padding: 40px;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row no-print mb-3">
            <div class="col-12">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Imprimir Documento
                </button>
                <button onclick="window.close()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>

        <div class="card shadow-lg">
            <div class="documento-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-0">DOCUMENTO DE ENVÍO</h1>
                        <h3 class="mt-2">{{ $envio->codigo }}</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <h5>PlantaCRUDS</h5>
                        <p class="mb-0">Sistema de Gestión de Envíos</p>
                        <p class="mb-0"><small>Santa Cruz de la Sierra, Bolivia</small></p>
                    </div>
                </div>
            </div>

            <div class="card-body" style="padding: 40px;">
                <!-- Información del Cliente -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-box">
                            <h5><i class="fas fa-user"></i> CLIENTE</h5>
                            <p class="mb-1"><strong>Nombre:</strong> {{ $envio->cliente->name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $envio->cliente->email ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Teléfono:</strong> {{ $envio->cliente->telefono ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <h5><i class="fas fa-calendar"></i> INFORMACIÓN DEL ENVÍO</h5>
                            <p class="mb-1"><strong>Fecha de Creación:</strong> {{ $envio->created_at->format('d/m/Y H:i') }}</p>
                            <p class="mb-1"><strong>Fecha Estimada:</strong> {{ $envio->fecha_llegada ? \Carbon\Carbon::parse($envio->fecha_llegada)->format('d/m/Y') : 'N/A' }}</p>
                            <p class="mb-0"><strong>Estado:</strong> 
                                @if($envio->estado == 'pendiente')
                                    <span class="badge badge-warning">PENDIENTE</span>
                                @elseif($envio->estado == 'en_transito')
                                    <span class="badge badge-info">EN TRÁNSITO</span>
                                @elseif($envio->estado == 'entregado')
                                    <span class="badge badge-success">ENTREGADO</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Origen y Destino -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-box">
                            <h5><i class="fas fa-warehouse"></i> ORIGEN</h5>
                            @php
                                $planta = \App\Models\Almacen::where('es_planta', true)->first();
                            @endphp
                            <p class="mb-1"><strong>Almacén:</strong> {{ $planta->nombre ?? 'Planta Principal' }}</p>
                            <p class="mb-0"><strong>Dirección:</strong> {{ $planta->direccion_completa ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <h5><i class="fas fa-map-marker-alt"></i> DESTINO</h5>
                            <p class="mb-1"><strong>Almacén:</strong> {{ $envio->almacenDestino->nombre ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>Dirección:</strong> {{ $envio->almacenDestino->direccion_completa ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Transportista y Vehículo -->
                @if($envio->asignacion)
                <div class="row mb-4">
                    @if($envio->asignacion->transportista)
                    <div class="col-md-6">
                        <div class="info-box">
                            <h5><i class="fas fa-user-tie"></i> TRANSPORTISTA</h5>
                            <p class="mb-1"><strong>Nombre:</strong> {{ $envio->asignacion->transportista->name }}</p>
                            <p class="mb-0"><strong>Email:</strong> {{ $envio->asignacion->transportista->email }}</p>
                        </div>
                    </div>
                    @endif
                    @if($envio->asignacion->vehiculo)
                    <div class="col-md-6">
                        <div class="info-box">
                            <h5><i class="fas fa-truck"></i> VEHÍCULO</h5>
                            <p class="mb-1"><strong>Placa:</strong> {{ $envio->asignacion->vehiculo->placa }}</p>
                            <p class="mb-0"><strong>Marca/Modelo:</strong> {{ $envio->asignacion->vehiculo->marca }} {{ $envio->asignacion->vehiculo->modelo }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Productos -->
                <h5 class="mb-3"><i class="fas fa-boxes"></i> PRODUCTOS DEL ENVÍO</h5>
                <table class="table table-bordered productos-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Peso Unit.</th>
                            <th>Precio Unit.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($envio->productos as $index => $producto)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $producto->producto_nombre ?? $producto->nombre }}</td>
                            <td>{{ $producto->cantidad }}</td>
                            <td>{{ number_format($producto->peso_unitario, 3) }} kg</td>
                            <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                            <td>${{ number_format($producto->total_precio, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No hay productos registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="font-weight-bold">
                        <tr>
                            <td colspan="2" class="text-right">TOTALES:</td>
                            <td>{{ $envio->total_cantidad }}</td>
                            <td>{{ number_format($envio->total_peso, 3) }} kg</td>
                            <td>-</td>
                            <td>${{ number_format($envio->total_precio, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Código QR -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="qr-code">
                            <h6>Código QR del Envío</h6>
                            <div id="qrcode"></div>
                            <p class="mt-2 mb-0"><strong>{{ $envio->codigo }}</strong></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stamp-box">
                            <p class="mb-4">FIRMA Y SELLO DE RECEPCIÓN</p>
                            <div style="height: 60px;"></div>
                            <hr>
                            <p class="mb-0"><small>Nombre y Firma del Receptor</small></p>
                        </div>
                    </div>
                </div>

                <!-- Notas -->
                <div class="mt-4 p-3 bg-light border">
                    <h6>Notas:</h6>
                    <p class="mb-0 text-muted"><small>
                        Este documento es generado automáticamente por el Sistema de Gestión de Planta.
                        Para cualquier consulta o reclamo, contacte con su ejecutivo de cuenta.
                    </small></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Generar QR Code
        new QRCode(document.getElementById('qrcode'), {
            text: '{{ $envio->codigo }}',
            width: 180,
            height: 180,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
    </script>
</body>
</html>

