<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TrazabilidadProductosService
{
    protected $apiUrl;
    protected $cacheTime;

    public function __construct()
    {
        $this->apiUrl = 'http://trazabilidad.dasalas.shop/api';
        $this->cacheTime = 3600; // 1 hora de cache
    }

    /**
     * Obtener todos los productos desde la API de Trazabilidad
     */
    public function obtenerProductos($filters = [])
    {
        $cacheKey = 'trazabilidad_productos_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($filters) {
            try {
                $response = Http::timeout(10)->get("{$this->apiUrl}/products", $filters);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Si la respuesta es paginada, obtener todos los productos
                    if (isset($data['data'])) {
                        return $data['data'];
                    }
                    
                    return $data;
                }
                
                Log::warning('Error al obtener productos de Trazabilidad', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return [];
            } catch (\Exception $e) {
                Log::error('Excepción al obtener productos de Trazabilidad', [
                    'error' => $e->getMessage()
                ]);
                
                return [];
            }
        });
    }

    /**
     * Obtener un producto específico por ID
     */
    public function obtenerProducto($id)
    {
        $cacheKey = "trazabilidad_producto_{$id}";
        
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($id) {
            try {
                $response = Http::timeout(10)->get("{$this->apiUrl}/products/{$id}");
                
                if ($response->successful()) {
                    return $response->json();
                }
                
                return null;
            } catch (\Exception $e) {
                Log::error('Excepción al obtener producto de Trazabilidad', [
                    'id' => $id,
                    'error' => $e->getMessage()
                ]);
                
                return null;
            }
        });
    }

    /**
     * Limpiar cache de productos
     */
    public function limpiarCache()
    {
        Cache::forget('trazabilidad_productos_*');
    }

    /**
     * Sincronizar productos (obtener y actualizar cache)
     */
    public function sincronizarProductos()
    {
        $this->limpiarCache();
        return $this->obtenerProductos();
    }
}

