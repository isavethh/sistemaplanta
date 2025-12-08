@extends('adminlte::page')

@section('title', 'Notas de Venta')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice-dollar text-success"></i> Notas de Venta</h1>
        <span class="badge badge-success badge-pill" style="font-size: 1rem; padding: 10px 20px;">
            <i class="fas fa-receipt"></i> {{ count($notasVenta) }} notas generadas
        </span>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-lg">
        <div class="card-header bg-gradient-success py-3">
            <h5 class="m-0 text-white">
                <i class="fas fa-list-alt"></i> Listado de Notas de Venta
            </h5>
        </div>
        <div class="card-body">
            @if(count($notasVenta) > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="dataTable">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th><i class="fas fa-hashtag"></i> Número Nota</th>
                                <th><i class="fas fa-box"></i> Código Envío</th>
                                <th><i class="fas fa-warehouse"></i> Almacén</th>
                                <th><i class="fas fa-calendar"></i> Fecha Emisión</th>
                                <th class="text-center"><i class="fas fa-cubes"></i> Cantidad</th>
                                <th class="text-right"><i class="fas fa-money-bill-wave"></i> Total</th>
                                <th class="text-center"><i class="fas fa-cogs"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notasVenta as $nota)
                                <tr>
                                    <td>
                                        <strong class="text-success">{{ $nota->numero_nota }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary" style="font-size: 0.9rem;">
                                            <i class="fas fa-shipping-fast"></i> {{ $nota->envio_codigo }}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-warehouse text-info"></i>
                                        {{ $nota->almacen_nombre ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <i class="far fa-calendar-alt text-secondary"></i>
                                        {{ \Carbon\Carbon::parse($nota->fecha_emision)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info" style="font-size: 0.95rem;">{{ $nota->total_cantidad }}</span>
                                    </td>
                                    <td class="text-right">
                                        <strong class="text-success" style="font-size: 1.1rem;">Bs {{ number_format($nota->total_precio, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('notas-venta.show', $nota->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Ver detalles">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <a href="{{ route('notas-venta.html', $nota->id) }}" 
                                               class="btn btn-sm btn-success" 
                                               target="_blank"
                                               title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay notas de venta generadas</h4>
                    <p class="text-muted">Las notas se generan automáticamente cuando un transportista acepta un envío</p>
                </div>
            @endif
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    .table th {
        font-weight: 600;
        vertical-align: middle;
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-weight: 500;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            order: [[3, 'desc']]
        });
    });
</script>
@stop
