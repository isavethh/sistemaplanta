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
                                <div class="modal fade" id="firmaModal{{ $item['envio']->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    Firma - Envío {{ $item['envio']->codigo }}
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <strong>Transportista:</strong> {{ $item['transportista'] }}<br>
                                                    <strong>Tipo:</strong> {{ $item['esBase64'] ? 'Base64 (Imagen)' : 'Texto' }}<br>
                                                    <strong>Longitud:</strong> {{ number_format($item['longitud']) }} caracteres<br>
                                                    <strong>Fecha:</strong> {{ $item['envio']->updated_at->format('d/m/Y H:i:s') }}
                                                </div>
                                                
                                                @if($item['esBase64'])
                                                    <div class="text-center">
                                                        <img src="{{ $item['firmaBase64'] }}" 
                                                             alt="Firma Transportista" 
                                                             style="max-width: 100%; border: 2px solid #ddd; border-radius: 8px; padding: 10px; background: white;">
                                                    </div>
                                                @else
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h6 class="card-title">Contenido de la Firma (Texto):</h6>
                                                            <div class="firma-texto-container">
                                                                <pre class="firma-texto-pre">{{ htmlspecialchars($item['firmaTexto'], ENT_QUOTES, 'UTF-8') }}</pre>
                                                            </div>
                                                        </div>
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
        .firma-texto-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background-color: #f8f9fa;
            padding: 15px;
        }
        .firma-texto-pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            background: transparent;
            border: none;
            max-height: none;
            overflow: visible;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
    </style>
@stop

