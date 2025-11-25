@extends('adminlte::page')
@section('title', 'Tracking de Envío')
@section('content_header')
    <h1>Tracking de Envío: {{ $envio->codigo }}</h1>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <p><strong>Estado actual:</strong> <span id="estado">{{ $envio->estado }}</span></p>
        <p><strong>Punto de entrega:</strong> {{ $envio->direccion->descripcion ?? '' }}</p>
        <div id="map" style="height:300px;width:100%;background:#eee;"></div>
        <button class="btn btn-primary" onclick="simularRuta()">Simular ruta en tiempo real</button>
    </div>
</div>
<script>
let estados = ['pendiente','en camino','entregado'];
let idx = 0;
function simularRuta() {
    idx = 0;
    let estadoElem = document.getElementById('estado');
    let interval = setInterval(() => {
        estadoElem.textContent = estados[idx];
        idx++;
        if(idx >= estados.length) clearInterval(interval);
    }, 2000);
}
</script>
@endsection
