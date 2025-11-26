@extends('adminlte::page')

@section('title', 'Códigos QR y Documentos')

@section('content_header')
    <h1><i class="fas fa-qrcode"></i> Códigos QR y Documentos de Envío</h1>
@endsection

@section('content')
<div class="row">
    <!-- Filtro por Cliente -->
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white"><i class="fas fa-filter"></i> Filtrar por Cliente</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('codigosqr.index') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cliente_id"><i class="fas fa-user"></i> Seleccione un Cliente</label>
                                <select name="cliente_id" id="cliente_id" class="form-control" onchange="this.form.submit()">
                                    <option value="">Todos los clientes</option>
                                    @php
                                        $clientes = \App\Models\User::where('tipo', 'cliente')
                                            ->orWhere('role', 'cliente')
                                            ->get();
                                    @endphp
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->name }} - {{ $cliente->email }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="estado"><i class="fas fa-check-circle"></i> Estado del Envío</label>
                                <select name="estado" id="estado" class="form-control" onchange="this.form.submit()">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="en_transito" {{ request('estado') == 'en_transito' ? 'selected' : '' }}>En Tránsito</option>
                                    <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Envíos del Cliente -->
<div class="card shadow">
    <div class="card-header bg-gradient-success">
        <h3 class="card-title text-white"><i class="fas fa-shipping-fast"></i> Envíos y Documentos</h3>
    </div>
    <div class="card-body">
        @php
            $query = \App\Models\Envio::with(['cliente', 'almacenDestino', 'productos']);
            
            if (request('cliente_id')) {
                $query->where('cliente_id', request('cliente_id'));
            }
            
            if (request('estado')) {
                $query->where('estado', request('estado'));
            }
            
            $envios = $query->orderBy('created_at', 'desc')->get();
        @endphp

        <table id="enviosTable" class="table table-striped table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Productos</th>
                    <th>Destino</th>
                    <th width="200px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($envios as $envio)
                <tr>
                    <td><strong>{{ $envio->codigo }}</strong></td>
                    <td>{{ $envio->cliente->name ?? 'N/A' }}</td>
                    <td>{{ $envio->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($envio->estado == 'pendiente')
                            <span class="badge badge-warning">PENDIENTE</span>
                        @elseif($envio->estado == 'en_transito')
                            <span class="badge badge-info">EN TRÁNSITO</span>
                        @elseif($envio->estado == 'entregado')
                            <span class="badge badge-success">ENTREGADO</span>
                        @else
                            <span class="badge badge-secondary">{{ strtoupper($envio->estado) }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-primary badge-pill">
                            {{ $envio->productos->count() }} producto(s)
                        </span>
                    </td>
                    <td>{{ $envio->almacenDestino->nombre ?? 'N/A' }}</td>
                    <td>
                        <div class="btn-group-vertical btn-block">
                            <button class="btn btn-sm btn-info" onclick="verQR('{{ $envio->codigo }}')">
                                <i class="fas fa-qrcode"></i> Ver QR
                            </button>
                            <a href="{{ route('codigosqr.show', $envio->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-file-pdf"></i> Ver Documento
                            </a>
                            <a href="{{ route('envios.tracking', $envio) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-map-marker-alt"></i> Tracking
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> No hay envíos para mostrar
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para QR -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-qrcode"></i> Código QR del Envío</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <div id="qrcode" class="mb-3"></div>
                <p id="qr-codigo" class="font-weight-bold"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="descargarQR()">
                    <i class="fas fa-download"></i> Descargar QR
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
$(document).ready(function() {
    $('#enviosTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        order: [[2, 'desc']]
    });
});

let qrCodeInstance = null;

function verQR(codigo) {
    // Limpiar QR anterior
    document.getElementById('qrcode').innerHTML = '';
    document.getElementById('qr-codigo').textContent = codigo;
    
    // Generar nuevo QR
    qrCodeInstance = new QRCode(document.getElementById('qrcode'), {
        text: codigo,
        width: 256,
        height: 256,
        colorDark: '#000000',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.H
    });
    
    // Mostrar modal
    $('#qrModal').modal('show');
}

function descargarQR() {
    const canvas = document.querySelector('#qrcode canvas');
    if (canvas) {
        const url = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = `QR_${document.getElementById('qr-codigo').textContent}.png`;
        link.href = url;
        link.click();
    }
}
</script>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<style>
    .btn-group-vertical .btn {
        margin-bottom: 2px;
    }
    #qrcode {
        display: inline-block;
    }
</style>
@endsection
