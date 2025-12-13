# Solución Final al Error de Red

## Problema
La app móvil no puede conectarse al servidor Laravel y tarda mucho en cargar.

## Soluciones Aplicadas

### 1. Timeout Reducido
- Timeout de API reducido de 15s a 5s (global) y 3s (específico para getByTransportista)
- La app ya no se queda colgada esperando

### 2. Optimización de Carga
- Eliminada detección automática de IP (causaba lentitud)
- Timeout en EnviosScreen para evitar bloqueos
- Errores de red no se loguean repetitivamente

### 3. Configuración de IP

**Edita el archivo:** `applanta/mobile-app/src/services/api.js`

**Línea 11-13:** Cambia la IP en `IP_CANDIDATES[0]` o directamente en `API_URL`:

```javascript
const IP_CANDIDATES = [
  '192.168.56.1',   // ← Cambia esta IP por la correcta
  '192.168.0.129',
  '100.125.212.89',
  '10.26.13.220',
];
```

O directamente:
```javascript
export const API_URL = Platform.OS === 'web' 
  ? 'http://localhost:8001/api'
  : 'http://TU_IP_AQUI:8001/api'; // ← Cambia TU_IP_AQUI
```

### 4. Verificar que Laravel esté corriendo

**Ejecuta:**
```bash
cd C:\Users\Personal\Downloads\proyectoplantajunto\Planta\plantaCruds
php artisan serve --host=0.0.0.0 --port=8001
```

**Verifica que esté escuchando en 0.0.0.0:**
```bash
netstat -ano | findstr :8001
```
Debe mostrar: `TCP    0.0.0.0:8001` (NO `127.0.0.1:8001`)

### 5. Abrir Firewall (IMPORTANTE)

**Ejecuta como Administrador:**
```bash
netsh advfirewall firewall add rule name="Laravel API 8001" dir=in action=allow protocol=TCP localport=8001
```

O ejecuta el archivo: `ABRIR_PUERTO_FIREWALL.bat` (como administrador)

### 6. Probar Conectividad

Desde tu navegador o con curl:
```
http://TU_IP:8001/api/ping
http://TU_IP:8001/api/transportista/1/envios
```

### 7. IPs Disponibles en tu Sistema

Según `ipconfig`, tienes estas IPs:
- `192.168.0.129` - Red local principal
- `192.168.56.1` - VirtualBox
- `100.125.212.89` - VPN/Red externa

**Prueba cambiando la IP en `api.js` a cada una de estas hasta que funcione.**

## Checklist Final

- [ ] Laravel corriendo en `0.0.0.0:8001`
- [ ] Firewall puerto 8001 abierto
- [ ] IP correcta en `api.js`
- [ ] Dispositivo móvil en misma red WiFi
- [ ] App móvil reiniciada completamente

## Si Aún No Funciona

1. Prueba cada IP manualmente cambiando `IP_CANDIDATES[0]`
2. Verifica que puedas acceder desde el navegador del móvil a `http://TU_IP:8001/api/ping`
3. Revisa los logs de Laravel: `storage/logs/laravel.log`
4. Verifica que no haya otro proceso usando el puerto 8001

