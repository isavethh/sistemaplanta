@extends('adminlte::page')

@section('title', 'Mi Inventario')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-truck"></i> Mi Inventario de Entregas</h1>
    </div>
    <p class="text-muted">Productos de envíos que has entregado exitosamente</p>
@endsection

@section('content')
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle"></i> {{ session('info') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Tarjetas de Resumen -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $estadisticas['total_productos'] }}</h3>
                <p>Productos Diferentes</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format($estadisticas['total_cantidad'], 0) }}</h3>
                <p>Total Unidades</p>
            </div>
            <div class="icon">
                <i class="fas fa-cubes"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($estadisticas['total_peso'], 2) }}</h3>
                <p>Peso Total (kg)</p>
            </div>
            <div class="icon">
                <i class="fas fa-weight"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $estadisticas['total_envios_entregados'] }}</h3>
                <p>Envíos Entregados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow mb-4">
    <div class="card-header bg-gradient-primary">
        <h3 class="card-title text-white"><i class="fas fa-filter"></i> Filtros</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('inventarios-transportista.index') }}" class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="categoria"><strong>Categoría:</strong></label>
                    <select name="categoria" id="categoria" class="form-control">
                        <option value="">-- Todas las categorías --</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria }}" {{ request('categoria') == $categoria ? 'selected' : '' }}>
                                {{ $categoria }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="producto"><strong>Buscar Producto:</strong></label>
                    <input type="text" name="producto" id="producto" class="form-control" 
                           value="{{ request('producto') }}" placeholder="Nombre del producto...">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('inventarios-transportista.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-redo"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Inventario -->
<div class="card shadow">
    <div class="card-header bg-gradient-success">
        <h3 class="card-title text-white"><i class="fas fa-list"></i> Detalle de Productos Entregados</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Peso (kg)</th>
                        <th class="text-right">Precio Unit.</th>
                        <th class="text-right">Total (Bs.)</th>
                        <th class="text-center">Última Entrega</th>
                        <th class="text-center">Envíos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventarios as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->producto_nombre }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $item->categoria ?? 'General' }}</span>
                        </td>
                        <td class="text-center">
                            <strong>{{ number_format($item->cantidad, 0) }}</strong>
                        </td>
                        <td class="text-right">
                            {{ number_format($item->peso, 2) }}
                        </td>
                        <td class="text-right">
                            Bs. {{ number_format($item->precio_unitario, 2) }}
                        </td>
                        <td class="text-right">
                            <strong>Bs. {{ number_format($item->total_precio, 2) }}</strong>
                        </td>
                        <td class="text-center">
                            @if($item->fecha_entrega)
                                <small>{{ \Carbon\Carbon::parse($item->fecha_entrega)->format('d/m/Y') }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-secondary">{{ $item->total_envios }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No has entregado ningún envío todavía.</p>
                            <p class="text-muted"><small>El inventario se actualiza automáticamente cuando marcas un envío como entregado.</small></p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($inventarios->count() > 0)
                <tfoot class="bg-light">
                    <tr>
                        <th colspan="3" class="text-right"><strong>TOTALES:</strong></th>
                        <th class="text-center"><strong>{{ number_format($inventarios->sum('cantidad'), 0) }}</strong></th>
                        <th class="text-right"><strong>{{ number_format($inventarios->sum('peso'), 2) }} kg</strong></th>
                        <th colspan="2" class="text-right"><strong>Bs. {{ number_format($inventarios->sum('total_precio'), 2) }}</strong></th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<!-- Información Adicional -->
<div class="card shadow mt-4">
    <div class="card-body">
        <h5><i class="fas fa-info-circle text-info"></i> Información</h5>
        <p class="text-muted mb-0">
            Este inventario muestra todos los productos de los envíos que has entregado exitosamente. 
            Los datos se agrupan por producto y categoría, mostrando el total acumulado de todas tus entregas.
        </p>
    </div>
</div>
@endsection

@section('css')
<style>
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }
    .small-box > .inner {
        padding: 10px;
    }
    .small-box > .small-box-footer {
        background-color: rgba(0,0,0,.1);
        color: rgba(255,255,255,.8);
        display: block;
        padding: 3px 0;
        position: relative;
        text-align: center;
        text-decoration: none;
        z-index: 10;
    }
</style>
@endsection

