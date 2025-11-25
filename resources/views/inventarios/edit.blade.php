@extends('adminlte::page')

@section('title', 'Editar Registro de Inventario')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Editar Registro de Inventario</h1>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-gradient-warning">
        <h3 class="card-title"><i class="fas fa-warehouse"></i> Modificar Información del Inventario</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('inventarios.update', $inventario) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="almacen_id"><i class="fas fa-warehouse"></i> Almacén *</label>
                        <select name="almacen_id" id="almacen_id" class="form-control @error('almacen_id') is-invalid @enderror" required>
                            <option value="">Seleccione un almacén</option>
                            @foreach($almacenes as $almacen)
                                <option value="{{ $almacen->id }}" {{ old('almacen_id', $inventario->almacen_id) == $almacen->id ? 'selected' : '' }}>
                                    {{ $almacen->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('almacen_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="producto_nombre"><i class="fas fa-box"></i> Nombre del Producto *</label>
                        <input type="text" name="producto_nombre" id="producto_nombre" class="form-control @error('producto_nombre') is-invalid @enderror" 
                               value="{{ old('producto_nombre', $inventario->producto_nombre) }}" required placeholder="Ingrese el nombre del producto">
                        @error('producto_nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="cantidad"><i class="fas fa-sort-numeric-up"></i> Cantidad *</label>
                        <input type="number" name="cantidad" id="cantidad" class="form-control @error('cantidad') is-invalid @enderror" 
                               value="{{ old('cantidad', $inventario->cantidad) }}" required min="0" placeholder="0">
                        @error('cantidad')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="peso"><i class="fas fa-weight"></i> Peso (kg)</label>
                        <input type="number" name="peso" id="peso" class="form-control @error('peso') is-invalid @enderror" 
                               value="{{ old('peso', $inventario->peso) }}" step="0.01" min="0" placeholder="0.00">
                        @error('peso')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="precio_unitario"><i class="fas fa-dollar-sign"></i> Precio Unitario</label>
                        <input type="number" name="precio_unitario" id="precio_unitario" class="form-control @error('precio_unitario') is-invalid @enderror" 
                               value="{{ old('precio_unitario', $inventario->precio_unitario) }}" step="0.01" min="0" placeholder="0.00">
                        @error('precio_unitario')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha_llegada"><i class="fas fa-calendar"></i> Fecha de Llegada</label>
                        <input type="date" name="fecha_llegada" id="fecha_llegada" class="form-control @error('fecha_llegada') is-invalid @enderror" 
                               value="{{ old('fecha_llegada', $inventario->fecha_llegada) }}">
                        @error('fecha_llegada')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="envio_producto_id"><i class="fas fa-shipping-fast"></i> Envío Producto (opcional)</label>
                        <select name="envio_producto_id" id="envio_producto_id" class="form-control @error('envio_producto_id') is-invalid @enderror">
                            <option value="">Seleccione un envío (opcional)</option>
                            @foreach($envioProductos as $envioProducto)
                                <option value="{{ $envioProducto->id }}" {{ old('envio_producto_id', $inventario->envio_producto_id) == $envioProducto->id ? 'selected' : '' }}>
                                    Envío #{{ $envioProducto->envio_id }} - {{ $envioProducto->producto_nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('envio_producto_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <hr>
            <div class="form-group">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Actualizar Inventario
                </button>
                <a href="{{ route('inventarios.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('css')
<style>
    .card {
        border-radius: 10px;
    }
</style>
@endsection

