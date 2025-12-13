# Configuración de IP para App Móvil

## Problema
La app móvil no puede conectarse a `localhost` o `127.0.0.1` porque estos apuntan al dispositivo móvil, no al servidor.

## Solución

### 1. Encontrar tu IP local

**Windows:**
```bash
ipconfig
```
Busca la línea "Dirección IPv4" en tu adaptador de red activo (generalmente `192.168.x.x` o `10.x.x.x`)

**Linux/Mac:**
```bash
ifconfig
# o
ip addr show
```

### 2. Configurar la IP en el archivo .env

Abre el archivo `.env` en la raíz del proyecto y agrega o modifica:

```env
APP_URL=http://192.168.0.129:8001
APP_MOBILE_API_URL=http://192.168.0.129:8001/api
```

**IMPORTANTE:** Reemplaza `192.168.0.129` con TU IP local.

### 3. Limpiar y recargar configuración

```bash
php artisan config:clear
php artisan config:cache
```

### 4. Verificar que el servidor esté escuchando en todas las interfaces

Asegúrate de que Laravel esté escuchando en `0.0.0.0` y no solo en `127.0.0.1`:

```bash
php artisan serve --host=0.0.0.0 --port=8001
```

### 5. Verificar configuración

Visita en tu navegador o desde la app móvil:
```
http://TU_IP:8001/api/config
```

Deberías ver un JSON con la configuración de la API.

### 6. Configurar la app móvil

En la app móvil, usa la URL que obtuviste del endpoint `/api/config` o directamente:
```
http://TU_IP:8001/api
```

## Endpoints disponibles

- `GET /api/config` - Obtener configuración de la API
- `GET /api/ping` - Verificar que la API funciona
- `GET /api/transportista/{id}/envios` - Obtener envíos del transportista
- `POST /api/envios/{id}/aceptar` - Aceptar envío
- `POST /api/envios/{id}/rechazar` - Rechazar envío

## Notas importantes

1. **Firewall:** Asegúrate de que el puerto 8001 esté abierto en el firewall de Windows
2. **Misma red:** El dispositivo móvil debe estar en la misma red WiFi que tu computadora
3. **IP dinámica:** Si tu IP cambia, actualiza el `.env` y recarga la configuración

