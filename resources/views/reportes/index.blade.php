@extends('adminlte::page')

@section('title', 'Centro de Reportes')

@section('adminlte_css_pre')
    @include('layouts.preloader-killer')
@endsection

@section('content_header')
    <h1 class="m-0"><i class="fas fa-chart-bar text-primary"></i> Centro de Reportes</h1>
    <p class="text-muted">Generación y exportación de reportes del sistema de logística</p>
@endsection

@section('content')
<div class="row">
    <!-- Reporte 1: Operaciones -->
    <div class="col-lg-6 col-md-6">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-truck-loading"></i> Reporte de Operaciones
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Resumen completo de todas las operaciones de transporte incluyendo envíos, 
                    estados, transportistas asignados y métricas de rendimiento.
                </p>
                <ul class="list-unstyled small text-muted mb-3">
                    <li><i class="fas fa-check text-success"></i> Filtros por fecha, estado, almacén</li>
                    <li><i class="fas fa-check text-success"></i> Estadísticas de peso y valor</li>
                    <li><i class="fas fa-check text-success"></i> Exportación PDF y Excel</li>
                </ul>
                <a href="{{ route('reportes.operaciones') }}" class="btn btn-primary btn-block">
                    <i class="fas fa-eye"></i> Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte 2: Nota de Entrega -->
    <div class="col-lg-6 col-md-6">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-file-signature"></i> Notas de Entrega
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Documento legal de recepción de mercancías. Incluye datos completos del envío, 
                    productos, firmas y conformidad según normativa boliviana.
                </p>
                <ul class="list-unstyled small text-muted mb-3">
                    <li><i class="fas fa-check text-success"></i> Documento con validez legal</li>
                    <li><i class="fas fa-check text-success"></i> Firma digital del transportista</li>
                    <li><i class="fas fa-check text-success"></i> Cumple normativa boliviana</li>
                </ul>
                <a href="{{ route('reportes.nota-entrega') }}" class="btn btn-success btn-block">
                    <i class="fas fa-eye"></i> Ver Entregas
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte 3: Incidentes -->
    <div class="col-lg-6 col-md-6">
        <div class="card card-outline card-danger shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-exclamation-triangle"></i> Reporte de Incidentes
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Análisis detallado de incidentes reportados durante el transporte. 
                    Incluye tipos de incidentes, tiempos de resolución y estadísticas.
                </p>
                <ul class="list-unstyled small text-muted mb-3">
                    <li><i class="fas fa-check text-success"></i> Distribución por tipo de incidente</li>
                    <li><i class="fas fa-check text-success"></i> Tiempo promedio de resolución</li>
                    <li><i class="fas fa-check text-success"></i> Exportación PDF y Excel</li>
                </ul>
                <a href="{{ route('reportes.incidentes') }}" class="btn btn-danger btn-block">
                    <i class="fas fa-eye"></i> Ver Reporte
                </a>
            </div>
        </div>
    </div>

    <!-- Reporte 4: Productividad -->
    <div class="col-lg-6 col-md-6">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-users-cog"></i> Productividad de Transportistas
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Métricas de desempeño de cada transportista. Ranking, tasa de efectividad, 
                    cantidad de entregas e incidentes.
                </p>
                <ul class="list-unstyled small text-muted mb-3">
                    <li><i class="fas fa-check text-success"></i> Ranking de transportistas</li>
                    <li><i class="fas fa-check text-success"></i> Tasa de efectividad</li>
                    <li><i class="fas fa-check text-success"></i> Exportación PDF y Excel</li>
                </ul>
                <a href="{{ route('reportes.productividad') }}" class="btn btn-info btn-block">
                    <i class="fas fa-eye"></i> Ver Reporte
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Información adicional -->
<div class="row mt-4">
    <div class="col-12">
        <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> Información sobre Reportes</h5>
            <p class="mb-0">
                Todos los reportes cuentan con filtros personalizables y pueden ser exportados en 
                <strong>PDF</strong> para documentación formal y <strong>Excel/CSV</strong> para 
                análisis de datos. Los documentos generados cumplen con los estándares de la 
                normativa boliviana para transporte de mercancías.
            </p>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .card-outline {
        border-top: 3px solid;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card-outline:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15) !important;
    }
</style>
@endsection

