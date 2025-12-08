<?php
/**
 * Visor de envíos en plantaCruds
 * Muestra los envíos creados desde la integración con Trazabilidad
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->instance('request', Illuminate\Http\Request::capture());

use App\Models\Envio;
use App\Models\EnvioProducto;
use App\Models\Almacen;

// Obtener envíos recientes
$envios = Envio::with(['almacenDestino', 'productos'])
    ->orderBy('created_at', 'desc')
    ->limit(20)
    ->get();

// Función para obtener color según estado
function getEstadoColor($estado) {
    switch ($estado) {
        case 'pendiente': return '#ffc107';
        case 'asignado': return '#17a2b8';
        case 'en_transito': return '#007bff';
        case 'entregado': return '#28a745';
        case 'cancelado': return '#dc3545';
        default: return '#6c757d';
    }
}

// Función para obtener emoji según estado
function getEstadoEmoji($estado) {
    switch ($estado) {
        case 'pendiente': return '⏳';
        case 'asignado': return '👤';
        case 'en_transito': return '🚚';
        case 'entregado': return '✅';
        case 'cancelado': return '❌';
        default: return '📦';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor de Envíos - plantaCruds</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #11998e;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .envios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .envio-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border-top: 4px solid;
        }
        
        .envio-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .envio-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .envio-codigo {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .envio-estado {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .envio-info {
            margin-bottom: 10px;
            font-size: 14px;
            color: #666;
        }
        
        .envio-info strong {
            color: #333;
            display: inline-block;
            min-width: 100px;
        }
        
        .productos-list {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .productos-title {
            font-size: 12px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .producto-item {
            font-size: 13px;
            color: #666;
            padding: 4px 0;
            border-bottom: 1px solid #e1e1e1;
        }
        
        .producto-item:last-child {
            border-bottom: none;
        }
        
        .observaciones {
            background: #fff3cd;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 12px;
            color: #856404;
            white-space: pre-line;
            max-height: 80px;
            overflow-y: auto;
        }
        
        .no-data {
            background: white;
            border-radius: 12px;
            padding: 60px;
            text-align: center;
            color: #999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .no-data-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .refresh-btn {
            background: white;
            color: #11998e;
            padding: 12px 24px;
            border: 2px solid #11998e;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .refresh-btn:hover {
            background: #11998e;
            color: white;
        }
        
        .fecha {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Visor de Envíos - plantaCruds</h1>
            <p class="subtitle">Envíos recibidos desde Trazabilidad</p>
            <button class="refresh-btn" onclick="location.reload()">🔄 Actualizar</button>
        </div>
        
        <?php
        $pendientes = $envios->where('estado', 'pendiente')->count();
        $asignados = $envios->where('estado', 'asignado')->count();
        $en_transito = $envios->where('estado', 'en_transito')->count();
        $entregados = $envios->where('estado', 'entregado')->count();
        ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $envios->count() ?></div>
                <div class="stat-label">Total Envíos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $pendientes ?></div>
                <div class="stat-label">⏳ Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $asignados ?></div>
                <div class="stat-label">👤 Asignados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $en_transito ?></div>
                <div class="stat-label">🚚 En Tránsito</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $entregados ?></div>
                <div class="stat-label">✅ Entregados</div>
            </div>
        </div>
        
        <?php if ($envios->isEmpty()): ?>
            <div class="no-data">
                <div class="no-data-icon">📭</div>
                <h2>No hay envíos registrados</h2>
                <p>Los envíos creados desde Trazabilidad aparecerán aquí</p>
            </div>
        <?php else: ?>
            <div class="envios-grid">
                <?php foreach ($envios as $envio): ?>
                    <div class="envio-card" style="border-top-color: <?= getEstadoColor($envio->estado) ?>">
                        <div class="envio-header">
                            <div class="envio-codigo"><?= $envio->codigo ?></div>
                            <div class="envio-estado" style="background-color: <?= getEstadoColor($envio->estado) ?>">
                                <?= getEstadoEmoji($envio->estado) ?> <?= ucfirst($envio->estado) ?>
                            </div>
                        </div>
                        
                        <div class="envio-info">
                            <strong>📍 Destino:</strong> <?= $envio->almacenDestino->nombre ?? 'N/A' ?>
                        </div>
                        
                        <div class="envio-info">
                            <strong>📅 Entrega:</strong> <?= $envio->fecha_estimada_entrega ? date('d/m/Y', strtotime($envio->fecha_estimada_entrega)) : 'N/A' ?>
                        </div>
                        
                        <div class="envio-info">
                            <strong>📊 Cantidad:</strong> <?= $envio->total_cantidad ?> unidades
                        </div>
                        
                        <div class="envio-info">
                            <strong>⚖️ Peso:</strong> <?= number_format($envio->total_peso, 2) ?> kg
                        </div>
                        
                        <?php if ($envio->productos->isNotEmpty()): ?>
                            <div class="productos-list">
                                <div class="productos-title">Productos (<?= $envio->productos->count() ?>)</div>
                                <?php foreach ($envio->productos as $producto): ?>
                                    <div class="producto-item">
                                        • <?= $producto->producto_nombre ?> 
                                        <strong>(<?= $producto->cantidad ?>)</strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($envio->observaciones): ?>
                            <div class="observaciones">
                                <strong>📝 Observaciones:</strong><br>
                                <?= nl2br(htmlspecialchars($envio->observaciones)) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="fecha">
                            Creado: <?= $envio->created_at->format('d/m/Y H:i') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
