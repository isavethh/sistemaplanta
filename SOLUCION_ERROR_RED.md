# Solución al Error de Red en App Móvil

## Problema
La app móvil muestra: `Network Error - No se puede conectar al servidor`

## Causa
Laravel está escuchando solo en `127.0.0.1` (localhost), que solo es accesible desde la misma máquina. La app móvil necesita que el servidor escuche en `0.0.0.0` (todas las interfaces) para poder conectarse desde otros dispositivos.

## Solución

### Opción 1: Usar el script batch (RECOMENDADO)

1. Ejecuta el archivo:
   ```
   INICIAR-LARAVEL-PARA-APP.bat
   ```

2. Este script inicia Laravel escuchando en `0.0.0.0:8001`

### Opción 2: Manualmente desde la terminal

```bash
cd C:\Users\Personal\Downloads\proyectoplantajunto\Planta\plantaCruds
php artisan serve --host=0.0.0.0 --port=8001
```

**IMPORTANTE:** Usa `--host=0.0.0.0` NO `--host=127.0.0.1` o `localhost`

### Verificar que funciona

1. Verifica que el servidor esté escuchando en todas las interfaces:
   ```bash
   netstat -ano | findstr :8001
   ```
   Deberías ver: `TCP    0.0.0.0:8001` (NO `127.0.0.1:8001`)

2. Prueba desde el navegador:
   ```
   http://TU_IP:8001/api/ping
   ```
   Reemplaza `TU_IP` con tu IP local (ej: `192.168.0.129`)

3. Prueba el endpoint de transportista:
   ```
   http://TU_IP:8001/api/transportista/1/envios
   ```

### Configurar la app móvil

En `applanta/mobile-app/src/services/api.js`, asegúrate de que la IP sea correcta:

```javascript
export const API_URL = Platform.OS === 'web' 
  ? 'http://localhost:8001/api'
  : 'http://TU_IP:8001/api'; // ⚠️ Cambia TU_IP por tu IP local
```

### Firewall

Si aún no funciona, verifica que el puerto 8001 esté abierto en el firewall de Windows:

1. Abre "Firewall de Windows Defender"
2. Ve a "Configuración avanzada"
3. Crea una regla de entrada para el puerto 8001 TCP

### Notas importantes

- El servidor debe estar corriendo ANTES de usar la app móvil
- El dispositivo móvil debe estar en la misma red WiFi
- Si cambias de red, actualiza la IP en `api.js`

