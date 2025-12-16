@extends('adminlte::page')

@section('title', 'Test - Firmas Guardadas')

@section('content_header')
    <h1><i class="fas fa-signature"></i> Test - Firmas Guardadas en Base de Datos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Envíos con Firmas Guardadas ({{ count($firmas) }})
            </h3>
        </div>
        <div class="card-body">
            @if(count($firmas) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID Envío</th>
                                <th>Código</th>
                                <th>Transportista</th>
                                <th>Tipo Firma</th>
                                <th>Longitud</th>
                                <th>Fecha Actualización</th>
                                <th>Vista Previa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($firmas as $item)
                                <tr>
                                    <td>{{ $item['envio']->id }}</td>
                                    <td>
                                        <a href="{{ route('envios.show', $item['envio']->id) }}" target="_blank">
                                            {{ $item['envio']->codigo }}
                                        </a>
                                    </td>
                                    <td>{{ $item['transportista'] }}</td>
                                    <td>
                                        @if($item['esBase64'])
                                            <span class="badge badge-success">Base64 (Imagen)</span>
                                        @else
                                            <span class="badge badge-info">Texto</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($item['longitud']) }} caracteres</td>
                                    <td>{{ $item['envio']->updated_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        @if($item['esBase64'])
                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#firmaModal{{ $item['envio']->id }}">
                                                <i class="fas fa-eye"></i> Ver Firma
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#firmaModal{{ $item['envio']->id }}">
                                                <i class="fas fa-file-text"></i> Ver Texto
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Modal para mostrar la firma -->
                                <div class="modal fade" id="firmaModal{{ $item['envio']->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Firma - Envío {{ $item['envio']->codigo }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <table class="table table-sm table-bordered mb-3">
                                                    <tr>
                                                        <th width="30%">Transportista:</th>
                                                        <td>{{ $item['transportista'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Tipo:</th>
                                                        <td>{{ $item['esBase64'] ? 'Base64 (Imagen)' : 'Texto' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Longitud:</th>
                                                        <td>{{ number_format($item['longitud']) }} caracteres</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Fecha:</th>
                                                        <td>{{ $item['envio']->updated_at->format('d/m/Y H:i:s') }}</td>
                                                    </tr>
                                                </table>
                                                
                                                @if($item['esBase64'])
                                                    <div class="text-center p-3" style="background: #f8f9fa; border-radius: 4px;">
                                                        <img src="{{ $item['firmaBase64'] }}" 
                                                             alt="Firma Transportista" 
                                                             style="max-width: 100%; max-height: 400px; border: 1px solid #ddd; border-radius: 4px;">
                                                    </div>
                                                @else
                                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
                                                        <strong>Contenido de la Firma (Texto):</strong>
                                                        <textarea readonly style="width: 100%; margin-top: 10px; padding: 10px; background: white; border: 1px solid #ddd; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 11px; line-height: 1.5; min-height: 200px; max-height: 400px; resize: vertical;">{{ $item['firmaTexto'] }}</textarea>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No se encontraron firmas guardadas en la base de datos.
                </div>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i> Información
            </h3>
        </div>
        <div class="card-body">
            <p><strong>Total de envíos con firma:</strong> {{ count($firmas) }}</p>
            <p><strong>Firmas Base64 (Imagen):</strong> {{ collect($firmas)->where('esBase64', true)->count() }}</p>
            <p><strong>Firmas de Texto:</strong> {{ collect($firmas)->where('esBase64', false)->count() }}</p>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table th {
            background-color: #f8f9fa;
        }
    </style>
@stop

