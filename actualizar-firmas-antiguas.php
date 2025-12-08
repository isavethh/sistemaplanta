<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Envio;
use Illuminate\Support\Facades\DB;

echo "🔄 Actualizando firmas de envíos antiguos...\n\n";

// Buscar envíos aceptados, en tránsito o entregados sin firma
$envios = Envio::with(['asignacion.transportista', 'almacenDestino'])
    ->whereIn('estado', ['aceptado', 'en_transito', 'entregado'])
    ->where(function($query) {
        $query->whereNull('firma_transportista')
              ->orWhere('firma_transportista', '');
    })
    ->whereHas('asignacion.transportista')
    ->get();

echo "📋 Encontrados {$envios->count()} envíos sin firma\n\n";

$actualizados = 0;

foreach ($envios as $envio) {
    $transportista = $envio->asignacion->transportista;
    
    $firma = "FIRMA DIGITAL DE ACEPTACIÓN\n\n";
    $firma .= "Yo, {$transportista->name}, con documento de identidad, ";
    $firma .= "acepto la responsabilidad del envío {$envio->codigo}.\n\n";
    $firma .= "Detalles del envío:\n";
    $firma .= "- Código: {$envio->codigo}\n";
    $firma .= "- Destino: " . ($envio->almacenDestino->nombre ?? 'N/A') . "\n";
    $firma .= "- Total productos: {$envio->total_cantidad} unidades\n";
    $firma .= "- Peso total: {$envio->total_peso} kg\n\n";
    
    // Usar fecha de aceptación si existe, sino usar created_at
    $fechaAceptacion = $envio->asignacion->fecha_aceptacion ?? $envio->created_at;
    $firma .= "Fecha y hora de aceptación: " . $fechaAceptacion->format('d/m/Y H:i:s') . "\n";
    $firma .= "Transportista: {$transportista->name}\n";
    
    if ($transportista->email) {
        $firma .= "Email: {$transportista->email}\n";
    }
    if ($transportista->licencia) {
        $firma .= "Licencia de conducir: {$transportista->licencia}\n";
    }
    
    $firma .= "\nEsta firma digital certifica que el transportista ha aceptado el envío y asume la responsabilidad de su entrega.";
    
    $envio->firma_transportista = $firma;
    $envio->save();
    
    $actualizados++;
    echo "✅ {$envio->codigo} - Firma agregada para {$transportista->name}\n";
}

echo "\n🎉 Proceso completado: {$actualizados} envíos actualizados\n";
